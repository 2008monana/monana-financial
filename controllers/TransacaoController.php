<?php
/**
 * TransacoesController - COMPLETO COM RESUMOS MENSAIS E FECHO DIÁRIO
 * Gerencia os lançamentos financeiros (vendas, compras, despesas, devoluções)
 */

// Garantir que o Controller base existe
if (!class_exists('Controller')) {
    require_once CAMINHO_RAIZ . '/core/Controller.php';
}

// Carregar autoload do Composer se existir
if (file_exists(CAMINHO_RAIZ . '/vendor/autoload.php')) {
    require_once CAMINHO_RAIZ . '/vendor/autoload.php';
}

// Construtores reutilizáveis de exportação
require_once CAMINHO_RAIZ . '/exports/pdf/RelatorioPdfBuilder.php';
require_once CAMINHO_RAIZ . '/exports/excel/RelatorioExcelBuilder.php';

// =============================================
// NOVO: Model ResumoMensal
// =============================================
require_once CAMINHO_RAIZ . '/models/ResumoMensal.php';

class TransacoesController extends Controller
{
    private $transacaoModel;
    private $categoriaModel;
    private $filialModel;
    private $empresaModel;

    public function __construct()
    {
        // Verificar autenticação
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirecionar('auth/login');
        }

        require_once CAMINHO_RAIZ . '/models/Transacao.php';
        require_once CAMINHO_RAIZ . '/models/Categoria.php';
        require_once CAMINHO_RAIZ . '/models/Filial.php';
        require_once CAMINHO_RAIZ . '/models/Empresa.php';
        require_once CAMINHO_RAIZ . '/helpers/formatacao.php';
        require_once CAMINHO_RAIZ . '/helpers/SessaoHelper.php';

