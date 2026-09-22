<?php
/**
 * MetodosPagamentoController
 * Gestão dinâmica de métodos de pagamento (entradas e saídas)
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/models/MetodoPagamento.php';
require_once CAMINHO_RAIZ . '/models/CategoriaSaida.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';

class MetodosPagamentoController extends Controller
{
    private MetodoPagamento $metodoModel;
    private CategoriaSaida $categoriaSaidaModel;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin', 'admin_empresa']);
        $this->metodoModel = new MetodoPagamento();
        $this->categoriaSaidaModel = new CategoriaSaida();
    }

    /**
     * Listar métodos de pagamento
     */
    public function index(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        
        if (!$empresaId) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('dashboard/index');
            return;
        }

        $metodos = $this->metodoModel->porEmpresa((int)$empresaId, false);
        
        $entradas = array_filter($metodos, fn($m) => $m['tipo'] === 'entrada');
        $saidas = array_filter($metodos, fn($m) => $m['tipo'] === 'saida');

        // Categorias de saída
        $categoriasSaida = $this->categoriaSaidaModel->porEmpresa((int)$empresaId, false);

        $this->renderizar('metodos-pagamento/index', [
            'tituloPagina' => 'Métodos de Pagamento',
            'paginaAtiva' => 'metodos-pagamento',
            'entradas' => $entradas,
            'saidas' => $saidas,
            'categoriasSaida' => $categoriasSaida,
            'empresaId' => $empresaId,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Formulário de criação
     */
    public function criar(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if (!$empresaId) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('dashboard/index');
            return;
        }

        $this->renderizar('metodos-pagamento/form', [
            'tituloPagina' => 'Novo Método de Pagamento',
            'paginaAtiva' => 'metodos-pagamento',
            'metodo' => null,
            'empresaId' => $empresaId,
            'categorias' => $this->metodoModel->getCategorias(),
            'cores' => $this->metodoModel->getCoresDisponiveis(),
            'icones' => $this->metodoModel->getIconesDisponiveis(),
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Salvar método
     */
    public function gravar(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('metodos-pagamento/criar');
            return;
        }

        $dados = [
            'empresa_id' => $empresaId,
            'nome' => trim($_POST['nome'] ?? ''),
            'codigo' => trim($_POST['codigo'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'entrada',
            'categoria' => $_POST['categoria'] ?? 'outro',
            'icone' => $_POST['icone'] ?? 'fa-credit-card',
            'cor' => $_POST['cor'] ?? '#64748b',
            'ordem' => (int) ($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['nome'])) {
            definirFlash('erro', 'O nome é obrigatório.');
            $this->redirecionar('metodos-pagamento/criar');
            return;
        }

        $id = $this->metodoModel->criar($dados);

        if ($id) {
            AuditoriaHelper::registar('metodo_criado', 'metodos_pagamento', (int) $id, null, $dados, 'media');
            definirFlash('sucesso', 'Método de pagamento criado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao criar método de pagamento.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    /**
     * Formulário de edição
     */
    public function editar(string $id): void
    {
        $metodo = $this->metodoModel->encontrarPorId((int)$id);

        if (!$metodo) {
            definirFlash('erro', 'Método não encontrado.');
            $this->redirecionar('metodos-pagamento/index');
            return;
        }

        $this->renderizar('metodos-pagamento/form', [
            'tituloPagina' => 'Editar Método de Pagamento',
            'paginaAtiva' => 'metodos-pagamento',
            'metodo' => $metodo,
            'empresaId' => $metodo['empresa_id'],
            'categorias' => $this->metodoModel->getCategorias(),
            'cores' => $this->metodoModel->getCoresDisponiveis(),
            'icones' => $this->metodoModel->getIconesDisponiveis(),
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Atualizar método
     */
    public function atualizar(string $id): void
    {
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('metodos-pagamento/editar/' . $id);
            return;
        }

        $dados = [
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'entrada',
            'categoria' => $_POST['categoria'] ?? 'outro',
            'icone' => $_POST['icone'] ?? 'fa-credit-card',
            'cor' => $_POST['cor'] ?? '#64748b',
            'ordem' => (int) ($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['nome'])) {
            definirFlash('erro', 'O nome é obrigatório.');
            $this->redirecionar('metodos-pagamento/editar/' . $id);
            return;
        }

        $antes = $this->metodoModel->encontrarPorId((int) $id);
        $atualizado = $this->metodoModel->atualizar((int)$id, $dados);

        if ($atualizado) {
            AuditoriaHelper::registar('metodo_editado', 'metodos_pagamento', (int) $id, $antes ?: null, $dados, 'media');
            definirFlash('sucesso', 'Método atualizado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao atualizar método.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    /**
     * Alternar estado (ativo/inativo)
     */
    public function alternarEstado(string $id): void
    {
        $metodo = $this->metodoModel->encontrarPorId((int)$id);

        if ($metodo) {
            $novoEstado = $metodo['ativo'] ? 0 : 1;
            $this->metodoModel->atualizar((int)$id, ['ativo' => $novoEstado]);
            AuditoriaHelper::registar('metodo_estado_alterado', 'metodos_pagamento', (int) $id, ['ativo'=>$metodo['ativo']], ['ativo'=>$novoEstado], 'media');
            definirFlash('sucesso', $metodo['ativo'] ? 'Método desativado.' : 'Método reativado.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    /**
     * Eliminar método
     */
    public function eliminar(string $id): void
    {
        $metodo = $this->metodoModel->encontrarPorId((int)$id);

        if ($metodo) {
            $this->metodoModel->eliminar((int)$id);
            definirFlash('sucesso', 'Método de pagamento eliminado.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    // =============================================
    // CATEGORIAS DE SAÍDA
    // =============================================

    /**
     * Criar categoria de saída
     */
    public function criarCategoriaSaida(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if (!$empresaId) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('dashboard/index');
            return;
        }

        $this->renderizar('metodos-pagamento/categoria-form', [
            'tituloPagina' => 'Nova Categoria de Saída',
            'paginaAtiva' => 'metodos-pagamento',
            'categoria' => null,
            'empresaId' => $empresaId,
            'cores' => $this->categoriaSaidaModel->getCoresDisponiveis(),
            'icones' => $this->categoriaSaidaModel->getIconesDisponiveis(),
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Salvar categoria de saída
     */
    public function gravarCategoriaSaida(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('metodos-pagamento/criarCategoriaSaida');
            return;
        }

        $dados = [
            'empresa_id' => $empresaId,
            'nome' => trim($_POST['nome'] ?? ''),
            'codigo' => trim($_POST['codigo'] ?? ''),
            'icone' => $_POST['icone'] ?? 'fa-tag',
            'cor' => $_POST['cor'] ?? '#64748b',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];

        if (empty($dados['nome'])) {
            definirFlash('erro', 'O nome é obrigatório.');
            $this->redirecionar('metodos-pagamento/criarCategoriaSaida');
            return;
        }

        $id = $this->categoriaSaidaModel->criar($dados);

        if ($id) {
            definirFlash('sucesso', 'Categoria de saída criada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao criar categoria de saída.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    /**
     * Alternar estado da categoria de saída
     */
    public function alternarEstadoCategoria(string $id): void
    {
        $categoria = $this->categoriaSaidaModel->encontrarPorId((int)$id);

        if ($categoria) {
            $novoEstado = $categoria['ativo'] ? 0 : 1;
            $this->categoriaSaidaModel->atualizar((int)$id, ['ativo' => $novoEstado]);
            definirFlash('sucesso', $categoria['ativo'] ? 'Categoria desativada.' : 'Categoria reativada.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }

    /**
     * Eliminar categoria de saída
     */
    public function eliminarCategoria(string $id): void
    {
        $categoria = $this->categoriaSaidaModel->encontrarPorId((int)$id);

        if ($categoria) {
            $this->categoriaSaidaModel->eliminar((int)$id);
            definirFlash('sucesso', 'Categoria de saída eliminada.');
        }

        $this->redirecionar('metodos-pagamento/index');
    }
}
