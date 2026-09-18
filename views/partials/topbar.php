<?php
/**
 * Topbar do sistema - MonanaFinancial
 * Inclui badge de notificações com atualização automática
 */

$usuarioNome = $_SESSION['usuario_nome'] ?? 'Utilizador';
$usuarioPerfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
$empresaNome = $_SESSION['empresa_nome'] ?? '';

// Iniciais para o avatar
$iniciais = '';
if ($usuarioNome) {
    $nomes = explode(' ', $usuarioNome);
    $iniciais = strtoupper(substr($nomes[0] ?? '', 0, 1));
    if (isset($nomes[1])) {
        $iniciais .= strtoupper(substr($nomes[1], 0, 1));
    }
    $iniciais = $iniciais ?: 'U';
} else {
    $iniciais = 'U';
}

// Buscar contagem de notificações não lidas
$naoLidas = 0;
if (isset($_SESSION['usuario_id'])) {
    try {
        require_once CAMINHO_RAIZ . '/models/Notificacao.php';
        $notificacaoModel = new Notificacao();
        $naoLidas = $notificacaoModel->contarNaoLidas((int) $_SESSION['usuario_id']);
    } catch (Exception $e) {
        $naoLidas = 0;
    }
}
?>

<div class="topbar">
    <div class="topbar-left">
        <div class="burger" onclick="abrirSidebar()">
            <i class="fas fa-bars"></i>
        </div>
        <div class="greeting">
            Olá, <strong><?php echo htmlspecialchars($usuarioNome); ?></strong>! <span class="wave">👋</span>
            <?php if (!empty($empresaNome)): ?>
                <span class="empresa-badge" style="font-size:12px; font-weight:400; color:var(--muted); margin-left:8px;">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($empresaNome); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="topbar-right">
        <!-- Período atual -->
        <div class="select-pill">
            <i class="fas fa-calendar-alt"></i>
            <?php echo date('d/m/Y'); ?>
        </div>

        <!-- NOTIFICAÇÕES COM BADGE -->
        <a class="bell-wrap" href="<?php echo URL_BASE; ?>/notificacoes/index" title="Notificações" id="bell-notificacoes">
            <i class="fa-regular fa-bell"></i>
            <div class="bell-dot" id="bell-dot" style="<?php echo $naoLidas > 0 ? '' : 'display:none;'; ?>">
                <?php echo $naoLidas > 0 ? $naoLidas : '0'; ?>
            </div>
        </a>

        <!-- PERFIL -->
        <a class="profile" href="<?php echo URL_BASE; ?>/perfil/index">
            <div class="avatar"><?php echo $iniciais; ?></div>
            <div class="profile-name"><?php echo htmlspecialchars($usuarioNome); ?></div>
            <i class="fas fa-chevron-down" style="font-size:10px; color:var(--muted);"></i>
        </a>

        <!-- SAIR -->
        <a href="<?php echo URL_BASE; ?>/auth/logout" class="logout-btn" title="Sair">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>

<style>
/* ============================================
   TOPBAR - ESTILOS COMPLETOS
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
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.burger {
    font-size: 18px;
    color: var(--muted);
    cursor: pointer;
    display: none;
    padding: 4px;
    border-radius: 6px;
    transition: var(--transition);
}

.burger:hover {
    background: var(--bg);
    color: var(--ink);
}

.greeting {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
}

.greeting .wave {
    display: inline-block;
    animation: wave 2s infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(15deg); }
    50% { transform: rotate(-10deg); }
    75% { transform: rotate(15deg); }
}

.empresa-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    background: rgba(14, 39, 72, 0.06);
    border-radius: 12px;
    font-weight: 500;
}

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
    cursor: default;
    white-space: nowrap;
}

.select-pill i {
    color: var(--muted);
}

/* ============================================
   NOTIFICAÇÕES - BADGE
   ============================================ */

.bell-wrap {
    position: relative;
    cursor: pointer;
    color: var(--muted);
    font-size: 20px;
    text-decoration: none;
    display: flex;
    align-items: center;
    padding: 4px;
    border-radius: 6px;
    transition: var(--transition);
}

.bell-wrap:hover {
    color: var(--ink);
    background: var(--bg);
}

.bell-dot {
    position: absolute;
    top: -4px;
    right: -6px;
    background: var(--red);
    color: #ffffff;
    font-size: 9px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
    animation: pulse-dot 2s infinite;
    line-height: 1;
}

@keyframes pulse-dot {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* ============================================
   PERFIL
   ============================================ */

.profile {
    display: flex;
    align-items: center;
    gap: 9px;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    padding: 4px 8px 4px 4px;
    border-radius: 8px;
    transition: var(--transition);
}

.profile:hover {
    background: var(--bg);
}

.avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--navy-light), var(--navy));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.profile-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    white-space: nowrap;
}

.logout-btn {
    color: var(--muted);
    text-decoration: none;
    font-size: 16px;
    transition: var(--transition);
    padding: 6px;
    border-radius: 6px;
}

.logout-btn:hover {
    color: var(--red);
    background: rgba(239, 68, 68, 0.08);
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .topbar {
        padding: 10px 16px;
    }
    .burger {
        display: block;
    }
    .profile-name {
        display: none;
    }
    .select-pill {
        font-size: 10px;
        padding: 4px 8px;
    }
    .greeting {
        font-size: 13px;
    }
    .bell-wrap {
        font-size: 17px;
    }
    .bell-dot {
        font-size: 8px;
        min-width: 16px;
        height: 16px;
        top: -3px;
        right: -5px;
    }
    .logout-btn {
        font-size: 14px;
    }
    .empresa-badge {
        display: none;
    }
}

@media (max-width: 480px) {
    .topbar {
        padding: 8px 12px;
    }
    .topbar-right {
        gap: 6px;
    }
    .greeting {
        font-size: 12px;
    }
    .profile {
        padding: 2px 4px;
    }
    .avatar {
        width: 28px;
        height: 28px;
        font-size: 10px;
    }
    .bell-dot {
        font-size: 7px;
        min-width: 14px;
        height: 14px;
    }
}
</style>