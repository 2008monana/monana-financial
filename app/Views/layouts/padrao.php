<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NOME; ?> — <?php echo $titulo ?? 'Dashboard'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/estilo.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/responsivo.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <div class="conteudo-principal">
        <?php include __DIR__ . '/../partials/topbar.php'; ?>
        
        <main class="conteudo">
            <?php echo $conteudo; ?>
        </main>
    </div>
    
    <script src="<?php echo APP_URL; ?>/js/principal.js"></script>

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

<script>
/* ===== MODAL DE CONFIRMAÇÃO DE LOGOUT ===== */
function confirmarLogout(e) {
    if (e) e.preventDefault();
    const modal = document.getElementById('modalLogout');
    if (modal) modal.style.display = 'flex';
}

function fecharModalLogout() {
    const modal = document.getElementById('modalLogout');
    if (modal) modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalLogout');
    if (modal) {
        modal.addEventListener('click', function(ev) {
            if (ev.target === modal) fecharModalLogout();
        });
        document.addEventListener('keydown', function(ev) {
            if (ev.key === 'Escape') fecharModalLogout();
        });
    }
    const btnSim = document.getElementById('btnLogoutSim');
    if (btnSim) {
        btnSim.addEventListener('click', function() {
            window.location.href = '<?php echo defined("URL_BASE") ? URL_BASE : APP_URL; ?>/auth/logout';
        });
    }
    const btnNao = document.getElementById('btnLogoutNao');
    if (btnNao) {
        btnNao.addEventListener('click', fecharModalLogout);
    }
});
</script>
</body>
</html>