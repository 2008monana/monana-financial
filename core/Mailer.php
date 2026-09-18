<?php
/**
 * Classe para Envio de Emails via SMTP
 */
class Mailer {
    private $mail;
    private $erro = null;

    public function __construct() {
        // Carregar PHPMailer (se disponível)
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            $this->mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->configurar();
        }
    }

    private function configurar() {
        if (!$this->mail) return;
        
        try {
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USUARIO;
            $this->mail->Password = SMTP_SENHA;
            $this->mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = SMTP_PORTA;
            $this->mail->setFrom(SMTP_EMAIL_REMETENTE, SMTP_NOME_REMETENTE);
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->erro = $e->getMessage();
        }
    }

    public function enviarBoasVindas($para, $nome, $senha) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => 'PHPMailer não instalado'];
        
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Bem-vindo ao MonanaFinancial';
            
            $corpo = $this->renderizarTemplate('boas-vindas', [
                'nome' => $nome,
                'email' => $para,
                'senha' => $senha,
                'login_url' => APP_URL . '/login'
            ]);
            
            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);
            
            return ['sucesso' => $this->mail->send()];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function enviarRedefinicaoSenha($para, $nome, $token) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => 'PHPMailer não instalado'];
        
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Redefinição de Senha - MonanaFinancial';
            
            $linkRedefinicao = APP_URL . '/redefinir-senha?token=' . $token;
            
            $corpo = $this->renderizarTemplate('redefinir-senha', [
                'nome' => $nome,
                'link_redefinicao' => $linkRedefinicao,
                'expiracao' => '1 hora'
            ]);
            
            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);
            
            return ['sucesso' => $this->mail->send()];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    public function enviarUsuarioCriado($para, $nome, $senha, $empresa) {
        if (!$this->mail) return ['sucesso' => false, 'erro' => 'PHPMailer não instalado'];
        
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($para);
            $this->mail->Subject = 'Sua conta foi criada no MonanaFinancial';
            
            $corpo = $this->renderizarTemplate('usuario-criado', [
                'nome' => $nome,
                'email' => $para,
                'senha' => $senha,
                'empresa' => $empresa,
                'login_url' => APP_URL . '/login'
            ]);
            
            $this->mail->Body = $corpo;
            $this->mail->AltBody = strip_tags($corpo);
            
            return ['sucesso' => $this->mail->send()];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    private function renderizarTemplate($template, $dados) {
        extract($dados);
        ob_start();
        if (file_exists(__DIR__ . '/../views/emails/' . $template . '.php')) {
            require __DIR__ . '/../views/emails/' . $template . '.php';
        } else {
            // Template padrão se o arquivo não existir
            echo "<h1>MonanaFinancial</h1>";
            echo "<p>Olá, {$nome}</p>";
            echo "<p>Este é um email automático do sistema.</p>";
        }
        return ob_get_clean();
    }

    public function getErro() {
        return $this->erro;
    }
}