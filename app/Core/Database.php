<?php
/**
 * Classe Database
 * Gere a ligação PDO à base de dados MySQL (padrão Singleton).
 */

class Database
{
    private static ?PDO $instancia = null;

    public static function obterLigacao(): PDO
    {
        if (self::$instancia === null) {
            $config = require CAMINHO_RAIZ . '/config/database.php';

            $dsn = "mysql:host={$config['host']};port={$config['porta']};dbname={$config['base']};charset={$config['charset']}";

            try {
                self::$instancia = new PDO($dsn, $config['usuario'], $config['senha'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('[MonanaFinancial] Erro de ligação à BD: ' . $e->getMessage());
                die('Não foi possível ligar à base de dados. Verifique a configuração em config/database.php.');
            }
        }

        return self::$instancia;
    }
}
