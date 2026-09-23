<?php
/**
 * Controller de Métodos de Pagamento
 * Gerencia as requisições relacionadas a métodos de pagamento
 */

require_once CAMINHO_RAIZ . '/models/MetodoPagamento.php';

class MetodosPagamentoController {
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
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /dashboard');
            exit;
        }
        
        $empresa_id = null;
        if ($usuario['perfil'] === 'admin_empresa') {
            $empresa_id = (int)$usuario['empresa_id'];
        }
        
        $metodos = $this->model->listar($empresa_id, false);
        
        include __DIR__ . '/../views/metodos_pagamento/index.php';
    }

    /**
     * Mostrar formulário de criação
     */
    public function criar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        include __DIR__ . '/../views/metodos_pagamento/formulario.php';
    }

    /**
     * Salvar novo método de pagamento
     */
    public function salvar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /metodos-pagamento');
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
            header('Location: /metodos-pagamento/criar');
            exit;
        }
        
        // Preparar dados
        $dados_processados = [
            'nome' => trim($dados['nome']),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'ordem' => !empty($dados['ordem']) ? (int)$dados['ordem'] : 0,
            'icone' => trim($dados['icone'] ?? '')
        ];
        
        // Admin Empresa vincula à sua empresa, Super Admin cria padrão (null)
        if ($usuario['perfil'] === 'admin_empresa') {
            $dados_processados['empresa_id'] = (int)$usuario['empresa_id'];
        } else {
            $dados_processados['empresa_id'] = !empty($dados['empresa_id']) ? (int)$dados['empresa_id'] : null;
        }
        
        if ($this->model->criar($dados_processados)) {
            $_SESSION['sucesso'] = 'Método de pagamento cadastrado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar método de pagamento.';
        }
        
        header('Location: /metodos-pagamento');
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
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        $id = (int)($_GET['id'] ?? 0);
        $metodo = $this->model->buscarPorId($id);
        
        if (!$metodo) {
            $_SESSION['erro'] = 'Método de pagamento não encontrado.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        // Admin Empresa só pode editar métodos da sua empresa
        if ($usuario['perfil'] === 'admin_empresa' && $metodo['empresa_id'] !== null && $metodo['empresa_id'] != $usuario['empresa_id']) {
            $_SESSION['erro'] = 'Permissão negada para editar este método.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        include __DIR__ . '/../views/metodos_pagamento/formulario.php';
    }

    /**
     * Atualizar método de pagamento
     */
    public function atualizar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas Super Admin e Admin Empresa podem atualizar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $metodo_existente = $this->model->buscarPorId($id);
        
        if (!$metodo_existente) {
            $_SESSION['erro'] = 'Método de pagamento não encontrado.';
            header('Location: /metodos-pagamento');
            exit;
        }
        
        // Admin Empresa só pode editar métodos da sua empresa
        if ($usuario['perfil'] === 'admin_empresa' && $metodo_existente['empresa_id'] !== null && $metodo_existente['empresa_id'] != $usuario['empresa_id']) {
            $_SESSION['erro'] = 'Permissão negada para editar este método.';
            header('Location: /metodos-pagamento');
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
            header('Location: /metodos-pagamento/editar?id=' . $id);
            exit;
        }
        
        // Preparar dados
        $dados_processados = [
            'nome' => trim($dados['nome']),
            'descricao' => trim($dados['descricao'] ?? ''),
            'ativo' => isset($dados['ativo']) ? 1 : 0,
            'ordem' => !empty($dados['ordem']) ? (int)$dados['ordem'] : 0,
            'icone' => trim($dados['icone'] ?? '')
        ];
        
        if ($this->model->atualizar($id, $dados_processados)) {
            $_SESSION['sucesso'] = 'Método de pagamento atualizado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar método de pagamento.';
        }
        
        header('Location: /metodos-pagamento');
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
