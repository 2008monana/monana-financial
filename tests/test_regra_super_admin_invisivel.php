<?php
session_start();
define('CAMINHO_RAIZ', '/workspace');

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec("CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nome TEXT, email TEXT, perfil TEXT, empresa_id INTEGER, ativo INTEGER DEFAULT 1)");
$pdo->exec("CREATE TABLE logs_auditoria (id INTEGER PRIMARY KEY AUTOINCREMENT, usuario_id INTEGER, acao TEXT, tabela_afetada TEXT, registo_id INTEGER, dados_antigos TEXT, dados_novos TEXT, prioridade TEXT, motivo TEXT, ip_origem TEXT, user_agent TEXT, criado_em TEXT DEFAULT CURRENT_TIMESTAMP)");
$pdo->exec("INSERT INTO usuarios VALUES (1,'Super Admin','sa@x.ao','super_admin',NULL,1),(2,'Admin Prenda','ap@x.ao','admin_empresa',1,1),(3,'Interno','i@x.ao','usuario_interno',1,1)");

// Injeta SQLite no singleton Database
require_once CAMINHO_RAIZ.'/core/Database.php';
$r = new ReflectionClass('Database');
$p = $r->getProperty('instancia'); $p->setAccessible(true); $p->setValue(null, $pdo);

require_once CAMINHO_RAIZ.'/core/Model.php';
require_once CAMINHO_RAIZ.'/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ.'/models/LogAuditoria.php';
require_once CAMINHO_RAIZ.'/models/Usuario.php';

$logs = new LogAuditoria();
$usuarios = new Usuario();
$fails = 0;
function ok($cond, $msg) { global $fails; if ($cond) echo "PASS: $msg\n"; else { echo "FAIL: $msg\n"; $fails++; } }

// 1) Registar evento com autor Super Admin -> deve gravar SEM autor (Sistema)
$logs->registar(1, 'empresa_editada', 'empresas', 5, null, ['nome'=>'X']);
$row = $pdo->query("SELECT usuario_id FROM logs_auditoria WHERE id=1")->fetch();
ok($row['usuario_id'] === null, 'Acao do super_admin gravada sem autor (anonima "Sistema")');

// 2) Registar evento de admin de empresa -> mantem autor
$logs->registar(2, 'transacao_criada', 'transacoes', 9, null, ['valor'=>100]);
$row = $pdo->query("SELECT usuario_id FROM logs_auditoria WHERE id=2")->fetch();
ok((int)$row['usuario_id'] === 2, 'Acao do admin_empresa mantem o autor');

// 3) Listagem em contexto de empresa NAO mostra eventos de super_admin historicos
$pdo->exec("INSERT INTO logs_auditoria (usuario_id,acao,tabela_afetada) VALUES (1,'login_sucesso',NULL)"); // historico "sujo"
$res = $logs->paginar([], 1, 1);
foreach ($res['itens'] as $item) ok(($item['usuario_id'] ?? null) != 1, 'Listagem da empresa exclui autor super_admin #'.$item['id']);
ok(count($res['itens']) === 1, 'Apenas 1 evento visivel para a empresa (total='.$res['total'].')');

// 4) Painel global tambem nao lista autores super_admin
$resGlobal = $logs->paginar([], null, 1);
ok(!array_filter($resGlobal['itens'], fn($l) => (int)($l['usuario_id']??0)===1), 'Painel global oculta registos com autor super_admin');

// 5) recentes() respeita o filtro
$rec = $logs->recentes(6, 1);
ok(count($rec) === 1 && (int)$rec[0]['usuario_id'] === 2, 'Feed do dashboard da empresa so traz eventos da propria empresa');

// 6) Contagens nunca incluem super_admin
ok($usuarios->contarAtivos() === 2, 'contarAtivos exclui super_admin');
ok($usuarios->contarTodos() === 2, 'contarTodos exclui super_admin');
ok(count($usuarios->porEmpresa(1)) === 2, 'porEmpresa devolve apenas utilizadores da empresa');
ok(count($usuarios->todosVisiveis()) === 2, 'todosVisiveis exclui super_admin');

// 7) encontrarPorId: sessao de admin_empresa nao ve super_admin
$_SESSION['usuario_perfil'] = 'admin_empresa'; $_SESSION['usuario_id'] = 2; $_SESSION['empresa_id'] = 1;
ok($usuarios->encontrarPorId(1) === false, 'Admin de empresa: super_admin e inexistente (detalhe)');
ok($usuarios->esSuperAdmin(1) === true, 'esSuperAdmin deteta conta SA');
unset($_SESSION['usuario_id']); // processo de sistema sem sessao
ok($usuarios->encontrarPorId(1) === false, 'Processo de sistema sem sessao tambem nao recebe conta SA');
$_SESSION['usuario_perfil'] = 'super_admin'; $_SESSION['usuario_id'] = 1;
ok($usuarios->encontrarPorId(1) !== false, 'Proprio Super Admin acede à sua conta');

echo $fails === 0 ? "\n>>> TODOS OS TESTES PASSARAM\n" : "\n>>> $fails FALHAS\n";
exit($fails === 0 ? 0 : 1);
