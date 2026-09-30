<?php
/**
 * Página de bloqueio por assinatura — ecrã completo, SEM layout (renderizarSemLayout).
 * Variáveis: $empresa_nome, $estado
 */
$raiz       = defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2);
$urlBase    = defined('URL_BASE') ? URL_BASE : '';
$fbv        = @filemtime($raiz . '/public/images/logo.png') ?: time();
$linkWhats  = AssinaturaHelper::linkWhatsapp(
    AssinaturaHelper::mensagemNegociacao($empresa_nome ?? '', 'expirou')
);
$ultimoPlano = is_array($estado['plano'] ?? null) ? ($estado['plano']['nome'] ?? 'Gratuito') : ($estado['plano'] ?? 'Gratuito');
$terminouEm  = !empty($estado['limite_carencia']) ? $estado['limite_carencia']
             : (!empty($estado['fim']) ? $estado['fim'] : null);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acesso suspenso — MonanaFinancial</title>
<link rel="icon" type="image/png" sizes="32x32" href="<?= $urlBase ?>/images/favicon-32.png?v=<?= $fbv ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  html,body{margin:0;padding:0;min-height:100vh;font-family:'Inter',sans-serif}
  body{
    background:linear-gradient(160deg,#0a1930 0%,#0e2748 55%,#173a67 100%);
    display:flex;align-items:center;justify-content:center;padding:24px;position:relative;overflow-x:hidden
  }
  body::before{ /* brilho radial verde discreto, como no painel da marca do login */
    content:"";position:absolute;top:-160px;left:-160px;width:480px;height:480px;border-radius:50%;
    background:radial-gradient(circle,rgba(34,197,94,.18) 0%,rgba(34,197,94,0) 70%);pointer-events:none
  }
  .bl-card{
    position:relative;background:#fff;border-radius:20px;padding:48px 40px;max-width:560px;width:100%;
    text-align:center;box-shadow:0 20px 60px -20px rgba(10,25,48,.25);
    animation:blEntrada .28s ease-out both
  }
  @keyframes blEntrada{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
  .bl-logo{height:72px;object-fit:contain}
  .bl-marca{font-family:'Sora',sans-serif;font-weight:800;font-size:20px;color:#0a1930;margin-top:10px}
  .bl-marca span{color:#22c55e}
  .bl-icone{width:72px;height:72px;border-radius:50%;background:#fee2e2;color:#dc2626;
    display:flex;align-items:center;justify-content:center;font-size:28px;margin:26px auto 18px}
  .bl-titulo{font-family:'Sora',sans-serif;font-size:28px;font-weight:700;color:#0a1930;margin:0 0 12px}
  .bl-msg{font-size:15px;color:#5b6b7f;line-height:1.55;margin:0 0 22px}
  .bl-info{background:#f4f6fa;border:1px solid #e5eaf2;border-radius:12px;padding:14px 18px;
    font-size:13px;color:#5b6b7f;text-align:left;margin:0 0 24px;display:flex;flex-direction:column;gap:6px}
  .bl-info strong{color:#0a1930;font-weight:600}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:10px;
    font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;padding:10px 18px;transition:.2s}
  .btn-success{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff}
  .btn-success:hover{filter:brightness(1.08)}
  .btn-secondary{background:#eef1f6;color:#33415e;border:1px solid #dbe3ee}
  .btn-secondary:hover{background:#e4e9f1}
  .btn-sm{padding:8px 14px;font-size:12px}
  .bl-whats{width:100%;padding:14px;font-size:15px;border-radius:12px;margin-bottom:14px}
  .bl-semnum{font-size:14px;color:#5b6b7f;margin-bottom:14px}
  .bl-foot{margin-top:26px;font-size:12px;color:#94a3b8}
  @media(max-width:480px){.bl-card{padding:32px 20px;max-width:100%}.bl-titulo{font-size:22px}}
</style>
</head>
<body>
  <main class="bl-card">
    <img src="<?= $urlBase ?>/images/logo.png?v=<?= $fbv ?>" alt="MonanaFinancial" class="bl-logo">
    <div class="bl-marca">Monana<span>Financial</span></div>

    <div class="bl-icone"><i class="fas fa-lock"></i></div>

    <h1 class="bl-titulo">Acesso temporariamente suspenso</h1>
    <p class="bl-msg">
      A assinatura da empresa <strong><?= htmlspecialchars($empresa_nome ?? '') ?></strong> terminou
      e o período de tolerância já passou. Para voltar a usar o sistema, contacte o administrador principal
      para negociar a sua assinatura.
    </p>

    <div class="bl-info">
      <div><strong>Último plano:</strong> <?= htmlspecialchars($ultimoPlano) ?></div>
      <div><strong>Terminou em:</strong>
        <?= $terminouEm ? htmlspecialchars(date('d/m/Y', strtotime($terminouEm))) : '—' ?>
      </div>
    </div>

    <?php if ($linkWhats): ?>
      <a href="<?= htmlspecialchars($linkWhats) ?>" target="_blank" rel="noopener" class="btn btn-success bl-whats">
        <i class="fa-brands fa-whatsapp"></i> Falar com o administrador no WhatsApp
      </a>
    <?php else: ?>
      <p class="bl-semnum"><i class="fas fa-phone-slash"></i> Contacte o administrador do sistema.</p>
    <?php endif; ?>

    <a href="<?= $urlBase ?>/auth/logout" class="btn btn-secondary btn-sm">
      <i class="fas fa-right-from-bracket"></i> Terminar sessão
    </a>

    <div class="bl-foot">© <?= date('Y') ?> MonanaFinancial</div>
  </main>
</body>
</html>
