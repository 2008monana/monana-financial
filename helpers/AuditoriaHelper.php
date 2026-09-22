<?php
/** Registo imutável, normalizado e centralizado de eventos auditáveis. */
class AuditoriaHelper
{
    private const CAMPOS_SENSIVEIS = ['senha', 'senha_hash', 'smtp_senha', 'token', 'csrf_token'];
    private const PRIORIDADES = ['baixa', 'media', 'alta'];

    public static function registar(
        string $acao,
        ?string $tabelaAfetada = null,
        ?int $registoId = null,
        ?array $dadosAntigos = null,
        ?array $dadosNovos = null,
        string $prioridade = 'media',
        ?string $motivo = null,
        ?int $usuarioId = null
    ): void {
        try {
            require_once CAMINHO_RAIZ . '/models/LogAuditoria.php';
            (new LogAuditoria())->registar(
                $usuarioId ?? (isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null),
                $acao,
                $tabelaAfetada,
                $registoId,
                self::ocultarSensivel($dadosAntigos),
                self::ocultarSensivel($dadosNovos),
                in_array($prioridade, self::PRIORIDADES, true) ? $prioridade : 'media',
                $motivo
            );
        } catch (Throwable $e) {
            // A auditoria nunca deve interromper o fluxo principal da aplicação.
            error_log('[Auditoria] ' . $e->getMessage());
        }
    }

    private static function ocultarSensivel(?array $dados): ?array
    {
        if ($dados === null) return null;
        foreach (self::CAMPOS_SENSIVEIS as $campo) {
            if (array_key_exists($campo, $dados)) $dados[$campo] = '[OCULTO]';
        }
        return $dados;
    }
}
