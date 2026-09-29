-- =========================================================
-- RESET DE SENHA: Administrador da Farmácia BESTON
-- Email: admin@farmaciabeston.com
-- Nova senha temporária: Beston@123
--
-- Execute no phpMyAdmin (aba SQL) ou via linha de comando:
--   mysql -u root monana_financial < reset_senha_beston.sql
--
-- IMPORTANTE: após o primeiro login, altere a senha em
-- Perfil > Alterar Senha.
-- =========================================================

USE monana_financial;

-- 1) Ver os utilizadores da empresa BESTON antes de alterar
SELECT u.id, u.nome, u.email, u.perfil, u.ativo, e.nome AS empresa
FROM usuarios u
LEFT JOIN empresas e ON e.id = u.empresa_id
WHERE e.nome LIKE '%beston%' OR u.email LIKE '%beston%';

-- 2) Redefinir a senha (hash bcrypt de "Beston@123")
UPDATE usuarios
SET senha_hash = '$2y$10$XJy6JThsQldZyfVQlzaT1ejegwyXMoMeJfeEEiwnuP7gDaxy7xeK2',
    primeiro_acesso = 1,
    atualizado_em = NOW()
WHERE email = 'admin@farmaciabeston.com';

-- 3) Confirmar que a alteração foi aplicada
SELECT id, nome, email, perfil, ativo FROM usuarios
WHERE email = 'admin@farmaciabeston.com';
