<?php
require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

/**
 * Middleware de Assinatura — corre logo depois do AuthMiddleware, para
 * rotas não públicas.
 *
 *  - Super Admin: sai logo (nunca é bloqueado);
 *  - Utilizador sem empresa: sai logo;
 *  - Calcula o estado da empresa e memoriza-o no AssinaturaHelper para o
 *    layout usar sem repetir a consulta;
 *  - Sem acesso => redirecciona para /assinaturas/bloqueada (JSON 403 em AJAX);
 *  - Rede de segurança: se entrou em carência por expiração natural e ainda
 *    não existe o aviso `carencia`, cria a notificação (idempotente);
 *  - FALHA ABERTA: qualquer excepção na BD => error_log e deixa passar.
 */
class AssinaturaMiddleware
{
    public function verificar(string $rota): void
    {
        try {
            $perfil = $_SESSION['usuario_perfil'] ?? '';
            if ($perfil === 'super_admin') {
                return; // regra 3.8: o Super Admin nunca é afectado por este módulo
            }

            $empresaId = (int) ($_SESSION['empresa_id'] ?? 0);
            if ($empresaId <= 0) {
                return; // utilizador sem empresa (o fluxo existente trata disso)
            }

            $carenciaHoras = AssinaturaHelper::carenciaHoras();
            $estado = AssinaturaHelper::calcularEMemorizar($empresaId);
            if ($estado === null) {
                return; // cálculo falhou (já registado em error_log) -> falha aberta
            }

            $linhaId = isset($estado['linha']['id']) ? (int) $estado['linha']['id'] : 0;

            if ($estado['estado'] === 'carencia' && $linhaId > 0) {
                // Rede de segurança para o caso de o cron não ter corrido:
                // entrada em carência por expiração natural -> notificar (idempotente).
                $causa = (($estado['linha']['plano_codigo'] ?? '') === 'gratuito') ? 'gratuita cessou' : 'expirou';
                AssinaturaHelper::notificarCarencia($empresaId, $linhaId, $causa, $carenciaHoras);
            } elseif ($estado['estado'] === 'bloqueada' && $linhaId > 0) {
                AssinaturaHelper::notificarBloqueio($empresaId, $linhaId);
            }

            $rotaNormalizada = strtolower(trim($rota, '/'));
            $permiteSemAcesso = in_array($rotaNormalizada, ['auth/logout', 'assinaturas/bloqueada'], true);

            if (!$estado['acesso'] && !$permiteSemAcesso) {
                $ajx = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
                if ($ajx || strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'sucesso' => false,
                        'erro' => 'ASSINATURA_BLOQUEADA',
                        'mensagem' => 'O acesso da sua empresa está suspenso por falta de assinatura.',
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                header('Location: ' . URL_BASE . '/assinaturas/bloqueada');
                exit;
            }
        } catch (Throwable $e) {
            // FALHA ABERTA: uma avaria deste módulo não pode deixar clientes sem acesso.
            error_log('[AssinaturaMiddleware] ' . $e->getMessage());
            return;
        }
    }
}
