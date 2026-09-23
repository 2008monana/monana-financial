<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Backup.php';
require_once CAMINHO_RAIZ . '/models/Configuracao.php';
require_once CAMINHO_RAIZ . '/helpers/BackupHelper.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * BackupsController
 * Super Admin  -> âmbito global: vê/gera backups da base de dados inteira.
 * Admin Empresa -> âmbito da sua empresa: vê/gera backups só com os dados dela.
 * Cada um só vê e só consegue descarregar/eliminar os backups do seu próprio âmbito.
 */
class BackupsController extends Controller
{
    private Backup $backups;
    private Configuracao $configuracoes;

    private const CHAVE_FREQUENCIA = 'backup_automatico_frequencia_dias';
    private const CHAVE_ATIVO = 'backup_automatico_ativo';

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin', 'admin_empresa']);
        $this->backups = new Backup();
        $this->configuracoes = new Configuracao();
    }

    /** null = âmbito global (Super Admin); int = id da empresa (Admin Empresa). */
    private function empresaId(): ?int
    {
        return ($_SESSION['usuario_perfil'] ?? '') === 'super_admin' ? null : (int) ($_SESSION['empresa_id'] ?? 0);
    }

    private function csrf(): bool
    {
        return SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '');
    }

    public function index(): void
    {
        $empresaId = $this->empresaId();

        // Tentativa "leve" de executar o backup automático em atraso para o
        // próprio âmbito, já que o sistema pode não ter um cron real configurado.
        $this->executarSeEmAtraso($empresaId);

        $config = $this->configuracoes->obterTodas($empresaId);

        $this->renderizar('backups/index', [
            'tituloPagina'   => 'Backups',
            'paginaAtiva'    => 'backups',
            'backups'        => $this->backups->listar($empresaId),
            'escopoGlobal'   => $empresaId === null,
            'frequenciaDias' => (int) ($config[self::CHAVE_FREQUENCIA] ?? 7),
            'automaticoAtivo'=> ($config[self::CHAVE_ATIVO] ?? '1') === '1',
            'ultimoBackup'   => $this->backups->ultimoConcluidoEm($empresaId),
            'csrf_token'     => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /** Backup manual, disparado pelo utilizador. */
    public function gerar(): void
    {
        if (!$this->csrf()) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('backups/index');
        }

        $this->executar('manual');
        $this->redirecionar('backups/index');
    }

    public function configurarAutomatico(): void
    {
        if (!$this->csrf()) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('backups/index');
        }

        $empresaId = $this->empresaId();
        $dias = (int) ($_POST['frequencia_dias'] ?? 7);
        $dias = max(1, min(7, $dias)); // entre 1 e 7 dias, conforme pedido
        $ativo = !empty($_POST['ativo']) ? '1' : '0';

        $this->configuracoes->guardar($empresaId, self::CHAVE_FREQUENCIA, (string) $dias, 'numero');
        $this->configuracoes->guardar($empresaId, self::CHAVE_ATIVO, $ativo, 'booleano');

        AuditoriaHelper::registar('backup_agendamento_alterado', 'backups', null, null, [
            'empresa_id' => $empresaId, 'frequencia_dias' => $dias, 'ativo' => $ativo,
        ], 'media');

        definirFlash('sucesso', 'Agendamento de backup automático atualizado.');
        $this->redirecionar('backups/index');
    }

    public function download(string $id): void
    {
        $registo = $this->backups->encontrarVisivel((int) $id, $this->empresaId());
        if (!$registo) {
            http_response_code(404);
            echo 'Backup não encontrado.';
            return;
        }

        $caminho = CAMINHO_BACKUPS . '/' . $registo['caminho_relativo'];
        if (!file_exists($caminho)) {
            http_response_code(404);
            echo 'O ficheiro deste backup já não existe no servidor.';
            return;
        }

        AuditoriaHelper::registar('backup_descarregado', 'backups', (int) $registo['id'], null, null, 'media');

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($registo['nome_arquivo']) . '"');
        header('Content-Length: ' . filesize($caminho));
        readfile($caminho);
        exit;
    }

    public function eliminar(string $id): void
    {
        if (!$this->csrf()) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('backups/index');
        }

        $registo = $this->backups->encontrarVisivel((int) $id, $this->empresaId());
        if ($registo) {
            $caminho = CAMINHO_BACKUPS . '/' . $registo['caminho_relativo'];
            if (file_exists($caminho)) {
                @unlink($caminho);
            }
            $this->backups->eliminar((int) $registo['id']);
            AuditoriaHelper::registar('backup_eliminado', 'backups', (int) $registo['id'], $registo, null, 'alta');
            definirFlash('sucesso', 'Backup eliminado.');
        }

        $this->redirecionar('backups/index');
    }

    // ---------------------------------------------------------------
    // Internos
    // ---------------------------------------------------------------

    private function executar(string $tipo): void
    {
        $empresaId = $this->empresaId();
        $resultado = $empresaId === null
            ? BackupHelper::gerarGlobal()
            : BackupHelper::gerarPorEmpresa($empresaId);

        if ($resultado['sucesso']) {
            $this->backups->inserir([
                'empresa_id'        => $empresaId,
                'usuario_id'        => $_SESSION['usuario_id'] ?? null,
                'tipo'              => $tipo,
                'escopo'            => $empresaId === null ? 'global' : 'empresa',
                'nome_arquivo'      => $resultado['nome'],
                'caminho_relativo'  => $resultado['caminho'],
                'tamanho_bytes'     => $resultado['tamanho'],
                'status'            => 'concluido',
            ]);
            AuditoriaHelper::registar('backup_gerado', 'backups', null, null, [
                'empresa_id' => $empresaId, 'tipo' => $tipo, 'tamanho_bytes' => $resultado['tamanho'],
            ], 'alta');
            definirFlash('sucesso', 'Backup gerado com sucesso (' . $this->formatarTamanho($resultado['tamanho']) . ').');
        } else {
            $this->backups->inserir([
                'empresa_id'       => $empresaId,
                'usuario_id'       => $_SESSION['usuario_id'] ?? null,
                'tipo'             => $tipo,
                'escopo'           => $empresaId === null ? 'global' : 'empresa',
                'nome_arquivo'     => '-',
                'caminho_relativo' => '-',
                'tamanho_bytes'    => 0,
                'status'           => 'falhou',
                'mensagem_erro'    => substr($resultado['erro'] ?? 'Erro desconhecido.', 0, 490),
            ]);
            AuditoriaHelper::registar('backup_falhou', 'backups', null, null, [
                'empresa_id' => $empresaId, 'erro' => $resultado['erro'] ?? null,
            ], 'alta');
            definirFlash('erro', 'Falha ao gerar o backup: ' . ($resultado['erro'] ?? 'erro desconhecido.'));
        }
    }

    private function formatarTamanho(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' bytes';
    }

    /** Gera automaticamente o backup do próprio âmbito se já passou o número de dias configurado. */
    private function executarSeEmAtraso(?int $empresaId): void
    {
        $config = $this->configuracoes->obterTodas($empresaId);
        if (($config[self::CHAVE_ATIVO] ?? '1') !== '1') {
            return;
        }
        $frequencia = max(1, min(7, (int) ($config[self::CHAVE_FREQUENCIA] ?? 7)));
        $ultimo = $this->backups->ultimoConcluidoEm($empresaId, 'automatico');

        $emAtraso = $ultimo === null || (strtotime($ultimo) <= strtotime("-{$frequencia} days"));
        if ($emAtraso) {
            $this->executar('automatico');
        }
    }
}
