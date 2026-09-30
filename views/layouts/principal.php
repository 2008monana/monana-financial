<?php
// Verificar sessão
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL_BASE . '/auth/login');
    exit;
}

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Utilizador';
$usuario_perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
$paginaAtiva = $paginaAtiva ?? 'dashboard';

// Nome da empresa: prioriza variável passada pela view, senão a sessão (definida no login)
if (!isset($empresa_nome) || $empresa_nome === '') {
    $empresa_nome = $_SESSION['empresa_nome'] ?? '';
}

// Fallback: se ainda não houver nome de empresa e existir empresa_id na sessão, busca na BD
if ($empresa_nome === '' && !empty($_SESSION['empresa_id']) && class_exists('Empresa')) {
    try {
        $empModel = new Empresa();
        $emp = $empModel->encontrarPorId((int) $_SESSION['empresa_id']);
        if ($emp) {
            $empresa_nome = $emp['nome'] ?? '';
            $_SESSION['empresa_nome'] = $empresa_nome;
        }
    } catch (Exception $e) {
        // silencioso — continua sem nome de empresa
    }
}

// =============================================
// PERFIS
// =============================================
$isSuperAdmin = $usuario_perfil === 'super_admin';
$isAdminEmpresa = $usuario_perfil === 'admin_empresa';
$isViewer = $usuario_perfil === 'visualizador';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tituloPagina ?? 'Dashboard'); ?> — MonanaFinancial</title>
    
    <?php $__fbv = @filemtime(CAMINHO_RAIZ . '/public/images/logo.png') ?: time(); ?>
    <!-- Favicon: gerado a partir de public/images/logo.png -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo URL_BASE; ?>/images/favicon-32.png?v=<?php echo $__fbv; ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?php echo URL_BASE; ?>/images/favicon-48.png?v=<?php echo $__fbv; ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo URL_BASE; ?>/images/favicon-192.png?v=<?php echo $__fbv; ?>">
    <link rel="apple-touch-icon" href="<?php echo URL_BASE; ?>/images/apple-touch-icon.png?v=<?php echo $__fbv; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo URL_BASE; ?>/favicon.ico?v=<?php echo $__fbv; ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo URL_BASE; ?>/favicon.ico?v=<?php echo $__fbv; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/css/formularios.css">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/css/responsivo.css?v=<?php echo file_exists(CAMINHO_RAIZ . '/public/css/responsivo.css') ? filemtime(CAMINHO_RAIZ . '/public/css/responsivo.css') : time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <style>
:root {
    --navy-deep: #0a1930;
    --navy: #0e2748;
    --navy-light: #173a67;
    --green: #22c55e;
    --green-dark: #16a34a;
    --blue: #3b82f6;
    --blue-dark: #2563eb;
    --purple: #8b5cf6;
    --purple-dark: #7c3aed;
    --orange: #f59e0b;
    --orange-dark: #d97706;
    --red: #ef4444;
    --red-dark: #dc2626;
    --gold: #fbbf24;
    --pink: #ec4899;
    --teal: #14b8a6;
    --ink: #1e293b;
    --muted: #94a3b8;
    --border: #e2e8f0;
    --bg: #f1f5f9;
    --white: #ffffff;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
    --radius: 14px;
    --radius-sm: 8px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== HEADER DO DASHBOARD ===== */
.dash-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 16px;
}

.dash-header-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.dash-title {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.dash-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

.empresa-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.period-selector {
    display: flex;
    gap: 4px;
    background: var(--white);
    padding: 4px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
}

.period-btn {
    padding: 6px 16px;
    border: none;
    border-radius: 6px;
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: var(--transition);
    font-family: 'Inter', sans-serif;
}

.period-btn:hover {
    color: var(--ink);
    background: rgba(0,0,0,0.04);
}

.period-btn.active {
    background: var(--navy);
    color: var(--white);
    box-shadow: 0 2px 8px rgba(14, 39, 72, 0.3);
}

.period-btn.active-period {
    background: var(--green);
    color: var(--white);
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}

/* ===== KPI CARDS ===== */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.kpi-grid-6 {
    grid-template-columns: repeat(6, 1fr);
}

.kpi-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid var(--border);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--green), var(--blue), var(--purple));
    opacity: 0.3;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
}

.kpi-card:hover::before {
    opacity: 1;
}

.kpi-icon-wrapper {
    flex-shrink: 0;
}

.kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--white);
    transition: var(--transition);
}

.kpi-card:hover .kpi-icon {
    transform: scale(1.05) rotate(-2deg);
}

.kpi-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
.kpi-icon.blue { background: linear-gradient(135deg, var(--blue), var(--blue-dark)); }
.kpi-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }
.kpi-icon.green { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.kpi-icon.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }
.kpi-icon.gold { background: linear-gradient(135deg, var(--gold), var(--orange)); }
.kpi-icon.orange { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }
.kpi-icon.pink { background: linear-gradient(135deg, var(--pink), #db2777); }
.kpi-icon.teal { background: linear-gradient(135deg, var(--teal), #0d9488); }

.kpi-info {
    flex: 1;
    min-width: 0;
}

.kpi-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    margin-bottom: 2px;
}

.kpi-value {
    display: block;
    font-family: 'Sora', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.kpi-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
}

.kpi-change.up { color: var(--green-dark); }
.kpi-change.down { color: var(--red-dark); }
.kpi-change.neutral { color: var(--muted); }

/* ============================================
   GRÁFICOS - CORRIGIDOS
   ============================================ */

.charts-row {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.chart-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 24px;
    border: 1px solid var(--border);
    transition: var(--transition);
}

.chart-card:hover {
    box-shadow: var(--shadow-md);
}

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.chart-header h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.chart-header h3 i {
    color: var(--green);
    margin-right: 8px;
}

.chart-period {
    font-size: 12px;
    color: var(--muted);
    background: var(--bg);
    padding: 4px 12px;
    border-radius: 12px;
}

/* CORREÇÃO: Container do gráfico com altura fixa e proporcional */
.chart-body {
    height: 260px;
    position: relative;
    width: 100%;
}

.chart-body canvas {
    width: 100% !important;
    height: 100% !important;
}

/* CORREÇÃO: Donut com layout lado a lado */
.donut-body {
    display: flex;
    align-items: center;
    gap: 30px;
    height: 260px;
}

.donut-container {
    width: 180px;
    height: 180px;
    flex-shrink: 0;
    position: relative;
}

.donut-container canvas {
    width: 100% !important;
    height: 100% !important;
}

.donut-legend {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: var(--transition);
}

.legend-item:hover {
    background: var(--bg);
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-label {
    flex: 1;
    color: var(--ink);
    font-weight: 500;
}

.legend-value {
    font-weight: 600;
    color: var(--ink);
}

.legend-pct {
    color: var(--muted);
    font-size: 12px;
    min-width: 40px;
    text-align: right;
}

/* ============================================
   TABELA - CORRIGIDA
   ============================================ */

.table-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 24px;
    border: 1px solid var(--border);
    margin-bottom: 28px;
    transition: var(--transition);
}

.table-card:hover {
    box-shadow: var(--shadow-md);
}

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.table-header h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.table-header h3 i {
    color: var(--purple);
    margin-right: 8px;
}

.btn-link {
    color: var(--blue);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: var(--transition);
}

.btn-link:hover {
    color: var(--blue-dark);
    gap: 10px;
}

.table-responsive {
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.table-modern thead th {
    padding: 12px 16px;
    background: #f8fafc;
    color: var(--muted);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}

.table-modern tbody td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: var(--ink);
    vertical-align: middle;
}

.table-modern tbody tr:hover {
    background: #f8fafc;
}

.table-modern tfoot td {
    padding: 14px 16px;
    background: #f8fafc;
    font-weight: 600;
    border-top: 2px solid var(--border);
}

.text-center { text-align: center; }
.text-right { text-align: right; }

.positive { color: var(--green-dark); font-weight: 600; }
.negative { color: var(--red-dark); font-weight: 600; }

/* ============================================
   BADGES E STATUS - CORRIGIDOS
   ============================================ */

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
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

.badge-warning {
    background: rgba(245, 158, 11, 0.12);
    color: var(--orange-dark);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.ativo {
    background: #dcfce7;
    color: var(--green-dark);
}

.status-badge.ativo i {
    color: var(--green);
    font-size: 8px;
}

.status-badge.inativo {
    background: #fee2e2;
    color: var(--red-dark);
}

.status-badge.inativo i {
    color: var(--red);
    font-size: 8px;
}

.status-badge.manutencao {
    background: #fef3c7;
    color: var(--orange-dark);
}

.status-badge.manutencao i {
    color: var(--orange);
    font-size: 8px;
}

/* ============================================
   CARDS INFERIORES - CORRIGIDOS
   ============================================ */

.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.bottom-grid-empresa {
    display: grid;
    grid-template-columns: 1fr 1fr 1.2fr;
    gap: 20px;
    margin-bottom: 28px;
}

/* CORREÇÃO: Todos os cards inferiores com o mesmo padrão visual */
.card-activities,
.card-alerts,
.card-performance,
.card-category,
.card-movements {
    background: var(--white);
    border-radius: var(--radius);
    padding: 20px;
    border: 1px solid var(--border);
    transition: var(--transition);
}

.card-activities:hover,
.card-alerts:hover,
.card-performance:hover,
.card-category:hover,
.card-movements:hover {
    box-shadow: var(--shadow-md);
}

.card-activities h4,
.card-alerts h4,
.card-performance h4,
.card-category h4,
.card-movements h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

.card-activities h4 i,
.card-alerts h4 i,
.card-performance h4 i,
.card-category h4 i,
.card-movements h4 i {
    margin-right: 8px;
    color: var(--muted);
}

/* ===== ACTIVITIES ===== */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: var(--radius-sm);
    transition: var(--transition);
    cursor: pointer;
}

.activity-item:hover {
    background: var(--bg);
}

.activity-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 13px;
    font-weight: 700;
    flex-shrink: 0;
}

.activity-info {
    flex: 1;
    min-width: 0;
}

.activity-name {
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
}

.activity-detail {
    font-size: 12px;
    color: var(--muted);
}

.activity-time {
    color: var(--muted);
    font-size: 11px;
}

/* ===== ALERTS ===== */
.alert-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    margin-bottom: 10px;
    transition: var(--transition);
    cursor: pointer;
    border-left: 4px solid transparent;
}

.alert-item:hover {
    background: var(--bg);
}

.alert-item.critico {
    border-left-color: var(--red);
    background: rgba(239, 68, 68, 0.04);
}

.alert-item.aviso {
    border-left-color: var(--orange);
    background: rgba(245, 158, 11, 0.04);
}

.alert-item.sucesso {
    border-left-color: var(--green);
    background: rgba(34, 197, 94, 0.04);
}

.alert-item.info {
    border-left-color: var(--blue);
    background: rgba(59, 130, 246, 0.04);
}

.alert-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 14px;
}

