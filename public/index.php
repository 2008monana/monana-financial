<?php
/**
 * Ponto de entrada do sistema MonanaFinancial
 */

// =====================================================
// DEFINE O CAMINHO RAIZ UMA ÚNICA VEZ
// =====================================================
define('CAMINHO_RAIZ', dirname(__DIR__));

// =====================================================
// CARREGA AUTOLOAD DO COMPOSER
// =====================================================
$autoloadPath = CAMINHO_RAIZ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// =====================================================
// CARREGA CONFIGURAÇÕES
// =====================================================
require_once CAMINHO_RAIZ . '/config/config.php';

// =====================================================
// VERIFICA SESSÃO
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================================
// CARREGA O NÚCLEO
// =====================================================
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/core/Database.php';
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/core/Router.php';

// =====================================================
// CARREGA HELPERS
// =====================================================
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/formatacao.php';
require_once CAMINHO_RAIZ . '/helpers/FormatacaoHelper.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/SessaoHelper.php';
require_once CAMINHO_RAIZ . '/helpers/ValidacaoHelper.php';
require_once CAMINHO_RAIZ . '/helpers/Autenticacao.php';

// =====================================================
// CONSTANTES
// =====================================================
require_once CAMINHO_RAIZ . '/config/constants.php';

// =====================================================
// ROTEADOR - NÃO PRECISA DE ROTAS MANUAIS!
// O Router já faz o mapeamento automático:
// /transacoes/exportar/pdf → TransacoesController@exportarPDF
// =====================================================
$roteador = new Router();

// =====================================================
// EXECUTA
// =====================================================
$url = $_GET['url'] ?? '';
$roteador->despachar($url);