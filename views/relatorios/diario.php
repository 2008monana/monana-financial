<?php
/**
 * Relatório Diário
 * Super Admin: Todas as empresas + filtro empresa
 * Admin Empresa: Apenas sua empresa
 */
?>

<div class="relatorio-header">
    <div class="relatorio-header-left">
        <h1 class="relatorio-titulo">
            <i class="fas fa-calendar-day"></i>
            Relatório Diário
            <span class="titulo-badge"><?php echo $dataFormatada; ?></span>
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
                <i class="fa-regular fa-clock"></i> <?php echo count($transacoes); ?> transações
            </span>
        </p>
    </div>
    <div class="relatorio-header-right">
        <div class="btn-group">
            <a href="<?php echo URL_BASE; ?>/relatorios/diario?data=<?php echo $data; ?>&exportar=excel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="<?php echo URL_BASE; ?>/relatorios/diario?data=<?php echo $data; ?>&exportar=pdf" class="btn btn-danger">
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
            <label><i class="fa-regular fa-calendar"></i> Data</label>
            <div style="display:flex; gap:6px; align-items:center;">
                <input type="date" name="data" value="<?php echo $data; ?>">
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            </div>
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
            <a href="<?php echo URL_BASE; ?>/relatorios/diario" class="btn btn-outline btn-sm">Hoje</a>
        </div>
    </form>
</div>

<!-- Resumo -->
<div class="relatorio-resumo grid-6">
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-cart-shopping"></i> Vendas</span>
        <span class="resumo-valor positivo"><?php echo number_format($totais['vendas'], 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-box"></i> Compras</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['compras'], 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-file-invoice-dollar"></i> Despesas</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['despesas'], 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-rotate-left"></i> Devoluções</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['devolucoes'], 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-right"></i> Entradas</span>
        <span class="resumo-valor positivo"><?php echo number_format($totais['entradas'], 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-left"></i> Saídas</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['saidas'], 0, ',', '.'); ?> Kz</span>
    </div>
</div>

<!-- Tabela -->
<div class="relatorio-tabela">
    <div class="tabela-header">
        <h3><i class="fas fa-list-ul"></i> Transações do Dia</h3>
        <span class="badge-info"><i class="fas fa-file-lines"></i> <?php echo count($transacoes); ?> registos</span>
    </div>
    <div class="tabela-scroll">
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php if ($perfil === 'super_admin'): ?>
                    <th>Empresa</th>
                    <?php endif; ?>
                    <th>Filial</th>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th class="text-right">Valor</th>
                    <th>Utilizador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transacoes)): ?>
                <tr>
                    <td colspan="<?php echo $perfil === 'super_admin' ? 7 : 6; ?>" class="text-center">
                        <div class="relatorio-vazio">
                            <i class="fas fa-inbox"></i>
                            <h4>Nenhuma transação registada</h4>
                            <p>Não há movimentos financeiros neste dia.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($transacoes as $t): ?>
                <tr>
                    <td><?php echo date('H:i', strtotime($t['criado_em'] ?? $t['data_transacao'])); ?></td>
                    <?php if ($perfil === 'super_admin'): ?>
                    <td>
                        <span class="empresa-tag" style="background:#0e274820; color:#0e2748; padding:2px 10px; border-radius:12px; font-size:11px; font-weight:600;">
                            <?php echo htmlspecialchars($t['empresa_nome'] ?? 'N/A'); ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($t['filial_nome'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($t['descricao'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $t['tipo'] === 'entrada' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $t['tipo'] === 'entrada' ? 'Entrada' : 'Saída'; ?>
                        </span>
                    </td>
                    <td class="text-right <?php echo $t['tipo'] === 'entrada' ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($t['valor'], 0, ',', '.'); ?> Kz
                    </td>
                    <td><?php echo htmlspecialchars($t['usuario_nome'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="<?php echo $perfil === 'super_admin' ? 4 : 3; ?>"><strong>TOTAL</strong></td>
                    <td></td>
                    <td class="text-right positive"><strong><?php echo number_format($totais['entradas'], 0, ',', '.'); ?> Kz</strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>