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

        if (!in_array($perfil, $perfisPermitidos, true)) {
            http_response_code(403);
            die('Acesso negado. Não tem permissão para aceder a este recurso.');
        }
    }
}
