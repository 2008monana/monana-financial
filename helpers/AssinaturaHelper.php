<?php
require_once CAMINHO_RAIZ . '/models/Configuracao.php';
require_once CAMINHO_RAIZ . '/models/Assinatura.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';

/**
 * Helper do módulo de assinaturas:
 *  - calcula e memoriza (por pedido) o estado da empresa para o layout;
 *  - lê configurações globais (whatsapp_admin, assinatura_carencia_horas);
 *  - gera o link de WhatsApp do administrador principal;
 *  - cria avisos idempotentes (tabela assinatura_avisos) + notificações.
 */
class AssinaturaHelper
{
    /** Estado calculado no pedido corrente (evita repetir a consulta no layout). */
    private static ?array $estadoCorrente = null;
    private static bool $estadoCalculado = false;

    /** Horas de carência configuráveis (chave global `assinatura_carencia_horas`). */
    public static function carenciaHoras(): int
    {
        try {
            $cfg = (new Configuracao())->obterTodas(null);
            $horas = (int) ($cfg['assinatura_carencia_horas'] ?? 48);
            return $horas > 0 ? min($horas, 24 * 30) : 48;
        } catch (Throwable $e) {
            error_log('[Assinaturas] Falha ao ler assinatura_carencia_horas: ' . $e->getMessage());
            return 48;
        }
    }

