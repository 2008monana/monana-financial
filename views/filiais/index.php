<?php
/**
 * Listagem de Filiais de uma Empresa selecionada
 */
$totalFiliais   = count($filiais);
$filiaisAtivas  = count(array_filter($filiais, fn($f) => (bool) $f['ativa']));
$filiaisInativas = $totalFiliais - $filiaisAtivas;
$semEmpresaNaSessao = empty($_SESSION['empresa_id']);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-store-alt"></i> Filiais</h1>
        <p class="page-subtitle">
            <?php if ($empresa): ?>
                <span class="empresa-tag"><i class="fas fa-building"></i> <?php echo htmlspecialchars($empresa['nome']); ?></span>
            <?php else: ?>
                Gerir as filiais da empresa
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-right">
        <?php if ($semEmpresaNaSessao): ?>
            <a href="<?php echo URL_BASE; ?>/filiais/index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Trocar Empresa
            </a>
        <?php endif; ?>
        <a href="<?php echo URL_BASE; ?>/filiais/criar<?php echo $semEmpresaNaSessao ? '?empresa_id=' . (int) $empresa['id'] : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Filial
        </a>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-store-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $totalFiliais; ?></span>
            <span class="stat-label">Total de Filiais</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $filiaisAtivas; ?></span>
            <span class="stat-label">Filiais Ativas</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-ban"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $filiaisInativas; ?></span>
            <span class="stat-label">Filiais Inativas</span>
        </div>
    </div>
</div>

<!-- Grid de Filiais -->
<div class="filiais-grid">
    <?php if (empty($filiais)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-store-alt"></i></div>
            <h3>Nenhuma filial registada</h3>
            <p>Esta empresa ainda não tem filiais cadastradas.</p>
            <a href="<?php echo URL_BASE; ?>/filiais/criar<?php echo $semEmpresaNaSessao ? '?empresa_id=' . (int) $empresa['id'] : ''; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Criar Filial
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($filiais as $filial): ?>
            <div class="filial-card <?php echo $filial['ativa'] ? '' : 'inativa'; ?>">
                <div class="filial-card-header">
                    <div class="filial-icon"><?php echo strtoupper(substr($filial['nome'], 0, 2)); ?></div>
                    <div class="filial-info">
                        <h3><?php echo htmlspecialchars($filial['nome']); ?></h3>
                        <?php if (!empty($filial['telefone'])): ?>
                            <span class="filial-telefone"><i class="fas fa-phone"></i><?php echo htmlspecialchars($filial['telefone']); ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="status-badge <?php echo $filial['ativa'] ? 'ativo' : 'inativo'; ?>">
                        <i class="fas fa-circle"></i> <?php echo $filial['ativa'] ? 'Ativa' : 'Inativa'; ?>
                    </span>
                </div>
                <div class="filial-card-body">
                    <?php if (!empty($filial['endereco'])): ?>
                        <div class="filial-endereco">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($filial['endereco']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="filial-card-footer">
                    <a href="<?php echo URL_BASE; ?>/filiais/editar/<?php echo (int) $filial['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form method="post" action="<?php echo URL_BASE; ?>/filiais/alternarEstado/<?php echo (int) $filial['id']; ?>" style="display:contents;">
                        <button type="submit" class="btn btn-sm <?php echo $filial['ativa'] ? 'btn-danger' : 'btn-success'; ?>"
                                onclick="return confirm('<?php echo $filial['ativa'] ? 'Deseja desativar' : 'Deseja reativar'; ?> esta filial?')">
                            <i class="fas <?php echo $filial['ativa'] ? 'fa-ban' : 'fa-undo'; ?>"></i>
                            <?php echo $filial['ativa'] ? 'Desativar' : 'Reativar'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<style>
/* ============================================
   FILIAIS - INDEX ESTILOS COMPLETOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
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
    margin: 0;
}

.empresa-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: rgba(14, 39, 72, 0.08);
    border-radius: 20px;
    font-weight: 600;
    color: var(--navy);
    font-size: 13px;
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--white);
    flex-shrink: 0;
}

.stat-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
.stat-icon.green { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.stat-icon.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.stat-label {
    font-size: 12px;
    color: var(--muted);
    font-weight: 500;
}

.filiais-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}

.filial-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.3s ease;
}

.filial-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.filial-card.inativa {
    opacity: 0.7;
    border-color: #f1f3f6;
}

.filial-card.inativa .filial-card-header {
    background: #f8fafc;
}

.filial-card-header {
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid var(--border);
    background: #fafbfc;
}

.filial-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}

.filial-info {
    flex: 1;
    min-width: 0;
}

.filial-info h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.filial-telefone {
    font-size: 13px;
    color: var(--muted);
}

.filial-telefone i { margin-right: 4px; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.ativo {
    background: #dcfce7;
    color: var(--green-dark);
}

.status-badge.ativo i { color: var(--green); font-size: 7px; }

.status-badge.inativo {
    background: #fee2e2;
    color: var(--red-dark);
}

.status-badge.inativo i { color: var(--red); font-size: 7px; }

.filial-card-body {
    padding: 14px 20px;
}

.filial-endereco {
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.filial-endereco i {
    margin-top: 2px;
    color: var(--muted);
}

.filial-card-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.filial-card-footer .btn {
    flex: 1;
    justify-content: center;
    min-width: 60px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
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
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
}

.btn-danger {
    background: linear-gradient(135deg, var(--red), var(--red-dark));
    color: var(--white);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--white);
    border-radius: var(--radius);
    border: 2px dashed var(--border);
}

.empty-icon {
    font-size: 56px;
    color: var(--muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 22px;
    margin: 0 0 8px;
    color: var(--ink);
}

.empty-state p {
    font-size: 15px;
    color: var(--muted);
    margin-bottom: 20px;
}

@media (max-width: 992px) {
    .stats-grid { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .filiais-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-title { font-size: 22px; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .filial-card-footer { flex-direction: column; }
    .filial-card-footer .btn { width: 100%; justify-content: center; }
}
</style>