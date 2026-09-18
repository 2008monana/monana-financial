<?php
/**
 * Listagem de Métodos de Pagamento
 * Com gestão de entradas, saídas e categorias
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">
            <i class="fas fa-credit-card"></i> Métodos de Pagamento
        </h1>
        <p class="page-subtitle">
            Gerencie os métodos de pagamento e categorias de saída da sua empresa
        </p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/metodos-pagamento/criar" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Método
        </a>
        <a href="<?php echo URL_BASE; ?>/metodos-pagamento/criarCategoriaSaida" class="btn btn-secondary">
            <i class="fas fa-tag"></i> Nova Categoria
        </a>
    </div>
</div>

<!-- ============================================
     STATS
     ============================================ -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-arrow-right"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($entradas); ?></span>
            <span class="stat-label">Entradas (Receitas)</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-arrow-left"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($saidas); ?></span>
            <span class="stat-label">Saídas (Despesas)</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($categoriasSaida); ?></span>
            <span class="stat-label">Categorias de Saída</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php 
                $ativos = 0;
                foreach (array_merge($entradas, $saidas) as $m) {
                    if ($m['ativo']) $ativos++;
                }
                echo $ativos;
            ?></span>
            <span class="stat-label">Métodos Ativos</span>
        </div>
    </div>
</div>

<!-- ============================================
     ENTRADAS
     ============================================ -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="background:#dcfce7; border-bottom:2px solid #16a34a; padding:16px 20px; border-radius:14px 14px 0 0;">
        <h3 style="margin:0; font-size:16px; font-weight:600; color:#16a34a;">
            <i class="fas fa-arrow-right"></i> Entradas (Receitas)
            <span style="font-size:12px; font-weight:400; color:#64748b; margin-left:8px;"><?php echo count($entradas); ?> métodos</span>
        </h3>
    </div>
    <div class="card-body" style="padding:16px;">
        <?php if (empty($entradas)): ?>
            <div class="empty-state" style="padding:20px; text-align:center; color:var(--muted);">
                <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                Nenhum método de entrada registado.
            </div>
        <?php else: ?>
            <div class="metodos-grid">
                <?php foreach ($entradas as $metodo): ?>
                    <?php echo renderMetodoCard($metodo); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================
     SAÍDAS
     ============================================ -->
<div class="card" style="margin-bottom:24px;">
    <div class="card-header" style="background:#fee2e2; border-bottom:2px solid #dc2626; padding:16px 20px; border-radius:14px 14px 0 0;">
        <h3 style="margin:0; font-size:16px; font-weight:600; color:#dc2626;">
            <i class="fas fa-arrow-left"></i> Saídas (Despesas)
            <span style="font-size:12px; font-weight:400; color:#64748b; margin-left:8px;"><?php echo count($saidas); ?> métodos</span>
        </h3>
    </div>
    <div class="card-body" style="padding:16px;">
        <?php if (empty($saidas)): ?>
            <div class="empty-state" style="padding:20px; text-align:center; color:var(--muted);">
                <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                Nenhum método de saída registado.
            </div>
        <?php else: ?>
            <div class="metodos-grid">
                <?php foreach ($saidas as $metodo): ?>
                    <?php echo renderMetodoCard($metodo); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================
     CATEGORIAS DE SAÍDA
     ============================================ -->
<div class="card">
    <div class="card-header" style="background:#fef3c7; border-bottom:2px solid #f59e0b; padding:16px 20px; border-radius:14px 14px 0 0;">
        <h3 style="margin:0; font-size:16px; font-weight:600; color:#f59e0b;">
            <i class="fas fa-tags"></i> Categorias de Saída
            <span style="font-size:12px; font-weight:400; color:#64748b; margin-left:8px;"><?php echo count($categoriasSaida); ?> categorias</span>
        </h3>
        <div>
            <a href="<?php echo URL_BASE; ?>/metodos-pagamento/criarCategoriaSaida" class="btn btn-sm btn-warning">
                <i class="fas fa-plus"></i> Nova Categoria
            </a>
        </div>
    </div>
    <div class="card-body" style="padding:16px;">
        <?php if (empty($categoriasSaida)): ?>
            <div class="empty-state" style="padding:20px; text-align:center; color:var(--muted);">
                <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                Nenhuma categoria de saída registada.
            </div>
        <?php else: ?>
            <div class="categorias-grid">
                <?php foreach ($categoriasSaida as $cat): ?>
                    <?php echo renderCategoriaCard($cat); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * Renderizar card de método de pagamento
 */
