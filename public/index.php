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
// CARREGA O NÚCLEO (agora em app/Core)
// =====================================================
require_once CAMINHO_RAIZ . '/app/Core/Controller.php';
require_once CAMINHO_RAIZ . '/app/Core/Database.php';
require_once CAMINHO_RAIZ . '/app/Core/Model.php';
require_once CAMINHO_RAIZ . '/app/Core/Router.php';

// =====================================================
// CARREGA HELPERS (agora em app/Helpers)
// =====================================================
require_once CAMINHO_RAIZ . '/app/Helpers/flash.php';
require_once CAMINHO_RAIZ . '/app/Helpers/formatacao.php';
require_once CAMINHO_RAIZ . '/app/Helpers/FormatacaoHelper.php';
require_once CAMINHO_RAIZ . '/app/Helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/app/Helpers/SessaoHelper.php';
require_once CAMINHO_RAIZ . '/app/Helpers/ValidacaoHelper.php';
require_once CAMINHO_RAIZ . '/app/Helpers/Autenticacao.php';

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

// Compatibilidade: remove QUALQUER prefixo de subpasta do projecto
// (ex.: url=/monana-financial/logs ou /htdocs/monana-financial/logs)
// para que o Router veja apenas a rota interna (logs). Sao testados:
//  - o prefixo detectado via URL_BASE;
//  - os primeiros segmentos do REQUEST_URI ate ao front controller.
$prefixos = [];
$baseDir = trim(preg_replace('#^https?://[^/]+#i', '', URL_BASE), '/');
if ($baseDir !== '') $prefixos[] = $baseDir;
$ruPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$posPub = stripos($ruPath, '/public/');
if ($posPub !== false) {
    $pre = trim(substr($ruPath, 0, $posPub), '/');
    if ($pre !== '') $prefixos[] = $pre;
}
if ($url !== '' && !empty($prefixos)) {
    $u = ltrim($url, '/');
    foreach ($prefixos as $p) {
        if ($u === $p || strpos($u, $p . '/') === 0) {
            $url = substr($u, strlen($p));
            break;
        }
    }
}

$roteador->despachar($url);