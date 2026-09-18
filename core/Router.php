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
        'auth/login',
        'auth/autenticar',
        'auth/esqueciSenha',
        'auth/enviarLinkRedefinicao',
        'auth/redefinirSenha',
        'auth/salvarNovaSenha',
    ];

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
        'transacoes/fechoDiario' => 'movimentos',
        'transacoes/salvarFecho' => 'movimentos',
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
        'relatorios/diario-planilha' => 'relatorios',

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
        'empresas' => 'configuracoes',
        'empresas/index' => 'configuracoes',
        'empresas/criar' => 'configuracoes',
        'empresas/gravar' => 'configuracoes',
        'empresas/editar' => 'configuracoes',
        'empresas/atualizar' => 'configuracoes',
        'empresas/alternarEstado' => 'configuracoes',

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
        // IMPORTAÇÃO
        // =============================================
        'importacao' => 'importacao',
        'importacao/index' => 'importacao',
        'importacao/upload' => 'importacao',
        'importacao/processar' => 'importacao',
        'importacao/historico' => 'importacao',

        // =============================================
        // LOGS (apenas Super Admin)
        // =============================================
        'logs' => 'logs',
        'logs/index' => 'logs',

        // =============================================
        // CONFIGURAÇÕES (apenas Super Admin)
        // =============================================
        'configuracoes' => 'configuracoes',
        'configuracoes/index' => 'configuracoes',

        // =============================================
        // MÉTODOS DE PAGAMENTO (NOVO)
        // =============================================
        'metodos-pagamento' => 'metodos_pagamento',
        'metodos-pagamento/index' => 'metodos_pagamento',
        'metodos-pagamento/criar' => 'metodos_pagamento',
        'metodos-pagamento/gravar' => 'metodos_pagamento',
        'metodos-pagamento/editar' => 'metodos_pagamento',
        'metodos-pagamento/atualizar' => 'metodos_pagamento',
        'metodos-pagamento/alternarEstado' => 'metodos_pagamento',
        'metodos-pagamento/eliminar' => 'metodos_pagamento',
        'metodos-pagamento/criarCategoriaSaida' => 'metodos_pagamento',
        'metodos-pagamento/gravarCategoriaSaida' => 'metodos_pagamento',
        'metodos-pagamento/alternarEstadoCategoria' => 'metodos_pagamento',
        'metodos-pagamento/eliminarCategoria' => 'metodos_pagamento',
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

    public function despachar(string $caminho): void
    {
        // Limpar o caminho
        $caminho = trim($caminho, '/');
        
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
        }
        
        $parametro = $partes[2] ?? null;

        $rotaAtual = ($partes[0] ?? '') . '/' . $acaoOriginal;

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
            http_response_code(404);
            echo "Página não encontrada. Controlador: " . $nomeControlador;
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
            http_response_code(404);
            echo "Ação não encontrada: " . $acao . " (URL original: " . $acaoOriginal . ")";
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