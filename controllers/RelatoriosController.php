<?php
/**
 * RelatoriosController - COMPLETO
 * Gerencia relatórios diários, mensais, anuais, por filial e planilha
 * Super Admin: Visão global de todas as empresas
 * Admin Empresa: Visão local da sua empresa
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Transacao.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/helpers/formatacao.php';
require_once CAMINHO_RAIZ . '/helpers/SessaoHelper.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';

// Carregar autoload do Composer se existir
if (file_exists(CAMINHO_RAIZ . '/vendor/autoload.php')) {
    require_once CAMINHO_RAIZ . '/vendor/autoload.php';
}

// Construtores reutilizáveis de exportação
require_once CAMINHO_RAIZ . '/exports/pdf/RelatorioPdfBuilder.php';
require_once CAMINHO_RAIZ . '/exports/excel/RelatorioExcelBuilder.php';

class RelatoriosController extends Controller
{
    private $transacaoModel;
    private $filialModel;
    private $empresaModel;
    private $usuarioModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirecionar('auth/login');
        }

        $this->transacaoModel = new Transacao();
        $this->filialModel = new Filial();
        $this->empresaModel = new Empresa();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Dashboard de relatórios
     * Super Admin: "Relatórios Consolidados" - todas as empresas
     * Admin Empresa: "Relatórios da Empresa" - apenas sua empresa
     */
    public function index(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $empresaNome = $_SESSION['empresa_nome'] ?? '';

        // Buscar empresas (Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Buscar filiais
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Resumo do mês atual
        $mesAtual = date('m');
        $anoAtual = date('Y');
        $resumoMes = $this->calcularResumoMensal(
            $perfil === 'super_admin' ? null : (int) $empresaId,
            $mesAtual,
            $anoAtual
        );

        // Resumo por empresa (Super Admin) ou por filial (Admin Empresa)
        $resumoEmpresas = [];
        $resumoFiliais = [];

        if ($perfil === 'super_admin') {
            $resumoEmpresas = $this->calcularResumoPorEmpresa($mesAtual, $anoAtual);
        } else {
            $resumoFiliais = $this->calcularResumoPorFilial((int) $empresaId, $mesAtual, $anoAtual);
        }

        // Período para exibição
        $periodoInicio = date('Y-m-01');
        $periodoFim = date('Y-m-t');

        $this->renderizar('relatorios/index', [
            'tituloPagina' => 'Relatórios',
            'paginaAtiva' => 'relatorios',
            'empresas' => $empresas,
            'filiais' => $filiais,
            'resumoMes' => $resumoMes,
            'resumoEmpresas' => $resumoEmpresas,
            'resumoFiliais' => [],
            'periodoInicio' => $periodoInicio,
            'periodoFim' => $periodoFim,
            'mesAtual' => $mesAtual,
            'anoAtual' => $anoAtual,
            'perfil' => $perfil,
            'empresaNome' => $empresaNome,
            'meses' => $this->getMeses(),
            'anos' => range(date('Y') - 5, date('Y')),
        ]);
    }

    /**
     * Relatório Diário
     */
    public function diario(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $data = $_GET['data'] ?? date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $data = date('Y-m-d');
        }

        // Filtros adicionais
        $empresaFiltro = isset($_GET['empresa_id']) && $_GET['empresa_id'] > 0 ? (int) $_GET['empresa_id'] : null;
        $filialFiltro = isset($_GET['filial_id']) && $_GET['filial_id'] > 0 ? (int) $_GET['filial_id'] : 0;

        // Buscar empresas para filtro (Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Buscar filiais para filtro
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Buscar transações do dia
        $empresaBusca = $perfil === 'super_admin' ? $empresaFiltro : (int) $empresaId;
        $transacoes = $this->transacaoModel->buscarComFiltros(
            $empresaBusca,
            null,
            $data,
            $data,
            '',
            0,
            $filialFiltro,
            ''
        );

        // Calcular totais
        $totais = $this->calcularTotais($transacoes);

        // Verificar exportação
        if (isset($_GET['exportar'])) {
            if ($_GET['exportar'] === 'excel') {
                $this->exportarDiarioExcel($transacoes, $totais, $data, $perfil);
                return;
            } elseif ($_GET['exportar'] === 'pdf') {
                $this->exportarDiarioPDF($transacoes, $totais, $data, $perfil);
                return;
            }
        }

        $this->renderizar('relatorios/diario', [
            'tituloPagina' => 'Relatório Diário',
            'paginaAtiva' => 'relatorios',
            'transacoes' => $transacoes,
            'totais' => $totais,
            'data' => $data,
            'dataFormatada' => date('d/m/Y', strtotime($data)),
            'perfil' => $perfil,
            'empresas' => $empresas,
            'filiais' => $filiais,
            'empresaNome' => $_SESSION['empresa_nome'] ?? '',
        ]);
    }

    /**
     * Relatório Mensal
     */
    public function mensal(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $mes = (int) ($_GET['mes'] ?? date('m'));
        $ano = (int) ($_GET['ano'] ?? date('Y'));

        if ($mes < 1 || $mes > 12) $mes = (int) date('m');
        if ($ano < 2000 || $ano > 2100) $ano = (int) date('Y');

        // Filtros adicionais
        $empresaFiltro = isset($_GET['empresa_id']) && $_GET['empresa_id'] > 0 ? (int) $_GET['empresa_id'] : null;
        $filialFiltro = isset($_GET['filial_id']) && $_GET['filial_id'] > 0 ? (int) $_GET['filial_id'] : 0;

        $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

        // Buscar empresas para filtro (Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Buscar filiais para filtro
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Buscar transações do mês
        $empresaBusca = $perfil === 'super_admin' ? $empresaFiltro : (int) $empresaId;
        $transacoes = $this->transacaoModel->buscarComFiltros(
            $empresaBusca,
            null,
            $dataInicio,
            $dataFim,
            '',
            0,
            $filialFiltro,
            ''
        );

        // Calcular resumo diário
        $resumoDiario = [];
        $totais = $this->calcularTotais($transacoes);
        $totais['total_transacoes'] = count($transacoes);

        foreach ($transacoes as $t) {
            $dia = (int) date('d', strtotime($t['data_transacao']));
            if (!isset($resumoDiario[$dia])) {
                $resumoDiario[$dia] = [
                    'dia' => $dia,
                    'entradas' => 0,
                    'saidas' => 0,
                    'vendas' => 0,
                    'compras' => 0,
                    'despesas' => 0,
                    'devolucoes' => 0,
                    'total' => 0,
                ];
            }
            $resumoDiario[$dia]['total']++;
            if ($t['tipo'] === 'venda') {
                $resumoDiario[$dia]['entradas'] += (float) $t['valor'];
                $resumoDiario[$dia]['vendas'] += (float) $t['valor'];
            } else {
                $resumoDiario[$dia]['saidas'] += (float) $t['valor'];
                if ($t['tipo'] === 'devolucao') {
                    $resumoDiario[$dia]['devolucoes'] += (float) $t['valor'];
                } elseif ($t['tipo'] === 'compra') {
                    $resumoDiario[$dia]['compras'] += (float) $t['valor'];
                } else {
                    $resumoDiario[$dia]['despesas'] += (float) $t['valor'];
                }
            }
        }
        ksort($resumoDiario);

        // Verificar exportação
        if (isset($_GET['exportar'])) {
            if ($_GET['exportar'] === 'excel') {
                $this->exportarMensalExcel($resumoDiario, $totais, $mes, $ano, $perfil);
                return;
            } elseif ($_GET['exportar'] === 'pdf') {
                $this->exportarMensalPDF($resumoDiario, $totais, $mes, $ano, $perfil);
                return;
            }
        }

        $this->renderizar('relatorios/mensal', [
            'tituloPagina' => 'Relatório Mensal',
            'paginaAtiva' => 'relatorios',
            'resumoDiario' => $resumoDiario,
            'totais' => $totais,
            'mes' => $mes,
            'ano' => $ano,
            'nomeMes' => $this->getNomeMes($mes),
            'diasNoMes' => cal_days_in_month(CAL_GREGORIAN, $mes, $ano),
            'perfil' => $perfil,
            'empresas' => $empresas,
            'filiais' => $filiais,
            'empresaNome' => $_SESSION['empresa_nome'] ?? '',
            'meses' => $this->getMeses(),
            'anos' => range(date('Y') - 5, date('Y')),
        ]);
    }

    /**
     * Relatório Anual
     */
    public function anual(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $ano = (int) ($_GET['ano'] ?? date('Y'));

        if ($ano < 2000 || $ano > 2100) $ano = (int) date('Y');

        // Filtros adicionais
        $empresaFiltro = isset($_GET['empresa_id']) && $_GET['empresa_id'] > 0 ? (int) $_GET['empresa_id'] : null;

        // Buscar empresas para filtro (Super Admin)
        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        // Buscar filiais
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        $resumoMensal = [];
        $totais = [
            'entradas' => 0,
            'saidas' => 0,
            'saldo' => 0,
            'vendas' => 0,
            'compras' => 0,
            'despesas' => 0,
            'devolucoes' => 0,
            'total_transacoes' => 0,
        ];

        for ($mes = 1; $mes <= 12; $mes++) {
            $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
            $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

            $empresaBusca = $perfil === 'super_admin' ? $empresaFiltro : (int) $empresaId;
            $transacoes = $this->transacaoModel->buscarComFiltros(
                $empresaBusca,
                null,
                $dataInicio,
                $dataFim,
                '',
                0,
                0,
                ''
            );

            $entradas = 0;
            $saidas = 0;
            $vendas = 0;
            $compras = 0;
            $despesas = 0;
            $devolucoes = 0;
            $total = count($transacoes);

            foreach ($transacoes as $t) {
                if ($t['tipo'] === 'venda') {
                    $entradas += (float) $t['valor'];
                    $vendas += (float) $t['valor'];
                } else {
                    $saidas += (float) $t['valor'];
                    if ($t['tipo'] === 'devolucao') {
                        $devolucoes += (float) $t['valor'];
                    } elseif ($t['tipo'] === 'compra') {
                        $compras += (float) $t['valor'];
                    } else {
                        $despesas += (float) $t['valor'];
                    }
                }
            }

            $resumoMensal[$mes] = [
                'nome' => $this->getNomeMes($mes, true),
                'mes' => $mes,
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $entradas - $saidas,
                'vendas' => $vendas,
                'compras' => $compras,
                'despesas' => $despesas,
                'devolucoes' => $devolucoes,
                'total' => $total,
            ];

            $totais['entradas'] += $entradas;
            $totais['saidas'] += $saidas;
            $totais['vendas'] += $vendas;
            $totais['compras'] += $compras;
            $totais['despesas'] += $despesas;
            $totais['devolucoes'] += $devolucoes;
            $totais['total_transacoes'] += $total;
        }
        $totais['saldo'] = $totais['entradas'] - $totais['saidas'];

        // Verificar exportação
        if (isset($_GET['exportar'])) {
            if ($_GET['exportar'] === 'excel') {
                $this->exportarAnualExcel($resumoMensal, $totais, $ano, $perfil);
                return;
            } elseif ($_GET['exportar'] === 'pdf') {
                $this->exportarAnualPDF($resumoMensal, $totais, $ano, $perfil);
                return;
            }
        }

        $this->renderizar('relatorios/anual', [
            'tituloPagina' => 'Relatório Anual',
            'paginaAtiva' => 'relatorios',
            'resumoMensal' => $resumoMensal,
            'totais' => $totais,
            'ano' => $ano,
            'perfil' => $perfil,
            'empresas' => $empresas,
            'filiais' => $filiais,
            'empresaNome' => $_SESSION['empresa_nome'] ?? '',
            'anos' => range(date('Y') - 5, date('Y')),
        ]);
    }

    /**
     * Relatório por Filial
     * Super Admin: Seleciona empresa → depois filial
     * Admin Empresa: Apenas filiais da sua empresa
     */
    public function filial(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $empresaNome = $_SESSION['empresa_nome'] ?? '';
        $filialId = (int) ($_GET['id'] ?? 0);
        $mes = (int) ($_GET['mes'] ?? date('m'));
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $empresaSelecionada = (int) ($_GET['empresa_id'] ?? 0);

        if ($mes < 1 || $mes > 12) $mes = (int) date('m');
        if ($ano < 2000 || $ano > 2100) $ano = (int) date('Y');

        // =============================================
        // SUPER ADMIN: Buscar todas as empresas e filiais
        // =============================================
        $empresas = [];
        $filiais = [];
        $filiaisTodas = [];
        $filial = null;
        $transacoes = [];
        $totais = ['entradas' => 0, 'saidas' => 0, 'saldo' => 0, 'total' => 0];

        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();

            // Lista completa (todas as empresas) — usada pelo JS para re-filtrar
            // sem precisar recarregar a página ao trocar de empresa no select
            $filiaisTodas = $this->filialModel->todas();

            // Filtrar filiais por empresa selecionada (para o select inicial)
            if ($empresaSelecionada > 0) {
                $filiais = array_values(array_filter(
                    $filiaisTodas,
                    fn($f) => (int) $f['empresa_id'] === $empresaSelecionada
                ));
            } else {
                $filiais = $filiaisTodas;
            }

            // Se houver filial selecionada, buscar dados
            if ($filialId > 0) {
                $filial = $this->filialModel->encontrarPorId($filialId);
                if ($filial) {
                    $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
                    $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

                    $transacoes = $this->transacaoModel->buscarComFiltros(
                        null,
                        null,
                        $dataInicio,
                        $dataFim,
                        '',
                        0,
                        $filialId,
                        ''
                    );

                    $totais = $this->calcularTotais($transacoes);
                    $totais['total'] = count($transacoes);
                }
            }
        } else {
            // =============================================
            // ADMIN EMPRESA: Apenas filiais da sua empresa
            // =============================================
            $empresas = [];
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
            
            if ($filialId > 0) {
                $filial = $this->filialModel->encontrarPorId($filialId);
                if ($filial && $filial['empresa_id'] == $empresaId) {
                    $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
                    $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

                    $transacoes = $this->transacaoModel->buscarComFiltros(
                        (int) $empresaId,
                        null,
                        $dataInicio,
                        $dataFim,
                        '',
                        0,
                        $filialId,
                        ''
                    );

                    $totais = $this->calcularTotais($transacoes);
                    $totais['total'] = count($transacoes);
                }
            }
        }

        $this->renderizar('relatorios/filial', [
            'tituloPagina' => 'Relatório por Filial',
            'paginaAtiva' => 'relatorios',
            'filial' => $filial,
            'filiais' => $filiais,
            'filiaisTodas' => $filiaisTodas ?? [],
            'empresas' => $empresas,
            'empresaSelecionada' => $empresaSelecionada,
            'empresaNome' => $empresaNome,
            'transacoes' => $transacoes,
            'totais' => $totais,
            'mes' => $mes,
            'ano' => $ano,
            'nomeMes' => $this->getNomeMes($mes),
            'perfil' => $perfil,
            'meses' => $this->getMeses(),
            'anos' => range(date('Y') - 5, date('Y')),
        ]);
    }

    // =============================================
    // RELATÓRIO DIÁRIO - ESTILO PLANILHA (NOVO)
    // =============================================

    /**
     * Relatório Diário no estilo da planilha Excel
     * Mostra todos os campos da planilha: TPA-BCA, TPA-Keve, Transf, etc.
     */
    public function diarioPlanilha(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;
        
        $mes = (int) ($_GET['mes'] ?? date('m'));
        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $filialId = (int) ($_GET['filial_id'] ?? 0);

        if ($mes < 1 || $mes > 12) $mes = (int) date('m');
        if ($ano < 2000 || $ano > 2100) $ano = (int) date('Y');

        // Buscar filiais
        if ($perfil === 'super_admin') {
            $filiais = $this->filialModel->todas();
        } else {
            $filiais = $this->filialModel->porEmpresa((int) $empresaId);
        }

        // Se não houver filial selecionada e houver apenas uma, selecionar automaticamente
        if ($filialId === 0 && count($filiais) === 1) {
            $filialId = (int) $filiais[0]['id'];
        }

        // Buscar nome da filial
        $filialNome = '';
        if ($filialId > 0) {
            $filial = $this->filialModel->encontrarPorId($filialId);
            $filialNome = $filial['nome'] ?? '';
        }

        // Período
        $periodoInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $periodoFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

        // A planilha adapta as colunas às categorias que tiveram movimentos no período.
        $colunasEntrada = [];
        $colunasSaida = [];
        $linhasPlanilha = [];
        $totaisEntrada = [];
        $totaisSaida = [];
        $saldoInicial = 0.0;
        $saldoFinal = 0.0;

        if ($filialId > 0) {
            $movimentos = $this->transacaoModel->buscarPlanilhaPorCategoria($filialId, $periodoInicio, $periodoFim);
            $saldoInicial = $this->transacaoModel->buscarSaldoAnteriorPlanilha($filialId, $periodoInicio);
            $saldoAtual = $saldoInicial;

            foreach ($movimentos as $movimento) {
                $categoriaId = (int) $movimento['categoria_id'];
                $tipo = $movimento['categoria_tipo'] === 'entrada' ? 'entrada' : 'saida';
                if ($tipo === 'entrada' && !isset($colunasEntrada[$categoriaId])) {
                    $colunasEntrada[$categoriaId] = ['id' => $categoriaId, 'nome' => $movimento['categoria_nome']];
                }
                if ($tipo === 'saida' && !isset($colunasSaida[$categoriaId])) {
                    $colunasSaida[$categoriaId] = ['id' => $categoriaId, 'nome' => $movimento['categoria_nome']];
                }

                $dia = (int) $movimento['dia'];
                if (!isset($linhasPlanilha[$dia])) {
                    $linhasPlanilha[$dia] = ['dia' => $dia, 'entradas' => [], 'saidas' => [], 'total_entradas' => 0.0, 'total_saidas' => 0.0, 'saldo' => 0.0];
                }
                $valor = (float) $movimento['valor'];
                $linhasPlanilha[$dia][$tipo . 's'][$categoriaId] = $valor;
                $linhasPlanilha[$dia]['total_' . $tipo . 's'] += $valor;
                if ($tipo === 'entrada') {
                    $totaisEntrada[$categoriaId] = ($totaisEntrada[$categoriaId] ?? 0) + $valor;
                } else {
                    $totaisSaida[$categoriaId] = ($totaisSaida[$categoriaId] ?? 0) + $valor;
                }
            }

            ksort($linhasPlanilha);
            foreach ($linhasPlanilha as &$linha) {
                $saldoAtual += $linha['total_entradas'] - $linha['total_saidas'];
                $linha['saldo'] = $saldoAtual;
            }
            unset($linha);
            $saldoFinal = $saldoAtual;
        }

        $this->renderizar('relatorios/diario-planilha', [
            'tituloPagina' => 'Relatório Planilha',
            'paginaAtiva' => 'planilha',
            'linhasPlanilha' => $linhasPlanilha,
            'colunasEntrada' => array_values($colunasEntrada),
            'colunasSaida' => array_values($colunasSaida),
            'totaisEntrada' => $totaisEntrada,
            'totaisSaida' => $totaisSaida,
            'saldoInicial' => $saldoInicial,
            'saldoFinal' => $saldoFinal,
            'filialId' => $filialId,
            'filialNome' => $filialNome,
            'filiais' => $filiais,
            'mes' => $mes,
            'ano' => $ano,
            'periodoInicio' => $periodoInicio,
            'periodoFim' => $periodoFim,
            'nomeMes' => $this->getNomeMes($mes),
            'resumoFiliais' => [],
            'perfil' => $perfil,
            'meses' => $this->getMeses(),
            'anos' => range(date('Y') - 5, date('Y')),
        ]);
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    private function calcularTotais($transacoes): array
    {
        $totais = [
            'vendas' => 0,
            'devolucoes' => 0,
            'compras' => 0,
            'despesas' => 0,
            'entradas' => 0,
            'saidas' => 0,
            'saldo' => 0,
        ];

        foreach ($transacoes as $t) {
            if ($t['tipo'] === 'venda') {
                $totais['entradas'] += (float) $t['valor'];
                $totais['vendas'] += (float) $t['valor'];
            } else {
                $totais['saidas'] += (float) $t['valor'];
                if ($t['tipo'] === 'devolucao') {
                    $totais['devolucoes'] += (float) $t['valor'];
                } elseif ($t['tipo'] === 'compra') {
                    $totais['compras'] += (float) $t['valor'];
                } else {
                    $totais['despesas'] += (float) $t['valor'];
                }
            }
        }
        $totais['saldo'] = $totais['entradas'] - $totais['saidas'];
        return $totais;
    }

    private function calcularResumoMensal($empresaId, $mes, $ano): array
    {
        $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

        $transacoes = $this->transacaoModel->buscarComFiltros(
            $empresaId,
            null,
            $dataInicio,
            $dataFim,
            '',
            0,
            0,
            ''
        );

        $entradas = 0;
        $saidas = 0;
        foreach ($transacoes as $t) {
            if ($t['tipo'] === 'venda') {
                $entradas += (float) $t['valor'];
            } else {
                $saidas += (float) $t['valor'];
            }
        }

        return [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'saldo' => $entradas - $saidas,
            'total' => count($transacoes),
        ];
    }

    private function calcularResumoPorEmpresa($mes, $ano): array
    {
        $empresas = $this->empresaModel->todos();
        $resumo = [];

        foreach ($empresas as $emp) {
            $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
            $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

            $transacoes = $this->transacaoModel->buscarComFiltros(
                (int) $emp['id'],
                null,
                $dataInicio,
                $dataFim,
                '',
                0,
                0,
                ''
            );

            $entradas = 0;
            $saidas = 0;
            foreach ($transacoes as $t) {
                if ($t['tipo'] === 'venda') {
                    $entradas += (float) $t['valor'];
                } else {
                    $saidas += (float) $t['valor'];
                }
            }

            $resumo[] = [
                'nome' => $emp['nome'],
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $entradas - $saidas,
                'total' => count($transacoes),
            ];
        }

        return $resumo;
    }

    private function calcularResumoPorFilial($empresaId, $mes, $ano): array
    {
        $filiais = $this->filialModel->porEmpresa($empresaId);
        $resumo = [];

        foreach ($filiais as $filial) {
            $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
            $dataFim = sprintf('%04d-%02d-%02d', $ano, $mes, cal_days_in_month(CAL_GREGORIAN, $mes, $ano));

            $transacoes = $this->transacaoModel->buscarComFiltros(
                $empresaId,
                null,
                $dataInicio,
                $dataFim,
                '',
                0,
                (int) $filial['id'],
                ''
            );

            $entradas = 0;
            $saidas = 0;
            foreach ($transacoes as $t) {
                if ($t['tipo'] === 'venda') {
                    $entradas += (float) $t['valor'];
                } else {
                    $saidas += (float) $t['valor'];
                }
            }

            $resumo[] = [
                'nome' => $filial['nome'],
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $entradas - $saidas,
                'total' => count($transacoes),
            ];
        }

        return $resumo;
    }

    private function getCorEmpresa($id): string
    {
        $cores = ['#0e2748', '#3b82f6', '#22c55e', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899', '#14b8a6'];
        return $cores[$id % count($cores)];
    }

    private function getMeses(): array
    {
        return [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
    }

    private function getNomeMes(int $mes, bool $abreviado = false): string
    {
        $nomes = $this->getMeses();
        $abreviados = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];
        return $abreviado ? ($abreviados[$mes] ?? $mes) : ($nomes[$mes] ?? $mes);
    }

    protected function setFlash($tipo, $mensagem)
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    protected function redirecionar($rota)
    {
        header('Location: ' . URL_BASE . '/' . ltrim($rota, '/'));
        exit;
    }

    // =============================================
    // EXPORTAÇÕES
    // =============================================

    private function exportarDiarioExcel($transacoes, $totais, $data, $perfil)
    {
        $colunas = ['Data', 'Descrição', 'Tipo', 'Valor (Kz)', 'Filial', 'Utilizador'];
        if ($perfil === 'super_admin') {
            array_splice($colunas, 1, 0, ['Empresa']);
        }

        $linhas = [];
        foreach ($transacoes as $t) {
            $linha = [date('d/m/Y', strtotime($t['data_transacao']))];
            if ($perfil === 'super_admin') {
                $linha[] = $t['empresa_nome'] ?? 'N/A';
            }
            $linha[] = $t['descricao'] ?? '-';
            $linha[] = ucfirst($t['tipo']);
            $linha[] = ['valor' => (float) $t['valor'], 'tipo' => 'moeda', 'estilo' => $t['tipo'] === 'venda' ? 'positivo' : 'negativo'];
            $linha[] = $t['filial_nome'] ?? '-';
            $linha[] = $t['usuario_nome'] ?? '-';
            $linhas[] = $linha;
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioExcelBuilder('Relatório Diário - ' . date('d/m/Y', strtotime($data)), 'relatorio_diario_' . $data))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . date('d/m/Y', strtotime($data)) . '  •  Total: ' . count($transacoes) . ' transações')
            ->definirNomeFolha('Diário')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirResumo([
                ['rotulo' => 'Total Entradas', 'valor' => $totais['entradas'], 'estilo' => 'positivo'],
                ['rotulo' => 'Total Saídas', 'valor' => $totais['saidas'], 'estilo' => 'negativo'],
                ['rotulo' => 'Saldo', 'valor' => $totais['saldo'], 'estilo' => $totais['saldo'] >= 0 ? 'positivo' : 'negativo'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function exportarDiarioPDF($transacoes, $totais, $data, $perfil)
    {
        $colunas = ['Data', 'Descrição', 'Tipo', 'Valor (Kz)', 'Filial', 'Utilizador'];
        if ($perfil === 'super_admin') {
            array_splice($colunas, 1, 0, ['Empresa']);
        }

        $linhas = [];
        foreach ($transacoes as $t) {
            $linha = [
                ['texto' => date('d/m/Y', strtotime($t['data_transacao']))],
            ];
            if ($perfil === 'super_admin') {
                $linha[] = ['texto' => $t['empresa_nome'] ?? 'N/A'];
            }
            $linha[] = ['texto' => $t['descricao'] ?? '-'];
            $linha[] = ['texto' => ucfirst($t['tipo']), 'tipo' => 'badge', 'badge_classe' => $t['tipo'] === 'venda' ? 'badge-sucesso' : 'badge-perigo'];
            $linha[] = ['texto' => number_format((float) $t['valor'], 0, ',', '.'), 'classe' => $t['tipo'] === 'venda' ? 'positivo' : 'negativo', 'alinhar' => 'direita'];
            $linha[] = ['texto' => $t['filial_nome'] ?? '-'];
            $linha[] = ['texto' => $t['usuario_nome'] ?? '-'];
            $linhas[] = $linha;
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioPdfBuilder('Relatório Diário - ' . date('d/m/Y', strtotime($data)), 'relatorio_diario_' . $data))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . date('d/m/Y', strtotime($data)) . '  •  Total: ' . count($transacoes) . ' transações')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirCartoesResumo([
                ['rotulo' => 'Total Entradas', 'valor' => number_format($totais['entradas'], 0, ',', '.') . ' Kz', 'cor' => 'verde'],
                ['rotulo' => 'Total Saídas', 'valor' => number_format($totais['saidas'], 0, ',', '.') . ' Kz', 'cor' => 'vermelho'],
                ['rotulo' => 'Saldo', 'valor' => number_format($totais['saldo'], 0, ',', '.') . ' Kz', 'cor' => $totais['saldo'] >= 0 ? 'verde' : 'vermelho'],
                ['rotulo' => 'Transações', 'valor' => (string) count($transacoes), 'cor' => 'navy'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function exportarMensalExcel($resumoDiario, $totais, $mes, $ano, $perfil)
    {
        // Similar ao diário mas com estrutura mensal
        $colunas = ['Dia', 'Entradas', 'Saídas', 'Saldo', 'Vendas', 'Compras', 'Despesas', 'Devoluções', 'Total'];
        
        $linhas = [];
        foreach ($resumoDiario as $dia) {
            $linhas[] = [
                $dia['dia'],
                ['valor' => $dia['entradas'], 'tipo' => 'moeda', 'estilo' => 'positivo'],
                ['valor' => $dia['saidas'], 'tipo' => 'moeda', 'estilo' => 'negativo'],
                ['valor' => $dia['entradas'] - $dia['saidas'], 'tipo' => 'moeda', 'estilo' => ($dia['entradas'] - $dia['saidas']) >= 0 ? 'positivo' : 'negativo'],
                ['valor' => $dia['vendas'], 'tipo' => 'moeda'],
                ['valor' => $dia['compras'], 'tipo' => 'moeda'],
                ['valor' => $dia['despesas'], 'tipo' => 'moeda'],
                ['valor' => $dia['devolucoes'], 'tipo' => 'moeda'],
                $dia['total'],
            ];
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioExcelBuilder('Relatório Mensal - ' . $this->getNomeMes($mes) . '/' . $ano, 'relatorio_mensal_' . $ano . '_' . $mes))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . $this->getNomeMes($mes) . '/' . $ano . '  •  Total: ' . $totais['total_transacoes'] . ' transações')
            ->definirNomeFolha('Mensal')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirResumo([
                ['rotulo' => 'Total Entradas', 'valor' => $totais['entradas'], 'estilo' => 'positivo'],
                ['rotulo' => 'Total Saídas', 'valor' => $totais['saidas'], 'estilo' => 'negativo'],
                ['rotulo' => 'Saldo', 'valor' => $totais['saldo'], 'estilo' => $totais['saldo'] >= 0 ? 'positivo' : 'negativo'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function exportarMensalPDF($resumoDiario, $totais, $mes, $ano, $perfil)
    {
        $colunas = ['Dia', 'Entradas', 'Saídas', 'Saldo', 'Vendas', 'Compras', 'Despesas', 'Devoluções', 'Total'];
        
        $linhas = [];
        foreach ($resumoDiario as $dia) {
            $linhas[] = [
                ['texto' => (string) $dia['dia'], 'alinhar' => 'centro'],
                ['texto' => number_format($dia['entradas'], 0, ',', '.'), 'classe' => 'positivo', 'alinhar' => 'direita'],
                ['texto' => number_format($dia['saidas'], 0, ',', '.'), 'classe' => 'negativo', 'alinhar' => 'direita'],
                ['texto' => number_format($dia['entradas'] - $dia['saidas'], 0, ',', '.'), 'classe' => ($dia['entradas'] - $dia['saidas']) >= 0 ? 'positivo' : 'negativo', 'alinhar' => 'direita'],
                ['texto' => number_format($dia['vendas'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($dia['compras'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($dia['despesas'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($dia['devolucoes'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => (string) $dia['total'], 'alinhar' => 'centro'],
            ];
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioPdfBuilder('Relatório Mensal - ' . $this->getNomeMes($mes) . '/' . $ano, 'relatorio_mensal_' . $ano . '_' . $mes))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . $this->getNomeMes($mes) . '/' . $ano . '  •  Total: ' . $totais['total_transacoes'] . ' transações')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirCartoesResumo([
                ['rotulo' => 'Total Entradas', 'valor' => number_format($totais['entradas'], 0, ',', '.') . ' Kz', 'cor' => 'verde'],
                ['rotulo' => 'Total Saídas', 'valor' => number_format($totais['saidas'], 0, ',', '.') . ' Kz', 'cor' => 'vermelho'],
                ['rotulo' => 'Saldo', 'valor' => number_format($totais['saldo'], 0, ',', '.') . ' Kz', 'cor' => $totais['saldo'] >= 0 ? 'verde' : 'vermelho'],
                ['rotulo' => 'Transações', 'valor' => (string) $totais['total_transacoes'], 'cor' => 'navy'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function exportarAnualExcel($resumoMensal, $totais, $ano, $perfil)
    {
        $colunas = ['Mês', 'Entradas', 'Saídas', 'Saldo', 'Vendas', 'Compras', 'Despesas', 'Devoluções', 'Total'];
        
        $linhas = [];
        foreach ($resumoMensal as $mes) {
            $linhas[] = [
                $mes['nome'],
                ['valor' => $mes['entradas'], 'tipo' => 'moeda', 'estilo' => 'positivo'],
                ['valor' => $mes['saidas'], 'tipo' => 'moeda', 'estilo' => 'negativo'],
                ['valor' => $mes['saldo'], 'tipo' => 'moeda', 'estilo' => $mes['saldo'] >= 0 ? 'positivo' : 'negativo'],
                ['valor' => $mes['vendas'], 'tipo' => 'moeda'],
                ['valor' => $mes['compras'], 'tipo' => 'moeda'],
                ['valor' => $mes['despesas'], 'tipo' => 'moeda'],
                ['valor' => $mes['devolucoes'], 'tipo' => 'moeda'],
                $mes['total'],
            ];
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioExcelBuilder('Relatório Anual - ' . $ano, 'relatorio_anual_' . $ano))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . $ano . '  •  Total: ' . $totais['total_transacoes'] . ' transações')
            ->definirNomeFolha('Anual')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirResumo([
                ['rotulo' => 'Total Entradas', 'valor' => $totais['entradas'], 'estilo' => 'positivo'],
                ['rotulo' => 'Total Saídas', 'valor' => $totais['saidas'], 'estilo' => 'negativo'],
                ['rotulo' => 'Saldo', 'valor' => $totais['saldo'], 'estilo' => $totais['saldo'] >= 0 ? 'positivo' : 'negativo'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function exportarAnualPDF($resumoMensal, $totais, $ano, $perfil)
    {
        $colunas = ['Mês', 'Entradas', 'Saídas', 'Saldo', 'Vendas', 'Compras', 'Despesas', 'Devoluções', 'Total'];
        
        $linhas = [];
        foreach ($resumoMensal as $mes) {
            $linhas[] = [
                ['texto' => $mes['nome']],
                ['texto' => number_format($mes['entradas'], 0, ',', '.'), 'classe' => 'positivo', 'alinhar' => 'direita'],
                ['texto' => number_format($mes['saidas'], 0, ',', '.'), 'classe' => 'negativo', 'alinhar' => 'direita'],
                ['texto' => number_format($mes['saldo'], 0, ',', '.'), 'classe' => $mes['saldo'] >= 0 ? 'positivo' : 'negativo', 'alinhar' => 'direita'],
                ['texto' => number_format($mes['vendas'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($mes['compras'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($mes['despesas'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => number_format($mes['devolucoes'], 0, ',', '.'), 'alinhar' => 'direita'],
                ['texto' => (string) $mes['total'], 'alinhar' => 'centro'],
            ];
        }

        $empresa = $this->buscarEmpresa();

        (new RelatorioPdfBuilder('Relatório Anual - ' . $ano, 'relatorio_anual_' . $ano))
            ->definirEmpresa($empresa)
            ->definirSubtitulo('Período: ' . $ano . '  •  Total: ' . $totais['total_transacoes'] . ' transações')
            ->definirColunas($colunas)
            ->definirLinhas($linhas)
            ->definirCartoesResumo([
                ['rotulo' => 'Total Entradas', 'valor' => number_format($totais['entradas'], 0, ',', '.') . ' Kz', 'cor' => 'verde'],
                ['rotulo' => 'Total Saídas', 'valor' => number_format($totais['saidas'], 0, ',', '.') . ' Kz', 'cor' => 'vermelho'],
                ['rotulo' => 'Saldo', 'valor' => number_format($totais['saldo'], 0, ',', '.') . ' Kz', 'cor' => $totais['saldo'] >= 0 ? 'verde' : 'vermelho'],
                ['rotulo' => 'Transações', 'valor' => (string) $totais['total_transacoes'], 'cor' => 'navy'],
            ])
            ->definirOrientacao('landscape')
            ->stream();
    }

    private function buscarEmpresa(): array
    {
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $empresa = [];
        if ($empresaId) {
            $dados = $this->empresaModel->encontrarPorId((int) $empresaId);
            if ($dados) {
                $empresa = [
                    'nome' => $dados['nome'] ?? '',
                    'nif' => $dados['nif'] ?? '',
                    'endereco' => $dados['endereco'] ?? '',
                ];
            }
        }
        return $empresa;
    }
}
