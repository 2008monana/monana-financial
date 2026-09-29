<?php
/**
 * Classe Router
 * Gerencia o roteamento de todas as URLs do sistema
 */

if (!defined('CAMINHO_RAIZ')) {
    die('CAMINHO_RAIZ não definido. Verifique o index.php.');
}

class Router
{
    private array $rotasPublicas = [
        'auth',
        'auth/index',
        'auth/login',
        'auth/autenticar',
        'auth/logout',
        'auth/esqueciSenha',
        'auth/enviarLinkRedefinicao',
        'auth/redefinirSenha',
        'auth/salvarNovaSenha',
        'auth/processarEsqueciSenha',
        'auth/atualizarSenha',
    ];

    /**
     * Normaliza a rota para as chaves de $rotaParaModulo.
     * Ex: 'dashboard' => 'dashboard/index'; 'metodos-pagamento' => 'metodos-pagamento/index'
     */
    private function normalizarRota(string $modulo, string $acao): string
    {
        $rota = $modulo . '/' . $acao;
        if ($acao === 'index' || !isset($this->rotaParaModulo[$rota])) {
            $rotaIndex = $modulo . '/index';
            if (isset($this->rotaParaModulo[$rotaIndex])) {
                return $rotaIndex;
            }
        }
        return $rota;
    }

    // Mapeamento de rotas para módulos (para verificação de permissão)
    private array $rotaParaModulo = [
        // =============================================
        // DASHBOARD
        // =============================================
        'dashboard' => 'dashboard',
        'dashboard/index' => 'dashboard',
        'dashboard/superadmin' => 'dashboard',
        'dashboard/adminempresa' => 'dashboard',

        // =============================================
        // TRANSAÇÕES / MOVIMENTOS
        // =============================================
        'transacoes' => 'movimentos',
        'transacoes/index' => 'movimentos',
        'transacoes/criar' => 'movimentos',
        'transacoes/armazenar' => 'movimentos',
        'transacoes/editar' => 'movimentos',
        'transacoes/atualizar' => 'movimentos',
        'transacoes/excluir' => 'movimentos',
        'transacoes/fechoDiario' => 'fecho_diario',
        'transacoes/salvarFecho' => 'fecho_diario',
        'transacoes/exportar' => 'movimentos',
        'transacoes/exportarPDF' => 'movimentos',
        'transacoes/exportarExcel' => 'movimentos',

        // =============================================
        // RELATÓRIOS
        // =============================================
        'relatorios' => 'relatorios',
        'relatorios/index' => 'relatorios',
        'relatorios/diario' => 'relatorios',
        'relatorios/mensal' => 'relatorios',
        'relatorios/anual' => 'relatorios',
        'relatorios/filial' => 'relatorios',
        'relatorios/pesquisa' => 'relatorios',
        'relatorios/diario-planilha' => 'planilha',

        // =============================================
        // CATEGORIAS
        // =============================================
        'categorias' => 'categorias',
        'categorias/index' => 'categorias',
        'categorias/criar' => 'categorias',
        'categorias/armazenar' => 'categorias',
        'categorias/editar' => 'categorias',
        'categorias/atualizar' => 'categorias',
        'categorias/alternarEstado' => 'categorias',
        'categorias/eliminar' => 'categorias',

        // =============================================
        // FILIAIS
        // =============================================
        'filiais' => 'filiais',
        'filiais/index' => 'filiais',
        'filiais/criar' => 'filiais',
        'filiais/gravar' => 'filiais',
        'filiais/editar' => 'filiais',
        'filiais/atualizar' => 'filiais',
        'filiais/alternarEstado' => 'filiais',
        'filiais/selecionar-empresa' => 'filiais',

        // =============================================
        // EMPRESAS (apenas Super Admin)
        // =============================================
        'empresas' => 'empresas',
        'empresas/index' => 'empresas',
        'empresas/criar' => 'empresas',
        'empresas/gravar' => 'empresas',
        'empresas/editar' => 'empresas',
        'empresas/atualizar' => 'empresas',
        'empresas/alternarEstado' => 'empresas',

        // =============================================
        // UTILIZADORES
        // =============================================
        'usuarios' => 'usuarios',
        'usuarios/index' => 'usuarios',
        'usuarios/criar' => 'usuarios',
        'usuarios/gravar' => 'usuarios',
        'usuarios/editar' => 'usuarios',
        'usuarios/atualizar' => 'usuarios',
        'usuarios/alternarEstado' => 'usuarios',
        'usuarios/redefinirSenha' => 'usuarios',
        'usuarios/selecionar-empresa' => 'usuarios',

        // =============================================
        // PERFIL (sempre acessível)
        // =============================================
        'perfil' => 'perfil',
        'perfil/index' => 'perfil',
        'perfil/atualizar' => 'perfil',
        'perfil/alterarSenha' => 'perfil',

        // =============================================
        // NOTIFICAÇÕES
        // =============================================
        'notificacoes' => 'notificacoes',
        'notificacoes/index' => 'notificacoes',
        'notificacoes/marcarComoLida' => 'notificacoes',
        'notificacoes/marcarTodasComoLidas' => 'notificacoes',
        'notificacoes/contagem' => 'notificacoes',
        'notificacoes/recentes' => 'notificacoes',

        // =============================================
        // BACKUPS (Super Admin: global | Admin Empresa: apenas a sua empresa)
        // =============================================
        'backups' => 'backups',
        'backups/index' => 'backups',
        'backups/gerar' => 'backups',
        'backups/configurarAutomatico' => 'backups',
        'backups/download' => 'backups',
        'backups/eliminar' => 'backups',
        'backups/importar' => 'backups',

        // =============================================
        // LOGS (apenas Super Admin)
        // =============================================
        'logs' => 'logs',
        'logs/index' => 'logs',
        'logs/detalhes' => 'logs',
        'logs/exportar' => 'logs',

        // =============================================
        // CONFIGURAÇÕES (Super Admin e Admin de Empresa)
        // =============================================
        'configuracoes' => 'configuracoes',
        'configuracoes/index' => 'configuracoes',
        'configuracoes/sistema' => 'configuracoes',
        'configuracoes/empresa' => 'configuracoes',
        'configuracoes/guardar' => 'configuracoes',

        // =============================================
        // MÉTODOS DE PAGAMENTO
        // =============================================
        'metodos-pagamento' => 'metodos_pagamento',
        'metodos-pagamento/index' => 'metodos_pagamento',
        'metodos-pagamento/criar' => 'metodos_pagamento',
        'metodos-pagamento/salvar' => 'metodos_pagamento',
        'metodos-pagamento/editar' => 'metodos_pagamento',
        'metodos-pagamento/atualizar' => 'metodos_pagamento',
        'metodos-pagamento/excluir' => 'metodos_pagamento',
    ];

