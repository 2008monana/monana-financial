<?php
/**
 * View: Listagem de Funcionários (conteúdo do layout principal)
 * Renderizada via Controller::renderizar() — sidebar/topbar incluídas automaticamente.
 */
$perfilAtual = $_SESSION['usuario_perfil'] ?? 'visualizador';
$podeGerir = in_array($perfilAtual, ['super_admin', 'admin_empresa']);
$buscaAtual = $_GET['busca'] ?? '';
$filialAtual = $_GET['filial_id'] ?? '';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users"></i> Funcionários</h1>
        <p class="page-subtitle">Gestão do quadro de funcionários da empresa</p>
    </div>
    <div class="page-header-right">
        <?php if ($podeGerir): ?>
            <a href="<?php echo URL_BASE; ?>/funcionarios/criar" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Funcionário
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo (int) $total; ?></span>
            <span class="stat-label">Total de Funcionários</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count(array_filter($funcionarios, fn($f) => ($f['estado'] ?? '') === 'activo')); ?></span>
            <span class="stat-label">Activos</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-store-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($filiais); ?></span>
            <span class="stat-label">Filiais</span>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card">
    <form method="GET" action="<?php echo URL_BASE; ?>/funcionarios/index" class="filtros-form">
        <div class="filtro-campo" style="flex:2; min-width:220px;">
            <label>Buscar</label>
            <input type="text" name="busca" placeholder="Nome, cargo ou email"
                   value="<?php echo htmlspecialchars($buscaAtual); ?>">
        </div>
        <div class="filtro-campo" style="flex:1; min-width:180px;">
            <label>Filial</label>
            <select name="filial_id">
                <option value="">Todas as filiais</option>
                <?php foreach ($filiais as $filial): ?>
                    <option value="<?php echo (int) $filial['id']; ?>"
                        <?php echo $filialAtual != '' && (int) $filialAtual === (int) $filial['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($filial['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filtro-acoes">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="<?php echo URL_BASE; ?>/funcionarios/index" class="btn btn-secondary"><i class="fas fa-times"></i> Limpar</a>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="card">
    <div class="card-head">
        <div class="card-title"><i class="fas fa-list"></i> Lista de Funcionários</div>
        <span class="badge-info"><?php echo (int) $total; ?> registado(s)</span>
    </div>
    <div class="tabela-scroll" style="overflow-x:auto;">
        <table class="tabela">
            <thead>
                <tr>
                    <th style="width:70px;">Foto</th>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Filial</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <?php if ($podeGerir): ?><th style="width:120px; text-align:right;">Acções</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($funcionarios)): ?>
                    <tr>
                        <td colspan="<?php echo $podeGerir ? 8 : 7; ?>" style="text-align:center; padding:36px; color:var(--muted);">
                            <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                            Nenhum funcionário encontrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($funcionarios as $funcionario): ?>
                        <tr>
                            <td>
                                <?php if (!empty($funcionario['foto'])): ?>
                                    <img src="<?php echo URL_BASE . '/' . htmlspecialchars($funcionario['foto']); ?>"
                                         alt="Foto" style="width:44px; height:44px; border-radius:50%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:44px; height:44px; border-radius:50%; background:#eef2f7; display:flex; align-items:center; justify-content:center; color:var(--muted);">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($funcionario['nome']); ?></strong>
                                <?php if (!empty($funcionario['data_admissao'])): ?>
                                    <br><small style="color:var(--muted);"><i class="far fa-calendar-alt"></i> Admissão: <?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($funcionario['cargo'] ?? '-'); ?>
                                <?php if (!empty($funcionario['departamento'])): ?>
                                    <br><small style="color:var(--muted);"><?php echo htmlspecialchars($funcionario['departamento']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($funcionario['filial_nome'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($funcionario['telefone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($funcionario['email'] ?? '-'); ?></td>
                            <td>
                                <?php $estado = $funcionario['estado'] ?? 'activo'; ?>
                                <span class="badge badge-<?php echo $estado === 'activo' ? 'success' : ($estado === 'suspenso' ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($estado); ?>
                                </span>
                            </td>
                            <?php if ($podeGerir): ?>
                                <td style="text-align:right;">
                                    <div class="accoes">
                                        <a href="<?php echo URL_BASE; ?>/funcionarios/editar/<?php echo (int) $funcionario['id']; ?>"
                                           class="acao-btn editar" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="acao-btn excluir" title="Excluir"
                                                onclick="confirmarExclusaoFuncionario(<?php echo (int) $funcionario['id']; ?>, '<?php echo htmlspecialchars(addslashes($funcionario['nome'])); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="tabela-rodape">
        Total: <strong><?php echo (int) $total; ?></strong> funcionário(s)
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal-overlay" id="modalExclusaoFuncionario">
    <div class="modal-caixa">
        <div class="modal-icone perigo"><i class="fas fa-triangle-exclamation"></i></div>
        <h3>Confirmar Exclusão</h3>
        <p>Tem a certeza que deseja excluir o funcionário <strong id="nomeFuncionarioExcluir"></strong>?<br>
           <small style="color:var(--red);">Esta acção não pode ser desfeita.</small></p>
        <div class="modal-acoes">
            <button type="button" class="btn btn-secondary" onclick="fecharModalExclusaoFuncionario()">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoFuncionario">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </div>
    </div>
</div>

<style>
/* ============================================
   FUNCIONÁRIOS — ESTILOS (mesmo padrão de Utilizadores)
   ============================================ */

/* ---- HEADER ---- */
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.page-title { font-family:'Sora',sans-serif; font-size:26px; font-weight:700; color:var(--navy-deep); margin:0; }
.page-title i { color:var(--green); margin-right:12px; }
.page-subtitle { font-size:14px; color:var(--muted); margin:0; }
.page-header-right { display:flex; gap:10px; flex-wrap:wrap; }

/* ---- ESTATÍSTICAS ---- */
.stats-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:24px; }
.stat-card { background:var(--white); border-radius:var(--radius); padding:18px 20px; display:flex; align-items:center; gap:14px; border:1px solid var(--border); transition:all .3s ease; }
.stat-card:hover { transform:translateY(-2px); box-shadow:var(--shadow-md); }
.stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; color:var(--white); flex-shrink:0; }
.stat-icon.navy { background:linear-gradient(135deg, var(--navy), var(--navy-light)); }
.stat-icon.green { background:linear-gradient(135deg, var(--green), var(--green-dark)); }
.stat-icon.orange { background:linear-gradient(135deg, var(--orange), var(--orange-dark)); }
.stat-info { display:flex; flex-direction:column; }
.stat-value { font-family:'Sora',sans-serif; font-size:24px; font-weight:700; color:var(--ink); line-height:1.2; }
.stat-label { font-size:12px; color:var(--muted); font-weight:500; }

/* ---- CARTÕES ---- */
.card { background:var(--white); border-radius:var(--radius); border:1px solid var(--border); margin-bottom:24px; overflow:hidden; }
.card-head { display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid var(--border); }
.card-title { font-family:'Sora',sans-serif; font-size:15px; font-weight:700; color:var(--navy-deep); }
.card-title i { color:var(--blue); margin-right:8px; }

/* ---- BOTÕES ---- */
.btn { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px; font-size:13px; font-weight:600; font-family:'Inter',sans-serif; text-decoration:none; transition:all .3s ease; border:none; cursor:pointer; }
.btn i { font-size:14px; }
.btn-primary { background:linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color:var(--white); box-shadow:0 4px 14px rgba(14,39,72,.25); }
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(14,39,72,.35); }
.btn-secondary { background:var(--bg); color:var(--ink); border:1.5px solid var(--border); }
.btn-secondary:hover { background:var(--border); }
.btn-danger { background:linear-gradient(135deg, var(--red), var(--red-dark)); color:var(--white); box-shadow:0 4px 14px rgba(239,68,68,.25); }
.btn-danger:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(239,68,68,.35); }

/* ---- FILTROS ---- */
.filtros-form { display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap; padding:20px; }
.filtro-campo label { display:block; font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:6px; }
.filtro-campo input, .filtro-campo select {
    width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:10px;
    font-family:'Inter',sans-serif; font-size:14px; background:#fff; color:var(--ink); outline:none;
    transition:border-color .2s, box-shadow .2s;
}
.filtro-campo input:focus, .filtro-campo select:focus { border-color:var(--navy); box-shadow:0 0 0 3px rgba(14,39,72,.1); }
.filtro-acoes { display:flex; gap:10px; }
.tabela { width:100%; border-collapse:collapse; font-size:13.5px; }
.tabela thead th {
    text-align:left; padding:12px 14px; background:#f4f6fa; color:#64748b;
    font-size:11.5px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border);
}
.tabela tbody td { padding:12px 14px; border-bottom:1px solid #eef2f7; vertical-align:middle; }
.tabela tbody tr:hover { background:#f8fafc; }
.tabela-rodape { padding:12px 20px; text-align:right; font-size:13px; color:var(--muted); border-top:1px solid var(--border); }
.accoes { display:inline-flex; gap:6px; }
.acao-btn {
    width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:#fff;
    cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:13px;
    transition:all .2s; text-decoration:none;
}
.acao-btn.editar { color:var(--blue-dark); }
.acao-btn.editar:hover { background:var(--blue); color:#fff; border-color:var(--blue); }
.acao-btn.excluir { color:var(--red-dark); }
.acao-btn.excluir:hover { background:var(--red); color:#fff; border-color:var(--red); }
.badge { display:inline-block; padding:4px 10px; border-radius:999px; font-size:11.5px; font-weight:700; }
.badge-success { background:#dcfce7; color:#15803d; }
.badge-warning { background:#fef3c7; color:#b45309; }
.badge-secondary { background:#e2e8f0; color:#475569; }
.badge-info { background:#e0ecff; color:#1d4ed8; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
.modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(10,25,48,.55); z-index:1000;
    align-items:center; justify-content:center;
}
.modal-overlay.aberto { display:flex; }
.modal-caixa {
    background:#fff; border-radius:16px; padding:28px; width:min(420px, 92vw);
    box-shadow:0 20px 60px rgba(0,0,0,.25); text-align:center;
}
.modal-icone {
    width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center;
    margin:0 auto 14px; font-size:24px;
}
.modal-icone.perigo { background:#fee2e2; color:var(--red); }
.modal-caixa h3 { font-family:'Sora',sans-serif; margin-bottom:8px; color:var(--ink); }
.modal-caixa p { font-size:14px; color:#475569; margin-bottom:20px; }
.modal-acoes { display:flex; gap:10px; justify-content:center; }
.btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; border:none; cursor:pointer; font-size:14px; font-weight:600; text-decoration:none; font-family:'Inter',sans-serif; transition:all .2s; }
.btn-danger { background:var(--red); color:#fff; }
.btn-danger:hover { background:var(--red-dark); }
.btn-secondary { background:#e2e8f0; color:#334155; }
.btn-secondary:hover { background:#cbd5e1; }
</style>

<script>
let funcionarioIdParaExcluir = null;

function confirmarExclusaoFuncionario(id, nome) {
    funcionarioIdParaExcluir = id;
    document.getElementById('nomeFuncionarioExcluir').textContent = nome;
    document.getElementById('modalExclusaoFuncionario').classList.add('aberto');
}

function fecharModalExclusaoFuncionario() {
    funcionarioIdParaExcluir = null;
    document.getElementById('modalExclusaoFuncionario').classList.remove('aberto');
}

document.getElementById('btnConfirmarExclusaoFuncionario').addEventListener('click', function () {
    if (!funcionarioIdParaExcluir) return;
    fetch('<?php echo URL_BASE; ?>/funcionarios/excluir/' + funcionarioIdParaExcluir, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erro ao excluir funcionário.');
            fecharModalExclusaoFuncionario();
        }
    })
    .catch(() => {
        alert('Erro ao excluir funcionário.');
        fecharModalExclusaoFuncionario();
    });
});

// Fechar modal clicando fora
document.getElementById('modalExclusaoFuncionario').addEventListener('click', function (e) {
    if (e.target === this) fecharModalExclusaoFuncionario();
});
</script>
