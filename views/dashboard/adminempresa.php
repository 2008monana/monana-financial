<?php
/**
 * Dashboard do Administrador da Empresa
 */
?>

<div class="dash-header">
    <div class="dash-header-left">
        <h1 class="dash-title">Painel da Empresa</h1>
        <p class="dash-subtitle">
            <span class="empresa-badge"><i class="fas fa-building"></i> <?php echo htmlspecialchars($nome_empresa); ?></span>
            <span style="margin-left: 12px; font-size: 13px; color: var(--muted);">
                <i class="fa-regular fa-calendar"></i> 
                <?php echo date('d/m/Y', strtotime($periodoInicio)); ?> — <?php echo date('d/m/Y', strtotime($periodoFim)); ?>
            </span>
        </p>
    </div>
    <div class="dash-header-right">
        <div class="period-selector">
            <a href="?periodo=hoje" class="period-btn <?php echo $periodo === 'hoje' ? 'active' : ''; ?>" title="Mostrar dados de hoje">
                <i class="fa-regular fa-sun"></i> <span>Hoje</span>
            </a>
            <a href="?periodo=semana" class="period-btn <?php echo $periodo === 'semana' ? 'active' : ''; ?>" title="Mostrar dados desta semana">
                <i class="fa-regular fa-calendar-week"></i> <span>Semana</span>
            </a>
            <a href="?periodo=mes" class="period-btn <?php echo $periodo === 'mes' ? 'active-period' : ''; ?>" title="Mostrar dados deste mês">
                <i class="fa-regular fa-calendar"></i> <span>Mês</span>
            </a>
            <a href="?periodo=ano" class="period-btn <?php echo $periodo === 'ano' ? 'active' : ''; ?>" title="Mostrar dados deste ano">
                <i class="fa-regular fa-calendar-alt"></i> <span>Ano</span>
            </a>
        </div>
    </div>
</div>

<!-- KPI CARDS -->
<div class="kpi-grid kpi-grid-6">
    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon navy"><i class="fas fa-store-alt"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Filiais</span>
            <span class="kpi-value"><?php echo $total_filiais; ?></span>
            <span class="kpi-change neutral"><i class="fas fa-minus"></i> <?php echo $novas_filiais; ?> este mês</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Utilizadores</span>
            <span class="kpi-value"><?php echo $total_usuarios; ?></span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $novos_usuarios; ?> este mês</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon green"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Vendas Totais</span>
            <span class="kpi-value"><?php echo number_format($vendas_totais, 0, ',', '.'); ?> Kz</span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_vendas; ?>%</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon red"><i class="fas fa-file-invoice-dollar"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Despesas Totais</span>
            <span class="kpi-value"><?php echo number_format($despesas_totais, 0, ',', '.'); ?> Kz</span>
            <span class="kpi-change <?php echo $crescimento_despesas >= 0 ? 'up' : 'down'; ?>">
                <i class="fas fa-<?php echo $crescimento_despesas >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                <?php echo abs($crescimento_despesas); ?>%
            </span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon gold"><i class="fas fa-coins"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Resultado Líquido</span>
            <span class="kpi-value"><?php echo number_format($resultado_liquido, 0, ',', '.'); ?> Kz</span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_resultado; ?>%</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon blue"><i class="fas fa-ticket-alt"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Ticket Médio</span>
            <span class="kpi-value"><?php echo number_format($ticket_medio, 0, ',', '.'); ?> Kz</span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_ticket; ?>%</span>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="charts-row">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-line"></i> Evolução de Vendas por Filial</h3>
            <span class="chart-period"><?php echo $periodoLabel; ?></span>
        </div>
        <div class="chart-body">
            <canvas id="graficoVendasFilial"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-pie"></i> Distribuição de Vendas por Filial</h3>
        </div>
        <div class="chart-body donut-body">
            <div class="donut-container">
                <canvas id="graficoDistribuicao"></canvas>
            </div>
            <div class="donut-legend">
                <?php foreach ($vendas_por_filial as $filial): ?>
                <div class="legend-item">
                    <span class="legend-dot" style="background:<?php echo $filial['cor']; ?>"></span>
                    <span class="legend-label"><?php echo htmlspecialchars($filial['nome']); ?></span>
                    <span class="legend-value"><?php echo number_format($filial['vendas'], 0, ',', '.'); ?> Kz</span>
                    <span class="legend-pct"><?php echo $filial['percentual']; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- TABELA RESUMO POR FILIAL -->
