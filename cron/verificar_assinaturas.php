<?php
/**
 * Verificação diária de assinaturas — script de cron.
 *
 * Linha de crontab (correr uma vez por dia, ex.: às 04:00):
 *   0 4 * * *  php /caminho/para/monana/cron/verificar_assinaturas.php
 *
 * Para cada assinatura `activa` com data de fim:
 *  - faltam <= 7 dias -> aviso '7d' (uma única vez)
 *  - faltam <= 3 dias -> aviso '3d' (uma única vez)
 *  - faltam <= 1 dia  -> aviso '1d' (uma única vez)
 * Também detecta transições para carência e bloqueio (avisos 'carencia'/'bloqueio')
 * e envia um resumo do dia aos Super Admins (a vencer, em carência, bloqueadas).
 *
 * A idempotência é garantida pela tabela `assinatura_avisos` (UNIQUE assinatura_id+tipo),
 * portanto correr duas vezes no mesmo dia não duplica notificações.
 *
 * Não depende de sessão/login: corre apenas pela linha de comandos (CLI).
 */

define('CAMINHO_RAIZ', dirname(__DIR__));
require_once CAMINHO_RAIZ . '/config/config.php';
require_once CAMINHO_RAIZ . '/config/constants.php';
require_once CAMINHO_RAIZ . '/core/Database.php';
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/models/Configuracao.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Assinatura.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Este script só pode ser executado pela linha de comandos (cron).');
}

date_default_timezone_set('Africa/Luanda');

$assinaturas = new Assinatura();
$carenciaHoras = AssinaturaHelper::carenciaHoras();
$agora = time();

$vencerBreve = $emCarencia = $bloqueadas = 0;
$novosAvisos = 0;

echo '[' . date('Y-m-d H:i:s') . "] Inicio da verificacao de assinaturas (carencia: {$carenciaHoras}h)\n";

try {
    // 1) Assinaturas ACTIVAS pagas com data de fim -> avisos 7d/3/1d
    $stmt = Database::obterLigacao()->prepare(
        "SELECT a.id, a.empresa_id, a.fim, p.nome AS plano_nome, p.codigo AS plano_codigo
         FROM assinaturas a
         JOIN planos p ON p.id = a.plano_id
         WHERE a.estado = 'activa' AND a.bloqueada_manual = 0
           AND a.fim IS NOT NULL AND a.fim > :agora1
         ORDER BY a.fim ASC"
    );
    $stmt->execute(['agora1' => date('Y-m-d H:i:s', $agora)]);
    $activasFuturas = $stmt->fetchAll();

    foreach ($activasFuturas as $lin) {
        $fimTs   = strtotime($lin['fim']);
        $restante = $fimTs - $agora;
        $dias     = (int) ceil($restante / 86400);

        if ($restante <= 7 * 86400) { $novosAvisos += AssinaturaHelper::notificarVencimento((int) $lin['empresa_id'], (int) $lin['id'], '7d', min($dias, 7)) ? 1 : 0; }
        if ($restante <= 3 * 86400) { $novosAvisos += AssinaturaHelper::notificarVencimento((int) $lin['empresa_id'], (int) $lin['id'], '3d', min($dias, 3)) ? 1 : 0; }
        if ($restante <= 86400)     { $novosAvisos += AssinaturaHelper::notificarVencimento((int) $lin['empresa_id'], (int) $lin['id'], '1d', max(1, $dias)) ? 1 : 0; }
        if ($restante <= 7 * 86400) { $vencerBreve++; }
    }

    // 2) Estado calculado por empresa -> detectar carência e bloqueio (rede de segurança; o middleware também trata)
    try {
        $listaEmpresas = Database::obterLigacao()->query("SELECT id, nome FROM empresas WHERE ativa = 1 ORDER BY id")->fetchAll();
    } catch (Throwable $e) {
        $listaEmpresas = [];
    }

    foreach ($listaEmpresas as $emp) {
        $estado = $assinaturas->estadoDaEmpresa((int) $emp['id'], $carenciaHoras);
        $st = $estado['estado'];
        $linhaId = (int) ($estado['linha']['id'] ?? 0);

        if ($st === 'carencia') {
            $emCarencia++;
            if ($linhaId > 0) {
                $causa = (($estado['linha']['plano_codigo'] ?? '') === 'gratuito') ? 'cessou' : 'expirou';
                AssinaturaHelper::notificarCarencia((int) $emp['id'], $linhaId, $causa, $carenciaHoras);
            }
        } elseif ($st === 'bloqueada') {
            $bloqueadas++;
            if ($linhaId > 0) {
                AssinaturaHelper::notificarBloqueio((int) $emp['id'], $linhaId);
            }
        }
    }

    // 3) Resumo diário aos Super Admins
    $resumo = "Resumo diario das assinaturas (" . date('d/m/Y') . "):\n"
            . "- A vencer nos proximos 7 dias: {$vencerBreve}\n"
            . "- Em carencia: {$emCarencia}\n"
            . "- Bloqueadas: {$bloqueadas}";
    NotificacaoHelper::paraSuperAdministradores(NOTIF_AVISO, 'Resumo de assinaturas do dia', $resumo, URL_BASE . '/assinaturas');

    echo "Concluido: {$vencerBreve} a vencer, {$emCarencia} em carencia, {$bloqueadas} bloqueadas. Novos avisos enviados: {$novosAvisos}\n";
} catch (Throwable $e) {
    error_log('[cron verificar_assinaturas] ' . $e->getMessage());
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

echo "[" . date('Y-m-d H:i:s') . "] Fim.\n";