function renderMetodoCard($metodo) {
    $cor = $metodo['cor'] ?? '#64748b';
    $ativo = $metodo['ativo'] ?? 1;
    $icone = $metodo['icone'] ?? 'fa-credit-card';
    $categoria = $metodo['categoria'] ?? 'outro';
    
    $categoriaLabels = [
        'tpa' => 'TPA',
        'transferencia' => 'Transferência',
        'dinheiro' => 'Dinheiro',
        'deposito' => 'Depósito',
        'gasto' => 'Gasto',
        'outro' => 'Outro'
    ];
    
    return '
    <div class="metodo-card ' . ($ativo ? '' : 'inativo') . '">
        <div class="metodo-icon" style="background:' . htmlspecialchars($cor) . '; color:#fff;">
            <i class="fas ' . htmlspecialchars($icone) . '"></i>
        </div>
        <div class="metodo-info">
            <h4>' . htmlspecialchars($metodo['nome']) . '</h4>
            <div class="metodo-meta">
                <span class="categoria-badge">' . ($categoriaLabels[$categoria] ?? $categoria) . '</span>
                <span class="status-badge ' . ($ativo ? 'ativo' : 'inativo') . '">
                    <i class="fas fa-circle"></i> ' . ($ativo ? 'Ativo' : 'Inativo') . '
                </span>
            </div>
        </div>
        <div class="metodo-actions">
            <a href="' . URL_BASE . '/metodos-pagamento/editar/' . $metodo['id'] . '" class="btn btn-sm btn-secondary" title="Editar">
                <i class="fas fa-edit"></i>
            </a>
            <form method="POST" action="' . URL_BASE . '/metodos-pagamento/alternarEstado/' . $metodo['id'] . '" style="display:inline;">
                <input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">
                <button type="submit" class="btn btn-sm ' . ($ativo ? 'btn-warning' : 'btn-success') . '" title="' . ($ativo ? 'Desativar' : 'Reativar') . '">
                    <i class="fas ' . ($ativo ? 'fa-pause' : 'fa-play') . '"></i>
                </button>
            </form>
            <form method="POST" action="' . URL_BASE . '/metodos-pagamento/eliminar/' . $metodo['id'] . '" style="display:inline;">
                <input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Eliminar este método?\')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>';
}

/**
 * Renderizar card de categoria de saída
 */
function renderCategoriaCard($categoria) {
    $cor = $categoria['cor'] ?? '#64748b';
    $ativo = $categoria['ativo'] ?? 1;
    $icone = $categoria['icone'] ?? 'fa-tag';
    
    return '
    <div class="categoria-card ' . ($ativo ? '' : 'inativo') . '">
        <div class="categoria-icon" style="background:' . htmlspecialchars($cor) . '; color:#fff;">
            <i class="fas ' . htmlspecialchars($icone) . '"></i>
        </div>
        <div class="categoria-info">
            <h4>' . htmlspecialchars($categoria['nome']) . '</h4>
            <span class="status-badge ' . ($ativo ? 'ativo' : 'inativo') . '">
                <i class="fas fa-circle"></i> ' . ($ativo ? 'Ativa' : 'Inativa') . '
            </span>
        </div>
        <div class="categoria-actions">
            <form method="POST" action="' . URL_BASE . '/metodos-pagamento/alternarEstadoCategoria/' . $categoria['id'] . '" style="display:inline;">
                <input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">
                <button type="submit" class="btn btn-sm ' . ($ativo ? 'btn-warning' : 'btn-success') . '">
                    <i class="fas ' . ($ativo ? 'fa-pause' : 'fa-play') . '"></i>
                </button>
            </form>
            <form method="POST" action="' . URL_BASE . '/metodos-pagamento/eliminarCategoria/' . $categoria['id'] . '" style="display:inline;">
                <input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Eliminar esta categoria?\')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>';
}
?>

<style>
/* ============================================
   MÉTODOS DE PAGAMENTO - ESTILOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-title {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.page-title i {
    color: var(--green);
    margin-right: 12px;
}

.page-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 4px 0 0;
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid var(--border);
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--white);
    flex-shrink: 0;
}

.stat-icon.green { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.stat-icon.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }
.stat-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }
.stat-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.stat-label {
    font-size: 12px;
    color: var(--muted);
    font-weight: 500;
}

/* Cards */
.card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.card-body {
    padding: 16px;
}

/* Grids */
.metodos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 12px;
}

.categorias-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}

/* Método Card */
.metodo-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.metodo-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.metodo-card.inativo {
    opacity: 0.6;
}

.metodo-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.metodo-info {
    flex: 1;
    min-width: 0;
}

.metodo-info h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.metodo-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
    flex-wrap: wrap;
}

.categoria-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 1px 8px;
    background: var(--bg);
    color: var(--muted);
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.metodo-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

/* Categoria Card */
.categoria-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.categoria-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.categoria-card.inativo {
    opacity: 0.6;
}

.categoria-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.categoria-info {
    flex: 1;
}

.categoria-info h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.categoria-actions {
    display: flex;
    gap: 4px;
}

/* Botões */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 6px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(14, 39, 72, 0.25);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14, 39, 72, 0.35);
}

.btn-secondary {
    background: var(--bg);
    color: var(--ink);
    border: 1.5px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}

.btn-success {
    background: linear-gradient(135deg, var(--green), var(--green-dark));
    color: var(--white);
}

.btn-success:hover {
    transform: translateY(-2px);
}

.btn-warning {
    background: linear-gradient(135deg, var(--orange), var(--orange-dark));
    color: var(--white);
}

.btn-warning:hover {
    transform: translateY(-2px);
}

.btn-danger {
    background: linear-gradient(135deg, var(--red), var(--red-dark));
    color: var(--white);
}

.btn-danger:hover {
    transform: translateY(-2px);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    padding: 2px 10px;
    border-radius: 12px;
}

.status-badge.ativo {
    color: var(--green-dark);
    background: rgba(34, 197, 94, 0.10);
}

.status-badge.ativo i {
    color: var(--green);
    font-size: 6px;
}

.status-badge.inativo {
    color: var(--muted);
    background: rgba(0, 0, 0, 0.04);
}

.status-badge.inativo i {
    color: var(--muted);
    font-size: 6px;
}

/* Responsivo */
@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    .metodos-grid {
        grid-template-columns: 1fr;
    }
    .categorias-grid {
        grid-template-columns: 1fr;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-right {
        width: 100%;
    }
    .page-header-right .btn {
        flex: 1;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .metodo-card {
        flex-wrap: wrap;
    }
    .metodo-actions {
        width: 100%;
        justify-content: flex-end;
    }
    .categoria-card {
        flex-wrap: wrap;
    }
    .categoria-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>