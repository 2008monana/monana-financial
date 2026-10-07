-- =========================================================
-- MIGRAÇÃO: categorias_saida + perfis_usuarios + tentativas_login
-- Resolve erros do tipo:
--   Table 'monana_financial.categorias_saida' doesn't exist
--   Table 'monana_financial.perfis_usuarios' doesn't exist
-- Execute após o schema.sql (ou em bases já existentes).
-- Todas as instruções são idempotentes (IF NOT EXISTS).
-- =========================================================

USE monana_financial;

-- ---------------------------------------------------------
-- CATEGORIAS DE SAIDA (tipos de gasto, por empresa)
-- Usada por: models/CategoriaSaida.php, helpers/BackupHelper.php,
--            helpers/AuditoriaHelper.php
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias_saida (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    cor VARCHAR(20) DEFAULT '#dc2626',
    icone VARCHAR(50) DEFAULT 'fa-tag',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cs_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_nome_saida (empresa_id, nome),
    INDEX idx_saida_empresa (empresa_id, ativo)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Categorias de saída padrão para todas as empresas existentes
INSERT INTO categorias_saida (empresa_id, nome, cor, icone)
SELECT e.id, c.nome, c.cor, c.icone
FROM empresas e
CROSS JOIN (
    SELECT 'Salários' AS nome, '#dc2626' AS cor, 'fa-users' AS icone UNION ALL
    SELECT 'Renda',           '#ea580c', 'fa-house'          UNION ALL
    SELECT 'Fornecedores',    '#d97706', 'fa-truck'          UNION ALL
    SELECT 'Água / Energia',  '#0891b2', 'fa-bolt'           UNION ALL
    SELECT 'Combustível',     '#7c3aed', 'fa-gas-pump'       UNION ALL
    SELECT 'Manutenção',      '#2563eb', 'fa-screwdriver-wrench' UNION ALL
    SELECT 'Impostos',        '#475569', 'fa-file-invoice-dollar' UNION ALL
    SELECT 'Outros Gastos',   '#64748b', 'fa-ellipsis'
) c
WHERE NOT EXISTS (
    SELECT 1 FROM categorias_saida cs
    WHERE cs.empresa_id = e.id AND cs.nome = c.nome
);

-- ---------------------------------------------------------
-- PERFIS DE UTILIZADOR EXTENDIDOS (foto via Cloudinary, contactos)
-- Usada por: models/PerfilUsuario.php
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfis_usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    foto_url VARCHAR(500) DEFAULT NULL,
    foto_public_id VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(30) DEFAULT NULL,
    cargo VARCHAR(100) DEFAULT NULL,
    departamento VARCHAR(100) DEFAULT NULL,
    biografia TEXT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pu_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_pu_usuario (usuario_id)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- TENTATIVAS DE LOGIN (bloqueio por força bruta)
-- Usada por: models/TentativaLogin.php
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS tentativas_login (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    dispositivo VARCHAR(150) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    tentado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentativa_email_data (email, tentado_em)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
