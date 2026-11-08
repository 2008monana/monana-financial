-- ============================================================
-- MonanaFinancial — Migração: Logs de Auditoria + Configurações
-- Execute no phpMyAdmin. Idempotente (seguro repetir).
-- ============================================================

CREATE TABLE IF NOT EXISTS configuracoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NULL,
    chave VARCHAR(100) NOT NULL,
    valor TEXT NULL,
    tipo ENUM('texto','numero','booleano','json','secreto') NOT NULL DEFAULT 'texto',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_configuracoes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_configuracao_escopo (empresa_id, chave)
) ENGINE=InnoDB;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='ip_origem');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN ip_origem VARCHAR(45) NULL','SELECT "ip_origem ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='tabela_afetada');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN tabela_afetada VARCHAR(100) NULL','SELECT "tabela_afetada ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @fid := (SELECT id FROM empresas WHERE nome LIKE '%Beston%' LIMIT 1);
INSERT IGNORE INTO configuracoes (empresa_id, chave, valor, tipo) VALUES
(@fid,'moeda_padrao','AOA','texto'),
(@fid,'periodo_fiscal_inicio','1','texto'),
(@fid,'periodo_fiscal_duracao','12','texto'),
(@fid,'notificacoes_email','1','booleano'),
(@fid,'notificacoes_sistema','1','booleano'),
(@fid,'notificacoes_saldo_baixo','1','booleano'),
(@fid,'notificacoes_vencimentos','1','booleano'),
(@fid,'notificacoes_relatorios','0','booleano');

DELETE FROM metodos_pagamento WHERE empresa_id IS NULL;

SELECT 'Migracao concluida.' AS resultado;
