<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'MonanaFinancial') ?> — MonanaFinancial</title>

    <!-- Favicon e logotipo: data URI (inline) como garantia absoluta de
         que aparecem mesmo se a pasta /images estiver inacessível. -->
    <?php
    if (!function_exists('monanaDataUriImagem')) {
        /**
         * Converte uma imagem do projeto em data URI (base64).
         * Usa cache estático por pedido para não repetir o trabalho.
         */
        function monanaDataUriImagem(string $relativo): string
        {
            static $cache = [];
            if (isset($cache[$relativo])) {
                return $cache[$relativo];
            }
            $candidatos = [
                CAMINHO_RAIZ . '/public/' . $relativo,
                CAMINHO_RAIZ . '/' . $relativo,
            ];
            $resultado = '';
            foreach ($candidatos as $caminho) {
                if (is_file($caminho)) {
                    $ext = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
                    $mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                             'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon'][$ext] ?? 'image/png';
                    $dados = @file_get_contents($caminho);
                    if ($dados !== false) {
                        $resultado = 'data:' . $mime . ';base64,' . base64_encode($dados);
                    }
                    break;
                }
            }
            return $cache[$relativo] = $resultado;
        }
    }

    // URL limpa dos ficheiros (sem /public na barra de endereço)
    $urlFavicon = URL_BASE . '/images/favicon.png';
    $urlLogo    = URL_BASE . '/images/logo.png';
    $uriFavicon = monanaDataUriImagem('images/favicon.png');
    $uriLogo    = monanaDataUriImagem('images/logo.png');
    $cacheBust  = @filemtime(CAMINHO_RAIZ . '/public/images/favicon.png') ?: time();
    ?>
    <link rel="icon" type="image/png" href="<?= $uriFavicon !== '' ? $uriFavicon : htmlspecialchars($urlFavicon); ?>">
    <link rel="apple-touch-icon" href="<?= $uriFavicon !== '' ? $uriFavicon : htmlspecialchars($urlFavicon); ?>">
    <?php if ($uriFavicon === ''): /* fallback remoto quando o ficheiro não existe */ ?>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($urlFavicon); ?>?v=<?= (int) $cacheBust ?>">
    <?php endif; ?>

    <!-- Logotipo padrão em data URI: nunca depende do acesso à pasta /images -->
    <script>window.MONANA_LOGO_URI = <?= json_encode($uriLogo) ?>;</script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Estilos das páginas de autenticação -->
    <link rel="stylesheet" href="<?= URL_BASE ?>/css/estilo-login.css?v=<?= (int) (@filemtime(CAMINHO_RAIZ . '/public/css/estilo-login.css') ?: time()) ?>">
    <link rel="stylesheet" href="<?= URL_BASE ?>/css/autenticacao.css?v=<?= (int) (@filemtime(CAMINHO_RAIZ . '/public/css/autenticacao.css') ?: time()) ?>">
</head>
<body>
    <div class="painel-login">
        <?= $conteudo ?>
    </div>

    <!-- Scripts -->
    <script src="<?= URL_BASE ?>/js/notificacoes.js"></script>
</body>
</html>
