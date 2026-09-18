<?php
/**
 * Sidebar do sistema - MonanaFinancial
 * Estrutura organizada por grupos de funcionalidades
 * 
 * NOTA: Este ficheiro substitui completamente o anterior.
 * As mudanças são visíveis: grupos, separadores, novos itens.
 */

$perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
$isSuperAdmin = $perfil === 'super_admin';
$isAdminEmpresa = $perfil === 'admin_empresa';
$isViewer = $perfil === 'visualizador';
$paginaAtiva = $paginaAtiva ?? 'dashboard';

// =============================================
// DEFINIÇÃO DOS ÍTENS DO MENU POR GRUPO
// =============================================

// Grupo 1: PRINCIPAL (todos os utilizadores)
$grupoPrincipal = [
    ['rota' => 'dashboard/index', 'icone' => 'fa-house', 'texto' => 'Dashboard', 'modulo' => 'dashboard'],
    ['rota' => 'transacoes/index', 'icone' => 'fa-list-ul', 'texto' => 'Movimentos', 'modulo' => 'movimentos'],
];

// Grupo 2: RELATÓRIOS (todos os utilizadores)
$grupoRelatorios = [
    ['rota' => 'relatorios/index', 'icone' => 'fa-chart-column', 'texto' => 'Relatórios', 'modulo' => 'relatorios'],
    ['rota' => 'categorias/index', 'icone' => 'fa-tags', 'texto' => 'Categorias', 'modulo' => 'categorias'],
];

// Grupo 3: GESTÃO (Admin Empresa + Super Admin)
$grupoGestao = [];
if ($isSuperAdmin) {
    $grupoGestao[] = ['rota' => 'empresas/index', 'icone' => 'fa-building', 'texto' => 'Empresas', 'modulo' => 'empresas'];
}
if ($isAdminEmpresa || $isSuperAdmin) {
    $grupoGestao[] = ['rota' => 'filiais/index', 'icone' => 'fa-code-branch', 'texto' => 'Filiais', 'modulo' => 'filiais'];
    $grupoGestao[] = ['rota' => 'usuarios/index', 'icone' => 'fa-users', 'texto' => 'Utilizadores', 'modulo' => 'usuarios'];
}

// Grupo 4: FERRAMENTAS (Admin Empresa + Super Admin)
$grupoFerramentas = [];
if ($isAdminEmpresa || $isSuperAdmin) {
    $grupoFerramentas[] = ['rota' => 'importacao/index', 'icone' => 'fa-file-import', 'texto' => 'Importar Excel', 'modulo' => 'importacao'];
}

// Grupo 5: ADMINISTRAÇÃO (apenas Super Admin)
$grupoAdmin = [];
if ($isSuperAdmin) {
    $grupoAdmin[] = ['rota' => 'logs/index', 'icone' => 'fa-clipboard-list', 'texto' => 'Logs Auditoria', 'modulo' => 'logs'];
    $grupoAdmin[] = ['rota' => 'configuracoes/index', 'icone' => 'fa-gear', 'texto' => 'Configurações Sistema', 'modulo' => 'configuracoes'];
}

// Grupo 6: CONTA (todos os utilizadores)
$grupoConta = [
    ['rota' => 'perfil/index', 'icone' => 'fa-user-cog', 'texto' => 'Meu Perfil', 'modulo' => 'perfil'],
    ['rota' => 'notificacoes/index', 'icone' => 'fa-bell', 'texto' => 'Notificações', 'modulo' => 'notificacoes'],
];

// =============================================
// FUNÇÃO PARA EXIBIR GRUPO DE ITENS
// =============================================
function exibirGrupoMenu($itens, $paginaAtiva, $titulo = null) {
    if (empty($itens)) return '';
    
    $html = '';
    if ($titulo) {
        $html .= '<div class="menu-grupo-label">' . $titulo . '</div>';
    }
    foreach ($itens as $item) {
        $ativo = ($paginaAtiva === $item['modulo']) ? ' active' : '';
        $html .= '<a class="nav-item' . $ativo . '" href="' . URL_BASE . '/' . $item['rota'] . '">';
        $html .= '<i class="fa-solid ' . $item['icone'] . '"></i>';
        $html .= $item['texto'];
        $html .= '</a>';
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
        <!-- GRUPO 1: PRINCIPAL -->
        <?php echo exibirGrupoMenu($grupoPrincipal, $paginaAtiva, 'PRINCIPAL'); ?>

        <!-- GRUPO 2: RELATÓRIOS -->
        <?php echo exibirGrupoMenu($grupoRelatorios, $paginaAtiva, 'RELATÓRIOS'); ?>

        <!-- GRUPO 3: GESTÃO -->
        <?php if (!empty($grupoGestao)): ?>
            <?php echo exibirGrupoMenu($grupoGestao, $paginaAtiva, 'GESTÃO'); ?>
        <?php endif; ?>

        <!-- GRUPO 4: FERRAMENTAS -->
        <?php if (!empty($grupoFerramentas)): ?>
            <?php echo exibirGrupoMenu($grupoFerramentas, $paginaAtiva, 'FERRAMENTAS'); ?>
        <?php endif; ?>

        <!-- GRUPO 5: ADMINISTRAÇÃO (apenas Super Admin) -->
        <?php if (!empty($grupoAdmin)): ?>
            <?php echo exibirGrupoMenu($grupoAdmin, $paginaAtiva, 'ADMINISTRAÇÃO'); ?>
        <?php endif; ?>

        <!-- SEPARADOR -->
        <div class="menu-divider"></div>

        <!-- GRUPO 6: CONTA -->
        <?php echo exibirGrupoMenu($grupoConta, $paginaAtiva, 'CONTA'); ?>

        <!-- SAIR -->
        <a class="nav-item logout-item" href="<?php echo URL_BASE; ?>/auth/logout">
            <i class="fa-solid fa-right-from-bracket"></i>
            Sair
        </a>
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