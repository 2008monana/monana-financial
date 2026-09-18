<?php
/**
 * Seleção de Empresa - Filiais
 * Mostra todas as empresas com contagem de filiais
 */
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-store-alt"></i> Filiais
        </h1>
        <p class="page-subtitle">Selecione uma empresa para gerir as suas filiais</p>
    </div>
    <a href="<?php echo URL_BASE; ?>/empresas/index" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<!-- Breadcrumb visual -->
<div class="breadcrumb-visual">
    <span class="breadcrumb-item active">
        <i class="fas fa-building"></i> Selecionar Empresa
    </span>
    <span class="breadcrumb-arrow"><i class="fas fa-chevron-right"></i></span>
    <span class="breadcrumb-item">
        <i class="fas fa-store-alt"></i> Gerir Filiais
    </span>
</div>

<?php if (empty($empresas)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-building"></i>
        </div>
        <h3>Nenhuma empresa ativa</h3>
        <p>Não existem empresas ativas no sistema.</p>
        <a href="<?php echo URL_BASE; ?>/empresas/criar" class="btn btn-primary">
            <i class="fas fa-plus"></i> Criar Empresa
        </a>
    </div>
<?php else: ?>
    <!-- Contador -->
    <div class="selection-counter">
        <i class="fas fa-building"></i>
        <span><?php echo count($empresas); ?> empresas disponíveis</span>
    </div>

    <div class="selection-grid">
        <?php 
        $cores = [
            ['bg' => '#0e2748', 'light' => 'rgba(14,39,72,0.08)'],
            ['bg' => '#3b82f6', 'light' => 'rgba(59,130,246,0.08)'],
            ['bg' => '#22c55e', 'light' => 'rgba(34,197,94,0.08)'],
            ['bg' => '#8b5cf6', 'light' => 'rgba(139,92,246,0.08)'],
            ['bg' => '#f59e0b', 'light' => 'rgba(245,158,11,0.08)'],
            ['bg' => '#ef4444', 'light' => 'rgba(239,68,68,0.08)'],
            ['bg' => '#ec4899', 'light' => 'rgba(236,72,153,0.08)'],
            ['bg' => '#14b8a6', 'light' => 'rgba(20,184,166,0.08)'],
        ];
        
        $i = 0;
        foreach ($empresas as $empresa): 
            if (!isset($empresa['id']) || !isset($empresa['nome'])) {
                continue;
            }
            
            $cor = $cores[$i % count($cores)];
            $i++;
            
            // =============================================
            // DADOS DA EMPRESA (JÁ VÊM DO CONTROLLER)
            // =============================================
            $totalFiliais = isset($empresa['total_filiais']) ? (int) $empresa['total_filiais'] : 0;
            $emailContacto = $empresa['email_contacto'] ?? 'Sem e-mail';
            $telefone = $empresa['telefone'] ?? '';
            $nif = $empresa['nif'] ?? '';
            $ativa = isset($empresa['ativa']) ? (bool) $empresa['ativa'] : true;
        ?>
            <a href="<?php echo URL_BASE; ?>/filiais/index?empresa_id=<?php echo (int) $empresa['id']; ?>" 
               class="selection-card" 
               style="--card-color: <?php echo $cor['bg']; ?>; --card-light: <?php echo $cor['light']; ?>;">
                
                <div class="selection-card-top">
                    <div class="selection-icon" style="background: <?php echo $cor['bg']; ?>;">
                        <?php echo strtoupper(substr($empresa['nome'], 0, 2)); ?>
                    </div>
                    <div class="selection-badge-count">
                        <i class="fas fa-store-alt"></i>
                        <span><?php echo $totalFiliais; ?></span>
                    </div>
                </div>

                <div class="selection-info">
                    <h3><?php echo htmlspecialchars($empresa['nome']); ?></h3>
                    <div class="selection-details">
                        <span class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($emailContacto); ?>
                        </span>
                        <?php if (!empty($telefone)): ?>
                            <span class="detail-item">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($telefone); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($nif)): ?>
                            <span class="detail-item">
                                <i class="fas fa-id-card"></i>
                                <?php echo htmlspecialchars($nif); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="selection-card-bottom">
                    <span class="selection-action">
                        <i class="fas fa-arrow-right"></i>
                        <span>Ver Filiais</span>
                    </span>
                    <span class="selection-status <?php echo $ativa ? 'active' : 'inactive'; ?>">
                        <i class="fas fa-circle"></i>
                        <?php echo $ativa ? 'Ativa' : 'Inativa'; ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ESTILOS (MANTIDOS) -->
