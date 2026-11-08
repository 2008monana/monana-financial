<?php
/**
 * Funções de Segurança
 */
class SegurancaHelper {
    
    // Gerar token CSRF
    public static function gerarTokenCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validar token CSRF
    public static function validarTokenCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Sanitizar entrada
    public static function sanitizar($entrada) {
        if (is_array($entrada)) {
            return array_map([self::class, 'sanitizar'], $entrada);
        }
        return htmlspecialchars(trim($entrada), ENT_QUOTES, 'UTF-8');
    }

    // Validar email
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Gerar senha aleatória
    public static function gerarSenha($comprimento = 10) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
        return substr(str_shuffle($caracteres), 0, $comprimento);
    }

    // Hash de senha
    public static function hashSenha($senha) {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    // Verificar senha
    public static function verificarSenha($senha, $hash) {
        return password_verify($senha, $hash);
    }

    // Verificar tentativas de login
    public static function verificarTentativasLogin($email) {
        $chave = "tentativas_login_{$email}";
        $tentativas = $_SESSION[$chave] ?? 0;
        $ultimaTentativa = $_SESSION["ultima_tentativa_{$email}"] ?? 0;

        if ($tentativas >= 5 && (time() - $ultimaTentativa) < 900) { // 15 minutos
            return false;
        }

        if (time() - $ultimaTentativa > 900) {
            $_SESSION[$chave] = 0;
        }

        return true;
    }

    public static function incrementarTentativasLogin($email) {
        $chave = "tentativas_login_{$email}";
        $_SESSION[$chave] = ($_SESSION[$chave] ?? 0) + 1;
        $_SESSION["ultima_tentativa_{$email}"] = time();
    }

    // Gerar token único
    public static function gerarToken() {
        return bin2hex(random_bytes(32));
    }

    // Validar URL
    public static function validarURL($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // Escapar saída
    public static function escapar($texto) {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}