<div class="table-card">
    <div class="table-header">
        <h3><i class="fas fa-list-ul"></i> Resumo por Filial</h3>
        <a href="#" class="btn-link">Ver todas <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Filial</th>
                    <th class="text-center">Utilizadores</th>
                    <th class="text-right">Vendas</th>
                    <th class="text-right">Despesas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-center">Crescimento</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumo_filiais as $filial): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($filial['nome']); ?></strong></td>
                    <td class="text-center"><?php echo $filial['utilizadores']; ?></td>
                    <td class="text-right"><?php echo number_format($filial['vendas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right"><?php echo number_format($filial['despesas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right <?php echo $filial['saldo'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($filial['saldo'], 0, ',', '.'); ?> Kz
                    </td>
                    <td class="text-center">
                        <span class="badge <?php echo $filial['crescimento'] >= 0 ? 'badge-success' : 'badge-danger'; ?>">
                            <i class="fas fa-<?php echo $filial['crescimento'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($filial['crescimento']); ?>%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge <?php echo $filial['status']; ?>">
                            <i class="fas fa-circle"></i> 
                            <?php echo ucfirst($filial['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-center"><strong><?php echo $total_usuarios; ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($vendas_totais, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right"><strong><?php echo number_format($despesas_totais, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right positive"><strong><?php echo number_format($resultado_liquido, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-center"><span class="badge badge-success"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_geral; ?>%</span></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- BOTTOM GRID -->
<div class="bottom-grid-empresa">
    <div class="card-category">
        <h4><i class="fas fa-tags"></i> Vendas por Categoria</h4>
        <div class="category-list">
            <?php foreach ($vendas_categorias as $cat): ?>
            <div class="category-item">
                <span class="cat-label"><?php echo htmlspecialchars($cat['nome']); ?></span>
                <div class="cat-bar">
                    <div class="cat-fill" style="width: <?php echo $cat['percentual']; ?>%; background: <?php echo $cat['cor']; ?>;"></div>
                </div>
                <span class="cat-pct"><?php echo $cat['percentual']; ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card-category">
        <h4><i class="fas fa-file-invoice"></i> Despesas por Categoria</h4>
        <div class="category-list">
            <?php foreach ($despesas_categorias as $cat): ?>
            <div class="category-item">
                <span class="cat-label"><?php echo htmlspecialchars($cat['nome']); ?></span>
                <div class="cat-bar">
                    <div class="cat-fill" style="width: <?php echo $cat['percentual']; ?>%; background: <?php echo $cat['cor']; ?>;"></div>
                </div>
                <span class="cat-pct"><?php echo $cat['percentual']; ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card-movements">
        <h4><i class="fas fa-clock"></i> Movimentos Recentes</h4>
        <div class="movement-list">
            <?php foreach ($movimentos_recentes as $mov): ?>
            <div class="movement-item <?php echo $mov['tipo']; ?>">
                <span class="mov-date"><?php echo $mov['data']; ?></span>
                <span class="mov-desc"><?php echo htmlspecialchars($mov['descricao']); ?></span>
                <span class="mov-branch"><?php echo htmlspecialchars($mov['filial']); ?></span>
                <span class="mov-value <?php echo $mov['valor'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $mov['valor'] >= 0 ? '+' : ''; ?><?php echo number_format($mov['valor'], 0, ',', '.'); ?> Kz
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- AÇÕES RÁPIDAS -->
<div class="quick-actions">
    <a href="#" class="quick-btn primary"><i class="fas fa-plus-circle"></i> Novo Lançamento</a>
    <a href="#" class="quick-btn success"><i class="fas fa-store"></i> Nova Filial</a>
    <a href="#" class="quick-btn purple"><i class="fas fa-user-plus"></i> Novo Utilizador</a>
    <a href="<?php echo URL_BASE; ?>/backups/index" class="quick-btn orange"><i class="fas fa-database"></i> Backups</a>
    <a href="#" class="quick-btn blue"><i class="fas fa-file-alt"></i> Gerar Relatório</a>
</div>

<!-- SCRIPTS DOS GRÁFICOS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Linhas - Vendas por Filial
    const ctxFilial = document.getElementById('graficoVendasFilial');
    if (ctxFilial) {
        new Chart(ctxFilial, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: <?php echo json_encode($datasets_filiais); ?>
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { 
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12, family: 'Inter' }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { 
                            callback: function(value) { return value.toLocaleString() + ' Kz'; },
                            font: { size: 11 }
                        }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                },
                tension: 0.4,
                fill: true
            }
        });
    }

    // Gráfico Donut - Distribuição por Filial
    const ctxDist = document.getElementById('graficoDistribuicao');
    if (ctxDist) {
        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($vendas_por_filial, 'nome')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($vendas_por_filial, 'vendas')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($vendas_por_filial, 'cor')); ?>,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '70%'
            }
        });
    }
});
</script>