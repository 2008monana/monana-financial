<?php
/**
 * CategoriasController
 * Gestão de categorias de entrada e saída
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Categoria.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';

class CategoriasController extends Controller
{
    private Categoria $categoriaModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        (new AuthMiddleware())->verificar();
        
        // Apenas Admin Empresa e Super Admin podem gerir categorias
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        if ($perfil === 'visualizador' || $perfil === 'usuario_interno') {
            definirFlash('erro', 'Não tem permissão para aceder a esta página.');
            $this->redirecionar('dashboard/index');
        }

        $this->categoriaModel = new Categoria();
        $this->empresaModel = new Empresa();
    }

    /**
     * Listar categorias da empresa
     */
    public function index(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Super Admin: pode escolher a empresa
        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_GET['empresa_id'] ?? $empresaId);
        }

        // Buscar empresas (Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Buscar categorias da empresa
        $categorias = [];
        if ($empresaId) {
            $categorias = $this->categoriaModel->porEmpresa((int) $empresaId);
        }

        // Separar por tipo
        $entradas = array_filter($categorias, fn($c) => $c['tipo'] === 'entrada');
        $saidas = array_filter($categorias, fn($c) => $c['tipo'] === 'saida');

        // Buscar nome da empresa
        $empresaNome = '';
        if ($empresaId) {
            $empresa = $this->empresaModel->encontrarPorId((int) $empresaId);
            $empresaNome = $empresa['nome'] ?? '';
        }

        $this->renderizar('categorias/index', [
            'tituloPagina' => 'Categorias',
            'paginaAtiva' => 'categorias',
            'entradas' => $entradas,
            'saidas' => $saidas,
            'empresaId' => $empresaId,
            'empresaNome' => $empresaNome,
            'empresas' => $empresas,
            'perfil' => $perfil,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Formulário de criação
     */
    public function criar(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_GET['empresa_id'] ?? $empresaId);
        }

        if (!$empresaId) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('categorias/index');
            return;
        }

        $this->renderizar('categorias/form', [
            'tituloPagina' => 'Nova Categoria',
            'paginaAtiva' => 'categorias',
            'categoria' => null,
            'empresaId' => $empresaId,
            'erros' => [],
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Salvar nova categoria
     */
    public function armazenar(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_POST['empresa_id'] ?? $empresaId);
        }

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('categorias/criar');
            return;
        }

        $dados = $this->validarDados($_POST);

        if (!empty($dados['erros'])) {
            $this->renderizar('categorias/form', [
                'tituloPagina' => 'Nova Categoria',
                'paginaAtiva' => 'categorias',
                'categoria' => $_POST,
                'empresaId' => $empresaId,
                'erros' => $dados['erros'],
                'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            ]);
            return;
        }

        $dados['campos']['empresa_id'] = $empresaId;
        $id = $this->categoriaModel->inserir($dados['campos']);

        if ($id) {
            definirFlash('sucesso', 'Categoria criada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao criar categoria.');
        }

        $this->redirecionar('categorias/index' . ($perfil === 'super_admin' ? '?empresa_id=' . $empresaId : ''));
    }

    /**
     * Formulário de edição
     */
    public function editar(string $id): void
    {
        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if (!$categoria) {
            definirFlash('erro', 'Categoria não encontrada.');
            $this->redirecionar('categorias/index');
            return;
        }

        $this->renderizar('categorias/form', [
            'tituloPagina' => 'Editar Categoria',
            'paginaAtiva' => 'categorias',
            'categoria' => $categoria,
            'empresaId' => $categoria['empresa_id'],
            'erros' => [],
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Atualizar categoria
     */
    public function atualizar(string $id): void
    {
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('categorias/editar/' . $id);
            return;
        }

        $dados = $this->validarDados($_POST);

        if (!empty($dados['erros'])) {
            $this->renderizar('categorias/form', [
                'tituloPagina' => 'Editar Categoria',
                'paginaAtiva' => 'categorias',
                'categoria' => array_merge(['id' => $id], $_POST),
                'empresaId' => $_POST['empresa_id'] ?? null,
                'erros' => $dados['erros'],
                'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            ]);
            return;
        }

        $atualizado = $this->categoriaModel->atualizar((int) $id, $dados['campos']);

        if ($atualizado) {
            definirFlash('sucesso', 'Categoria atualizada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao atualizar categoria.');
        }

        $this->redirecionar('categorias/index');
    }

    /**
     * Alternar estado (ativo/inativo)
     */
    public function alternarEstado(string $id): void
    {
        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if ($categoria) {
            $novoEstado = $categoria['ativa'] ? 0 : 1;
            $this->categoriaModel->atualizar((int) $id, ['ativa' => $novoEstado]);
            definirFlash('sucesso', $categoria['ativa'] ? 'Categoria desativada.' : 'Categoria reativada.');
        }

        $this->redirecionar('categorias/index');
    }

    /**
     * Eliminar categoria (apenas Super Admin)
     */
    public function eliminar(string $id): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        if ($perfil !== 'super_admin') {
            definirFlash('erro', 'Apenas o Super Administrador pode eliminar categorias.');
            $this->redirecionar('categorias/index');
            return;
        }

        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if ($categoria) {
            $this->categoriaModel->eliminar((int) $id);
            definirFlash('sucesso', 'Categoria eliminada com sucesso.');
        }

        $this->redirecionar('categorias/index');
    }

    /**
     * Validar dados do formulário
     */
    private function validarDados(array $dados): array
    {
        $erros = [];
        $nome = trim($dados['nome'] ?? '');
        $tipo = $dados['tipo'] ?? '';
        $cor = trim($dados['cor'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome da categoria é obrigatório.';
        }

        if (!in_array($tipo, ['entrada', 'saida'])) {
            $erros['tipo'] = 'Selecione um tipo válido.';
        }

        // Validar cor (hexadecimal simples)
        if ($cor !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $cor)) {
            $erros['cor'] = 'Cor inválida. Use o formato #RRGGBB.';
        }

        return [
            'erros' => $erros,
            'campos' => [
                'nome' => $nome,
                'tipo' => $tipo,
                'cor' => $cor ?: '#64748b',
                'ativa' => isset($dados['ativa']) ? 1 : 0,
            ],
        ];
    }
}