<?php
/**
 * Relatório por Filial
 * Super Admin: Seleciona empresa → depois filial
 * Admin Empresa: Apenas filiais da sua empresa
 */
?>

<div class="relatorio-header">
    <div class="relatorio-header-left">
        <h1 class="relatorio-titulo">
            <i class="fas fa-store-alt"></i>
            Relatório por Filial
            <?php if (!empty($filial)): ?>
            <span class="titulo-badge"><?php echo htmlspecialchars($filial['nome']); ?></span>
            <?php endif; ?>
        </h1>
        <p class="relatorio-subtitulo">
            <?php if ($perfil === 'super_admin'): ?>
                <span class="empresa-tag global">
                    <i class="fas fa-globe"></i> Todas as Empresas
                </span>
            <?php else: ?>
                <span class="empresa-tag">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($empresaNome ?? 'Minha Empresa'); ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($filial)): ?>
            <span style="color:var(--muted); font-size:13px;">
                <i class="fa-regular fa-calendar"></i> <?php echo $nomeMes . ' ' . $ano; ?>
                <i class="fa-regular fa-clock" style="margin-left:8px;"></i> <?php echo count($transacoes); ?> transações
            </span>
            <?php endif; ?>
        </p>
    </div>
    <div class="relatorio-header-right">
        <a href="<?php echo URL_BASE; ?>/relatorios/index" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- ============================================
     FILTROS - SUPER ADMIN vs ADMIN EMPRESA
     ============================================ -->
