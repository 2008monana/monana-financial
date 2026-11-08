-- Migração: garante que a tabela logs_auditoria tem todas as colunas necessárias
-- Executar no phpMyAdmin (aba SQL) sobre a base de dados do MonanaFinancial.
-- É segura de repetir: só adiciona colunas que ainda não existem.

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    acao VARCHAR(100) NOT NULL,
    prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',
    tabela_afetada VARCHAR(100) NULL,
    registo_id INT NULL,
    dados_antigos LONGTEXT NULL,
    dados_novos LONGTEXT NULL,
    ip_origem VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    motivo VARCHAR(255) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='tabela_afetada');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN tabela_afetada VARCHAR(100) NULL','SELECT "tabela_afetada ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='registo_id');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN registo_id INT NULL','SELECT "registo_id ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='dados_antigos');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN dados_antigos LONGTEXT NULL','SELECT "dados_antigos ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='dados_novos');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN dados_novos LONGTEXT NULL','SELECT "dados_novos ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='ip_origem');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN ip_origem VARCHAR(45) NULL','SELECT "ip_origem ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='user_agent');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN user_agent VARCHAR(500) NULL','SELECT "user_agent ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='motivo');
SET @s := IF(@ex=0,'ALTER TABLE logs_auditoria ADD COLUMN motivo VARCHAR(255) NULL','SELECT "motivo ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @ex := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='logs_auditoria' AND column_name='prioridade');
SET @s := IF(@ex=0,"ALTER TABLE logs_auditoria ADD COLUMN prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media'",'SELECT "prioridade ja existe"');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
