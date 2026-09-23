<?php
/**
 * View: Listagem de Métodos de Pagamento (conteúdo do layout principal)
 * Renderizada via Controller::renderizar() — sidebar/topbar incluídas automaticamente.
 * Segue o mesmo padrão visual dos módulos Funcionários / Utilizadores.
 */
$perfilAtual = $_SESSION['usuario_perfil'] ?? ($usuario['perfil'] ?? 'visualizador');
$podeGerir = in_array($perfilAtual, ['super_admin', 'admin_empresa']);

$totalMetodos   = count($metodos);
$totalActivos   = count(array_filter($metodos, fn($m) => !empty($m['ativo'])));
$totalPadrao    = count(array_filter($metodos, fn($m) => empty($m['empresa_id'])));
?>

<div class="page-header">
    <div>
        <h1 class="page-title mp-page-title"><i class="fas fa-credit-card"></i> Métodos de Pagamento</h1>
        <p class="page-subtitle">Gestão dos métodos de pagamento disponíveis no sistema</p>
    </div>
    <div class="page-header-right">
        <?php if ($podeGerir): ?>
            <a href="<?php echo URL_BASE; ?>/metodos-pagamento/criar" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Método
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-credit-card"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $totalMetodos; ?></span>
            <span class="stat-label">Total de Métodos</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-toggle-on"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $totalActivos; ?></span>
            <span class="stat-label">Activos</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-globe"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $totalPadrao; ?></span>
            <span class="stat-label">Padrão do Sistema</span>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="card">
    <div class="card-head">
        <div class="card-title"><i class="fas fa-list"></i> Lista de Métodos de Pagamento</div>
        <span class="badge-info"><?php echo $totalMetodos; ?> registado(s)</span>
    </div>
    <div class="tabela-scroll" style="overflow-x:auto;">
        <table class="tabela">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Descrição</th>
                    <th>Âmbito</th>
                    <th>Estado</th>
                    <th>Criado em</th>
                    <?php if ($podeGerir): ?><th style="width:120px; text-align:right;">Acções</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($metodos)): ?>
                    <tr>
                        <td colspan="<?php echo $podeGerir ? 6 : 5; ?>" style="text-align:center; padding:36px; color:var(--muted);">
                            <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                            Nenhum método de pagamento encontrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($metodos as $metodo): ?>
                        <tr>
                            <td>
                                <div class="metodo-cell">
                                    <div class="metodo-icone">
                                        <?php if (!empty($metodo['icone'])): ?>
                                            <i class="<?php echo htmlspecialchars($metodo['icone']); ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-wallet"></i>
                                        <?php endif; ?>
                                    </div>
                                    <strong><?php echo htmlspecialchars($metodo['nome']); ?></strong>
                                </div>
                            </td>
                            <td style="max-width:320px;">
                                <?php echo htmlspecialchars($metodo['descricao'] ?? '-') ?: '-'; ?>
                            </td>
                            <td>
                                <?php if (empty($metodo['empresa_id'])): ?>
                                    <span class="badge badge-info">Padrão do Sistema</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Empresa #<?php echo (int) $metodo['empresa_id']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo !empty($metodo['ativo']) ? 'success' : 'secondary'; ?>">
                                    <?php echo !empty($metodo['ativo']) ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo !empty($metodo['criado_em']) ? date('d/m/Y', strtotime($metodo['criado_em'])) : '-'; ?>
                            </td>
                            <?php if ($podeGerir): ?>
                                <td style="text-align:right;">
                                    <div class="accoes">
                                        <a href="<?php echo URL_BASE; ?>/metodos-pagamento/editar?id=<?php echo (int) $metodo['id']; ?>"
                                           class="acao-btn editar" title="Editar"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="acao-btn excluir" title="Excluir"
                                                onclick="confirmarExclusaoMetodo(<?php echo (int) $metodo['id']; ?>, '<?php echo htmlspecialchars(addslashes($metodo['nome'])); ?>')">
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
        Total: <strong><?php echo $totalMetodos; ?></strong> método(s)
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal-overlay" id="modalExclusaoMetodo">
    <div class="modal-caixa">
        <div class="modal-icone perigo"><i class="fas fa-triangle-exclamation"></i></div>
        <h3>Confirmar Exclusão</h3>
        <p>Tem a certeza que deseja excluir o método <strong id="nomeMetodoExcluir"></strong>?<br>
           <small style="color:var(--red);">Esta acção não pode ser desfeita.</small></p>
        <div class="modal-acoes">
            <button type="button" class="btn btn-secondary" onclick="fecharModalExclusaoMetodo()">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoMetodo">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </div>
    </div>
