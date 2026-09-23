<?php
/**
 * Helper de Autenticação
 * Centraliza a verificação de sessão e os dados do utilizador autenticado.
 */

require_once __DIR__ . '/SessaoHelper.php';

class Autenticacao
{
    /**
     * Verifica se o utilizador tem uma sessão activa.
     * Caso contrário redirecciona para o login.
     */
    public function verificarLogin(): void
    {
        if (!SessaoHelper::estaLogado()) {
            $_SESSION['erro'] = 'Faça login para acessar esta página.';
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * Retorna os dados do utilizador autenticado.
     *
     * @return array{id:int, nome:string, email:string, perfil:string, empresa_id:?int}
     */
    public function usuario(): array
    {
        $empresaId = SessaoHelper::getUsuarioEmpresaId();

        return [
            'id'         => (int) SessaoHelper::getUsuarioId(),
            'nome'       => SessaoHelper::getUsuarioNome(),
            'email'      => SessaoHelper::getUsuarioEmail(),
            'perfil'     => SessaoHelper::getUsuarioPerfil(),
            'empresa_id' => $empresaId !== null ? (int) $empresaId : null,
        ];
    }

    /**
     * Verifica se o utilizador autenticado possui um dos perfis permitidos.
     *
     * @param string[] $perfis Lista de perfis autorizados.
     */
    public function verificarPermissao(array $perfis): void
    {
        $this->verificarLogin();

        $usuario = $this->usuario();

        if (!in_array($usuario['perfil'], $perfis, true)) {
            $_SESSION['erro'] = 'Permissão negada.';
            header('Location: /dashboard');
            exit;
        }
    }
}
