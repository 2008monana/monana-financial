<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model UsuarioFilial
 * Associação entre um utilizador e as filiais a que tem acesso.
 */
class UsuarioFilial extends Model
{
    protected string $tabela = 'usuario_filiais';

    public function idsPorUsuario(int $usuarioId): array
    {
        $stmt = $this->bd->prepare("SELECT filial_id FROM usuario_filiais WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuarioId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Substitui todas as filiais associadas a um utilizador pela nova lista de IDs */
    public function substituir(int $usuarioId, array $filialIds): void
    {
        $stmt = $this->bd->prepare("DELETE FROM usuario_filiais WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuarioId]);

        if (empty($filialIds)) {
            return;
        }

        $sql = "INSERT INTO usuario_filiais (usuario_id, filial_id) VALUES (:usuario_id, :filial_id)";
        $stmt = $this->bd->prepare($sql);
        foreach ($filialIds as $filialId) {
            $stmt->execute(['usuario_id' => $usuarioId, 'filial_id' => (int) $filialId]);
        }
    }
}
