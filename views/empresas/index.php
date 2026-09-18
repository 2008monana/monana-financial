<?php
/**
 * Listagem de Empresas - Super Admin
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-building"></i> Empresas</h1>
        <p class="page-subtitle">Gerir todas as empresas do sistema</p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/empresas/criar" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Empresa
        </a>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-building"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($empresas); ?></span>
            <span class="stat-label">Total Empresas</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count(array_filter($empresas, fn($e) => $e['ativa'])); ?></span>
            <span class="stat-label">Empresas Ativas</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-ban"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count(array_filter($empresas, fn($e) => !$e['ativa'])); ?></span>
            <span class="stat-label">Empresas Inativas</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-store-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value"><?php echo array_sum(array_column($empresas, 'total_filiais')); ?></span>
            <span class="stat-label">Total Filiais</span>
        </div>
    </div>
</div>

<!-- Grid de Empresas -->
<div class="empresas-grid">
    <?php if (empty($empresas)): ?>
        <div class="empty-state">
            <i class="fas fa-building" style="font-size:48px; color:var(--muted);"></i>
            <h3>Nenhuma empresa registada</h3>
            <p>Comece por criar a primeira empresa do sistema.</p>
            <a href="<?php echo URL_BASE; ?>/empresas/criar" class="btn btn-primary">
                <i class="fas fa-plus"></i> Criar Empresa
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($empresas as $empresa): ?>
            <div class="empresa-card <?php echo $empresa['ativa'] ? '' : 'inativa'; ?>">
                <div class="empresa-card-header">
                    <div class="empresa-icon">
                        <?php echo strtoupper(substr($empresa['nome'], 0, 2)); ?>
                    </div>
                    <div class="empresa-info">
                        <h3><?php echo htmlspecialchars($empresa['nome']); ?></h3>
                        <span class="empresa-nif"><?php echo htmlspecialchars($empresa['nif'] ?: 'NIF não definido'); ?></span>
                    </div>
                    <span class="status-badge <?php echo $empresa['ativa'] ? 'ativo' : 'inativo'; ?>">
                        <i class="fas fa-circle"></i> <?php echo $empresa['ativa'] ? 'Ativa' : 'Inativa'; ?>
                    </span>
                </div>

                <div class="empresa-card-body">
                    <div class="empresa-details">
                        <?php if ($empresa['email_contacto']): ?>
                            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($empresa['email_contacto']); ?></span>
                        <?php endif; ?>
                        <?php if ($empresa['telefone']): ?>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($empresa['telefone']); ?></span>
                        <?php endif; ?>
                        <?php if ($empresa['endereco']): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($empresa['endereco']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="empresa-stats">
                        <div class="empresa-stat">
                            <span class="stat-number"><?php echo (int) $empresa['total_filiais']; ?></span>
                            <span class="stat-label">Filiais</span>
                        </div>
                        <div class="empresa-stat">
                            <span class="stat-number"><?php echo (int) ($empresa['total_usuarios'] ?? 0); ?></span>
                            <span class="stat-label">Utilizadores</span>
                        </div>
                    </div>
                </div>

                <div class="empresa-card-footer">
                    <a href="<?php echo URL_BASE; ?>/filiais/index?empresa_id=<?php echo $empresa['id']; ?>" class="btn btn-sm btn-secondary" title="Ver Filiais">
                        <i class="fas fa-store-alt"></i> Filiais
                    </a>
                    <a href="<?php echo URL_BASE; ?>/empresas/editar/<?php echo $empresa['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form method="post" action="<?php echo URL_BASE; ?>/empresas/alternarEstado/<?php echo $empresa['id']; ?>" style="display:inline;">
                        <button type="submit" class="btn btn-sm <?php echo $empresa['ativa'] ? 'btn-danger' : 'btn-success'; ?>" 
                                onclick="return confirm('<?php echo $empresa['ativa'] ? 'Deseja desativar' : 'Deseja reativar'; ?> esta empresa?')">
                            <i class="fas <?php echo $empresa['ativa'] ? 'fa-ban' : 'fa-undo'; ?>"></i>
                            <?php echo $empresa['ativa'] ? 'Desativar' : 'Reativar'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
/* ============================================
   EMPRESAS - ESTILOS COMPLETOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left { display: flex; flex-direction: column; gap: 2px; }

.page-title {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.page-title i { color: var(--green); margin-right: 12px; }

.page-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

.page-header-right { display: flex; gap: 10px; flex-wrap: wrap; }

/* ============================================
   ESTATÍSTICAS
   ============================================ */

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid var(--border);
    transition: var(--transition);
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
.stat-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }

.stat-info { display: flex; flex-direction: column; }

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

/* ============================================
   GRID DE EMPRESAS
   ============================================ */

.empresas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.empresa-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: var(--transition);
}

.empresa-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.empresa-card.inativa {
    opacity: 0.7;
    border-color: #f1f3f6;
}

.empresa-card.inativa .empresa-card-header {
    background: #f8fafc;
}

.empresa-card-header {
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid var(--border);
    background: #fafbfc;
}

.empresa-icon {
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

.empresa-info { flex: 1; min-width: 0; }

.empresa-info h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.empresa-nif {
    font-size: 12px;
    color: var(--muted);
}

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

.empresa-card-body {
    padding: 16px 20px;
}

.empresa-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 14px;
}

.empresa-details span {
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.empresa-details span i {
    width: 16px;
    color: var(--muted);
    font-size: 13px;
}

.empresa-stats {
    display: flex;
    gap: 24px;
    padding-top: 12px;
    border-top: 1px solid var(--border);
}

.empresa-stat {
    display: flex;
    flex-direction: column;
}

.empresa-stat .stat-number {
    font-family: 'Sora', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--ink);
}

.empresa-stat .stat-label {
    font-size: 11px;
    color: var(--muted);
    font-weight: 500;
}

.empresa-card-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.empresa-card-footer .btn {
    flex: 1;
    justify-content: center;
    min-width: 60px;
}

/* ============================================
   EMPTY STATE
   ============================================ */

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: var(--white);
    border-radius: var(--radius);
    border: 2px dashed var(--border);
}

.empty-state h3 {
    font-size: 20px;
    margin: 16px 0 8px;
    color: var(--ink);
}

.empty-state p {
    color: var(--muted);
    margin-bottom: 20px;
}

/* ============================================
   BOTÕES
   ============================================ */

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i { font-size: 14px; }

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

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 992px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .empresas-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
}

@media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr; }
    .empresa-card-footer .btn { flex: 1; font-size: 11px; padding: 6px 10px; }
}
</style>