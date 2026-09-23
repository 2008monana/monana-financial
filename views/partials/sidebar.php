<?php
/** Navegação visível apenas para páginas permitidas ao utilizador autenticado. */
require_once CAMINHO_RAIZ . '/models/Modulo.php';

$perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
$paginaAtiva = $paginaAtiva ?? 'dashboard';
$acessoTotal = $perfil === 'super_admin';
$modulosAdminEmpresa = ['dashboard', 'movimentos', 'fecho_diario', 'relatorios', 'planilha', 'categorias', 'filiais', 'usuarios', 'backups', 'logs', 'perfil', 'notificacoes'];
$moduloModel = new Modulo();
$permitidos = [];
if (!$acessoTotal && !empty($_SESSION['usuario_id'])) {
    foreach ($moduloModel->getPermissoesUsuario((int) $_SESSION['usuario_id']) as $modulo) {
        if ((int) $modulo['permitido'] === 1) $permitidos[$modulo['nome']] = true;
    }
}
$temAcessoMenu = static function (string $modulo) use ($acessoTotal, $perfil, $modulosAdminEmpresa, $permitidos): bool {
    if ($acessoTotal) return true;
    if ($perfil === 'admin_empresa') return in_array($modulo, $modulosAdminEmpresa, true);
    return !empty($permitidos[$modulo]);
};
$itens = [
    'principal' => [
        ['dashboard/index', 'fa-house', 'Dashboard', 'dashboard'],
        ['transacoes/index', 'fa-list-ul', 'Movimentos', 'movimentos'],
        ['transacoes/fechoDiario', 'fa-calendar-check', 'Fecho Diário', 'fecho_diario'],
    ],
    'relatorios' => [
        ['relatorios/index', 'fa-chart-column', 'Relatórios', 'relatorios'],
        ['relatorios/diario-planilha', 'fa-table', 'Planilha', 'planilha'],
        ['categorias/index', 'fa-tags', 'Categorias', 'categorias'],
    ],
    'gestao' => [
        ['empresas/index', 'fa-building', 'Empresas', 'empresas'],
        ['filiais/index', 'fa-code-branch', 'Filiais', 'filiais'],
        ['usuarios/index', 'fa-users', 'Utilizadores', 'usuarios'],
    ],
    'administracao' => [
        ['backups/index', 'fa-database', 'Backups', 'backups'],
        ['logs/index', 'fa-clipboard-list', 'Logs Auditoria', 'logs'],
        ['configuracoes/index', 'fa-gear', 'Configurações', 'configuracoes'],
    ],
    'conta' => [
        ['perfil/index', 'fa-user-cog', 'Meu Perfil', 'perfil'],
        ['notificacoes/index', 'fa-bell', 'Notificações', 'notificacoes'],
    ],
];
function exibirGrupoMenu(array $itens, string $paginaAtiva, string $titulo, callable $temAcessoMenu): string {
    $visiveis = array_filter($itens, static fn($item) => $temAcessoMenu($item[3]));
    if (!$visiveis) return '';
    $html = '<div class="menu-grupo-label">' . $titulo . '</div>';
    foreach ($visiveis as [$rota, $icone, $texto, $modulo]) {
        $html .= '<a class="nav-item' . ($paginaAtiva === $modulo ? ' active' : '') . '" href="' . URL_BASE . '/' . $rota . '">';
        $html .= '<i class="fa-solid ' . $icone . '"></i>' . $texto . '</a>';
    }
    return $html;
}
?>

<!-- =============================================
     SIDEBAR - HTML
     ============================================= -->
