-- =========================================================
-- MONANAFINANCIAL — SCHEMA DA BASE DE DADOS
-- Base de dados: monana_financial
-- =========================================================

CREATE DATABASE IF NOT EXISTS monana_financial
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE monana_financial;

-- ---------------------------------------------------------
-- EMPRESAS
-- ---------------------------------------------------------
CREATE TABLE empresas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    nif VARCHAR(30) DEFAULT NULL,
    logotipo VARCHAR(255) DEFAULT NULL,
    email_contacto VARCHAR(150) DEFAULT NULL,
    telefone VARCHAR(30) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- FILIAIS
-- ---------------------------------------------------------
CREATE TABLE filiais (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(150) NOT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(30) DEFAULT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_filiais_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- USUARIOS
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED DEFAULT NULL, -- NULL para super administrador
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('super_admin','admin_empresa','usuario_interno','visualizador') NOT NULL DEFAULT 'usuario_interno',
    telefone VARCHAR(30) DEFAULT NULL,
    cargo VARCHAR(100) DEFAULT NULL,
    biografia TEXT DEFAULT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    primeiro_acesso TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- USUARIO_FILIAIS (a quais filiais um utilizador tem acesso)
-- ---------------------------------------------------------
CREATE TABLE usuario_filiais (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    filial_id INT UNSIGNED NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_uf_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_uf_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_filial (usuario_id, filial_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- USUARIO_PERMISSOES (permissões granulares)
-- ---------------------------------------------------------
CREATE TABLE usuario_permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    ver_todas_filiais TINYINT(1) NOT NULL DEFAULT 0,
    criar_lancamentos TINYINT(1) NOT NULL DEFAULT 0,
    editar_proprios_lancamentos TINYINT(1) NOT NULL DEFAULT 0,
    eliminar_proprios_lancamentos TINYINT(1) NOT NULL DEFAULT 0,
    ver_relatorios_consolidados TINYINT(1) NOT NULL DEFAULT 0,
    ver_relatorios_filial TINYINT(1) NOT NULL DEFAULT 0,
    exportar_relatorios TINYINT(1) NOT NULL DEFAULT 0,
    importar_excel TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_up_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_permissoes (usuario_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CATEGORIAS (entradas/saídas)
-- ---------------------------------------------------------
CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL,
    cor VARCHAR(20) DEFAULT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categorias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TRANSACOES
-- ---------------------------------------------------------
CREATE TABLE transacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    filial_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL, -- quem lançou
    tipo ENUM('venda','devolucao','compra','custo') NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    valor DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    metodo_pagamento ENUM('numerario','transferencia','tpa','outro') NOT NULL DEFAULT 'numerario',
    data_transacao DATE NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_transacoes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_transacoes_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE,
    CONSTRAINT fk_transacoes_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT fk_transacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_transacoes_data (data_transacao),
    INDEX idx_transacoes_filial_data (filial_id, data_transacao)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- RESUMOS_MENSAIS (cache/consolidado por filial e mês)
-- ---------------------------------------------------------
CREATE TABLE resumos_mensais (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    filial_id INT UNSIGNED NOT NULL,
    ano SMALLINT UNSIGNED NOT NULL,
    mes TINYINT UNSIGNED NOT NULL,
    total_vendas DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_devolucoes DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_compras DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_custos DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    saldo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_resumos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_resumos_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE CASCADE,
    UNIQUE KEY uk_resumo_filial_mes (filial_id, ano, mes)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- LOGS_AUDITORIA
-- ---------------------------------------------------------
CREATE TABLE logs_auditoria (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED DEFAULT NULL,
    acao VARCHAR(100) NOT NULL,
    prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',
    tabela_afetada VARCHAR(60) DEFAULT NULL,
    registo_id INT UNSIGNED DEFAULT NULL,
    dados_antigos JSON DEFAULT NULL,
    dados_novos JSON DEFAULT NULL,
    detalhes TEXT DEFAULT NULL,
    ip_origem VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    motivo VARCHAR(500) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- IMPORTACOES (histórico de importações de Excel)
-- ---------------------------------------------------------
CREATE TABLE importacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    nome_arquivo VARCHAR(255) NOT NULL,
    total_linhas INT UNSIGNED NOT NULL DEFAULT 0,
    linhas_importadas INT UNSIGNED NOT NULL DEFAULT 0,
    linhas_falhadas INT UNSIGNED NOT NULL DEFAULT 0,
    log_erros TEXT DEFAULT NULL,
    status ENUM('processando','concluida','falhou') NOT NULL DEFAULT 'processando',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_importacoes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_importacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CONFIGURACOES (globais ou por empresa)
-- ---------------------------------------------------------
CREATE TABLE configuracoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NULL,
    chave VARCHAR(100) NOT NULL,
    valor TEXT DEFAULT NULL,
    tipo ENUM('texto','numero','booleano','json','secreto') NOT NULL DEFAULT 'texto',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_configuracoes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_configuracao_escopo (empresa_id, chave)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- REDEFINICOES_SENHA
-- ---------------------------------------------------------
CREATE TABLE redefinicoes_senha (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_redefinicoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PERFIS_USUARIO (dados adicionais de perfil, se necessário separar)
-- ---------------------------------------------------------
CREATE TABLE perfis_usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    preferencias JSON DEFAULT NULL,
    tema ENUM('claro','escuro') NOT NULL DEFAULT 'claro',
    idioma VARCHAR(10) NOT NULL DEFAULT 'pt',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfis_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_perfil_usuario (usuario_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- METODOS DE PAGAMENTO (por empresa)
-- ---------------------------------------------------------
CREATE TABLE metodos_pagamento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
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

-- ---------------------------------------------------------
-- CATEGORIAS DE SAIDA (tipos de gasto, por empresa)
-- ---------------------------------------------------------
CREATE TABLE categorias_saida (
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

-- ---------------------------------------------------------
-- MODULOS E PERMISSOES POR MODULO (controle de acesso administrativo)
-- ---------------------------------------------------------
CREATE TABLE modulos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(150) NOT NULL,
    icone VARCHAR(50) DEFAULT 'fa-puzzle-piece',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ordem INT UNSIGNED DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuario_modulo_permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    modulo_id INT UNSIGNED NOT NULL,
    pode_acessar TINYINT(1) NOT NULL DEFAULT 1,
    pode_editar TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ump_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ump_modulo FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_modulo (usuario_id, modulo_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- BACKUPS (registos de cópias de segurança manuais/automáticas)
-- ---------------------------------------------------------
CREATE TABLE backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('manual', 'automatico') NOT NULL DEFAULT 'manual',
    arquivo VARCHAR(255) NOT NULL,
    caminho VARCHAR(500) NOT NULL,
    tamanho BIGINT UNSIGNED DEFAULT 0,
    tabelas TEXT DEFAULT NULL,
    empresa_id INT UNSIGNED DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    status ENUM('concluido', 'em_processamento', 'falha') NOT NULL DEFAULT 'concluido',
    mensagem TEXT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_backup_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_backup_criado_em (criado_em)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- MODULO DE ASSINATURAS (SaaS): planos, assinaturas, pagamentos e avisos
-- ---------------------------------------------------------
CREATE TABLE planos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(500) DEFAULT NULL,
    preco DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    moeda VARCHAR(10) NOT NULL DEFAULT 'AOA',
    periodo ENUM('mensal','trimestral','semestral','anual') NOT NULL DEFAULT 'mensal',
    limite_empresas INT UNSIGNED DEFAULT 1,
    limite_filiais INT UNSIGNED DEFAULT 1,
    limite_usuarios INT UNSIGNED DEFAULT 5,
    limite_transacoes_mes INT UNSIGNED DEFAULT 0,
    funcionalidades JSON DEFAULT NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE assinaturas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    plano_id INT UNSIGNED NOT NULL,
    inicio DATE NOT NULL,
    fim DATE NOT NULL,
    valor DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    estado ENUM('ativa','pausada','expirada','cancelada','teste') NOT NULL DEFAULT 'ativa',
    renovacao_automatica TINYINT(1) NOT NULL DEFAULT 0,
    observacoes VARCHAR(500) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assin_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_assin_plano FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT,
    INDEX idx_assin_estado_fim (estado, fim)
) ENGINE=InnoDB;

CREATE TABLE assinatura_pagamentos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id INT UNSIGNED NOT NULL,
    valor DECIMAL(15,2) NOT NULL,
    metodo VARCHAR(50) DEFAULT NULL,
    referencia VARCHAR(150) DEFAULT NULL,
    pago_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comprovativo VARCHAR(255) DEFAULT NULL,
    registado_por INT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_aspag_assinatura FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE,
    INDEX idx_aspag_data (pago_em)
) ENGINE=InnoDB;

CREATE TABLE assinatura_avisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id INT UNSIGNED NOT NULL,
    tipo ENUM('expiracao','vencimento','expirada','renovada') NOT NULL,
    dias_restantes INT DEFAULT NULL,
    enviado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    canal ENUM('email','sistema') NOT NULL DEFAULT 'sistema',
    CONSTRAINT fk_aviso_assinatura FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_aviso_dia (assinatura_id, tipo, dias_restantes)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- TENTATIVAS DE LOGIN (segurança / bloqueio por força bruta)
-- ---------------------------------------------------------
CREATE TABLE tentativas_login (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    dispositivo VARCHAR(150) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    tentado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentativa_email_data (email, tentado_em)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- PERFIS DE UTILIZADOR EXTENDIDOS (foto via Cloudinary, contactos)
-- ---------------------------------------------------------
CREATE TABLE perfis_usuarios (
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
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- NOTIFICACOES
-- ---------------------------------------------------------
CREATE TABLE notificacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    tipo ENUM('alerta','sucesso','aviso','erro') NOT NULL DEFAULT 'alerta',
    titulo VARCHAR(150) NOT NULL,
    mensagem VARCHAR(500) NOT NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    link VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notificacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_notificacoes_usuario_lida (usuario_id, lida)
) ENGINE=InnoDB;

-- =========================================================
-- DADOS INICIAIS (SEED) — Super Administrador
-- Senha: Admin@123  (ALTERAR IMEDIATAMENTE APÓS PRIMEIRO ACESSO)
-- Hash gerado com password_hash() do PHP (bcrypt)
-- =========================================================
INSERT INTO usuarios (empresa_id, nome, email, senha_hash, perfil, ativo, primeiro_acesso)
VALUES (
    NULL,
    'Super Administrador',
    'admin@monanafinancial.com',
    '$2y$10$jEKCB2T7hrHFhiIFMX3t1.mKLy7DPlLeST3UMjXN1T0uvUbyYgcjS', -- Admin@123
    'super_admin',
    1,
    1
);
