<?php
/**
 * Classe Model (base)
 * Fornece operações CRUD genéricas para as entidades do sistema.
 */

abstract class Model
{
    protected PDO $bd;
    protected string $tabela;
    protected string $chavePrimaria = 'id';

    public function __construct()
    {
        $this->bd = Database::obterLigacao();
    }

    public function encontrarPorId(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$this->chavePrimaria} = :id LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function todos(string $ordem = ''): array
    {
        $sql = "SELECT * FROM {$this->tabela}";
        if ($ordem !== '') {
            $sql .= " ORDER BY {$ordem}";
        }
        $stmt = $this->bd->query($sql);
        return $stmt->fetchAll();
    }

    public function buscarPor(string $coluna, mixed $valor): array
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$coluna} = :valor";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['valor' => $valor]);
        return $stmt->fetchAll();
    }

    public function buscarUmPor(string $coluna, mixed $valor): array|false
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$coluna} = :valor LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['valor' => $valor]);
        return $stmt->fetch();
    }

    public function inserir(array $dados): int
    {
        $colunas = implode(', ', array_keys($dados));
        $marcadores = implode(', ', array_map(fn($c) => ":{$c}", array_keys($dados)));

        $sql = "INSERT INTO {$this->tabela} ({$colunas}) VALUES ({$marcadores})";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($dados);

        return (int) $this->bd->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $sets = implode(', ', array_map(fn($c) => "{$c} = :{$c}", array_keys($dados)));
        $sql = "UPDATE {$this->tabela} SET {$sets} WHERE {$this->chavePrimaria} = :id";

        $dados['id'] = $id;
        $stmt = $this->bd->prepare($sql);
        return $stmt->execute($dados);
    }

    public function eliminar(int $id): bool
    {
        $sql = "DELETE FROM {$this->tabela} WHERE {$this->chavePrimaria} = :id";
        $stmt = $this->bd->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
