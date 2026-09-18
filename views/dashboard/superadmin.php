<?php
/**
 * Dashboard do Super Administrador
 */
?>

<div class="dash-header">
    <div class="dash-header-left">
        <h1 class="dash-title">Painel de Controlo Global</h1>
        <p class="dash-subtitle">
            Visão consolidada de todas as empresas e filiais
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
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon navy"><i class="fas fa-building"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Empresas</span>
            <span class="kpi-value"><?php echo number_format($total_empresas, 0, ',', '.'); ?></span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $novas_empresas; ?> este mês</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon blue"><i class="fas fa-store-alt"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Filiais</span>
            <span class="kpi-value"><?php echo number_format($total_filiais, 0, ',', '.'); ?></span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $novas_filiais; ?> este mês</span>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon-wrapper">
            <div class="kpi-icon purple"><i class="fas fa-users"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Utilizadores</span>
            <span class="kpi-value"><?php echo number_format($total_usuarios, 0, ',', '.'); ?></span>
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
            <div class="kpi-icon gold"><i class="fas fa-coins"></i></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Resultado Global</span>
            <span class="kpi-value"><?php echo number_format($resultado_global, 0, ',', '.'); ?> Kz</span>
            <span class="kpi-change up"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_resultado; ?>%</span>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="charts-row">
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-bar"></i> Evolução de Vendas por Empresa</h3>
            <span class="chart-period"><?php echo $periodoLabel; ?></span>
        </div>
        <div class="chart-body">
            <canvas id="graficoVendas"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-pie"></i> Vendas por Método de Pagamento</h3>
        </div>
        <div class="chart-body donut-body">
            <div class="donut-container">
                <canvas id="graficoMetodos"></canvas>
            </div>
            <div class="donut-legend">
                <?php foreach ($metodos_pagamento as $metodo): ?>
                <div class="legend-item">
                    <span class="legend-dot" style="background:<?php echo $metodo['cor']; ?>"></span>
                    <span class="legend-label"><?php echo $metodo['nome']; ?></span>
                    <span class="legend-value"><?php echo number_format($metodo['valor'], 0, ',', '.'); ?> Kz</span>
                    <span class="legend-pct"><?php echo $metodo['percentual']; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- TABELA RESUMO POR EMPRESA -->
<div class="table-card">
    <div class="table-header">
        <h3><i class="fas fa-list-ul"></i> Resumo por Empresa</h3>
        <a href="#" class="btn-link">Ver todas <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th class="text-center">Filiais</th>
                    <th class="text-center">Utilizadores</th>
                    <th class="text-right">Vendas</th>
                    <th class="text-right">Despesas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-center">Crescimento</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumo_empresas as $empresa): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($empresa['nome']); ?></strong></td>
                    <td class="text-center"><?php echo $empresa['filiais']; ?></td>
                    <td class="text-center"><?php echo $empresa['usuarios']; ?></td>
                    <td class="text-right"><?php echo number_format($empresa['vendas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right"><?php echo number_format($empresa['despesas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right <?php echo $empresa['saldo'] >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($empresa['saldo'], 0, ',', '.'); ?> Kz
                    </td>
                    <td class="text-center">
                        <span class="badge <?php echo $empresa['crescimento'] >= 0 ? 'badge-success' : 'badge-danger'; ?>">
                            <i class="fas fa-<?php echo $empresa['crescimento'] >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                            <?php echo abs($empresa['crescimento']); ?>%
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL GERAL</strong></td>
                    <td class="text-center"><strong><?php echo $total_filiais; ?></strong></td>
                    <td class="text-center"><strong><?php echo $total_usuarios; ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($vendas_totais, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right"><strong><?php echo number_format($total_despesas, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right positive"><strong><?php echo number_format($resultado_global, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-center"><span class="badge badge-success"><i class="fas fa-arrow-up"></i> <?php echo $crescimento_global; ?>%</span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- BOTTOM GRID -->
<div class="bottom-grid">
    <div class="card-activities">
        <h4><i class="fas fa-clock"></i> Últimos Acessos</h4>
        <div class="activity-list">
            <?php foreach ($atividades_recentes as $atividade): ?>
            <div class="activity-item">
                <div class="activity-avatar" style="background:<?php echo $atividade['cor']; ?>">
                    <?php echo strtoupper(substr($atividade['nome'], 0, 2)); ?>
                </div>
                <div class="activity-info">
                    <div class="activity-name"><?php echo htmlspecialchars($atividade['nome']); ?></div>
                    <div class="activity-detail">
                        <?php echo htmlspecialchars($atividade['empresa']); ?>
                        <span class="activity-time">• <?php echo $atividade['tempo']; ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card-alerts">
        <h4><i class="fas fa-exclamation-triangle"></i> Alertas Globais</h4>
        <?php foreach ($alertas as $alerta): ?>
        <div class="alert-item <?php echo $alerta['tipo']; ?>">
            <div class="alert-icon">
                <i class="fas fa-<?php echo $alerta['icone']; ?>"></i>
            </div>
            <div class="alert-content">
                <div class="alert-title"><?php echo htmlspecialchars($alerta['titulo']); ?></div>
                <div class="alert-desc"><?php echo htmlspecialchars($alerta['descricao']); ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card-performance">
        <h4><i class="fas fa-chart-pie"></i> Análise de Performance</h4>
        <div class="performance-list">
            <?php foreach ($resumo_empresas as $empresa): ?>
            <div class="performance-item">
                <span class="perf-label"><?php echo htmlspecialchars($empresa['nome']); ?></span>
                <div class="perf-bar">
                    <div class="perf-fill" style="width: <?php echo $empresa['percentual']; ?>%; background: <?php echo $empresa['cor']; ?>;">
                        <?php echo $empresa['percentual']; ?>%
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- SCRIPTS DOS GRÁFICOS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de Barras - Vendas Consolidadas
    const ctxVendas = document.getElementById('graficoVendas');
    if (ctxVendas) {
        new Chart(ctxVendas, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: <?php echo json_encode($datasets_vendas); ?>
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
                borderRadius: 6
            }
        });
    }

    // Gráfico Donut - Métodos de Pagamento
    const ctxDonut = document.getElementById('graficoMetodos');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($metodos_pagamento, 'nome')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($metodos_pagamento, 'valor')); ?>,
                    backgroundColor: <?php echo json_encode(array_column($metodos_pagamento, 'cor')); ?>,
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