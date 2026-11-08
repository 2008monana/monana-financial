-- =============================================================
-- MonanaFinancial - Acesso aos módulos Funcionários e Métodos de Pagamento
-- Execute este script no phpMyAdmin (base: monana_financial)
-- =============================================================

-- 1. Garantir que as tabelas existem (não falha se já existirem)
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `empresa_id` INT NOT NULL,
  `filial_id` INT NULL,
  `nome` VARCHAR(150) NOT NULL,
  `cargo` VARCHAR(100) NULL,
  `departamento` VARCHAR(100) NULL,
  `telefone` VARCHAR(30) NULL,
  `email` VARCHAR(150) NULL,
  `data_admissao` DATE NULL,
  `salario_base` DECIMAL(14,2) NULL DEFAULT 0,
  `foto` VARCHAR(255) NULL,
  `estado` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `observacoes` TEXT NULL,
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`empresa_id`),
  INDEX (`filial_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `metodos_pagamento` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `empresa_id` INT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  `icone` VARCHAR(60) NULL,
  `padrao` TINYINT(1) NOT NULL DEFAULT 0,
  `estado` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Registar os módulos no catálogo (para o sistema de permissões por utilizador)
INSERT IGNORE INTO `modulos` (`nome`, `descricao`, `icone`, `ordem`) VALUES
('funcionarios', 'Funcionários', 'fa-user-tie', 11),
('metodos_pagamento', 'Métodos de Pagamento', 'fa-money-bill-wave', 16);

-- Garante que ficam ativos
UPDATE `modulos` SET `ativo` = 1 WHERE `nome` IN ('funcionarios', 'metodos_pagamento');

-- 3. Dar acesso automático a TODOS os Admins de Empresa
--    (Super Admin já tem acesso total por código, não precisa de registo)
INSERT IGNORE INTO `usuario_modulo_permissoes` (`usuario_id`, `modulo_id`)
SELECT u.id, m.id
FROM `usuarios` u
CROSS JOIN `modulos` m
WHERE u.perfil = 'admin_empresa'
  AND m.nome IN ('funcionarios', 'metodos_pagamento');

-- 4. Métodos de pagamento padrão (globais, empresa_id NULL)
INSERT INTO `metodos_pagamento` (`empresa_id`, `nome`, `icone`, `padrao`, `estado`)
SELECT NULL, t.nome, t.icone, 1, 'ativo'
FROM (
  SELECT 'Numerário' AS nome, 'fa-money-bill' AS icone UNION ALL
  SELECT 'Transferência Bancária', 'fa-right-left' UNION ALL
  SELECT 'TPA / Cartão', 'fa-credit-card' UNION ALL
  SELECT 'Cheque', 'fa-file-invoice-dollar' UNION ALL
  SELECT 'Multicaixa Express', 'fa-mobile-screen'
) t
WHERE NOT EXISTS (
  SELECT 1 FROM `metodos_pagamento` mp
  WHERE mp.empresa_id IS NULL AND mp.nome = t.nome
);

-- =============================================================
-- Após executar: faça logout e login novamente para atualizar o menu.
-- =============================================================
