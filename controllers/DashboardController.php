<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';

class DashboardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirecionar('auth/login');
        }

        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        // Determinar o período com base no parâmetro GET
        $periodo = $_GET['periodo'] ?? 'mes';
        $periodoLabel = $this->getPeriodoLabel($periodo);
        $datas = $this->getDatasPeriodo($periodo);

        if ($perfil === 'super_admin') {
            $this->superAdmin($periodo, $datas, $periodoLabel);
        } else {
            $this->adminEmpresa($periodo, $datas, $periodoLabel);
        }
    }

    private function getPeriodoLabel($periodo)
    {
        $labels = [
            'hoje' => 'Hoje',
            'semana' => 'Esta Semana',
            'mes' => 'Este Mês',
            'ano' => 'Este Ano'
        ];
        return $labels[$periodo] ?? 'Este Mês';
    }

    private function getDatasPeriodo($periodo)
    {
        $hoje = date('Y-m-d');
        
        switch ($periodo) {
            case 'hoje':
                return ['inicio' => $hoje, 'fim' => $hoje];
                
            case 'semana':
                $inicio = date('Y-m-d', strtotime('monday this week'));
                $fim = date('Y-m-d', strtotime('sunday this week'));
                return ['inicio' => $inicio, 'fim' => $fim];
                
            case 'mes':
                return ['inicio' => date('Y-m-01'), 'fim' => date('Y-m-t')];
                
            case 'ano':
                return ['inicio' => date('Y-01-01'), 'fim' => date('Y-12-31')];
                
            default:
                return ['inicio' => date('Y-m-01'), 'fim' => date('Y-m-t')];
        }
    }

    private function superAdmin($periodo, $datas, $periodoLabel)
    {
        // Buscar nome da empresa (para o Super Admin, mostra "Todas as Empresas" ou vazio)
        $empresa_nome = 'Todas as Empresas';

        $dados = [
            'tituloPagina' => 'Super Admin',
            'paginaAtiva' => 'dashboard',
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'periodoInicio' => $datas['inicio'],
            'periodoFim' => $datas['fim'],
            'empresa_nome' => $empresa_nome, // <-- ADICIONADO
            
            // KPIs (valores de exemplo)
            'total_empresas' => 5,
            'novas_empresas' => 2,
            'total_filiais' => 12,
            'novas_filiais' => 3,
            'total_usuarios' => 45,
            'novos_usuarios' => 8,
            'vendas_totais' => 62182000,
            'crescimento_vendas' => 15.2,
            'resultado_global' => 21630600,
            'crescimento_resultado' => 12.8,
            'total_despesas' => 4825000,
            'crescimento_global' => 10,
            
            // Resumo empresas
            'resumo_empresas' => [
                ['nome' => 'Farmácia BESTON', 'filiais' => 2, 'usuarios' => 12, 'vendas' => 15450000, 'despesas' => 1125000, 'saldo' => 7725000, 'crescimento' => 12, 'percentual' => 85, 'cor' => '#0e2748'],
                ['nome' => 'Farma+ Saúde', 'filiais' => 3, 'usuarios' => 8, 'vendas' => 12800000, 'despesas' => 980000, 'saldo' => 5200000, 'crescimento' => 8, 'percentual' => 95, 'cor' => '#3b82f6'],
                ['nome' => 'Saúde Total', 'filiais' => 4, 'usuarios' => 15, 'vendas' => 18900000, 'despesas' => 1450000, 'saldo' => 8900000, 'crescimento' => 15, 'percentual' => 72, 'cor' => '#22c55e'],
                ['nome' => 'Medifarma', 'filiais' => 2, 'usuarios' => 6, 'vendas' => 9200000, 'despesas' => 820000, 'saldo' => 3100000, 'crescimento' => -2, 'percentual' => 78, 'cor' => '#8b5cf6'],
                ['nome' => 'Vitalis', 'filiais' => 1, 'usuarios' => 4, 'vendas' => 5850000, 'despesas' => 450000, 'saldo' => 2700000, 'crescimento' => 5, 'percentual' => 55, 'cor' => '#f59e0b'],
            ],
            
            'metodos_pagamento' => [
                ['nome' => 'Dinheiro', 'valor' => 5150000, 'cor' => '#22c55e', 'percentual' => 33.3],
                ['nome' => 'TPA BCA', 'valor' => 4600000, 'cor' => '#3b82f6', 'percentual' => 29.8],
                ['nome' => 'TPA Keve', 'valor' => 3200000, 'cor' => '#f59e0b', 'percentual' => 20.7],
                ['nome' => 'Transferência', 'valor' => 2500000, 'cor' => '#8b5cf6', 'percentual' => 16.2],
            ],
            
            'atividades_recentes' => [
                ['nome' => 'João Silva', 'empresa' => 'Farmácia BESTON', 'tempo' => 'há 2 min', 'cor' => '#3b82f6'],
                ['nome' => 'Maria Santos', 'empresa' => 'Farma+ Saúde', 'tempo' => 'há 15 min', 'cor' => '#22c55e'],
                ['nome' => 'Carlos Pereira', 'empresa' => 'Saúde Total', 'tempo' => 'há 1 hora', 'cor' => '#8b5cf6'],
                ['nome' => 'Ana Paula', 'empresa' => 'Medifarma', 'tempo' => 'há 3 horas', 'cor' => '#f59e0b'],
                ['nome' => 'Ricardo Mendes', 'empresa' => 'Vitalis', 'tempo' => 'há 5 horas', 'cor' => '#ef4444'],
            ],
            
            'alertas' => [
                ['tipo' => 'critico', 'icone' => 'circle-exclamation', 'titulo' => 'Fecho diário pendente', 'descricao' => '3 empresas não realizaram o fecho diário de ontem'],
                ['tipo' => 'aviso', 'icone' => 'triangle-exclamation', 'titulo' => 'Utilizadores inativos', 'descricao' => '2 utilizadores estão inativos há mais de 30 dias'],
                ['tipo' => 'sucesso', 'icone' => 'circle-check', 'titulo' => 'Importação concluída', 'descricao' => 'Todas as importações de Excel foram processadas com sucesso'],
            ],
            
            'datasets_vendas' => [
                ['label' => 'Farmácia BESTON', 'data' => [4500000, 5200000, 4800000, 6100000, 5900000, 6500000, 6200000, 7800000, 7100000, 8500000, 8200000, 9100000], 'backgroundColor' => '#0e2748', 'borderRadius' => 4],
                ['label' => 'Farma+ Saúde', 'data' => [3200000, 3800000, 3500000, 4200000, 4000000, 4800000, 4500000, 5200000, 4900000, 5800000, 5500000, 6200000], 'backgroundColor' => '#3b82f6', 'borderRadius' => 4],
                ['label' => 'Saúde Total', 'data' => [5100000, 5800000, 5300000, 6200000, 5900000, 6800000, 6400000, 7200000, 6900000, 8100000, 7700000, 8500000], 'backgroundColor' => '#22c55e', 'borderRadius' => 4],
            ]
        ];

        $this->renderizar('dashboard/superadmin', $dados);
    }

    private function adminEmpresa($periodo, $datas, $periodoLabel)
    {
        // =============================================
        // BUSCAR O NOME DA EMPRESA REAL DO UTILIZADOR
        // =============================================
        $empresaId = $_SESSION['empresa_id'] ?? null;
        $empresaNome = 'Farmácia BESTON'; // fallback

        if ($empresaId) {
            $empresaModel = new Empresa();
            $empresa = $empresaModel->encontrarPorId($empresaId);
            if ($empresa) {
                $empresaNome = $empresa['nome'];
            }
        }

        $dados = [
            'tituloPagina' => 'Admin Empresa',
            'paginaAtiva' => 'dashboard',
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'periodoInicio' => $datas['inicio'],
            'periodoFim' => $datas['fim'],
            
            // =============================================
            // EMPRESA_NOME - AGORA VEM DA BASE DE DADOS
            // =============================================
            'empresa_nome' => $empresaNome,
            
            'nome_empresa' => $empresaNome,
            'total_filiais' => 2,
            'novas_filiais' => 0,
            'total_usuarios' => 12,
            'novos_usuarios' => 2,
            'vendas_totais' => 15450000,
            'crescimento_vendas' => 12.5,
            'despesas_totais' => 1125000,
            'crescimento_despesas' => 4.3,
            'resultado_liquido' => 7725000,
            'crescimento_resultado' => 15.2,
            'ticket_medio' => 12500,
            'crescimento_ticket' => 2.1,
            'crescimento_geral' => 10,
            
            'resumo_filiais' => [
                ['nome' => 'Prenda', 'utilizadores' => 8, 'vendas' => 8650000, 'despesas' => 625000, 'saldo' => 4925000, 'crescimento' => 12, 'status' => 'ativo'],
                ['nome' => 'Viana', 'utilizadores' => 4, 'vendas' => 6800000, 'despesas' => 500000, 'saldo' => 2800000, 'crescimento' => 8, 'status' => 'ativo'],
            ],
            
            'vendas_por_filial' => [
                ['nome' => 'Prenda', 'vendas' => 8650000, 'cor' => '#0e2748', 'percentual' => 56],
                ['nome' => 'Viana', 'vendas' => 6800000, 'cor' => '#3b82f6', 'percentual' => 44],
            ],
            
            'vendas_categorias' => [
                ['nome' => 'Medicamentos', 'percentual' => 65, 'cor' => '#22c55e'],
                ['nome' => 'Cosméticos', 'percentual' => 28, 'cor' => '#3b82f6'],
                ['nome' => 'Higiene', 'percentual' => 7, 'cor' => '#8b5cf6'],
            ],
            
            'despesas_categorias' => [
                ['nome' => 'Compras', 'percentual' => 52, 'cor' => '#f59e0b'],
                ['nome' => 'Salários', 'percentual' => 30, 'cor' => '#ef4444'],
                ['nome' => 'Renda', 'percentual' => 12, 'cor' => '#8b5cf6'],
                ['nome' => 'Energia/Água', 'percentual' => 6, 'cor' => '#3b82f6'],
            ],
            
            'movimentos_recentes' => [
                ['tipo' => 'venda', 'data' => '31/05', 'descricao' => 'Venda - TPA BCA', 'filial' => 'Prenda', 'valor' => 250000],
                ['tipo' => 'venda', 'data' => '30/05', 'descricao' => 'Venda - Dinheiro', 'filial' => 'Viana', 'valor' => 180000],
                ['tipo' => 'custo', 'data' => '30/05', 'descricao' => 'Despesa - Água/Luz', 'filial' => 'Prenda', 'valor' => -75000],
                ['tipo' => 'compra', 'data' => '29/05', 'descricao' => 'Compra - Farmaluz', 'filial' => 'Prenda', 'valor' => -450000],
                ['tipo' => 'venda', 'data' => '29/05', 'descricao' => 'Venda - TPA Keve', 'filial' => 'Viana', 'valor' => 120000],
            ],
            
            'datasets_filiais' => [
                ['label' => 'Prenda', 'data' => [420000, 380000, 450000, 520000, 490000, 580000, 550000, 620000, 590000, 680000, 650000, 720000], 'borderColor' => '#0e2748', 'backgroundColor' => 'rgba(14,39,72,0.1)', 'fill' => true, 'tension' => 0.4],
                ['label' => 'Viana', 'data' => [310000, 290000, 350000, 410000, 380000, 460000, 420000, 490000, 460000, 530000, 500000, 560000], 'borderColor' => '#3b82f6', 'backgroundColor' => 'rgba(59,130,246,0.1)', 'fill' => true, 'tension' => 0.4],
            ]
        ];

        $this->renderizar('dashboard/adminempresa', $dados);
    }
}