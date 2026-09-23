<?php
/**
 * Model de Funcionário
 * Gerencia operações CRUD de funcionários
 */
class Funcionario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::obterLigacao();
    }

    /**
     * Buscar todos os funcionários com filtros
     */
    public function listar(int $empresa_id, ?int $filial_id = null, string $busca = ''): array {
        $sql = "SELECT f.*, fi.nome as filial_nome, e.nome as empresa_nome 
                FROM funcionarios f
                INNER JOIN filiais fi ON f.filial_id = fi.id
                INNER JOIN empresas e ON fi.empresa_id = e.id
                WHERE fi.empresa_id = :empresa_id";
        
        if ($filial_id) {
            $sql .= " AND f.filial_id = :filial_id";
        }
        
        if (!empty($busca)) {
            $sql .= " AND (f.nome LIKE :busca OR f.cargo LIKE :busca OR f.email LIKE :busca)";
        }
        
        $sql .= " ORDER BY f.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
        
        if ($filial_id) {
            $stmt->bindValue(':filial_id', $filial_id, PDO::PARAM_INT);
        }
        
        if (!empty($busca)) {
            $stmt->bindValue(':busca', "%{$busca}%", PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar funcionário por ID
     */
    public function buscarPorId(int $id): ?array {
        $sql = "SELECT f.*, fi.nome as filial_nome, fi.empresa_id, e.nome as empresa_nome 
                FROM funcionarios f
                INNER JOIN filiais fi ON f.filial_id = fi.id
                INNER JOIN empresas e ON fi.empresa_id = e.id
                WHERE f.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    /**
     * Cadastrar novo funcionário
     */
    public function criar(array $dados): int|false {
        $sql = "INSERT INTO funcionarios (
                    empresa_id, filial_id, nome, data_nascimento, genero, 
                    nacionalidade, bi_passaporte, endereco, telefone, email, 
                    cargo, departamento, data_admissao, salario_base, 
                    foto, estado, criado_em
                ) VALUES (
                    :empresa_id, :filial_id, :nome, :data_nascimento, :genero,
                    :nacionalidade, :bi_passaporte, :endereco, :telefone, :email,
                    :cargo, :departamento, :data_admissao, :salario_base,
                    :foto, :estado, NOW()
                )";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':empresa_id', $dados['empresa_id'], PDO::PARAM_INT);
        $stmt->bindValue(':filial_id', $dados['filial_id'], PDO::PARAM_INT);
        $stmt->bindValue(':nome', $dados['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':data_nascimento', $dados['data_nascimento'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':genero', $dados['genero'] ?? 'M', PDO::PARAM_STR);
        $stmt->bindValue(':nacionalidade', $dados['nacionalidade'] ?? 'Angolana', PDO::PARAM_STR);
        $stmt->bindValue(':bi_passaporte', $dados['bi_passaporte'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':endereco', $dados['endereco'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $dados['telefone'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':email', $dados['email'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':cargo', $dados['cargo'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':departamento', $dados['departamento'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':data_admissao', $dados['data_admissao'] ?? date('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(':salario_base', $dados['salario_base'] ?? 0, PDO::PARAM_STR);
        $stmt->bindValue(':foto', $dados['foto'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $dados['estado'] ?? 'activo', PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Atualizar funcionário
     */
    public function atualizar(int $id, array $dados): bool {
        $sql = "UPDATE funcionarios SET
                    filial_id = :filial_id,
                    nome = :nome,
                    data_nascimento = :data_nascimento,
                    genero = :genero,
                    nacionalidade = :nacionalidade,
                    bi_passaporte = :bi_passaporte,
                    endereco = :endereco,
                    telefone = :telefone,
                    email = :email,
                    cargo = :cargo,
                    departamento = :departamento,
                    data_admissao = :data_admissao,
                    salario_base = :salario_base,
                    foto = :foto,
                    estado = :estado,
                    atualizado_em = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':filial_id', $dados['filial_id'], PDO::PARAM_INT);
        $stmt->bindValue(':nome', $dados['nome'], PDO::PARAM_STR);
        $stmt->bindValue(':data_nascimento', $dados['data_nascimento'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':genero', $dados['genero'] ?? 'M', PDO::PARAM_STR);
        $stmt->bindValue(':nacionalidade', $dados['nacionalidade'] ?? 'Angolana', PDO::PARAM_STR);
        $stmt->bindValue(':bi_passaporte', $dados['bi_passaporte'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':endereco', $dados['endereco'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $dados['telefone'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':email', $dados['email'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':cargo', $dados['cargo'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':departamento', $dados['departamento'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':data_admissao', $dados['data_admissao'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':salario_base', $dados['salario_base'] ?? 0, PDO::PARAM_STR);
        $stmt->bindValue(':foto', $dados['foto'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $dados['estado'] ?? 'activo', PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Excluir funcionário
     */
    public function excluir(int $id): bool {
        $sql = "DELETE FROM funcionarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Buscar filiais de uma empresa
     */
    public function buscarFiliais(int $empresa_id): array {
        $sql = "SELECT id, nome FROM filiais WHERE empresa_id = :empresa_id ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar total de funcionários
     */
    public function contar(int $empresa_id, ?int $filial_id = null): int {
        $sql = "SELECT COUNT(*) as total 
                FROM funcionarios f
                INNER JOIN filiais fi ON f.filial_id = fi.id
                WHERE fi.empresa_id = :empresa_id";
        
        if ($filial_id) {
            $sql .= " AND f.filial_id = :filial_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresa_id, PDO::PARAM_INT);
        
        if ($filial_id) {
            $stmt->bindValue(':filial_id', $filial_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($resultado['total'] ?? 0);
    }
}
