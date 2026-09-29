<?php
/**
 * Configuração geral do sistema MonanaFinancial
 */

// Define o caminho raiz APENAS se ainda não foi definido
if (!defined('CAMINHO_RAIZ')) {
    define('CAMINHO_RAIZ', dirname(__DIR__));
}

// Ambiente: 'desenvolvimento' ou 'producao'
define('AMBIENTE', 'desenvolvimento');

// =====================================================
// URL BASE DO SISTEMA (URLs "limpas", sem /public)
// Detecta automaticamente o prefixo da aplicação:
// - Local (XAMPP): http://localhost/monana-financial
// - Domínio próprio: http://seudominio.ao  (ou https)
// =====================================================
$__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https' : 'http';
$__host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
// SCRIPT_NAME ex.: /monana-financial/public/index.php ou /public/index.php
$__dir    = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
// Remove um eventual "/public" final ( URLs limpas não mostram a pasta public )
$__dir = preg_replace('#(/public)?$#', '', rtrim($__dir, '/'));
// Garante o prefixo "/" inicial quando existe subpasta (ex.: "/monana-financial").
// Se $__dir for '' ou '/', a aplicação está na raiz do DocumentRoot.
if ($__dir !== '' && $__dir !== '/') {
    if ($__dir[0] !== '/') {
        // dirname devolveu caminho relativo (raro); reconstrói via REQUEST_URI
        $ru = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $pos = stripos($ru, '/public/');
        $__dir = $pos !== false ? substr($ru, 0, $pos) : '';
    }
} else {
    $__dir = '';
}
define('URL_BASE', $__scheme . '://' . $__host . $__dir);
unset($__scheme, $__host, $__dir);

// Alias usados por alguns layouts/views antigas
if (!defined('APP_URL')) {
    define('APP_URL', URL_BASE);
}
if (!defined('APP_NOME')) {
    define('APP_NOME', 'MonanaFinancial');
}

// Nome do sistema
define('NOME_SISTEMA', 'MonanaFinancial');

// Fuso horário
date_default_timezone_set('Africa/Luanda');

// Exibição de erros (desligar em produção)
if (AMBIENTE === 'desenvolvimento') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// =====================================================
// PASTAS DE ESCRITA (logs / ficheiros temporários)
// Criadas automaticamente se não existirem, para evitar
// erros de escrita e conflitos com rotas do sistema.
// NOTA: a pasta de logs está DENTRO de /storage — nunca na
// raiz do projeto, porque uma pasta física "/logs" na raiz
// conflituava com a rota /logs e gerava "403 Forbidden".
// =====================================================
foreach (['storage/app_logs', 'storage/logs', 'storage/cache', 'storage/exports'] as $__pasta) {
    $__caminhoPasta = CAMINHO_RAIZ . '/' . $__pasta;
    if (!is_dir($__caminhoPasta)) {
        @mkdir($__caminhoPasta, 0775, true);
    }
}
unset($__pasta, $__caminhoPasta);

// =====================================================
// CORREÇÃO: Só inicia a sessão se ainda não estiver ativa
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    // Só define as configurações de sessão ANTES de iniciar
    ini_set('session.cookie_httponly', 1);
    session_start();
}

// Configuração de e-mail (SMTP)
return [
    'smtp_host'     => 'smtp.exemplo.com',
    'smtp_porta'    => 587,
    'smtp_usuario'  => '',
    'smtp_senha'    => '',
    'smtp_from'     => 'nao-responder@monanafinancial.com',
    'smtp_from_nome'=> NOME_SISTEMA,
];
