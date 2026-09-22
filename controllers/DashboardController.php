<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/DashboardFinanceiro.php';

class DashboardController extends Controller
{
    private DashboardFinanceiro $financeiro;

    public function __construct() { $this->financeiro = new DashboardFinanceiro(); }

    public function index(): void
    {
        if (empty($_SESSION['usuario_id'])) { $this->redirecionar('auth/login'); }
        // Ler o parâmetro uma única vez: o operador ternário anterior validava
        // o valor padrão, mas tentava acessar $_GET['periodo'] novamente no
        // ramo verdadeiro quando o parâmetro não existia.
        $periodoSolicitado = $_GET['periodo'] ?? 'mes';
        $periodo = in_array($periodoSolicitado, ['hoje', 'semana', 'mes', 'ano'], true)
            ? $periodoSolicitado
            : 'mes';
        $datas = $this->datasPeriodo($periodo);
        if (($_SESSION['usuario_perfil'] ?? '') === 'super_admin') {
            $this->superAdmin($periodo, $datas);
            return;
        }
        $this->adminEmpresa($periodo, $datas, (int) ($_SESSION['empresa_id'] ?? 0));
    }

    private function superAdmin(string $periodo, array $datas): void
    {
        $totais = $this->normalizar($this->financeiro->totais($datas['inicio'], $datas['fim']));
        $empresas = $this->financeiro->porEmpresa($datas['inicio'], $datas['fim']);
        $cores = ['#0e2748', '#3b82f6', '#22c55e', '#8b5cf6', '#f59e0b'];
        $resumo = array_map(function ($empresa, $i) use ($cores) {
            $saldo = (float) $empresa['vendas'] - (float) $empresa['saidas'];
            return ['nome' => $empresa['nome'], 'filiais' => $empresa['filiais'], 'usuarios' => $empresa['usuarios'], 'vendas' => (float) $empresa['vendas'], 'despesas' => (float) $empresa['saidas'], 'saldo' => $saldo, 'crescimento' => 0, 'percentual' => 0, 'cor' => $cores[$i % count($cores)]];
        }, $empresas, array_keys($empresas));
        $this->renderizar('dashboard/superadmin', array_merge($this->baseDados($periodo, $datas), [
            'total_empresas' => count((new Empresa())->todos()), 'novas_empresas' => 0, 'total_filiais' => $this->contar('filiais'), 'novas_filiais' => 0,
            'total_usuarios' => $this->contar('usuarios'), 'novos_usuarios' => 0, 'vendas_totais' => $totais['vendas'], 'crescimento_vendas' => 0,
            'resultado_global' => $totais['saldo'], 'crescimento_resultado' => 0, 'total_despesas' => $totais['compras'] + $totais['custos'], 'crescimento_global' => 0,
            'resumo_empresas' => $resumo, 'metodos_pagamento' => [], 'atividades_recentes' => $this->atividades(null), 'alertas' => [],
            'datasets_vendas' => $this->datasetsEmpresas($empresas, $cores),
        ]));
    }

    private function adminEmpresa(string $periodo, array $datas, int $empresaId): void
    {
        $empresa = $empresaId ? (new Empresa())->encontrarPorId($empresaId) : false;
        $totais = $this->normalizar($this->financeiro->totais($datas['inicio'], $datas['fim'], $empresaId));
        $filiais = $this->financeiro->porFilial($empresaId, $datas['inicio'], $datas['fim']);
        $cores = ['#0e2748', '#3b82f6', '#22c55e', '#8b5cf6'];
        $resumo = array_map(function ($filial) {
            return ['nome' => $filial['nome'], 'utilizadores' => $filial['usuarios'], 'vendas' => (float) $filial['vendas'], 'despesas' => (float) $filial['saidas'], 'saldo' => (float) $filial['vendas'] - (float) $filial['saidas'], 'crescimento' => 0, 'status' => 'ativo'];
        }, $filiais);
        $vendasPorFilial = array_map(fn($f, $i) => ['nome' => $f['nome'], 'vendas' => (float) $f['vendas'], 'cor' => $cores[$i % count($cores)], 'percentual' => $totais['vendas'] ? round($f['vendas'] * 100 / $totais['vendas'], 1) : 0], $filiais, array_keys($filiais));
        $this->renderizar('dashboard/adminempresa', array_merge($this->baseDados($periodo, $datas), [
            'nome_empresa' => $empresa['nome'] ?? 'Empresa', 'total_filiais' => count($filiais), 'novas_filiais' => 0, 'total_usuarios' => $this->contar('usuarios', $empresaId), 'novos_usuarios' => 0,
            'vendas_totais' => $totais['vendas'], 'crescimento_vendas' => 0, 'despesas_totais' => $totais['compras'] + $totais['custos'], 'crescimento_despesas' => 0,
            'resultado_liquido' => $totais['saldo'], 'crescimento_resultado' => 0, 'ticket_medio' => $totais['total'] ? $totais['vendas'] / $totais['total'] : 0, 'crescimento_ticket' => 0, 'crescimento_geral' => 0,
            'resumo_filiais' => $resumo, 'vendas_por_filial' => $vendasPorFilial,
            'vendas_categorias' => $this->categorias($empresaId, $datas, ['venda'], '#22c55e'), 'despesas_categorias' => $this->categorias($empresaId, $datas, ['compra', 'custo'], '#ef4444'),
            'movimentos_recentes' => $this->financeiro->recentes($empresaId), 'datasets_filiais' => $this->datasetsFiliais($filiais, $cores),
        ]));
    }

