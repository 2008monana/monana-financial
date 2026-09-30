<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Planos de assinatura (mensal, trimestral, ...).
 * Preços e durações vivem na base de dados, editáveis pelo Super Admin;
 * nunca devem ser hardcodados fora da migração inicial.
 */
class Plano extends Model
{
    protected string $tabela = 'planos';

    /** Todos os planos (activos e inactivos), ordenados. */
    public function todosOrdenados(): array
    {
        $stmt = $this->bd->query("SELECT * FROM planos ORDER BY ordem, id");
        return $stmt->fetchAll();
    }

    /** Apenas planos activos. */
    public function activos(): array
    {
        $stmt = $this->bd->query("SELECT * FROM planos WHERE ativo = 1 ORDER BY ordem, id");
        return $stmt->fetchAll();
    }

    public function porCodigo(string $codigo): array|false
    {
        $stmt = $this->bd->prepare("SELECT * FROM planos WHERE codigo = :codigo LIMIT 1");
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch();
    }

    /** ID do plano gratuito (usado pela migração e ao criar empresas sem plano indicado). */
    public function idGratuito(): ?int
    {
        $stmt = $this->bd->prepare("SELECT id FROM planos WHERE codigo = 'gratuito' LIMIT 1");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    /**
     * Edita preço/duração/estado de um plano. Não altera assinaturas já
     * existentes (cada assinatura guarda o valor_acordado no momento).
     */
    public function guardar(int $id, float $preco, ?int $duracaoDias, bool $ativo): bool
    {
        $stmt = $this->bd->prepare(
            "UPDATE planos SET preco = :preco, duracao_dias = :duracao, ativo = :ativo WHERE id = :id"
        );
        return $stmt->execute([
            'preco'   => max(0, $preco),
            'duracao' => $duracaoDias,
            'ativo'   => $ativo ? 1 : 0,
            'id'      => $id,
        ]);
    }
}
