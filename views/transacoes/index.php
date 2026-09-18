<?php
/**
 * Listagem de Transações - com filtros rápidos (Vendas, Compras, Despesas, Devoluções)
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">
            <i class="fas fa-list-ul"></i> Movimentos Financeiros
        </h1>
        <p class="page-subtitle">
            <?php if ($perfil === 'super_admin'): ?>
                Visão consolidada de todas as empresas e filiais
            <?php else: ?>
                Visão consolidada da sua empresa
            <?php endif; ?>
            
            <?php if (!empty($filtroAtivo)): ?>
                <span class="filtro-tipo-badge">
                    <i class="fas fa-filter"></i>
                    <?php echo $filtroAtivo; ?>
                    <a href="<?php echo URL_BASE; ?>/transacoes/index" class="remover-filtro" title="Remover filtro">
                        <i class="fas fa-times"></i>
                    </a>
                </span>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-right">
        <div class="btn-group">
            <a href="<?php echo URL_BASE; ?>/transacoes/index?exportar=excel&<?php echo http_build_query($filtros); ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="<?php echo URL_BASE; ?>/transacoes/index?exportar=pdf&<?php echo http_build_query($filtros); ?>" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <?php if ($perfil !== 'visualizador'): ?>
            <a href="<?php echo URL_BASE; ?>/transacoes/criar" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Lançamento
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ============================================
     FILTROS RÁPIDOS - VENDAS, COMPRAS, DESPESAS, DEVOLUÇÕES
     ============================================ -->
<div class="filtros-rapidos">
    <span class="filtros-rapidos-label">
        <i class="fas fa-filter"></i> Filtrar por:
    </span>
    <div class="filtros-rapidos-group">
        <a href="<?php echo URL_BASE; ?>/transacoes/index?tipo=venda" 
           class="filtro-rapido <?php echo ($filtros['tipo'] ?? '') === 'venda' ? 'active' : ''; ?>">
            <i class="fas fa-cart-shopping"></i> Vendas
        </a>
        <a href="<?php echo URL_BASE; ?>/transacoes/index?tipo=compra" 
           class="filtro-rapido <?php echo ($filtros['tipo'] ?? '') === 'compra' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Compras
        </a>
        <a href="<?php echo URL_BASE; ?>/transacoes/index?tipo=custo" 
           class="filtro-rapido <?php echo ($filtros['tipo'] ?? '') === 'custo' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice-dollar"></i> Despesas
        </a>
        <a href="<?php echo URL_BASE; ?>/transacoes/index?tipo=devolucao" 
           class="filtro-rapido <?php echo ($filtros['tipo'] ?? '') === 'devolucao' ? 'active' : ''; ?>">
            <i class="fas fa-rotate-left"></i> Devoluções
        </a>
        <?php if (!empty($filtros['tipo'])): ?>
            <a href="<?php echo URL_BASE; ?>/transacoes/index" class="filtro-rapido limpar">
                <i class="fas fa-times"></i> Limpar filtro
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo -->
<div class="summary-cards">
    <div class="summary-card">
        <span class="summary-label">Total Entradas</span>
        <span class="summary-value positive"><?php echo number_format($totalEntradas, 0, ',', '.'); ?> Kz</span>
        <span class="summary-detail">
            <?php 
            $countEntradas = 0;
            foreach ($transacoes as $t) { if ($t['tipo'] === 'entrada') $countEntradas++; }
            echo $countEntradas; 
            ?> transações
        </span>
    </div>
    <div class="summary-card">
        <span class="summary-label">Total Saídas</span>
        <span class="summary-value negative"><?php echo number_format($totalSaidas, 0, ',', '.'); ?> Kz</span>
        <span class="summary-detail">
            <?php 
            $countSaidas = 0;
            foreach ($transacoes as $t) { if ($t['tipo'] === 'saida') $countSaidas++; }
            echo $countSaidas; 
            ?> transações
        </span>
    </div>
    <div class="summary-card">
        <span class="summary-label">Saldo</span>
        <span class="summary-value <?php echo $saldo >= 0 ? 'positive' : 'negative'; ?>">
            <?php echo number_format($saldo, 0, ',', '.'); ?> Kz
        </span>
        <span class="summary-detail"><?php echo count($transacoes); ?> transações no total</span>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="filters-bar">
    <form method="GET" class="filters-form">
        <!-- Manter o filtro de tipo se existir -->
        <?php if (!empty($filtros['tipo'])): ?>
            <input type="hidden" name="tipo" value="<?php echo $filtros['tipo']; ?>">
        <?php endif; ?>
        
        <div class="filter-group">
            <label>Período</label>
            <div class="filter-date-group">
                <input type="date" name="data_inicio" value="<?php echo $filtros['data_inicio']; ?>">
                <span>até</span>
                <input type="date" name="data_fim" value="<?php echo $filtros['data_fim']; ?>">
            </div>
        </div>

        <?php if ($perfil === 'super_admin'): ?>
        <div class="filter-group">
            <label>Empresa</label>
            <select name="empresa_id" id="filtro_empresa">
                <option value="0">Todas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?php echo $emp['id']; ?>" <?php echo ($filtros['empresa_id'] ?? 0) == $emp['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="filter-group">
            <label>Filial</label>
            <select name="filial_id" id="filtro_filial">
                <option value="0">Todas</option>
                <?php foreach ($filiais as $filial): ?>
                <option value="<?php echo $filial['id']; ?>" <?php echo $filtros['filial_id'] == $filial['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($filial['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Categoria</label>
            <select name="categoria_id">
                <option value="0">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $filtros['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label>Busca</label>
            <input type="text" name="busca" placeholder="Descrição ou utilizador..." value="<?php echo htmlspecialchars($filtros['busca']); ?>">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Filtrar</button>
            <a href="<?php echo URL_BASE; ?>/transacoes/index" class="btn btn-outline"><i class="fas fa-undo"></i> Limpar</a>
        </div>
    </form>
</div>

<!-- Tabela -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Data</th>
                    <?php if ($perfil === 'super_admin'): ?>
                    <th>Empresa</th>
                    <?php endif; ?>
                    <th>Filial</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Utilizador</th>
                    <?php if ($perfil !== 'visualizador'): ?>
                    <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transacoes)): ?>
                <tr>
                    <td colspan="<?php 
                        $colunas = 7;
                        if ($perfil === 'super_admin') $colunas++;
                        if ($perfil !== 'visualizador') $colunas++;
                        echo $colunas; 
                    ?>" class="text-center" style="padding:40px; color:var(--muted);">
                        <i class="fas fa-inbox" style="font-size:32px; display:block; margin-bottom:12px;"></i>
                        Nenhuma transação encontrada.
                        <?php if ($perfil !== 'visualizador'): ?>
                        <br>
                        <a href="<?php echo URL_BASE; ?>/transacoes/criar" class="btn btn-primary" style="margin-top:12px;">
                            <i class="fas fa-plus"></i> Criar primeiro lançamento
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($transacoes as $t): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($t['data_transacao'])); ?></td>
                    <?php if ($perfil === 'super_admin'): ?>
                    <td>
                        <span class="empresa-tag" style="background:#0e274820; color:#0e2748; padding:2px 10px; border-radius:12px; font-size:11px; font-weight:600;">
                            <?php echo htmlspecialchars($t['empresa_nome'] ?? 'N/A'); ?>
                        </span>
                    </td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($t['filial_nome'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $t['tipo'] === 'entrada' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo $t['tipo'] === 'entrada' ? 'Entrada' : 'Saída'; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($t['categoria_nome'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($t['descricao'] ?: '-'); ?></td>
                    <td class="<?php echo $t['tipo'] === 'entrada' ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($t['valor'], 0, ',', '.'); ?> Kz
                    </td>
                    <td><?php echo htmlspecialchars($t['usuario_nome'] ?? '-'); ?></td>
                    <?php if ($perfil !== 'visualizador'): ?>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo URL_BASE; ?>/transacoes/editar/<?php echo $t['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-danger" data-id="<?php echo $t['id']; ?>" onclick="abrirModalExclusao(<?php echo $t['id']; ?>)" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Resumo por Filial (Admin Empresa) -->
<?php if (!empty($resumoPorFilial) && $perfil !== 'super_admin'): ?>
<div class="table-card" style="margin-top:20px;">
    <div class="table-header">
        <h3><i class="fas fa-chart-pie"></i> Resumo por Filial</h3>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Filial</th>
                    <th class="text-right">Entradas</th>
                    <th class="text-right">Saídas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-center">Transações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumoPorFilial as $filial): ?>
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
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right positive"><strong><?php echo number_format($totalEntradas, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right negative"><strong><?php echo number_format($totalSaidas, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right <?php echo $saldo >= 0 ? 'positive' : 'negative'; ?>"><strong><?php echo number_format($saldo, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-center"><strong><?php echo count($transacoes); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Resumo por Empresa (Super Admin) -->
<?php if (!empty($resumoPorEmpresa) && $perfil === 'super_admin'): ?>
<div class="table-card" style="margin-top:20px;">
    <div class="table-header">
        <h3><i class="fas fa-chart-pie"></i> Resumo por Empresa</h3>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th class="text-right">Entradas</th>
                    <th class="text-right">Saídas</th>
                    <th class="text-right">Saldo</th>
                    <th class="text-center">Transações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumoPorEmpresa as $emp): ?>
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
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL GERAL</strong></td>
                    <td class="text-right positive"><strong><?php echo number_format($totalEntradas, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right negative"><strong><?php echo number_format($totalSaidas, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-right <?php echo $saldo >= 0 ? 'positive' : 'negative'; ?>"><strong><?php echo number_format($saldo, 0, ',', '.'); ?> Kz</strong></td>
                    <td class="text-center"><strong><?php echo count($transacoes); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal de Confirmação de Eliminação -->
<div class="modal-overlay" id="modalExclusao" style="display:none;">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3 class="modal-title">Confirmar Eliminação</h3>
        </div>
        <div class="modal-body">
            <p class="modal-message">Tem certeza que deseja eliminar este lançamento?</p>
            <p class="modal-warning"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="fecharModalExclusao()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button class="btn btn-danger" id="btnConfirmarExclusao">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<script>
function abrirModalExclusao(id) {
    document.getElementById('modalExclusao').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.getElementById('btnConfirmarExclusao').setAttribute('data-id', id);
}

function fecharModalExclusao() {
    document.getElementById('modalExclusao').style.display = 'none';
    document.body.style.overflow = '';
}

document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    if (id) {
        window.location.href = '<?php echo URL_BASE; ?>/transacoes/excluir/' + id;
    }
});

// Fechar modal ao clicar fora
document.getElementById('modalExclusao').addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalExclusao();
    }
});

// Filtrar filiais por empresa (Super Admin)
document.addEventListener('DOMContentLoaded', function() {
    const selectEmpresa = document.getElementById('filtro_empresa');
    const selectFilial = document.getElementById('filtro_filial');
    
    if (selectEmpresa && selectFilial) {
        selectEmpresa.addEventListener('change', function() {
            const empresaId = this.value;
            const filiais = <?php echo json_encode($filiais); ?>;
            
            selectFilial.innerHTML = '<option value="0">Todas</option>';
            
            filiais.forEach(function(filial) {
                if (empresaId == 0 || filial.empresa_id == empresaId) {
                    const option = document.createElement('option');
                    option.value = filial.id;
                    option.textContent = filial.nome;
                    selectFilial.appendChild(option);
                }
            });
        });
    }
});
</script>

<style>
/* ============================================
   TRANSAÇÕES - ESTILOS COMPLETOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.page-title {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.page-title i {
    color: var(--green);
    margin-right: 12px;
}

.page-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

.filtro-tipo-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
}

.remover-filtro {
    color: var(--green-dark);
    margin-left: 4px;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.remover-filtro:hover {
    opacity: 1;
    color: var(--red);
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i { font-size: 14px; }

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

.btn-danger {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.25);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.35);
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
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 8px;
}

/* ============================================
   FILTROS RÁPIDOS
   ============================================ */

