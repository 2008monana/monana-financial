<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MonanaFinancial — Iniciar Sessão</title>
<link rel="icon" href="<?= URL_BASE ?>/images/favicon.png">
<link rel="stylesheet" href="<?= URL_BASE ?>/css/login.css">
</head>
<body>

<div class="panel">

  <!-- Lado esquerdo: marca -->
  <div class="brand-side">
    <img class="brand-photo" src="<?= URL_BASE ?>/images/login-bg.jpg" alt="">

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

    <form id="form-login" autocomplete="on">
      <div class="field">
        <label for="email">Utilizador</label>
        <div class="input-wrap">
          <i class="fa-regular fa-circle-user leading"></i>
          <input id="email" name="email" type="email" placeholder="Digite o seu e-mail" autocomplete="username" required>
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
</script>
<script src="<?= URL_BASE ?>/js/login.js"></script>
</body>
</html>
