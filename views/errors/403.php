<?php
/**
 * Página 403 - Acesso Negado
 */
$titulo = 'Acesso negado';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NOME; ?> — 403</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 20px 60px -20px rgba(10, 25, 48, 0.25);
        }
        
        .icon {
            font-size: 72px;
            color: #ef4444;
            margin-bottom: 16px;
        }
        
        h1 {
            font-family: 'Sora', sans-serif;
            font-size: 48px;
            font-weight: 800;
            color: #0a1930;
            margin-bottom: 4px;
        }
        
        h2 {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #0a1930;
            margin-bottom: 12px;
        }
        
        p {
            color: #64748b;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 32px;
            background: linear-gradient(135deg, #0e2748, #0a1930);
            color: #ffffff;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            box-shadow: 0 8px 24px -8px rgba(14, 39, 72, 0.4);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px -8px rgba(14, 39, 72, 0.5);
        }
        
        .btn-outline {
            background: transparent;
            color: #0a1930;
            box-shadow: none;
            border: 2px solid #e6eaf0;
        }
        
        .btn-outline:hover {
            background: #f4f6fa;
            box-shadow: none;
        }
        
        .links {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        
        .footer {
            margin-top: 32px;
            font-size: 13px;
            color: #94a3b8;
        }
        
        .footer a {
            color: #0a1930;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 32px 20px;
            }
            
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 20px;
            }
            
            .icon {
                font-size: 56px;
            }
            
            .btn {
                padding: 12px 24px;
                font-size: 14px;
            }
            
            .links {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <i class="fas fa-lock"></i>
        </div>
        
        <h1>403</h1>
        <h2>Acesso negado</h2>
        
        <p>
            Você não tem permissão para aceder a esta página.
            Contacte o administrador se acredita que isto é um erro.
        </p>
        
        <div class="links">
            <a href="/dashboard" class="btn">
                <i class="fas fa-home"></i> Ir para o Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
        
        <div class="footer">
            <p>
                <a href="/"><?php echo APP_NOME; ?></a> © <?php echo date('Y'); ?> — 
                <a href="mailto:<?php echo SMTP_EMAIL_REMETENTE; ?>">Suporte</a>
            </p>
        </div>
    </div>
</body>
</html>