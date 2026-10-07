<?php
/**
 * Classe para Envio de Emails via SMTP (PHPMailer)
 *
 * Configuração feita em config/config.php, chave 'email':
 *   - smtp_host, smtp_porta, smtp_usuario, smtp_senha,
 *     smtp_from, smtp_from_nome
 *
 * Se o PHPMailer não estiver instalado (composer require phpmailer/phpmailer)
 * ou o SMTP não estiver configurado, os métodos devolvem um erro claro
 * em vez de lançar exceções — o sistema continua funcional.
 */

require_once CAMINHO_RAIZ . '/config/config.php';

class Mailer {
    private ?\PHPMailer\PHPMailer\PHPMailer $mail = null;
    private ?string $erro = null;

    public function __construct() {
        $cfg = self::configuracao();

        // Sem SMTP configurado → não tentar ligar (devolve erro amigável depois)
        if (empty($cfg['smtp_host']) || $cfg['smtp_host'] === 'smtp.exemplo.com') {
            $this->erro = 'SMTP não configurado. Defina smtp_host/smtp_usuario/smtp_senha em config/config.php.';
            return;
        }

        // Carregar PHPMailer (se disponível)
        $autoload = CAMINHO_RAIZ . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            $this->erro = 'PHPMailer não instalado. Execute: composer require phpmailer/phpmailer';
            return;
        }
        require_once $autoload;

        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $this->erro = 'PHPMailer não instalado. Execute: composer require phpmailer/phpmailer';
            return;
        }

        try {
            $this->mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->configurar($cfg);
        } catch (\Throwable $e) {
            $this->mail = null;
            $this->erro = $e->getMessage();
        }
    }

    /** Lê a configuração de email do array retornado por config/config.php */
    public static function configuracao(): array {
        $cfg = require CAMINHO_RAIZ . '/config/config.php';

        $padrao = [
            'smtp_host'      => '',
            'smtp_porta'     => 587,
            'smtp_usuario'   => '',
            'smtp_senha'     => '',
            'smtp_from'      => 'nao-responder@monanafinancial.com',
            'smtp_from_nome' => defined('NOME_SISTEMA') ? NOME_SISTEMA : 'MonanaFinancial',
        ];

        // config.php retorna o array de SMTP diretamente (chaves smtp_*)
        // ou embrulhado na chave 'email'. Suporta ambos os formatos.
        if (is_array($cfg)) {
            if (isset($cfg['email']) && is_array($cfg['email'])) {
                return array_merge($padrao, $cfg['email']);
            }
            if (array_key_exists('smtp_host', $cfg)) {
                return array_merge($padrao, $cfg);
            }
        }
        return $padrao;
    }

    private function configurar(array $cfg): void {
        if (!$this->mail) return;

        try {
            $this->mail->isSMTP();
            $this->mail->Host       = $cfg['smtp_host'];
            $this->mail->SMTPAuth   = ($cfg['smtp_usuario'] !== '');
            $this->mail->Username   = $cfg['smtp_usuario'];
            $this->mail->Password   = $cfg['smtp_senha'];
            $this->mail->Port       = (int) ($cfg['smtp_porta'] ?? 587);
            // TLS na porta 587; SSL direto na 465; sem cifra em portas locais (25)
            if ((int) ($cfg['smtp_porta'] ?? 587) === 465) {
                $this->mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            } elseif (!empty($cfg['smtp_usuario'])) {
                $this->mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $this->mail->SMTPSecure = false;
                $this->mail->SMTPAutoTLS = false;
            }
            $this->mail->setFrom($cfg['smtp_from'], $cfg['smtp_from_nome']);
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
        } catch (\Throwable $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function enviarBoasVindas($para, $nome, $senha) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => $this->erro ?: 'PHPMailer não disponível'];

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Bem-vindo ao MonanaFinancial';

            $corpo = $this->renderizarTemplate('boas-vindas', [
                'nome' => $nome,
                'email' => $para,
                'senha' => $senha,
                'login_url' => URL_BASE . '/auth/login'
            ]);

            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);

            return ['sucesso' => $this->mail->send()];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function enviarRedefinicaoSenha($para, $nome, $token) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => $this->erro ?: 'PHPMailer não disponível'];

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Redefinição de Senha - MonanaFinancial';

            $linkRedefinicao = URL_BASE . '/auth/redefinirSenha?token=' . urlencode($token);

            $corpo = $this->renderizarTemplate('redefinir-senha', [
                'nome' => $nome,
                'link_redefinicao' => $linkRedefinicao,
                'expiracao' => '1 hora'
            ]);

            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);

            return ['sucesso' => $this->mail->send()];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function enviarUsuarioCriado($para, $nome, $senha, $empresa) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => $this->erro ?: 'PHPMailer não disponível'];

        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Sua conta foi criada no MonanaFinancial';

            $corpo = $this->renderizarTemplate('usuario-criado', [
                'nome' => $nome,
                'email' => $para,
                'senha' => $senha,
                'empresa' => $empresa,
                'login_url' => URL_BASE . '/auth/login'
            ]);

            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);

            return ['sucesso' => $this->mail->send()];
        } catch (\Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    private function renderizarTemplate($template, $dados) {
        extract($dados);
        ob_start();
        $caminho = CAMINHO_RAIZ . '/views/emails/' . $template . '.php';
        if (file_exists($caminho)) {
            require $caminho;
        } else {
            // Template padrão se o arquivo não existir
            $nomeSeguro = htmlspecialchars($nome ?? '', ENT_QUOTES, 'UTF-8');
            echo "<h1>" . (defined('NOME_SISTEMA') ? NOME_SISTEMA : 'MonanaFinancial') . "</h1>";
            echo "<p>Olá, {$nomeSeguro}</p>";
            echo "<p>Este é um email automático do sistema.</p>";
        }
        return ob_get_clean();
    }

    public function getErro() {
        return $this->erro;
    }
}
