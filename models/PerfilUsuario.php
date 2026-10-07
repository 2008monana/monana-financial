<?php
/**
 * Modelo de Perfil do Usuário
 * Tabela: perfis_usuarios (foto via Cloudinary, contactos, cargo...)
 */

require_once CAMINHO_RAIZ . '/core/Model.php';

class PerfilUsuario extends Model {
    protected string $tabela = 'perfis_usuarios';
    protected string $chavePrimaria = 'id';

    /** Colunas que podem ser gravadas a partir de dados do utilizador */
    private array $preenchiveis = [
        'usuario_id', 'foto_url', 'foto_public_id', 'telefone',
        'cargo', 'departamento', 'biografia'
    ];

    /**
     * Buscar perfil por usuário
     */
    public function buscarPorUsuario($usuarioId) {
        $sql = "SELECT * FROM {$this->tabela} WHERE usuario_id = :usuario_id LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['usuario_id' => (int) $usuarioId]);
        return $stmt->fetch();
    }

    /**
     * Criar ou atualizar perfil
     */
    public function salvar($usuarioId, $dados) {
        // Filtrar apenas colunas permitidas
        $dados = array_intersect_key($dados, array_flip($this->preenchiveis));

        $perfil = $this->buscarPorUsuario($usuarioId);

        if ($perfil) {
            unset($dados['usuario_id']);
            return $this->atualizar((int) $perfil['id'], $dados);
        } else {
            $dados['usuario_id'] = (int) $usuarioId;
            return $this->inserir($dados);
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
