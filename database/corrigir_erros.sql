-- =========================================================
-- CORREÇÃO DE ERROS - MonanaFinancial
-- =========================================================

USE monana_financial;

-- 1. Criar tabela metodos_pagamento (resolve erro de tabela não encontrada)
CREATE TABLE IF NOT EXISTS metodos_pagamento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL DEFAULT 'entrada',
    categoria VARCHAR(50) DEFAULT NULL,
    cor VARCHAR(20) DEFAULT '#0e2748',
    icone VARCHAR(50) DEFAULT 'fa-credit-card',
    ordem INT UNSIGNED DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mp_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_codigo (empresa_id, codigo),
    INDEX idx_empresa_tipo (empresa_id, tipo)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Inserir métodos de pagamento padrão
INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'TPA - BCA', 'tpa_bca', 'entrada', 'tpa', '#0e2748', 'fa-credit-card', 1
FROM empresas e;

INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'TPA - Keve', 'tpa_keve', 'entrada', 'tpa', '#173a67', 'fa-credit-card', 2
FROM empresas e;

INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'Transferência Bancária', 'transferencia', 'entrada', 'transferencia', '#3b82f6', 'fa-university', 3
FROM empresas e;

INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'Dinheiro', 'dinheiro', 'entrada', 'dinheiro', '#22c55e', 'fa-money-bill', 4
FROM empresas e;

INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'Depósito', 'deposito', 'saida', 'deposito', '#16a34a', 'fa-building-columns', 5
FROM empresas e;

INSERT IGNORE INTO metodos_pagamento (empresa_id, nome, codigo, tipo, categoria, cor, icone, ordem)
SELECT 
    e.id, 'Gasto/Despesa', 'gasto', 'saida', 'gasto', '#ef4444', 'fa-receipt', 6
FROM empresas e;

-- Nota: O campo 'saldo_final' não é uma coluna na tabela transacoes.
-- Ele é calculado dinamicamente nas queries SQL usando SUM() com CASE.
-- Não é necessário adicionar esta coluna à tabela.
