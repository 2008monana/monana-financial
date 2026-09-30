-- =====================================================================
-- Migração: MÓDULO DE ASSINATURAS (planos, assinaturas, pagamentos, avisos)
-- Idempotente: pode ser executada mais de uma vez sem efeitos laterais.
-- Execute depois de schema.sql / monana_financial.sql e
-- migracao_modulos_backup.sql.
--
--   mysql -u root -p monana_financial < database/migracao_modulo_assinaturas.sql
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) TABELA planos — preços e durações editáveis pelo Super Admin.
--    Nunca hardcodar preços no código: esta tabela é a fonte.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS planos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(80) NOT NULL,
    duracao_dias INT UNSIGNED NULL,            -- NULL = sem fim (plano gratuito)
    preco DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ordem INT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Semente dos 5 planos (ON DUPLICATE KEY só actualiza o nome/ordem;
-- NUNCA sobrepõe preços/durações que o Super Admin possa ter editado).
INSERT INTO planos (codigo, nome, duracao_dias, preco, ordem, ativo) VALUES
    ('gratuito',    'Gratuito',    NULL, 0.00,     0, 1),
    ('mensal',      'Mensal',      30,   20000.00, 1, 1),
    ('trimestral',  'Trimestral',  90,   50000.00, 2, 1),
    ('semestral',   'Semestral',   180,  100000.00,3, 1),
    ('anual',       'Anual',       365,  220000.00,4, 1)
ON DUPLICATE KEY UPDATE nome = VALUES(nome), ordem = VALUES(ordem);

-- ---------------------------------------------------------------------
-- 2) TABELA assinaturas — um registo por período de cada empresa.
--    Nunca se apaga: o histórico fica completo.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assinaturas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    plano_id INT UNSIGNED NOT NULL,
    estado ENUM('activa','pendente_pagamento','encerrada') NOT NULL DEFAULT 'activa',
    valor_acordado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    inicio DATETIME NULL,                       -- NULL enquanto pendente
    fim DATETIME NULL,                          -- NULL = sem fim (só no gratuito)
    carencia_ate DATETIME NULL,                 -- usado em pendente_pagamento
    bloqueada_manual TINYINT(1) NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    criado_por INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_assinaturas_plano   FOREIGN KEY (plano_id)   REFERENCES planos(id)   ON DELETE RESTRICT,
    CONSTRAINT fk_assinaturas_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id)  ON DELETE SET NULL,
    INDEX idx_assinaturas_empresa_estado (empresa_id, estado),
    INDEX idx_assinaturas_fim (fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 3) TABELA assinatura_pagamentos — pagamentos registados manualmente.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assinatura_pagamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id INT UNSIGNED NOT NULL,
    empresa_id INT UNSIGNED NOT NULL,
    valor DECIMAL(12,2) NOT NULL,
    metodo VARCHAR(50) NOT NULL,
    referencia VARCHAR(100) NULL,
    data_pagamento DATE NOT NULL,
    observacoes TEXT NULL,
    criado_por INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pag_assoc FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE,
    CONSTRAINT fk_pag_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_pag_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_pagamentos_empresa_data (empresa_id, data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 4) TABELA assinatura_avisos — evita avisos/notificações repetidos.
--    Inserir com INSERT IGNORE e só notificar se a linha foi inserida.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assinatura_avisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id INT UNSIGNED NOT NULL,
    tipo ENUM('7d','3d','1d','carencia','bloqueio') NOT NULL,
    enviado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aviso_assoc FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_aviso (assinatura_id, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- 5) Configurações globais do módulo (empresa_id NULL).
-- ---------------------------------------------------------------------
INSERT IGNORE INTO configuracoes (empresa_id, chave, valor, tipo) VALUES
    (NULL, 'whatsapp_admin', '', 'texto'),
    (NULL, 'assinatura_carencia_horas', '48', 'numero');

-- ---------------------------------------------------------------------
-- 6) Catálogo de módulos (permissões por página) + rota de menu do
--    Admin Empresa. O Router usa 'assinaturas' como módulo próprio;
--    'minha_assinatura' existe apenas para o item de menu/permissão.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO modulos (nome, descricao, icone, ordem) VALUES
    ('assinaturas',      'Assinaturas',        'fa-crown',   16),
    ('minha_assinatura', 'Minha Assinatura',   'fa-id-card', 17);

-- ---------------------------------------------------------------------
-- 7) Empresas já existentes: criar assinatura `activa` gratuita para
--    cada empresa sem nenhuma assinatura. Assim ninguém fica bloqueado
--    na instalação (critério de aceitação nº 1).
-- ---------------------------------------------------------------------
INSERT INTO assinaturas (empresa_id, plano_id, estado, valor_acordado, inicio, fim, criado_por, observacoes)
SELECT e.id,
       (SELECT p.id FROM planos p WHERE p.codigo = 'gratuito' LIMIT 1),
       'activa', 0.00, NOW(), NULL, NULL,
       'Assinatura inicial criada pela migração do módulo de assinaturas.'
FROM empresas e
WHERE (SELECT p.id FROM planos p WHERE p.codigo = 'gratuito' LIMIT 1) IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM assinaturas a WHERE a.empresa_id = e.id);
