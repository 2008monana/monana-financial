-- Execute uma única vez depois de database/schema.sql.
ALTER TABLE logs_auditoria
  ADD COLUMN tabela_afetada VARCHAR(60) NULL AFTER acao,
  ADD COLUMN dados_antigos JSON NULL AFTER registo_id,
  ADD COLUMN dados_novos JSON NULL AFTER dados_antigos,
  ADD COLUMN user_agent VARCHAR(500) NULL AFTER ip_origem,
  ADD INDEX idx_logs_usuario_data (usuario_id, criado_em),
  ADD INDEX idx_logs_tabela_data (tabela_afetada, criado_em);

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

ALTER TABLE importacoes
  ADD COLUMN token_reversao CHAR(36) NULL AFTER status,
  ADD COLUMN tempo_processamento_ms INT UNSIGNED NULL AFTER token_reversao,
  ADD INDEX idx_importacoes_empresa_data (empresa_id, criado_em);
