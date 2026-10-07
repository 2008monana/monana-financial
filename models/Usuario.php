<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Usuario
 * Representa a tabela `usuarios`.
 */
class Usuario extends Model
{
    protected string $tabela = 'usuarios';

    public function encontrarPorEmail(string $email): array|false
    {
        return $this->buscarUmPor('email', $email);
    }

    public function verificarSenha(string $senhaDigitada, string $hashArmazenado): bool
    {
        return password_verify($senhaDigitada, $hashArmazenado);
    }

    public function criarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    public function atualizarUltimoLogin(int $usuarioId): bool
    {
        return $this->atualizar($usuarioId, ['ultimo_login' => date('Y-m-d H:i:s')]);
    }

    public function porEmpresa(int $empresaId): array
    {
        // Excluir super_admin da listagem de usuários da empresa
        $stmt = $this->bd->prepare("SELECT * FROM usuarios WHERE empresa_id = :empresa_id AND perfil != 'super_admin' ORDER BY nome");
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Todos os utilizadores VISÍVEIS para listagens administrativas.
     * O Super Administrador nunca aparece em nenhuma listagem consumida
     * pelas empresas nem é contabilizado como utilizador do sistema.
     */
    public function todosVisiveis(string $ordem = 'nome'): array
    {
        $stmt = $this->bd->prepare("SELECT * FROM usuarios WHERE perfil != 'super_admin' ORDER BY {$ordem}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Contagem de utilizadores efetivos do sistema, excluindo sempre o
     * Super Administrador (que não pertence a nenhuma empresa e não deve
     * ser contabilizado nem visível pelas empresas).
     */
    public function contarAtivos(?int $empresaId = null): int
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE perfil != 'super_admin' AND ativo = 1";
        $params = [];
        if ($empresaId !== null) { $sql .= ' AND empresa_id = :empresa_id'; $params['empresa_id'] = $empresaId; }
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** Impedir qualquer operação sobre contas super_admin a partir de contextos de empresa. */
    public function esSuperAdmin(int $usuarioId): bool
    {
        $stmt = $this->bd->prepare("SELECT COUNT(*) FROM usuarios WHERE id = :id AND perfil = 'super_admin'");
        $stmt->execute(['id' => $usuarioId]);
        return (bool) (int) $stmt->fetchColumn();
    }

    /**
     * REGRA DE NEGÓCIO: as empresas não podem ver nem contabilizar o Super
     * Administrador como utilizador do sistema. Em qualquer contexto que não
     * seja o do próprio Super Admin, contas super_admin são tratadas como
     * inexistentes (devolve false → "Utilizador não encontrado").
     */
    private function deveOcultarSuperAdmin(): bool
    {
        return ($_SESSION['usuario_perfil'] ?? '') !== 'super_admin';
    }

    /**
     * Leitura por ID com a regra de visibilidade aplicada: um admin de
     * empresa nunca obtém uma conta super_admin através deste model.
     */
    public function encontrarPorId(int $id): array|false
    {
        $usuario = parent::encontrarPorId($id);
        if ($usuario !== false && $this->deveOcultarSuperAdmin() && ($usuario['perfil'] ?? '') === 'super_admin') {
            // Exceções legítimas: o próprio Super Admin a aceder à sua conta
            // (perfil/configurações) e processos de sistema sem sessão ativa.
            $sessaoId = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;
            if ($sessaoId === $id || $sessaoId === 0) {
                return $usuario;
            }
            return false;
        }
        return $usuario;
    }

    /**
     * Lista TODOS os utilizadores efetivos (globais), excluindo sempre as
     * contas super_admin — para listagens e filtros consumidos em contexto
     * misto (logs, relatórios) onde o Super Admin não deve aparecer nem ser
     * contabilizado. Usado no lugar de `todos()` em toda a aplicação.
     */
    public function todos(string $ordem = ''): array
    {
        $sql = "SELECT * FROM usuarios";
        // REGRA: em contexto de empresa, as contas super_admin são invisíveis
        // e não contabilizadas. Fora desse contexto (painel global do Super
        // Admin ou processos de sistema, ex.: notificações), devolve tudo.
        if (($perfilSessao = $_SESSION['usuario_perfil'] ?? '') !== 'super_admin') {
            $sql .= " WHERE perfil != 'super_admin'";
        }
        if ($ordem !== '') {
            $sql .= " ORDER BY {$ordem}";
        }
        $stmt = $this->bd->query($sql);
        return $stmt->fetchAll();
    }

    public function gerarSenhaAleatoria(int $tamanho = 10): string
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $senha = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $senha;
    }
}
