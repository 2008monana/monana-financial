<?php
/**
 * Controller de Métodos de Pagamento
 * Gerencia as requisições relacionadas a métodos de pagamento
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/MetodoPagamento.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/helpers/Autenticacao.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

class MetodosPagamentoController extends Controller {
    private MetodoPagamento $model;
    private Autenticacao $auth;

    public function __construct() {
        $this->model = new MetodoPagamento();
        $this->auth = new Autenticacao();
    }

    /**
     * Listar métodos de pagamento
     */
    public function index(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem acessar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            definirFlash('erro', 'Permissão negada.');
            header('Location: ' . URL_BASE . '/dashboard');
            exit;
        }
        
        $empresa_id = null;
        if ($usuario['perfil'] === 'admin_empresa') {
            $empresa_id = (int)$usuario['empresa_id'];
        }
        
        // Parâmetros de pesquisa/filtro (GET)
        $busca          = trim($_GET['busca'] ?? '');
        $filtro_empresa = null;
        if ($usuario['perfil'] === 'super_admin' && !empty($_GET['empresa_id'])) {
            $filtro_empresa = (int)$_GET['empresa_id'];
        }
        $metodos = $this->model->listar($empresa_id, false, $busca, $filtro_empresa);

        // Mapa empresa_id => nome (para exibir o nome da empresa na listagem em vez do ID)
        $empresas_mapa = [];
        $empresas_lista = [];
        $empresaModel = new Empresa();
        foreach ($empresaModel->ativas() as $emp) {
            $empresas_mapa[(int) $emp['id']] = $emp['nome'];
            $empresas_lista[] = $emp;
        }

        // Nome da empresa para a topbar
        $empresa_nome = '';
        if ($usuario['perfil'] === 'admin_empresa' && !empty($usuario['empresa_id'])) {
            $empresa = $empresaModel->encontrarPorId((int) $usuario['empresa_id']);
            $empresa_nome = $empresa['nome'] ?? '';
        }

        $this->renderizar('metodos_pagamento/index', [
            'tituloPagina' => 'Métodos de Pagamento',
            'paginaAtiva'  => 'metodos_pagamento',
            'empresa_nome' => $empresa_nome,
            'empresas_mapa' => $empresas_mapa,
            'empresas_lista' => $empresas_lista,
            'metodos'      => $metodos,
            'usuario'      => $usuario,
            'busca'        => $busca,
            'filtro_empresa' => $filtro_empresa,
        ]);
    }

    /**
     * Mostrar formulário de criação
     */
    public function criar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            definirFlash('erro', 'Permissão negada.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $empresas = [];
        if ($usuario['perfil'] === 'super_admin') {
            $empresaModel = new Empresa();
            $empresas = $empresaModel->ativas();
        }

        $this->renderizar('metodos_pagamento/formulario', [
            'tituloPagina' => 'Novo Método de Pagamento',
            'paginaAtiva'  => 'metodos_pagamento',
            'metodo'       => null,
            'empresas'     => $empresas,
            'usuario'      => $usuario,
        ]);
    }

    /**
     * Salvar novo método de pagamento
     */
    public function salvar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            definirFlash('erro', 'Permissão negada.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $erros = [];
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        // Validações
        if (empty($dados['nome'])) {
            $erros['nome'] = 'Nome é obrigatório.';
        }
        
        // Verificar se nome já existe
        if (!empty($dados['nome'])) {
            $metodos_existentes = $this->model->listar(null, false);
            foreach ($metodos_existentes as $metodo) {
                if (strcasecmp($metodo['nome'], $dados['nome']) === 0) {
                    $erros['nome'] = 'Este nome já está em uso.';
                    break;
                }
            }
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_form'] = $dados;
            header('Location: ' . URL_BASE . '/metodos-pagamento/criar');
            exit;
        }
        
        // Preparar dados
        $dados_processados = [
            'nome' => trim($dados['nome']),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'ordem' => !empty($dados['ordem']) ? (int)$dados['ordem'] : 0,
            'icone' => 'fas fa-wallet'
        ];
        
        // Admin Empresa vincula à sua empresa, Super Admin cria padrão (null)
        if ($usuario['perfil'] === 'admin_empresa') {
            $dados_processados['empresa_id'] = (int)$usuario['empresa_id'];
        } else {
            $dados_processados['empresa_id'] = !empty($dados['empresa_id']) ? (int)$dados['empresa_id'] : null;
        }
        
        if ($this->model->criar($dados_processados)) {
            definirFlash('sucesso', 'Método de pagamento cadastrado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao cadastrar método de pagamento.');
        }
        
        header('Location: ' . URL_BASE . '/metodos-pagamento');
        exit;
    }

    /**
     * Mostrar formulário de edição
     */
    public function editar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem editar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            definirFlash('erro', 'Permissão negada.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $id = (int)($_GET['id'] ?? 0);
        $metodo = $this->model->buscarPorId($id);
        
        if (!$metodo) {
            definirFlash('erro', 'Método de pagamento não encontrado.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        // Admin Empresa só pode editar métodos da sua empresa
        if ($usuario['perfil'] === 'admin_empresa' && $metodo['empresa_id'] !== null && $metodo['empresa_id'] != $usuario['empresa_id']) {
            definirFlash('erro', 'Permissão negada para editar este método.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $empresas = [];
        if ($usuario['perfil'] === 'super_admin') {
            $empresaModel = new Empresa();
            $empresas = $empresaModel->ativas();
        }

        $this->renderizar('metodos_pagamento/formulario', [
            'tituloPagina' => 'Editar Método de Pagamento',
            'paginaAtiva'  => 'metodos_pagamento',
            'metodo'       => $metodo,
            'empresas'     => $empresas,
            'usuario'      => $usuario,
        ]);
    }

    /**
     * Atualizar método de pagamento
     */
    public function atualizar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem atualizar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            definirFlash('erro', 'Permissão negada.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $metodo_existente = $this->model->buscarPorId($id);
        
        if (!$metodo_existente) {
            definirFlash('erro', 'Método de pagamento não encontrado.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        // Admin Empresa só pode editar métodos da sua empresa
        if ($usuario['perfil'] === 'admin_empresa' && $metodo_existente['empresa_id'] !== null && $metodo_existente['empresa_id'] != $usuario['empresa_id']) {
            definirFlash('erro', 'Permissão negada para editar este método.');
            header('Location: ' . URL_BASE . '/metodos-pagamento');
            exit;
        }
        
        $erros = [];
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        // Validações
        if (empty($dados['nome'])) {
            $erros['nome'] = 'Nome é obrigatório.';
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_form'] = $dados;
            header('Location: ' . URL_BASE . '/metodos-pagamento/editar?id=' . $id);
            exit;
        }
        
        // Preparar dados
        $dados_processados = [
            'nome' => trim($dados['nome']),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'ordem' => !empty($dados['ordem']) ? (int)$dados['ordem'] : 0,
            'icone' => 'fas fa-wallet'
        ];
        
        if ($this->model->atualizar($id, $dados_processados)) {
            definirFlash('sucesso', 'Método de pagamento atualizado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao atualizar método de pagamento.');
        }
        
        header('Location: ' . URL_BASE . '/metodos-pagamento');
        exit;
    }

    /**
     * Excluir método de pagamento
     */
    public function excluir(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem excluir
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            http_response_code(403);
            echo json_encode(['erro' => 'Permissão negada.']);
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $metodo = $this->model->buscarPorId($id);
        
        if (!$metodo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Método não encontrado.']);
            exit;
        }
        
        // Admin Empresa só pode excluir métodos da sua empresa
        if ($usuario['perfil'] === 'admin_empresa' && $metodo['empresa_id'] !== null && $metodo['empresa_id'] != $usuario['empresa_id']) {
            http_response_code(403);
            echo json_encode(['erro' => 'Permissão negada para excluir este método.']);
            exit;
        }
        
        // Verificar se está em uso
        if ($this->model->estaEmUso($id)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Não é possível excluir. Este método está sendo usado em transações.']);
            exit;
        }
        
        if ($this->model->excluir($id)) {
            echo json_encode(['sucesso' => 'Método de pagamento excluído com sucesso!']);
        } else {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir método de pagamento.']);
        }
        exit;
    }
}
