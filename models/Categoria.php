<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Categoria
 */
class Categoria extends Model
{
    protected string $tabela = 'categorias';

    /**
     * Buscar categorias por empresa (inclui as do sistema)
     */
    public function porEmpresa(int $empresaId): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND ativa = 1 
                ORDER BY tipo, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar categorias ativas do sistema (todas)
     */
    public function ativas(): array
    {
        $stmt = $this->bd->query("SELECT * FROM categorias WHERE ativa = 1 ORDER BY tipo, nome");
        return $stmt->fetchAll();
    }

    /**
     * Buscar categorias por tipo
     */
    public function porTipo(int $empresaId, string $tipo): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND tipo = :tipo 
                AND ativa = 1 
                ORDER BY nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'tipo' => $tipo]);
        return $stmt->fetchAll();
    }
}