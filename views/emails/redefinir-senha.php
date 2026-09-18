<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redefinição de Senha - MonanaFinancial</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f4f6fa; padding: 20px; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .header { text-align: center; border-bottom: 2px solid #22c55e; padding-bottom: 24px; margin-bottom: 24px; }
        .header h1 { color: #0a1930; margin: 0; font-size: 24px; font-weight: 700; }
        .header h1 span { color: #22c55e; }
        .header p { color: #64748b; margin: 4px 0 0; font-size: 14px; }
        .content { line-height: 1.7; color: #101828; }
        .content p { margin: 12px 0; }
        .btn { display: inline-block; background: linear-gradient(135deg, #0e2748, #0a1930); color: #fff; padding: 14px 36px; text-decoration: none; border-radius: 10px; font-weight: 600; margin: 20px 0; }
        .link-box { background: #f4f6fa; padding: 14px; border-radius: 8px; word-break: break-all; font-size: 13px; color: #0a1930; border: 1px solid #e6eaf0; }
        .warning { background: #fff4e0; border-left: 4px solid #f59e0b; padding: 14px 18px; margin: 20px 0; border-radius: 6px; font-size: 14px; color: #101828; }
        .footer { border-top: 1px solid #e6eaf0; padding-top: 20px; margin-top: 24px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Monana<span>Financial</span></h1>
            <p>🔐 Recuperação de Senha</p>
        </div>
        <div class="content">
            <p>Olá <strong><?php echo htmlspecialchars($nome); ?></strong>,</p>
            <p>Recebemos um pedido para redefinir a sua senha no MonanaFinancial.</p>
            
            <div style="text-align: center;">
                <a href="<?php echo $link_redefinicao; ?>" class="btn">Redefinir Senha</a>
            </div>
            
            <div class="warning">
                ⚠️ Este link é válido por <strong><?php echo $expiracao; ?></strong>.
                Se não solicitou a recuperação, ignore este email.
            </div>
            
            <p>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
            <div class="link-box">
                <?php echo $link_redefinicao; ?>
            </div>
        </div>
        <div class="footer">
            <p>MonanaFinancial © <?php echo date('Y'); ?> - Todos os direitos reservados.</p>
            <p>Este é um email automático, por favor não responda.</p>
        </div>
    </div>
</body>
</html>