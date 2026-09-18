<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model Usuario
 * Representa a tabela `usuarios`.
 */
class Usuario extends Model
{
    protected string $tabela = 'usuarios';

    public function encontrarPorEmail(string $email): array|false
    {
        return $this->buscarUmPor('email', $email);
    }

    public function verificarSenha(string $senhaDigitada, string $hashArmazenado): bool
    {
        return password_verify($senhaDigitada, $hashArmazenado);
    }

    public function criarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_BCRYPT);
    }

    public function atualizarUltimoLogin(int $usuarioId): bool
    {
        return $this->atualizar($usuarioId, ['ultimo_login' => date('Y-m-d H:i:s')]);
    }

    public function porEmpresa(int $empresaId): array
    {
        $stmt = $this->bd->prepare("SELECT * FROM usuarios WHERE empresa_id = :empresa_id ORDER BY nome");
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function gerarSenhaAleatoria(int $tamanho = 10): string
    {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $senha = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        return $senha;
    }
}
