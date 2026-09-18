<?php
$totalFilialVendas   = array_sum(array_column($resumoFiliais, 'vendas'));
$totalFilialDespesas = array_sum(array_column($resumoFiliais, 'despesas'));
$coresMetodo = ['numerario' => '#22c55e', 'tpa' => '#2563eb', 'transferencia' => '#8b5cf6', 'outro' => '#f59e0b'];
?>

<!-- KPI Cards -->
<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-top">
      <div class="kpi-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
      <div class="kpi-label">VENDAS TOTAIS</div>
    </div>
    <div class="kpi-value"><?= formatarKz($kpis['vendas']['valor']) ?></div>
    <div class="kpi-delta <?= $kpis['vendas']['variacao'] >= 0 ? 'up' : 'down' ?>">
      <?= $kpis['vendas']['variacao'] >= 0 ? '↑' : '↓' ?> <?= number_format(abs($kpis['vendas']['variacao']), 1, ',', '.') ?>% <span>vs mês anterior</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <div class="kpi-icon blue"><i class="fa-solid fa-rotate-left"></i></div>
      <div class="kpi-label">DEVOLUÇÕES</div>
    </div>
    <div class="kpi-value"><?= formatarKz($kpis['devolucoes']['valor']) ?></div>
    <div class="kpi-delta <?= $kpis['devolucoes']['variacao'] >= 0 ? 'up' : 'down' ?>">
      <?= $kpis['devolucoes']['variacao'] >= 0 ? '↑' : '↓' ?> <?= number_format(abs($kpis['devolucoes']['variacao']), 1, ',', '.') ?>% <span>vs mês anterior</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <div class="kpi-icon orange"><i class="fa-solid fa-cart-shopping"></i></div>
      <div class="kpi-label">COMPRAS</div>
    </div>
    <div class="kpi-value"><?= formatarKz($kpis['compras']['valor']) ?></div>
    <div class="kpi-delta <?= $kpis['compras']['variacao'] >= 0 ? 'up' : 'down' ?>">
      <?= $kpis['compras']['variacao'] >= 0 ? '↑' : '↓' ?> <?= number_format(abs($kpis['compras']['variacao']), 1, ',', '.') ?>% <span>vs mês anterior</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <div class="kpi-icon red"><i class="fa-solid fa-file-invoice-dollar"></i></div>
      <div class="kpi-label">DESPESAS</div>
    </div>
    <div class="kpi-value"><?= formatarKz($kpis['despesas']['valor']) ?></div>
    <div class="kpi-delta <?= $kpis['despesas']['variacao'] >= 0 ? 'up' : 'down' ?>">
      <?= $kpis['despesas']['variacao'] >= 0 ? '↑' : '↓' ?> <?= number_format(abs($kpis['despesas']['variacao']), 1, ',', '.') ?>% <span>vs mês anterior</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-top">
      <div class="kpi-icon navy"><i class="fa-solid fa-scale-balanced"></i></div>
      <div class="kpi-label">RESULTADO LÍQUIDO</div>
    </div>
    <div class="kpi-value"><?= formatarKz($kpis['resultado']['valor']) ?></div>
    <div class="kpi-delta <?= $kpis['resultado']['variacao'] >= 0 ? 'up' : 'down' ?>">
      <?= $kpis['resultado']['variacao'] >= 0 ? '↑' : '↓' ?> <?= number_format(abs($kpis['resultado']['variacao']), 1, ',', '.') ?>% <span>vs mês anterior</span>
    </div>
  </div>
</div>

<!-- Gráfico + Donut -->
<div class="mid-grid">
  <div class="card">
    <div class="card-head">
      <div class="card-title">Evolução das Vendas</div>
      <div class="chip">Diário <i class="fa-solid fa-chevron-down"></i></div>
    </div>
    <?php if (empty($evolucaoVendas)): ?>
      <div class="grafico-vazio">Ainda não há vendas registadas este mês.</div>
    <?php else: ?>
      <div class="grafico-wrap"><canvas id="grafico-vendas"></canvas></div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head">
      <div class="card-title">Vendas por Método de Pagamento</div>
    </div>
    <?php if (empty($porMetodo)): ?>
      <div class="grafico-vazio">Sem dados de pagamento este mês.</div>
    <?php else: ?>
      <div class="donut-wrap">
        <div class="donut-canvas-wrap"><canvas id="grafico-metodos"></canvas></div>
        <div class="legend">
          <?php
          $totalMetodos = array_sum(array_column($porMetodo, 'total'));
          foreach ($porMetodo as $m):
              $pct = $totalMetodos > 0 ? ($m['total'] / $totalMetodos) * 100 : 0;
          ?>
          <div class="legend-item">
            <div class="legend-dot" style="background:<?= $coresMetodo[$m['metodo_pagamento']] ?? '#94a3b8' ?>"></div>
            <div class="lbl"><?= htmlspecialchars($rotulosMetodo[$m['metodo_pagamento']] ?? ucfirst($m['metodo_pagamento'])) ?></div>
            <div class="val"><?= formatarKz((float) $m['total']) ?></div>
            <div class="pct"><?= number_format($pct, 1, ',', '.') ?>%</div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Resumo por Filial -->
