<?php
/**
 * Modelo de Log de Auditoria
 */
class LogAuditoria extends Model {
    protected $tabela = 'logs_auditoria';
    protected $chavePrimaria = 'id';
    protected $preenchiveis = [
        'usuario_id', 'acao', 'tabela', 'registro_id', 
        'dados_antigos', 'dados_novos', 'ip', 'user_agent'
    ];

    /**
     * Registrar ação
     */
    public function registrar($usuarioId, $acao, $tabela = null, $registroId = null, $dadosAntigos = null, $dadosNovos = null) {
        return $this->criar([
            'usuario_id' => $usuarioId,
            'acao' => $acao,
            'tabela' => $tabela,
            'registro_id' => $registroId,
            'dados_antigos' => $dadosAntigos ? json_encode($dadosAntigos) : null,
            'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Buscar logs por usuário
     */
    public function buscarPorUsuario($usuarioId, $limite = 50) {
        $sql = "SELECT * FROM {$this->tabela} 
                WHERE usuario_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId, $limite]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar logs recentes
     */
    public function buscarRecentes($limite = 100) {
        $sql = "SELECT la.*, u.nome as usuario_nome 
                FROM {$this->tabela} la
                LEFT JOIN usuarios u ON la.usuario_id = u.id
                ORDER BY la.created_at DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll();
    }

    /**
     * Limpar logs antigos
     */
    public function limparAntigos($dias = 90) {
        $sql = "DELETE FROM {$this->tabela} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$dias]);
    }
}