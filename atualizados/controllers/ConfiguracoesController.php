<?php
require_once CAMINHO_RAIZ.'/core/Controller.php';
require_once CAMINHO_RAIZ.'/models/Configuracao.php';
require_once CAMINHO_RAIZ.'/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ.'/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ.'/helpers/AuditoriaHelper.php';

class ConfiguracoesController extends Controller
{
    private Configuracao $model;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin','admin_empresa']);
        $this->model = new Configuracao();
    }

    /** Empresa do utilizador com fallback para a BD e reparo da sessão. */
    private function empresaIdSessao(): ?int
    {
        $id = (int)($_SESSION['empresa_id'] ?? 0);
        if ($id > 0) return $id;
        $uid = (int)($_SESSION['usuario_id'] ?? 0);
        if ($uid > 0) {
            require_once CAMINHO_RAIZ.'/models/Usuario.php';
            $u = (new Usuario())->encontrarPorId($uid);
            $id = (int)($u['empresa_id'] ?? 0);
            if ($id > 0) $_SESSION['empresa_id'] = $id;
        }
        return $id > 0 ? $id : null;
    }

    public function index(): void
    {
        if (($_SESSION['usuario_perfil'] ?? '') === 'super_admin') {
            // header() já pode ter sido enviado pelo layout — usar meta refresh seguro.
            echo '<meta http-equiv="refresh" content="0;url='.URL_BASE.'/configuracoes/sistema">';
            echo '<p>A redirecionar… <a href="'.URL_BASE.'/configuracoes/sistema">Clique aqui se não for redirecionado.</a></p>';
        } else {
            $this->empresa();
        }
    }

    public function sistema(): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        $this->renderizar('configuracoes/sistema', [
            'tituloPagina' => 'Configurações do Sistema',
            'paginaAtiva'  => 'configuracoes',
            'configuracoes'=> $this->model->obterTodas(null),
            'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    public function empresa(): void
    {
        $id = $this->empresaIdSessao();
        if ($id === null) {
            definirFlash('erro', 'Nenhuma empresa associada ao seu utilizador.');
            $this->redirecionar('dashboard');
            return;
        }
        $this->renderizar('configuracoes/empresa', [
            'tituloPagina' => 'Configurações da Empresa',
            'paginaAtiva'  => 'configuracoes',
            'configuracoes'=> $this->model->obterTodas($id),
            'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    public function guardar(): void
    {
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Sessão expirada. Volte a tentar.');
            $this->redirecionar('configuracoes/index');
            return;
        }

        $sistema = ($_SESSION['usuario_perfil'] ?? '') === 'super_admin'
                && ($_POST['contexto'] ?? '') === 'sistema';
        $id = $sistema ? null : $this->empresaIdSessao();
        if (!$sistema && $id === null) {
            definirFlash('erro', 'Nenhuma empresa associada ao seu utilizador.');
            $this->redirecionar('dashboard');
            return;
        }

        foreach (($_POST['configuracoes'] ?? []) as $chave => $valor) {
            if (!preg_match('/^[a-z0-9_]{2,100}$/', $chave)) continue;
            if (is_array($valor)) continue;
            $tipo = $chave === 'smtp_senha' ? 'secreto' : 'texto';
            if ($tipo === 'secreto' && $valor !== '') {
                $valor = $this->encriptar((string)$valor);
            }
            if ($valor !== '' || $tipo !== 'secreto') {
                $this->model->guardar($id, $chave, trim((string)$valor), $tipo);
            }
        }

        if (!$sistema) {
            $destino = CAMINHO_UPLOADS.'/logos';
            if (!is_dir($destino)) @mkdir($destino, 0775, true);

            if (!empty($_FILES['logotipo_arquivo']['name']) && ($_FILES['logotipo_arquivo']['error'] ?? 1) === 0) {
                $ext = strtolower(pathinfo($_FILES['logotipo_arquivo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png','jpg','jpeg','svg','webp']) && $_FILES['logotipo_arquivo']['size'] <= 2*1024*1024) {
                    $antigo = $this->model->obterTodas($id)['logotipo'] ?? '';
                    $nomeUnico = 'logo_'.$id.'_'.bin2hex(random_bytes(6)).'.'.$ext;
                    if (move_uploaded_file($_FILES['logotipo_arquivo']['tmp_name'], $destino.'/'.$nomeUnico)) {
                        $this->model->guardar($id, 'logotipo', 'uploads/logos/'.$nomeUnico, 'texto');
                        if ($antigo && is_file(CAMINHO_RAIZ.'/public/'.$antigo)) @unlink(CAMINHO_RAIZ.'/public/'.$antigo);
                    } else {
                        definirFlash('erro', 'Falha ao gravar o ficheiro do logotipo.');
                    }
                } else {
                    definirFlash('erro', 'Logotipo inválido: use PNG/JPG/SVG/WebP até 2 MB.');
                }
            } elseif (!empty($_POST['remover_logotipo'])) {
                $antigo = $this->model->obterTodas($id)['logotipo'] ?? '';
                $this->model->guardar($id, 'logotipo', '', 'texto');
                if ($antigo && is_file(CAMINHO_RAIZ.'/public/'.$antigo)) @unlink(CAMINHO_RAIZ.'/public/'.$antigo);
            }
        }

        AuditoriaHelper::registar('configuracao_alterada', 'configuracoes', null, null, ['contexto' => $sistema ? 'sistema' : 'empresa']);
        definirFlash('sucesso', 'Configurações guardadas com sucesso.');
        echo '<meta http-equiv="refresh" content="0;url='.URL_BASE.($sistema ? '/configuracoes/sistema' : '/configuracoes/empresa').'">';
    }

    private function encriptar(string $v): string
    {
        $key = hash('sha256', (defined('NOME_SISTEMA') ? NOME_SISTEMA : 'monana'), true);
        $iv = random_bytes(16);
        return base64_encode($iv . openssl_encrypt($v, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }
}
