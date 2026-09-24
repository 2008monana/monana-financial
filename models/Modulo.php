<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

class Modulo extends Model
{
    protected string $tabela = 'modulos';

    public function __construct()
    {
        parent::__construct();
        $this->garantirTabelas();
    }

    /**
     * Evita o erro fatal "tabela não existe" em instalações que ainda não
     * executaram database/migracao_modulos_backup.sql.
     */
    private function garantirTabelas(): void
    {
        $this->bd->exec(
            "CREATE TABLE IF NOT EXISTS modulos (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(60) NOT NULL UNIQUE,
                descricao VARCHAR(150) NULL,
                icone VARCHAR(40) NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1,
                ordem INT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE=InnoDB"
        );
        $this->bd->exec(
            "CREATE TABLE IF NOT EXISTS usuario_modulo_permissoes (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT UNSIGNED NOT NULL,
                modulo_id INT UNSIGNED NOT NULL,
                criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_ump_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                CONSTRAINT fk_ump_modulo FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE,
                UNIQUE KEY uk_usuario_modulo (usuario_id, modulo_id)
            ) ENGINE=InnoDB"
        );

        // O catálogo representa páginas navegáveis, não permissões técnicas.
        // INSERT IGNORE também acrescenta páginas novas a instalações já existentes.
        $stmt = $this->bd->prepare(
            'INSERT IGNORE INTO modulos (nome, descricao, icone, ordem) VALUES (:nome, :descricao, :icone, :ordem)'
        );
        foreach (self::catalogo() as [$nome, $descricao, $icone, $ordem]) {
            $stmt->execute(['nome' => $nome, 'descricao' => $descricao, 'icone' => $icone, 'ordem' => $ordem]);
        }
    }

    /** Páginas que podem ser atribuídas aos utilizadores no formulário de acessos. */
    public static function catalogo(): array
    {
        return [
            ['dashboard', 'Dashboard', 'fa-house', 1],
            ['movimentos', 'Movimentos', 'fa-list-ul', 2],
            ['relatorios', 'Relatórios', 'fa-chart-column', 3],
            ['planilha', 'Planilha', 'fa-table', 4],
            ['categorias', 'Categorias', 'fa-tags', 5],
            ['empresas', 'Empresas', 'fa-building', 6],
            ['filiais', 'Filiais', 'fa-code-branch', 7],
            ['usuarios', 'Utilizadores', 'fa-users', 8],
            ['fecho_diario', 'Fecho Diário', 'fa-calendar-check', 9],
            ['backups', 'Backups', 'fa-database', 10],
            ['funcionarios', 'Funcionários', 'fa-user-tie', 11],
            ['metodos_pagamento', 'Métodos de Pagamento', 'fa-money-bill-wave', 16],
            ['logs', 'Logs de Auditoria', 'fa-clipboard-list', 12],
            ['configuracoes', 'Configurações', 'fa-gear', 13],
            ['perfil', 'Meu Perfil', 'fa-user-cog', 14],
            ['notificacoes', 'Notificações', 'fa-bell', 15],
        ];
    }

    public function todosAtivos(): array
    {
        $stmt = $this->bd->query("SELECT * FROM modulos WHERE ativo = 1 ORDER BY ordem, id");
        return $stmt->fetchAll();
    }

    public function porUsuario(int $usuarioId): array
    {
        $sql = "SELECT m.*, 
                       CASE WHEN ump.modulo_id IS NOT NULL THEN 1 ELSE 0 END as permitido
                FROM modulos m
                LEFT JOIN usuario_modulo_permissoes ump 
                    ON ump.modulo_id = m.id AND ump.usuario_id = :usuario_id
                WHERE m.ativo = 1
                ORDER BY m.ordem, m.id";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function salvarPermissoes(int $usuarioId, array $modulosIds): void
    {
        // Remover permissões existentes
        $stmt = $this->bd->prepare("DELETE FROM usuario_modulo_permissoes WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $usuarioId]);

        // Inserir novas permissões
        if (empty($modulosIds)) {
            return;
        }

        $sql = "INSERT INTO usuario_modulo_permissoes (usuario_id, modulo_id) VALUES (:usuario_id, :modulo_id)";
        $stmt = $this->bd->prepare($sql);
        foreach ($modulosIds as $moduloId) {
            $stmt->execute([
                'usuario_id' => $usuarioId,
                'modulo_id' => (int) $moduloId
            ]);
        }
    }

    public function temPermissao(int $usuarioId, string $moduloNome): bool
    {
        $sql = "SELECT COUNT(*) FROM usuario_modulo_permissoes ump
                INNER JOIN modulos m ON m.id = ump.modulo_id
                WHERE ump.usuario_id = :usuario_id AND m.nome = :modulo_nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'modulo_nome' => $moduloNome
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Obter permissões de um utilizador (para exibir no perfil)
     */
    public function getPermissoesUsuario(int $usuarioId): array
    {
        $sql = "SELECT m.nome, m.icone, m.descricao,
                       CASE WHEN ump.modulo_id IS NOT NULL THEN 1 ELSE 0 END as permitido
                FROM modulos m
                LEFT JOIN usuario_modulo_permissoes ump 
                    ON ump.modulo_id = m.id AND ump.usuario_id = :usuario_id
                WHERE m.ativo = 1
                ORDER BY m.ordem, m.id";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }
}