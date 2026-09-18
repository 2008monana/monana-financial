<?php
/**
 * Modelo de Perfil do Usuário
 */
class PerfilUsuario extends Model {
    protected $tabela = 'perfis_usuarios';
    protected $chavePrimaria = 'id';
    protected $preenchiveis = [
        'usuario_id', 'foto_url', 'foto_public_id', 'telefone', 
        'cargo', 'departamento', 'biografia'
    ];

    /**
     * Buscar perfil por usuário
     */
    public function buscarPorUsuario($usuarioId) {
        $sql = "SELECT * FROM {$this->tabela} WHERE usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuarioId]);
        return $stmt->fetch();
    }

    /**
     * Criar ou atualizar perfil
     */
    public function salvar($usuarioId, $dados) {
        $perfil = $this->buscarPorUsuario($usuarioId);
        
        if ($perfil) {
            return $this->atualizar($perfil['id'], $dados);
        } else {
            $dados['usuario_id'] = $usuarioId;
            return $this->criar($dados);
        }
    }

    /**
     * Atualizar foto
     */
    public function atualizarFoto($usuarioId, $url, $publicId = null) {
        return $this->salvar($usuarioId, [
            'foto_url' => $url,
            'foto_public_id' => $publicId
        ]);
    }
}