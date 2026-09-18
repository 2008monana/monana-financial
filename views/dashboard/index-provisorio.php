<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MonanaFinancial — Dashboard (provisório)</title>
<style>
  body{font-family:'Inter',sans-serif;background:#f6f8fb;color:#101828;padding:48px;}
  .caixa{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;padding:32px;box-shadow:0 20px 40px -20px rgba(10,25,48,.25);}
  h1{font-size:20px;margin-bottom:8px;}
  p{color:#64748b;font-size:14px;margin-bottom:4px;}
  a{display:inline-block;margin-top:20px;color:#16a34a;font-weight:600;text-decoration:none;}
</style>
</head>
<body>
  <div class="caixa">
    <h1>✅ Login funcional!</h1>
    <p>Sessão iniciada como: <strong><?= htmlspecialchars($nome) ?></strong></p>
    <p>Perfil: <strong><?= htmlspecialchars($perfil) ?></strong></p>
    <p style="margin-top:16px;">Esta é uma página provisória — o Dashboard completo (KPIs, gráficos, tabelas) baseado no <code>dashboard.html</code> será construído na próxima fase.</p>
    <a href="<?= URL_BASE ?>/auth/logout">Terminar sessão →</a>
  </div>
</body>
</html>
