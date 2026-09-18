<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NOME; ?> — <?php echo $titulo ?? 'Iniciar Sessão'; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/estilo-login.css">
    
    <style>
        /* CSS de emergência caso os arquivos não carreguem */
        .painel-login { display: flex; min-height: 100vh; }
        .lado-formulario { flex: 1; padding: 40px; background: #fff; }
        .btn-submit { background: #0a1930; color: #fff; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; }
        .campo { margin-bottom: 16px; }
        .campo input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .campo label { display: block; margin-bottom: 4px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="painel-login">
        <?php echo $conteudo; ?>
    </div>
    
    <!-- Scripts -->
    <script src="<?php echo APP_URL; ?>/js/notificacoes.js"></script>
    <script src="<?php echo APP_URL; ?>/js/login.js"></script>
</body>
</html>