.alert-item.critico .alert-icon {
    background: rgba(239, 68, 68, 0.12);
    color: var(--red);
}

.alert-item.aviso .alert-icon {
    background: rgba(245, 158, 11, 0.12);
    color: var(--orange);
}

.alert-item.sucesso .alert-icon {
    background: rgba(34, 197, 94, 0.12);
    color: var(--green);
}

.alert-item.info .alert-icon {
    background: rgba(59, 130, 246, 0.12);
    color: var(--blue);
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    font-size: 13px;
    color: var(--ink);
}

.alert-desc {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}

/* ===== PERFORMANCE ===== */
.performance-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.performance-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.perf-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--ink);
    min-width: 110px;
    white-space: nowrap;
}

.perf-bar {
    flex: 1;
    height: 8px;
    background: var(--bg);
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.perf-fill {
    height: 100%;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    font-size: 9px;
    font-weight: 600;
    color: var(--white);
    padding-right: 4px;
    transition: width 0.6s ease;
    min-width: 24px;
}

/* ============================================
   CATEGORIAS - CORRIGIDAS (barras horizontais)
   ============================================ */

.category-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cat-label {
    font-size: 13px;
    color: var(--ink);
    min-width: 120px;
    white-space: nowrap;
    font-weight: 500;
}

.cat-bar {
    flex: 1;
    height: 10px;
    background: var(--bg);
    border-radius: 5px;
    overflow: hidden;
    position: relative;
}

.cat-fill {
    height: 100%;
    border-radius: 5px;
    transition: width 0.6s ease;
}

.cat-pct {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    min-width: 48px;
    text-align: right;
}

/* ============================================
   MOVIMENTOS - CORRIGIDOS
   ============================================ */

.movement-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.movement-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: var(--radius-sm);
    font-size: 12px;
    transition: var(--transition);
    border-bottom: 1px solid #f1f5f9;
}

.movement-item:last-child {
    border-bottom: none;
}

.movement-item:hover {
    background: var(--bg);
}

.mov-date {
    color: var(--muted);
    font-size: 11px;
    min-width: 44px;
    font-weight: 500;
}

.mov-desc {
    flex: 1;
    color: var(--ink);
    font-weight: 500;
}

.mov-branch {
    color: var(--muted);
    font-size: 12px;
    min-width: 70px;
}

.mov-value {
    font-weight: 600;
    min-width: 110px;
    text-align: right;
}

.movement-item.venda .mov-value { color: var(--green-dark); }
.movement-item.custo .mov-value { color: var(--red-dark); }
.movement-item.compra .mov-value { color: var(--orange-dark); }
.movement-item.devolucao .mov-value { color: var(--purple); }

/* ============================================
   AÇÕES RÁPIDAS - CORRIGIDAS
   ============================================ */

.quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 8px;
}

.quick-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 600;
    color: var(--white);
    text-decoration: none;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.quick-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.quick-btn.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
