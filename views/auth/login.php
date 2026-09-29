<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MonanaFinancial — Iniciar Sessão</title>
<link rel="icon" href="<?= URL_BASE ?>/images/favicon.png?v=<?= @filemtime(CAMINHO_RAIZ . '/public/images/favicon.png') ?: time ?>">
<link rel="shortcut icon" type="image/x-icon" href="<?= URL_BASE ?>/favicon.ico">
<link rel="stylesheet" href="<?= URL_BASE ?>/css/login.css?v=<?= @filemtime(CAMINHO_RAIZ . '/public/css/login.css') ?: time ?>">
</head>
<body>

<div class="panel">

  <!-- Lado esquerdo: marca -->
  <div class="brand-side">
    <img class="brand-photo"
         src="<?= URL_BASE ?>/images/login-bg.jpg"
         onerror="this.onerror=null;this.src='<?= URL_BASE ?>/images/background-monana.png';"
         alt="">

    <div class="brand-mark">
      <img src="<?= URL_BASE ?>/images/logo.png" alt="MonanaFinancial" class="brand-logo">
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

    <form id="form-login" method="post" action="<?= URL_BASE ?>/auth/autenticar" autocomplete="off">
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
        <a href="<?= URL_BASE ?>/auth/esqueciSenha">Esqueceu a palavra-passe?</a>
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
const URL_BASE = "<?= URL_BASE ?>";
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
(function () {
  const cssOk = getComputedStyle(document.body).display !== 'inline' &&
                getComputedStyle(document.body).fontFamily.indexOf('Inter') !== -1;
  if (cssOk) return;
  const s = document.createElement('style');
  s.textContent = 'body{font-family:system-ui,sans-serif;background:#f6f8fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.panel{display:grid;grid-template-columns:1fr 1fr;max-width:1180px;width:100%;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(10,25,48,.15)}form input{display:block;width:100%;padding:10px 12px;margin:6px 0;border:1px solid #e2e8f0;border-radius:8px}button.submit{padding:12px 20px;background:#16a34a;color:#fff;border:0;border-radius:8px;cursor:pointer}';
  document.head.appendChild(s);
})();
</script>
<script src="<?= URL_BASE ?>/js/login.js"></script>
</body>
</html>
