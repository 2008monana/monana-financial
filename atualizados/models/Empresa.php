<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

class Empresa extends Model
{
    protected string $tabela = 'empresas';

    /**
     * Busca todas as empresas ativas
     */
    public function ativas(): array
    {
        $stmt = $this->bd->query("SELECT * FROM empresas WHERE ativa = 1 ORDER BY nome");
        return $stmt->fetchAll();
    }

    /**
     * Busca uma empresa por ID
     */
    public function encontrarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM empresas WHERE id = :id LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Lista todas as empresas (ativas e inativas), ordenadas por nome.
     * Compatibilidade com controladores que chamam listar().
     */
    public function listar(): array
    {
        $stmt = $this->bd->query("SELECT * FROM empresas ORDER BY nome");
        return $stmt->fetchAll();
    }
}