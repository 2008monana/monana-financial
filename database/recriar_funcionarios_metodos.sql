-- ============================================================
-- RECRIAR TABELAS: funcionarios e metodos_pagamento
-- (versões compatíveis com os Models/Controllers do sistema)
-- Execute este script no phpMyAdmin na base monana_financial
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. TABELA: funcionarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `funcionarios`;

CREATE TABLE `funcionarios` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) UNSIGNED NOT NULL,
  `filial_id` int(10) UNSIGNED DEFAULT NULL,
  `nome` varchar(150) NOT NULL,
  `nacionalidade` varchar(60) DEFAULT NULL,
  `bi_passaporte` varchar(40) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `salario_base` decimal(15,2) DEFAULT 0.00,
  `foto` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `observacoes` text DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_funcionario_empresa_nome` (`empresa_id`,`nome`),
  KEY `fk_func_empresa` (`empresa_id`),
  KEY `fk_func_filial` (`filial_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados de exemplo (preservando os funcionários já existentes)
INSERT INTO `funcionarios` (`id`, `empresa_id`, `nome`, `cargo`, `telefone`, `observacoes`, `estado`) VALUES
(1, 1, 'Boss', 'Proprietário / Patrão', NULL, 'Aparece em todas as folhas com valor diário de 500 Kz', 'activo'),
(2, 1, 'René', 'Gerente', NULL, 'Valor diário de 2.000 Kz', 'activo'),
(3, 1, 'Operativo', 'Funcionário Operacional', NULL, 'Valor diário de 2.000 Kz', 'activo'),
(4, 1, 'Cebola', 'Funcionário', NULL, 'Valor diário de 2.000 Kz; ocasionalmente 30.000 Kz para compras', 'activo'),
(5, 1, 'Betânia', 'Funcionária', NULL, 'Valor diário variável 1.000–2.000 Kz', 'activo'),
(6, 1, 'Ludmila', 'Funcionária', NULL, 'Valor diário de 500 Kz', 'activo'),
(7, 1, 'Paula Pinto', 'Funcionária', NULL, 'Aparece como Paula / Paula Pinto', 'activo'),
(8, 1, 'Paula Capitango', 'Funcionária', NULL, 'Aparece como Paula Cap. em algumas folhas', 'activo'),
(9, 1, 'Odete', 'Funcionária', NULL, 'Valor diário de 2.000 Kz', 'activo'),
(10, 1, 'Chana', 'Funcionária', NULL, 'Valor diário de 2.000 Kz', 'activo'),
(11, 1, 'Leonora', 'Funcionária', NULL, 'Valor diário de 2.000 Kz', 'activo'),
(12, 1, 'Alfocina', 'Funcionária', NULL, 'Valor diário de 1.000 Kz', 'activo'),
(13, 1, 'Maria', 'Funcionária', NULL, 'Aparece em Julho/Agosto', 'activo'),
(14, 1, 'Alice', 'Funcionária', NULL, 'Aparece na coluna Saldo', 'activo'),
(15, 1, 'Pinto', 'Funcionário', NULL, 'Aparece na coluna de gastos diários', 'activo'),
(16, 1, 'Mota', 'Funcionário', NULL, 'Aparece na coluna de gastos diários', 'activo');

-- ------------------------------------------------------------
-- 2. TABELA: metodos_pagamento
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `metodos_pagamento`;

CREATE TABLE `metodos_pagamento` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) UNSIGNED DEFAULT NULL,
  `nome` varchar(60) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `icone` varchar(40) DEFAULT NULL,
  `taxa` decimal(5,2) NOT NULL DEFAULT 0.00,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_metodo_empresa` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Métodos de pagamento padrão da empresa 1
INSERT INTO `metodos_pagamento` (`id`, `empresa_id`, `nome`, `descricao`, `icone`, `taxa`, `ordem`, `ativo`) VALUES
(1, 1, 'Numerário', 'Pagamentos em dinheiro vivo', 'fa-money-bill-wave', 0.00, 1, 1),
(2, 1, 'Transferência Bancária', 'Transferências entre contas bancárias', 'fa-building-columns', 0.00, 2, 1),
(3, 1, 'TPA - BCA', 'Pagamentos por terminal do Banco de Comércio e Indústria', 'fa-credit-card', 0.00, 3, 1),
(4, 1, 'TPA - Keve', 'Pagamentos por terminal do Banco Keve', 'fa-credit-card', 0.00, 4, 1),
(5, 1, 'TPA - SOL', 'Pagamentos por terminal do Banco Sol', 'fa-credit-card', 0.00, 5, 1),
(6, 1, 'ZAP', 'Pagamentos via carteira móvel ZAP', 'fa-mobile-alt', 0.00, 6, 1),
(7, 1, 'Multicaixa Express', 'Pagamentos via app Multicaixa Express', 'fa-mobile-screen-button', 0.00, 7, 1),
(8, 1, 'Cheque', 'Pagamentos através de cheques', 'fa-file-invoice-dollar', 0.00, 8, 1),
(9, 1, 'Outro', 'Outros métodos de pagamento', 'fa-ellipsis-h', 0.00, 9, 1);

-- ------------------------------------------------------------
-- 3. Restaurar chave estrangeira de transacoes -> funcionarios
--    (foi removida temporariamente ao apagar a tabela)
-- ------------------------------------------------------------
ALTER TABLE `transacoes`
  ADD CONSTRAINT `fk_transacoes_funcionario` FOREIGN KEY (`funcionario_id`)
  REFERENCES `funcionarios` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIM — após executar, recarregue as páginas:
--   /public/funcionarios
--   /public/metodos-pagamento
-- ============================================================