.filtros-rapidos {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filtros-rapidos-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
}

.filtros-rapidos-label i {
    margin-right: 4px;
}

.filtros-rapidos-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.filtro-rapido {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    text-decoration: none;
    transition: all 0.3s ease;
    background: var(--white);
}

.filtro-rapido:hover {
    border-color: var(--navy);
    color: var(--navy);
    background: #f8fafc;
}

.filtro-rapido.active {
    background: var(--navy);
    color: var(--white);
    border-color: var(--navy);
}

.filtro-rapido.active:hover {
    background: var(--navy-deep);
}

.filtro-rapido i {
    font-size: 13px;
}

.filtro-rapido.limpar {
    border-color: var(--red);
    color: var(--red);
}

.filtro-rapido.limpar:hover {
    background: #fee2e2;
}

/* ============================================
   RESUMO
   ============================================ */

.summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.summary-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 20px;
    border: 1px solid var(--border);
}

.summary-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: var(--muted);
    margin-bottom: 4px;
}

.summary-value {
    display: block;
    font-family: 'Sora', sans-serif;
    font-size: 22px;
    font-weight: 700;
}

.summary-detail {
    display: block;
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}

.positive { color: var(--green-dark); font-weight: 600; }
.negative { color: var(--red-dark); font-weight: 600; }