.quick-btn.success { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.quick-btn.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }
.quick-btn.orange { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }
.quick-btn.blue { background: linear-gradient(135deg, var(--blue), var(--blue-dark)); }
.quick-btn.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 1200px) {
    .kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .kpi-grid-6 { grid-template-columns: repeat(3, 1fr); }
    .charts-row { grid-template-columns: 1fr; }
    .bottom-grid { grid-template-columns: 1fr; }
    .bottom-grid-empresa { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
    .kpi-grid { grid-template-columns: 1fr 1fr; }
    .kpi-grid-6 { grid-template-columns: 1fr 1fr; }
    .dash-header { flex-direction: column; align-items: flex-start; }
    .period-selector { flex-wrap: wrap; }
    .period-btn { font-size: 11px; padding: 4px 10px; }
    .donut-body { flex-direction: column; height: auto; }
    .donut-container { width: 150px; height: 150px; }
    .chart-body { height: 200px; }
    .bottom-grid-empresa { grid-template-columns: 1fr; }
    .quick-actions { flex-direction: column; }
    .quick-btn { justify-content: center; }
    .table-modern { font-size: 11px; }
}

@media (max-width: 480px) {
    .kpi-grid { grid-template-columns: 1fr; }
    .kpi-grid-6 { grid-template-columns: 1fr; }
    .kpi-card { padding: 16px; }
    .kpi-value { font-size: 18px; }
    .chart-body { height: 160px; }
    .category-item { flex-wrap: wrap; }
    .cat-label { min-width: 80px; font-size: 12px; }
    .cat-pct { font-size: 12px; }
    .movement-item { flex-wrap: wrap; gap: 4px; }
    .mov-date { min-width: 36px; }
    .mov-value { min-width: 80px; }
}
        /* ============================================
           CSS COMPLETO - MONANAFINANCIAL
           Inclui: Layout, Sidebar, Dashboard, Relatórios
           ============================================ */
        :root {
            --navy-deep: #0a1930;
            --navy: #0e2748;
            --navy-light: #173a67;
            --green: #22c55e;
            --green-dark: #16a34a;
            --blue: #3b82f6;
            --blue-dark: #2563eb;
            --purple: #8b5cf6;
            --purple-dark: #7c3aed;
            --orange: #f59e0b;
            --orange-dark: #d97706;
            --red: #ef4444;
            --red-dark: #dc2626;
            --gold: #fbbf24;
            --ink: #1e293b;
            --muted: #94a3b8;
            --border: #e2e8f0;
            --bg: #f1f5f9;
            --white: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
            --radius: 14px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--ink);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
        }

        /* ============================================
           SIDEBAR - COM GRUPOS
           ============================================ */
        .sidebar {
            width: 250px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--navy-deep) 0%, var(--navy) 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 20px 14px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .side-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 4px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 16px;
        }

        .side-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #fff;
            object-fit: contain;
            padding: 3px;
            flex-shrink: 0;
        }

        .side-brand-text .name {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-size: 15px;
            color: #fff;
        }
        .side-brand-text .name span { color: var(--green); }
        .side-brand-text .sub {
            font-size: 9.5px;
            letter-spacing: 0.4px;
            color: rgba(255,255,255,0.4);
            margin-top: 1px;
        }

        .side-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .menu-grupo-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.3);
            padding: 12px 8px 4px;
            font-weight: 700;
            margin-top: 4px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 6px;
        }

        .menu-divider {
            border-top: 1px solid rgba(255,255,255,0.06);
            margin: 8px 4px 12px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(34,197,94,0.14);
            color: #fff;
            box-shadow: inset 3px 0 0 var(--green);
        }

        .nav-item.logout-item {
            margin-top: 4px;
            border-top: 1px solid rgba(255,255,255,0.06);
            padding-top: 12px;
            color: rgba(255,255,255,0.4);
        }
        .nav-item.logout-item:hover {
            color: var(--red);
            background: rgba(239,68,68,0.1);
        }

        .side-foot {
            padding-top: 14px;
            margin-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 10px;
            color: rgba(255,255,255,0.25);
            text-align: center;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        /* ============================================
           MAIN
           ============================================ */
        .main {
            flex: 1;
            min-width: 0;
            margin-left: 250px;
            width: calc(100% - 250px);
            max-width: 100%;
            overflow-x: hidden;
        }

        /* ============================================
           TOPBAR
           ============================================ */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 50;
            flex-wrap: wrap;
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .burger { font-size: 18px; color: var(--muted); cursor: pointer; display: none; }
        .greeting { font-size: 15px; font-weight: 600; color: var(--ink); }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .select-pill {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12px;
            color: var(--ink);
            background: var(--white);
            white-space: nowrap;
        }
        .select-pill i { color: var(--muted); }

        .bell-wrap {
            position: relative;
            cursor: pointer;
            color: var(--muted);
            font-size: 18px;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .bell-dot {
            position: absolute;
            top: -6px;
            right: -8px;
            background: var(--red);
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--navy-light), var(--navy));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .profile-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        .logout-btn {
            color: var(--muted);
            text-decoration: none;
            font-size: 16px;
            transition: var(--transition);
        }
        .logout-btn:hover { color: var(--red); }

        /* ============================================
           CONTENT
           ============================================ */
        .content {
            padding: 20px 24px 40px;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* ============================================
           FLASH
           ============================================ */
        .flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .flash-sucesso {
            background: rgba(34,197,94,0.10);
            color: var(--green-dark);
            border: 1px solid rgba(34,197,94,0.25);
        }
        .flash-erro {
            background: rgba(239,68,68,0.10);
            color: var(--red-dark);
            border: 1px solid rgba(239,68,68,0.25);
        }

        /* ============================================
           DASHBOARD - ESTILOS
           ============================================ */
        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dash-header-left {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .dash-title {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--navy-deep);
            margin: 0;
        }

        .dash-subtitle {
            font-size: 14px;
            color: var(--muted);
            margin: 0;
        }

        .empresa-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(34, 197, 94, 0.12);
            color: var(--green-dark);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .period-selector {
            display: flex;
            gap: 4px;
            background: var(--white);
            padding: 4px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            flex-wrap: wrap;
        }

        .period-btn {
            padding: 6px 16px;
            border: none;
            border-radius: 6px;
            background: transparent;
            font-size: 12px;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .period-btn:hover {
            color: var(--ink);
            background: rgba(0,0,0,0.04);
        }

        .period-btn.active {
            background: var(--navy);
            color: var(--white);
            box-shadow: 0 2px 8px rgba(14, 39, 72, 0.3);
        }

        .period-btn.active-period {
            background: var(--green);
            color: var(--white);
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
        }

        /* ---- KPI CARDS ---- */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 28px;
            width: 100%;
            max-width: 100%;
        }

        .kpi-grid-6 {
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
        }

        .kpi-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid var(--border);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            min-height: 72px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--green), var(--blue), var(--purple));
            opacity: 0.3;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-icon-wrapper {
            flex-shrink: 0;
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--white);
            transition: var(--transition);
            flex-shrink: 0;
        }

        .kpi-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
        .kpi-icon.blue { background: linear-gradient(135deg, var(--blue), var(--blue-dark)); }
        .kpi-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }
        .kpi-icon.green { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
        .kpi-icon.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }
        .kpi-icon.gold { background: linear-gradient(135deg, var(--gold), var(--orange)); }
        .kpi-icon.orange { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }

        .kpi-info {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .kpi-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--muted);
            margin-bottom: 1px;
            white-space: nowrap;
        }

        .kpi-value {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .kpi-value.large-value {
            font-size: 15px;
            letter-spacing: -0.3px;
        }

        .kpi-change {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            font-weight: 500;
            margin-top: 2px;
            white-space: nowrap;
        }

        .kpi-change.up { color: var(--green-dark); }
        .kpi-change.down { color: var(--red-dark); }
        .kpi-change.neutral { color: var(--muted); }

        /* ---- GRÁFICOS ---- */
        .charts-row {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .chart-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .chart-card:hover {
            box-shadow: var(--shadow-md);
        }

        .chart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .chart-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .chart-header h3 i {
            color: var(--green);
            margin-right: 8px;
        }

        .chart-period {
            font-size: 12px;
            color: var(--muted);
            background: var(--bg);
            padding: 4px 12px;
            border-radius: 12px;
        }

        .chart-body {
            height: 260px;
            position: relative;
            width: 100%;
        }

        .chart-body canvas {
            width: 100% !important;
            height: 100% !important;
        }

        /* ---- DONUT ---- */
        .donut-body {
            display: flex;
            align-items: center;
            gap: 24px;
            height: 260px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
            padding: 0 4px;
        }

        .donut-container {
            width: 170px;
            height: 170px;
            flex-shrink: 0;
            position: relative;
        }

        .donut-container canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .donut-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            padding: 3px 6px;
            border-radius: 6px;
            transition: var(--transition);
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .legend-item:hover {
            background: var(--bg);
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-label {
            flex: 1;
            color: var(--ink);
            font-weight: 500;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .legend-value {
            font-weight: 600;
            color: var(--ink);
            font-size: 12px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .legend-pct {
            color: var(--muted);
            font-size: 11px;
            min-width: 36px;
            text-align: right;
            flex-shrink: 0;
        }

        /* ---- TABELA ---- */
        .table-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--border);
            margin-bottom: 28px;
            transition: var(--transition);
        }

        .table-card:hover {
            box-shadow: var(--shadow-md);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .table-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .table-header h3 i {
            color: var(--purple);
            margin-right: 8px;
        }

        .btn-link {
            color: var(--blue);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .btn-link:hover {
            color: var(--blue-dark);
            gap: 10px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table-modern {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table-modern thead th {
            padding: 12px 16px;
            background: #f8fafc;
            color: var(--muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        .table-modern tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--ink);
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background: #f8fafc;
        }

        .table-modern tfoot td {
            padding: 14px 16px;
            background: #f8fafc;
            font-weight: 600;
            border-top: 2px solid var(--border);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .positive { color: var(--green-dark); font-weight: 600; }
        .negative { color: var(--red-dark); font-weight: 600; }

        /* ---- BADGES ---- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
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

        .badge-warning {
            background: rgba(245, 158, 11, 0.12);
            color: var(--orange-dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.ativo {
            background: #dcfce7;
            color: var(--green-dark);
        }

        .status-badge.ativo i {
            color: var(--green);
            font-size: 8px;
        }

        .status-badge.inativo {
            background: #fee2e2;
            color: var(--red-dark);
        }

        .status-badge.inativo i {
            color: var(--red);
            font-size: 8px;
        }

        /* ---- BOTTOM GRID ---- */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .bottom-grid-empresa {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .card-activities,
        .card-alerts,
        .card-performance,
        .card-category,
        .card-movements {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .card-activities:hover,
        .card-alerts:hover,
        .card-performance:hover,
        .card-category:hover,
        .card-movements:hover {
            box-shadow: var(--shadow-md);
        }

        .card-activities h4,
        .card-alerts h4,
        .card-performance h4,
        .card-category h4,
        .card-movements h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            margin: 0 0 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .card-activities h4 i,
        .card-alerts h4 i,
        .card-performance h4 i,
        .card-category h4 i,
        .card-movements h4 i {
            margin-right: 8px;
            color: var(--muted);
        }

        /* ---- ACTIVITIES ---- */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            cursor: pointer;
        }

        .activity-item:hover {
            background: var(--bg);
        }

        .activity-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .activity-info {
            flex: 1;
            min-width: 0;
        }

        .activity-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--ink);
        }

        .activity-detail {
            font-size: 12px;
            color: var(--muted);
        }

        .activity-time {
            color: var(--muted);
            font-size: 11px;
        }

        /* ---- ALERTS ---- */
        .alert-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            margin-bottom: 10px;
            transition: var(--transition);
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .alert-item:hover {
            background: var(--bg);
        }

        .alert-item.critico {
            border-left-color: var(--red);
            background: rgba(239, 68, 68, 0.04);
        }

        .alert-item.aviso {
            border-left-color: var(--orange);
            background: rgba(245, 158, 11, 0.04);
        }

        .alert-item.sucesso {
            border-left-color: var(--green);
            background: rgba(34, 197, 94, 0.04);
        }

        .alert-item.info {
            border-left-color: var(--blue);
            background: rgba(59, 130, 246, 0.04);
        }

        .alert-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }

        .alert-item.critico .alert-icon {
            background: rgba(239, 68, 68, 0.12);
            color: var(--red);
        }

        .alert-item.aviso .alert-icon {
            background: rgba(245, 158, 11, 0.12);
            color: var(--orange);
        }

        .alert-item.sucesso .alert-icon {
            background: rgba(34, 197, 94, 0.12);
            color: var(--green);
        }

        .alert-item.info .alert-icon {
            background: rgba(59, 130, 246, 0.12);
            color: var(--blue);
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            font-size: 13px;
            color: var(--ink);
        }

        .alert-desc {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* ---- PERFORMANCE ---- */
        .performance-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .performance-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .perf-label {
            font-size: 11px;
            font-weight: 500;
            color: var(--ink);
            min-width: 90px;
            max-width: 90px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }

        .perf-bar {
            flex: 1;
            height: 8px;
            background: var(--bg);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            min-width: 40px;
        }

        .perf-fill {
            height: 100%;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            font-size: 8px;
            font-weight: 700;
            color: var(--white);
            padding-right: 4px;
            transition: width 0.8s ease;
            min-width: 20px;
            position: relative;
        }

        .perf-pct {
            font-size: 11px;
            font-weight: 600;
            color: var(--ink);
            min-width: 38px;
            text-align: right;
            flex-shrink: 0;
        }

        .perf-fill.green { background: linear-gradient(90deg, var(--green), var(--green-dark)); }
        .perf-fill.blue { background: linear-gradient(90deg, var(--blue), var(--blue-dark)); }
        .perf-fill.purple { background: linear-gradient(90deg, var(--purple), var(--purple-dark)); }
        .perf-fill.orange { background: linear-gradient(90deg, var(--orange), var(--orange-dark)); }
        .perf-fill.red { background: linear-gradient(90deg, var(--red), var(--red-dark)); }

        /* ---- CATEGORIAS ---- */
        .category-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cat-label {
            font-size: 13px;
            color: var(--ink);
            min-width: 120px;
            white-space: nowrap;
            font-weight: 500;
        }

        .cat-bar {
            flex: 1;
            height: 10px;
            background: var(--bg);
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .cat-fill {
            height: 100%;
            border-radius: 5px;
            transition: width 0.6s ease;
        }

        .cat-pct {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            min-width: 48px;
            text-align: right;
        }

        /* ---- MOVIMENTOS ---- */
        .movement-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .movement-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            transition: var(--transition);
            border-bottom: 1px solid #f1f5f9;
        }

        .movement-item:last-child {
            border-bottom: none;
        }

        .movement-item:hover {
            background: var(--bg);
        }

        .mov-date {
            color: var(--muted);
            font-size: 11px;
            min-width: 44px;
            font-weight: 500;
        }

        .mov-desc {
            flex: 1;
            color: var(--ink);
            font-weight: 500;
        }

        .mov-branch {
            color: var(--muted);
            font-size: 12px;
            min-width: 70px;
        }

        .mov-value {
            font-weight: 600;
            min-width: 110px;
            text-align: right;
        }

        .movement-item.venda .mov-value { color: var(--green-dark); }
        .movement-item.custo .mov-value { color: var(--red-dark); }
        .movement-item.compra .mov-value { color: var(--orange-dark); }
        .movement-item.devolucao .mov-value { color: var(--purple); }

        /* ---- AÇÕES RÁPIDAS ---- */
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .quick-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .quick-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .quick-btn.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
        .quick-btn.success { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
        .quick-btn.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }
        .quick-btn.orange { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }
        .quick-btn.blue { background: linear-gradient(135deg, var(--blue), var(--blue-dark)); }

        /* ============================================
           RELATÓRIOS - ESTILOS
           ============================================ */
        .relatorio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .relatorio-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .relatorio-titulo {
            font-family: 'Sora', sans-serif;
            font-size: 26px;
            font-weight: 700;
            color: var(--navy-deep);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .relatorio-titulo i {
            color: var(--green);
            font-size: 24px;
        }

        .relatorio-titulo .titulo-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 12px;
            border-radius: 20px;
            background: rgba(34, 197, 94, 0.12);
            color: var(--green-dark);
            font-family: 'Inter', sans-serif;
        }

        .relatorio-subtitulo {
            font-size: 14px;
            color: var(--muted);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .empresa-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 14px;
            background: rgba(14, 39, 72, 0.08);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--navy);
        }

        .empresa-tag.global {
            background: rgba(139, 92, 246, 0.12);
            color: var(--purple);
        }

        .empresa-tag i {
            font-size: 12px;
        }

        .relatorio-header-right {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .relatorio-filtros {
            background: var(--white);
            border-radius: var(--radius);
            padding: 16px 20px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px 16px;
        }

        .relatorio-filtros .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .relatorio-filtros .filter-group label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .relatorio-filtros .filter-group input,
        .relatorio-filtros .filter-group select {
            padding: 8px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--white);
            min-width: 130px;
            transition: all 0.3s ease;
        }

        .relatorio-filtros .filter-group input:focus,
        .relatorio-filtros .filter-group select:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
        }

        .relatorio-filtros .filter-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            padding-bottom: 1px;
        }

        .relatorio-resumo {
            display: grid;
            gap: 12px;
            margin-bottom: 24px;
        }

        .relatorio-resumo.grid-3 { grid-template-columns: repeat(3, 1fr); }
        .relatorio-resumo.grid-4 { grid-template-columns: repeat(4, 1fr); }
        .relatorio-resumo.grid-5 { grid-template-columns: repeat(5, 1fr); }
        .relatorio-resumo.grid-6 { grid-template-columns: repeat(6, 1fr); }

        .resumo-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 16px 18px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .resumo-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0.3;
        }

        .resumo-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .resumo-card .resumo-label {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--muted);
            margin-bottom: 2px;
        }

        .resumo-card .resumo-valor {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--ink);
        }

        .resumo-card .resumo-valor.positivo { color: var(--green-dark); }
        .resumo-card .resumo-valor.negativo { color: var(--red-dark); }

        .relatorios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .relatorio-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--ink);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .relatorio-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .relatorio-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .relatorio-card:hover::before {
            opacity: 1;
        }

        .relatorio-card .relatorio-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--white);
            flex-shrink: 0;
        }

        .relatorio-card .relatorio-info {
            flex: 1;
        }

        .relatorio-card .relatorio-info h3 {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .relatorio-card .relatorio-info p {
            font-size: 13px;
            color: var(--muted);
            margin: 2px 0 0;
        }

        .relatorio-card .relatorio-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--green-dark);
            margin-top: 6px;
            transition: all 0.3s ease;
        }

        .relatorio-card .relatorio-link i {
            transition: all 0.3s ease;
        }

        .relatorio-card:hover .relatorio-link i {
            transform: translateX(4px);
        }

        .relatorio-tabela {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .relatorio-tabela .tabela-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .relatorio-tabela .tabela-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .relatorio-tabela .tabela-header h3 i {
            color: var(--purple);
            margin-right: 8px;
        }

        .relatorio-tabela .tabela-header .badge-info {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.12);
            color: var(--blue);
        }

        .relatorio-tabela .tabela-scroll {
            overflow-x: auto;
        }

        .relatorio-tabela table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .relatorio-tabela table thead th {
            padding: 10px 12px;
            background: #f8fafc;
            color: var(--muted);
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            white-space: nowrap;
        }

        .relatorio-tabela table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--ink);
            vertical-align: middle;
        }

        .relatorio-tabela table tbody tr:hover {
            background: #f8fafc;
        }

        .relatorio-tabela table tfoot td {
            padding: 12px 12px;
            background: #f8fafc;
            font-weight: 600;
            border-top: 2px solid var(--border);
        }

        .relatorio-vazio {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .relatorio-vazio i {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .relatorio-vazio h4 {
            font-size: 18px;
            color: var(--ink);
            margin-bottom: 4px;
        }

        /* ============================================
           BOTÕES GERAIS
           ============================================ */
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

        .btn i { font-size: 14px; }

        .btn-success {
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.25);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.35);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
        }

        .btn-secondary {
            background: var(--bg);
            color: var(--ink);
            border: 1.5px solid var(--border);
        }
        .btn-secondary:hover {
            background: var(--border);
        }

        .btn-outline {
            background: transparent;
            color: var(--muted);
            border: 1.5px solid var(--border);
        }
        .btn-outline:hover {
            background: var(--bg);
            color: var(--ink);
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ============================================
           RESPONSIVO
           ============================================ */
        @media (max-width: 1200px) {
            .kpi-grid { grid-template-columns: repeat(3, 1fr); }
            .kpi-grid-6 { grid-template-columns: repeat(3, 1fr); }
            .kpi-value { font-size: 16px; }
            .kpi-value.large-value { font-size: 14px; }
            .charts-row { grid-template-columns: 1fr; }
            .bottom-grid { grid-template-columns: 1fr; }
            .bottom-grid-empresa { grid-template-columns: 1fr; }
            .perf-label { min-width: 80px; max-width: 80px; font-size: 10px; }
            .donut-body { height: 220px; gap: 16px; }
            .donut-container { width: 150px; height: 150px; }
            .relatorio-resumo.grid-6 { grid-template-columns: repeat(3, 1fr); }
            .relatorio-resumo.grid-5 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 992px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .kpi-grid-6 { grid-template-columns: repeat(2, 1fr); }
            .kpi-card { padding: 12px 14px; min-height: 64px; }
            .kpi-icon { width: 34px; height: 34px; font-size: 14px; }
            .kpi-value { font-size: 14px; }
            .kpi-value.large-value { font-size: 12px; }
            .kpi-label { font-size: 9px; }
            .kpi-change { font-size: 9px; }
            .donut-body { height: 200px; gap: 14px; }
            .donut-container { width: 130px; height: 130px; }
            .legend-item { font-size: 11px; }
            .legend-label { font-size: 11px; }
            .legend-value { font-size: 11px; }
            .legend-pct { font-size: 10px; min-width: 32px; }
            .relatorio-resumo.grid-4 { grid-template-columns: repeat(2, 1fr); }
            .relatorio-resumo.grid-6 { grid-template-columns: repeat(2, 1fr); }
            .relatorio-resumo.grid-5 { grid-template-columns: repeat(2, 1fr); }
            .relatorios-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
            .relatorio-titulo { font-size: 22px; }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                left: 0;
                width: 280px;
                height: 100vh;
                z-index: 100;
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .main { margin-left: 0; width: 100%; }
            .burger { display: block; }
            
            .kpi-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .kpi-grid-6 { grid-template-columns: 1fr 1fr; gap: 8px; }
            .kpi-card { padding: 10px 12px; min-height: 56px; gap: 8px; }
            .kpi-icon { width: 30px; height: 30px; font-size: 12px; border-radius: 8px; }
            .kpi-value { font-size: 13px; }
            .kpi-value.large-value { font-size: 11px; }
            .kpi-label { font-size: 8px; }
            .kpi-change { font-size: 8px; }
            
            .dash-header { flex-direction: column; align-items: flex-start; }
            .period-selector { flex-wrap: wrap; }
            .period-btn { font-size: 10px; padding: 4px 8px; }
            .bottom-grid-empresa { grid-template-columns: 1fr; }
            .quick-actions { flex-direction: column; }
            .quick-btn { justify-content: center; }
            .table-modern { font-size: 11px; }
            .content { padding: 12px; }
            .topbar { padding: 10px 12px; }
            .topbar-right { gap: 6px; }
            .select-pill { font-size: 10px; padding: 4px 8px; }
            .profile-name { display: none; }
            
            .perf-label { min-width: 60px; max-width: 60px; font-size: 9px; }
            .perf-pct { font-size: 10px; min-width: 32px; }
            .perf-bar { height: 6px; }
            .perf-fill { font-size: 7px; min-width: 16px; }
            
            .donut-body {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                height: auto;
                min-height: 180px;
                gap: 12px;
                padding: 8px 0;
            }
            .donut-container {
                width: 130px;
                height: 130px;
                flex-shrink: 0;
            }
            .donut-legend {
                flex: 1;
                min-width: 120px;
                max-width: 100%;
                gap: 6px;
            }
            .legend-item {
                font-size: 10px;
                padding: 2px 4px;
                gap: 6px;
            }
            .legend-label { font-size: 10px; }
            .legend-value { font-size: 10px; }
            .legend-pct { font-size: 9px; min-width: 28px; }
            .legend-dot { width: 8px; height: 8px; }
            
            .relatorio-header { flex-direction: column; align-items: flex-start; }
            .relatorio-filtros { flex-direction: column; align-items: stretch; }
            .relatorio-filtros .filter-group input,
            .relatorio-filtros .filter-group select { min-width: auto; width: 100%; }
            .relatorio-resumo.grid-3 { grid-template-columns: 1fr; }
            .relatorio-resumo.grid-4 { grid-template-columns: 1fr 1fr; }
            .relatorio-resumo.grid-5 { grid-template-columns: 1fr 1fr; }
            .relatorio-resumo.grid-6 { grid-template-columns: 1fr 1fr; }
            .relatorios-grid { grid-template-columns: 1fr; }
            .relatorio-tabela table { font-size: 11px; }
            .relatorio-tabela table thead th,
            .relatorio-tabela table tbody td { padding: 6px 8px; }
            .btn-group { width: 100%; }
            .btn-group .btn { flex: 1; justify-content: center; }
        }

        @media (max-width: 600px) {
            .kpi-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
            .kpi-grid-6 { grid-template-columns: 1fr 1fr; gap: 6px; }
            .kpi-card { padding: 8px 10px; min-height: 48px; gap: 6px; }
            .kpi-icon { width: 26px; height: 26px; font-size: 11px; border-radius: 6px; }
            .kpi-value { font-size: 11px; }
            .kpi-value.large-value { font-size: 10px; }
            .kpi-label { font-size: 7px; letter-spacing: 0.2px; }
            .kpi-change { font-size: 7px; }
            
            .content { padding: 8px; }
            .topbar { padding: 6px 8px; }
            .topbar-right { gap: 4px; }
            .select-pill { font-size: 8px; padding: 2px 6px; }
            .bell-wrap { font-size: 14px; }
            .logout-btn { font-size: 14px; }
            .greeting { font-size: 11px; }
            .avatar { width: 26px; height: 26px; font-size: 10px; }
            
            .chart-body { height: 160px; }
            .chart-card { padding: 12px; }
            .table-card { padding: 10px; }
            .table-modern { font-size: 9px; }
            .table-modern thead th,
            .table-modern tbody td { padding: 4px 6px; }
            
            .category-item { flex-wrap: wrap; }
            .cat-label { min-width: 60px; font-size: 10px; }
            .cat-pct { font-size: 10px; min-width: 34px; }
            .cat-bar { height: 6px; }
            
            .movement-item { font-size: 9px; padding: 4px 6px; gap: 4px; }
            .mov-date { min-width: 30px; font-size: 8px; }
            .mov-value { min-width: 60px; font-size: 9px; }
            .mov-branch { min-width: 50px; font-size: 9px; }
            
            .perf-label { min-width: 40px; max-width: 40px; font-size: 8px; }
            .perf-pct { font-size: 8px; min-width: 26px; }
            .perf-bar { height: 5px; }
            .perf-fill { font-size: 6px; min-width: 12px; }
            
            .relatorio-resumo.grid-4 { grid-template-columns: 1fr; }
            .relatorio-resumo.grid-5 { grid-template-columns: 1fr; }
            .relatorio-resumo.grid-6 { grid-template-columns: 1fr; }
            .relatorio-titulo { font-size: 18px; flex-wrap: wrap; }
            .relatorio-titulo .titulo-badge { font-size: 10px; padding: 2px 8px; }
            .relatorio-tabela { padding: 12px; }
            .relatorio-tabela table { font-size: 10px; }
            .relatorio-tabela table thead th,
            .relatorio-tabela table tbody td { padding: 4px 6px; }
        }

        @media (max-width: 560px) and (max-height: 700px) {
            .donut-body {
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: auto;
                min-height: 200px;
                gap: 12px;
                padding: 12px 8px;
                width: 100%;
                max-width: 100%;
                overflow: hidden;
            }
            .donut-container { width: 140px; height: 140px; flex-shrink: 0; }
            .donut-legend {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4px 12px;
                width: 100%;
                max-width: 100%;
                padding: 0 4px;
            }
            .legend-item {
                font-size: 10px;
                padding: 2px 4px;
                gap: 4px;
                width: 100%;
                overflow: hidden;
            }
            .legend-dot { width: 8px; height: 8px; flex-shrink: 0; }
            .legend-label { font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; }
            .legend-value { font-size: 9px; flex-shrink: 0; }
            .legend-pct { font-size: 8px; min-width: 24px; flex-shrink: 0; }
            .bottom-grid-empresa { grid-template-columns: 1fr; gap: 12px; }
            .card-activities, .card-alerts, .card-performance, .card-category, .card-movements {
                padding: 12px;
                min-height: auto;
            }
            .card-activities h4, .card-alerts h4, .card-performance h4, .card-category h4, .card-movements h4 {
                font-size: 12px;
                margin-bottom: 10px;
                padding-bottom: 8px;
            }
            .kpi-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
            .kpi-grid-6 { grid-template-columns: 1fr 1fr; gap: 6px; }
            .kpi-card { padding: 8px 10px; min-height: 48px; gap: 6px; }
            .kpi-icon { width: 24px; height: 24px; font-size: 10px; border-radius: 6px; }
            .kpi-value { font-size: 11px; }
            .kpi-value.large-value { font-size: 10px; }
            .kpi-label { font-size: 7px; }
            .kpi-change { font-size: 7px; }
            .chart-body { height: 140px; }
            .chart-card { padding: 10px; }
            .chart-header h3 { font-size: 12px; }
            .chart-period { font-size: 9px; padding: 2px 8px; }
            .table-card { padding: 8px; }
            .table-modern { font-size: 8px; }
            .table-modern thead th, .table-modern tbody td { padding: 3px 4px; }
            .perf-label { min-width: 35px; max-width: 35px; font-size: 7px; }
            .perf-pct { font-size: 7px; min-width: 22px; }
            .perf-bar { height: 4px; }
            .perf-fill { font-size: 5px; min-width: 10px; }
            .cat-label { min-width: 50px; font-size: 9px; }
            .cat-pct { font-size: 9px; min-width: 30px; }
            .cat-bar { height: 5px; }
            .movement-item { font-size: 8px; padding: 3px 4px; gap: 3px; }
            .mov-date { min-width: 24px; font-size: 7px; }
            .mov-value { min-width: 50px; font-size: 8px; }
            .mov-branch { min-width: 40px; font-size: 8px; }
        }

        @media (max-width: 380px) {
            .donut-body { min-height: 180px; gap: 8px; padding: 8px 4px; }
            .donut-container { width: 110px; height: 110px; }
            .donut-legend { grid-template-columns: 1fr 1fr; gap: 2px 8px; }
            .legend-item { font-size: 8px; padding: 1px 2px; gap: 3px; }
            .legend-dot { width: 6px; height: 6px; }
            .legend-label { font-size: 7px; }
            .legend-value { font-size: 7px; }
            .legend-pct { font-size: 6px; min-width: 18px; }
            .kpi-grid { grid-template-columns: 1fr 1fr; gap: 4px; }
            .kpi-card { padding: 6px 8px; min-height: 40px; gap: 4px; }
            .kpi-icon { width: 20px; height: 20px; font-size: 8px; }
            .kpi-value { font-size: 9px; }
            .kpi-value.large-value { font-size: 8px; }
            .kpi-label { font-size: 6px; }
            .kpi-change { font-size: 6px; }
        }

        /* ============================================
           MICRO-ECRÃS (ex.: 277x667) — ajuste global
           ============================================ */
        @media (max-width: 320px) {
            html, body { max-width: 100%; overflow-x: hidden; }
            .content { padding: 8px 6px 24px; }
            .topbar { padding: 6px; }
            .sidebar { width: 240px; }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .table-modern { font-size: 8px; }
            .table-modern thead th, .table-modern tbody td { padding: 3px; white-space: nowrap; }
            .chart-body { height: 120px; }
            .chart-card, .table-card { padding: 6px; }
            .chart-header h3, .table-header h3 { font-size: 10px; }
            .dash-header h2, .page-title { font-size: 14px; }
            .period-btn { font-size: 8px; padding: 3px 5px; }
            .btn, .btn-success, .btn-primary { font-size: 10px; padding: 6px 8px; }
            input, select, textarea { font-size: 12px; max-width: 100%; }
            .modal, .caixa { width: 96% !important; }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        /* ============================================
           FECHO DIÁRIO - ESTILOS ADICIONAIS
           ============================================ */
        .fecho-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .fecho-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .fecho-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #fafbfc;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .fecho-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .fecho-header-left h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--ink);
            margin: 0;
        }

        .fecho-header-left h3 i {
            color: var(--green);
            margin-right: 8px;
        }

        .filial-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 12px;
            background: rgba(14, 39, 72, 0.08);
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--navy);
        }

        .fecho-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 20px;
        }

        .fecho-coluna {
            padding: 0 20px;
        }

        .fecho-coluna:first-child {
            border-right: 1px solid var(--border);
        }

        .coluna-header {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
        }

        .coluna-header.entrada {
            background: #dcfce7;
            color: var(--green-dark);
        }

        .coluna-header.saida {
            background: #fee2e2;
            color: var(--red-dark);
        }

        .campo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .campo-row:last-child {
            border-bottom: none;
        }

        .campo-row label {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink);
            min-width: 100px;
            flex-shrink: 0;
        }

        .campo-row.destaque label {
            font-weight: 600;
            color: var(--blue);
        }

        .campo-row.total label {
            font-weight: 700;
            color: var(--navy);
            font-size: 14px;
        }

        .input-wrap {
            display: flex;
            align-items: center;
            flex: 1;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            background: var(--white);
            transition: all 0.3s ease;
        }

        .input-wrap:focus-within {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
        }

        .input-wrap .prefix {
            padding: 8px 12px;
            background: var(--bg);
            color: var(--muted);
            font-weight: 600;
            font-size: 13px;
            border-right: 1px solid var(--border);
            flex-shrink: 0;
        }

        .input-wrap input {
            border: none !important;
            border-radius: 0 !important;
            padding: 8px 12px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--white);
            flex: 1;
            min-width: 0;
        }

        .input-wrap input:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        .input-wrap input.total-input {
            font-weight: 700;
            font-size: 15px;
            background: #f8fafc;
        }

        .input-wrap input.saldo-final {
            font-size: 16px;
        }

        .fecho-obs {
            padding: 0 20px 16px;
        }

        .fecho-obs label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .fecho-obs textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--white);
            resize: vertical;
            min-height: 60px;
        }

        .fecho-obs textarea:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
        }

        .fecho-acoes {
            display: flex;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            background: #fafbfc;
            flex-wrap: wrap;
        }

        /* ============================================
           RELATÓRIO PLANILHA - ESTILOS ADICIONAIS
           ============================================ */
        .planilha-tabela {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .planilha-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 1000px;
        }

        .planilha-table thead th {
            padding: 10px 12px;
            background: var(--navy);
            color: var(--white);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
            border: 1px solid var(--navy-light);
        }

        .planilha-table thead th.coluna-entrada {
            background: #16a34a;
            font-size: 12px;
        }

        .planilha-table thead th.coluna-saida {
            background: #dc2626;
            font-size: 12px;
        }

        .planilha-table thead th.coluna-total {
            background: var(--navy-deep);
            font-size: 12px;
        }

        .planilha-table thead th.sub {
            background: #0e2748;
            font-size: 9px;
            font-weight: 500;
            padding: 6px 8px;
        }

        .planilha-table thead th.sub.destaque {
            background: #0a1930;
            font-weight: 700;
        }

        .planilha-table tbody td {
            padding: 8px 10px;
            border: 1px solid var(--border);
            color: var(--ink);
        }

        .planilha-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .planilha-table tbody tr:hover {
            background: #eef2f7;
        }

        .planilha-table tbody td.destaque {
            font-weight: 600;
            color: var(--blue);
        }

        .planilha-table tfoot td {
            padding: 10px 12px;
            background: #f1f3f6;
            font-weight: 700;
            border: 1px solid var(--border);
            border-top: 2px solid var(--navy);
        }

        .planilha-table tfoot td.destaque {
            background: #0e2748;
            color: var(--white);
        }

        .resumo-consolidado {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .resumo-consolidado .resumo-card.destaque {
            background: linear-gradient(135deg, var(--navy), var(--navy-light));
            border-color: var(--navy);
        }

        .resumo-consolidado .resumo-card.destaque .resumo-label {
            color: rgba(255, 255, 255, 0.7);
        }

        .resumo-consolidado .resumo-card.destaque .resumo-valor {
            color: var(--white);
        }
    </style>
</head>
<body>

<!-- ===== OVERLAY MOBILE ===== -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="fecharSidebar()"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="side-brand">
        <?php
            // Logotipo da empresa (Configurações da empresa → Logotipo); cai no logo padrão se não existir.
            $logoEmpresa = '';
            if (!empty($_SESSION['empresa_id'])) {
                static $logoCache = null;
                if ($logoCache === null) {
                    $logoCache = [];
                    try {
                        require_once CAMINHO_RAIZ . '/models/Configuracao.php';
                        $cfgModel = new Configuracao();
                        foreach ($cfgModel->obterTodas(null) as $k => $v) { $logoCache['g_' . $k] = $v; }
                        foreach ($cfgModel->obterTodas((int)$_SESSION['empresa_id']) as $k => $v) { $logoCache[$k] = $v; }
                    } catch (Throwable $e) { /* silencioso: usa o logo padrão */ }
                }
                $logoEmpresa = $logoCache['logotipo'] ?? '';
            }
        ?>
        <?php
            // Normaliza o caminho do logotipo para URL limpa (sem /public)
            $logoSrc = '';
            if ($logoEmpresa !== '') {
                if (preg_match('#^(https?://|data:image)#i', $logoEmpresa)) {
                    $logoSrc = $logoEmpresa; // URL externa ou base64: usa como está
                } else {
                    $caminhoRel = preg_replace('#^(/?(public/)?uploads/)#i', 'uploads/', ltrim($logoEmpresa, '/'));
                    if (strpos($caminhoRel, 'uploads/') !== 0) {
                        $caminhoRel = 'uploads/' . $caminhoRel;
                    }
                    // Verifica se o ficheiro existe em /public/uploads ou /uploads (raiz)
                    $existeFisico = is_file(CAMINHO_RAIZ . '/public/' . $caminhoRel);
                    if (!$existeFisico && is_file(CAMINHO_RAIZ . '/' . $caminhoRel)) {
                        // Logotipo gravado na raiz por versão antiga do sistema:
                        // copia para /public/uploads para poder ser servido.
                        $origem = CAMINHO_RAIZ . '/' . $caminhoRel;
                        $destino = CAMINHO_RAIZ . '/public/' . $caminhoRel;
                        @mkdir(dirname($destino), 0775, true);
                        $existeFisico = @copy($origem, $destino);
                    }
                    if ($existeFisico) {
                        $logoSrc = URL_BASE . '/' . $caminhoRel . '?v=' . (@filemtime(CAMINHO_RAIZ . '/public/' . $caminhoRel) ?: time());
                    } else {
                        // Ficheiro não existe — cai no logo padrão
                        $logoSrc = URL_BASE . '/images/logo.png';
                    }
                }
            } else {
                $logoSrc = URL_BASE . '/images/logo.png';
            }
        ?>
        <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="MonanaFinancial" class="side-logo" onerror="this.src='<?php echo URL_BASE; ?>/images/logo.png';">
        <div class="side-brand-text">
            <div class="name">Monana<span>Financial</span></div>
            <div class="sub">GESTÃO FINANCEIRA</div>
        </div>
    </div>

    <nav class="side-nav">
        <!-- GRUPO 1: PRINCIPAL -->
        <div class="menu-grupo-label">PRINCIPAL</div>
        <a class="nav-item <?php echo $paginaAtiva === 'dashboard' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/dashboard">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'transacoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/transacoes">
            <i class="fa-solid fa-list-ul"></i> Movimentos
        </a>

        <!-- GRUPO 2: RELATÓRIOS -->
        <div class="menu-grupo-label">RELATÓRIOS</div>
        <a class="nav-item <?php echo $paginaAtiva === 'relatorios' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/relatorios">
            <i class="fa-solid fa-chart-column"></i> Relatórios
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'planilha' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/relatorios/diario-planilha">
            <i class="fa-solid fa-table"></i> Planilha
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'pesquisa' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/relatorios/pesquisa">
            <i class="fa-solid fa-magnifying-glass"></i> Pesquisa de Movimentos
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'categorias' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/categorias">
            <i class="fa-solid fa-tags"></i> Categorias
        </a>

        <!-- GRUPO 3: GESTÃO -->
        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <div class="menu-grupo-label">GESTÃO</div>
        <?php endif; ?>

        <?php if ($isSuperAdmin): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'empresas' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/empresas">
            <i class="fa-solid fa-building"></i> Empresas
        </a>
        <?php endif; ?>

        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'filiais' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/filiais">
            <i class="fa-solid fa-code-branch"></i> Filiais
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'usuarios' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/usuarios">
            <i class="fa-solid fa-users"></i> Utilizadores
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'metodos_pagamento' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/metodos-pagamento">
            <i class="fa-solid fa-money-bill-wave"></i> Métodos de Pagamento
        </a>
        <?php endif; ?>

        <?php if ($isSuperAdmin): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'assinaturas' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/assinaturas">
            <i class="fa-solid fa-crown"></i> Assinaturas
        </a>
        <?php endif; ?>

        <!-- GRUPO 4: FERRAMENTAS -->
        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <div class="menu-grupo-label">FERRAMENTAS</div>
        <a class="nav-item <?php echo $paginaAtiva === 'fecho-diario' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/transacoes/fechoDiario">
            <i class="fa-solid fa-file-invoice-day"></i> Fecho Diário
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'backups' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/backups">
            <i class="fa-solid fa-database"></i> Backups
        </a>
        <?php endif; ?>

        <!-- GRUPO 5: ADMINISTRAÇÃO -->
        <?php if ($isSuperAdmin || $isAdminEmpresa): ?>
        <div class="menu-grupo-label">ADMINISTRAÇÃO</div>
        <?php if ($isSuperAdmin || $isAdminEmpresa): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'logs' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/logs">
            <i class="fa-solid fa-clipboard-list"></i> Logs Auditoria
        </a>
        <?php endif; ?>
        <a class="nav-item <?php echo $paginaAtiva === 'configuracoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/configuracoes">
            <i class="fa-solid fa-gear"></i> Configurações
        </a>
        <?php endif; ?>

        <div class="menu-divider"></div>

        <!-- GRUPO 6: CONTA -->
        <div class="menu-grupo-label">CONTA</div>
        <a class="nav-item <?php echo $paginaAtiva === 'perfil' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/perfil">
            <i class="fa-solid fa-user-cog"></i> Meu Perfil
        </a>
        <?php if ($isAdminEmpresa): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'notificacoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/notificacoes">
            <i class="fa-solid fa-bell"></i> Notificações
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'minha_assinatura' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/assinaturas/minha">
            <i class="fa-solid fa-id-card"></i> Minha Assinatura
        </a>
        <?php endif; ?>

        <a class="nav-item logout-item" href="#" onclick="confirmarLogout(event)">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </a>
    </nav>

    <div class="side-foot">© <?php echo date('Y'); ?> MonanaFinancial</div>
</aside>

<!-- ===== MAIN ===== -->
<div class="main">

    <!-- ===== TOPBAR ===== -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="burger" onclick="abrirSidebar()">
                <i class="fa-solid fa-bars"></i>
            </div>
            <div class="greeting">
                <?php 
                if ($isSuperAdmin): 
                    echo 'Todas as empresas';
                elseif (!empty($empresa_nome)): // admin empresa e colaboradores veem o nome da empresa
                    echo htmlspecialchars($empresa_nome);
                else:
                    echo htmlspecialchars($tituloPagina ?? 'MonanaFinancial');
                endif; 
                ?>
            </div>
        </div>
        <div class="topbar-right">
            <div class="select-pill">
                <i class="fa-regular fa-calendar"></i>
                <?php echo date('d/m/Y'); ?>
            </div>
            <a class="bell-wrap" href="<?php echo URL_BASE; ?>/notificacoes" title="Notificações" id="sino-notificacoes" style="display:none;">
                <i class="fa-regular fa-bell"></i>
                <div class="bell-dot" style="display:none;">0</div>
            </a>
            <a class="profile" href="<?php echo URL_BASE; ?>/perfil">
                <div class="avatar"><?php echo strtoupper(substr($usuario_nome, 0, 2)); ?></div>
                <div class="profile-name"><?php echo htmlspecialchars($usuario_nome); ?></div>
            </a>
            <a href="#" onclick="confirmarLogout(event)" class="logout-btn" title="Sair">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <?php
    // ===== FAIXA DE AVISO DE ASSINATURA (apenas para utilizadores de empresa) =====
    if (!$isSuperAdmin && !empty($_SESSION['empresa_id'])) {
        $caminhoAssinaturaHelper = CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';
        if (file_exists($caminhoAssinaturaHelper)) {
            require_once $caminhoAssinaturaHelper;
            $estAs = AssinaturaHelper::detalharParaFaixa(AssinaturaHelper::estadoActual()); // memorizado pelo middleware (sem repetir a consulta)
            if ($estAs && in_array($estAs['estado'] ?? '', ['carencia', 'por_vencer'], true)) {
                // "Ver detalhes" só aparece para quem tem acesso à página Minha Assinatura.
                $podeVerMinhaAssinatura = $isAdminEmpresa;
                $linkWaFaixa = AssinaturaHelper::linkWhatsapp(
                    'Olá! Sou da empresa ' . ($_SESSION['empresa_nome'] ?? '') . '. '
                    . ($estAs['estado'] === 'carencia' ? 'A nossa assinatura expirou. Gostaria de negociar a assinatura do Monana Financial.'
                                                       : 'A nossa assinatura está perto de terminar. Gostaria de renovar a assinatura do Monana Financial.')
                );
                $segRestantes = max(0, (int) ($estAs['segundos_restantes'] ?? 0));
                $limiteTs = isset($estAs['limite_carencia']) ? strtotime(str_replace(' ', 'T', $estAs['limite_carencia'])) : null;
                if ($estAs['estado'] === 'carencia'):
                    // Sem botão de fechar: não se pode dispensar a carência.
                    $codPlanoFaixa = $estAs['plano']['codigo'] ?? '';
                    ?>
                    <div class="assin-faixa assin-faixa-carencia" id="assin-faixa"
                         data-limite="<?php echo $limiteTs ? (int) $limiteTs : ''; ?>">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>A sua assinatura <?php echo $codPlanoFaixa === 'gratuito' ? 'gratuita cessou' : 'expirou'; ?>.
                            Faltam <strong id="assin-contagem"><?php echo htmlspecialchars(AssinaturaHelper::tempoLegivel($segRestantes)); ?></strong>
                            para o bloqueio do acesso.</span>
                        <span class="assin-fixa-acoes">
                            <?php if ($linkWaFaixa): ?>
                                <a class="btn btn-sm btn-success" href="<?php echo htmlspecialchars($linkWaFaixa); ?>" target="_blank" rel="noopener">
                                    <i class="fa-brands fa-whatsapp"></i> Negociar no WhatsApp
                                </a>
                            <?php endif; ?>
                            <?php if ($podeVerMinhaAssinatura): ?>
                            <a class="assin-faixa-link" href="<?php echo URL_BASE; ?>/assinaturas/minha">Ver detalhes</a>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php
                else:
                    // Últimos 7 dias: pode ser fechada até ao fim da sessão.
                    if (empty($_SESSION['assin_faixa_7d_fechada'])):
                    $diasRestantes = (int) ceil($segRestantes / 86400);
                    ?>
                    <div class="assin-faixa assin-faixa-vencimento" id="assin-faixa">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>A sua assinatura termina em <?php echo $diasRestantes; ?> <?php echo $diasRestantes === 1 ? 'dia' : 'dias'; ?>.</span>
                        <span class="assin-fixa-acoes">
                            <?php if ($linkWaFaixa): ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars($linkWaFaixa); ?>" target="_blank" rel="noopener">
                                    <i class="fa-solid fa-rotate"></i> Renovar
                                </a>
                            <?php endif; ?>
                            <?php if ($podeVerMinhaAssinatura): ?>
                            <a class="assin-faixa-link" href="<?php echo URL_BASE; ?>/assinaturas/minha">Ver detalhes</a>
                            <?php endif; ?>
                            <button type="button" class="assin-faixa-fechar" title="Fechar"
                                    onclick="document.getElementById('assin-faixa').remove();
                                             fetch('<?php echo URL_BASE; ?>/assinaturas/esconderFaixa', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}});">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </span>
                    </div>
                    <?php
                    endif;
                endif;
            }
        }
    }
    ?>

    <!-- ===== CONTENT ===== -->
    <div class="content">
        <?php
        // Flash messages
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            $tipo = $flash['tipo'] ?? 'sucesso';
            $mensagem = $flash['mensagem'] ?? '';
            if ($mensagem) {
                echo '<div class="flash flash-' . $tipo . '">';
                echo '<i class="fa-solid ' . ($tipo === 'sucesso' ? 'fa-circle-check' : 'fa-circle-exclamation') . '"></i>';
                echo htmlspecialchars($mensagem);
                echo '</div>';
            }
        }
        ?>
        <?php echo $conteudo ?? '<p>Conteúdo não disponível.</p>'; ?>
    </div>
