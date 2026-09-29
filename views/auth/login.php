<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MonanaFinancial — Iniciar Sessão</title>
<?php
// Favicon e CSS "à prova de falhas": o conteúdo é lido do disco e servido
// diretamente na página (data URI / <style>), por isso funciona mesmo se a
// pasta /images ou /css estiver inacessível no servidor (permissões, .htaccess).
$uriFavicon = '';
$caminhoFavicon = CAMINHO_RAIZ . '/public/images/favicon.png';
if (is_file($caminhoFavicon)) {
    $dadosFavicon = @file_get_contents($caminhoFavicon);
    if ($dadosFavicon !== false) {
        $uriFavicon = 'data:image/png;base64,' . base64_encode($dadosFavicon);
    }
}
// Logotipo da marca: embebido na página para nunca depender do acesso à pasta /images
$uriLogo = '';
$caminhoLogo = CAMINHO_RAIZ . '/public/images/logo.png';
if (is_file($caminhoLogo)) {
    $dadosLogo = @file_get_contents($caminhoLogo);
    if ($dadosLogo !== false) {
        $uriLogo = 'data:image/png;base64,' . base64_encode($dadosLogo);
    }
}
// Imagem de fundo do painel esquerdo: também embebida (data URI), para que o
// background apareça mesmo se a pasta /images estiver inacessível no servidor.
$uriFundoLogin = '';
foreach (['/public/images/login-bg.jpg', '/public/images/background-monana.png'] as $__fundoRel) {
    $__caminhoFundo = CAMINHO_RAIZ . $__fundoRel;
    if (is_file($__caminhoFundo)) {
        $__dadosFundo = @file_get_contents($__caminhoFundo);
        if ($__dadosFundo !== false) {
            $__mime = strtolower(pathinfo($__caminhoFundo, PATHINFO_EXTENSION)) === 'png' ? 'image/png' : 'image/jpeg';
            $uriFundoLogin = 'data:' . $__mime . ';base64,' . base64_encode($__dadosFundo);
            break;
        }
    }
}
$caminhoCssLogin = CAMINHO_RAIZ . '/public/css/login.css';
$cssLogin = is_file($caminhoCssLogin) ? (@file_get_contents($caminhoCssLogin) ?: '') : '';
// Mensagens passadas pelo controlador (modo POST clássico / fallback sem JS)
$msgErro  = $erro ?? null;
$msgSucesso = $sucesso ?? null;
$emailTentativo = $email_tentativo ?? '';
?>
<link rel="icon" type="image/png" href="<?= $uriFavicon !== '' ? $uriFavicon : URL_BASE . '/images/favicon.png' ?>">
<link rel="apple-touch-icon" href="<?= $uriFavicon !== '' ? $uriFavicon : URL_BASE . '/images/favicon.png' ?>">
<link rel="stylesheet" href="<?= URL_BASE ?>/css/login.css?v=<?= (int) (@filemtime($caminhoCssLogin) ?: time()) ?>">
<style><?= $cssLogin /* fallback: garante o estilo mesmo se o link externo falhar */ ?>
/* Fundo do painel esquerdo embebido (data URI): nunca depende do acesso a /images */
.brand-photo{background-image:url('<?= $uriFundoLogin ?>');}
</style>
</head>
<body>

<div class="panel">

  <!-- Lado esquerdo: marca -->
  <div class="brand-side">
    <img class="brand-photo" src="<?= $uriFundoLogin !== '' ? $uriFundoLogin : URL_BASE . '/images/login-bg.jpg' ?>" alt="" onerror="this.style.visibility='hidden';">

    <div class="brand-mark">
        <img src="<?= $uriLogo !== '' ? $uriLogo : URL_BASE . '/images/logo.png' ?>" alt="MonanaFinancial" class="brand-logo">
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

    <?php if ($msgErro): ?>
    <div class="aviso aviso-erro" role="alert" style="padding:12px 16px;border-radius:10px;background:rgba(239,68,68,0.12);color:#b91c1c;border:1px solid rgba(239,68,68,0.35);margin-bottom:16px;font-size:14px;">
      <?= htmlspecialchars($msgErro) ?>
    </div>
    <?php endif; ?>
    <?php if ($msgSucesso): ?>
    <div class="aviso aviso-sucesso" role="status" style="padding:12px 16px;border-radius:10px;background:rgba(34,197,94,0.12);color:#15803d;border:1px solid rgba(34,197,94,0.35);margin-bottom:16px;font-size:14px;">
      <?= htmlspecialchars($msgSucesso) ?>
    </div>
    <?php endif; ?>

    <form id="form-login" autocomplete="on">
      <div class="field">
        <label for="email">Utilizador</label>
        <div class="input-wrap">
          <i class="fa-regular fa-circle-user leading"></i>
          <input id="email" name="email" type="email" placeholder="Digite o seu e-mail" autocomplete="username" required value="<?= htmlspecialchars($emailTentativo) ?>">
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
// Fallback à prova de falhas: se /js/login.js não carregar (cache antigo,
// permissões, .htaccess), o formulário envia por POST normal em vez de
// recarregar a página com "?email=...&senha=..." na barra de endereço.
window.addEventListener('load', function () {
  var form = document.getElementById('form-login');
  if (form && !window.MONANA_LOGIN_JS_OK) {
    form.setAttribute('method', 'POST');
    form.setAttribute('action', URL_BASE + '/auth/autenticar');
  }
});
</script>
<script src="<?= URL_BASE ?>/js/login.js?v=<?= time() ?>" onerror="var f=document.getElementById('form-login');if(f){f.setAttribute('method','POST');f.setAttribute('action','<?= URL_BASE ?>/auth/autenticar');}"></script>
</body>
</html>
