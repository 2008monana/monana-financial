-- =========================================================
-- DADOS DE EXEMPLO (OPCIONAL)
-- Execute depois do schema.sql se quiser testar o sistema já
-- com uma empresa, filiais, utilizador e transações reais.
-- Baseado nos relatórios "MAMABAR (SU), Lda" que enviaste.
-- =========================================================

USE monana_financial;

-- Empresa
INSERT INTO empresas (nome, nif, email_contacto, telefone, endereco, ativa)
VALUES ('MAMABAR (SU), Lda', '5417123456', 'geral@mamabar.co.ao', '+244 923 000 111', 'Luanda, Angola', 1);

SET @empresa_id = LAST_INSERT_ID();

-- Filiais
INSERT INTO filiais (empresa_id, nome, endereco, telefone, ativa) VALUES
    (@empresa_id, 'Prenda', 'Rua Principal, Prenda, Luanda', '+244 923 111 222', 1),
    (@empresa_id, 'Viana', 'Estrada Nacional, Viana, Luanda', '+244 923 333 444', 1);

SET @filial_prenda = (SELECT id FROM filiais WHERE empresa_id = @empresa_id AND nome = 'Prenda');
SET @filial_viana  = (SELECT id FROM filiais WHERE empresa_id = @empresa_id AND nome = 'Viana');

-- Administrador da Empresa (senha: Mamabar@123)
INSERT INTO usuarios (empresa_id, nome, email, senha_hash, perfil, ativo, primeiro_acesso)
VALUES (@empresa_id, 'Administrador MAMABAR', 'admin@mamabar.co.ao',
    '$2y$10$1S2zhZKhVsMhoF3zykW31eAv0iJ5LXjELH45oVxJ71gk6nsYAYBDi', -- Mamabar@123
    'admin_empresa', 1, 1);

SET @admin_empresa_id = LAST_INSERT_ID();

INSERT INTO usuario_filiais (usuario_id, filial_id) VALUES
    (@admin_empresa_id, @filial_prenda),
    (@admin_empresa_id, @filial_viana);

INSERT INTO usuario_permissoes (usuario_id, ver_todas_filiais, criar_lancamentos, editar_proprios_lancamentos,
    eliminar_proprios_lancamentos, ver_relatorios_consolidados, ver_relatorios_filial, exportar_relatorios, importar_excel)
VALUES (@admin_empresa_id, 1, 1, 1, 1, 1, 1, 1, 1);

-- Categorias
INSERT INTO categorias (empresa_id, nome, tipo, cor) VALUES
    (@empresa_id, 'Vendas ao Balcão', 'entrada', '#22c55e'),
    (@empresa_id, 'Fornecedores', 'saida', '#f59e0b'),
    (@empresa_id, 'Água e Luz', 'saida', '#ef4444'),
    (@empresa_id, 'Salários', 'saida', '#8b5cf6');

SET @cat_venda  = (SELECT id FROM categorias WHERE empresa_id = @empresa_id AND nome = 'Vendas ao Balcão');
SET @cat_compra = (SELECT id FROM categorias WHERE empresa_id = @empresa_id AND nome = 'Fornecedores');
SET @cat_custo  = (SELECT id FROM categorias WHERE empresa_id = @empresa_id AND nome = 'Água e Luz');

-- Transações do mês corrente (para o Dashboard já mostrar dados)
INSERT INTO transacoes (empresa_id, filial_id, categoria_id, usuario_id, tipo, descricao, valor, metodo_pagamento, data_transacao) VALUES
    (@empresa_id, @filial_prenda, @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 850000, 'numerario', CURDATE()),
    (@empresa_id, @filial_prenda, @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 620000, 'tpa', CURDATE() - INTERVAL 1 DAY),
    (@empresa_id, @filial_viana,  @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 480000, 'numerario', CURDATE() - INTERVAL 1 DAY),
    (@empresa_id, @filial_prenda, @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 710000, 'transferencia', CURDATE() - INTERVAL 2 DAY),
    (@empresa_id, @filial_viana,  @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 390000, 'tpa', CURDATE() - INTERVAL 2 DAY),
    (@empresa_id, @filial_prenda, @cat_venda, @admin_empresa_id, 'venda', 'Venda balcão', 905000, 'numerario', CURDATE() - INTERVAL 3 DAY),
    (@empresa_id, @filial_prenda, @cat_compra, @admin_empresa_id, 'compra', 'Farmaluz, Lda', 450000, 'transferencia', CURDATE() - INTERVAL 1 DAY),
    (@empresa_id, @filial_viana,  @cat_compra, @admin_empresa_id, 'compra', 'Distrimax', 320000, 'transferencia', CURDATE() - INTERVAL 3 DAY),
    (@empresa_id, @filial_prenda, @cat_custo,  @admin_empresa_id, 'custo', 'Água/Luz', 75000, 'numerario', CURDATE() - INTERVAL 1 DAY),
    (@empresa_id, @filial_viana,  @cat_custo,  @admin_empresa_id, 'custo', 'Água/Luz', 52000, 'numerario', CURDATE() - INTERVAL 2 DAY),
    (@empresa_id, @filial_prenda, @cat_venda, @admin_empresa_id, 'devolucao', 'Devolução cliente', 45000, 'numerario', CURDATE() - INTERVAL 2 DAY);

-- Notificações de exemplo
INSERT INTO notificacoes (usuario_id, tipo, titulo, mensagem) VALUES
    (@admin_empresa_id, 'alerta', 'Fecho diário pendente', 'Existem dias sem fecho na filial Prenda.'),
    (@admin_empresa_id, 'aviso', 'Stock baixo', 'Alguns produtos estão abaixo do nível mínimo em Viana.');