</div>

<script>
function abrirSidebar() {
    document.getElementById('sidebar').classList.add('mobile-open');
    document.getElementById('sidebar-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebar-overlay').classList.remove('active');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('sidebar-overlay')?.addEventListener('click', fecharSidebar);
});
</script>
<script>
// ============================================
// NOTIFICAÇÕES - ATUALIZAÇÃO AUTOMÁTICA
// ============================================

/**
 * Atualiza a contagem de notificações não lidas
 * Busca via AJAX e atualiza o badge
 */
function atualizarContagemNotificacoes() {
    const sino = document.getElementById('sino-notificacoes');
    // Sino oculto por padrão: só aparece quando existe pelo menos 1 notificação não lida.
    if (!sino) return;

    const dot = sino.querySelector('.bell-dot');

    fetch('<?php echo URL_BASE; ?>/notificacoes/contagem')
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.sucesso && typeof data.nao_lidas !== 'undefined') {
                const count = parseInt(data.nao_lidas) || 0;
                if (count > 0) {
                    sino.style.display = 'flex';
                    if (dot) {
                        dot.style.display = 'flex';
                        dot.textContent = count > 99 ? '99+' : count;
                    }
                } else {
                    sino.style.display = 'none';
                    if (dot) dot.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.warn('Erro ao buscar contagem de notificações:', error);
            // Manter estado atual em caso de erro
        });
}

