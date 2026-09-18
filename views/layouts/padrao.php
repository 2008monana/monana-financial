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
</body>
</html>