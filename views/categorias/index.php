<?php
/**
 * Listagem de Categorias
 */
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-tags"></i> Categorias</h1>
        <p class="page-subtitle">
            Gestão de categorias de entrada e saída
            <?php if (!empty($empresaNome)): ?>
                <span class="empresa-tag"><i class="fas fa-building"></i> <?php echo htmlspecialchars($empresaNome); ?></span>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-right">
        <?php if ($perfil === 'super_admin' && !empty($empresas)): ?>
        <div class="filter-group" style="display:inline-block; margin-right:10px;">
            <select name="empresa_id" onchange="window.location.href='<?php echo URL_BASE; ?>/categorias/index?empresa_id='+this.value" style="padding:8px 12px; border-radius:8px; border:1.5px solid var(--border); font-size:13px;">
                <option value="">Todas as Empresas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?php echo $emp['id']; ?>" <?php echo ($empresaId ?? 0) == $emp['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <a href="<?php echo URL_BASE; ?>/categorias/criar<?php echo $empresaId ? '?empresa_id=' . $empresaId : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Categoria
        </a>
    </div>
</div>

<?php if (empty($entradas) && empty($saidas)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-tags"></i></div>
        <h3>Nenhuma categoria registada</h3>
        <p>Crie categorias para organizar os seus lançamentos financeiros.</p>
        <a href="<?php echo URL_BASE; ?>/categorias/criar<?php echo $empresaId ? '?empresa_id=' . $empresaId : ''; ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Criar Categoria
        </a>
    </div>
<?php else: ?>

<!-- ==========================================
ENTRADAS
========================================== -->
<div class="categoria-grupo">
    <div class="categoria-grupo-header">
        <h3><i class="fas fa-arrow-right" style="color:var(--green);"></i> Entradas (<?php echo count($entradas); ?>)</h3>
        <span class="badge badge-success">Receitas</span>
    </div>
    <div class="categoria-grid">
        <?php foreach ($entradas as $cat): ?>
            <?php echo renderCategoriaCard($cat, $perfil); ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- ==========================================
SAÍDAS
========================================== -->
<div class="categoria-grupo">
    <div class="categoria-grupo-header">
        <h3><i class="fas fa-arrow-left" style="color:var(--red);"></i> Saídas (<?php echo count($saidas); ?>)</h3>
        <span class="badge badge-danger">Despesas</span>
    </div>
    <div class="categoria-grid">
        <?php foreach ($saidas as $cat): ?>
            <?php echo renderCategoriaCard($cat, $perfil); ?>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>

<?php
/**
 * Função para renderizar um card de categoria
 */
function renderCategoriaCard($categoria, $perfil)
{
    $cor = $categoria['cor'] ?? '#64748b';
    $ativa = $categoria['ativa'] ?? 1;
    $statusClass = $ativa ? 'ativo' : 'inativo';
    $statusText = $ativa ? 'Ativa' : 'Inativa';
    
    $html = '<div class="categoria-card ' . ($ativa ? '' : 'inativa') . '">';
    $html .= '<div class="categoria-card-header">';
    $html .= '<div class="categoria-cor" style="background:' . htmlspecialchars($cor) . ';"></div>';
    $html .= '<div class="categoria-info">';
    $html .= '<h4>' . htmlspecialchars($categoria['nome']) . '</h4>';
    $html .= '<span class="status-badge ' . $statusClass . '"><i class="fas fa-circle"></i> ' . $statusText . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="categoria-card-footer">';
    
    // Editar (todos podem editar)
    $html .= '<a href="' . URL_BASE . '/categorias/editar/' . $categoria['id'] . '" class="btn btn-sm btn-secondary">';
    $html .= '<i class="fas fa-edit"></i> Editar';
    $html .= '</a>';
    
    // Ativar/Desativar
    $html .= '<form method="POST" action="' . URL_BASE . '/categorias/alternarEstado/' . $categoria['id'] . '" style="display:inline;">';
    $html .= '<input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">';
    $html .= '<button type="submit" class="btn btn-sm ' . ($ativa ? 'btn-warning' : 'btn-success') . '" ';
    $html .= 'onclick="return confirm(\'' . ($ativa ? 'Desativar' : 'Reativar') . ' esta categoria?\')">';
    $html .= '<i class="fas ' . ($ativa ? 'fa-pause' : 'fa-play') . '"></i> ';
    $html .= $ativa ? 'Desativar' : 'Reativar';
    $html .= '</button>';
    $html .= '</form>';
    
    // Eliminar (apenas Super Admin)
    if ($perfil === 'super_admin') {
        $html .= '<form method="POST" action="' . URL_BASE . '/categorias/eliminar/' . $categoria['id'] . '" style="display:inline;">';
        $html .= '<input type="hidden" name="csrf_token" value="' . SegurancaHelper::gerarTokenCSRF() . '">';
        $html .= '<button type="submit" class="btn btn-sm btn-danger" ';
        $html .= 'onclick="return confirm(\'Tem certeza que deseja eliminar esta categoria?\')">';
        $html .= '<i class="fas fa-trash"></i> Eliminar';
        $html .= '</button>';
        $html .= '</form>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}
?>

<style>
/* ============================================
   CATEGORIAS - ESTILOS COMPLETOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
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
    align-items: center;
}

/* ============================================
   GRUPOS DE CATEGORIAS
   ============================================ */

.categoria-grupo {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 20px;
    margin-bottom: 20px;
    transition: var(--transition);
}

.categoria-grupo:hover {
    box-shadow: var(--shadow-md);
}

.categoria-grupo-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.categoria-grupo-header h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.categoria-grupo-header h3 i {
    margin-right: 8px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.12);
    color: var(--red-dark);
}

/* ============================================
   GRID DE CATEGORIAS
   ============================================ */

.categoria-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
}

.categoria-card {
    background: var(--white);
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: var(--transition);
}

.categoria-card:hover {
    border-color: var(--muted);
    box-shadow: var(--shadow-sm);
}

.categoria-card.inativa {
    opacity: 0.6;
}

.categoria-card.inativa .categoria-card-header {
    opacity: 0.7;
}

.categoria-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.categoria-cor {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    flex-shrink: 0;
    border: 1px solid var(--border);
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

.categoria-card-footer {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    padding-top: 10px;
    border-top: 1px solid var(--border);
}

/* ============================================
   EMPTY STATE
   ============================================ */

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

/* ============================================
   BOTÕES
   ============================================ */

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i { font-size: 13px; }

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

.btn-warning {
    background: linear-gradient(135deg, var(--orange), var(--orange-dark));
    color: var(--white);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.25);
}

.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
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
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 6px;
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .categoria-grid {
        grid-template-columns: 1fr;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-right {
        width: 100%;
        flex-wrap: wrap;
    }
    .page-header-right .btn {
        flex: 1;
        justify-content: center;
    }
    .page-title {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .categoria-card-footer {
        flex-direction: column;
    }
    .categoria-card-footer .btn {
        justify-content: center;
        width: 100%;
    }
    .page-title {
        font-size: 18px;
    }
}
</style>