/**
 * Verifica se há notificações a cada 60 segundos
 */
function iniciarPollingNotificacoes() {
    // Atualizar imediatamente ao carregar
    atualizarContagemNotificacoes();

    // Atualizar a cada 60 segundos
    setInterval(atualizarContagemNotificacoes, 60000);

    // Atualizar quando a janela ganhar foco (utilizador voltou ao sistema)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            atualizarContagemNotificacoes();
        }
    });
}

// ============================================
// SIDEBAR MOBILE
// ============================================

function abrirSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.add('mobile-open');
    if (overlay) overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function fecharSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar) sidebar.classList.remove('mobile-open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
}

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Notificações
    iniciarPollingNotificacoes();

    // Fechar sidebar ao clicar no overlay
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', fecharSidebar);
    }

    // Ajustar valores grandes nos KPIs
    document.querySelectorAll('.kpi-value').forEach(function(el) {
        const text = el.textContent.replace(/[^0-9]/g, '');
        if (text.length > 6) {
            el.classList.add('large-value');
        }
    });
});

/* ===== MODAL DE CONFIRMAÇÃO DE LOGOUT ===== */
function confirmarLogout(e) {
    if (e) e.preventDefault();
    const modal = document.getElementById('modalLogout');
    if (modal) modal.classList.add('aberto');
}

