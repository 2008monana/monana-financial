# Alterações desta atualização

## 1. Bug corrigido — permissões por página / logs de auditoria

**Causa raiz encontrada:** as tabelas `modulos` e `usuario_modulo_permissoes`
eram usadas em todo o código (`models/Modulo.php`, `middleware/ModuloMiddleware.php`,
`controllers/UsuariosController.php`, `views/usuarios/form.php`) mas nunca
tinham sido criadas na base de dados. Qualquer utilizador com perfil
`usuario_interno` ou `visualizador` provocava um erro fatal do PDO ("tabela
não existe") ao abrir praticamente qualquer página, e o ecrã de "permissões
por módulo" no formulário de utilizador nunca conseguia gravar nada.

**Correção:** ficheiro novo `database/migracao_modulos_backup.sql`, que cria
as duas tabelas em falta e semeia as páginas atuais do sistema. Depois de o
executar, o sistema de permissões por página — que já estava totalmente
implementado no código, só sem base de dados por trás — passa a funcionar:

- Super Admin e Admin Empresa continuam com acesso total aos módulos já
  previstos para eles.
- Um novo `usuario_interno` ou `visualizador` só vê a página **Perfil**
  por padrão. O Admin Empresa escolhe, ao criar/editar esse utilizador, a
  quais páginas ele passa a ter acesso (ecrã já existente em
  `usuarios/criar` e `usuarios/editar`).
- Os Logs de Auditoria (Super Admin: globais; Admin Empresa: só da sua
  empresa) já estavam corretamente implementados em `LogsController` — o
  problema era o crash noutras páginas do sistema, não a página de logs em si.

> Nota: também reparei que `metodos_pagamento` e `categorias_saida` têm o
> mesmo problema (tabelas usadas no código mas nunca criadas no schema).
> Não mexi nisso porque não fazia parte do pedido, mas fica sinalizado.

## 2. Módulo de Importação de Excel — removido

- Apagados: `controllers/ImportacaoController.php`,
  `helpers/ImportadorModeloDiario.php`, `views/importacao/`.
- Rotas e permissão de módulo `importacao` removidas de `core/Router.php` e
  `middleware/ModuloMiddleware.php`.
- Coluna `importar_excel` removida de `usuario_permissoes`
  (`models/UsuarioPermissao.php` + migração).
- Tabela `importacoes` (histórico de importações) removida pela migração.
- Links do menu lateral e do dashboard atualizados para apontar para o
  novo módulo de Backups em vez de Importar Excel.
- **Não foi tocada** a exportação de Excel (`exports/excel/RelatorioExcelBuilder.php`),
  usada em Relatórios/Transações — isso é uma funcionalidade diferente.

## 3. Módulo de Backups — novo

Ficheiros novos: `models/Backup.php`, `helpers/BackupHelper.php`,
`controllers/BackupsController.php`, `views/backups/index.php`,
`cron/backup_automatico.php`.

- **Super Admin**: vê e gera backups de **toda a base de dados**.
- **Admin Empresa**: vê e gera backups **só com os dados da sua empresa**
  (filtrados por `empresa_id`, incluindo tabelas relacionadas via
  `usuario_id`). Cada um só pode descarregar/eliminar os backups do seu
  próprio âmbito (verificação feita no controller, não só na interface).
- **Backup manual**: botão "Gerar backup manual" gera um `.zip` (com um
  `.sql` lá dentro) na hora.
- **Backup automático**: cada âmbito escolhe a frequência (1 a 7 dias) e
  se está ativo, guardado na tabela `configuracoes` já existente. Como o
  sistema pode não ter acesso a um `cron` real do servidor, a própria
  página de Backups verifica e gera o backup em atraso sempre que é
  aberta. Para não depender disso, incluí também
  `cron/backup_automatico.php`, para agendar no servidor (ex.: `0 3 * * *`).
- Geração do `.sql`: usa `mysqldump` quando disponível no servidor
  (mais rápido/completo); caso contrário, faz o dump em PHP puro
  (`SHOW CREATE TABLE` + `SELECT` linha a linha), para funcionar em
  qualquer alojamento.
- Ficheiros guardados fora de `public/`, em `storage/backups/`, com
  `.htaccess` a bloquear acesso direto — só são descarregados através do
  controller, que valida sessão + âmbito.

### Para ativar tudo

```
mysql -u root monana_financial < database/migracao_modulos_administracao.sql   -- se ainda não executou
mysql -u root monana_financial < database/migracao_modulos_backup.sql
```

Garanta que a pasta `storage/backups/` tem permissão de escrita para o PHP
(`chmod -R 750 storage`).
