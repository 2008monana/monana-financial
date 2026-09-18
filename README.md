# MonanaFinancial — Sistema de Gestão Financeira

## Como instalar no XAMPP

1. Copie a pasta `monana-financial` para `C:\xampp\htdocs\` (Windows) ou
   `/opt/lampp/htdocs/` (Linux).
2. Abra o **phpMyAdmin**, e importe **por esta ordem**:
   1. `database/schema.sql` (cria a base `monana_financial` e todas as tabelas)
   2. *(opcional, recomendado)* `database/seed_exemplo.sql` — cria a empresa
      **MAMABAR (SU), Lda**, filiais **Prenda** e **Viana**, categorias e
      transações do mês corrente, para já veres o Dashboard com números reais.
3. Confirme as credenciais da base de dados em `config/database.php`
   (por defeito: utilizador `root`, senha em branco — padrão do XAMPP).
4. Ajuste a constante `URL_BASE` em `config/config.php` para corresponder
   ao caminho real, por exemplo:
   `http://localhost/monana-financial/public`
5. Aceda no navegador a:
   `http://localhost/monana-financial/public/auth/login`

## Se já tinha a base de dados criada (correção de bug)

Se o login do Super Administrador der "Credenciais inválidas" mesmo com
`Admin@123`, a sua base de dados foi criada antes da correcção do hash da
senha. Basta importar `database/corrigir_senha_superadmin.sql` no
phpMyAdmin — resolve sem precisar de recriar nada.

## Utilizadores para teste

| Perfil | E-mail | Senha |
|---|---|---|
| Super Administrador | admin@monanafinancial.com | Admin@123 |
| Administrador da Empresa (só se importou o `seed_exemplo.sql`) | admin@mamabar.co.ao | Mamabar@123 |

⚠️ Altere estas senhas assim que possível.

## O que já funciona

**Núcleo e Autenticação**
- Estrutura completa de pastas, schema MySQL com todas as tabelas da especificação.
- Núcleo MVC: `Router`, `Controller`, `Model`, `Database` (PDO), `AuthMiddleware`.
- Login real com o layout/cores do `login.html`, **modal** de processamento
  (5 segundos) → sucesso (boas-vindas) ou erro (credenciais inválidas),
  hash bcrypt, sessão e logout.

**Dashboard**
- Layout principal (sidebar + topbar) reutilizado em todo o sistema.
- KPIs com variação % real vs. mês anterior, gráficos (Chart.js — linha e
  donut), resumo por filial, movimentos/compras recentes, alertas — tudo
  ligado à base de dados.

**Empresas e Filiais**
- CRUD completo de Empresas (exclusivo do Super Administrador).
- CRUD completo de Filiais, com seletor de empresa para o Super Admin e
  âmbito automático para o Administrador da Empresa.
- Ativar/desativar (eliminação lógica, preserva o histórico financeiro).

**Utilizadores e Permissões**
- CRUD completo de Utilizadores (Administrador da Empresa, Utilizador
  Interno, Visualizador — o Super Admin gere-os por empresa).
- Atribuição de **filiais** (que filiais cada utilizador pode ver) e das
  **8 permissões granulares** da especificação (criar/editar/eliminar
  lançamentos, relatórios, exportação, importação).
- Senha gerada automaticamente na criação e na redefinição — mostrada numa
  mensagem para o administrador partilhar (o envio automático por e-mail
  fica para a fase do Mailer).

## Testes realizados nesta entrega

Antes de enviar, montei um ambiente MySQL + PHP local e testei de ponta a
ponta: login (certo/errado) de ambos os perfis, dashboard, criação de
Empresa → Filial → Utilizador com permissões, e-mail duplicado, redefinição
de senha, ativar/desativar registos e rota inexistente — tudo sem erros PHP.

## Próximas fases sugeridas

1. Módulo de Transações (lançamentos de vendas/compras/despesas/devoluções) e Categorias.
2. Recuperação de senha (envio real de e-mail via `core/Mailer.php`).
3. Importação de Excel (PhpSpreadsheet) e Exportação (PDF/Excel).
4. Sistema de Notificações (painel completo, marcar como lida).
5. Módulo de Perfil (editar dados, foto via Cloudinary).

## Nota sobre bibliotecas externas (Composer)

Para as fases de PDF (Dompdf), Excel (PhpSpreadsheet) e e-mail (PHPMailer),
vamos precisar de instalar o **Composer** no XAMPP e adicionar um
`composer.json` — isso será feito quando chegarmos a essas fases.

