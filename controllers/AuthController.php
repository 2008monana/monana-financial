<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';

/**
 * AuthController
 * Responsável pelo login, logout e recuperação de senha.
 */
class AuthController extends Controller
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /** Exibe a tela de login */
    public function login(): void
    {
        // Se já existe sessão activa, vai directo ao dashboard
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirecionar('dashboard/index');
        }

        $this->renderizarSemLayout('auth/login');
    }

    /**
     * Recebe o pedido de autenticação via AJAX (fetch) e responde em JSON.
     * O frontend trata a exibição do ícone de sucesso/erro.
     */
    public function autenticar(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $this->responderJson([
                'sucesso' => false,
                'mensagem' => 'Preencha o utilizador e a palavra-passe.',
            ], 422);
        }

        $usuario = $this->usuarioModel->encontrarPorEmail($email);

        if (!$usuario || !$usuario['ativo']) {
            $this->responderJson([
                'sucesso' => false,
                'mensagem' => 'Credenciais inválidas. Tente novamente.',
            ], 401);
        }

        if (!$this->usuarioModel->verificarSenha($senha, $usuario['senha_hash'])) {
            $this->responderJson([
                'sucesso' => false,
                'mensagem' => 'Credenciais inválidas. Tente novamente.',
            ], 401);
        }

        // Autenticação bem-sucedida — inicia a sessão
        $_SESSION['usuario_id']      = $usuario['id'];
        $_SESSION['usuario_nome']    = $usuario['nome'];
        $_SESSION['usuario_email']   = $usuario['email'];
        $_SESSION['usuario_perfil']  = $usuario['perfil'];
        $_SESSION['empresa_id']      = $usuario['empresa_id'];

        $this->usuarioModel->atualizarUltimoLogin((int) $usuario['id']);

        $this->responderJson([
            'sucesso'    => true,
            'mensagem'   => 'Bem-vindo, ' . $usuario['nome'] . '!',
            'redirecionar' => URL_BASE . '/dashboard/index',
        ]);
    }

    /** Termina a sessão */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirecionar('auth/login');
    }

    /** Exibe a tela de "esqueci a senha" */
    public function esqueciSenha(): void
    {
        $this->renderizarSemLayout('auth/esqueci-senha');
    }

    /**
     * Gera um token de redefinição e envia por e-mail.
     * (Envio de e-mail será ligado ao Mailer numa fase seguinte.)
     */
    public function enviarLinkRedefinicao(): void
    {
        $email = trim($_POST['email'] ?? '');
        $usuario = $this->usuarioModel->encontrarPorEmail($email);

        // Por segurança, a resposta é sempre a mesma, exista ou não o e-mail.
        $this->responderJson([
            'sucesso'  => true,
            'mensagem' => 'Se o e-mail existir na nossa base de dados, enviaremos as instruções de recuperação.',
        ]);
    }

    /** Exibe a tela para definir nova senha (a partir do link recebido por e-mail) */
    public function redefinirSenha(string $token = ''): void
    {
        $this->renderizarSemLayout('auth/redefinir-senha', ['token' => $token]);
    }

    /** Grava a nova senha após validar o token */
    public function salvarNovaSenha(): void
    {
        // Implementação completa (validação de token/expiração) na fase de
        // Recuperação de Senha + Mailer.
        $this->responderJson(['sucesso' => true, 'mensagem' => 'Senha redefinida com sucesso.']);
    }
}
