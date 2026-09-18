<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Filial
 */
class Filial extends Model
{
    protected string $tabela = 'filiais';

    /**
     * Buscar filiais por empresa
     */
    public function porEmpresa(int $empresaId): array
    {
        $stmt = $this->bd->prepare("SELECT * FROM filiais WHERE empresa_id = :empresa_id AND ativa = 1 ORDER BY nome");
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar todas as filiais de uma empresa (ativas e inativas) — usado na gestão
     */
    public function porEmpresaTodas(int $empresaId): array
    {
        $stmt = $this->bd->prepare("SELECT * FROM filiais WHERE empresa_id = :empresa_id ORDER BY nome");
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar todas as filiais ativas (Super Admin)
     */
    public function todas(): array
    {
        $stmt = $this->bd->query("SELECT f.*, e.nome as empresa_nome 
                                  FROM filiais f 
                                  LEFT JOIN empresas e ON f.empresa_id = e.id 
                                  WHERE f.ativa = 1 
                                  ORDER BY e.nome, f.nome");
        return $stmt->fetchAll();
    }

    /**
     * Filiais a que um utilizador tem acesso (via usuario_filiais)
     */
    public function porUsuario(int $usuarioId): array
    {
        $sql = "SELECT f.*, e.nome as empresa_nome 
                FROM filiais f
                INNER JOIN usuario_filiais uf ON uf.filial_id = f.id
                LEFT JOIN empresas e ON f.empresa_id = e.id
                WHERE uf.usuario_id = :usuario_id AND f.ativa = 1
                ORDER BY f.nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }
}