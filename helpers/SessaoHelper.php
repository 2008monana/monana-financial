<?php
/**
 * Gerenciador de Sessão
 */
class SessaoHelper {
    
    public static function iniciar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NOME);
            session_start();
        }
    }

    public static function definir($chave, $valor) {
        $_SESSION[$chave] = $valor;
    }

    public static function obter($chave) {
        return $_SESSION[$chave] ?? null;
    }

    public static function existe($chave) {
        return isset($_SESSION[$chave]);
    }

    public static function remover($chave) {
        unset($_SESSION[$chave]);
    }

    public static function destruir() {
        session_destroy();
        $_SESSION = [];
    }

    public static function regenerar() {
        session_regenerate_id(true);
    }

    public static function estaLogado() {
        return self::existe('usuario_id');
    }

    public static function getUsuarioId() {
        return self::obter('usuario_id');
    }

    public static function getUsuarioPerfil() {
        return self::obter('usuario_perfil');
    }

    public static function getUsuarioEmpresaId() {
        return self::obter('usuario_empresa_id');
    }

    public static function getUsuarioFiliaisIds() {
        return self::obter('usuario_filiais_ids') ?? [];
    }

    public static function getUsuarioNome() {
        return self::obter('usuario_nome');
    }

    public static function getUsuarioEmail() {
        return self::obter('usuario_email');
    }

    public static function getUsuarioFiliais() {
        return self::obter('usuario_filiais') ?? [];
    }

    public static function getUsuarioPermissoes() {
        return self::obter('usuario_permissoes') ?? [];
    }
}