<?php
require_once CAMINHO_RAIZ . '/models/Modulo.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';

class ModuloMiddleware
{
    private Modulo $moduloModel;

    // Módulos permitidos para Admin Empresa
    private array $modulosPermitidosAdmin = [
        'dashboard',
        'movimentos',
        'fecho_diario',
        'relatorios',
        'planilha',
        'categorias',
        'filiais',
        'usuarios',
        'backups',
        'perfil',
        'notificacoes',
        'logs',
        'funcionarios',
        'metodos_pagamento',
        'configuracoes',
    ];

    public function __construct()
    {
        $this->moduloModel = new Modulo();
    }

    /**
     * Verifica se o utilizador tem permissão para aceder a um módulo
     */
    public function verificar(string $moduloNome): void
    {
        // Verificar se está logado
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . URL_BASE . '/auth/login');
            exit;
        }

        $usuarioId = (int) $_SESSION['usuario_id'];
        $perfil = $_SESSION['usuario_perfil'] ?? '';

        // Super Admin tem acesso a tudo
        if ($perfil === 'super_admin') {
            return;
        }

        // Admin Empresa tem acesso aos módulos permitidos
        if ($perfil === 'admin_empresa') {
            // Verificar se o módulo está na lista de permitidos
            if (in_array($moduloNome, $this->modulosPermitidosAdmin)) {
                return;
            }
            
            // Módulos que são exclusivos do Super Admin
            // ('logs' NÃO entra aqui: LogsController permite admin_empresa e já limita
            // a consulta à própria empresa via LogsController::empresaId())
            // ('configuracoes' NÃO entra aqui: o Admin Empresa acede às configurações
            // da SUA empresa; ConfiguracoesController restringe a página /sistema)
            $modulosExclusivos = ['empresas'];
            if (in_array($moduloNome, $modulosExclusivos)) {
                $this->negarAcesso();
            }
            
            // Por segurança, negar se não estiver na lista
            $this->negarAcesso();
            return;
        }

        // Perfil é sempre acessível
        if ($moduloNome === 'perfil') {
            return;
        }

        // Utilizador Interno e Visualizador: verificar permissões
        if (!$this->moduloModel->temPermissao($usuarioId, $moduloNome)) {
            $this->negarAcesso();
        }
    }

    /**
     * Negar acesso com mensagem - redireciona para Perfil
     */
    private function negarAcesso(): void
    {
        AuditoriaHelper::registar('permissao_negada', null, null, null, ['rota'=>$_GET['url'] ?? '', 'perfil'=>$_SESSION['usuario_perfil'] ?? ''], 'alta', 'Utilizador sem permissão para o módulo solicitado');
        $_SESSION['flash'] = [
            'tipo' => 'erro',
            'mensagem' => 'Não tem permissão para aceder a esta página.'
        ];
        
        header('Location: ' . URL_BASE . '/perfil/index');
        exit;
    }
}