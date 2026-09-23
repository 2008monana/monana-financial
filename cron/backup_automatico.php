<?php
/**
 * Script para ser executado periodicamente pelo cron do servidor (ex.: uma vez por dia):
 *
 *   0 3 * * *  php /caminho/para/monana/cron/backup_automatico.php
 *
 * Percorre o backup global e o de cada empresa e gera um novo backup
 * automático sempre que já passou o número de dias configurado
 * (Configurações > Backups, 1 a 7 dias). Isto é um complemento ao
 * mecanismo "preguiçoso" que já corre quando alguém abre a página
 * de Backups — este script garante que os backups automáticos
 * acontecem mesmo que ninguém entre no sistema nesse dia.
 *
 * Não depende de sessão/login: corre pela linha de comandos (CLI) ou,
 * se for exposto via web, deve ficar fora de /public ou protegido.
 */

define('CAMINHO_RAIZ', dirname(__DIR__));
require_once CAMINHO_RAIZ . '/config/config.php';
require_once CAMINHO_RAIZ . '/config/constants.php';
require_once CAMINHO_RAIZ . '/core/Database.php';
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/models/Backup.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Configuracao.php';
require_once CAMINHO_RAIZ . '/helpers/BackupHelper.php';

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Este script só pode ser executado pela linha de comandos (cron).');
}

$backupModel = new Backup();
$configModel = new Configuracao();

function emAtraso(Configuracao $configModel, Backup $backupModel, ?int $empresaId): bool
{
    $config = $configModel->obterTodas($empresaId);
    if (($config['backup_automatico_ativo'] ?? '1') !== '1') {
        return false;
    }
    $frequencia = max(1, min(7, (int) ($config['backup_automatico_frequencia_dias'] ?? 7)));
    $ultimo = $backupModel->ultimoConcluidoEm($empresaId, 'automatico');
    return $ultimo === null || strtotime($ultimo) <= strtotime("-{$frequencia} days");
}

function registarResultado(Backup $backupModel, ?int $empresaId, array $resultado): void
{
    $backupModel->inserir([
        'empresa_id'       => $empresaId,
        'usuario_id'       => null,
        'tipo'             => 'automatico',
        'escopo'           => $empresaId === null ? 'global' : 'empresa',
        'nome_arquivo'     => $resultado['nome'] ?? '-',
        'caminho_relativo' => $resultado['caminho'] ?? '-',
        'tamanho_bytes'    => $resultado['tamanho'] ?? 0,
        'status'           => $resultado['sucesso'] ? 'concluido' : 'falhou',
        'mensagem_erro'    => $resultado['sucesso'] ? null : substr((string) ($resultado['erro'] ?? 'Erro desconhecido.'), 0, 490),
    ]);
}

// Backup global (Super Admin)
if (emAtraso($configModel, $backupModel, null)) {
    echo "A gerar backup global...\n";
    registarResultado($backupModel, null, BackupHelper::gerarGlobal());
}

// Backup de cada empresa ativa
$empresas = (new Empresa())->ativas();
foreach ($empresas as $empresa) {
    $empresaId = (int) $empresa['id'];
    if (emAtraso($configModel, $backupModel, $empresaId)) {
        echo "A gerar backup da empresa #{$empresaId} ({$empresa['nome']})...\n";
        registarResultado($backupModel, $empresaId, BackupHelper::gerarPorEmpresa($empresaId));
    }
}

echo "Concluído.\n";