    /**
     * Converte uma string com hífen para camelCase
     * Ex: diario-planilha → diarioPlanilha
     */
    private function converterParaCamelCase(string $string): string
    {
        if (strpos($string, '-') === false) {
            return $string;
        }
        
        $partes = explode('-', $string);
        $resultado = array_shift($partes);
        foreach ($partes as $parte) {
            $resultado .= ucfirst($parte);
        }
        return $resultado;
    }

    /**
     * Estrutura valida de URL: apenas "dominio/modulo" ou "dominio/modulo/acao[/parametro]".
     * Qualquer coisa fora desta estrutura (nomes de ficheiros como index.php,
     * extensoes .php/.html, caminhos com /public/, etc.) deve resultar em 404.
     */
    private function caminhoInvalido(string $caminho): bool
    {
        if ($caminho === '') {
            return false; // raiz -> dashboard
        }
        // Apenas letras, numeros, hifen, underscore e barras - nada de pontos!
        if (!preg_match('#^[a-zA-Z0-9_-]+(/[a-zA-Z0-9_-]+){0,2}$#', $caminho)) {
            return true;
        }
        // Bloqueio explicito de nomes de ficheiros / extensoes conhecidas
        if (preg_match('#\.(php[0-9]?|phtml|html?|js|css|json|xml|zip|tar|gz|bak|sql|log|swp|env|ini)$#i', $caminho)) {
            return true;
        }
        // Bloqueio do acesso direto a pasta public via URL limpa
        $primeiro = strtolower(explode('/', $caminho)[0]);
        if ($primeiro === 'public') {
            return true;
        }
        return false;
    }

    /**
     * Resposta padrao 404 para URLs fora da estrutura permitida
     */
    private function responder404(): void
    {
        http_response_code(404);
        $caminhoErro = CAMINHO_RAIZ . '/views/errors/404.php';
        if (file_exists($caminhoErro)) {
            require_once $caminhoErro;
            return;
        }
        echo '<!DOCTYPE html><html lang="pt"><head><meta charset="UTF-8"><title>404 - Pagina nao encontrada</title>'
           . '<style>body{font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;'
           . 'min-height:100vh;margin:0;background:#f3f4f6}.box{text-align:center;padding:40px}'
           . 'h1{color:#1e3a8a;font-size:72px;margin:0}p{color:#6b7280}a{color:#2563eb;text-decoration:none'
           . ';font-weight:bold}</style></head><body><div class="box"><h1>404</h1>'
           . '<p>A pagina que procura nao existe ou o endereco esta incorreto.</p>'
           . '<p><a href="' . URL_BASE . '/dashboard">Voltar ao Dashboard</a></p></div></body></html>';
    }

