<?php
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * FiliaisController
 * - Super Administrador: escolhe a empresa (via ?empresa_id=) para gerir as suas filiais.
 * - Administrador da Empresa: gere directamente as filiais da sua própria empresa.
 */
class FiliaisController extends Controller
{
    private Filial $filialModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin', 'admin_empresa']);
        $this->filialModel  = new Filial();
        $this->empresaModel = new Empresa();
    }

    /** Determina para qual empresa estamos a trabalhar nesta requisição */
    private function empresaAtualId(): ?int
    {
        if (!empty($_SESSION['empresa_id'])) {
            return (int) $_SESSION['empresa_id']; // admin_empresa: âmbito fixo
        }
        // super_admin: vem de ?empresa_id= ou do campo do formulário
        $valor = $_GET['empresa_id'] ?? $_POST['empresa_id'] ?? null;
        return $valor ? (int) $valor : null;
    }

    public function index(): void
    {
        $empresaId = $this->empresaAtualId();

        // Super admin sem empresa escolhida: mostra selector de empresas
        if ($empresaId === null) {
            $empresas = $this->empresaModel->ativas();
            foreach ($empresas as &$emp) {
                $emp['total_filiais'] = count($this->filialModel->porEmpresa((int) $emp['id']));
            }
            unset($emp);

            $this->renderizar('filiais/selecionar-empresa', [
                'tituloPagina' => 'Filiais — Selecionar Empresa',
                'paginaAtiva'  => 'filiais',
                'empresas'     => $empresas,
            ]);
            return;
        }

        $empresa = $this->empresaModel->encontrarPorId($empresaId);
        $filiais = $this->filialModel->porEmpresaTodas($empresaId);

        $this->renderizar('filiais/index', [
            'tituloPagina' => 'Filiais',
            'paginaAtiva'  => 'filiais',
            'empresa'      => $empresa,
            'filiais'      => $filiais,
        ]);
    }

    public function criar(): void
    {
        $empresaId = $this->empresaAtualId();
        if ($empresaId === null) {
            $this->redirecionar('filiais/index');
        }

        $this->renderizar('filiais/form', [
            'tituloPagina' => 'Nova Filial',
            'paginaAtiva'  => 'filiais',
            'empresaId'    => $empresaId,
            'filial'       => null,
            'erros'        => [],
        ]);
    }

    public function gravar(): void
    {
        $empresaId = $this->empresaAtualId();
        $dados = $this->dadosValidados();

        if ($empresaId === null || !empty($dados['erros'])) {
            $this->renderizar('filiais/form', [
                'tituloPagina' => 'Nova Filial',
                'paginaAtiva'  => 'filiais',
                'empresaId'    => $empresaId,
                'filial'       => $_POST,
                'erros'        => $empresaId === null ? ['nome' => 'Selecione uma empresa válida.'] : $dados['erros'],
            ]);
            return;
        }

        $dados['campos']['empresa_id'] = $empresaId;
        $id = $this->filialModel->inserir($dados['campos']);
        AuditoriaHelper::registar('filial_criada', 'filiais', $id, null, $dados['campos']);
        definirFlash('sucesso', 'Filial criada com sucesso.');
        $this->redirecionar('filiais/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . $empresaId));
    }

    public function editar(string $id): void
    {
        $filial = $this->filialModel->encontrarPorId((int) $id);

        if (!$filial) {
            definirFlash('erro', 'Filial não encontrada.');
            $this->redirecionar('filiais/index');
        }

        $this->renderizar('filiais/form', [
            'tituloPagina' => 'Editar Filial',
            'paginaAtiva'  => 'filiais',
            'empresaId'    => $filial['empresa_id'],
            'filial'       => $filial,
            'erros'        => [],
        ]);
    }

    public function atualizar(string $id): void
    {
        $dados = $this->dadosValidados();

        if (!empty($dados['erros'])) {
            $this->renderizar('filiais/form', [
                'tituloPagina' => 'Editar Filial',
                'paginaAtiva'  => 'filiais',
                'empresaId'    => $_POST['empresa_id'] ?? null,
                'filial'       => array_merge(['id' => $id], $_POST),
                'erros'        => $dados['erros'],
            ]);
            return;
        }

        $antes = $this->filialModel->encontrarPorId((int) $id);
        $this->filialModel->atualizar((int) $id, $dados['campos']);
        AuditoriaHelper::registar('filial_editada', 'filiais', (int) $id, $antes ?: null, $dados['campos']);
        definirFlash('sucesso', 'Filial atualizada com sucesso.');
        $this->redirecionar('filiais/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . ($_POST['empresa_id'] ?? '')));
    }

    public function alternarEstado(string $id): void
    {
        $filial = $this->filialModel->encontrarPorId((int) $id);

        if ($filial) {
            $this->filialModel->atualizar((int) $id, ['ativa' => $filial['ativa'] ? 0 : 1]);
            definirFlash('sucesso', $filial['ativa'] ? 'Filial desativada.' : 'Filial reativada.');
            if ($filial['ativa']) {
                NotificacaoHelper::paraAdministradoresEmpresa((int) $filial['empresa_id'], 'aviso', 'Filial desativada', 'Filial ' . $filial['nome'] . ' foi desativada por si.', URL_BASE . '/filiais/index');
            }
        }

        $this->redirecionar('filiais/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . ($filial['empresa_id'] ?? '')));
    }

    private function dadosValidados(): array
    {
        $erros = [];
        $nome = trim($_POST['nome'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome da filial é obrigatório.';
        }

        return [
            'erros'  => $erros,
            'campos' => [
                'nome'     => $nome,
                'endereco' => $endereco ?: null,
                'telefone' => $telefone ?: null,
            ],
        ];
    }
}