        $this->transacaoModel = new Transacao();
        $this->categoriaModel = new Categoria();
        $this->filialModel = new Filial();
        $this->empresaModel = new Empresa();
    }

    // =============================================
    // MÉTODO AUXILIAR: ATUALIZAR RESUMO MENSAL
    // =============================================
    
    /**
     * Atualizar resumo mensal após uma transação
     */
    private function atualizarResumoMensal(int $filialId, string $dataTransacao): void
    {
        $ano = (int) date('Y', strtotime($dataTransacao));
        $mes = (int) date('m', strtotime($dataTransacao));
        
        $resumoModel = new ResumoMensal();
        $resumoModel->calcularEAtualizar($filialId, $ano, $mes);
    }

    // =============================================
    // CRUD - TRANSAÇÕES
    // =============================================

    /**
     * Listar transações com filtros
     */
    public function index()
    {
        // Verificar se é uma exportação
        if (isset($_GET['exportar'])) {
            if ($_GET['exportar'] === 'pdf') {
                $this->exportarPDF();
                return;
            } elseif ($_GET['exportar'] === 'excel') {
                $this->exportarExcel();
                return;
            }
        }

        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $usuarioId = (int) $_SESSION['usuario_id'];

        // Buscar empresas (para Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Filtrar filiais a que o utilizador tem acesso
        if ($perfil === 'usuario_interno') {
            $filiaisIds = SessaoHelper::getUsuarioFiliaisIds();
        } else {
            $filiaisIds = null;
        }

        // Parâmetros de filtro
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t'),
            'tipo' => $_GET['tipo'] ?? '',
            'categoria_id' => (int) ($_GET['categoria_id'] ?? 0),
            'filial_id' => (int) ($_GET['filial_id'] ?? 0),
            'empresa_id' => (int) ($_GET['empresa_id'] ?? 0),
            'busca' => $_GET['busca'] ?? '',
        ];

        // Se for Admin da Empresa, forçar o filtro da empresa dele
        if ($perfil !== 'super_admin') {
            $filtros['empresa_id'] = (int) $empresaId;
        }

        // Buscar transações
        $transacoes = $this->transacaoModel->buscarComFiltros(
            $filtros['empresa_id'] ?: null,
            $filiaisIds,
            $filtros['data_inicio'],
            $filtros['data_fim'],
            $filtros['tipo'],
            $filtros['categoria_id'],
            $filtros['filial_id'],
            $filtros['busca']
        );

        // Buscar categorias e filiais para os filtros
        if ($perfil === 'super_admin') {
            $categorias = $this->categoriaModel->ativas();
            $filiais = $this->filialModel->todas();
        } else {
            $categorias = $this->categoriaModel->porEmpresa((int) $empresaId);
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Calcular totais
        $totalEntradas = 0;
        $totalSaidas = 0;
        foreach ($transacoes as $t) {
            if ($t['tipo'] === 'entrada') {
                $totalEntradas += (float) $t['valor'];
            } else {
                $totalSaidas += (float) $t['valor'];
            }
        }
        $saldo = $totalEntradas - $totalSaidas;

        // Resumo por Filial (Admin Empresa) ou por Empresa (Super Admin)
        $resumoPorFilial = [];
        $resumoPorEmpresa = [];

        if ($perfil === 'super_admin') {
            $empresasList = $this->empresaModel->todos();
            foreach ($empresasList as $emp) {
                $entradas = 0;
                $saidas = 0;
                $total = 0;
                foreach ($transacoes as $t) {
                    if (isset($t['empresa_id']) && (int)$t['empresa_id'] === (int)$emp['id']) {
                        $total++;
                        if ($t['tipo'] === 'entrada') {
                            $entradas += (float) $t['valor'];
                        } else {
                            $saidas += (float) $t['valor'];
                        }
                    }
                }
                if ($total > 0) {
                    $resumoPorEmpresa[] = [
                        'nome' => $emp['nome'],
                        'entradas' => $entradas,
                        'saidas' => $saidas,
                        'saldo' => $entradas - $saidas,
                        'total' => $total,
                    ];
                }
            }
        } else {
            foreach ($filiais as $filial) {
                $entradas = 0;
                $saidas = 0;
                $total = 0;
                foreach ($transacoes as $t) {
                    if ((int)$t['filial_id'] === (int)$filial['id']) {
                        $total++;
                        if ($t['tipo'] === 'entrada') {
                            $entradas += (float) $t['valor'];
                        } else {
                            $saidas += (float) $t['valor'];
                        }
                    }
                }
                if ($total > 0) {
                    $resumoPorFilial[] = [
                        'nome' => $filial['nome'],
                        'entradas' => $entradas,
                        'saidas' => $saidas,
                        'saldo' => $entradas - $saidas,
                        'total' => $total,
                    ];
                }
            }
        }

        // Determinar o título do filtro ativo
        $filtroAtivo = '';
        $tiposLabels = [
            'venda' => 'Vendas',
            'compra' => 'Compras',
            'custo' => 'Despesas',
            'devolucao' => 'Devoluções'
        ];
        if (!empty($filtros['tipo']) && isset($tiposLabels[$filtros['tipo']])) {
            $filtroAtivo = $tiposLabels[$filtros['tipo']];
        }

        $this->renderizar('transacoes/index', [
            'tituloPagina' => 'Movimentos Financeiros',
            'tituloIcon' => 'fa-list-ul',
            'filtroAtivo' => $filtroAtivo,
            'paginaAtiva' => 'transacoes',
            'transacoes' => $transacoes,
            'categorias' => $categorias,
            'filiais' => $filiais,
            'empresas' => $empresas,
            'filtros' => $filtros,
            'totalEntradas' => $totalEntradas,
            'totalSaidas' => $totalSaidas,
            'saldo' => $saldo,
            'resumoPorFilial' => $resumoPorFilial,
            'resumoPorEmpresa' => $resumoPorEmpresa,
            'perfil' => $perfil,
            'tipos' => [
                'entrada' => 'Entrada',
                'saida' => 'Saída',
            ],
            'metodos_pagamento' => [
                'numerario' => 'Numerário',
                'tpa' => 'TPA',
                'transferencia' => 'Transferência',
                'outro' => 'Outro',
            ],
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Formulário de criação de transação
     */
    public function criar()
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Verificar permissão
        if ($perfil === 'visualizador') {
            $this->setFlash('erro', 'Não tem permissão para criar lançamentos.');
            $this->redirecionar('transacoes/index');
            return;
        }

        // Buscar filiais a que o utilizador tem acesso
        if ($perfil === 'usuario_interno') {
            $filiais = $this->filialModel->porUsuario((int) $_SESSION['usuario_id']);
        } else if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        $categorias = $this->categoriaModel->porEmpresa((int) $empresaId);

        $this->renderizar('transacoes/criar', [
            'tituloPagina' => 'Novo Lançamento',
            'paginaAtiva' => 'transacoes',
            'filiais' => $filiais,
            'categorias' => $categorias,
            'tipos' => [
                'entrada' => 'Entrada (Venda)',
                'saida' => 'Saída (Compra/Despesa)',
            ],
            'metodos_pagamento' => [
                'numerario' => 'Numerário',
                'tpa' => 'TPA',
                'transferencia' => 'Transferência',
                'outro' => 'Outro',
            ],
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Salvar nova transação
     */
    public function armazenar()
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $usuarioId = (int) $_SESSION['usuario_id'];

        // Validar CSRF
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('transacoes/criar');
            return;
        }

        // =============================================
        // CORREÇÃO: Validar tipo corretamente
        // =============================================
        $tipo = $_POST['tipo'] ?? '';
        if (!in_array($tipo, ['entrada', 'saida'])) {
            $this->setFlash('erro', 'Tipo inválido selecionado.');
            $this->redirecionar('transacoes/criar');
            return;
        }

        // Validar dados
        $erros = $this->validarDados($_POST);

        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_antigos'] = $_POST;
            $this->redirecionar('transacoes/criar');
            return;
        }

        // Preparar dados
        $dados = [
            'empresa_id' => $empresaId,
            'filial_id' => (int) $_POST['filial_id'],
            'usuario_id' => $usuarioId,
            'categoria_id' => (int) $_POST['categoria_id'],
            'tipo' => $tipo,  // 'entrada' ou 'saida'
            'descricao' => trim($_POST['descricao'] ?? ''),
            'valor' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor'])),
            'metodo_pagamento' => $_POST['metodo_pagamento'],
            'data_transacao' => $_POST['data_transacao'],
        ];

        // Inserir
        $id = $this->transacaoModel->inserir($dados);

        if ($id) {
            // Atualizar resumo mensal
            $this->atualizarResumoMensal((int) $dados['filial_id'], $dados['data_transacao']);
            $this->setFlash('sucesso', 'Lançamento criado com sucesso!');
        } else {
            $this->setFlash('erro', 'Erro ao criar lançamento. Tente novamente.');
        }

        $this->redirecionar('transacoes/index');
    }

    /**
     * Formulário de edição de transação
     */
    public function editar($id)
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Buscar transação
        $transacao = $this->transacaoModel->encontrarPorId((int) $id);

        if (!$transacao) {
            $this->setFlash('erro', 'Transação não encontrada.');
            $this->redirecionar('transacoes/index');
            return;
        }

        // Verificar permissão (Super Admin pode editar tudo)
        if ($perfil !== 'super_admin' && $perfil !== 'admin_empresa') {
            if ((int) $transacao['usuario_id'] !== $usuarioId) {
                $this->setFlash('erro', 'Não tem permissão para editar este lançamento.');
                $this->redirecionar('transacoes/index');
                return;
            }
        }

        $empresaId = $_SESSION['empresa_id'] ?? null;
        $categorias = $this->categoriaModel->porEmpresa((int) $empresaId);
        
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        $this->renderizar('transacoes/editar', [
            'tituloPagina' => 'Editar Lançamento',
            'paginaAtiva' => 'transacoes',
            'transacao' => $transacao,
            'filiais' => $filiais,
            'categorias' => $categorias,
            'tipos' => [
                'entrada' => 'Entrada (Venda)',
                'saida' => 'Saída (Compra/Despesa)',
            ],
            'metodos_pagamento' => [
                'numerario' => 'Numerário',
                'tpa' => 'TPA',
                'transferencia' => 'Transferência',
                'outro' => 'Outro',
            ],
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Atualizar transação
     */
    public function atualizar($id)
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Validar CSRF
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('transacoes/editar/' . $id);
            return;
        }

        // Buscar transação
        $transacao = $this->transacaoModel->encontrarPorId((int) $id);

        if (!$transacao) {
            $this->setFlash('erro', 'Transação não encontrada.');
            $this->redirecionar('transacoes/index');
            return;
        }

        // Verificar permissão (Super Admin pode editar tudo)
        if ($perfil !== 'super_admin' && $perfil !== 'admin_empresa') {
            if ((int) $transacao['usuario_id'] !== $usuarioId) {
                $this->setFlash('erro', 'Não tem permissão para editar este lançamento.');
                $this->redirecionar('transacoes/index');
                return;
            }
        }

        // Validar dados
        $erros = $this->validarDados($_POST);

        if (!empty($erros)) {
            $_SESSION['erros'] = $erros;
            $_SESSION['dados_antigos'] = $_POST;
            $this->redirecionar('transacoes/editar/' . $id);
            return;
        }

        // Guardar dados antigos para atualizar resumo
        $filialIdAntigo = (int) $transacao['filial_id'];
        $dataAntiga = $transacao['data_transacao'];

        // Preparar dados
        $dados = [
            'filial_id' => (int) $_POST['filial_id'],
            'categoria_id' => (int) $_POST['categoria_id'],
            'tipo' => $_POST['tipo'],
            'descricao' => trim($_POST['descricao'] ?? ''),
            'valor' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor'])),
            'metodo_pagamento' => $_POST['metodo_pagamento'],
            'data_transacao' => $_POST['data_transacao'],
        ];

        // Atualizar
        $atualizado = $this->transacaoModel->atualizar((int) $id, $dados);

        if ($atualizado) {
            // Atualizar resumo mensal (data antiga e nova)
            $this->atualizarResumoMensal($filialIdAntigo, $dataAntiga);
            $this->atualizarResumoMensal((int) $dados['filial_id'], $dados['data_transacao']);
            $this->setFlash('sucesso', 'Lançamento atualizado com sucesso!');
        } else {
            $this->setFlash('erro', 'Erro ao atualizar lançamento.');
        }

        $this->redirecionar('transacoes/index');
    }

    /**
     * Excluir transação
     */
    public function excluir($id)
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Buscar transação
        $transacao = $this->transacaoModel->encontrarPorId((int) $id);

        if (!$transacao) {
            $this->setFlash('erro', 'Transação não encontrada.');
            $this->redirecionar('transacoes/index');
            return;
        }

        // Verificar permissão (Super Admin pode excluir tudo)
        if ($perfil !== 'super_admin' && $perfil !== 'admin_empresa') {
            if ((int) $transacao['usuario_id'] !== $usuarioId) {
                $this->setFlash('erro', 'Não tem permissão para eliminar este lançamento.');
                $this->redirecionar('transacoes/index');
                return;
            }
        }

        // Guardar dados antes de eliminar
        $filialId = (int) $transacao['filial_id'];
        $dataTransacao = $transacao['data_transacao'];

        // Eliminar
        $excluido = $this->transacaoModel->eliminar((int) $id);

        if ($excluido) {
            // Atualizar resumo mensal
            $this->atualizarResumoMensal($filialId, $dataTransacao);
            $this->setFlash('sucesso', 'Lançamento eliminado com sucesso!');
        } else {
            $this->setFlash('erro', 'Erro ao eliminar lançamento.');
        }

        $this->redirecionar('transacoes/index');
    }

    // =============================================
    // EXPORTAÇÃO
    // =============================================

    /**
     * Exportar transações para Excel (.xlsx)
     */
    public function exportarExcel()
    {
        [$transacoes, $totalEntradas, $totalSaidas, $filtros, $perfil, $empresa] = $this->prepararDadosExportacao();

        $colunas = ['Data'];
        if ($perfil === 'super_admin') {
            $colunas[] = 'Empresa';
        }
        array_push($colunas, 'Filial', 'Tipo', 'Categoria', 'Descrição', 'Valor (Kz)', 'Utilizador');

        $linhas = [];
        foreach ($transacoes as $t) {
            $linha = [date('d/m/Y', strtotime($t['data_transacao']))];
            if ($perfil === 'super_admin') {
                $linha[] = $t['empresa_nome'] ?? 'N/A';
            }
            $linha[] = $t['filial_nome'] ?? 'N/A';
            $linha[] = $t['tipo'] === 'entrada' ? 'Entrada' : 'Saída';
            $linha[] = $t['categoria_nome'] ?? 'N/A';
            $linha[] = $t['descricao'] ?? '-';
            $linha[] = ['valor' => (float) $t['valor'], 'tipo' => 'moeda', 'estilo' => $t['tipo'] === 'entrada' ? 'positivo' : 'negativo'];
            $linha[] = $t['usuario_nome'] ?? 'N/A';
            $linhas[] = $linha;
        }

        $saldo = $totalEntradas - $totalSaidas;

        (new RelatorioExcelBuilder('Relatório de Transações', 'transacoes_' . date('Y-m-d')))
            ->definirEmpresa($empresa)
            ->definirSubtitulo($this->descreverPeriodoFiltros($filtros, $perfil, count($transacoes)))
            ->definirNomeFolha('Transações')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirResumo([
                ['rotulo' => 'Total Entradas', 'valor' => $totalEntradas, 'estilo' => 'positivo'],
                ['rotulo' => 'Total Saídas', 'valor' => $totalSaidas, 'estilo' => 'negativo'],
                ['rotulo' => 'Saldo', 'valor' => $saldo, 'estilo' => $saldo >= 0 ? 'positivo' : 'negativo'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    /**
     * Exportar transações para PDF
     */
    public function exportarPDF()
    {
        [$transacoes, $totalEntradas, $totalSaidas, $filtros, $perfil, $empresa] = $this->prepararDadosExportacao();

        $colunas = ['Data'];
        if ($perfil === 'super_admin') {
            $colunas[] = 'Empresa';
        }
        array_push($colunas, 'Filial', 'Tipo', 'Categoria', 'Descrição', 'Valor (Kz)', 'Utilizador');

        $linhas = [];
        foreach ($transacoes as $t) {
            $linha = [date('d/m/Y', strtotime($t['data_transacao']))];
            if ($perfil === 'super_admin') {
                $linha[] = $t['empresa_nome'] ?? 'N/A';
            }
            $linha[] = $t['filial_nome'] ?? 'N/A';
            $linha[] = [
                'texto' => $t['tipo'] === 'entrada' ? 'Entrada' : 'Saída',
                'tipo' => 'badge',
                'badge_classe' => $t['tipo'] === 'entrada' ? 'badge-sucesso' : 'badge-perigo',
            ];
            $linha[] = $t['categoria_nome'] ?? 'N/A';
            $linha[] = $t['descricao'] ?? '-';
            $linha[] = [
                'texto' => number_format((float) $t['valor'], 0, ',', '.'),
                'classe' => $t['tipo'] === 'entrada' ? 'positivo' : 'negativo',
                'alinhar' => 'direita',
            ];
            $linha[] = $t['usuario_nome'] ?? 'N/A';
            $linhas[] = $linha;
        }

        $saldo = $totalEntradas - $totalSaidas;

        (new RelatorioPdfBuilder('Relatório de Transações', 'transacoes_' . date('Y-m-d')))
            ->definirEmpresa($empresa)
            ->definirSubtitulo($this->descreverPeriodoFiltros($filtros, $perfil, count($transacoes)))
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirCartoesResumo([
                ['rotulo' => 'Total Entradas', 'valor' => number_format($totalEntradas, 0, ',', '.') . ' Kz', 'cor' => 'verde'],
                ['rotulo' => 'Total Saídas', 'valor' => number_format($totalSaidas, 0, ',', '.') . ' Kz', 'cor' => 'vermelho'],
                ['rotulo' => 'Saldo', 'valor' => number_format($saldo, 0, ',', '.') . ' Kz', 'cor' => $saldo >= 0 ? 'verde' : 'vermelho'],
                ['rotulo' => 'Total de Transações', 'valor' => (string) count($transacoes), 'cor' => 'navy'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    /**
     * Validar dados do formulário
     */
    private function validarDados($dados)
    {
        $erros = [];

        if (empty($dados['filial_id']) || $dados['filial_id'] <= 0) {
            $erros['filial_id'] = 'Selecione uma filial.';
        }

        if (empty($dados['categoria_id']) || $dados['categoria_id'] <= 0) {
            $erros['categoria_id'] = 'Selecione uma categoria.';
        }

        // =============================================
        // CORREÇÃO: Validar tipo corretamente
        // =============================================
        $tipo = $dados['tipo'] ?? '';
        if (empty($tipo) || !in_array($tipo, ['entrada', 'saida'])) {
            $erros['tipo'] = 'Selecione um tipo válido (Entrada ou Saída).';
        }

        if (empty($dados['valor']) || $dados['valor'] <= 0) {
            $erros['valor'] = 'Introduza um valor válido (maior que zero).';
        }

        if (empty($dados['data_transacao'])) {
            $erros['data_transacao'] = 'Selecione uma data.';
        }

        if (empty($dados['metodo_pagamento'])) {
            $erros['metodo_pagamento'] = 'Selecione um método de pagamento.';
        }

        return $erros;
    }

    /**
     * Reúne dados comuns às exportações (PDF e Excel)
     */
    private function prepararDadosExportacao(): array
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;

        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? date('Y-m-01'),
            'data_fim' => $_GET['data_fim'] ?? date('Y-m-t'),
            'tipo' => $_GET['tipo'] ?? '',
            'categoria_id' => (int) ($_GET['categoria_id'] ?? 0),
            'filial_id' => (int) ($_GET['filial_id'] ?? 0),
            'empresa_id' => (int) ($_GET['empresa_id'] ?? 0),
            'busca' => $_GET['busca'] ?? '',
        ];

        if ($perfil !== 'super_admin') {
            $filtros['empresa_id'] = (int) $empresaId;
        }

        $transacoes = $this->transacaoModel->buscarComFiltros(
            $filtros['empresa_id'] ?: null,
            null,
            $filtros['data_inicio'],
            $filtros['data_fim'],
            $filtros['tipo'],
            $filtros['categoria_id'],
            $filtros['filial_id'],
            $filtros['busca']
        );

        $totalEntradas = 0;
        $totalSaidas = 0;
        foreach ($transacoes as $t) {
            if ($t['tipo'] === 'entrada') {
                $totalEntradas += (float) $t['valor'];
            } else {
                $totalSaidas += (float) $t['valor'];
            }
        }

        // Dados da empresa para o cabeçalho do relatório
        $empresa = [];
        $idEmpresaParaCabecalho = $filtros['empresa_id'] ?: (int) $empresaId;
        if ($idEmpresaParaCabecalho) {
            $dadosEmpresa = $this->empresaModel->encontrarPorId((int) $idEmpresaParaCabecalho);
            if ($dadosEmpresa) {
                $empresa = [
                    'nome' => $dadosEmpresa['nome'] ?? '',
                    'nif' => $dadosEmpresa['nif'] ?? '',
                    'endereco' => $dadosEmpresa['endereco'] ?? '',
                ];
            }
        }

        return [$transacoes, $totalEntradas, $totalSaidas, $filtros, $perfil, $empresa];
    }

    /**
     * Monta a linha de subtítulo (período + âmbito + total) usada nos relatórios.
     */
    private function descreverPeriodoFiltros(array $filtros, string $perfil, int $totalTransacoes): string
    {
        $partes = [
            'Período: ' . date('d/m/Y', strtotime($filtros['data_inicio'])) . ' até ' . date('d/m/Y', strtotime($filtros['data_fim'])),
        ];
        if ($perfil === 'super_admin' && !$filtros['empresa_id']) {
            $partes[] = 'Todas as Empresas';
        }
        $partes[] = 'Total: ' . $totalTransacoes . ' transações';

        return implode('  •  ', $partes);
    }

    /**
     * Definir mensagem flash
     */
    protected function setFlash($tipo, $mensagem)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    /**
     * Redirecionar para rota
     */
    protected function redirecionar($rota)
    {
        header('Location: ' . URL_BASE . '/' . ltrim($rota, '/'));
        exit;
    }

    // =============================================
    // FECHO DIÁRIO (ESTILO PLANILHA)
    // =============================================

    /**
     * Formulário de fecho diário (igual à planilha)
     */
    public function fechoDiario(): void
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $usuarioId = (int) $_SESSION['usuario_id'];

        // Verificar permissão
        if ($perfil === 'visualizador') {
            $this->setFlash('erro', 'Não tem permissão para aceder a esta página.');
            $this->redirecionar('dashboard/index');
            return;
        }

        // Buscar filiais
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Data atual
        $data = $_GET['data'] ?? date('Y-m-d');
        
        // Buscar fecho existente para esta data e filial
        $fechoExistente = null;
        $filialId = (int) ($_GET['filial_id'] ?? 0);
        if ($filialId > 0) {
            $fechoExistente = $this->transacaoModel->buscarPorDia($filialId, $data);
        }

        // Buscar saldo anterior (último dia do mês anterior)
        $saldoAnterior = $this->calcularSaldoAnterior($filialId, $data);

        $this->renderizar('transacoes/fecho-diario', [
            'tituloPagina' => 'Fecho Diário',
            'paginaAtiva' => 'transacoes',
            'filiais' => $filiais,
            'data' => $data,
            'filialId' => $filialId,
            'fecho' => $fechoExistente,
            'saldoAnterior' => $saldoAnterior,
            'perfil' => $perfil,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Salvar fecho diário
     */
    public function salvarFecho(): void
    {
        // Validar CSRF
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->setFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('transacoes/fechoDiario');
            return;
        }

        $filialId = (int) ($_POST['filial_id'] ?? 0);
        $data = $_POST['data_transacao'] ?? date('Y-m-d');
        $usuarioId = (int) $_SESSION['usuario_id'];
        $empresaId = $_SESSION['empresa_id'] ?? null;

        // Validar filial
        if ($filialId <= 0) {
            $this->setFlash('erro', 'Selecione uma filial.');
            $this->redirecionar('transacoes/fechoDiario');
            return;
        }

        // Preparar dados
        $dados = [
            'empresa_id' => $empresaId,
            'filial_id' => $filialId,
            'usuario_id' => $usuarioId,
            'categoria_id' => 1, // Categoria padrão
            'tipo' => 'entrada',
            'descricao' => 'Fecho diário - ' . date('d/m/Y', strtotime($data)),
            'data_transacao' => $data,
            
            // Campos da planilha
            'tpa_bca' => (float) str_replace(',', '.', str_replace('.', '', $_POST['tpa_bca'] ?? 0)),
            'tpa_keve' => (float) str_replace(',', '.', str_replace('.', '', $_POST['tpa_keve'] ?? 0)),
            'transferencias' => (float) str_replace(',', '.', str_replace('.', '', $_POST['transferencias'] ?? 0)),
            'despesas' => (float) str_replace(',', '.', str_replace('.', '', $_POST['despesas'] ?? 0)),
            'devolucao' => (float) str_replace(',', '.', str_replace('.', '', $_POST['devolucao'] ?? 0)),
            'dinheiro' => (float) str_replace(',', '.', str_replace('.', '', $_POST['dinheiro'] ?? 0)),
            'deposito' => (float) str_replace(',', '.', str_replace('.', '', $_POST['deposito'] ?? 0)),
            'saidas_extra' => (float) str_replace(',', '.', str_replace('.', '', $_POST['saidas_extra'] ?? 0)),
            'gastos_diario' => (float) str_replace(',', '.', str_replace('.', '', $_POST['gastos_diario'] ?? 0)),
            'gastos_extra' => (float) str_replace(',', '.', str_replace('.', '', $_POST['gastos_extra'] ?? 0)),
            'saldo_anterior' => (float) str_replace(',', '.', str_replace('.', '', $_POST['saldo_anterior'] ?? 0)),
            'metodo_pagamento' => 'numerario',
        ];

        // Verificar se já existe fecho para este dia
        $existente = $this->transacaoModel->buscarPorDia($filialId, $data);

        if ($existente) {
            // Atualizar
            $resultado = $this->transacaoModel->atualizarFechoDiario((int) $existente['id'], $dados);
            $mensagem = 'Fecho diário atualizado com sucesso!';
        } else {
            // Inserir novo
            $resultado = $this->transacaoModel->inserirFechoDiario($dados);
            $mensagem = 'Fecho diário registado com sucesso!';
        }

        if ($resultado) {
            // Atualizar resumo mensal
            $this->atualizarResumoMensal($filialId, $data);
            $this->setFlash('sucesso', $mensagem);
        } else {
            $this->setFlash('erro', 'Erro ao registar fecho diário.');
        }

        $this->redirecionar('transacoes/fechoDiario?filial_id=' . $filialId . '&data=' . $data);
    }

    /**
     * Calcular saldo anterior (último dia do mês anterior)
     */
    private function calcularSaldoAnterior(int $filialId, string $data): float
    {
        // Se não houver filial, retornar 0
        if ($filialId <= 0) {
            return 0;
        }

        // Usar o método da classe Transacao
        return $this->transacaoModel->buscarSaldoAnterior($filialId, $data);
    }
}