<?php
require_once CAMINHO_RAIZ . '/models/Notificacao.php';

/**
 * Cria notificações destinadas aos perfis correctos sem expor dados entre empresas.
 */
class NotificacaoHelper
{
    public static function paraUsuario(int $usuarioId, string $tipo, string $titulo, string $mensagem, ?string $link = null): int
    {
        return (new Notificacao())->criar($usuarioId, $tipo, $titulo, $mensagem, $link);
    }

    public static function paraAdministradoresEmpresa(int $empresaId, string $tipo, string $titulo, string $mensagem, ?string $link = null): void
    {
        $bd = Database::obterLigacao();
        $stmt = $bd->prepare("SELECT id FROM usuarios WHERE empresa_id = :empresa_id AND ativo = 1 AND perfil = 'admin_empresa'");
        $stmt->execute(['empresa_id' => $empresaId]);

        foreach ($stmt->fetchAll() as $usuario) {
            self::paraUsuario((int) $usuario['id'], $tipo, $titulo, $mensagem, $link);
        }
    }

    public static function paraSuperAdministradores(string $tipo, string $titulo, string $mensagem, ?string $link = null): void
    {
        $stmt = Database::obterLigacao()->query("SELECT id FROM usuarios WHERE ativo = 1 AND perfil = 'super_admin'");
        foreach ($stmt->fetchAll() as $usuario) {
            self::paraUsuario((int) $usuario['id'], $tipo, $titulo, $mensagem, $link);
        }
    }
}
