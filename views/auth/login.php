<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MonanaFinancial — Iniciar Sessão</title>
<?php
// Constantes de segurança: se algo não estiver definido, usa caminhos relativos.
$__urlBase = defined('URL_BASE') ? URL_BASE : '';
$__raiz    = defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2);
?>
<?php $__fbv = @filemtime($__raiz . '/public/images/logo.png') ?: time(); ?>
<!-- Favicon: gerado a partir de public/images/logo.png -->
<link rel="icon" type="image/png" sizes="32x32" href="<?= $__urlBase ?>/images/favicon-32.png?v=<?= $__fbv ?>">
<link rel="icon" type="image/png" sizes="48x48" href="<?= $__urlBase ?>/images/favicon-48.png?v=<?= $__fbv ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= $__urlBase ?>/images/favicon-192.png?v=<?= $__fbv ?>">
<link rel="apple-touch-icon" href="<?= $__urlBase ?>/images/apple-touch-icon.png?v=<?= $__fbv ?>">
<link rel="shortcut icon" type="image/x-icon" href="<?= $__urlBase ?>/favicon.ico?v=<?= $__fbv ?>">
<link rel="icon" type="image/x-icon" href="<?= $__urlBase ?>/favicon.ico?v=<?= $__fbv ?>">
<link rel="stylesheet" href="<?= $__urlBase ?>/css/login.css?v=<?= @filemtime($__raiz . '/public/css/login.css') ?: time ?>">
<!-- Fallback: se a folha de estilos externa falhar (MIME/404), o layout basico e aplicado aqui. -->
<style>
  html,body{margin:0;padding:0;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;background:#eef2f7;color:#0a1930}
  .panel{display:grid;grid-template-columns:1.1fr 1fr;max-width:1180px;width:min(96%,1180px);margin:4vh auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(10,25,48,.15)}
  .brand-side{position:relative;display:flex;flex-direction:column;justify-content:center;gap:22px;padding:44px;color:#fff;background:linear-gradient(150deg,#0b3d2e 0%,#14532d 55%,#166534 100%)}
  .brand-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.28;pointer-events:none}
  .brand-mark{display:flex;align-items:center;gap:12px;position:relative}
  .brand-logo{width:52px;height:52px;border-radius:12px;background:#fff;padding:4px;object-fit:contain}
  .brand-name{font-size:1.5rem;font-weight:700}.brand-name span{color:#7ee2a8}
  .brand-tagline-small{font-size:.72rem;letter-spacing:.14em;opacity:.85}
  .brand-hero h1{font-size:1.9rem;line-height:1.25;margin:0 0 10px;position:relative}
  .brand-hero p{margin:0 0 18px;opacity:.9;position:relative}
  .pillars{display:flex;gap:18px;flex-wrap:wrap;position:relative}
  .pillar{display:flex;flex-direction:column;align-items:center;gap:6px;font-size:.8rem}
  .pillar .circle{width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.1rem}
  .footer-note{font-size:.75rem;opacity:.7;position:relative}
  .form-side{padding:52px 48px;display:flex;flex-direction:column;justify-content:center}
  .welcome-eyebrow{text-transform:uppercase;letter-spacing:.12em;font-size:.72rem;color:#16a34a;font-weight:700}
  .welcome-title{font-size:1.6rem;font-weight:700;margin:6px 0 4px}.welcome-title span{color:#0b3d2e}
  .welcome-sub{color:#5b6b7f;margin-bottom:24px;font-size:.92rem}
  .aviso{padding:10px 14px;border-radius:10px;margin-bottom:16px;font-size:.9rem}
  .aviso-erro{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
  .aviso-sucesso{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}
  .field{margin-bottom:18px}
  .field label{display:block;font-size:.85rem;font-weight:600;margin-bottom:6px;color:#334155}
  .input-wrap{position:relative;display:flex;align-items:center}
  .input-wrap i.leading{position:absolute;left:12px;color:#94a3b8}
  .input-wrap input{width:100%;padding:12px 42px 12px 40px;border:1.5px solid #dbe3ee;border-radius:10px;font-size:.95rem;box-sizing:border-box;background:#fff}
  .input-wrap input:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.15)}
  .toggle-pass{position:absolute;right:8px;background:none;border:0;color:#94a3b8;cursor:pointer;padding:6px}
  .row-between{display:flex;justify-content:space-between;align-items:center;margin:4px 0 22px;font-size:.85rem}
  .row-between a{color:#16a34a;text-decoration:none;font-weight:600}
  .remember{display:flex;gap:6px;align-items:center;color:#475569}
  button.submit{width:100%;padding:13px;border:0;border-radius:10px;background:linear-gradient(135deg,#16a34a,#0b3d2e);color:#fff;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px}
  button.submit:hover{filter:brightness(1.08)}
  .copyright{margin-top:26px;text-align:center;font-size:.75rem;color:#94a3b8}
  .modal-overlay{position:fixed;inset:0;background:rgba(10,25,48,.55);display:flex;align-items:center;justify-content:center;z-index:999}
  .modal-overlay[hidden]{display:none}
  .modal-caixa{background:#fff;border-radius:16px;padding:34px 40px;text-align:center;max-width:340px;box-shadow:0 24px 70px rgba(0,0,0,.3)}
  .modal-icone{font-size:2rem;margin-bottom:12px;color:#16a34a}
  .modal-titulo{font-weight:700;margin-bottom:6px}
  .modal-mensagem{color:#5b6b7f;font-size:.9rem}
  @media(max-width:860px){.panel{grid-template-columns:1fr}.brand-side{padding:28px}}
</style>
<?php
// =============================================
// GARANTIA DE ESTILO (à prova de falhas do Apache/MIME):
// o CSS do login e injectado directamente na pagina.
// Assim, mesmo que o ficheiro externo nao seja servido com o
// Content-Type correcto (o que fazia o navegador recusar a folha
// de estilos e "quebrar" o layout), a pagina fica SEMPRE estilizada.
// =============================================
$caminhoCssLogin = $__raiz . '/public/css/login.css';
if (is_readable($caminhoCssLogin)) {
    echo "<style>\n" . file_get_contents($caminhoCssLogin) . "\n</style>";
}
?>
</head>
<body>

<div class="panel">

  <!-- Lado esquerdo: marca -->
  <div class="brand-side">
    <img class="brand-photo"
         src="<?= $__urlBase ?>/images/login-bg.jpg"
         onerror="this.onerror=null;this.src='<?= $__urlBase ?>/images/background-monana.png';"
         alt="">

    <div class="brand-mark">
      <img src="<?= $__urlBase ?>/images/logo.png" alt="MonanaFinancial" class="brand-logo">
      <div>
        <div class="brand-name">Monana<span>Financial</span></div>
        <div class="brand-tagline-small">GESTÃO FINANCEIRA INTELIGENTE</div>
      </div>
    </div>

    <div class="brand-hero">
      <h1>Cada kwanza sob controlo, em tempo real.</h1>
      <p>Acompanhe vendas, compras e despesas de todas as suas filiais num único painel, feito para o ritmo do seu negócio.</p>

      <div class="pillars">
        <div class="pillar">
          <div class="circle"><i class="fa-solid fa-check"></i></div>
          <span>Controlo</span>
        </div>
        <div class="pillar">
          <div class="circle"><i class="fa-solid fa-clock"></i></div>
          <span>Transparência</span>
        </div>
        <div class="pillar">
          <div class="circle"><i class="fa-solid fa-shield-halved"></i></div>
          <span>Segurança</span>
        </div>
        <div class="pillar">
          <div class="circle"><i class="fa-solid fa-arrow-trend-up"></i></div>
          <span>Crescimento</span>
        </div>
      </div>
    </div>

    <div class="footer-note">&copy; <?= date('Y') ?> MonanaFinancial. Todos os direitos reservados.</div>
  </div>

  <!-- Lado direito: formulário -->
  <div class="form-side">
    <div class="welcome-eyebrow">Bem-vindo de volta</div>
    <div class="welcome-title">Entrar na <span>MonanaFinancial</span></div>
    <div class="welcome-sub">Introduza as suas credenciais para aceder ao painel.</div>

    <?php if (!empty($erro)): ?>
      <div class="aviso aviso-erro" role="alert"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <?php if (!empty($sucesso)): ?>
      <div class="aviso aviso-sucesso" role="status"><?= htmlspecialchars($sucesso) ?></div>
    <?php endif; ?>

    <form id="form-login" method="post" action="<?= $__urlBase ?>/auth/autenticar" autocomplete="off">
      <div class="field">
        <label for="email">Utilizador</label>
        <div class="input-wrap">
          <i class="fa-regular fa-circle-user leading"></i>
          <input id="email" name="email" type="text" inputmode="email" placeholder="Digite o seu e-mail" autocomplete="username" autocapitalize="none" spellcheck="false" required>
        </div>
      </div>

      <div class="field">
        <label for="senha">Palavra-passe</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock leading"></i>
          <input id="senha" name="senha" type="password" placeholder="Digite a sua palavra-passe" autocomplete="current-password" required>
          <button type="button" class="toggle-pass" id="btn-toggle-pass" aria-label="Mostrar palavra-passe">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>
      </div>

      <div class="row-between">
        <label class="remember"><input type="checkbox" name="lembrar"> Lembrar-me</label>
        <a href="<?= $__urlBase ?>/auth/esqueciSenha">Esqueceu a palavra-passe?</a>
      </div>

      <button class="submit" type="submit" id="btn-submit">
        <i class="fa-solid fa-right-to-bracket"></i>
        <span id="btn-submit-texto">Iniciar Sessão</span>
      </button>
    </form>

    <div class="copyright">&copy; <?= date('Y') ?> MonanaFinancial. Todos os direitos reservados.</div>
  </div>

</div>

<!-- Modal de estado do login (processando / sucesso / erro) -->
<div id="modal-login" class="modal-overlay" hidden>
  <div class="modal-caixa">
    <div id="modal-icone" class="modal-icone processando">
      <i class="fa-solid fa-spinner fa-spin"></i>
    </div>
    <div id="modal-titulo" class="modal-titulo">A processar...</div>
    <div id="modal-mensagem" class="modal-mensagem">Estamos a validar as suas credenciais.</div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js" crossorigin="anonymous"></script>
<script>
const URL_BASE = "<?= $__urlBase ?>";
// Rede de seguranca: se o JS externo falhar, os icones Font Awesome
// sao substituidos por SVG inline — a pagina nunca fica "partida".
window.addEventListener('load', function () {
  setTimeout(function () {
    if (document.querySelector('svg.svg-inline--fa')) return; // FA carregou
    const MAPA = {
      'fa-check': 'M20 12l-6 6-8-8', 'fa-clock': 'M12 6v6l4 2 M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z',
      'fa-shield-halved': 'M12 2l8 3v6c0 5-3.5 8.5-8 11-4.5-2.5-8-6-8-11V5z',
      'fa-arrow-trend-up': 'M3 17l6-6 4 4 8-8 M15 7h6v6', 'fa-lock': 'M7 11V7a5 5 0 0 1 10 0v4 M5 11h14v10H5z',
      'fa-circle-user': 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M4 21a8 8 0 0 1 16 0 M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z',
      'fa-eye': 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z M12 12m-3 0a3 3 0 1 0 6 0 3 3 0 1 0-6 0',
      'fa-eye-slash': 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z M4 4l16 16',
      'fa-right-to-bracket': 'M9 4H5v16h4 M14 8l4 4-4 4 M18 12H9',
      'fa-spinner': 'M12 2a10 10 0 1 0 10 10', 'fa-circle-check': 'M9 12l2 2 4-4 M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z',
      'fa-circle-xmark': 'M9 9l6 6M15 9l-6 6 M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z'
    };
    document.querySelectorAll('i.fa-solid, i.fa-regular, i.fa-brands').forEach(function (el) {
      const cls = Array.from(el.classList).find(c => MAPA[c]);
      if (!cls) return;
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('viewBox', '0 0 24 24');
      svg.setAttribute('width', '1em'); svg.setAttribute('height', '1em');
      svg.style.fill = 'none'; svg.style.stroke = 'currentColor';
      svg.style.strokeWidth = '2'; svg.style.strokeLinecap = 'round';
      MAPA[cls].split(' M').forEach((d, i) => {
        const p = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        p.setAttribute('d', (i === 0 ? '' : 'M') + d);
        svg.appendChild(p);
      });
      el.replaceWith(svg);
    });
  }, 1200);
});
</script>
<script>
// Rede de seguranca do CSS: se a folha de estilos externa nao for aplicada
// (ex.: MIME type recusado pelo navegador), injecta um minimo de estilos
// para a pagina de login nunca ficar sem formatacao.
// IMPORTANTE: so corre quando o CSS NAO esta activo — com o estilo
// embutido acima, nao deve fazer nada; e apenas um ultimo recurso.
(function () {
  const cssOk = getComputedStyle(document.body).display !== 'inline' &&
                getComputedStyle(document.querySelector('.panel') || document.body)
                  .getPropertyValue('max-width') !== 'none';
  if (cssOk) return;
  const s = document.createElement('style');
  s.textContent = 'body{font-family:system-ui,sans-serif;background:#f6f8fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.panel{display:grid;grid-template-columns:1fr 1fr;max-width:1180px;width:100%;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(10,25,48,.15)}form input{display:block;width:100%;padding:10px 12px;margin:6px 0;border:1px solid #e2e8f0;border-radius:8px}button.submit{padding:12px 20px;background:#16a34a;color:#fff;border:0;border-radius:8px;cursor:pointer}';
  document.head.appendChild(s);
})();
</script>
<script src="<?= $__urlBase ?>/js/login.js"></script>
</body>
</html>
