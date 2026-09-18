<?php
/**
 * Modelo de Redefinição de Senha
 */
class RedefinicaoSenha extends Model {
    protected $tabela = 'redefinicoes_senha';
    protected $chavePrimaria = 'id';
    protected $preenchiveis = [
        'usuario_id', 'token', 'expira_em', 'usado'
    ];

    /**
     * Criar token de redefinição
     */
    public function criarToken($usuarioId) {
        $token = SegurancaHelper::gerarToken();
        $expiraEm = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Invalidar tokens anteriores não usados
        $sql = "UPDATE {$this->tabela} SET usado = TRUE 
                WHERE usuario_id = ? AND usado = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);

        // Criar novo token
        return $this->criar([
            'usuario_id' => $usuarioId,
            'token' => $token,
            'expira_em' => $expiraEm,
            'usado' => false
        ]);
    }

    /**
     * Buscar token válido
     */
    public function buscarTokenValido($token) {
        $sql = "SELECT * FROM {$this->tabela} 
                WHERE token = ? AND usado = FALSE AND expira_em > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Marcar token como usado
     */
    public function marcarComoUsado($id) {
        return $this->atualizar($id, ['usado' => true]);
    }

    /**
     * Limpar tokens expirados
     */
    public function limparExpirados() {
        $sql = "DELETE FROM {$this->tabela} WHERE expira_em < NOW() OR usado = TRUE";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }
}