<?php
/**
 * Auto-migrações MonanaFinancial
 *
 * Garante que a estrutura da base de dados está sincronizada com o código,
 * adicionando automaticamente colunas/tabelas em falta (idempotente).
 * Executa apenas uma vez por pedido e guarda um carimbo de conclusão na tabela _migracoes.
 */

function executarAutoMigracoes(PDO $db): void {
    static $executado = false;
    if ($executado) {
        return;
    }
    $executado = true;

    try {
        // Tabela de controlo de migrações
        $db->exec("CREATE TABLE IF NOT EXISTS _migracoes (
            nome VARCHAR(100) PRIMARY KEY,
            aplicada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Se já foi aplicada recentemente, não repetir
        $stmt = $db->prepare("SELECT aplicada_em FROM _migracoes WHERE nome = :nome");
        $stmt->execute(['nome' => 'auto_2026_09_metodos_pagamento']);
        $aplicada = $stmt->fetchColumn();
        if ($aplicada && strtotime((string)$aplicada) > time() - 3600) {
            return;
        }

        // Colunas opcionais em falta na tabela metodos_pagamento
        $colunas = [
            'descricao' => "ALTER TABLE metodos_pagamento ADD COLUMN descricao VARCHAR(255) NULL AFTER nome",
            'ordem'     => "ALTER TABLE metodos_pagamento ADD COLUMN ordem INT UNSIGNED NOT NULL DEFAULT 0 AFTER ativo",
            'cor'       => "ALTER TABLE metodos_pagamento ADD COLUMN cor VARCHAR(20) NULL DEFAULT '#0e2748' AFTER icone",
            'categoria' => "ALTER TABLE metodos_pagamento ADD COLUMN categoria VARCHAR(50) NULL AFTER tipo",
        ];

        $temTabela = (bool)$db->query("SHOW TABLES LIKE 'metodos_pagamento'")->fetchColumn();
        if ($temTabela) {
            $existentes = [];
            foreach ($db->query("SHOW COLUMNS FROM metodos_pagamento") as $col) {
                $existentes[$col['Field']] = true;
            }
            foreach ($colunas as $nome => $sql) {
                if (!isset($existentes[$nome])) {
                    $db->exec($sql);
                }
            }
        }

        // Marcar migração como aplicada
        $stmt = $db->prepare("INSERT INTO _migracoes (nome) VALUES (:nome)
                              ON DUPLICATE KEY UPDATE aplicada_em = CURRENT_TIMESTAMP");
        $stmt->execute(['nome' => 'auto_2026_09_metodos_pagamento']);
    } catch (Throwable $e) {
        // Nunca bloquear o sistema por causa de auto-migrações
        error_log('[MonanaFinancial] Auto-migrações falharam: ' . $e->getMessage());
    }
}
