<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Configurações globais (empresa_id NULL) e próprias de cada empresa.
 */
class Configuracao extends Model
{
    protected string $tabela = 'configuracoes';

    public function __construct()
    {
        parent::__construct();
        $this->garantirTabela();
    }

    /**
     * Evita uma página fatal em instalações existentes que ainda não
     * executaram a migração dos módulos administrativos.
     */
    private function garantirTabela(): void
    {
        $this->bd->exec(
            "CREATE TABLE IF NOT EXISTS configuracoes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                empresa_id INT UNSIGNED NULL,
                chave VARCHAR(100) NOT NULL,
                valor TEXT NULL,
                tipo ENUM('texto','numero','booleano','json','secreto') NOT NULL DEFAULT 'texto',
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_configuracoes_empresa
                    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
                UNIQUE KEY uk_configuracao_escopo (empresa_id, chave)
            ) ENGINE=InnoDB"
        );
    }

    public function obterTodas(?int $empresaId): array
    {
        $sql = 'SELECT chave, valor, tipo FROM configuracoes WHERE empresa_id ';
        $sql .= $empresaId === null ? 'IS NULL' : '= :empresa_id';

        $stmt = $this->bd->prepare($sql);
        $stmt->execute($empresaId === null ? [] : ['empresa_id' => $empresaId]);

        $configuracoes = [];
        foreach ($stmt->fetchAll() as $linha) {
            $configuracoes[$linha['chave']] = $linha['valor'];
        }

        return $configuracoes;
    }

    public function guardar(?int $empresaId, string $chave, string $valor, string $tipo = 'texto'): void
    {
        // Em MySQL, uma chave UNIQUE permite vários NULL. Por isso, o
        // escopo global exige uma procura explícita antes de gravar.
        $sql = 'SELECT id FROM configuracoes WHERE chave = :chave AND empresa_id ';
        $sql .= $empresaId === null ? 'IS NULL LIMIT 1' : '= :empresa_id LIMIT 1';
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($empresaId === null ? ['chave' => $chave] : [
            'chave' => $chave,
            'empresa_id' => $empresaId,
        ]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            $stmt = $this->bd->prepare(
                'UPDATE configuracoes SET valor = :valor, tipo = :tipo WHERE id = :id'
            );
            $stmt->execute(['valor' => $valor, 'tipo' => $tipo, 'id' => $id]);
            return;
        }

        $stmt = $this->bd->prepare(
            'INSERT INTO configuracoes (empresa_id, chave, valor, tipo)
             VALUES (:empresa_id, :chave, :valor, :tipo)'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'chave' => $chave,
            'valor' => $valor,
            'tipo' => $tipo,
        ]);
    }

}