function fecharModalLogout() {
    const modal = document.getElementById('modalLogout');
    if (modal) modal.classList.remove('aberto');
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalLogout');
    if (modal) {
        // Fechar ao clicar fora do cartão
        modal.addEventListener('click', function(ev) {
            if (ev.target === modal) fecharModalLogout();
        });
        // Fechar com a tecla ESC
        document.addEventListener('keydown', function(ev) {
            if (ev.key === 'Escape') fecharModalLogout();
        });
        // Botão "Sim, sair"
        const btnSim = document.getElementById('btnLogoutSim');
        if (btnSim) {
            btnSim.addEventListener('click', function() {
                window.location.href = '<?php echo URL_BASE; ?>/auth/logout';
            });
        }
        // Botão "Não, permanecer"
        const btnNao = document.getElementById('btnLogoutNao');
        if (btnNao) {
            btnNao.addEventListener('click', fecharModalLogout);
        }
    }
});
</script>

<!-- MODAL CONFIRMAÇÃO LOGOUT -->
<div id="modalLogout" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; padding:32px 28px; max-width:400px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3); text-align:center;">
        <div style="width:64px; height:64px; margin:0 auto 16px; border-radius:50%; background:#fef2f2; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-right-from-bracket" style="font-size:26px; color:#dc2626;"></i>
        </div>
        <h3 style="margin:0 0 8px; font-size:18px; color:#0f172a;">Terminar sessão?</h3>
        <p style="margin:0 0 24px; font-size:14px; color:#64748b;">Tem a certeza que deseja sair do sistema? Terá de voltar a iniciar sessão para aceder novamente.</p>
        <div style="display:flex; gap:12px; justify-content:center;">
            <button id="btnLogoutNao" style="flex:1; padding:11px 16px; border:1px solid #e2e8f0; background:#f8fafc; color:#334155; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">Não, permanecer</button>
            <button id="btnLogoutSim" style="flex:1; padding:11px 16px; border:none; background:#dc2626; color:#fff; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">Sim, sair</button>
        </div>
    </div>
