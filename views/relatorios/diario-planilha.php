<?php
/**
 * Relatório Diário - Estilo Planilha Excel
 * Mostra os dados exatamente como na planilha
 */
?>

<div class="relatorio-header">
    <div class="relatorio-header-left">
        <h1 class="relatorio-titulo">
            <i class="fas fa-file-invoice"></i>
            Relatório Diário - Planilha
            <span class="titulo-badge"><?php echo $nomeMes . ' ' . $ano; ?></span>
        </h1>
        <p class="relatorio-subtitulo">
            <span class="empresa-tag">
                <i class="fas fa-store"></i> <?php echo htmlspecialchars($filialNome); ?>
            </span>
            <span style="color:var(--muted); font-size:13px;">
                <i class="fa-regular fa-calendar"></i> 
                <?php echo date('d/m/Y', strtotime($periodoInicio)); ?> — 
                <?php echo date('d/m/Y', strtotime($periodoFim)); ?>
            </span>
            <span style="color:var(--muted); font-size:13px; margin-left:8px;">
                <i class="fa-regular fa-clock"></i> <?php echo count($dias); ?> dias
            </span>
        </p>
    </div>
    <div class="relatorio-header-right">
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <a href="<?php echo URL_BASE; ?>/relatorios/diario-planilha?exportar=excel&filial_id=<?php echo $filialId; ?>&mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="<?php echo URL_BASE; ?>/transacoes/fechoDiario?filial_id=<?php echo $filialId; ?>&data=<?php echo date('Y-m-d'); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Fecho Diário
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
        <div class="filter-group">
            <label><i class="fas fa-store"></i> Filial</label>
            <select name="filial_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($filiais as $f): ?>
                <option value="<?php echo $f['id']; ?>" <?php echo $filialId == $f['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
            <a href="<?php echo URL_BASE; ?>/relatorios/diario-planilha" class="btn btn-outline btn-sm">Mês Atual</a>
        </div>
    </form>
</div>

<!-- Resumo Consolidado -->
<div class="resumo-consolidado">
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-chart-line"></i> Total Vendas</span>
        <span class="resumo-valor positivo"><?php echo number_format($consolidado['total_vendas'] ?? 0, 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-coins"></i> Total Depósitos</span>
        <span class="resumo-valor negativo"><?php echo number_format($consolidado['total_deposito'] ?? 0, 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-file-invoice-dollar"></i> Gastos Diário</span>
        <span class="resumo-valor negativo"><?php echo number_format($consolidado['total_gastos_diario'] ?? 0, 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fas fa-arrow-right"></i> Saídas Extra</span>
        <span class="resumo-valor negativo"><?php echo number_format($consolidado['total_saidas_extra'] ?? 0, 0, ',', '.'); ?> Kz</span>
    </div>
    <div class="resumo-card destaque">
        <span class="resumo-label"><i class="fas fa-scale-balanced"></i> Saldo Final</span>
        <span class="resumo-valor <?php echo ($consolidado['saldo_final'] ?? 0) >= 0 ? 'positivo' : 'negativo'; ?>">
            <?php echo number_format($consolidado['saldo_final'] ?? 0, 0, ',', '.'); ?> Kz
        </span>
    </div>
</div>

<!-- Tabela Planilha -->
<div class="planilha-tabela">
    <div class="tabela-scroll">
        <table class="planilha-table">
            <thead>
                <tr>
                    <th rowspan="2">Dia</th>
                    <th colspan="6" class="coluna-entrada">ENTRADAS</th>
                    <th colspan="4" class="coluna-saida">SAÍDAS</th>
                    <th rowspan="2" class="coluna-total">Saldo</th>
                </tr>
                <tr>
                    <th class="sub">TPA-BCA</th>
                    <th class="sub">TPA-Keve</th>
                    <th class="sub">Transf</th>
                    <th class="sub">Despesas</th>
                    <th class="sub">Devolução</th>
                    <th class="sub destaque">Dinheiro</th>
                    <th class="sub">Depósito</th>
                    <th class="sub">Saídas Extra</th>
                    <th class="sub">Gastos Diário</th>
                    <th class="sub">Gastos Extra</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dias)): ?>
                <tr>
                    <td colspan="12" class="text-center" style="padding:40px; color:var(--muted);">
                        <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:12px;"></i>
                        Nenhum fecho registado para este período.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($dias as $dia): ?>
                <tr>
                    <td class="text-center"><strong><?php echo $dia['dia']; ?></strong></td>
                    <td class="text-right"><?php echo number_format($dia['tpa_bca'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['tpa_keve'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['transferencias'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['despesas'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['devolucao'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right destaque"><?php echo number_format($dia['dinheiro'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['deposito'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['saidas_extra'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['gastos_diario'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($dia['gastos_extra'] ?? 0, 0, ',', '.'); ?></td>
                    <td class="text-right <?php echo ($dia['saldo_final'] ?? 0) >= 0 ? 'positivo' : 'negativo'; ?>">
                        <strong><?php echo number_format($dia['saldo_final'] ?? 0, 0, ',', '.'); ?></strong>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_tpa_bca'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_tpa_keve'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_transferencias'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_despesas'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_devolucao'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right destaque"><strong><?php echo number_format($consolidado['total_dinheiro'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_deposito'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_saidas_extra'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_gastos_diario'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right"><strong><?php echo number_format($consolidado['total_gastos_extra'] ?? 0, 0, ',', '.'); ?></strong></td>
                    <td class="text-right <?php echo ($consolidado['saldo_final'] ?? 0) >= 0 ? 'positivo' : 'negativo'; ?>">
                        <strong><?php echo number_format($consolidado['saldo_final'] ?? 0, 0, ',', '.'); ?></strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Resumo por Filial (Super Admin) -->
<?php if ($perfil === 'super_admin' && !empty($resumoFiliais)): ?>
<div class="relatorio-tabela" style="margin-top:20px;">
    <div class="tabela-header">
        <h3><i class="fas fa-chart-pie"></i> Resumo por Filial</h3>
    </div>
    <div class="tabela-scroll">
        <table class="planilha-table">
            <thead>
                <tr>
                    <th>Filial</th>
                    <th class="text-right">TPA-BCA</th>
                    <th class="text-right">TPA-Keve</th>
                    <th class="text-right">Transf</th>
                    <th class="text-right">Dinheiro</th>
                    <th class="text-right destaque">Total Vendas</th>
                    <th class="text-right">Depósito</th>
                    <th class="text-right">Gastos</th>
                    <th class="text-right <?php echo ($consolidado['saldo_final'] ?? 0) >= 0 ? 'positivo' : 'negativo'; ?>">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumoFiliais as $filial): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($filial['filial_nome']); ?></strong></td>
                    <td class="text-right"><?php echo number_format($filial['tpa_bca'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($filial['tpa_keve'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($filial['transferencias'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($filial['dinheiro'], 0, ',', '.'); ?></td>
                    <td class="text-right destaque"><?php echo number_format($filial['total_vendas'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($filial['deposito'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format($filial['gastos_diario'] + $filial['gastos_extra'] + $filial['saidas_extra'], 0, ',', '.'); ?></td>
                    <td class="text-right <?php echo $filial['saldo_final'] >= 0 ? 'positivo' : 'negativo'; ?>">
                        <?php echo number_format($filial['saldo_final'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
/* ============================================
   RELATÓRIO DIÁRIO - ESTILO PLANILHA
   ============================================ */

.relatorio-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.relatorio-titulo {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.relatorio-titulo i {
    color: var(--green);
    margin-right: 12px;
}

.titulo-badge {
    display: inline-block;
    padding: 4px 14px;
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.relatorio-subtitulo {
    font-size: 14px;
    color: var(--muted);
    margin: 4px 0 0;
}

.empresa-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    background: rgba(14, 39, 72, 0.08);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    color: var(--navy);
}

.relatorio-filtros {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 20px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    color: var(--ink);
    background: var(--white);
    min-width: 130px;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

.btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(14, 39, 72, 0.25);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14, 39, 72, 0.35);
}

.btn-success {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.25);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.35);
}

.btn-secondary {
    background: var(--bg);
    color: var(--ink);
    border: 1.5px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}

.btn-outline {
    background: transparent;
    color: var(--muted);
    border: 1.5px solid var(--border);
}

.btn-outline:hover {
    background: var(--bg);
    color: var(--ink);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 6px;
}

/* Resumo Consolidado */
.resumo-consolidado {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.resumo-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 14px 16px;
    border: 1px solid var(--border);
}

.resumo-card.destaque {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    border-color: var(--navy);
}

.resumo-card.destaque .resumo-label {
    color: rgba(255, 255, 255, 0.7);
}

.resumo-card.destaque .resumo-valor {
    color: var(--white);
}

.resumo-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: var(--muted);
}

.resumo-valor {
    display: block;
    font-family: 'Sora', sans-serif;
    font-size: 20px;
    font-weight: 700;
    margin-top: 2px;
}

.resumo-valor.positivo { color: var(--green-dark); }
.resumo-valor.negativo { color: var(--red-dark); }

/* Tabela Planilha */
.planilha-tabela {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
}

.tabela-scroll {
    overflow-x: auto;
}

.planilha-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    min-width: 1000px;
}

.planilha-table thead th {
    padding: 10px 12px;
    background: var(--navy);
    color: var(--white);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    text-align: center;
    border: 1px solid var(--navy-light);
}

.planilha-table thead th.coluna-entrada {
    background: #16a34a;
    font-size: 12px;
}

.planilha-table thead th.coluna-saida {
    background: #dc2626;
    font-size: 12px;
}

.planilha-table thead th.coluna-total {
    background: var(--navy-deep);
    font-size: 12px;
}

.planilha-table thead th.sub {
    background: #0e2748;
    font-size: 9px;
    font-weight: 500;
    padding: 6px 8px;
}

.planilha-table thead th.sub.destaque {
    background: #0a1930;
    font-weight: 700;
}

.planilha-table tbody td {
    padding: 8px 10px;
    border: 1px solid var(--border);
    color: var(--ink);
}

.planilha-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.planilha-table tbody tr:hover {
    background: #eef2f7;
}

.planilha-table tbody td.destaque {
    font-weight: 600;
    color: var(--blue);
}

.planilha-table tfoot td {
    padding: 10px 12px;
    background: #f1f3f6;
    font-weight: 700;
    border: 1px solid var(--border);
    border-top: 2px solid var(--navy);
}

.planilha-table tfoot td.destaque {
    background: #0e2748;
    color: var(--white);
}

.text-right { text-align: right; }
.text-center { text-align: center; }
.positivo { color: var(--green-dark); }
.negativo { color: var(--red-dark); }

/* Responsivo */
@media (max-width: 992px) {
    .resumo-consolidado {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .resumo-consolidado {
        grid-template-columns: 1fr 1fr;
    }
    
    .relatorio-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .relatorio-titulo {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .resumo-consolidado {
        grid-template-columns: 1fr;
    }
    
    .planilha-table {
        font-size: 10px;
        min-width: 700px;
    }
    
    .planilha-table thead th,
    .planilha-table tbody td,
    .planilha-table tfoot td {
        padding: 4px 6px;
    }
    
    .relatorio-titulo {
        font-size: 17px;
    }
    
    .btn-group .btn {
        font-size: 11px;
        padding: 6px 10px;
    }
}
</style>