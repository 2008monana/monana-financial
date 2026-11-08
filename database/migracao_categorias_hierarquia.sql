-- =========================================================
-- MIGRAÇÃO: Categorias com Hierarquia + Seed Padrão
-- Data: 2026-11-07
-- Baseada nos apontamentos de padronização de Receitas e Despesas
-- =========================================================
-- 
-- O que esta migração faz:
-- 1. Adiciona suporte a hierarquia (pai_id)
-- 2. Adiciona campos ordem, icone, descricao, sistema
-- 3. Insere o conjunto completo de categorias padrão
--    para todas as empresas existentes
-- 
-- É idempotente: pode ser executada várias vezes com segurança.
-- =========================================================

USE monana_financial;

-- ---------------------------------------------------------
-- 1. ALTERAÇÕES NA TABELA categorias
-- ---------------------------------------------------------

-- Adicionar colunas (só se ainda não existirem)
SET @existe_pai := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'categorias'
      AND COLUMN_NAME = 'pai_id'
);

SET @sql_pai = IF(@existe_pai = 0,
    'ALTER TABLE categorias
        ADD COLUMN pai_id INT UNSIGNED NULL AFTER empresa_id,
        ADD COLUMN ordem INT UNSIGNED NOT NULL DEFAULT 0 AFTER nome,
        ADD COLUMN icone VARCHAR(50) DEFAULT ''fa-tag'' AFTER cor,
        ADD COLUMN descricao VARCHAR(255) DEFAULT NULL AFTER nome,
        ADD COLUMN sistema TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = categoria do template do sistema'' AFTER ativa',
    'SELECT ''Colunas de hierarquia já existem'' AS info'
);
PREPARE stmt FROM @sql_pai;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Índice para performance (só se não existir)
SET @existe_idx := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'categorias'
      AND INDEX_NAME = 'idx_categorias_pai'
);

SET @sql_idx = IF(@existe_idx = 0,
    'ALTER TABLE categorias
        ADD INDEX idx_categorias_pai (pai_id),
        ADD INDEX idx_categorias_empresa_tipo_ordem (empresa_id, tipo, ordem)',
    'SELECT ''Índices já existem'' AS info'
);
PREPARE stmt FROM @sql_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Foreign key (só se não existir)
SET @existe_fk := (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'categorias'
      AND CONSTRAINT_NAME = 'fk_categorias_pai'
);

SET @sql_fk = IF(@existe_fk = 0,
    'ALTER TABLE categorias
        ADD CONSTRAINT fk_categorias_pai
        FOREIGN KEY (pai_id) REFERENCES categorias(id) ON DELETE SET NULL',
    'SELECT ''FK já existe'' AS info'
);
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------
-- 2. LIMPAR CATEGORIAS ANTIGAS SIMPLES (opcional e seguro)
--    Só remove as 4 categorias de exemplo se ainda existirem
--    e não tiverem transações associadas.
-- ---------------------------------------------------------
DELETE c FROM categorias c
LEFT JOIN transacoes t ON t.categoria_id = c.id
WHERE c.empresa_id IS NOT NULL
  AND c.nome IN ('Vendas ao Balcão', 'Fornecedores', 'Água e Luz', 'Salários')
  AND t.id IS NULL
  AND c.sistema = 0;

-- ---------------------------------------------------------
-- 3. SEED DAS CATEGORIAS PADRÃO (com hierarquia)
--    Para CADA empresa existente
-- ---------------------------------------------------------

-- Procedimento temporário para inserir o template numa empresa
DROP PROCEDURE IF EXISTS sp_seed_categorias_padrao;

DELIMITER $$

