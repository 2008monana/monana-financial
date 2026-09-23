<?php
/**
 * Model de Métodos de Pagamento
 * Gerencia operações relacionadas a métodos de pagamento
 */

class MetodoPagamento {
    private PDO $db;

    public function __construct() {
        $this->db = Database::obterLigacao();

        // Auto-migração: garante colunas opcionais (ordem, descricao, cor, categoria)
        if (file_exists(CAMINHO_RAIZ . '/database/auto_migracoes.php')) {
            require_once CAMINHO_RAIZ . '/database/auto_migracoes.php';
            executarAutoMigracoes($this->db);
        }
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

        // Ordena por nome (coluna garantida); a coluna ordem é opcional e pode não existir
        $sql .= " ORDER BY nome ASC";

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
     * Verifica se uma coluna existe na tabela (cache por pedido)
     */
    private function temColuna(string $coluna): bool {
        static $cols = null;
        if ($cols === null) {
            $cols = [];
            foreach ($this->db->query("SHOW COLUMNS FROM metodos_pagamento") as $c) {
                $cols[$c['Field']] = true;
            }
        }
        return isset($cols[$coluna]);
    }

    /**
     * Criar novo método de pagamento
     */
    public function criar(array $dados): bool {
        $campos = ['nome' => $dados['nome'], 'empresa_id' => $dados['empresa_id'] ?? null];
        $extras = [
            'descricao' => $dados['descricao'] ?? null,
            'ordem'     => $dados['ordem'] ?? 0,
            'icone'     => $dados['icone'] ?? null,
            'cor'       => $dados['cor'] ?? null,
        ];
        foreach ($extras as $campo => $valor) {
            if ($this->temColuna($campo)) {
                $campos[$campo] = $valor;
            }
        }
        if ($this->temColuna('ativo')) {
            $campos['ativo'] = $dados['ativo'] ?? 1;
        }

        $colunas = implode(', ', array_keys($campos));
        $marcadores = implode(', ', array_map(fn($c) => ":$c", array_keys($campos)));

        $stmt = $this->db->prepare("INSERT INTO metodos_pagamento ($colunas) VALUES ($marcadores)");
        return $stmt->execute($campos);
    }

    /**
     * Atualizar método de pagamento
     */
    public function atualizar(int $id, array $dados): bool {
        $set = ['nome' => $dados['nome']];
        $extras = [
            'descricao' => $dados['descricao'] ?? null,
            'ordem'     => $dados['ordem'] ?? 0,
            'icone'     => $dados['icone'] ?? null,
            'cor'       => $dados['cor'] ?? null,
        ];
        foreach ($extras as $campo => $valor) {
            if ($this->temColuna($campo)) {
                $set[$campo] = $valor;
            }
        }
        if ($this->temColuna('ativo')) {
            $set['ativo'] = $dados['ativo'] ?? 1;
        }

        $sql = "UPDATE metodos_pagamento SET " . implode(', ', array_map(fn($c) => "$c = :$c", array_keys($set))) . " WHERE id = :id";
        $set['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($set);
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
        $ordem = $this->temColuna('ordem') ? "ordem ASC, " : "";
        $stmt = $this->db->prepare("SELECT * FROM metodos_pagamento WHERE empresa_id IS NULL AND ativo = 1 ORDER BY {$ordem}nome ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
