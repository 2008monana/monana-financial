<?php
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/core/Mailer.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/models/RedefinicaoSenha.php';
require_once CAMINHO_RAIZ . '/models/TentativaLogin.php';

/**
 * AuthController
 * Responsável pelo login, logout e recuperação de senha.
 */
class AuthController extends Controller
{
    private Usuario $usuarioModel;
    private TentativaLogin $tentativasModel;
    private RedefinicaoSenha $redefinicaoModel;

    public const MAX_TENTATIVAS = 10;
    public const MINUTOS_BLOQUEIO = 30;

    public function __construct()
    {
        $this->usuarioModel     = new Usuario();
        $this->tentativasModel  = new TentativaLogin();
        $this->redefinicaoModel = new RedefinicaoSenha();
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

        // ---- Controlo de tentativas por email + dispositivo ----
        $dispositivo = TentativaLogin::identificadorDispositivo();
        $bloqueio = null;
        try {
            $bloqueio = $this->tentativasModel->verificarBloqueio($email, $dispositivo);
        } catch (Throwable $e) {
            // tabela indisponível — não bloqueia o login
        }

        if ($bloqueio) {
            AuditoriaHelper::registar('login_bloqueado', 'usuarios', null, null, ['email_tentado' => $email], 'alta', 'Dispositivo bloqueado por excesso de tentativas');
            $this->json([
                'sucesso'  => false,
                'bloqueado' => true,
                'minutos_restantes' => $bloqueio['minutos_restantes'],
                'mensagem' => 'Demasiadas tentativas falhadas a partir deste dispositivo. Por favor tente novamente mais tarde (dentro de aproximadamente ' . $bloqueio['minutos_restantes'] . ' minuto(s)).',
            ], 429);
        }

        if ($email === '' || $senha === '') {
            AuditoriaHelper::registar('login_falhado', 'usuarios', null, null, ['email_tentado' => $email], 'alta', 'Credenciais incompletas');
            $this->json([
                'sucesso' => false,
                'mensagem' => 'Preencha o utilizador e a palavra-passe.',
            ], 422);
        }

        $usuario = $this->usuarioModel->encontrarPorEmail($email);

        if (!$usuario || !$usuario['ativo']) {
            $tentativas = $this->registarTentativaFalhada($email, $dispositivo);
            AuditoriaHelper::registar('login_falhado', 'usuarios', null, null, ['email_tentado' => $email], 'alta', 'Utilizador inexistente ou inativo');
            $this->json([
                'sucesso'    => false,
                'tentativas' => $tentativas,
                'restam'     => max(0, self::MAX_TENTATIVAS - $tentativas),
                'mensagem'   => 'Credenciais inválidas. Tente novamente.',
            ], 401);
        }

        if (!$this->usuarioModel->verificarSenha($senha, $usuario['senha_hash'])) {
            $tentativas = $this->registarTentativaFalhada($email, $dispositivo);
            AuditoriaHelper::registar('login_falhado', 'usuarios', (int) $usuario['id'], null, ['email_tentado' => $email], 'alta', 'Palavra-passe inválida');
            $this->json([
                'sucesso'    => false,
                'tentativas' => $tentativas,
                'restam'     => max(0, self::MAX_TENTATIVAS - $tentativas),
                'mensagem'   => 'Credenciais inválidas. Tente novamente.',
            ], 401);
        }

        // Autenticação bem-sucedida — inicia uma sessão nova para evitar fixação.
        try {
            $this->tentativasModel->limpar($email, $dispositivo);
        } catch (Throwable $e) {
            // silencioso
        }
        session_regenerate_id(true);
        $_SESSION['usuario_id']      = $usuario['id'];
        $_SESSION['usuario_nome']    = $usuario['nome'];
        $_SESSION['usuario_email']   = $usuario['email'];
        $_SESSION['usuario_perfil']  = $usuario['perfil'];
        $_SESSION['empresa_id']      = $usuario['empresa_id'];
        $_SESSION['sessao_iniciada_em'] = time();
        
        // Carregar nome da empresa ou definir valor padrão para super_admin
        if ($usuario['perfil'] === 'super_admin') {
            $_SESSION['empresa_nome'] = 'Todas as empresas';
        } elseif ($usuario['empresa_id']) {
            require_once CAMINHO_RAIZ . '/models/Empresa.php';
            $empresaModel = new Empresa();
            $empresa = $empresaModel->encontrarPorId((int) $usuario['empresa_id']);
            $_SESSION['empresa_nome'] = $empresa['nome'] ?? 'Empresa';
        } else {
            $_SESSION['empresa_nome'] = '';
        }

        $this->usuarioModel->atualizarUltimoLogin((int) $usuario['id']);
        AuditoriaHelper::registar('login_sucesso', 'usuarios', (int) $usuario['id'], null, ['email' => $usuario['email']], 'alta');

        $this->json([
            'sucesso'    => true,
            'mensagem'   => 'Bem-vindo, ' . $usuario['nome'] . '!',
            'redirecionar' => URL_BASE . '/dashboard/index',
        ]);
    }