<div class="relatorio-filtros">
    <form method="GET" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; width:100%;" id="formFiltroFilial">
        
        <?php if ($perfil === 'super_admin'): ?>
        <!-- ==========================================
             SUPER ADMIN: Seleciona Empresa → Filial
             ========================================== -->
        <div class="filter-group">
            <label><i class="fas fa-building"></i> Empresa</label>
            <select name="empresa_id" id="selectEmpresa" onchange="carregarFiliais(this.value)">
                <option value="0">Todas as Empresas</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?php echo $emp['id']; ?>" 
                        <?php echo ($empresaSelecionada ?? 0) == $emp['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($emp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label><i class="fas fa-store-alt"></i> Filial</label>
            <select name="id" id="selectFilial" required>
                <option value="0">Selecione uma filial</option>
                <?php if (!empty($filiais)): ?>
                    <?php foreach ($filiais as $f): ?>
                    <option value="<?php echo $f['id']; ?>" 
                            <?php echo ($filial['id'] ?? 0) == $f['id'] ? 'selected' : ''; ?>
                            data-empresa="<?php echo $f['empresa_id']; ?>">
                        <?php echo htmlspecialchars($f['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <!-- Botão Filtrar (Super Admin) -->
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm" id="btnFiltrar">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <a href="<?php echo URL_BASE; ?>/relatorios/filial" class="btn btn-outline btn-sm">
                <i class="fas fa-undo"></i> Limpar
            </a>
        </div>

        <?php else: ?>
        <!-- ==========================================
             ADMIN EMPRESA: Apenas filiais da sua empresa
             ========================================== -->
        <div class="filter-group">
            <label><i class="fas fa-store-alt"></i> Filial</label>
            <select name="id" required>
                <option value="0">Selecione uma filial</option>
                <?php foreach ($filiais as $f): ?>
                <option value="<?php echo $f['id']; ?>" <?php echo ($filial['id'] ?? 0) == $f['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Botão Filtrar (Admin Empresa) -->
        <div class="filter-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-search"></i> Filtrar
            </button>
            <a href="<?php echo URL_BASE; ?>/relatorios/filial" class="btn btn-outline btn-sm">
                <i class="fas fa-undo"></i> Limpar
            </a>
        </div>
        <?php endif; ?>

        <!-- Filtros de Período (comum a ambos) -->
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
    </form>
</div>

<!-- ============================================
     CONTEÚDO - SÓ MOSTRA SE HOUVER FILIAL SELECIONADA
     ============================================ -->
<?php if (!empty($filial)): ?>

<!-- Resumo -->
<div class="relatorio-resumo grid-3">
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-right"></i> Total Entradas</span>
        <span class="resumo-valor positivo"><?php echo number_format($totais['entradas'], 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $totais['total']; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-arrow-left"></i> Total Saídas</span>
        <span class="resumo-valor negativo"><?php echo number_format($totais['saidas'], 0, ',', '.'); ?> Kz</span>
        <span class="resumo-detalhe"><?php echo $totais['total']; ?> transações</span>
    </div>
    <div class="resumo-card">
        <span class="resumo-label"><i class="fa-regular fa-scale-balanced"></i> Saldo</span>
        <span class="resumo-valor <?php echo $totais['saldo'] >= 0 ? 'positivo' : 'negativo'; ?>">
            <?php echo number_format($totais['saldo'], 0, ',', '.'); ?> Kz
        </span>
    </div>
</div>

<!-- Tabela -->
<div class="relatorio-tabela">
    <div class="tabela-header">
        <h3><i class="fas fa-list-ul"></i> Transações</h3>
        <span class="badge-info"><i class="fas fa-file-lines"></i> <?php echo count($transacoes); ?> registos</span>
    </div>
    <div class="tabela-scroll">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <?php if ($perfil === 'super_admin'): ?>
                    <th>Empresa</th>
                    <?php endif; ?>
                    <th>Descrição</th>
                    <th>Tipo</th>
                    <th class="text-right">Valor</th>
                    <th>Utilizador</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transacoes)): ?>
                <tr>
                    <td colspan="<?php echo $perfil === 'super_admin' ? 6 : 5; ?>" class="text-center">
                        <div class="relatorio-vazio">
                            <i class="fas fa-inbox"></i>
                            <h4>Nenhuma transação registada</h4>
                            <p>Não há movimentos financeiros para esta filial neste período.</p>
                        </div>
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
                    <td><?php echo htmlspecialchars($t['descricao'] ?? '-'); ?></td>
                    <td>
                        <span class="badge <?php echo $t['tipo'] === 'venda' ? 'badge-success' : 'badge-danger'; ?>">
                            <?php echo htmlspecialchars(ucfirst($t['tipo'])); ?>
                        </span>
                    </td>
                    <td class="text-right <?php echo $t['tipo'] === 'venda' ? 'positive' : 'negative'; ?>">
                        <?php echo number_format($t['valor'], 0, ',', '.'); ?> Kz
                    </td>
                    <td><?php echo htmlspecialchars($t['usuario_nome'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- ==========================================
     MENSAGEM QUANDO NENHUMA FILIAL ESTÁ SELECIONADA
     ========================================== -->
<div class="relatorio-tabela">
    <div class="tabela-header">
        <h3><i class="fas fa-store-alt"></i> Selecione uma filial</h3>
    </div>
    <div class="tabela-scroll">
        <div class="relatorio-vazio" style="padding:60px 20px;">
            <i class="fas fa-store-alt" style="font-size:56px; color:var(--muted); opacity:0.3;"></i>
            <h4 style="margin-top:16px;">Nenhuma filial selecionada</h4>
            <p style="color:var(--muted);">
                <?php if ($perfil === 'super_admin'): ?>
                    Selecione uma empresa e depois uma filial para visualizar o relatório.
                <?php else: ?>
                    Selecione uma filial da sua empresa para visualizar o relatório.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================
     SCRIPT: CARREGAR FILIAIS POR EMPRESA (SUPER ADMIN)
     ============================================ -->
<?php if ($perfil === 'super_admin'): ?>
<script>
/**
 * Carrega as filiais com base na empresa selecionada
 */
function carregarFiliais(empresaId) {
    const selectFilial = document.getElementById('selectFilial');
    const filiais = <?php echo json_encode($filiaisTodas ?? []); ?>;
    
    // Limpar select
    selectFilial.innerHTML = '<option value="0">Selecione uma filial</option>';
    
    if (empresaId == 0) {
        // Mostrar todas as filiais
        filiais.forEach(function(filial) {
            const option = document.createElement('option');
            option.value = filial.id;
            option.textContent = filial.nome;
            option.dataset.empresa = filial.empresa_id;
            selectFilial.appendChild(option);
        });
    } else {
        // Mostrar apenas filiais da empresa selecionada
        filiais.forEach(function(filial) {
            if (filial.empresa_id == empresaId) {
                const option = document.createElement('option');
                option.value = filial.id;
                option.textContent = filial.nome;
                option.dataset.empresa = filial.empresa_id;
                selectFilial.appendChild(option);
            }
        });
    }
    
    // Se houver apenas uma filial, seleciona automaticamente
    if (selectFilial.options.length === 2) {
        selectFilial.value = selectFilial.options[1].value;
    }
}

// ============================================
// INICIALIZAÇÃO: Se já houver empresa selecionada, carregar filiais
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const selectEmpresa = document.getElementById('selectEmpresa');
    const empresaSelecionada = selectEmpresa ? selectEmpresa.value : '0';
    
    if (empresaSelecionada !== '0') {
        carregarFiliais(empresaSelecionada);
    }
    
    // ============================================
    // SUBMISSÃO AUTOMÁTICA AO SELECIONAR FILIAL
    // ============================================
    const selectFilial = document.getElementById('selectFilial');
    if (selectFilial) {
        selectFilial.addEventListener('change', function() {
            if (this.value !== '0') {
                document.getElementById('formFiltroFilial').submit();
            }
        });
    }
    
    // ============================================
    // SUBMISSÃO AUTOMÁTICA AO SELECIONAR EMPRESA
    // ============================================
    if (selectEmpresa) {
        selectEmpresa.addEventListener('change', function() {
            carregarFiliais(this.value);
            // Se já houver uma filial selecionada, submeter
            const selectFilial = document.getElementById('selectFilial');
            if (selectFilial && selectFilial.value !== '0') {
                document.getElementById('formFiltroFilial').submit();
            }
        });
    }
});
</script>
<?php endif; ?>