<style>
/* ============================================
   SELEÇÃO DE EMPRESA - ESTILOS COMPLETOS
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
    font-size: 28px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.page-title i {
    color: var(--green);
    margin-right: 14px;
    font-size: 26px;
}

.page-subtitle {
    font-size: 15px;
    color: var(--muted);
    margin: 4px 0 0;
}

.breadcrumb-visual {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 24px;
}

.breadcrumb-item {
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

.breadcrumb-item.active {
    color: var(--navy);
    font-weight: 600;
}

.breadcrumb-item.active i {
    color: var(--green);
}

.breadcrumb-arrow {
    color: var(--border);
    font-size: 12px;
}

.selection-counter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: var(--white);
    border-radius: 20px;
    border: 1px solid var(--border);
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 20px;
}

.selection-counter i {
    color: var(--green);
}

.selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}

.selection-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 24px;
    text-decoration: none;
    color: var(--ink);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.selection-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--card-color);
    opacity: 0.6;
    transition: all 0.3s ease;
}

.selection-card:hover::before {
    opacity: 1;
    height: 5px;
}

.selection-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: var(--card-color);
}

.selection-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.selection-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 20px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.selection-card:hover .selection-icon {
    transform: scale(1.05) rotate(-2deg);
}

.selection-badge-count {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: var(--card-light);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: var(--card-color);
}

.selection-badge-count i {
    font-size: 12px;
}

.selection-info {
    flex: 1;
}

.selection-info h3 {
    font-size: 17px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 10px 0;
}

.selection-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-item {
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-item i {
    width: 16px;
    color: var(--muted);
    font-size: 13px;
}

.selection-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid var(--border);
}

.selection-action {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--card-color);
    transition: all 0.3s ease;
}

.selection-action i {
    transition: all 0.3s ease;
    font-size: 14px;
}

.selection-card:hover .selection-action i {
    transform: translateX(4px);
}

.selection-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    padding: 4px 12px;
    border-radius: 20px;
}

.selection-status i {
    font-size: 8px;
}

.selection-status.active {
    background: #dcfce7;
    color: var(--green-dark);
}

.selection-status.inactive {
    background: #fee2e2;
    color: var(--red-dark);
}

.empty-state {
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
    margin-bottom: 24px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
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

/* Animações */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.selection-card {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

.selection-card:nth-child(1) { animation-delay: 0.05s; }
.selection-card:nth-child(2) { animation-delay: 0.10s; }
.selection-card:nth-child(3) { animation-delay: 0.15s; }
.selection-card:nth-child(4) { animation-delay: 0.20s; }
.selection-card:nth-child(5) { animation-delay: 0.25s; }
.selection-card:nth-child(6) { animation-delay: 0.30s; }
.selection-card:nth-child(7) { animation-delay: 0.35s; }
.selection-card:nth-child(8) { animation-delay: 0.40s; }

@media (max-width: 992px) {
    .selection-grid { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
}

@media (max-width: 768px) {
    .selection-grid { grid-template-columns: 1fr; max-width: 500px; margin: 0 auto; }
    .page-title { font-size: 24px; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .breadcrumb-visual { flex-wrap: wrap; gap: 8px; }
    .selection-card { padding: 18px; }
    .selection-icon { width: 44px; height: 44px; font-size: 17px; }
    .selection-info h3 { font-size: 15px; }
}

@media (max-width: 480px) {
    .selection-card-top { flex-wrap: wrap; gap: 10px; }
    .selection-card-bottom { flex-wrap: wrap; gap: 10px; }
    .selection-details .detail-item { font-size: 12px; }
    .selection-badge-count { font-size: 12px; padding: 3px 10px; }
}
</style>