<div class="card" style="margin-bottom:16px;">
  <div class="card-head"><div class="card-title">Resumo por Filial</div></div>
  <?php if (empty($resumoFiliais)): ?>
    <div class="tabela-vazia">Ainda não existem filiais ou movimentos registados.</div>
  <?php else: ?>
  <table>
    <thead><tr><th>Filial</th><th>Vendas</th><th>Despesas</th><th>Resultado</th></tr></thead>
    <tbody>
      <?php foreach ($resumoFiliais as $f): $resultadoFilial = $f['vendas'] - $f['despesas']; ?>
      <tr>
        <td><?= htmlspecialchars($f['filial_nome']) ?></td>
        <td><?= formatarKz((float) $f['vendas']) ?></td>
        <td><?= formatarKz((float) $f['despesas']) ?></td>
        <td class="<?= $resultadoFilial >= 0 ? 'amt-pos' : 'amt-neg' ?>"><?= formatarKz($resultadoFilial) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td><strong>Total</strong></td>
        <td><strong><?= formatarKz($totalFilialVendas) ?></strong></td>
        <td><strong><?= formatarKz($totalFilialDespesas) ?></strong></td>
        <td class="<?= ($totalFilialVendas - $totalFilialDespesas) >= 0 ? 'amt-pos' : 'amt-neg' ?>"><strong><?= formatarKz($totalFilialVendas - $totalFilialDespesas) ?></strong></td>
      </tr>
    </tbody>
  </table>
  <a class="see-more" href="<?= URL_BASE ?>/relatorios/index">Ver relatório completo →</a>
  <?php endif; ?>
</div>

<!-- Bottom Grid -->
<div class="bottom-grid">
  <div class="card">
    <div class="card-head"><div class="card-title">Movimentos Recentes</div></div>
    <?php if (empty($movimentosRecentes)): ?>
      <div class="tabela-vazia">Sem movimentos recentes.</div>
    <?php else: ?>
    <table>
      <thead><tr><th>Data</th><th>Descrição</th><th>Filial</th><th>Valor</th></tr></thead>
      <tbody>
        <?php foreach ($movimentosRecentes as $m):
            $positivo = $m['tipo'] === 'venda';
            $rotuloTipo = ['venda' => 'Venda', 'devolucao' => 'Devolução', 'custo' => 'Despesa'][$m['tipo']] ?? ucfirst($m['tipo']);
        ?>
        <tr>
          <td><?= formatarDataCurta($m['data_transacao']) ?></td>
          <td><?= htmlspecialchars($rotuloTipo . ($m['descricao'] ? ' - ' . $m['descricao'] : '')) ?></td>
          <td><?= htmlspecialchars($m['filial_nome']) ?></td>
          <td class="<?= $positivo ? 'amt-pos' : 'amt-neg' ?>"><?= $positivo ? '+' : '-' ?><?= formatarKz((float) $m['valor']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head"><div class="card-title">Compras Recentes</div></div>
    <?php if (empty($comprasRecentes)): ?>
      <div class="tabela-vazia">Sem compras recentes.</div>
    <?php else: ?>
    <table>
      <thead><tr><th>Data</th><th>Descrição</th><th>Filial</th><th>Valor</th></tr></thead>
      <tbody>
        <?php foreach ($comprasRecentes as $c): ?>
        <tr>
          <td><?= formatarDataCurta($c['data_transacao']) ?></td>
          <td><?= htmlspecialchars($c['descricao'] ?: 'Compra') ?></td>
          <td><?= htmlspecialchars($c['filial_nome']) ?></td>
          <td class="amt-neg">-<?= formatarKz((float) $c['valor']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head"><div class="card-title">Alertas e Notificações</div></div>
    <?php if (empty($alertas)): ?>
      <div class="tabela-vazia">Sem alertas no momento. Tudo em dia! ✅</div>
    <?php else: ?>
      <?php
      $iconesAlerta = ['alerta' => 'fa-circle-exclamation', 'aviso' => 'fa-triangle-exclamation', 'sucesso' => 'fa-circle-check', 'erro' => 'fa-circle-xmark'];
      foreach ($alertas as $a):
      ?>
      <a class="alert-item" href="<?= URL_BASE ?>/notificacoes/index">
        <div class="alert-dot <?= $a['tipo'] ?>"><i class="fa-solid <?= $iconesAlerta[$a['tipo']] ?? 'fa-bell' ?>"></i></div>
        <div class="alert-text">
          <div class="t"><?= htmlspecialchars($a['titulo']) ?></div>
          <div class="s"><?= htmlspecialchars($a['mensagem']) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Dados para os gráficos Chart.js -->
<script id="dados-dashboard" type="application/json">
<?= json_encode([
    'evolucaoVendas' => array_map(fn($d) => ['data' => formatarDataCurta($d['data']), 'total' => (float) $d['total']], $evolucaoVendas),
    'metodos' => array_map(fn($m) => [
        'rotulo' => $rotulosMetodo[$m['metodo_pagamento']] ?? ucfirst($m['metodo_pagamento']),
        'total'  => (float) $m['total'],
        'cor'    => $coresMetodo[$m['metodo_pagamento']] ?? '#94a3b8',
    ], $porMetodo),
], JSON_UNESCAPED_UNICODE) ?>
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js" crossorigin="anonymous"></script>
<script src="<?= URL_BASE ?>/js/dashboard.js"></script>