<aside class="sidebar" id="sidebar">
    <!-- MARCA -->
    <div class="side-brand">
        <img src="<?php echo URL_BASE; ?>/images/logo.png" alt="MonanaFinancial" class="side-logo">
        <div class="side-brand-text">
            <div class="name">Monana<span>Financial</span></div>
            <div class="sub">GESTÃO FINANCEIRA</div>
        </div>
    </div>

    <!-- NAVEGAÇÃO -->
    <nav class="side-nav">
        <?php echo exibirGrupoMenu($itens['principal'], $paginaAtiva, 'PRINCIPAL', $temAcessoMenu); ?>
        <?php echo exibirGrupoMenu($itens['relatorios'], $paginaAtiva, 'RELATÓRIOS', $temAcessoMenu); ?>
        <?php echo exibirGrupoMenu($itens['gestao'], $paginaAtiva, 'GESTÃO', $temAcessoMenu); ?>
        <?php echo exibirGrupoMenu($itens['administracao'], $paginaAtiva, 'ADMINISTRAÇÃO', $temAcessoMenu); ?>
        <div class="menu-divider"></div>
        <?php echo exibirGrupoMenu($itens['conta'], $paginaAtiva, 'CONTA', $temAcessoMenu); ?>
        <a class="nav-item logout-item" href="<?php echo URL_BASE; ?>/auth/logout"><i class="fa-solid fa-right-from-bracket"></i>Sair</a>
    </nav>

    <!-- RODAPÉ -->
    <div class="side-foot">© <?php echo date('Y'); ?> MonanaFinancial</div>
</aside>

<!-- OVERLAY MOBILE -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="fecharSidebar()"></div>

<style>
/* ============================================
   SIDEBAR - ESTILOS COMPLETOS
   ============================================ */

/* ---- Sidebar principal ---- */
.sidebar {
    width: 250px;
    flex-shrink: 0;
    background: linear-gradient(180deg, var(--navy-deep) 0%, var(--navy) 100%);
    color: #ffffff;
    display: flex;
    flex-direction: column;
    padding: 20px 14px;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 100;
    transition: transform 0.3s ease;
}

/* ---- Marca ---- */
.side-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 4px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 16px;
}

.side-logo {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #ffffff;
    object-fit: contain;
    padding: 3px;
    flex-shrink: 0;
}

.side-brand-text .name {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 15px;
    color: #ffffff;
}

.side-brand-text .name span {
    color: var(--green);
}

.side-brand-text .sub {
    font-size: 9.5px;
    letter-spacing: 0.4px;
    color: rgba(255, 255, 255, 0.4);
    margin-top: 1px;
}

/* ---- Navegação ---- */
.side-nav {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

/* ---- Grupos do Menu (NOVO - VISÍVEL) ---- */
.menu-grupo-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: rgba(255, 255, 255, 0.3);
    padding: 12px 8px 4px;
    font-weight: 700;
    margin-top: 4px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding-bottom: 6px;
}

.menu-divider {
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    margin: 8px 4px 12px;
}

/* ---- Itens do Menu ---- */
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.15s ease;
    position: relative;
}

.nav-item i {
    width: 20px;
    text-align: center;
    font-size: 14px;
    flex-shrink: 0;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #ffffff;
}

.nav-item.active {
    background: rgba(34, 197, 94, 0.14);
    color: #ffffff;
    box-shadow: inset 3px 0 0 var(--green);
}

/* ---- Sair ---- */
.nav-item.logout-item {
    margin-top: 4px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    padding-top: 12px;
    color: rgba(255, 255, 255, 0.4);
}

.nav-item.logout-item:hover {
    color: var(--red);
    background: rgba(239, 68, 68, 0.1);
}

/* ---- Rodapé ---- */
.side-foot {
    padding-top: 14px;
    margin-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 10px;
    color: rgba(255, 255, 255, 0.25);
    text-align: center;
}

/* ---- Overlay Mobile ---- */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 99;
}

.sidebar-overlay.active {
    display: block;
}

/* ---- Mobile ---- */
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
    .sidebar.mobile-open {
        transform: translateX(0);
    }
}
</style>

<script>
// Abrir sidebar mobile
function abrirSidebar() {
    document.getElementById('sidebar').classList.add('mobile-open');
    document.getElementById('sidebar-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Fechar sidebar mobile
function fecharSidebar() {
    document.getElementById('sidebar').classList.remove('mobile-open');
    document.getElementById('sidebar-overlay').classList.remove('active');
    document.body.style.overflow = '';
}

// Fechar ao clicar no overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', fecharSidebar);
    }
});
</script>