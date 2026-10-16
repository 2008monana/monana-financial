-- =====================================================================
-- MONANAFINANCIAL — BASE DE DADOS COMPLETA (reconstrução de emergência)
-- =====================================================================
-- Gerado a partir da arquitectura actual do código (models/, helpers/,
-- controllers/, core/auto_migracoes.php), consolidando schema.sql e todas
-- as migrações, com dados-semente mínimos para o sistema arrancar.
--
-- COMO USAR (phpMyAdmin ou linha de comandos):
--   1. Importe ESTE ficheiro por completo. Ele APAGA a base existente
--      `monana_financial` e recria tudo do zero (estrutura + dados base).
--   2. Faça login em:  http://localhost/monana-financial/public/auth/login
--        Super Admin:  admin@monanafinancial.com  /  Admin@123
--      (altere a senha imediatamente após o primeiro acesso!)
--   3. OPCIONAL — dados de demonstração MAMABAR (empresa, filiais Prenda/
--      Viana, categorias, transações do mês corrente): importe depois o
--      ficheiro  seed_exemplo.sql.
--
-- Tabelas incluídas (26 + tabela de controlo de auto-migrações):
--   empresas, filiais, usuarios, usuario_filiais, usuario_permissoes,
--   categorias, categorias_saida, transacoes, funcionarios,
--   metodos_pagamento, resumos_mensais, importacoes, configuracoes,
--   logs_auditoria, redefinicoes_senha, perfis_usuario, perfis_usuarios,
--   modulos, usuario_modulo_permissoes, backups, planos, assinaturas,
--   assinatura_pagamentos, assinatura_avisos, tentativas_login,
--   notificacoes, _migracoes
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS monana_financial;
CREATE DATABASE monana_financial
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- USUARIOS  (empresa_id NULL apenas para o Super Administrador)
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED DEFAULT NULL,
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
    CONSTRAINT fk_usuarios_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_usuarios_perfil (perfil),
    INDEX idx_usuarios_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- USUARIO_PERMISSOES (permissões granulares — 7 colunas; a antiga
-- importar_excel foi removida pela migração dos módulos administrativos)
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
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_up_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_permissoes (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- CATEGORIAS (entradas/saídas clássicas)
-- ---------------------------------------------------------
CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL,
    cor VARCHAR(20) DEFAULT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categorias_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    INDEX idx_categorias_empresa_tipo (empresa_id, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- CATEGORIAS DE SAÍDA (tipos de gasto, por empresa)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- FUNCIONÁRIOS (aparecem nos movimentos/planilha diária)
-- ---------------------------------------------------------
CREATE TABLE funcionarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    filial_id INT UNSIGNED DEFAULT NULL,
    nome VARCHAR(150) NOT NULL,
    nacionalidade VARCHAR(60) DEFAULT NULL,
    bi_passaporte VARCHAR(40) DEFAULT NULL,
    endereco VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(30) DEFAULT NULL,
    email VARCHAR(120) DEFAULT NULL,
    cargo VARCHAR(100) DEFAULT NULL,
    departamento VARCHAR(100) DEFAULT NULL,
    data_admissao DATE DEFAULT NULL,
    salario_base DECIMAL(15,2) DEFAULT 0.00,
    foto VARCHAR(255) DEFAULT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    observacoes TEXT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_func_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_func_filial FOREIGN KEY (filial_id) REFERENCES filiais(id) ON DELETE SET NULL,
    UNIQUE KEY uk_funcionario_empresa_nome (empresa_id, nome),
    INDEX idx_funcionarios_estado (empresa_id, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- MÉTODOS DE PAGAMENTO (por empresa; colunas extras exigidas pelo
-- modelo MetodoPagamento / auto_migracoes: descricao, categoria, cor, ordem)
-- ---------------------------------------------------------
CREATE TABLE metodos_pagamento (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    codigo VARCHAR(50) NOT NULL,
    tipo ENUM('entrada','saida') NOT NULL DEFAULT 'entrada',
    categoria VARCHAR(50) DEFAULT NULL,
    cor VARCHAR(20) DEFAULT '#0e2748',
    icone VARCHAR(50) DEFAULT 'fa-credit-card',
    ordem INT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mp_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_empresa_codigo (empresa_id, codigo),
    INDEX idx_empresa_tipo (empresa_id, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- TRANSACOES (inclui funcionario_id, ausente no schema original)
-- ---------------------------------------------------------
CREATE TABLE transacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    filial_id INT UNSIGNED NOT NULL,
    categoria_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL, -- quem lançou
    funcionario_id INT UNSIGNED DEFAULT NULL,
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
    CONSTRAINT fk_transacoes_funcionario FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE SET NULL,
    INDEX idx_transacoes_data (data_transacao),
    INDEX idx_transacoes_filial_data (filial_id, data_transacao),
    INDEX idx_transacoes_empresa_data (empresa_id, data_transacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- RESUMOS MENSAIS (cache de consolidação por filial/mês)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- IMPORTACOES (histórico de importações Excel — usado pelo
-- ImportacaoController; colunas token_reversao/tempo_processamento_ms)
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
    status ENUM('processando','concluida','falhou','revertida') NOT NULL DEFAULT 'processando',
    token_reversao CHAR(36) DEFAULT NULL,
    tempo_processamento_ms INT UNSIGNED DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_importacoes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_importacoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- CONFIGURACOES (globais quando empresa_id IS NULL, ou por empresa)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- LOGS DE AUDITORIA (estrutura completa exigida pelo AuditoriaHelper)
-- ---------------------------------------------------------
CREATE TABLE logs_auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED DEFAULT NULL,
    empresa_id INT UNSIGNED DEFAULT NULL,
    acao VARCHAR(100) NOT NULL,
    prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',
    tabela_afetada VARCHAR(100) DEFAULT NULL,
    registo_id INT UNSIGNED DEFAULT NULL,
    dados_antigos LONGTEXT DEFAULT NULL,
    dados_novos LONGTEXT DEFAULT NULL,
    detalhes TEXT DEFAULT NULL,
    ip_origem VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    motivo VARCHAR(500) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_logs_criado_em (criado_em),
    INDEX idx_logs_acao (acao),
    INDEX idx_logs_empresa (empresa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- REDEFINIÇÕES DE SENHA (recuperação por e-mail)
-- ---------------------------------------------------------
CREATE TABLE redefinicoes_senha (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL UNIQUE,
    expira_em DATETIME NOT NULL,
    usado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_redefinicoes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- PERFIS_USUARIO (preferências antigas — mantido por compatibilidade)
-- ---------------------------------------------------------
CREATE TABLE perfis_usuario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    preferencias JSON DEFAULT NULL,
    tema ENUM('claro','escuro') NOT NULL DEFAULT 'claro',
    idioma VARCHAR(10) NOT NULL DEFAULT 'pt',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfis_usuario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_perfil_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- PERFIS_USUARIOS (perfil estendido — foto Cloudinary, contactos)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- MODULOS (controle de acesso administrativo) + semente padrão
-- ---------------------------------------------------------
CREATE TABLE modulos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao VARCHAR(150) NOT NULL,
    icone VARCHAR(50) DEFAULT 'fa-puzzle-piece',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ordem INT UNSIGNED DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO modulos (nome, descricao, icone, ativo, ordem) VALUES
('dashboard',          'Dashboard',            'fa-house',                 1, 1),
('movimentos',         'Movimentos',           'fa-list-ul',               1, 2),
('relatorios',         'Relatórios',           'fa-chart-column',          1, 3),
('planilha',           'Planilha',             'fa-table',                 1, 4),
('categorias',         'Categorias',           'fa-tags',                  1, 5),
('empresas',           'Empresas',             'fa-building',              1, 6),
('filiais',            'Filiais',              'fa-code-branch',           1, 7),
('usuarios',           'Utilizadores',         'fa-users',                 1, 8),
('fecho_diario',       'Fecho Diário',         'fa-calendar-check',        1, 9),
('backups',            'Backups',              'fa-database',              1, 10),
('metodos_pagamento',  'Métodos de Pagamento', 'fa-credit-card',           1, 11),
('logs',               'Logs de Auditoria',    'fa-clipboard-list',        1, 12),
('configuracoes',      'Configurações',        'fa-gear',                  1, 13),
('perfil',             'Meu Perfil',           'fa-user-cog',              1, 14),
('notificacoes',       'Notificações',         'fa-bell',                  1, 15);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- BACKUPS (cópias de segurança manuais/automáticas)
-- ---------------------------------------------------------
CREATE TABLE backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('manual','automatico') NOT NULL DEFAULT 'manual',
    arquivo VARCHAR(255) NOT NULL,
    caminho VARCHAR(500) NOT NULL,
    tamanho BIGINT UNSIGNED DEFAULT 0,
    tabelas TEXT DEFAULT NULL,
    empresa_id INT UNSIGNED DEFAULT NULL,
    criado_por INT UNSIGNED DEFAULT NULL,
    status ENUM('concluido','em_processamento','falha') NOT NULL DEFAULT 'concluido',
    mensagem TEXT DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_backup_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE SET NULL,
    CONSTRAINT fk_backup_criador FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_backup_criado_em (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- ASSINATURAS (SaaS): planos, assinaturas, pagamentos, avisos
-- (versão da migracao_modulo_assinaturas.sql — é esta que o código usa)
-- ---------------------------------------------------------
CREATE TABLE planos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(80) NOT NULL,
    duracao_dias INT UNSIGNED NULL,          -- NULL = sem fim (gratuito)
    preco DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ordem INT UNSIGNED NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assinaturas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id INT UNSIGNED NOT NULL,
    plano_id INT UNSIGNED NOT NULL,
    estado ENUM('activa','pendente_pagamento','encerrada') NOT NULL DEFAULT 'activa',
    valor_acordado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    inicio DATETIME NULL,
    fim DATETIME NULL,                        -- NULL = sem fim (só gratuito)
    carencia_ate DATETIME NULL,
    bloqueada_manual TINYINT(1) NOT NULL DEFAULT 0,
    observacoes TEXT NULL,
    criado_por INT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_assinaturas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_assinaturas_plano FOREIGN KEY (plano_id) REFERENCES planos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_assinaturas_usuario FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_assinaturas_empresa_estado (empresa_id, estado),
    INDEX idx_assinaturas_fim (fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assinatura_pagamentos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assinatura_avisos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assinatura_id INT UNSIGNED NOT NULL,
    tipo ENUM('7d','3d','1d','carencia','bloqueio') NOT NULL,
    enviado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aviso_assoc FOREIGN KEY (assinatura_id) REFERENCES assinaturas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_aviso (assinatura_id, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- TENTATIVAS DE LOGIN (proteção contra força bruta)
-- ---------------------------------------------------------
CREATE TABLE tentativas_login (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    dispositivo VARCHAR(150) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    tentado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tentativa_email_data (email, tentado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- _MIGRACOES (tabela de controlo das auto-migrações em runtime)
-- ---------------------------------------------------------
CREATE TABLE _migracoes (
    nome VARCHAR(100) PRIMARY KEY,
    aplicada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Carimbo da auto-migração de Setembro/2026 (colunas já criadas acima,
-- evita ALTER desnecessário no primeiro pedido)
INSERT INTO _migracoes (nome) VALUES ('auto_2026_09_metodos_pagamento');

-- =====================================================================
-- DADOS INICIAIS (SEED)
-- =====================================================================

-- Super Administrador — senha: Admin@123  (ALTERAR NO PRIMEIRO ACESSO)
INSERT INTO usuarios (empresa_id, nome, email, senha_hash, perfil, ativo, primeiro_acesso) VALUES
(NULL, 'Super Administrador', 'admin@monanafinancial.com',
 '$2y$10$jEKCB2T7hrHFhiIFMX3t1.mKLy7DPlLeST3UMjXN1T0uvUbyYgcjS', 'super_admin', 1, 0);

INSERT INTO usuario_permissoes (usuario_id, ver_todas_filiais, criar_lancamentos,
    editar_proprios_lancamentos, eliminar_proprios_lancamentos,
    ver_relatorios_consolidados, ver_relatorios_filial, exportar_relatorios)
VALUES (1, 1, 1, 1, 1, 1, 1, 1);

-- Perfil estendido do Super Admin
INSERT INTO perfis_usuarios (usuario_id, departamento) VALUES (1, 'Administração');

-- Planos de assinatura
INSERT INTO planos (codigo, nome, duracao_dias, preco, ordem, ativo) VALUES
('gratuito',   'Gratuito',   NULL, 0.00,       0, 1),
('mensal',     'Mensal',     30,   20000.00,   1, 1),
('trimestral', 'Trimestral', 90,   50000.00,   2, 1),
('semestral',  'Semestral',  180,  100000.00,  3, 1),
('anual',      'Anual',      365,  220000.00,  4, 1);

-- Configurações globais padrão do módulo de backups
INSERT INTO configuracoes (empresa_id, chave, valor, tipo) VALUES
(NULL, 'backup_automatico_ativo', '1', 'booleano'),
(NULL, 'backup_automatico_frequencia_dias', '7', 'numero');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- FIM. Se quiser os dados de demonstração (empresa MAMABAR, filiais
-- Prenda/Viana, categorias, métodos de pagamento e transações),
-- importe agora o ficheiro:  database/seed_exemplo.sql
-- =====================================================================
