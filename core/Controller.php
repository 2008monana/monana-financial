<?php
/**
 * Classe base Controller
 * Todos os controladores devem estender esta classe
 */

if (!defined('CAMINHO_RAIZ')) {
    die('CAMINHO_RAIZ não definido. Verifique o index.php.');
}

abstract class Controller
{
    /**
     * Renderiza uma view com layout
     */
    protected function renderizar(string $view, array $dados = [])
    {
        extract($dados);
        
        $caminhoView = CAMINHO_RAIZ . '/views/' . $view . '.php';
        
        if (!file_exists($caminhoView)) {
            die("View não encontrada: " . $view);
        }
        
        ob_start();
        require $caminhoView;
        $conteudo = ob_get_clean();
        
        // Layout principal
        $caminhoLayout = CAMINHO_RAIZ . '/views/layouts/principal.php';
        if (file_exists($caminhoLayout)) {
            require $caminhoLayout;
        } else {
            echo $conteudo;
        }
    }

    /**
     * Renderiza view sem layout (login, emails, etc.)
     */
    protected function renderizarSemLayout(string $view, array $dados = [])
    {
        extract($dados);
        $caminhoView = CAMINHO_RAIZ . '/views/' . $view . '.php';
        
        if (!file_exists($caminhoView)) {
            die("View não encontrada: " . $view);
        }
        
        require $caminhoView;
    }

    /**
     * Redireciona para outra rota
     */
    protected function redirecionar(string $rota)
    {
        header('Location: ' . URL_BASE . '/' . ltrim($rota, '/'));
        exit;
    }

    /**
     * Responde em JSON
     */
    protected function json(array $dados, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}