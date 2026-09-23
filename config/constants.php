<?php
/**
 * Constantes globais do sistema
 */

// =====================================================
// CORREÇÃO: Só define CAMINHO_RAIZ se ainda não foi definido
// =====================================================
if (!defined('CAMINHO_RAIZ')) {
    define('CAMINHO_RAIZ', dirname(__DIR__));
}

// Perfis de utilizador
define('PERFIL_SUPER_ADMIN', 'super_admin');
define('PERFIL_ADMIN_EMPRESA', 'admin_empresa');
define('PERFIL_USUARIO_INTERNO', 'usuario_interno');
define('PERFIL_VISUALIZADOR', 'visualizador');

// Tipos de transação
define('TIPO_VENDA', 'venda');
define('TIPO_DEVOLUCAO', 'devolucao');
define('TIPO_COMPRA', 'compra');
define('TIPO_CUSTO', 'custo');

// Tipos de notificação
define('NOTIF_ALERTA', 'alerta');
define('NOTIF_SUCESSO', 'sucesso');
define('NOTIF_AVISO', 'aviso');
define('NOTIF_ERRO', 'erro');

// Caminhos absolutos (usando CAMINHO_RAIZ já definido)
define('CAMINHO_VIEWS', CAMINHO_RAIZ . '/views');
define('CAMINHO_UPLOADS', CAMINHO_RAIZ . '/public/uploads');
define('CAMINHO_LOGS', CAMINHO_RAIZ . '/logs');
define('CAMINHO_EXPORTS', CAMINHO_RAIZ . '/exports');
// Pasta de armazenamento dos backups (fora de /public para não ser acedida diretamente)
define('CAMINHO_BACKUPS', CAMINHO_RAIZ . '/storage/backups');

// Duração do token de redefinição de senha (segundos) — 1 hora
define('DURACAO_TOKEN_SENHA', 3600);

// Duração do "processamento" visual do login (ms)
define('DURACAO_PROCESSAMENTO_LOGIN_MS', 1500);