</div>

<style>
/* ============================================
   MÉTODOS DE PAGAMENTO — ESTILOS (mesmo padrão de Funcionários)
   ============================================ */

/* ---- HEADER ---- */
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.mp-page-title { font-family:'Sora',sans-serif; font-size:26px; font-weight:700; color:var(--navy-deep); margin:0; }
.mp-page-title i { color:var(--green); margin-right:12px; }
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
.btn { display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px; font-size:14px; font-weight:600; font-family:'Inter',sans-serif; text-decoration:none; transition:all .3s ease; border:none; cursor:pointer; }
.btn i { font-size:14px; }
.btn-primary { background:linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); color:var(--white); box-shadow:0 4px 14px rgba(14,39,72,.25); }
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(14,39,72,.35); }
.btn-secondary { background:#e2e8f0; color:#334155; }
.btn-secondary:hover { background:#cbd5e1; }
.btn-danger { background:var(--red); color:#fff; }
.btn-danger:hover { background:var(--red-dark); }

/* ---- TABELA ---- */
.tabela { width:100%; border-collapse:collapse; font-size:13.5px; }
.tabela thead th {
    text-align:left; padding:12px 14px; background:#f4f6fa; color:#64748b;
    font-size:11.5px; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border);
}
.tabela tbody td { padding:12px 14px; border-bottom:1px solid #eef2f7; vertical-align:middle; }
.tabela tbody tr:hover { background:#f8fafc; }
.tabela-rodape { padding:12px 20px; text-align:right; font-size:13px; color:var(--muted); border-top:1px solid var(--border); }

.metodo-cell { display:flex; align-items:center; gap:12px; }
.metodo-icone {
    width:40px; height:40px; border-radius:10px; background:#eef2f7; color:var(--navy);
    display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;
}

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
.badge-secondary { background:#e2e8f0; color:#475569; }
.badge-info { background:#e0ecff; color:#1d4ed8; }
span.badge-info { padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }

/* ---- MODAL ---- */
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
</style>

<script>
let metodoIdParaExcluir = null;

function confirmarExclusaoMetodo(id, nome) {
    metodoIdParaExcluir = id;
    document.getElementById('nomeMetodoExcluir').textContent = nome;
    document.getElementById('modalExclusaoMetodo').classList.add('aberto');
}

function fecharModalExclusaoMetodo() {
    metodoIdParaExcluir = null;
    document.getElementById('modalExclusaoMetodo').classList.remove('aberto');
}

document.getElementById('btnConfirmarExclusaoMetodo').addEventListener('click', function () {
    if (!metodoIdParaExcluir) return;
    const dados = new FormData();
    dados.append('id', metodoIdParaExcluir);

    fetch('<?php echo URL_BASE; ?>/metodos-pagamento/excluir', {
        method: 'POST',
        body: dados
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            location.reload();
        } else {
            alert(data.erro || 'Erro ao excluir método de pagamento.');
            fecharModalExclusaoMetodo();
        }
    })
    .catch(() => {
        alert('Erro ao excluir método de pagamento.');
        fecharModalExclusaoMetodo();
    });
});

// Fechar modal clicando fora
document.getElementById('modalExclusaoMetodo').addEventListener('click', function (e) {
    if (e.target === this) fecharModalExclusaoMetodo();
});
</script>
