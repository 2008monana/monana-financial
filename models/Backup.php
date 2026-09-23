<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Backup
 * Histórico de cópias de segurança (manuais e automáticas).
 * empresa_id = NULL  -> backup global (apenas visível ao Super Admin)
 * empresa_id = X     -> backup filtrado, apenas dessa empresa
 */
class Backup extends Model
{
    protected string $tabela = 'backups';

    public function __construct()
    {
        parent::__construct();
        $this->garantirTabela();
    }

    /** Evita o erro fatal "tabela não existe" em instalações que ainda não executaram a migração. */
    private function garantirTabela(): void
    {
        $this->bd->exec(
            "CREATE TABLE IF NOT EXISTS backups (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                empresa_id INT UNSIGNED NULL,
                usuario_id INT UNSIGNED NULL,
                tipo ENUM('manual','automatico') NOT NULL DEFAULT 'manual',
                escopo ENUM('global','empresa') NOT NULL DEFAULT 'empresa',
                nome_arquivo VARCHAR(255) NOT NULL,
                caminho_relativo VARCHAR(500) NOT NULL,
                tamanho_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
                status ENUM('concluido','falhou') NOT NULL DEFAULT 'concluido',
                mensagem_erro VARCHAR(500) NULL,
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_backup_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
                CONSTRAINT fk_backup_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
                INDEX idx_backups_empresa_data (empresa_id, criado_em)
            ) ENGINE=InnoDB"
        );
    }

    /**
     * Lista o histórico visível para o âmbito indicado.
     * $empresaId === null  -> Super Admin: vê tudo (globais + de todas as empresas)
     * $empresaId === int   -> Admin Empresa: vê apenas os backups da sua empresa
     */
    public function listar(?int $empresaId, int $limite = 100): array
    {
        if ($empresaId === null) {
            $sql = "SELECT b.*, e.nome AS empresa_nome, u.nome AS usuario_nome
                    FROM backups b
                    LEFT JOIN empresas e ON e.id = b.empresa_id
                    LEFT JOIN usuarios u ON u.id = b.usuario_id
                    ORDER BY b.criado_em DESC
                    LIMIT :limite";
            $stmt = $this->bd->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $sql = "SELECT b.*, e.nome AS empresa_nome, u.nome AS usuario_nome
                FROM backups b
                LEFT JOIN empresas e ON e.id = b.empresa_id
                LEFT JOIN usuarios u ON u.id = b.usuario_id
                WHERE b.empresa_id = :empresa_id
                ORDER BY b.criado_em DESC
                LIMIT :limite";
        $stmt = $this->bd->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Devolve o registo apenas se estiver dentro do âmbito permitido (segurança). */
    public function encontrarVisivel(int $id, ?int $empresaId): array|false
    {
        $sql = "SELECT * FROM backups WHERE id = :id";
        $params = ['id' => $id];
        if ($empresaId !== null) {
            $sql .= " AND empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /** Data/hora do último backup concluído com sucesso para um âmbito (para o agendamento automático). */
    public function ultimoConcluidoEm(?int $empresaId, string $tipo = null): ?string
    {
        $sql = "SELECT criado_em FROM backups WHERE status = 'concluido' AND empresa_id ";
        $sql .= $empresaId === null ? 'IS NULL' : '= :empresa_id';
        if ($tipo) {
            $sql .= " AND tipo = :tipo";
        }
        $sql .= " ORDER BY criado_em DESC LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $params = [];
        if ($empresaId !== null) $params['empresa_id'] = $empresaId;
        if ($tipo) $params['tipo'] = $tipo;
        $stmt->execute($params);
        $valor = $stmt->fetchColumn();
        return $valor ?: null;
    }
}