    /** Termina a sessão */
    public function logout(): void
    {
        AuditoriaHelper::registar('logout', 'usuarios', (int) ($_SESSION['usuario_id'] ?? 0), null, ['duracao_segundos' => max(0, time() - (int) ($_SESSION['sessao_iniciada_em'] ?? time()))], 'media');
        $_SESSION = [];
        session_destroy();
        $this->redirecionar('auth/login');
    }

    /** Exibe a tela de "esqueci a senha" */
    public function esqueciSenha(): void
    {
        $this->renderizarSemLayout('auth/esqueci-senha', [
            'erro'        => $_SESSION['flash_auth_erro'] ?? null,
            'sucesso'     => $_SESSION['flash_auth_sucesso'] ?? null,
            'csrf_token'  => SegurancaHelper::gerarTokenCSRF(),
        ]);
        unset($_SESSION['flash_auth_erro'], $_SESSION['flash_auth_sucesso']);
    }

    /** Formulário clássico (POST) da página "esqueci a senha" */
    public function processarEsqueciSenha(): void
    {
        if (!$this->validarCsrf()) {
            $_SESSION['flash_auth_erro'] = 'Sessão expirada. Tente novamente.';
            $this->redirecionar('auth/esqueciSenha');
        }

        $email = trim($_POST['email'] ?? '');
        $usuario = $email !== '' ? $this->usuarioModel->encontrarPorEmail($email) : false;

        if ($usuario) {
            $envio = $this->gerarEEnviarToken((int) $usuario['id'], $usuario['email'], $usuario['nome']);
            AuditoriaHelper::registar('senha_recuperacao_solicitada', 'usuarios', (int) $usuario['id'], null, ['email' => $email], 'alta');
            if ($envio['sucesso']) {
                $_SESSION['flash_auth_sucesso'] = 'Foi enviado um link de recuperação para o seu e-mail. Verifique a sua caixa de entrada (e o spam).';
            } else {
                $_SESSION['flash_auth_erro'] = 'Não foi possível enviar o e-mail de recuperação agora (' . $envio['erro'] . '). Tente novamente mais tarde.';
            }
        } else {
            AuditoriaHelper::registar('senha_recuperacao_solicitada', 'usuarios', null, null, ['email' => $email], 'alta', 'Email inexistente');
            // Por segurança, não revelamos se o email existe ou não.
            $_SESSION['flash_auth_sucesso'] = 'Se o e-mail existir na nossa base de dados, enviaremos as instruções de recuperação.';
        }

        $this->redirecionar('auth/esqueciSenha');
    }

    /**
     * Gera um token de redefinição e envia por e-mail.
     */
    public function enviarLinkRedefinicao(): void
    {
        $email = trim($_POST['email'] ?? '');
        $usuario = $email !== '' ? $this->usuarioModel->encontrarPorEmail($email) : false;
        AuditoriaHelper::registar('senha_recuperacao_solicitada', 'usuarios', $usuario ? (int) $usuario['id'] : null, null, ['email' => $email], 'alta');

        if ($usuario) {
            $this->gerarEEnviarToken((int) $usuario['id'], $usuario['email'], $usuario['nome']);
        }

        // Por segurança, a resposta é sempre a mesma, exista ou não o e-mail.
        $this->json([
            'sucesso'  => true,
            'mensagem' => 'Se o e-mail existir na nossa base de dados, enviaremos as instruções de recuperação.',
        ]);
    }

