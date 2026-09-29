<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Modelo TentativaLogin
 * Controla tentativas de login falhadas por email + dispositivo (cookie),
 * aplicando bloqueio temporário após o limite de tentativas.
 */
class TentativaLogin extends Model
{
    protected string $tabela = 'tentativas_login';

    /** Máximo de tentativas inválidas antes do bloqueio. */
    public const LIMITE_TENTATIVAS = 10;

    /** Duração do bloqueio, em minutos. */
    public const MINUTOS_BLOQUEIO = 30;

    public function __construct()
    {
        parent::__construct();
        $this->garantirTabela();
    }

    private function garantirTabela(): void
    {
        try {
            $this->bd->exec(
                "CREATE TABLE IF NOT EXISTS tentativas_login (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(150) NOT NULL,
                    dispositivo VARCHAR(64) NOT NULL DEFAULT '',
                    tentativas INT UNSIGNED NOT NULL DEFAULT 0,
                    bloqueado_ate DATETIME NULL,
                    ultima_tentativa DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uk_email_disp (email, dispositivo)
                ) ENGINE=InnoDB"
            );
        } catch (Throwable $e) {
            // silencioso — o chamador trata a indisponibilidade
        }
    }

    /**
     * Identificador estável do dispositivo (cookie próprio, sobrevive a sessões).
     */
    public static function identificadorDispositivo(): string
    {
        if (!empty($_COOKIE['monana_dispositivo']) && preg_match('/^[a-f0-9]{32,64}$/i', $_COOKIE['monana_dispositivo'])) {
            return $_COOKIE['monana_dispositivo'];
        }
        $id = bin2hex(random_bytes(16));
        if (!headers_sent()) {
            setcookie('monana_dispositivo', $id, [
                'expires'  => time() + 3600 * 24 * 365,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        return $id;
    }

    /**
     * Verifica se o par email+dispositivo está bloqueado.
     *
     * @return array|null null = não bloqueado; array com 'minutos_restantes' = bloqueado.
     */
    public function verificarBloqueio(string $email, string $dispositivo): ?array
    {
        $stmt = $this->bd->prepare(
            "SELECT bloqueado_ate FROM tentativas_login
             WHERE email = :email AND dispositivo = :disp LIMIT 1"
        );
        $stmt->execute([':email' => mb_strtolower($email), ':disp' => $dispositivo]);
        $linha = $stmt->fetch();

        if ($linha && !empty($linha['bloqueado_ate']) && strtotime((string) $linha['bloqueado_ate']) > time()) {
            $restante = strtotime((string) $linha['bloqueado_ate']) - time();
            return ['minutos_restantes' => max(1, (int) ceil($restante / 60))];
        }

        return null;
    }

    /**
     * Regista uma tentativa falhada; bloqueia ao atingir o limite.
     *
     * @return int tentativas acumuladas
     */
    public function registarFalha(string $email, string $dispositivo): int
    {
        $email = mb_strtolower($email);

        $stmt = $this->bd->prepare(
            "SELECT id, tentativas FROM tentativas_login
             WHERE email = :email AND dispositivo = :disp LIMIT 1"
        );
        $stmt->execute([':email' => $email, ':disp' => $dispositivo]);
        $linha = $stmt->fetch();

        if ($linha) {
            $novas = (int) $linha['tentativas'] + 1;
            $bloqueio = $novas >= self::LIMITE_TENTATIVAS
                ? date('Y-m-d H:i:s', time() + self::MINUTOS_BLOQUEIO * 60)
                : null;

            $upd = $this->bd->prepare(
                "UPDATE tentativas_login
                 SET tentativas = :t,
                     bloqueado_ate = COALESCE(:b, bloqueado_ate),
                     ultima_tentativa = NOW()
                 WHERE id = :id"
            );
            $upd->execute([':t' => $novas, ':b' => $bloqueio, ':id' => $linha['id']]);
            return $novas;
        }

        $ins = $this->bd->prepare(
            "INSERT INTO tentativas_login (email, dispositivo, tentativas, ultima_tentativa)
             VALUES (:email, :disp, 1, NOW())"
        );
        $ins->execute([':email' => $email, ':disp' => $dispositivo]);
        return 1;
    }

    /** Limpa as tentativas após login bem-sucedido. */
    public function limpar(string $email, string $dispositivo): void
    {
        $stmt = $this->bd->prepare(
            "DELETE FROM tentativas_login WHERE email = :email AND dispositivo = :disp"
        );
        $stmt->execute([':email' => mb_strtolower($email), ':disp' => $dispositivo]);
    }
}
