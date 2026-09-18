<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * FilialController
 */
class FilialController extends Controller
{
    private Filial $filialModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin', 'admin_empresa']);
        $this->filialModel = new Filial();
        $this->empresaModel = new Empresa();
    }

    private function empresaAtualId(): ?int
    {
        if (!empty($_SESSION['empresa_id'])) {
            return (int) $_SESSION['empresa_id'];
        }
        $valor = $_GET['empresa_id'] ?? $_POST['empresa_id'] ?? null;
        return $valor ? (int) $valor : null;
    }

    public function index(): void
    {
        $empresaId = $this->empresaAtualId();

        // Se não tem empresa selecionada, mostra a seleção de empresas
        if ($empresaId === null) {
            $this->selecionarEmpresa();
            return;
        }

        // Buscar empresa e suas filiais
        $empresa = $this->empresaModel->encontrarPorId($empresaId);
        
        if (!$empresa) {
            definirFlash('erro', 'Empresa não encontrada.');
            $this->redirecionar('filiais/index');
            return;
        }

        $filiais = $this->filialModel->porEmpresa($empresaId);

        $this->renderizar('filiais/index', [
            'tituloPagina' => 'Filiais - ' . htmlspecialchars($empresa['nome']),
            'paginaAtiva'  => 'filiais',
            'empresa'      => $empresa,
            'filiais'      => $filiais,
        ]);
    }

    /**
     * Mostra a seleção de empresas com contagem de filiais
     * CORRIGIDO: Usa método simples e direto
     */
    private function selecionarEmpresa(): void
    {
        // =============================================
        // 1. BUSCAR EMPRESAS ATIVAS (MÉTODO SIMPLES)
        // =============================================
        $empresas = $this->empresaModel->ativas();
        
        // =============================================
        // 2. VERIFICAR SE EXISTEM EMPRESAS
        // =============================================
        if (empty($empresas)) {
            definirFlash('aviso', 'Não existem empresas ativas. Crie uma empresa primeiro.');
            $this->redirecionar('empresas/criar');
            return;
        }

        // =============================================
        // 3. CALCULAR TOTAL DE FILIAIS PARA CADA EMPRESA
        // =============================================
        $empresasComDados = [];
        foreach ($empresas as $empresa) {
            // Buscar filiais desta empresa
            $filiais = $this->filialModel->porEmpresa((int) $empresa['id']);
            
            // Montar array com todos os dados
            $empresasComDados[] = [
                'id' => (int) $empresa['id'],
                'nome' => $empresa['nome'] ?? 'Empresa',
                'email_contacto' => $empresa['email_contacto'] ?? '',
                'telefone' => $empresa['telefone'] ?? '',
                'nif' => $empresa['nif'] ?? '',
                'ativa' => isset($empresa['ativa']) ? (bool) $empresa['ativa'] : true,
                'total_filiais' => count($filiais), // CONTAGEM CORRETA
            ];
        }

        // =============================================
        // 4. DEBUG (podes remover depois)
        // =============================================
        // error_log('Empresas com dados: ' . print_r($empresasComDados, true));

        // =============================================
        // 5. RENDERIZAR VIEW
        // =============================================
        $this->renderizar('filiais/selecionar-empresa', [
            'tituloPagina' => 'Selecionar Empresa',
            'paginaAtiva'  => 'filiais',
            'empresas'     => $empresasComDados,
        ]);
    }

    public function criar(): void
    {
        $empresaId = $this->empresaAtualId();
        if ($empresaId === null) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('filiais/index');
            return;
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
        $this->filialModel->inserir($dados['campos']);
        definirFlash('sucesso', 'Filial criada com sucesso.');
        $this->redirecionar('filiais/index?empresa_id=' . $empresaId);
    }

    public function editar(string $id): void
    {
        $filial = $this->filialModel->encontrarPorId((int) $id);

        if (!$filial) {
            definirFlash('erro', 'Filial não encontrada.');
            $this->redirecionar('filiais/index');
            return;
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

        $this->filialModel->atualizar((int) $id, $dados['campos']);
        definirFlash('sucesso', 'Filial atualizada com sucesso.');
        $this->redirecionar('filiais/index?empresa_id=' . ($_POST['empresa_id'] ?? ''));
    }

    public function alternarEstado(string $id): void
    {
        $filial = $this->filialModel->encontrarPorId((int) $id);

        if ($filial) {
            $this->filialModel->atualizar((int) $id, ['ativa' => $filial['ativa'] ? 0 : 1]);
            definirFlash('sucesso', $filial['ativa'] ? 'Filial desativada.' : 'Filial reativada.');
        }

        $this->redirecionar('filiais/index?empresa_id=' . ($filial['empresa_id'] ?? ''));
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