</div>

<style>
#modalLogout.aberto { display: flex !important; animation: fadeInModal .2s ease; }
@keyframes fadeInModal { from { opacity: 0; } to { opacity: 1; } }
</style>

<style>
/* ---- Faixa de aviso de assinatura (módulo Assinaturas) ---- */
.assin-faixa{display:flex;align-items:center;gap:12px;padding:12px 24px;font-size:13.5px;font-weight:600;flex-wrap:wrap}
.assin-fixa-acoes{margin-left:auto;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.assin-faixa-carencia{background:#fef3c7;color:#b45309;border-left:4px solid var(--orange)}
.assin-faixa-vencimento{background:#eff6ff;color:#1e3a8a;border-left:4px solid var(--blue)}
.assin-faixa i.fa-solid{font-size:16px}
.assin-faixa-link{color:inherit;text-decoration:underline;font-weight:700;font-size:13px}
.assin-faixa-fechar{background:none;border:none;cursor:pointer;color:inherit;font-size:15px;padding:4px}
@media(max-width:768px){.assin-faixa{padding:10px 14px}.assin-fixa-acoes{margin-left:0}}
</style>

<script>
// Contagem decrescente da faixa de carência — actualiza a cada minuto, sem recarregar a página.
(function() {
    const faixa = document.getElementById('assin-faixa');
    if (!faixa || !faixa.dataset.limite) return;
    const limite = parseInt(faixa.dataset.limite, 10);
    const alvo = document.getElementById('assin-contagem');
    if (!alvo || !limite) return;
    function fmt(seg) {
        if (seg <= 0) return '0min';
        const d = Math.floor(seg / 86400), h = Math.floor((seg % 86400) / 3600), m = Math.floor((seg % 3600) / 60);
        if (d >= 2) return d + ' dias';
        if (d === 1) return '1 dia';
        if (h > 0) return h + 'h ' + String(m).padStart(2, '0') + 'min';
        return Math.max(1, m) + 'min';
    }
    setInterval(function() {
        const restantes = Math.max(0, Math.floor(limite - Date.now() / 1000));
        alvo.textContent = fmt(restantes);
    }, 60000);
})();
</script>
</body>
</html>