    private function normalizar(array $t): array { $t = array_map('floatval', $t); $t['saldo'] = $t['vendas'] - ($t['compras'] + $t['custos'] + $t['devolucoes']); return $t; }
    private function baseDados(string $periodo, array $datas): array { return ['tituloPagina' => 'Dashboard', 'paginaAtiva' => 'dashboard', 'periodo' => $periodo, 'periodoLabel' => ['hoje'=>'Hoje','semana'=>'Esta Semana','mes'=>'Este Mês','ano'=>'Este Ano'][$periodo], 'periodoInicio' => $datas['inicio'], 'periodoFim' => $datas['fim']]; }
    private function datasPeriodo(string $p): array { $h = date('Y-m-d'); return match($p) {'hoje'=>['inicio'=>$h,'fim'=>$h], 'semana'=>['inicio'=>date('Y-m-d', strtotime('monday this week')),'fim'=>date('Y-m-d', strtotime('sunday this week'))], 'ano'=>['inicio'=>date('Y-01-01'),'fim'=>date('Y-12-31')], default=>['inicio'=>date('Y-m-01'),'fim'=>date('Y-m-t')]}; }
    private function contar(string $tabela, ?int $empresaId = null): int { $bd = Database::obterLigacao(); $sql = "SELECT COUNT(*) FROM $tabela" . ($empresaId ? ' WHERE empresa_id = :id' : ''); $s = $bd->prepare($sql); $s->execute($empresaId ? ['id'=>$empresaId] : []); return (int) $s->fetchColumn(); }
    private function atividades(?int $empresaId): array { return array_map(fn($t) => ['nome'=>$t['usuario'] ?? 'Utilizador', 'empresa'=>$t['empresa'] ?? '', 'tempo'=>date('d/m H:i', strtotime($t['data_transacao'])), 'cor'=>'#3b82f6'], $this->financeiro->recentes($empresaId)); }
    private function categorias(int $empresaId, array $datas, array $tipos, string $cor): array { $dados=$this->financeiro->porCategoria($empresaId,$datas['inicio'],$datas['fim'],$tipos); $total=array_sum(array_column($dados,'valor')); return array_map(fn($d)=>['nome'=>$d['nome'],'percentual'=>$total ? round($d['valor']*100/$total,1):0,'cor'=>$cor],$dados); }
    private function datasetsEmpresas(array $empresas, array $cores): array { return array_map(fn($e,$i)=>['label'=>$e['nome'],'data'=>array_fill(0,12,(float)$e['vendas']),'backgroundColor'=>$cores[$i%count($cores)],'borderRadius'=>4],$empresas,array_keys($empresas)); }
    private function datasetsFiliais(array $filiais, array $cores): array { return array_map(fn($f,$i)=>['label'=>$f['nome'],'data'=>array_fill(0,12,(float)$f['vendas']),'borderColor'=>$cores[$i%count($cores)],'backgroundColor'=>'rgba(59,130,246,0.1)','fill'=>true,'tension'=>0.4],$filiais,array_keys($filiais)); }
}
