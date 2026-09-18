<?php
/**
 * helpers/notificacoes.php
 * Funções auxiliares para criar notificações
 */

require_once CAMINHO_RAIZ . '/models/Notificacao.php';

if (!function_exists('criarNotificacao')) {
    function criarNotificacao(int $usuarioId, string $tipo, string $titulo, string $mensagem, ?string $link = null): int
    {
        $notificacaoModel = new Notificacao();
        return $notificacaoModel->criar($usuarioId, $tipo, $titulo, $mensagem, $link);
    }
}

if (!function_exists('criarNotificacaoParaTodos')) {
    function criarNotificacaoParaTodos(string $tipo, string $titulo, string $mensagem, ?string $link = null): void
    {
        require_once CAMINHO_RAIZ . '/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->todos();
        
        foreach ($usuarios as $usuario) {
            criarNotificacao((int) $usuario['id'], $tipo, $titulo, $mensagem, $link);
        }
    }
}