<?php
/**
 * BackupHelper
 * Motor de geração das cópias de segurança em .zip (contendo um .sql).
 *
 * - gerarGlobal()          -> cópia de TODA a base de dados (apenas Super Admin).
 * - gerarPorEmpresa($id)   -> cópia apenas dos dados dessa empresa (Admin Empresa).
 *
 * Estratégia: tenta usar `mysqldump` (mais rápido e mais completo, inclui
 * estrutura) quando disponível no servidor; caso contrário usa um dump
 * feito em PHP puro (estrutura via SHOW CREATE TABLE + dados via SELECT),
 * para funcionar em qualquer alojamento, mesmo sem acesso a `exec()`.
 */
class BackupHelper
{
    /** Tabelas cujos registos pertencem diretamente a uma empresa (têm coluna empresa_id). */
    private const TABELAS_DIRETAS = [
        'filiais', 'categorias', 'categorias_saida', 'metodos_pagamento',
        'transacoes', 'resumos_mensais', 'configuracoes',
    ];

    /** Tabelas cujos registos pertencem a uma empresa indiretamente, através de usuario_id -> usuarios.empresa_id. */
    private const TABELAS_POR_USUARIO = [
        'notificacoes', 'usuario_filiais', 'usuario_permissoes',
        'usuario_modulo_permissoes', 'redefinicoes_senha', 'perfis_usuario',
        'logs_auditoria',
    ];

