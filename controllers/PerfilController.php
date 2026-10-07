<?php
/**
 * PerfilController
 * Gerencia o perfil do utilizador logado
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Modulo.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/CloudinaryHelper.php';
require_once CAMINHO_RAIZ . '/models/PerfilUsuario.php';

class PerfilController extends Controller
{
    private Usuario $usuarioModel;
    private Empresa $empresaModel;
    private Modulo $moduloModel;
    private PerfilUsuario $perfilModel;

    public function __construct()
    {
        (new AuthMiddleware())->verificar();
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
        $this->moduloModel = new Modulo();
        $this->perfilModel = new PerfilUsuario();
    }

    /**
     * Página principal do perfil
     */
    public function index(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $usuario = $this->usuarioModel->encontrarPorId($usuarioId);
        
        if (!$usuario) {
            definirFlash('erro', 'Utilizador não encontrado.');
            $this->redirecionar('dashboard');
            return;
        }

        // Buscar nome da empresa
        $empresaNome = '';
        if (!empty($usuario['empresa_id'])) {
            $empresa = $this->empresaModel->encontrarPorId((int) $usuario['empresa_id']);
            $empresaNome = $empresa['nome'] ?? '';
        }

        // Buscar permissões do utilizador
        $permissoes = $this->moduloModel->getPermissoesUsuario($usuarioId);

        // Perfil estendido (foto Cloudinary, departamento, biografia)
        $perfilExtra = [];
        try {
            $perfilExtra = $this->perfilModel->buscarPorUsuario($usuarioId) ?: [];
        } catch (Throwable $e) {
            error_log('perfil_extra_consulta: ' . $e->getMessage());
        }
        // Fallback: foto guardada diretamente na tabela usuarios
        if (empty($perfilExtra['foto_url']) && !empty($usuario['foto_url'])) {
            $perfilExtra['foto_url'] = $usuario['foto_url'];
        }

        $this->renderizar('perfil/index', [
            'tituloPagina' => 'Meu Perfil',
            'paginaAtiva' => 'perfil',
            'usuario' => $usuario,
            'empresaNome' => $empresaNome,
            'permissoes' => $permissoes,
            'perfilExtra' => $perfilExtra,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Atualizar dados do perfil
     */
    public function atualizar(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('perfil');
            return;
        }

        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');

        if ($nome === '') {
            definirFlash('erro', 'O nome é obrigatório.');
            $this->redirecionar('perfil');
            return;
        }

        $atualizado = $this->usuarioModel->atualizar($usuarioId, [
            'nome' => $nome,
            'telefone' => $telefone ?: null,
            'cargo' => $cargo ?: null,
        ]);

        // Campos estendidos do perfil (tabela perfis_usuarios)
        $departamento = trim($_POST['departamento'] ?? '');
        $biografia    = trim($_POST['biografia'] ?? '');
        $perfilExtra  = ['departamento' => $departamento ?: null, 'biografia' => $biografia ?: null];

        // Upload de foto de perfil (Cloudinary com fallback local)
        $fotoProcessada = false;
        if (!empty($_FILES['foto']['name']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = CloudinaryHelper::enviarImagem($_FILES['foto'], 'monana/avatares');
            if ($upload['sucesso']) {
                $anterior = $this->perfilModel->buscarPorUsuario($usuarioId);
                if (!empty($anterior['foto_public_id'])) {
                    CloudinaryHelper::removerImagem((string) $anterior['foto_public_id']);
                }
                $perfilExtra['foto_url'] = $upload['url'];
                $perfilExtra['foto_public_id'] = $upload['public_id'];
                $fotoProcessada = true;
            } else {
                definirFlash('erro', 'Foto não enviada: ' . $upload['erro']);
            }
        } elseif (isset($_POST['remover_foto'])) {
            $anterior = $this->perfilModel->buscarPorUsuario($usuarioId);
            if (!empty($anterior['foto_public_id'])) {
                CloudinaryHelper::removerImagem((string) $anterior['foto_public_id']);
            }
            $perfilExtra['foto_url'] = null;
            $perfilExtra['foto_public_id'] = null;
            $fotoProcessada = true;
        }

        try {
            $this->perfilModel->salvar($usuarioId, $perfilExtra + ['telefone' => $telefone ?: null, 'cargo' => $cargo ?: null]);
        } catch (Throwable $e) {
            // perfis_usuarios pode ainda não existir na base (migração pendente)
            error_log('perfil_extra: ' . $e->getMessage());
        }

        // Fallback: garante que a foto fica registada mesmo sem a tabela perfis_usuarios
        if ($fotoProcessada) {
            $guardado = false;
            try {
                $pdo = Database::obterLigacao();
                $st  = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t');
                $st->execute([':t' => 'perfis_usuarios']);
                if ((int) $st->fetchColumn() > 0) {
                    $this->perfilModel->salvar($usuarioId, [
                        'foto_url'        => $perfilExtra['foto_url'] ?? null,
                        'foto_public_id'  => $perfilExtra['foto_public_id'] ?? null,
                    ]);
                    $guardado = true;
                }
            } catch (Throwable $e) {
                error_log('fallback_foto_perfil: ' . $e->getMessage());
            }
            if (!$guardado) {
                try {
                    $this->garantirColunaFotoUsuarios();
                    $this->usuarioModel->atualizar($usuarioId, [
                        'foto_url' => $perfilExtra['foto_url'] ?? null,
                    ]);
                } catch (Throwable $e) {
                    error_log('fallback_foto_usuarios: ' . $e->getMessage());
                    definirFlash('erro', 'A foto foi enviada, mas não foi possível associá-la ao seu perfil.');
                }
            }
        }

        if ($atualizado) {
            $_SESSION['usuario_nome'] = $nome;
            AuditoriaHelper::registar('perfil_atualizado', 'usuarios', $usuarioId, null, ['nome' => $nome, 'telefone' => $telefone, 'cargo' => $cargo], 'baixa');
            definirFlash('sucesso', 'Perfil atualizado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao atualizar perfil.');
        }

        $this->redirecionar('perfil');
    }

    /**
     * Garante que a tabela usuarios tem a coluna foto_url (fallback quando
     * a tabela perfis_usuarios ainda não foi migrada). Idempotente.
     */
    private function garantirColunaFotoUsuarios(): void
    {
        $pdo = Database::obterLigacao();
        $st  = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c'
        );
        $st->execute([':t' => 'usuarios', ':c' => 'foto_url']);
        if ((int) $st->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE usuarios ADD COLUMN foto_url VARCHAR(500) NULL');
        }
    }

    /**
     * Alterar senha do utilizador
     */
    public function alterarSenha(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('perfil');
            return;
        }

        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Buscar utilizador
        $usuario = $this->usuarioModel->encontrarPorId($usuarioId);
        if (!$usuario) {
            definirFlash('erro', 'Utilizador não encontrado.');
            $this->redirecionar('perfil');
            return;
        }

        // Validar senha atual
        if (!$this->usuarioModel->verificarSenha($senhaAtual, $usuario['senha_hash'])) {
            definirFlash('erro', 'Senha atual incorreta.');
            $this->redirecionar('perfil');
            return;
        }

        // Validar nova senha
        if (strlen($novaSenha) < 8) {
            definirFlash('erro', 'A nova senha deve ter pelo menos 8 caracteres.');
            $this->redirecionar('perfil');
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            definirFlash('erro', 'As senhas não coincidem.');
            $this->redirecionar('perfil');
            return;
        }

        // Atualizar senha
        $atualizado = $this->usuarioModel->atualizar($usuarioId, [
            'senha_hash' => $this->usuarioModel->criarHashSenha($novaSenha),
            'primeiro_acesso' => 0,
        ]);

        if ($atualizado) {
            AuditoriaHelper::registar('senha_alterada', 'usuarios', $usuarioId, null, null, 'alta');
            definirFlash('sucesso', 'Senha alterada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao alterar senha.');
        }

        $this->redirecionar('perfil');
    }
}