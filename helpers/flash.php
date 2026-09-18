<?php
/**
 * Mensagens "flash" — persistem por um único redireccionamento via sessão.
 */

if (!function_exists('definirFlash')) {
    function definirFlash(string $tipo, string $mensagem): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }
}

if (!function_exists('consumirFlash')) {
    function consumirFlash(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
}
