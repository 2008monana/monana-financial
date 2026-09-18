<?php
/**
 * Script para calcular resumos mensais de todas as filiais
 * Executar: php database/migrar_resumos.php
 * Ou via browser: http://localhost/monana-financial/database/migrar_resumos.php
 */

define('CAMINHO_RAIZ', dirname(__DIR__));

// Carregar configurações
require_once CAMINHO_RAIZ . '/config/config.php';
require_once CAMINHO_RAIZ . '/core/Database.php';
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/ResumoMensal.php';

// Verificar se está sendo executado via CLI ou browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    // Verificar autenticação (apenas Super Admin pode executar)
    session_start();
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_perfil'] ?? '') !== 'super_admin') {
        die('Acesso negado. Apenas Super Administrador pode executar esta ação.');
    }
    echo "<pre>";
}

echo "=== MIGRAÇÃO DE RESUMOS MENSAIS ===\n\n";

$filialModel = new Filial();
$resumoModel = new ResumoMensal();

// Buscar todas as filiais
$filiais = $filialModel->todas();

if (empty($filiais)) {
    echo "Nenhuma filial encontrada.\n";
    exit;
}

echo "Encontradas " . count($filiais) . " filiais.\n\n";

$totalAtualizados = 0;

foreach ($filiais as $filial) {
    $filialId = (int) $filial['id'];
    echo "Filial: " . $filial['nome'] . " (ID: $filialId)\n";
    
    // Recalcular todos os meses
    $resumoModel->recalcularTudo($filialId);
    $totalAtualizados++;
    
    echo "  ✅ Resumos calculados.\n";
}

echo "\n✅ Migração concluída com sucesso!\n";
echo "   Total de filiais processadas: " . $totalAtualizados . "\n";

if (!$isCLI) {
    echo "</pre>";
    echo '<p><a href="' . URL_BASE . '/dashboard/index">Voltar ao Dashboard</a></p>';
}