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
// Basta colocar o conteúdo do pasta /public na raiz do
// servidor (ou apontar o DocumentRoot para /public).
// =====================================================
$__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https' : 'http';
$__host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__dir    = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
if ($__dir === '/' || $__dir === '.') {
    $__dir = '';
}
// Remove um eventual "/public" final ( URLs limpas não mostram a pasta public )
$__dir = preg_replace('#/public$#', '', rtrim($__dir, '/'));
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