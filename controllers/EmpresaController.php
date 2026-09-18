<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/models/UsuarioFilial.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * EmpresaController
 * Gestão de empresas — funcionalidade exclusiva do Super Administrador,
 * com criação automática do Administrador da Empresa.
 */
class EmpresaController extends Controller
{
    private Empresa $empresaModel;
    private Filial $filialModel;
    private Usuario $usuarioModel;
    private UsuarioFilial $usuarioFilialModel;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        $this->empresaModel = new Empresa();
        $this->filialModel = new Filial();
        $this->usuarioModel = new Usuario();
        $this->usuarioFilialModel = new UsuarioFilial();
    }

    public function index(): void
    {
        $empresas = $this->empresaModel->todos('nome');

        // Conta filiais de cada empresa
        foreach ($empresas as &$empresa) {
            $empresa['total_filiais'] = count($this->filialModel->porEmpresa((int) $empresa['id']));
            $empresa['total_usuarios'] = count($this->usuarioModel->porEmpresa((int) $empresa['id']));
        }
        unset($empresa);

        $this->renderizar('empresas/index', [
            'tituloPagina' => 'Empresas',
            'paginaAtiva'  => 'empresas',
            'empresas'     => $empresas,
        ]);
    }

    public function criar(): void
    {
        $this->renderizar('empresas/form', [
            'tituloPagina' => 'Nova Empresa',
            'paginaAtiva'  => 'empresas',
            'empresa'      => null,
            'erros'        => [],
        ]);
    }

    public function gravar(): void
    {
        // Validar dados da empresa
        $dados = $this->dadosValidados();

        // Validar dados do administrador (apenas na criação)
        $adminDados = $this->validarAdmin();

        // Combinar erros
        $erros = array_merge($dados['erros'], $adminDados['erros']);

        if (!empty($erros)) {
            $this->renderizar('empresas/form', [
                'tituloPagina' => 'Nova Empresa',
                'paginaAtiva'  => 'empresas',
                'empresa'      => $_POST,
                'erros'        => $erros,
            ]);
            return;
        }

        // =============================================
        // 1. CRIAR A EMPRESA
        // =============================================
        $empresaId = $this->empresaModel->inserir($dados['campos']);

        if (!$empresaId) {
            definirFlash('erro', 'Erro ao criar a empresa. Tente novamente.');
            $this->redirecionar('empresas/criar');
        }

        // =============================================
        // 2. CRIAR O ADMINISTRADOR DA EMPRESA
        // =============================================
        // Usar senha fornecida ou gerar automaticamente
        $senhaAdmin = trim($_POST['admin_senha'] ?? '');
        $senhaGerada = false;

        if (empty($senhaAdmin)) {
            $senhaAdmin = $this->usuarioModel->gerarSenhaAleatoria();
            $senhaGerada = true;
        }

        $adminId = $this->usuarioModel->inserir([
            'empresa_id' => $empresaId,
            'nome' => trim($_POST['admin_nome']),
            'email' => trim($_POST['admin_email']),
            'senha_hash' => password_hash($senhaAdmin, PASSWORD_BCRYPT),
            'perfil' => 'admin_empresa',
            'telefone' => trim($_POST['admin_telefone'] ?? ''),
            'primeiro_acesso' => 1,
            'ativo' => 1,
        ]);

        if (!$adminId) {
            // Se falhar a criar o admin, apagar a empresa (rollback)
            $this->empresaModel->eliminar($empresaId);
            definirFlash('erro', 'Erro ao criar o administrador. Operação cancelada.');
            $this->redirecionar('empresas/criar');
        }

        // =============================================
        // 3. ENVIAR EMAIL COM CREDENCIAIS (OPCIONAL)
        // =============================================
        // (new Mailer())->enviarBoasVindas(
        //     $_POST['admin_email'],
        //     $_POST['admin_nome'],
        //     $senhaAdmin,
        //     $_POST['nome']
        // );

        // =============================================
        // 4. MENSAGEM DE SUCESSO
        // =============================================
        $msgSenha = $senhaGerada ? 'Senha gerada automaticamente: "' . $senhaAdmin . '"' : 'Senha definida manualmente.';
        
        definirFlash('sucesso', 
            '✅ Empresa criada com sucesso!<br>' .
            '👤 Administrador: ' . htmlspecialchars($_POST['admin_nome']) . '<br>' .
            '📧 E-mail: ' . htmlspecialchars($_POST['admin_email']) . '<br>' .
            '🔑 ' . $msgSenha
        );

        $this->redirecionar('empresas/index');
    }

    /**
     * Validar dados do Administrador (apenas na criação)
     */
    private function validarAdmin(): array
    {
        $erros = [];
        $nome = trim($_POST['admin_nome'] ?? '');
        $email = trim($_POST['admin_email'] ?? '');
        $senha = trim($_POST['admin_senha'] ?? '');
        $senhaConfirmar = trim($_POST['admin_senha_confirmar'] ?? '');

        // Nome do admin
        if ($nome === '') {
            $erros['admin_nome'] = 'O nome do administrador é obrigatório.';
        }

        // Email do admin
        if ($email === '') {
            $erros['admin_email'] = 'O e-mail do administrador é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['admin_email'] = 'Introduza um e-mail válido.';
        } else {
            // Verificar se o email já existe
            $existente = $this->usuarioModel->encontrarPorEmail($email);
            if ($existente) {
                $erros['admin_email'] = 'Este e-mail já está em uso por outro utilizador.';
            }
        }

        // Senha (apenas se for fornecida)
        if (!empty($senha) || !empty($senhaConfirmar)) {
            if (strlen($senha) < 8) {
                $erros['admin_senha'] = 'A senha deve ter pelo menos 8 caracteres.';
            }
            if ($senha !== $senhaConfirmar) {
                $erros['admin_senha_confirmar'] = 'As senhas não coincidem.';
            }
        }

        return ['erros' => $erros];
    }

    private function dadosValidados(): array
    {
        $erros = [];
        $nome = trim($_POST['nome'] ?? '');
        $nif = trim($_POST['nif'] ?? '');
        $email = trim($_POST['email_contacto'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome da empresa é obrigatório.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email_contacto'] = 'Introduza um e-mail válido.';
        }

        return [
            'erros'  => $erros,
            'campos' => [
                'nome'           => $nome,
                'nif'            => $nif ?: null,
                'email_contacto' => $email ?: null,
                'telefone'       => $telefone ?: null,
                'endereco'       => $endereco ?: null,
            ],
        ];
    }

    public function editar(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if (!$empresa) {
            definirFlash('erro', 'Empresa não encontrada.');
            $this->redirecionar('empresas/index');
        }

        $this->renderizar('empresas/form', [
            'tituloPagina' => 'Editar Empresa',
            'paginaAtiva'  => 'empresas',
            'empresa'      => $empresa,
            'erros'        => [],
        ]);
    }

    public function atualizar(string $id): void
    {
        $dados = $this->dadosValidados();

        if (!empty($dados['erros'])) {
            $this->renderizar('empresas/form', [
                'tituloPagina' => 'Editar Empresa',
                'paginaAtiva'  => 'empresas',
                'empresa'      => array_merge(['id' => $id], $_POST),
                'erros'        => $dados['erros'],
            ]);
            return;
        }

        $this->empresaModel->atualizar((int) $id, $dados['campos']);
        definirFlash('sucesso', 'Empresa atualizada com sucesso.');
        $this->redirecionar('empresas/index');
    }

    public function alternarEstado(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if ($empresa) {
            $this->empresaModel->atualizar((int) $id, ['ativa' => $empresa['ativa'] ? 0 : 1]);
            definirFlash('sucesso', $empresa['ativa'] ? 'Empresa desativada.' : 'Empresa reativada.');
        }

        $this->redirecionar('empresas/index');
    }
}