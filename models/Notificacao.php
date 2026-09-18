<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Notificacao
 */
class Notificacao extends Model
{
    protected string $tabela = 'notificacoes';

    public function recentesPorUsuario(int $usuarioId, int $limite = 5): array
    {
        $sql = "SELECT * FROM notificacoes
                WHERE usuario_id = :usuario_id
                ORDER BY criado_em DESC
                LIMIT :limite";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarNaoLidas(int $usuarioId): int
    {
        $sql = "SELECT COUNT(*) FROM notificacoes WHERE usuario_id = :usuario_id AND lida = 0";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    public function marcarComoLida(int $id, int $usuarioId): bool
    {
        $sql = "UPDATE notificacoes SET lida = 1 WHERE id = :id AND usuario_id = :usuario_id";
        $stmt = $this->bd->prepare($sql);
        return $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);
    }

    public function criar(int $usuarioId, string $tipo, string $titulo, string $mensagem, ?string $link = null): int
    {
        return $this->inserir([
            'usuario_id' => $usuarioId,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensagem'   => $mensagem,
            'link'       => $link,
        ]);
    }
}
