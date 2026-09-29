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
// NUNCA FICAR EM TELA BRANCA: regista qualquer erro
// fatal em logs/php_errors.log e mostra uma mensagem
// amigável em vez de uma página completamente vazia.
// =====================================================
ini_set('log_errors', 1);
$__logErr = (defined('CAMINHO_LOGS') ? CAMINHO_LOGS : dirname(__DIR__) . '/logs');
$__logErrReal = dirname(__DIR__) . '/storage/logs'; // fora da raiz web: evita colisao com a rota dinamica /logs
if (!is_dir($__logErrReal)) { @mkdir($__logErrReal, 0775, true); }
ini_set('error_log', $__logErrReal . '/php_errors.log');
unset($__logErr);

register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            http_response_code(500);
            echo '<!DOCTYPE html><html lang="pt"><head><meta charset="UTF-8">'
               . '<title>Erro no sistema</title></head>'
               . '<body style="font-family:system-ui,sans-serif;background:#f6f8fb;display:flex;'
               . 'align-items:center;justify-content:center;min-height:100vh;margin:0">'
               . '<div style="background:#fff;border-radius:14px;padding:32px 40px;max-width:640px;'
               . 'box-shadow:0 10px 40px rgba(10,25,48,.12);border-left:6px solid #dc2626">'
               . '<h2 style="margin:0 0 10px;color:#991b1b">Ocorreu um erro ao carregar esta página</h2>'
               . '<p style="color:#475569;margin:0 0 12px">Detalhe técnico (também registado em <code>storage/logs/php_errors.log</code>):</p>'
               . '<pre style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;'
               . 'white-space:pre-wrap;word-break:break-all;font-size:13px;color:#7f1d1d;margin:0">'
               . htmlspecialchars($e['message'] . ' — ' . $e['file'] . ':' . $e['line'])
               . '</pre></div></body></html>';
        }
    }
});

// =====================================================
// PASTAS DE ESCRITA (logs / ficheiros temporários)
// Criadas automaticamente se não existirem, para evitar
// erros de escrita e conflitos com rotas do sistema.
// =====================================================
foreach (['logs', 'storage/logs', 'storage/cache', 'storage/exports'] as $__pasta) {
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
