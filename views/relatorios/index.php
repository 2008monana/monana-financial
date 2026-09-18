<?php
/**
 * Dashboard de Relatórios
 * Super Admin: "Relatórios Consolidados" + todas as empresas
 * Admin Empresa: "Relatórios da Empresa" + apenas sua empresa
 */
?>

<div class="relatorio-header">
    <div class="relatorio-header-left">
        <h1 class="relatorio-titulo">
            <i class="fas fa-chart-column"></i>
            <?php if ($perfil === 'super_admin'): ?>
                Relatórios Consolidados
                <span class="titulo-badge"><i class="fas fa-globe"></i> Global</span>
            <?php else: ?>
                Relatórios da Empresa
                <span class="titulo-badge"><i class="fas fa-building"></i> Local</span>
            <?php endif; ?>
        </h1>
        <p class="relatorio-subtitulo">
            <?php if ($perfil === 'super_admin'): ?>
                <span class="empresa-tag global">
                    <i class="fas fa-globe"></i> Visão Global - Todas as Empresas
                </span>
            <?php else: ?>
                <span class="empresa-tag">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($empresaNome ?? 'Minha Empresa'); ?>
                </span>
            <?php endif; ?>
            <span style="color:var(--muted); font-size:13px;">
                <i class="fa-regular fa-calendar"></i> 
                <?php echo date('d/m/Y', strtotime($periodoInicio ?? date('Y-m-01'))); ?> — 
                <?php echo date('d/m/Y', strtotime($periodoFim ?? date('Y-m-t'))); ?>
            </span>
        </p>
    </div>
</div>

