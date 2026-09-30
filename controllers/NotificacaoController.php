<?php
/**
 * NotificacaoController
 * Gerencia as notificações do utilizador
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Notificacao.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';

class NotificacaoController extends Controller
{
    private Notificacao $notificacaoModel;

    public function __construct()
    {
        (new AuthMiddleware())->verificar();
        $this->notificacaoModel = new Notificacao();
    }

    /**
     * Listar notificações do utilizador
     */
    public function index(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $notificacoes = $this->notificacaoModel->recentesPorUsuario($usuarioId, 100);
        $naoLidas = $this->notificacaoModel->contarNaoLidas($usuarioId);

        $this->renderizar('notificacoes/index', [
            'tituloPagina' => 'Notificações',
            'paginaAtiva' => 'notificacoes',
            'notificacoes' => $notificacoes,
            'naoLidas' => $naoLidas,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Marcar notificação como lida
     */
    public function marcarComoLida(string $id): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $resultado = $this->notificacaoModel->marcarComoLida((int) $id, $usuarioId);

        if ($resultado) {
            AuditoriaHelper::registar('notificacao_lida', 'notificacoes', (int) $id, null, null, 'baixa');
            // Redirecionar para a página de notificações
            $this->redirecionar('notificacoes');
        } else {
            definirFlash('erro', 'Erro ao marcar notificação como lida.');
            $this->redirecionar('notificacoes');
        }
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function marcarTodasComoLidas(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];

        // Buscar todas as notificações não lidas
        $notificacoes = $this->notificacaoModel->recentesPorUsuario($usuarioId, 1000);
        $count = 0;

        foreach ($notificacoes as $notificacao) {
            if (!$notificacao['lida']) {
                $this->notificacaoModel->marcarComoLida((int) $notificacao['id'], $usuarioId);
                $count++;
            }
        }

        if ($count > 0) AuditoriaHelper::registar('notificacao_lida', 'notificacoes', null, null, ['quantidade'=>$count], 'baixa');
        definirFlash('sucesso', $count . ' notificações marcadas como lidas.');
        $this->redirecionar('notificacoes');
    }

    /**
     * API: Buscar contagem de notificações não lidas (para o badge)
     */
    public function contagem(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        // Notificações destinam-se apenas ao admin da empresa — os restantes
        // perfis (incluindo o super admin) recebem contagem zero, mantendo o
        // sino de notificações oculto.
        $perfil = $_SESSION['usuario_perfil'] ?? '';
        $naoLidas = $perfil === 'admin_empresa' ? $this->notificacaoModel->contarNaoLidas($usuarioId) : 0;

        $this->json([
            'sucesso' => true,
            'nao_lidas' => $naoLidas,
        ]);
    }

    /**
     * API: Buscar notificações recentes (para o dropdown)
     */
    public function recentes(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $notificacoes = $this->notificacaoModel->recentesPorUsuario($usuarioId, 10);
        $naoLidas = $this->notificacaoModel->contarNaoLidas($usuarioId);

        $this->json([
            'sucesso' => true,
            'nao_lidas' => $naoLidas,
            'notificacoes' => $notificacoes,
        ]);
    }

    /**
     * Criar notificação (método auxiliar para outros controllers)
     */
    public static function criar(int $usuarioId, string $tipo, string $titulo, string $mensagem, ?string $link = null): int
    {
        $notificacaoModel = new Notificacao();
        return $notificacaoModel->criar($usuarioId, $tipo, $titulo, $mensagem, $link);
    }
}