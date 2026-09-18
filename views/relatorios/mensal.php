<?php
/**
 * Relatório Mensal
 * Super Admin: Todas as empresas + filtro empresa
 * Admin Empresa: Apenas sua empresa
 */
?>

<div class="relatorio-header">
    <div class="relatorio-header-left">
        <h1 class="relatorio-titulo">
            <i class="fas fa-calendar-alt"></i>
            Relatório Mensal
            <span class="titulo-badge"><?php echo $nomeMes . ' ' . $ano; ?></span>
        </h1>
        <p class="relatorio-subtitulo">
            <?php if ($perfil === 'super_admin'): ?>
                <span class="empresa-tag global">
                    <i class="fas fa-globe"></i> Todas as Empresas
                </span>
            <?php else: ?>
                <span class="empresa-tag">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($_SESSION['empresa_nome'] ?? 'Minha Empresa'); ?>
                </span>
            <?php endif; ?>
            <span style="color:var(--muted); font-size:13px;">
                <i class="fa-regular fa-clock"></i> <?php echo $totais['total_transacoes']; ?> transações
            </span>
        </p>
    </div>
    <div class="relatorio-header-right">
        <div class="btn-group">
            <a href="<?php echo URL_BASE; ?>/relatorios/mensal?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>&exportar=excel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="<?php echo URL_BASE; ?>/relatorios/mensal?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>&exportar=pdf" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="<?php echo URL_BASE; ?>/relatorios/index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="relatorio-filtros">
    <form method="GET" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; width:100%;">
        <div class="filter-group">
            <label><i class="fa-regular fa-calendar"></i> Mês</label>
            <select name="mes">
                <?php foreach ($meses as $i => $nome): ?>
                <option value="<?php echo $i; ?>" <?php echo $i == $mes ? 'selected' : ''; ?>>
                    <?php echo $nome; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label><i class="fa-regular fa-calendar"></i> Ano</label>
            <select name="ano">
                <?php foreach ($anos as $a): ?>
                <option value="<?php echo $a; ?>" <?php echo $a == $ano ? 'selected' : ''; ?>>
                    <?php echo $a; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($perfil === 'super_admin' && !empty($empresas)): ?>
        <div class="filter-group">
            <label><i class="fas fa-building"></i> Empresa</label>
            <select name="empresa_id" onchange="this.form.submit()">
                <option value="0">Todas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?php echo $emp['id']; ?>" <?php echo ($_GET['empresa_id'] ?? 0) == $emp['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <?php if (!empty($filiais)): ?>
        <div class="filter-group">
            <label><i class="fas fa-store-alt"></i> Filial</label>
            <select name="filial_id" onchange="this.form.submit()">
                <option value="0">Todas</option>
                <?php foreach ($filiais as $f): ?>
                <option value="<?php echo $f['id']; ?>" <?php echo ($_GET['filial_id'] ?? 0) == $f['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            <a href="<?php echo URL_BASE; ?>/relatorios/mensal" class="btn btn-outline btn-sm">Mês Atual</a>
        </div>
    </form>
</div>

<!-- Resumo -->
<div class="relatorio-resumo grid-4">
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-right"></i> Total Entradas</span>
        <span class="resumo-valor positivo"><?php echo number_format($totais['entradas'], 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $totais['total_transacoes']; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-left"></i> Total Saídas</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['saidas'], 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $totais['total_transacoes']; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-scale-balanced"></i> Saldo</span>
        <span class="resumo-valor <?php echo $totais['saldo'] >= 0 ? 'positivo' : 'negativo'; ?>">
            <?php echo number_format($totais['saldo'], 0, ',', '.'); ?> Kz
        </span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-clock"></i> Dias com Movimento</span>
        <span class="resumo-valor" style="color:var(--navy);"><?php echo count($resumoDiario); ?> / <?php echo $diasNoMes; ?></span>
        <span class="resumo-detalhe"><?php echo number_format((count($resumoDiario) / $diasNoMes) * 100, 1, ',', '.'); ?>% do mês</span>
    </div>
</div>

<!-- Tabela -->
<div class="relatorio-tabela">
    <div class="tabela-header">
        <h3><i class="fas fa-list-ul"></i> Resumo Diário</h3>
        <span class="badge-info"><i class="fas fa-calendar-day"></i> <?php echo count($resumoDiario); ?> dias</span>
    </div>
    <div class="tabela-scroll">
        <table>
            <thead>
                <tr>
                    <th>Dia</th>
                    <th class="text-right">Entradas</th>
                    <th class="text-right">Saídas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-right">Vendas</th>
                    <th class="text-right">Compras</th>
                    <th class="text-right">Despesas</th>
                    <th class="text-right">Devoluções</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($resumoDiario)): ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="relatorio-vazio">
                            <i class="fas fa-inbox"></i>
                            <h4>Nenhuma transação registada</h4>
                            <p>Não há movimentos financeiros neste mês.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($resumoDiario as $dia): ?>
                <tr>
                    <td><strong><?php echo $dia['dia']; ?></strong></td>
                    <td class="text-right positive"><?php echo number_format($dia['entradas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right negative"><?php echo number_format($dia['saidas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right <?php echo ($dia['entradas'] - $dia['saidas']) >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($dia['entradas'] - $dia['saidas'], 0, ',', '.'); ?> Kz
                    </td>
                    <td class="text-right"><?php echo number_format($dia['vendas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right"><?php echo number_format($dia['compras'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right"><?php echo number_format($dia['despesas'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-right"><?php echo number_format($dia['devolucoes'], 0, ',', '.'); ?> Kz</td>
                    <td class="text-center"><?php echo $dia['total']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right positive"><strong><?php echo number_format($totais['entradas'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right negative"><strong><?php echo number_format($totais['saidas'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right <?php echo $totais['saldo'] >= 0 ? 'positive' : 'negative'; ?>">
                        <strong><?php echo number_format($totais['saldo'], 0, ',', '.'); ?> Kz</strong>
                    </td>
                    <td class="text-right"><strong><?php echo number_format($totais['vendas'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right"><strong><?php echo number_format($totais['compras'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right"><strong><?php echo number_format($totais['despesas'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right"><strong><?php echo number_format($totais['devolucoes'], 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-center"><strong><?php echo $totais['total_transacoes']; ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>