<!-- Resumo Rápido -->
<div class="relatorio-resumo grid-3">
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-right"></i> Entradas (Mês)</span>
        <span class="resumo-valor positivo"><?php echo number_format($resumoMes['entradas'] ?? 0, 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $resumoMes['total'] ?? 0; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-left"></i> Saídas (Mês)</span>
        <span class="resumo-valor negativo"><?php echo number_format($resumoMes['saidas'] ?? 0, 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $resumoMes['total'] ?? 0; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-scale-balanced"></i> Saldo (Mês)</span>
        <span class="resumo-valor <?php echo ($resumoMes['saldo'] ?? 0) >= 0 ? 'positivo' : 'negativo'; ?>">
            <?php echo number_format($resumoMes['saldo'] ?? 0, 0, ',', '.'); ?> Kz
        </span>
        <span class="resumo-detalhe">Resultado do mês atual</span>
    </div>
</div>

<!-- ============================================
     CARDS DE ACESSO RÁPIDO - SEM COMPARATIVO
     ============================================ -->
<div class="relatorios-grid">
    <a href="<?php echo URL_BASE; ?>/relatorios/diario" class="relatorio-card">
        <div class="relatorio-icon" style="background:linear-gradient(135deg, var(--blue), var(--blue-dark));">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="relatorio-info">
            <h3>Relatório Diário</h3>
            <p>Lançamentos do dia com totais</p>
            <span class="relatorio-link">Ver detalhes <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>

    <a href="<?php echo URL_BASE; ?>/relatorios/mensal" class="relatorio-card">
        <div class="relatorio-icon" style="background:linear-gradient(135deg, var(--green), var(--green-dark));">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="relatorio-info">
            <h3>Relatório Mensal</h3>
            <p>Consolidado por dia com totais</p>
            <span class="relatorio-link">Ver detalhes <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>

    <a href="<?php echo URL_BASE; ?>/relatorios/anual" class="relatorio-card">
        <div class="relatorio-icon" style="background:linear-gradient(135deg, var(--purple), var(--purple-dark));">
            <i class="fas fa-calendar-year"></i>
        </div>
        <div class="relatorio-info">
            <h3>Relatório Anual</h3>
            <p>Consolidado por mês com totais</p>
            <span class="relatorio-link">Ver detalhes <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>

    <?php if (!empty($filiais)): ?>
    <a href="#" class="relatorio-card" onclick="document.getElementById('formFilial').submit(); return false;">
        <div class="relatorio-icon" style="background:linear-gradient(135deg, var(--teal), #0d9488);">
            <i class="fas fa-store-alt"></i>
        </div>
        <div class="relatorio-info">
            <h3>Por Filial</h3>
            <p>Relatório filtrado por filial</p>
            <span class="relatorio-link">Selecionar filial <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
    <form id="formFilial" method="GET" action="<?php echo URL_BASE; ?>/relatorios/filial" style="display:none;">
        <select name="id" required>
            <?php foreach ($filiais as $filial): ?>
            <option value="<?php echo $filial['id']; ?>">
                <?php echo htmlspecialchars($filial['nome']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="hidden" name="mes" value="<?php echo date('m'); ?>">
        <input type="hidden" name="ano" value="<?php echo date('Y'); ?>">
    </form>
    <?php endif; ?>
</div>

<!-- Resumo por Empresa (Super Admin) ou Resumo por Filial (Admin Empresa) -->
<div class="relatorio-tabela">
    <div class="tabela-header">
        <h3>
            <i class="fas fa-chart-pie"></i>
            <?php if ($perfil === 'super_admin'): ?>
                Resumo por Empresa
            <?php else: ?>
                Resumo por Filial
            <?php endif; ?>
        </h3>
        <a href="<?php echo URL_BASE; ?>/relatorios/<?php echo $perfil === 'super_admin' ? 'mensal?empresa_id=0' : 'mensal'; ?>" class="btn-link">
            Ver completo <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="tabela-scroll">
        <table>
            <thead>
                <tr>
                    <?php if ($perfil === 'super_admin'): ?>
                    <th>Empresa</th>
                    <?php else: ?>
                    <th>Filial</th>
                    <?php endif; ?>
                    <th class="text-right">Entradas</th>
                    <th class="text-right">Saídas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-center">Transações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($perfil === 'super_admin'): ?>
                    <?php foreach ($resumoEmpresas ?? [] as $emp): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($emp['nome']); ?></strong></td>
                        <td class="text-right positive"><?php echo number_format($emp['entradas'], 0, ',', '.'); ?> Kz</td>
                        <td class="text-right negative"><?php echo number_format($emp['saidas'], 0, ',', '.'); ?> Kz</td>
                        <td class="text-right <?php echo $emp['saldo'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($emp['saldo'], 0, ',', '.'); ?> Kz
                        </td>
                        <td class="text-center"><?php echo $emp['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($resumoFiliais ?? [] as $filial): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($filial['nome']); ?></strong></td>
                        <td class="text-right positive"><?php echo number_format($filial['entradas'], 0, ',', '.'); ?> Kz</td>
                        <td class="text-right negative"><?php echo number_format($filial['saidas'], 0, ',', '.'); ?> Kz</td>
                        <td class="text-right <?php echo $filial['saldo'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo number_format($filial['saldo'], 0, ',', '.'); ?> Kz
                        </td>
                        <td class="text-center"><?php echo $filial['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================
     ESTILOS DOS RELATÓRIOS (JÁ INCLUÍDOS NO principal.php)
     ============================================ -->
<style>
/* ============================================
   RELATÓRIOS - ESTILOS ADICIONAIS
   ============================================ */

.relatorios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.relatorio-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    text-decoration: none;
    color: var(--ink);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.relatorio-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
}

.relatorio-card .relatorio-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--white);
    flex-shrink: 0;
}

.relatorio-card .relatorio-info {
    flex: 1;
}

.relatorio-card .relatorio-info h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.relatorio-card .relatorio-info p {
    font-size: 13px;
    color: var(--muted);
    margin: 2px 0 0;
}

.relatorio-card .relatorio-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: var(--green-dark);
    margin-top: 6px;
    transition: all 0.3s ease;
}

.relatorio-card .relatorio-link i {
    transition: all 0.3s ease;
}

.relatorio-card:hover .relatorio-link i {
    transform: translateX(4px);
}

@media (max-width: 768px) {
    .relatorios-grid {
        grid-template-columns: 1fr;
    }
    .relatorio-card {
        padding: 16px;
    }
}
</style>