    public function despachar(string $caminho): void
    {
        // Limpar o caminho
        $caminho = trim($caminho, '/');

        // =============================================
        // REGRA DE URLs LIMPAS: so dominio + modulo (+ acao + parametro).
        // Qualquer link fora desta estrutura vai para a pagina 404.
        // =============================================
        if ($this->caminhoInvalido($caminho)) {
            $this->responder404();
            return;
        }

        // Se estiver vazio, vai para o dashboard
        if ($caminho === '') {
            $caminho = 'dashboard/index';
        }

        // Dividir a URL
        $partes = explode('/', $caminho);
        $nomeControlador = ucfirst($partes[0] ?? 'auth') . 'Controller';
        $acao = $partes[1] ?? 'index';
        
        // =============================================
        // CORREÇÃO: Converter hífen para camelCase
        // Ex: diario-planilha → diarioPlanilha
        // =============================================
        $acaoOriginal = $acao;
        $acao = $this->converterParaCamelCase($acao);
        
        // =============================================
        // CORREÇÃO: Metodos-pagamento → MetodosPagamentoController
        // =============================================
        if ($nomeControlador === 'Metodos-pagamentoController') {
            $nomeControlador = 'MetodosPagamentoController';
        }
        
        // =============================================
        // CORREÇÃO: Transacao → Transacoes (plural)
        // =============================================
        if ($nomeControlador === 'TransacaoController') {
            $nomeControlador = 'TransacoesController';
        } elseif ($nomeControlador === 'NotificacoesController') {
            // A rota é plural, mas o controlador existente é singular.
            $nomeControlador = 'NotificacaoController';
        }
        
        $parametro = $partes[2] ?? null;

        // =============================================
        // CORREÇÃO: URLs limpas (ex: /dashboard em vez de /dashboard/index)
        // Se o "método" não existir no controlador, é na verdade o index
        // Ex: /relatorios/pesquisa → método 'pesquisa'; /categorias → método 'index'
        // =============================================
        if ($parametro === null && $acao !== 'index') {
            $caminhoTmp = CAMINHO_RAIZ . '/controllers/' . $nomeControlador . '.php';
            if (file_exists($caminhoTmp)) {
                require_once $caminhoTmp;
                if (class_exists($nomeControlador) && !method_exists($nomeControlador, $acao)) {
                    $acaoOriginal = 'index';
                    $acao = 'index';
                }
            }
        }

        // Rota normalizada para verificação de permissões
        $rotaAtual = $this->normalizarRota($partes[0] ?? '', $acaoOriginal);

        // =============================================
        // MIDDLEWARE DE AUTENTICAÇÃO
        // =============================================
        if (!in_array($rotaAtual, $this->rotasPublicas, true)) {
            $caminhoMiddleware = CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
            if (file_exists($caminhoMiddleware)) {
                require_once $caminhoMiddleware;
                (new AuthMiddleware())->verificar();
            }

            // =============================================
            // MIDDLEWARE DE PERMISSÃO POR MÓDULO
            // =============================================
            $modulo = $this->rotaParaModulo[$rotaAtual] ?? null;
            
            // Perfil é sempre permitido, mas verificar os outros
            if ($modulo && $modulo !== 'perfil') {
                $caminhoModuloMiddleware = CAMINHO_RAIZ . '/middleware/ModuloMiddleware.php';
                if (file_exists($caminhoModuloMiddleware)) {
                    require_once $caminhoModuloMiddleware;
                    (new ModuloMiddleware())->verificar($modulo);
                }
            }
        }

        $caminhoControlador = CAMINHO_RAIZ . '/controllers/' . $nomeControlador . '.php';
        
        // Correção: Verificar se o ficheiro existe, tentar alternativas
        if (!file_exists($caminhoControlador)) {
            // Tentar com nome alternativo
            if ($nomeControlador === 'TransacoesController') {
                $caminhoControlador = CAMINHO_RAIZ . '/controllers/TransacaoController.php';
            } elseif ($nomeControlador === 'MetodosPagamentoController') {
                $caminhoControlador = CAMINHO_RAIZ . '/controllers/Metodos-pagamentoController.php';
            }
        }

        if (!file_exists($caminhoControlador)) {
            $this->responder404();
            return;
        }

        require_once $caminhoControlador;

        // Correção: Verificar se a classe existe
        if (!class_exists($nomeControlador)) {
            // Tentar com nome alternativo
            if ($nomeControlador === 'TransacoesController' && class_exists('TransacaoController')) {
                $nomeControlador = 'TransacaoController';
            } elseif ($nomeControlador === 'MetodosPagamentoController' && class_exists('MetodosPagamentoController')) {
                // OK
            } else {
                http_response_code(500);
                echo "Classe não encontrada: " . $nomeControlador;
                return;
            }
        }

        if (!method_exists($nomeControlador, $acao)) {
            $this->responder404();
            return;
        }

        $controlador = new $nomeControlador();

        if ($parametro !== null) {
            $controlador->$acao($parametro);
        } else {
            $controlador->$acao();
        }
    }
}