    /** Número de WhatsApp do administrador principal (string crua da BD). */
    public static function whatsappAdmin(): string
    {
        try {
            $cfg = (new Configuracao())->obterTodas(null);
            return trim((string) ($cfg['whatsapp_admin'] ?? ''));
        } catch (Throwable $e) {
            error_log('[Assinaturas] Falha ao ler whatsapp_admin: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Link wa.me com mensagem pré-preenchida. Devolve null quando não há
     * número configurado — as views devem então ESCONDER o botão e mostrar
     * "Contacte o administrador do sistema." Nunca rebenta com números inválidos.
     */
    public static function linkWhatsapp(string $mensagem): ?string
    {
        $numero = preg_replace('/\D/', '', self::whatsappAdmin());
        if ($numero === '' || $numero === null) {
            return null;
        }
        // Prefixo internacional "00" -> "+"
        if (str_starts_with($numero, '00')) {
            $numero = substr($numero, 2);
        }
        // Número angolano local (9 dígitos a começar por 9) -> prefixo 244
        if (strlen($numero) === 9 && str_starts_with($numero, '9')) {
            $numero = '244' . $numero;
        }
        if (strlen($numero) < 8) {
            return null; // número demasiado curto para ser válido: esconder botão
        }
        return 'https://wa.me/' . $numero . '?text=' . urlencode($mensagem);
    }

    /** Mensagem-padrão de negociação enviada pelo cliente ao Super Admin. */
    public static function mensagemNegociacao(string $empresaNome, string $situacao = 'expirou'): string
    {
        return 'Olá! Sou da empresa ' . $empresaNome . '. A nossa assinatura ' . $situacao
             . '. Gostaria de negociar a assinatura do Monana Financial.';
    }

    /**
     * Calcula (uma vez por pedido) e memoriza o estado da empresa do utilizador.
     * Super Admin ou utilizador sem empresa => null.
     */
    public static function calcularEMemorizar(?int $empresaId): ?array
    {
        if (self::$estadoCalculado) {
            return self::$estadoCorrente;
        }
        self::$estadoCalculado = true;
        if ($empresaId === null || $empresaId <= 0 || ($_SESSION['usuario_perfil'] ?? '') === 'super_admin') {
            return self::$estadoCorrente = null;
        }
        try {
            self::$estadoCorrente = (new Assinatura())->estadoDaEmpresa($empresaId, self::carenciaHoras());
        } catch (Throwable $e) {
            // Rede de segurança: uma avaria não pode deixar clientes sem acesso.
            error_log('[Assinaturas] estadoDaEmpresa falhou: ' . $e->getMessage());
            self::$estadoCorrente = null;
        }
        return self::$estadoCorrente;
    }

    /** Estado memorizado do pedido (sem repetir a consulta). */
    public static function estadoActual(): ?array
    {
        return self::$estadoCorrente;
    }

    /** Regista um aviso idempotente; devolve true só se a linha foi realmente inserida. */
    public static function garantirAviso(int $assinaturaId, string $tipo): bool
    {
        $tiposValidos = ['7d', '3d', '1d', 'carencia', 'bloqueio'];
        if (!in_array($tipo, $tiposValidos, true)) {
            return false;
        }
        try {
            $stmt = Database::obterLigacao()->prepare(
                "INSERT IGNORE INTO assinatura_avisos (assinatura_id, tipo) VALUES (:a, :t)"
            );
            $stmt->execute(['a' => $assinaturaId, 't' => $tipo]);
            return $stmt->rowCount() > 0;
        } catch (Throwable $e) {
            error_log('[Assinaturas] garantirAviso falhou: ' . $e->getMessage());
            return false;
        }
    }

    /** Notificação de entrada em carência (regras 3.3 e 3.4), idempotente pelo tipo `carencia`. */
    public static function notificarCarencia(int $empresaId, int $assinaturaId, string $causa, int $carenciaHoras): void
    {
        if (!self::garantirAviso($assinaturaId, 'carencia')) {
            return; // já tinha sido notificado
        }
        $nome = self::nomeEmpresa($empresaId);
        $titulo = 'Assinatura ' . ($causa === 'expirou' ? 'expirou' : 'cessou') . ' — regularize em ' . $carenciaHoras . ' horas';
        $mensagem = 'A sua assinatura ' . ($causa === 'expirou' ? 'expirou' : 'gratuita cessou')
                  . '. Assine em ' . $carenciaHoras . ' horas, no mínimo o plano mensal, para continuar a usar o sistema.'
                  . ' Contacte o administrador principal via WhatsApp para negociar.';
        try {
            NotificacaoHelper::paraAdministradoresEmpresa($empresaId, NOTIF_AVISO, $titulo, $mensagem, URL_BASE . '/assinaturas/minha');
        } catch (Throwable $e) {
            error_log('[Assinaturas] notificarCarencia falhou: ' . $e->getMessage());
        }
    }

    /** Notificação de bloqueio efectiva, idempotente pelo tipo `bloqueio`. */
    public static function notificarBloqueio(int $empresaId, int $assinaturaId): void
    {
        if (!self::garantirAviso($assinaturaId, 'bloqueio')) {
            return;
        }
        $nome = self::nomeEmpresa($empresaId);
        try {
            NotificacaoHelper::paraAdministradoresEmpresa(
                $empresaId, NOTIF_ALERTA, 'Acesso suspenso por falta de assinatura',
                'O período de tolerância da empresa ' . $nome . ' terminou e o acesso foi bloqueado.'
                . ' Contacte o administrador principal via WhatsApp para negociar a assinatura.',
                URL_BASE . '/assinaturas/bloqueada'
            );
        } catch (Throwable $e) {
            error_log('[Assinaturas] notificarBloqueio falhou: ' . $e->getMessage());
        }
    }

    /** Aviso prévio de vencimento (7/3/1 dias), idempotente pelo tipo. */
    public static function notificarVencimento(int $empresaId, int $assinaturaId, string $tipo, int $dias): void
    {
        if (!self::garantirAviso($assinaturaId, $tipo)) {
            return;
        }
        $nome = self::nomeEmpresa($empresaId);
        try {
            NotificacaoHelper::paraAdministradoresEmpresa(
                $empresaId, NOTIF_AVISO, 'A assinatura termina em ' . $dias . ' dia' . ($dias === 1 ? '' : 's'),
                'A assinatura da empresa ' . $nome . ' termina em ' . $dias . ' dia' . ($dias === 1 ? '' : 's')
                . '. Renove para não perder o acesso. Contacte o administrador principal via WhatsApp.',
                URL_BASE . '/assinaturas/minha'
            );
        } catch (Throwable $e) {
            error_log('[Assinaturas] notificarVencimento falhou: ' . $e->getMessage());
        }
    }

    /** Confirmação interna quando o Super Admin regista um pagamento. */
    public static function notificarPagamentoRecebido(int $empresaId, string $planoNome): void
    {
        $nome = self::nomeEmpresa($empresaId);
        try {
            NotificacaoHelper::paraAdministradoresEmpresa(
                $empresaId, NOTIF_SUCESSO, 'Pagamento registado — assinatura activa',
                'O pagamento do plano ' . $planoNome . ' da empresa ' . $nome . ' foi registado. O acesso está activo.',
                URL_BASE . '/assinaturas/minha'
            );
        } catch (Throwable $e) {
            error_log('[Assinaturas] notificarPagamentoRecebido falhou: ' . $e->getMessage());
        }
    }

    private static function nomeEmpresa(int $empresaId): string
    {
        try {
            require_once CAMINHO_RAIZ . '/models/Empresa.php';
            $e = (new Empresa())->encontrarPorId($empresaId);
            return $e['nome'] ?? ('Empresa #' . $empresaId);
        } catch (Throwable $ex) {
            return 'Empresa #' . $empresaId;
        }
    }
}
