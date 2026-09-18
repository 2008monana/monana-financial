-- =========================================================
-- CORREÇÃO: senha do Super Administrador
-- Execute este script SE o login com admin@monanafinancial.com
-- / Admin@123 estiver a dar "Credenciais inválidas".
--
-- Isto acontece se a sua base de dados foi criada a partir de uma
-- versão anterior do schema.sql (antes da correcção do hash bcrypt).
-- Este UPDATE funciona seja qual for o estado actual da sua BD.
-- =========================================================

USE monana_financial;

UPDATE usuarios
SET senha_hash = '$2y$10$jEKCB2T7hrHFhiIFMX3t1.mKLy7DPlLeST3UMjXN1T0uvUbyYgcjS' -- Admin@123
WHERE email = 'admin@monanafinancial.com';
