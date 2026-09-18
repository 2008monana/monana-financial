<?php
/**
 * Controlador de Usuários
 */
class UsuarioController extends Controller {
    private $usuarioModel;
    private $perfilModel;

    public function __construct() {
        // Verificar autenticação
        if (!SessaoHelper::estaLogado()) {
            $this->redirecionar('/login');
            return;
        }

        $this->usuarioModel = new Usuario();
        $this->perfilModel = new PerfilUsuario();
    }

    /**
     * Listar usuários
     */
    public function index() {
        // Verificar permissão
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->setFlash('erro', 'Sem permissão para aceder a esta página.');
            $this->redirecionar('/dashboard');
            return;
        }

        $empresaId = SessaoHelper::getUsuarioEmpresaId();
        $usuarios = $this->usuarioModel->buscarPorEmpresa($empresaId);

        $this->renderizar('usuarios/index', [
            'usuarios' => $usuarios,
            'perfil_usuario' => SessaoHelper::getUsuarioPerfil()
        ]);
    }

    /**
     * Página de criação de usuário
     */
    public function criar() {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->setFlash('erro', 'Sem permissão para aceder a esta página.');
            $this->redirecionar('/dashboard');
            return;
        }

        // Buscar filiais da empresa
        $filialModel = new Filial();
        $empresaId = SessaoHelper::getUsuarioEmpresaId();
        $filiais = $filialModel->buscarPorEmpresa($empresaId);

        $this->renderizar('usuarios/criar', [
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            'filiais' => $filiais,
            'erro' => $this->getFlash('erro'),
            'sucesso' => $this->getFlash('sucesso')
        ]);
    }

    /**
     * Armazenar novo usuário
     */
    public function armazenar() {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->json(['erro' => 'Sem permissão'], 403);
            return;
        }

        // Validar CSRF
        if (!$this->validarCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('/usuarios/criar');
            return;
        }

        $dados = [
            'empresa_id' => SessaoHelper::getUsuarioEmpresaId(),
            'nome' => SegurancaHelper::sanitizar($_POST['nome'] ?? ''),
            'email' => SegurancaHelper::sanitizar($_POST['email'] ?? ''),
            'perfil' => $_POST['perfil'] ?? 'internal_user',
            'status' => 'pending'
        ];

        // Validar campos obrigatórios
        if (empty($dados['nome']) || empty($dados['email'])) {
            $this->setFlash('erro', 'Nome e email são obrigatórios.');
            $this->redirecionar('/usuarios/criar');
            return;
        }

        // Validar email
        if (!SegurancaHelper::validarEmail($dados['email'])) {
            $this->setFlash('erro', 'Email inválido.');
            $this->redirecionar('/usuarios/criar');
            return;
        }

        // Verificar se email já existe
        if ($this->usuarioModel->emailExiste($dados['email'])) {
            $this->setFlash('erro', 'Este email já está em uso.');
            $this->redirecionar('/usuarios/criar');
            return;
        }

        // Gerar senha aleatória
        $senha = SegurancaHelper::gerarSenha(10);
        $dados['senha'] = SegurancaHelper::hashSenha($senha);

        // Criar usuário
        $usuarioId = $this->usuarioModel->criar($dados);

        if (!$usuarioId) {
            $this->setFlash('erro', 'Erro ao criar usuário. Tente novamente.');
            $this->redirecionar('/usuarios/criar');
            return;
        }

        // Atribuir filiais
        $filiaisIds = $_POST['filiais'] ?? [];
        if (!empty($filiaisIds)) {
            $this->usuarioModel->atribuirFiliais($usuarioId, $filiaisIds);
        }

        // Atribuir permissões padrão
        $this->atribuirPermissoesPadrao($usuarioId, $dados['perfil']);

        // Criar perfil
        $this->perfilModel->salvar($usuarioId, [
            'cargo' => $_POST['cargo'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'departamento' => $_POST['departamento'] ?? null
        ]);

        // Enviar email com credenciais
        $empresaModel = new Empresa();
        $empresa = $empresaModel->findById($dados['empresa_id']);
        $nomeEmpresa = $empresa ? $empresa['nome'] : 'Sistema';

        $mailer = new Mailer();
        $resultado = $mailer->enviarUsuarioCriado(
            $dados['email'],
            $dados['nome'],
            $senha,
            $nomeEmpresa
        );

        if (!$resultado['sucesso']) {
            error_log("Erro ao enviar email para {$dados['email']}: " . ($resultado['erro'] ?? ''));
        }

        $this->setFlash('sucesso', 'Usuário criado com sucesso! As credenciais foram enviadas por email.');
        $this->redirecionar('/usuarios');
    }

    /**
     * Atribuir permissões padrão
     */
    private function atribuirPermissoesPadrao($usuarioId, $perfil) {
        $permissoes = [];

        switch ($perfil) {
            case 'company_admin':
                $permissoes = [
                    'can_manage_users' => true,
                    'can_manage_branches' => true,
                    'can_manage_categories' => true,
                    'can_import_excel' => true,
                    'can_export_reports' => true,
                    'can_view_reports' => true,
                    'can_create_transactions' => true,
                    'can_edit_transactions' => true,
                    'can_delete_transactions' => true
                ];
                break;

            case 'internal_user':
                $permissoes = [
                    'can_create_transactions' => true,
                    'can_edit_transactions' => true,
                    'can_export_reports' => true,
                    'can_view_reports' => true
                ];
                break;

            case 'viewer':
                $permissoes = [
                    'can_view_reports' => true,
                    'can_export_reports' => true
                ];
                break;
        }

        if (!empty($permissoes)) {
            $this->usuarioModel->atribuirPermissoes($usuarioId, $permissoes);
        }
    }

    /**
     * Página de edição de usuário
     */
    public function editar($id) {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->setFlash('erro', 'Sem permissão.');
            $this->redirecionar('/dashboard');
            return;
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $this->setFlash('erro', 'Usuário não encontrado.');
            $this->redirecionar('/usuarios');
            return;
        }

        $filialModel = new Filial();
        $empresaId = SessaoHelper::getUsuarioEmpresaId();
        $filiais = $filialModel->buscarPorEmpresa($empresaId);
        
        $usuarioFiliais = $this->usuarioModel->buscarFiliais($id);
        $usuarioFiliaisIds = array_column($usuarioFiliais, 'id');

        $perfil = $this->perfilModel->buscarPorUsuario($id);

        $this->renderizar('usuarios/editar', [
            'usuario' => $usuario,
            'perfil_usuario' => $perfil,
            'filiais' => $filiais,
            'usuario_filiais_ids' => $usuarioFiliaisIds,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            'erro' => $this->getFlash('erro'),
            'sucesso' => $this->getFlash('sucesso')
        ]);
    }

    /**
     * Atualizar usuário
     */
    public function atualizar($id) {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->json(['erro' => 'Sem permissão'], 403);
            return;
        }

        if (!$this->validarCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('/usuarios/' . $id . '/editar');
            return;
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $this->setFlash('erro', 'Usuário não encontrado.');
            $this->redirecionar('/usuarios');
            return;
        }

        $dados = [
            'nome' => SegurancaHelper::sanitizar($_POST['nome'] ?? ''),
            'perfil' => $_POST['perfil'] ?? $usuario['perfil'],
            'status' => $_POST['status'] ?? $usuario['status']
        ];

        if (empty($dados['nome'])) {
            $this->setFlash('erro', 'Nome é obrigatório.');
            $this->redirecionar('/usuarios/' . $id . '/editar');
            return;
        }

        // Verificar email se foi alterado
        $email = SegurancaHelper::sanitizar($_POST['email'] ?? '');
        if ($email && $email !== $usuario['email']) {
            if (!SegurancaHelper::validarEmail($email)) {
                $this->setFlash('erro', 'Email inválido.');
                $this->redirecionar('/usuarios/' . $id . '/editar');
                return;
            }
            if ($this->usuarioModel->emailExiste($email, $id)) {
                $this->setFlash('erro', 'Este email já está em uso.');
                $this->redirecionar('/usuarios/' . $id . '/editar');
                return;
            }
            $dados['email'] = $email;
        }

        // Atualizar usuário
        $atualizado = $this->usuarioModel->atualizar($id, $dados);

        if (!$atualizado) {
            $this->setFlash('erro', 'Erro ao atualizar usuário.');
            $this->redirecionar('/usuarios/' . $id . '/editar');
            return;
        }

        // Atualizar filiais
        $filiaisIds = $_POST['filiais'] ?? [];
        $this->usuarioModel->atribuirFiliais($id, $filiaisIds);

        // Atualizar perfil
        $this->perfilModel->salvar($id, [
            'cargo' => $_POST['cargo'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'departamento' => $_POST['departamento'] ?? null,
            'biografia' => $_POST['biografia'] ?? null
        ]);

        $this->setFlash('sucesso', 'Usuário atualizado com sucesso!');
        $this->redirecionar('/usuarios');
    }

    /**
     * Eliminar usuário
     */
    public function eliminar($id) {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->json(['erro' => 'Sem permissão'], 403);
            return;
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $this->json(['erro' => 'Usuário não encontrado'], 404);
            return;
        }

        // Não permitir eliminar o próprio usuário
        if ($id == SessaoHelper::getUsuarioId()) {
            $this->setFlash('erro', 'Não pode eliminar sua própria conta.');
            $this->redirecionar('/usuarios');
            return;
        }

        $eliminado = $this->usuarioModel->eliminar($id);

        if (!$eliminado) {
            $this->setFlash('erro', 'Erro ao eliminar usuário.');
        } else {
            $this->setFlash('sucesso', 'Usuário eliminado com sucesso!');
        }

        $this->redirecionar('/usuarios');
    }

    /**
     * Página de permissões do usuário
     */
    public function permissoes($id) {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->setFlash('erro', 'Sem permissão.');
            $this->redirecionar('/dashboard');
            return;
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $this->setFlash('erro', 'Usuário não encontrado.');
            $this->redirecionar('/usuarios');
            return;
        }

        $permissoes = $this->usuarioModel->buscarPermissoes($id);
        $permissoesArray = [];
        foreach ($permissoes as $p) {
            $permissoesArray[$p['nome_permissao']] = (bool)$p['valor'];
        }

        // Lista de todas as permissões disponíveis
        $todasPermissoes = [
            'can_manage_branches' => 'Gerir Filiais',
            'can_manage_categories' => 'Gerir Categorias',
            'can_create_transactions' => 'Criar Lançamentos',
            'can_edit_transactions' => 'Editar Lançamentos',
            'can_delete_transactions' => 'Eliminar Lançamentos',
            'can_view_reports' => 'Ver Relatórios',
            'can_export_reports' => 'Exportar Relatórios',
            'can_import_excel' => 'Importar Excel'
        ];

        $this->renderizar('usuarios/permissoes', [
            'usuario' => $usuario,
            'permissoes' => $permissoesArray,
            'todas_permissoes' => $todasPermissoes,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            'erro' => $this->getFlash('erro'),
            'sucesso' => $this->getFlash('sucesso')
        ]);
    }

    /**
     * Atualizar permissões do usuário
     */
    public function atualizarPermissoes($id) {
        if (!AuthMiddleware::temPermissao('can_manage_users')) {
            $this->json(['erro' => 'Sem permissão'], 403);
            return;
        }

        if (!$this->validarCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('/usuarios/' . $id . '/permissoes');
            return;
        }

        $usuario = $this->usuarioModel->findById($id);
        if (!$usuario) {
            $this->setFlash('erro', 'Usuário não encontrado.');
            $this->redirecionar('/usuarios');
            return;
        }

        // Coletar permissões do formulário
        $permissoes = [];
        $todasPermissoes = [
            'can_manage_branches',
            'can_manage_categories',
            'can_create_transactions',
            'can_edit_transactions',
            'can_delete_transactions',
            'can_view_reports',
            'can_export_reports',
            'can_import_excel'
        ];

        foreach ($todasPermissoes as $permissao) {
            $permissoes[$permissao] = isset($_POST[$permissao]) && $_POST[$permissao] == '1';
        }

        $atribuido = $this->usuarioModel->atribuirPermissoes($id, $permissoes);

        if (!$atribuido) {
            $this->setFlash('erro', 'Erro ao atualizar permissões.');
        } else {
            $this->setFlash('sucesso', 'Permissões atualizadas com sucesso!');
        }

        $this->redirecionar('/usuarios');
    }

    /**
     * Página de perfil do usuário logado
     */
    public function perfil() {
        $id = SessaoHelper::getUsuarioId();
        $usuario = $this->usuarioModel->buscarComPermissoes($id);
        $perfil = $this->perfilModel->buscarPorUsuario($id);

        $this->renderizar('perfil/index', [
            'usuario' => $usuario,
            'perfil' => $perfil
        ]);
    }
}