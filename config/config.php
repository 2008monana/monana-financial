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

// URL base do sistema
define('URL_BASE', 'http://localhost/monana-financial/public');

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