/* ============================================
   FILTROS AVANÇADOS
   ============================================ */

.filters-bar {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 20px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
}

.filters-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
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
    transition: all 0.3s ease;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

.filter-date-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-date-group span {
    color: var(--muted);
    font-size: 13px;
}

.filter-date-group input {
    min-width: 120px;
}

.filter-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* ============================================
   TABELA
   ============================================ */

.table-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 20px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
    transition: var(--transition);
}

.table-card:hover {
    box-shadow: var(--shadow-md);
}

.table-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.table-header h3 {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.table-header h3 i {
    color: var(--purple);
    margin-right: 8px;
}

.table-responsive {
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    min-width: 700px;
    border-collapse: collapse;
    font-size: 13px;
}

.table-modern thead th {
    padding: 10px 14px;
    background: #f8fafc;
    color: var(--muted);
    font-weight: 600;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}

.table-modern tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: var(--ink);
    vertical-align: middle;
}

.table-modern tbody tr:hover { background: #f8fafc; }

.table-modern tfoot td {
    padding: 12px 14px;
    background: #f8fafc;
    font-weight: 600;
    border-top: 2px solid var(--border);
}

.text-center { text-align: center; }
.text-right { text-align: right; }

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-success {
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
}

.badge-danger {
    background: rgba(239, 68, 68, 0.12);
    color: var(--red-dark);
}

.action-buttons {
    display: flex;
    gap: 6px;
}

.empresa-tag {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

/* ============================================
   MODAL
   ============================================ */

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: modalFadeIn 0.3s ease;
}

.modal-container {
    background: var(--white);
    border-radius: 20px;
    max-width: 420px;
    width: 90%;
    padding: 32px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease;
}

.modal-header {
    text-align: center;
    margin-bottom: 20px;
}

.modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.modal-icon i {
    font-size: 28px;
    color: var(--red);
}

.modal-title {
    font-family: 'Sora', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--ink);
    margin: 0;
}

