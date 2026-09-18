<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

class Modulo extends Model
{
    protected string $tabela = 'modulos';

    public function todosAtivos(): array
    {
        $stmt = $this->bd->query("SELECT * FROM modulos WHERE ativo = 1 ORDER BY id");
        return $stmt->fetchAll();
    }

    public function porUsuario(int $usuarioId): array
    {
        $sql = "SELECT m.*, 
                       CASE WHEN ump.modulo_id IS NOT NULL THEN 1 ELSE 0 END as permitido
                FROM modulos m
                LEFT JOIN usuario_modulo_permissoes ump 
                    ON ump.modulo_id = m.id AND ump.usuario_id = :usuario_id
                WHERE m.ativo = 1
                ORDER BY m.id";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function salvarPermissoes(int $usuarioId, array $modulosIds): void
    {
        // Remover permissões existentes
        $stmt = $this->bd->prepare("DELETE FROM usuario_modulo_permissoes WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuarioId]);

        // Inserir novas permissões
        if (empty($modulosIds)) {
            return;
        }

        $sql = "INSERT INTO usuario_modulo_permissoes (usuario_id, modulo_id) VALUES (:usuario_id, :modulo_id)";
        $stmt = $this->bd->prepare($sql);
        foreach ($modulosIds as $moduloId) {
            $stmt->execute([
                'usuario_id' => $usuarioId,
                'modulo_id' => (int) $moduloId
            ]);
        }
    }

    public function temPermissao(int $usuarioId, string $moduloNome): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario_modulo_permissoes ump
                INNER JOIN modulos m ON m.id = ump.modulo_id
                WHERE ump.usuario_id = :usuario_id AND m.nome = :modulo_nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'modulo_nome' => $moduloNome
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Obter permissões de um utilizador (para exibir no perfil)
     */
    public function getPermissoesUsuario(int $usuarioId): array
    {
        $sql = "SELECT m.nome, m.icone, m.descricao,
                       CASE WHEN ump.modulo_id IS NOT NULL THEN 1 ELSE 0 END as permitido
                FROM modulos m
                LEFT JOIN usuario_modulo_permissoes ump 
                    ON ump.modulo_id = m.id AND ump.usuario_id = :usuario_id
                WHERE m.ativo = 1
                ORDER BY m.id";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }
}