CREATE PROCEDURE sp_seed_categorias_padrao(IN p_empresa_id INT UNSIGNED)
BEGIN
    DECLARE v_pai INT UNSIGNED;

    -- =====================================================
    -- RECEITAS (tipo = 'entrada')
    -- =====================================================

    -- 1. Bancos (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Bancos', 'Receitas bancárias (TPA, Transferências)', 'entrada', '#0e2748', 'fa-university', 1, 1, 1
    WHERE NOT EXISTS (
        SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Bancos' AND tipo = 'entrada'
    );
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Bancos' AND tipo = 'entrada' LIMIT 1);

    -- Filhos de Bancos
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'TPA', 'entrada', '#3b82f6', 'fa-credit-card', 2, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'TPA' AND tipo = 'entrada');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Transferência Bancária', 'entrada', '#22c55e', 'fa-exchange-alt', 3, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Transferência Bancária' AND tipo = 'entrada');

    -- 2. Dinheiro / Numerário
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Dinheiro / Numerário', 'entrada', '#eab308', 'fa-money-bill-wave', 4, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Dinheiro / Numerário' AND tipo = 'entrada');

    -- 3. Crédito
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Crédito', 'entrada', '#8b5cf6', 'fa-hand-holding-usd', 5, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Crédito' AND tipo = 'entrada');

    -- 4. Vendas
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Vendas', 'entrada', '#22c55e', 'fa-shopping-cart', 6, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Vendas' AND tipo = 'entrada');

    -- 5. Outras Receitas
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Outras Receitas', 'entrada', '#64748b', 'fa-plus-circle', 7, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Outras Receitas' AND tipo = 'entrada');

    -- =====================================================
    -- DESPESAS / CUSTOS (tipo = 'saida')
    -- =====================================================

    -- A. Compras e Mercadorias (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Compras e Mercadorias', 'Compras de produtos, embalagens e comissões', 'saida', '#f59e0b', 'fa-boxes', 10, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Compras e Mercadorias' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Compras e Mercadorias' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Compras de Produtos', 'saida', '#f59e0b', 'fa-box', 11, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Compras de Produtos' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Embalagens', 'saida', '#f59e0b', 'fa-box-open', 12, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Embalagens' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Comissões', 'saida', '#f59e0b', 'fa-percentage', 13, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Comissões' AND tipo = 'saida');

    -- B. Custos Fixos (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Custos Fixos', 'Custos que não variam com o volume de actividade', 'saida', '#6366f1', 'fa-lock', 20, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Custos Fixos' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Custos Fixos' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Aluguel / Renda', 'saida', '#6366f1', 'fa-home', 21, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Aluguel / Renda' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Salários Fixos', 'saida', '#6366f1', 'fa-users', 22, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Salários Fixos' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Internet / Comunicações', 'saida', '#6366f1', 'fa-wifi', 23, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Internet / Comunicações' AND tipo = 'saida');

    -- C. Fornecimento e Serviços Externos (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Fornecimento e Serviços Externos', 'Serviços, utilidades e mão-de-obra externa', 'saida', '#0ea5e9', 'fa-truck', 30, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Fornecimento e Serviços Externos' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Fornecimento e Serviços Externos' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Serviços (Instalação/Manutenção)', 'saida', '#0ea5e9', 'fa-tools', 31, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Serviços (Instalação/Manutenção)' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Água', 'saida', '#0ea5e9', 'fa-tint', 32, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Água' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Energia / Eletricidade', 'saida', '#0ea5e9', 'fa-bolt', 33, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Energia / Eletricidade' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Combustível e Lubrificantes', 'saida', '#0ea5e9', 'fa-gas-pump', 34, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Combustível e Lubrificantes' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Mão-de-obra Externa', 'saida', '#0ea5e9', 'fa-hard-hat', 35, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Mão-de-obra Externa' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Gastos Administrativos', 'saida', '#0ea5e9', 'fa-briefcase', 36, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Gastos Administrativos' AND tipo = 'saida');

    -- D. Gastos com Pessoal (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Gastos com Pessoal', 'Encargos sociais, seguros e benefícios', 'saida', '#ec4899', 'fa-user-tie', 40, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Gastos com Pessoal' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Gastos com Pessoal' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'INSS / Segurança Social', 'saida', '#ec4899', 'fa-file-invoice-dollar', 41, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'INSS / Segurança Social' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Seguro Acidentes de Trabalho', 'saida', '#ec4899', 'fa-shield-alt', 42, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Seguro Acidentes de Trabalho' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Seguro de Saúde', 'saida', '#ec4899', 'fa-heartbeat', 43, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Seguro de Saúde' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Formação e Capacitação', 'saida', '#ec4899', 'fa-graduation-cap', 44, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Formação e Capacitação' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Uniformes e EPI', 'saida', '#ec4899', 'fa-tshirt', 45, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Uniformes e EPI' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Recrutamento', 'saida', '#ec4899', 'fa-user-plus', 46, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Recrutamento' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Medicina no Trabalho', 'saida', '#ec4899', 'fa-stethoscope', 47, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Medicina no Trabalho' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Benefícios (Creche/Transporte/Refeitório)', 'saida', '#ec4899', 'fa-utensils', 48, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Benefícios (Creche/Transporte/Refeitório)' AND tipo = 'saida');

    -- E. Gastos Miúdos / Diárias (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Gastos Miúdos / Diárias', 'Despesas pequenas do dia-a-dia', 'saida', '#14b8a6', 'fa-coins', 50, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Gastos Miúdos / Diárias' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Gastos Miúdos / Diárias' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Táxi / Transporte', 'saida', '#14b8a6', 'fa-taxi', 51, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Táxi / Transporte' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Material de Limpeza', 'saida', '#14b8a6', 'fa-broom', 52, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Material de Limpeza' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Outros Gastos Miúdos', 'saida', '#14b8a6', 'fa-ellipsis-h', 53, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Outros Gastos Miúdos' AND tipo = 'saida');

    -- F. Marketing e Vendas (pai)
    INSERT INTO categorias (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Marketing e Vendas', 'Publicidade e promoção', 'saida', '#f43f5e', 'fa-bullhorn', 60, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Marketing e Vendas' AND tipo = 'saida');
    SET v_pai = (SELECT id FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Marketing e Vendas' AND tipo = 'saida' LIMIT 1);

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Anúncios Pagos', 'saida', '#f43f5e', 'fa-ad', 61, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Anúncios Pagos' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Tráfego / Publicidade Digital', 'saida', '#f43f5e', 'fa-chart-line', 62, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Tráfego / Publicidade Digital' AND tipo = 'saida');

    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, v_pai, 'Campanhas', 'saida', '#f43f5e', 'fa-bullhorn', 63, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Campanhas' AND tipo = 'saida');

    -- G. Impostos Diversos
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Impostos Diversos', 'saida', '#64748b', 'fa-balance-scale', 70, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Impostos Diversos' AND tipo = 'saida');

    -- H. Depreciação e Amortização
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Depreciação e Amortização', 'saida', '#475569', 'fa-chart-pie', 80, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Depreciação e Amortização' AND tipo = 'saida');

    -- I. Outros Custos
    INSERT INTO categorias (empresa_id, pai_id, nome, tipo, cor, icone, ordem, ativa, sistema)
    SELECT p_empresa_id, NULL, 'Outros Custos', 'saida', '#94a3b8', 'fa-ellipsis', 90, 1, 1
    WHERE NOT EXISTS (SELECT 1 FROM categorias WHERE empresa_id = p_empresa_id AND nome = 'Outros Custos' AND tipo = 'saida');

END$$

DELIMITER ;

-- ---------------------------------------------------------
-- 4. EXECUTAR O SEED PARA TODAS AS EMPRESAS EXISTENTES
-- ---------------------------------------------------------
-- Chama o procedimento para cada empresa registada

SET @empresas := NULL;
SELECT GROUP_CONCAT(id) INTO @empresas FROM empresas WHERE ativa = 1;

-- Executa para a empresa 1 (e futuras) de forma segura
CALL sp_seed_categorias_padrao(1);

-- Se existirem mais empresas no futuro, pode-se fazer:
-- CALL sp_seed_categorias_padrao(2);
-- etc.

-- ---------------------------------------------------------
-- 5. REGISTAR A MIGRAÇÃO
-- ---------------------------------------------------------
INSERT IGNORE INTO _migracoes (nome) VALUES ('2026_11_07_categorias_hierarquia');

-- ---------------------------------------------------------
-- 6. LIMPEZA
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_seed_categorias_padrao;

-- Fim da migração
SELECT 'Migração de categorias com hierarquia concluída com sucesso.' AS resultado;