.modal-body {
    text-align: center;
    margin-bottom: 24px;
}

.modal-message {
    font-size: 15px;
    color: var(--ink);
    margin: 0 0 8px;
    line-height: 1.5;
}

.modal-warning {
    font-size: 13px;
    color: var(--orange);
    margin: 0;
    font-weight: 500;
}

.modal-warning i {
    margin-right: 6px;
}

.modal-footer {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.modal-footer .btn {
    padding: 10px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
}

@keyframes modalFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes modalSlideIn {
    from { transform: translateY(-30px) scale(0.95); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 992px) {
    .summary-cards { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 768px) {
    .filters-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group input,
    .filter-group select {
        min-width: auto;
        width: 100%;
    }
    
    .filter-date-group {
        flex-wrap: wrap;
    }
    
    .filter-date-group input {
        flex: 1;
        min-width: 80px;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
        justify-content: center;
        padding: 8px 12px;
        font-size: 12px;
    }
    
    .filtros-rapidos {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    
    .filtros-rapidos-group {
        justify-content: center;
    }
    
    .filtro-rapido {
        flex: 1;
        justify-content: center;
        padding: 6px 10px;
        font-size: 11px;
    }
    
    .table-modern {
        font-size: 11px;
        min-width: 600px;
    }
    
    .table-modern thead th,
    .table-modern tbody td {
        padding: 6px 8px;
    }
    
    .modal-container {
        padding: 24px;
    }
    
    .modal-icon {
        width: 52px;
        height: 52px;
    }
    
    .modal-icon i {
        font-size: 22px;
    }
    
    .modal-title {
        font-size: 18px;
    }
    
    .modal-footer {
        flex-direction: column;
    }
    
    .modal-footer .btn {
        justify-content: center;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 18px;
    }
    
    .page-subtitle {
        font-size: 12px;
    }
    
    .summary-value {
        font-size: 18px;
    }
    
    .filters-bar {
        padding: 12px;
    }
    
    .table-card {
        padding: 12px;
    }
    
    .filtros-rapidos-group {
        flex-wrap: wrap;
    }
    
    .filtro-rapido {
        flex: 1;
        min-width: calc(50% - 4px);
        justify-content: center;
        font-size: 10px;
        padding: 4px 8px;
    }
}
</style>