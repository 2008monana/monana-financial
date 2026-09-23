<?php
// Verificar sessão
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . URL_BASE . '/auth/login');
    exit;
}

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Utilizador';
$usuario_perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
$paginaAtiva = $paginaAtiva ?? 'dashboard';
$empresa_nome = $empresa_nome ?? '';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($tituloPagina ?? 'Dashboard'); ?> — MonanaFinancial</title>
    
    <link rel="icon" href="<?php echo URL_BASE; ?>/images/favicon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_BASE; ?>/css/formularios.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    
    <style>
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
        <img src="<?php echo URL_BASE; ?>/images/logo.png" alt="MonanaFinancial" class="side-logo">
        <div class="side-brand-text">
            <div class="name">Monana<span>Financial</span></div>
            <div class="sub">GESTÃO FINANCEIRA</div>
        </div>
    </div>

    <nav class="side-nav">
        <!-- GRUPO 1: PRINCIPAL -->
        <div class="menu-grupo-label">PRINCIPAL</div>
        <a class="nav-item <?php echo $paginaAtiva === 'dashboard' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/dashboard/index">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'transacoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/transacoes/index">
            <i class="fa-solid fa-list-ul"></i> Movimentos
        </a>

        <!-- GRUPO 2: RELATÓRIOS -->
        <div class="menu-grupo-label">RELATÓRIOS</div>
        <a class="nav-item <?php echo $paginaAtiva === 'relatorios' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/relatorios/index">
            <i class="fa-solid fa-chart-column"></i> Relatórios
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'planilha' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/relatorios/diario-planilha">
            <i class="fa-solid fa-table"></i> Planilha
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'categorias' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/categorias/index">
            <i class="fa-solid fa-tags"></i> Categorias
        </a>

        <!-- GRUPO 3: GESTÃO -->
        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <div class="menu-grupo-label">GESTÃO</div>
        <?php endif; ?>

        <?php if ($isSuperAdmin): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'empresas' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/empresas/index">
            <i class="fa-solid fa-building"></i> Empresas
        </a>
        <?php endif; ?>

        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <a class="nav-item <?php echo $paginaAtiva === 'filiais' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/filiais/index">
            <i class="fa-solid fa-code-branch"></i> Filiais
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'usuarios' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/usuarios/index">
            <i class="fa-solid fa-users"></i> Utilizadores
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'funcionarios' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/funcionarios/index">
            <i class="fa-solid fa-user-tie"></i> Funcionários
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'metodos_pagamento' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/metodos-pagamento/index">
            <i class="fa-solid fa-money-bill-wave"></i> Métodos de Pagamento
        </a>
        <?php endif; ?>

        <!-- GRUPO 4: FERRAMENTAS -->
        <?php if ($isAdminEmpresa || $isSuperAdmin): ?>
        <div class="menu-grupo-label">FERRAMENTAS</div>
        <a class="nav-item <?php echo $paginaAtiva === 'fecho-diario' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/transacoes/fechoDiario">
            <i class="fa-solid fa-file-invoice-day"></i> Fecho Diário
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'backups' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/backups/index">
            <i class="fa-solid fa-database"></i> Backups
        </a>
        <?php endif; ?>

        <!-- GRUPO 5: ADMINISTRAÇÃO -->
        <?php if ($isSuperAdmin): ?>
        <div class="menu-grupo-label">ADMINISTRAÇÃO</div>
        <a class="nav-item <?php echo $paginaAtiva === 'logs' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/logs/index">
            <i class="fa-solid fa-clipboard-list"></i> Logs Auditoria
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'configuracoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/configuracoes/index">
            <i class="fa-solid fa-gear"></i> Configurações
        </a>
        <?php endif; ?>

        <div class="menu-divider"></div>

        <!-- GRUPO 6: CONTA -->
        <div class="menu-grupo-label">CONTA</div>
        <a class="nav-item <?php echo $paginaAtiva === 'perfil' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/perfil/index">
            <i class="fa-solid fa-user-cog"></i> Meu Perfil
        </a>
        <a class="nav-item <?php echo $paginaAtiva === 'notificacoes' ? 'active' : ''; ?>" href="<?php echo URL_BASE; ?>/notificacoes/index">
            <i class="fa-solid fa-bell"></i> Notificações
        </a>

        <a class="nav-item logout-item" href="<?php echo URL_BASE; ?>/auth/logout">
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
                elseif ($isAdminEmpresa && !empty($empresa_nome)):
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
            <a class="bell-wrap" href="<?php echo URL_BASE; ?>/notificacoes/index" title="Notificações">
                <i class="fa-regular fa-bell"></i>
                <div class="bell-dot">0</div>
            </a>
            <a class="profile" href="<?php echo URL_BASE; ?>/perfil/index">
                <div class="avatar"><?php echo strtoupper(substr($usuario_nome, 0, 2)); ?></div>
                <div class="profile-name"><?php echo htmlspecialchars($usuario_nome); ?></div>
            </a>
            <a href="<?php echo URL_BASE; ?>/auth/logout" class="logout-btn" title="Sair">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

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
    const dot = document.getElementById('bell-dot');
    if (!dot) return;

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
                    dot.style.display = 'flex';
                    dot.textContent = count > 99 ? '99+' : count;
                } else {
                    dot.style.display = 'none';
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
</script>
</body>
</html>
