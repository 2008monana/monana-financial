<?php
/**
 * Controller de Funcionários
 * Gerencia as requisições relacionadas a funcionários
 */

// Garantir que o Model seja carregado
require_once CAMINHO_RAIZ . '/models/Funcionario.php';
require_once CAMINHO_RAIZ . '/helpers/Autenticacao.php';

class FuncionariosController {
    private Funcionario $model;
    private Autenticacao $auth;

    public function __construct() {
        $this->model = new Funcionario();
        $this->auth = new Autenticacao();
        $this->auth->verificarLogin();
    }

    /**
     * Listar funcionários
     */
    public function index(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        // Super Admin sem empresa vinculada vê todas as empresas
        $empresa_id = ($usuario['perfil'] === 'super_admin' && empty($usuario['empresa_id']))
            ? null
            : (int) ($usuario['empresa_id'] ?? 0);
        $filial_id = $_GET['filial_id'] ?? null;
        $busca = $_GET['busca'] ?? '';

        // Utilizadores não-super admin devem ter uma empresa vinculada
        if ($empresa_id === 0 && $usuario['perfil'] !== 'super_admin') {
            $_SESSION['erro'] = 'Nenhuma empresa associada ao seu utilizador. Contacte o administrador.';
            header('Location: /dashboard');
            exit;
        }
        
        $funcionarios = $this->model->listar($empresa_id, $filial_id ? (int)$filial_id : null, $busca);
        $filiais = $this->model->buscarFiliais($empresa_id);
        $total = $this->model->contar($empresa_id, $filial_id ? (int)$filial_id : null);
        
        include __DIR__ . '/../views/funcionarios/index.php';
    }

    /**
     * Mostrar formulário de criação
     */
    public function criar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas admin da empresa e super admin podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $empresa_id = $usuario['empresa_id'];
        $filiais = $this->model->buscarFiliais($empresa_id);
        
        include __DIR__ . '/../views/funcionarios/formulario.php';
    }

    /**
     * Salvar novo funcionário
     */
    public function salvar(): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas admin da empresa e super admin podem criar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $erros = [];
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        // Validações
        if (empty($dados['nome'])) {
            $erros['nome'] = 'Nome é obrigatório.';
        }
        
        if (empty($dados['filial_id'])) {
            $erros['filial_id'] = 'Selecione uma filial.';
        }
        
        if (!empty($dados['email']) && !$this->validarEmail($dados['email'])) {
            $erros['email'] = 'E-mail inválido.';
        }
        
        // Upload de foto
        $foto_path = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $foto_path = $this->uploadFoto($_FILES['foto']);
            if (!$foto_path) {
                $erros['foto'] = 'Erro ao fazer upload da foto.';
            }
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_form'] = $dados;
            header('Location: /funcionarios/criar');
            exit;
        }
        
        $dados['empresa_id'] = $usuario['empresa_id'];
        $dados['foto'] = $foto_path;
        
        if ($this->model->criar($dados)) {
            $_SESSION['sucesso'] = 'Funcionário cadastrado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao cadastrar funcionário.';
        }
        
        header('Location: /funcionarios');
        exit;
    }

    /**
     * Mostrar formulário de edição
     */
    public function editar(int $id): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas admin da empresa e super admin podem editar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $funcionario = $this->model->buscarPorId($id);
        
        if (!$funcionario) {
            $_SESSION['erro'] = 'Funcionário não encontrado.';
            header('Location: /funcionarios');
            exit;
        }
        
        // Verificar permissão por empresa
        if ($usuario['perfil'] !== 'super_admin' && $funcionario['empresa_id'] !== $usuario['empresa_id']) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $empresa_id = $usuario['empresa_id'];
        $filiais = $this->model->buscarFiliais($empresa_id);
        
        include __DIR__ . '/../views/funcionarios/formulario.php';
    }

    /**
     * Atualizar funcionário
     */
    public function atualizar(int $id): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas admin da empresa e super admin podem editar
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $funcionario = $this->model->buscarPorId($id);
        
        if (!$funcionario) {
            $_SESSION['erro'] = 'Funcionário não encontrado.';
            header('Location: /funcionarios');
            exit;
        }
        
        // Verificar permissão por empresa
        if ($usuario['perfil'] !== 'super_admin' && $funcionario['empresa_id'] !== $usuario['empresa_id']) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /funcionarios');
            exit;
        }
        
        $erros = [];
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        // Validações
        if (empty($dados['nome'])) {
            $erros['nome'] = 'Nome é obrigatório.';
        }
        
        if (empty($dados['filial_id'])) {
            $erros['filial_id'] = 'Selecione uma filial.';
        }
        
        if (!empty($dados['email']) && !$this->validarEmail($dados['email'])) {
            $erros['email'] = 'E-mail inválido.';
        }
        
        // Upload de foto
        $foto_path = $funcionario['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            // Remover foto antiga se existir
            if ($foto_path && file_exists(__DIR__ . '/../public/' . $foto_path)) {
                unlink(__DIR__ . '/../public/' . $foto_path);
            }
            
            $foto_path = $this->uploadFoto($_FILES['foto']);
            if (!$foto_path) {
                $erros['foto'] = 'Erro ao fazer upload da foto.';
            }
        }
        
        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_form'] = $dados;
            header("Location: /funcionarios/editar/$id");
            exit;
        }
        
        $dados['foto'] = $foto_path;
        
        if ($this->model->atualizar($id, $dados)) {
            $_SESSION['sucesso'] = 'Funcionário atualizado com sucesso!';
        } else {
            $_SESSION['erro'] = 'Erro ao atualizar funcionário.';
        }
        
        header("Location: /funcionarios/editar/$id");
        exit;
    }

    /**
     * Excluir funcionário
     */
    public function excluir(int $id): void {
        $this->auth->verificarLogin();
        
        $usuario = $this->auth->usuario();
        
        // Apenas admin da empresa e super admin podem excluir
        if (!in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])) {
            echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
            exit;
        }
        
        $funcionario = $this->model->buscarPorId($id);
        
        if (!$funcionario) {
            echo json_encode(['success' => false, 'message' => 'Funcionário não encontrado.']);
            exit;
        }
        
        // Verificar permissão por empresa
        if ($usuario['perfil'] !== 'super_admin' && $funcionario['empresa_id'] !== $usuario['empresa_id']) {
            echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
            exit;
        }
        
        // Remover foto se existir
        if ($funcionario['foto'] && file_exists(__DIR__ . '/../public/' . $funcionario['foto'])) {
            unlink(__DIR__ . '/../public/' . $funcionario['foto']);
        }
        
        if ($this->model->excluir($id)) {
            echo json_encode(['success' => true, 'message' => 'Funcionário excluído com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir funcionário.']);
        }
        exit;
    }

    /**
     * Validar e-mail
     */
    private function validarEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Upload de foto
     */
    private function uploadFoto(array $file): ?string {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            return null;
        }
        
        if ($file['size'] > $max_size) {
            return null;
        }
        
        $upload_dir = __DIR__ . '/../public/uploads/funcionarios/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid('func_') . '.' . $extensao;
        $caminho_completo = $upload_dir . $novo_nome;
        
        if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
            return 'uploads/funcionarios/' . $novo_nome;
        }
        
        return null;
    }
}
