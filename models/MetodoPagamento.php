<?php
/**
 * Model de Métodos de Pagamento
 * Gerencia operações relacionadas a métodos de pagamento
 */

class MetodoPagamento {
    private PDO $db;

    public function __construct() {
        $this->db = Database::obterLigacao();
    }

    /**
     * Listar métodos de pagamento
     */
    public function listar(?int $empresa_id = null, bool $apenas_ativos = true): array {
        $sql = "SELECT * FROM metodos_pagamento WHERE 1=1";
        $params = [];

        if ($empresa_id !== null) {
            $sql .= " AND (empresa_id = :empresa_id OR empresa_id IS NULL)";
            $params['empresa_id'] = $empresa_id;
        }

        if ($apenas_ativos) {
            $sql .= " AND ativo = 1";
        }

        $sql .= " ORDER BY ordem ASC, nome ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar método por ID
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM metodos_pagamento WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Criar novo método de pagamento
     */
    public function criar(array $dados): bool {
        $sql = "INSERT INTO metodos_pagamento 
                (nome, descricao, empresa_id, ativo, ordem, icone) 
                VALUES (:nome, :descricao, :empresa_id, :ativo, :ordem, :icone)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'empresa_id' => $dados['empresa_id'] ?? null,
            'ativo' => $dados['ativo'] ?? 1,
            'ordem' => $dados['ordem'] ?? 0,
            'icone' => $dados['icone'] ?? null
        ]);
    }

    /**
     * Atualizar método de pagamento
     */
    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE metodos_pagamento SET 
                nome = :nome,
                descricao = :descricao,
                ativo = :ativo,
                ordem = :ordem,
                icone = :icone
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'ativo' => $dados['ativo'] ?? 1,
            'ordem' => $dados['ordem'] ?? 0,
            'icone' => $dados['icone'] ?? null
        ]);
    }

    /**
     * Excluir método de pagamento
     */
    public function excluir(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM metodos_pagamento WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verificar se método está em uso
     */
    public function estaEmUso(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM transacoes WHERE metodo_pagamento = (SELECT nome FROM metodos_pagamento WHERE id = :id)");
        $stmt->execute(['id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($resultado['total'] ?? 0) > 0;
    }

    /**
     * Obter métodos padrão do sistema
     */
    public function obterPadroes(): array {
        $stmt = $this->db->prepare("SELECT * FROM metodos_pagamento WHERE empresa_id IS NULL AND ativo = 1 ORDER BY ordem ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
