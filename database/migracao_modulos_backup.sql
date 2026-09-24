-- =====================================================================
-- Migração: módulos/permissões por página + módulo de Backup
-- Execute uma única vez depois de schema.sql e migracao_modulos_administracao.sql
-- =====================================================================

-- ---------------------------------------------------------------------
-- BUG CORRIGIDO: as tabelas `modulos` e `usuario_modulo_permissoes`
-- eram usadas em todo o código (Modulo.php, ModuloMiddleware.php,
-- UsuariosController.php, views/usuarios/form.php) mas nunca tinham
-- sido criadas na base de dados. Qualquer utilizador com perfil
-- 'usuario_interno' ou 'visualizador' provocava um erro fatal do PDO
-- (tabela inexistente) ao tentar abrir qualquer página protegida,
-- e o ecrã de "permissões por módulo" no formulário de utilizador
-- nunca conseguia gravar nada. É esta a causa dos comportamentos
-- estranhos / bug reportado.
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS modulos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL UNIQUE,
    descricao VARCHAR(150) NULL,
    icone VARCHAR(40) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ordem INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuario_modulo_permissoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    modulo_id INT UNSIGNED NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ump_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ump_modulo FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_usuario_modulo (usuario_id, modulo_id)
) ENGINE=InnoDB;

-- Catálogo de páginas/módulos existentes (tem de acompanhar o Router::$rotaParaModulo).
-- 'perfil' está no catálogo para ficar explícito no formulário, embora permaneça sempre acessível.
INSERT IGNORE INTO modulos (nome, descricao, icone, ordem) VALUES
    ('dashboard',          'Dashboard',                       'fa-house',            1),
    ('movimentos',         'Movimentos',                      'fa-list-ul',          2),
    ('relatorios',         'Relatórios',                      'fa-chart-column',     3),
    ('planilha',           'Planilha',                        'fa-table',            4),
    ('categorias',         'Categorias',                      'fa-tags',             5),
    ('empresas',           'Empresas',                        'fa-building',         6),
    ('filiais',            'Filiais',                         'fa-code-branch',      7),
    ('usuarios',           'Utilizadores',                    'fa-users',            8),
    ('fecho_diario',       'Fecho Diário',                    'fa-calendar-check',   9),
    ('backups',            'Backups',                         'fa-database',        10),
    ('metodos_pagamento',  'Métodos de Pagamento',             'fa-credit-card',     11),
    ('logs',               'Logs de Auditoria',                'fa-clipboard-list',  12),
    ('configuracoes',      'Configurações',                    'fa-gear',            13),
    ('perfil',             'Meu Perfil',                      'fa-user-cog',        14),
    ('notificacoes',       'Notificações',                     'fa-bell',            15);

-- ---------------------------------------------------------------------
-- MÓDULO DE BACKUP
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    -- NULL = backup global (apenas Super Admin); caso contrário, backup filtrado de uma empresa
    empresa_id INT UNSIGNED NULL,
    usuario_id INT UNSIGNED NULL,
    tipo ENUM('manual','automatico') NOT NULL DEFAULT 'manual',
    escopo ENUM('global','empresa') NOT NULL DEFAULT 'empresa',
    nome_arquivo VARCHAR(255) NOT NULL,
    caminho_relativo VARCHAR(500) NOT NULL,
    tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('concluido','falhou') NOT NULL DEFAULT 'concluido',
    mensagem_erro VARCHAR(500) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_backup_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    CONSTRAINT fk_backup_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_backups_empresa_data (empresa_id, criado_em)
) ENGINE=InnoDB;

-- Agendamento do backup automático (1 a 7 dias). empresa_id NULL = agendamento global (Super Admin).
-- Reaproveita a tabela `configuracoes` (chave/valor) já existente em vez de criar mais uma tabela.
INSERT IGNORE INTO configuracoes (empresa_id, chave, valor, tipo) VALUES
    (NULL, 'backup_automatico_frequencia_dias', '7', 'numero'),
    (NULL, 'backup_automatico_ativo', '1', 'booleano');

-- ---------------------------------------------------------------------
-- REMOÇÃO DO MÓDULO DE IMPORTAÇÃO DE EXCEL
-- ---------------------------------------------------------------------
-- 'importar_excel' deixou de existir como funcionalidade; remove a coluna
-- da tabela de permissões finas (não estava sequer a ser usada na interface).
ALTER TABLE usuario_permissoes DROP COLUMN importar_excel;

-- Histórico de importações deixa de ser necessário. Se preferir manter o
-- histórico para consulta, comente a linha abaixo antes de executar.
DROP TABLE IF EXISTS importacoes;
