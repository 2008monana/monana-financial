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

        <!-- NOTIFICAÇÕES COM BADGE E PAINEL RÁPIDO -->
        <div class="notificacoes-topbar">
            <button class="bell-wrap" type="button" title="Notificações" id="bell-notificacoes" aria-expanded="false" aria-controls="dropdown-notificacoes">
                <i class="fa-regular fa-bell"></i>
                <span class="bell-dot" id="bell-dot" style="<?php echo $naoLidas > 0 ? '' : 'display:none;'; ?>"><?php echo $naoLidas > 99 ? '99+' : $naoLidas; ?></span>
            </button>
            <div class="dropdown-notificacoes" id="dropdown-notificacoes" hidden>
                <div class="dropdown-notificacoes-cabecalho">
                    <strong><i class="fa-regular fa-bell"></i> Notificações</strong>
                    <form method="post" action="<?php echo URL_BASE; ?>/notificacoes/marcarTodasComoLidas">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SegurancaHelper::gerarTokenCSRF()); ?>">
                        <button type="submit" class="marcar-todas">Marcar todas <i class="fa-solid fa-check"></i></button>
                    </form>
                </div>
                <div id="dropdown-notificacoes-lista" class="dropdown-notificacoes-lista"><div class="dropdown-carregar">A carregar notificações…</div></div>
                <a class="dropdown-ver-todas" href="<?php echo URL_BASE; ?>/notificacoes/index">Ver todas as notificações <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>

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
<style>
.notificacoes-topbar { position: relative; }
.dropdown-notificacoes { position:absolute; right:0; top:calc(100% + 10px); width:380px; max-width:calc(100vw - 32px); background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:0 16px 36px rgba(14,39,72,.18); overflow:hidden; z-index:120; }
.dropdown-notificacoes-cabecalho { padding:14px 16px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); color:var(--ink); }
.marcar-todas { border:0; background:transparent; color:var(--green-dark); font-weight:600; cursor:pointer; font-size:12px; }
.dropdown-notificacao { display:flex; gap:10px; padding:12px 16px; text-decoration:none; color:inherit; border-bottom:1px solid var(--border); }
.dropdown-notificacao:hover { background:var(--bg); }.dropdown-notificacao.nao-lida { background:rgba(34,197,94,.05); }
.dropdown-notificacao-icone { width:22px; padding-top:2px; }.tipo-sucesso { color:var(--green); }.tipo-alerta,.tipo-aviso { color:#f59e0b; }.tipo-erro { color:var(--red); }
.dropdown-notificacao-corpo { flex:1; min-width:0; }.dropdown-notificacao-titulo { display:block; font-size:13px; font-weight:700; }.dropdown-notificacao-mensagem { display:block; color:var(--muted); font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:3px; }.dropdown-notificacao-tempo { display:block; color:var(--muted); font-size:11px; margin-top:4px; }
.dropdown-ver-todas { display:block; padding:13px 16px; text-decoration:none; color:var(--green-dark); font-size:13px; font-weight:700; }.dropdown-ver-todas:hover { background:var(--bg); }.dropdown-carregar,.dropdown-vazia { padding:20px 16px; color:var(--muted); font-size:13px; text-align:center; }
</style>
<script>
(function () {
 const botao=document.getElementById('bell-notificacoes'), painel=document.getElementById('dropdown-notificacoes'), lista=document.getElementById('dropdown-notificacoes-lista'), badge=document.getElementById('bell-dot');
 const esc=v=>String(v||'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
 const tempo=data=>{const s=Math.max(0,Math.floor((Date.now()-new Date(data.replace(' ','T')).getTime())/1000));if(s<60)return 'agora mesmo';if(s<3600)return 'há '+Math.floor(s/60)+' min';if(s<86400)return 'há '+Math.floor(s/3600)+' hora(s)';return 'há '+Math.floor(s/86400)+' dia(s)';};
 function atualizarBadge(n){badge.textContent=n>99?'99+':n;badge.style.display=n>0?'flex':'none';}
 function carregar(){fetch('<?php echo URL_BASE; ?>/notificacoes/recentes',{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(d=>{atualizarBadge(d.nao_lidas||0);if(!d.notificacoes||!d.notificacoes.length){lista.innerHTML='<div class="dropdown-vazia">Não tem notificações.</div>';return;}lista.innerHTML=d.notificacoes.map(n=>'<a class="dropdown-notificacao '+(n.lida?'':'nao-lida')+'" href="'+esc(n.link||'<?php echo URL_BASE; ?>/notificacoes/index')+'"><span class="dropdown-notificacao-icone tipo-'+esc(n.tipo)+'"><i class="fa-solid '+({sucesso:'fa-circle-check',alerta:'fa-bell',aviso:'fa-triangle-exclamation',erro:'fa-circle-xmark'}[n.tipo]||'fa-bell')+'"></i></span><span class="dropdown-notificacao-corpo"><span class="dropdown-notificacao-titulo">'+esc(n.titulo)+'</span><span class="dropdown-notificacao-mensagem">'+esc(n.mensagem)+'</span><span class="dropdown-notificacao-tempo">'+tempo(n.criado_em)+'</span></span></a>').join('');}).catch(()=>{lista.innerHTML='<div class="dropdown-vazia">Não foi possível carregar as notificações.</div>';});}
 botao.addEventListener('click',()=>{const aberto=!painel.hidden;painel.hidden=aberto;botao.setAttribute('aria-expanded',String(!aberto));if(!aberto)carregar();});document.addEventListener('click',e=>{if(!e.target.closest('.notificacoes-topbar'))painel.hidden=true;});setInterval(()=>fetch('<?php echo URL_BASE; ?>/notificacoes/contagem').then(r=>r.json()).then(d=>atualizarBadge(d.nao_lidas||0)).catch(()=>{}),60000);
}());
</script>
