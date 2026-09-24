<?php
/**
 * AuthMiddleware
 * Garante que apenas utilizadores autenticados acedam às rotas protegidas.
 */

class AuthMiddleware
{
    public function verificar(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . URL_BASE . '/auth/login');
            exit;
        }
    }

    /**
     * Verifica se o utilizador autenticado tem um dos perfis permitidos.
     * Uso: (new AuthMiddleware())->exigirPerfil(['super_admin','admin_empresa']);
     */
    public function exigirPerfil(array $perfisPermitidos): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? null;

        if (in_array($perfil, $perfisPermitidos, true)) {
            return;
        }

        // Para perfis operacionais, a autorização é definida pelas páginas
        // selecionadas no formulário de utilizador. O Router já aplica esta
        // mesma regra antes de instanciar o controlador; esta verificação
        // adicional mantém a proteção quando o controlador é chamado direto.
        $rota = trim((string) ($_GET['url'] ?? ''), '/');
        $modulo = $this->moduloDaRota($rota);
        if ($modulo && !empty($_SESSION['usuario_id'])) {
            require_once CAMINHO_RAIZ . '/models/Modulo.php';
            if ((new Modulo())->temPermissao((int) $_SESSION['usuario_id'], $modulo)) {
                return;
            }
        }

        http_response_code(403);
        die('Acesso negado. Não tem permissão para aceder a este recurso.');
    }
    private function moduloDaRota(string $rota): ?string
    {
        if (str_starts_with($rota, 'transacoes/fechoDiario') || str_starts_with($rota, 'transacoes/salvarFecho')) return 'fecho_diario';
        if (str_starts_with($rota, 'transacoes/')) return 'movimentos';
        if (str_starts_with($rota, 'relatorios/diario-planilha')) return 'planilha';
        $pagina = explode('/', $rota)[0] ?? '';
        return [
            'empresas' => 'empresas', 'filiais' => 'filiais', 'usuarios' => 'usuarios',
            'backups' => 'backups', 'logs' => 'logs', 'configuracoes' => 'configuracoes',
        ][$pagina] ?? null;
    }

}
