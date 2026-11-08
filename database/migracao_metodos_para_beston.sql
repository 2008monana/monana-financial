-- =====================================================================
-- MonanaFinancial — Script: métodos de pagamento padrão → Farmácia Beston
-- ---------------------------------------------------------------------
-- O sistema NÃO deve ter métodos de pagamento globais/"padrão"
-- (empresa_id NULL). Todos os métodos pertencem a uma empresa.
-- Este script:
--   1. Move TODOS os métodos sem empresa (globais) para a Farmácia Beston;
--   2. Remove duplicados (mesmo nome já existente na Beston);
--   3. Garante que existe um conjunto base de métodos na Beston;
--   4. Zera a flag `padrao` (coluna opcional) para eliminar o conceito
--      de "método padrão do sistema".
-- Execute no phpMyAdmin (base: monana_financial). É seguro repetir (idempotente).
-- =====================================================================

SET @beston := (SELECT id FROM empresas WHERE nome LIKE '%beston%' ORDER BY id LIMIT 1);

-- Fallback: se não encontrar pelo nome, usa a empresa 1.
SET @beston := IFNULL(@beston, 1);

SELECT CONCAT('Farmácia Beston = empresa_id ', @beston) AS destino;

-- ---------------------------------------------------------------------
-- 0. Tabela de trabalho com o conjunto base de métodos padrão
-- ---------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_metodos_base;
CREATE TEMPORARY TABLE tmp_metodos_base (
  nome VARCHAR(100) NOT NULL PRIMARY KEY,
  icone VARCHAR(60) NOT NULL,
  ordem INT NOT NULL
);
INSERT INTO tmp_metodos_base (nome, icone, ordem) VALUES
('Numerário',              'fa-money-bill-wave',        1),
('Transferência Bancária', 'fa-building-columns',       2),
('TPA - BCA',              'fa-credit-card',            3),
('TPA - Keve',             'fa-credit-card',            4),
('TPA - SOL',              'fa-credit-card',            5),
('ZAP',                    'fa-mobile-alt',             6),
('Multicaixa Express',     'fa-mobile-screen-button',   7),
('Cheque',                 'fa-file-invoice-dollar',    8),
('Outro',                  'fa-ellipsis-h',             9);

-- ---------------------------------------------------------------------
-- 1. Eliminar métodos globais duplicados (já existem na Beston c/ mesmo nome)
-- ---------------------------------------------------------------------
DELETE mp FROM metodos_pagamento mp
WHERE mp.empresa_id IS NULL
  AND EXISTS (
    SELECT 1 FROM (SELECT * FROM metodos_pagamento) x
    WHERE x.empresa_id = @beston AND x.nome = mp.nome
  );

-- ---------------------------------------------------------------------
-- 2. Mover os restantes métodos globais para a Farmácia Beston
-- ---------------------------------------------------------------------
UPDATE metodos_pagamento SET empresa_id = @beston WHERE empresa_id IS NULL;

-- ---------------------------------------------------------------------
-- 3. Garantir o conjunto base na Beston (insere apenas os que faltam)
--    Compatível com esquemas antigos (sem colunas descricao/ordem/padrao).
-- ---------------------------------------------------------------------
INSERT INTO metodos_pagamento (empresa_id, nome, icone)
SELECT @beston, t.nome, t.icone
FROM tmp_metodos_base t
WHERE NOT EXISTS (
  SELECT 1 FROM metodos_pagamento mp
  WHERE mp.empresa_id = @beston AND mp.nome = t.nome
);

-- Actualizar ícone/ordem/descrição se as colunas existirem (dinâmico):
SET @s := IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='metodos_pagamento' AND COLUMN_NAME='ordem'),
  'UPDATE metodos_pagamento mp JOIN tmp_metodos_base t ON t.nome = mp.nome SET mp.ordem = t.ordem WHERE mp.empresa_id = @beston',
  'SELECT "coluna ordem inexistente — ignorada" AS aviso');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='metodos_pagamento' AND COLUMN_NAME='descricao'),
  'UPDATE metodos_pagamento mp JOIN tmp_metodos_base t ON t.nome = mp.nome SET mp.descricao = CONCAT(''Método padrão da '', (SELECT nome FROM empresas WHERE id = @beston)) WHERE mp.empresa_id = @beston AND (mp.descricao IS NULL OR mp.descricao = '''')',
  'SELECT "coluna descricao inexistente — ignorada" AS aviso');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 4. Remover o conceito de "padrão global": zera a flag padrao (se existir)
-- ---------------------------------------------------------------------
SET @s := IF(
  EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='metodos_pagamento' AND COLUMN_NAME='padrao'),
  'UPDATE metodos_pagamento SET padrao = 0',
  'SELECT "coluna padrao inexistente — nada a fazer" AS aviso');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------------------------------------------------------------------
-- 5. Verificação final
-- ---------------------------------------------------------------------
SELECT mp.id, mp.empresa_id, e.nome AS empresa, mp.nome, mp.estado
FROM metodos_pagamento mp
LEFT JOIN empresas e ON e.id = mp.empresa_id
ORDER BY mp.empresa_id, mp.nome;

-- ✔ Concluído: nenhum método fica sem empresa; todos os "padrões" estão na Beston.