    /** Exibe a tela para definir nova senha (a partir do link recebido por e-mail) */
    public function redefinirSenha(string $token = ''): void
    {
        // Aceita tanto o segmento da URL (?/abc123) como o parâmetro do link do e-mail (?token=abc123)
        if ($token === '' && isset($_GET['token'])) {
            $token = trim((string) $_GET['token']);
        }

        $tokenValido = false;
        try {
            $tokenValido = (bool) $this->redefinicaoModel->buscarTokenValido($token);
        } catch (Throwable $e) {
            $tokenValido = false;
        }

        if (!$tokenValido) {
            $this->renderizarSemLayout('auth/redefinir-senha', [
                'token'      => $token,
                'erro'       => 'Link inválido ou expirado. Solicite uma nova recuperação de senha.',
                'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
            ]);
            return;
        }

        $this->renderizarSemLayout('auth/redefinir-senha', [
            'token'      => $token,
            'erro'       => $_SESSION['flash_auth_erro'] ?? null,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
        unset($_SESSION['flash_auth_erro']);
    }

    /** POST da tela "Nova Senha" */
    public function atualizarSenha(): void
    {
        $this->salvarNovaSenha();
    }

    /** Grava a nova senha após validar o token */
    public function salvarNovaSenha(): void
    {
        if (!$this->validarCsrf()) {
            $_SESSION['flash_auth_erro'] = 'Sessão expirada. Tente novamente.';
            $this->redirecionar('auth/redefinirSenha');
        }

        $token = trim($_POST['token'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmar = (string) ($_POST['confirmar_senha'] ?? $_POST['confirmar'] ?? '');

        try {
            $registro = $this->redefinicaoModel->buscarTokenValido($token);
        } catch (Throwable $e) {
            $registro = false;
        }

        if (!$registro) {
            $_SESSION['flash_auth_erro'] = 'Link inválido ou expirado. Solicite uma nova recuperação de senha.';
            $this->redirecionar('auth/redefinirSenha');
        }

        if (strlen($senha) < 8) {
            $_SESSION['flash_auth_erro'] = 'A palavra-passe deve ter pelo menos 8 caracteres.';
            $this->redirecionar('auth/redefinirSenha/' . rawurlencode($token));
        }

        if ($senha !== $confirmar) {
            $_SESSION['flash_auth_erro'] = 'As palavras-passe não coincidem.';
            $this->redirecionar('auth/redefinirSenha/' . rawurlencode($token));
        }

        $novoHash = $this->usuarioModel->criarHashSenha($senha);
        $this->usuarioModel->atualizar((int) $registro['usuario_id'], [
            'senha_hash'      => $novoHash,
            'primeiro_acesso' => 0,
        ]);
        $this->redefinicaoModel->marcarComoUsado((int) $registro['id']);

        AuditoriaHelper::registar('senha_redefinida', 'usuarios', (int) $registro['usuario_id'], null, [], 'alta');

        // Redireciona para a página de login com aviso de sucesso
        require_once CAMINHO_RAIZ . '/helpers/flash.php';
        definirFlash('sucesso', 'Palavra-passe alterada com sucesso. Inicie sessão com a sua nova senha.');
        $this->redirecionar('auth/login');
    }

    /** Valida o token CSRF dos formulários públicos de autenticação */
    private function validarCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return $token !== '' && SegurancaHelper::validarTokenCSRF($token);
    }

    /** Regista tentativa falhada e devolve o total acumulado */
    private function registarTentativaFalhada(string $email, string $dispositivo): int
    {
        try {
            return $this->tentativasModel->registarFalha($email, $dispositivo);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Gera token de redefinição e envia o e-mail */
    private function gerarEEnviarToken(int $usuarioId, string $email, string $nome): array
    {
        try {
            $token = $this->redefinicaoModel->criarToken($usuarioId);
            if (!$token) {
                return ['sucesso' => false, 'erro' => 'falha ao gerar token'];
            }
            $mailer = new Mailer();
            return $mailer->enviarRedefinicaoSenha($email, $nome, $token);
        } catch (Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
}