    /** Gera um backup completo de toda a base de dados. Devolve ['sucesso'=>bool, 'caminho'=>?, 'nome'=>?, 'tamanho'=>?, 'erro'=>?]. */
    public static function gerarGlobal(): array
    {
        try {
            $pdo = Database::obterLigacao();
            $nomeBase = 'backup-global-' . date('Y-m-d_His');

            $sql = self::tentarMysqldumpCompleto();
            if ($sql === null) {
                // Fallback: dump em PHP puro de todas as tabelas.
                $tabelas = self::listarTodasTabelas($pdo);
                $sql = self::cabecalho('Backup global — ' . NOME_SISTEMA);
                foreach ($tabelas as $tabela) {
                    $sql .= self::dumpEstrutura($pdo, $tabela);
                    $sql .= self::dumpDados($pdo, $tabela);
                }
            }

            return self::gravarEZipar($sql, $nomeBase);
        } catch (Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    /** Gera um backup contendo apenas os dados de uma empresa específica. */
    public static function gerarPorEmpresa(int $empresaId): array
    {
        try {
            $pdo = Database::obterLigacao();
            $nomeBase = 'backup-empresa-' . $empresaId . '-' . date('Y-m-d_His');

            $sql = self::cabecalho('Backup da empresa #' . $empresaId . ' — ' . NOME_SISTEMA);

            // A própria empresa
            $sql .= self::dumpEstrutura($pdo, 'empresas');
            $sql .= self::dumpDados($pdo, 'empresas', 'id = :id', ['id' => $empresaId]);

            foreach (self::TABELAS_DIRETAS as $tabela) {
                if (!self::tabelaExiste($pdo, $tabela)) continue;
                $sql .= self::dumpEstrutura($pdo, $tabela);
                $sql .= self::dumpDados($pdo, $tabela, 'empresa_id = :empresa_id', ['empresa_id' => $empresaId]);
            }

            // usuarios primeiro (para as tabelas "por usuário" fazerem sentido a seguir)
            if (self::tabelaExiste($pdo, 'usuarios')) {
                $sql .= self::dumpEstrutura($pdo, 'usuarios');
                $sql .= self::dumpDados($pdo, 'usuarios', 'empresa_id = :empresa_id', ['empresa_id' => $empresaId]);
            }

            foreach (self::TABELAS_POR_USUARIO as $tabela) {
                if (!self::tabelaExiste($pdo, $tabela)) continue;
                $sql .= self::dumpEstrutura($pdo, $tabela);
                $where = "usuario_id IN (SELECT id FROM usuarios WHERE empresa_id = :empresa_id)";
                $sql .= self::dumpDados($pdo, $tabela, $where, ['empresa_id' => $empresaId]);
            }

            return self::gravarEZipar($sql, $nomeBase);
        } catch (Throwable $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }

    // ---------------------------------------------------------------
    // Internos
    // ---------------------------------------------------------------

    private static function cabecalho(string $titulo): string
    {
        return "-- =====================================================\n"
             . "-- {$titulo}\n"
             . "-- Gerado em: " . date('Y-m-d H:i:s') . "\n"
             . "-- =====================================================\n"
             . "SET FOREIGN_KEY_CHECKS=0;\n\n";
    }

    private static function tabelaExiste(PDO $pdo, string $tabela): bool
    {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :t");
        $stmt->execute(['t' => $tabela]);
        return (bool) $stmt->fetchColumn();
    }

    private static function listarTodasTabelas(PDO $pdo): array
    {
        $stmt = $pdo->query('SHOW TABLES');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function dumpEstrutura(PDO $pdo, string $tabela): string
    {
        try {
            $stmt = $pdo->query("SHOW CREATE TABLE `{$tabela}`");
            $linha = $stmt->fetch(PDO::FETCH_NUM);
            if (!$linha) return '';
            return "\n--\n-- Estrutura da tabela `{$tabela}`\n--\nDROP TABLE IF EXISTS `{$tabela}`;\n{$linha[1]};\n\n";
        } catch (Throwable $e) {
            return "-- Aviso: não foi possível obter a estrutura de `{$tabela}`: {$e->getMessage()}\n";
        }
    }

    private static function dumpDados(PDO $pdo, string $tabela, ?string $where = null, array $params = []): string
    {
        $sqlSelect = "SELECT * FROM `{$tabela}`" . ($where ? " WHERE {$where}" : '');
        try {
            $stmt = $pdo->prepare($sqlSelect);
            $stmt->execute($params);
        } catch (Throwable $e) {
            return "-- Aviso: não foi possível ler dados de `{$tabela}`: {$e->getMessage()}\n";
        }

        $saida = "--\n-- Dados da tabela `{$tabela}`\n--\n";
        $linhas = 0;
        while ($registo = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $colunas = array_keys($registo);
            $valores = array_map(function ($v) use ($pdo) {
                if ($v === null) return 'NULL';
                if (is_int($v) || is_float($v)) return $v;
                return $pdo->quote((string) $v);
            }, array_values($registo));

            $saida .= "INSERT INTO `{$tabela}` (`" . implode('`, `', $colunas) . "`) VALUES ("
                    . implode(', ', $valores) . ");\n";
            $linhas++;
        }
        if ($linhas === 0) {
            $saida .= "-- (sem registos)\n";
        }
        return $saida . "\n";
    }

    /** Tenta invocar o binário `mysqldump`; devolve null se não estiver disponível. */
    private static function tentarMysqldumpCompleto(): ?string
    {
        if (!function_exists('shell_exec') || stripos((string) ini_get('disable_functions'), 'shell_exec') !== false) {
            return null;
        }

        $config = require CAMINHO_RAIZ . '/config/database.php';
        $comandoTeste = @shell_exec('command -v mysqldump 2>/dev/null');
        if (empty(trim((string) $comandoTeste))) {
            return null;
        }

        $senhaArg = $config['senha'] !== '' ? '-p' . escapeshellarg($config['senha']) : '';
        $comando = sprintf(
            'mysqldump --no-tablespaces -h%s -P%s -u%s %s %s 2>/dev/null',
            escapeshellarg($config['host']),
            escapeshellarg((string) $config['porta']),
            escapeshellarg($config['usuario']),
            $senhaArg,
            escapeshellarg($config['base'])
        );

        $resultado = @shell_exec($comando);
        return (!empty($resultado)) ? $resultado : null;
    }

    /** Grava o conteúdo SQL em ficheiro temporário, comprime em .zip e devolve os metadados. */
    private static function gravarEZipar(string $conteudoSql, string $nomeBase): array
    {
        if (!is_dir(CAMINHO_BACKUPS)) {
            mkdir(CAMINHO_BACKUPS, 0750, true);
            // Impede acesso direto via web caso a pasta alguma vez fique acessível.
            file_put_contents(CAMINHO_BACKUPS . '/.htaccess', "Require all denied\n");
        }

        $nomeSql = $nomeBase . '.sql';
        $nomeZip = $nomeBase . '.zip';
        $caminhoSql = CAMINHO_BACKUPS . '/' . $nomeSql;
        $caminhoZip = CAMINHO_BACKUPS . '/' . $nomeZip;

        file_put_contents($caminhoSql, $conteudoSql);

        if (!class_exists('ZipArchive')) {
            return ['sucesso' => false, 'erro' => 'A extensão ZipArchive não está disponível no servidor PHP.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($caminhoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['sucesso' => false, 'erro' => 'Não foi possível criar o ficheiro .zip do backup.'];
        }
        $zip->addFile($caminhoSql, $nomeSql);
        $zip->close();

        // Já não precisamos do .sql solto, só do .zip.
        @unlink($caminhoSql);

        if (!file_exists($caminhoZip)) {
            return ['sucesso' => false, 'erro' => 'O ficheiro .zip do backup não foi gerado.'];
        }

        return [
            'sucesso'  => true,
            'nome'     => $nomeZip,
            'caminho'  => $nomeZip, // caminho relativo dentro de CAMINHO_BACKUPS
            'tamanho'  => filesize($caminhoZip),
        ];
    }
}
