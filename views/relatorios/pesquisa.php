<?php
/**
 * Pesquisa de Movimentos
 * Barra de pesquisa + selects (Descrição, Tipo, Categoria, Período: Diário/Mensal/Anual/Filial)
 * Exibe toda a informação relacionada com a descrição/tipo pesquisados.
 */
$fmt = static fn($v) => number_format((float) $v, 2, ',', '.') . ' Kz';

$rotulosTipos = [
    'venda' => ['r' => 'Venda', 'c' => '#0a8f3c', 'bg' => '#e5f5ea'],
    'compra' => ['r' => 'Compra', 'c' => '#b45309', 'bg' => '#fdf1e0'],
    'despesa' => ['r' => 'Despesa', 'c' => '#c62828', 'bg' => '#fdeaea'],
    'devolucao' => ['r' => 'Devolução', 'c' => '#1565c0', 'bg' => '#e8f1fb'],
    'transferencia' => ['r' => 'Transferência', 'c' => '#6a1b9a', 'bg' => '#f3eafa'],
];

// Monta a query string atual (sem 'exportar') para os botões de exportação
$queryAtual = $_GET;
unset($queryAtual['exportar']);
$qsExport = http_build_query($queryAtual);
?>
<div class="relatorio-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
    <div>
        <h2 style="margin:0; color:#123c6b;"><i class="fa-solid fa-magnifying-glass-chart" style="color:#1d5fa8;"></i> Pesquisa de Movimentos</h2>
        <p style="margin:4px 0 0; color:#667085;">Encontre movimentos por <strong>descrição</strong> e <strong>tipo</strong>, combinando filtros de categoria, empresa, filial e período.</p>
    </div>
    <?php if (!empty($temPesquisa)): ?>
    <div style="display:flex; gap:8px;">
        <a class="btn btn-outline" href="<?php echo URL_BASE; ?>/relatorios/pesquisa?<?php echo htmlspecialchars($qsExport); ?>&exportar=excel">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a class="btn btn-outline" href="<?php echo URL_BASE; ?>/relatorios/pesquisa?<?php echo htmlspecialchars($qsExport); ?>&exportar=pdf">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- ===== FORMULÁRIO DE PESQUISA ===== -->
<form method="get" action="<?php echo URL_BASE; ?>/relatorios/pesquisa" class="filtro-form" id="formPesquisa"
      style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 1px 6px rgba(18,60,107,.10); margin-bottom:20px; border:1px solid #e6ebf2;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; align-items:end;">

        <!-- Barra de pesquisa de descrição -->
        <div style="grid-column:span 2; min-width:260px;">
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Descrição</label>
            <div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#98a2b3; font-size:13px;"></i>
                <input type="text" name="descricao" id="inputDescricao" value="<?php echo htmlspecialchars($descricao); ?>"
                       placeholder="Digite ou escolha uma descrição..." list="listaDescricoes" autocomplete="off"
                       style="width:100%; padding:9px 10px 9px 32px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
            </div>
            <datalist id="listaDescricoes">
                <?php foreach ($descricoesDisponiveis as $d): ?>
                    <option value="<?php echo htmlspecialchars($d); ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>

        <!-- Select de Tipo (múltiplos permitidos) -->
        <div>
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Tipo</label>
            <select name="tipo[]" multiple size="1" id="selTipo"
                    style="width:100%; padding:8px 10px; border:1px solid #d0d5dd; border-radius:8px; min-height:38px; box-sizing:border-box; font-size:14px;"
                    title="Segure Ctrl (ou Cmd no Mac) para escolher vários tipos">
                <?php foreach ($rotulosTipos as $valor => $info): ?>
                    <option value="<?php echo $valor; ?>" <?php echo in_array($valor, $tiposSelecionados, true) ? 'selected' : ''; ?>>
                        <?php echo $info['r']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color:#98a2b3;">Ctrl+clique: vários tipos</small>
        </div>

        <!-- Select de Categoria (filtra no cliente) -->
        <div>
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Categoria</label>
            <select id="selCategoria" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <option value="">Todas as categorias</option>
                <?php foreach ($categoriasDisponiveis as $c): ?>
                    <option value="<?php echo htmlspecialchars(mb_strtolower($c['nome'])); ?>">
                        <?php echo htmlspecialchars($c['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Select de Período -->
        <div>
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Período</label>
            <select name="periodo" id="selPeriodo" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <option value="todos"   <?php echo $periodo === 'todos' ? 'selected' : ''; ?>>Todos os tempos</option>
                <option value="diario"  <?php echo $periodo === 'diario' ? 'selected' : ''; ?>>Diário</option>
                <option value="mensal"  <?php echo $periodo === 'mensal' ? 'selected' : ''; ?>>Mensal</option>
                <option value="anual"   <?php echo $periodo === 'anual' ? 'selected' : ''; ?>>Anual</option>
                <option value="filial"  <?php echo $periodo === 'filial' ? 'selected' : ''; ?>>Por Filial</option>
            </select>
        </div>

        <!-- Campos dinâmicos conforme o período -->
        <div id="campoData" style="<?php echo $periodo === 'diario' ? '' : 'display:none;'; ?>">
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Data</label>
            <input type="date" name="data" value="<?php echo htmlspecialchars($data); ?>"
                   style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
        </div>

        <div class="camposMesAno" style="<?php echo in_array($periodo, ['mensal', 'anual', 'filial'], true) ? '' : 'display:none;'; ?>">
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Mês</label>
            <select name="mes" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <?php foreach ($meses as $num => $nome): ?>
                    <option value="<?php echo $num; ?>" <?php echo $num === $mes ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="camposMesAno" style="<?php echo in_array($periodo, ['mensal', 'anual', 'filial'], true) ? '' : 'display:none;'; ?>">
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Ano</label>
            <select name="ano" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <?php foreach ($anos as $a): ?>
                    <option value="<?php echo $a; ?>" <?php echo $a === $ano ? 'selected' : ''; ?>><?php echo $a; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Empresa (Super Admin) -->
        <?php if ($perfil === 'super_admin'): ?>
        <div>
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Empresa</label>
            <select name="empresa_id" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <option value="0">Todas as empresas</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?php echo $e['id']; ?>" <?php echo ((int)($empresaFiltro ?? 0)) === (int)$e['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($e['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Filial (aparece quando período = filial) -->
        <div id="campoFilial" style="<?php echo $periodo === 'filial' ? '' : 'display:none;'; ?>">
            <label style="font-weight:600; display:block; margin-bottom:5px; color:#344054; font-size:13px;">Filial</label>
            <select name="filial_id" style="width:100%; padding:9px 10px; border:1px solid #d0d5dd; border-radius:8px; box-sizing:border-box; font-size:14px;">
                <option value="0">Todas as filiais</option>
                <?php foreach ($filiais as $f): ?>
                    <option value="<?php echo $f['id']; ?>" <?php echo (int)$filialId === (int)$f['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($f['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary" style="padding:9px 20px; border-radius:8px;">
                <i class="fa-solid fa-magnifying-glass"></i> Pesquisar
            </button>
            <a href="<?php echo URL_BASE; ?>/relatorios/pesquisa" class="btn btn-outline" style="padding:9px 16px; border-radius:8px;">Limpar</a>
        </div>
    </div>
</form>

<!-- ===== RESULTADOS ===== -->
<?php if (!empty($temPesquisa)): ?>

    <!-- Cartões de totais -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:12px; margin-bottom:16px;">
        <div class="kpi-card" style="background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 1px 4px rgba(0,0,0,.08); border-left:4px solid #1d5fa8;">
            <small style="color:#667085;"><i class="fa-solid fa-list-check"></i> Registos encontrados</small>
            <div style="font-size:22px; font-weight:700; color:#123c6b;"><?php echo $totais['total_registos']; ?></div>
        </div>
        <div class="kpi-card" style="background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 1px 4px rgba(0,0,0,.08); border-left:4px solid #0a8f3c;">
            <small style="color:#667085;"><i class="fa-solid fa-arrow-trend-up"></i> Entradas</small>
            <div style="font-size:22px; font-weight:700; color:#0a8f3c;"><?php echo $fmt($totais['entradas']); ?></div>
        </div>
        <div class="kpi-card" style="background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 1px 4px rgba(0,0,0,.08); border-left:4px solid #c62828;">
            <small style="color:#667085;"><i class="fa-solid fa-arrow-trend-down"></i> Saídas</small>
            <div style="font-size:22px; font-weight:700; color:#c62828;"><?php echo $fmt($totais['saidas']); ?></div>
        </div>
        <div class="kpi-card" style="background:#fff; border-radius:10px; padding:14px 16px; box-shadow:0 1px 4px rgba(0,0,0,.08); border-left:4px solid <?php echo $totais['saldo'] >= 0 ? '#0a8f3c' : '#c62828'; ?>;">
            <small style="color:#667085;"><i class="fa-solid fa-scale-balanced"></i> Saldo</small>
            <div style="font-size:22px; font-weight:700; color:<?php echo $totais['saldo'] >= 0 ? '#0a8f3c' : '#c62828'; ?>;"><?php echo $fmt($totais['saldo']); ?></div>
        </div>
    </div>

    <div class="card" style="background:#fff; border-radius:12px; box-shadow:0 1px 6px rgba(18,60,107,.10); overflow:auto; border:1px solid #e6ebf2;">
        <?php if (empty($transacoes)): ?>
            <p style="padding:32px; text-align:center; color:#777;">
                <i class="fa-solid fa-circle-info" style="font-size:22px; color:#c9d4e3; display:block; margin-bottom:8px;"></i>
                Nenhum movimento encontrado com esses critérios.
            </p>
        <?php else: ?>
        <table class="tabela" id="tblResultados" style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#123c6b; color:#fff; text-align:left;">
                    <th style="padding:11px 12px;">Data</th>
                    <th style="padding:11px 12px;">Tipo</th>
                    <th style="padding:11px 12px;">Descrição</th>
                    <th style="padding:11px 12px;">Categoria</th>
                    <th style="padding:11px 12px;">Filial</th>
                    <?php if ($perfil === 'super_admin'): ?><th style="padding:11px 12px;">Empresa</th><?php endif; ?>
                    <th style="padding:11px 12px; text-align:right;">Valor</th>
                    <th style="padding:11px 12px;">Registado por</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transacoes as $t): ?>
                <tr style="border-bottom:1px solid #eef1f5;" data-categoria="<?php echo htmlspecialchars(mb_strtolower($t['categoria_nome'] ?? '')); ?>">
                    <td style="padding:10px 12px; white-space:nowrap; color:#475467;"><?php echo date('d/m/Y', strtotime($t['data_transacao'])); ?></td>
                    <td style="padding:10px 12px;">
                        <span style="display:inline-block; padding:3px 10px; border-radius:999px; font-weight:600; font-size:12px;
                                     background:<?php echo $rotulosTipos[$t['tipo']]['bg'] ?? '#eef1f5'; ?>;
                                     color:<?php echo $rotulosTipos[$t['tipo']]['c'] ?? '#333'; ?>;">
                            <?php echo $rotulosTipos[$t['tipo']]['r'] ?? ucfirst($t['tipo']); ?>
                        </span>
                    </td>
                    <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['descricao'] ?? '-'); ?></td>
                    <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['categoria_nome'] ?? '-'); ?></td>
                    <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['filial_nome'] ?? '-'); ?></td>
                    <?php if ($perfil === 'super_admin'): ?>
                    <td style="padding:10px 12px;"><?php echo htmlspecialchars($t['empresa_nome'] ?? '-'); ?></td>
                    <?php endif; ?>
                    <td style="padding:10px 12px; text-align:right; font-weight:700; white-space:nowrap; color:<?php echo $t['tipo'] === 'venda' ? '#0a8f3c' : '#c62828'; ?>;">
                        <?php echo $fmt($t['valor']); ?>
                    </td>
                    <td style="padding:10px 12px; color:#475467;"><?php echo htmlspecialchars($t['usuario_nome'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f5f7fa; font-weight:700;">
                    <td colspan="<?php echo $perfil === 'super_admin' ? 6 : 5; ?>" style="padding:12px;">TOTAIS</td>
                    <td style="padding:12px; text-align:right; white-space:nowrap;">
                        <span style="color:#0a8f3c;">Entradas: <?php echo $fmt($totais['entradas']); ?></span><br>
                        <span style="color:#c62828;">Saídas: <?php echo $fmt($totais['saidas']); ?></span><br>
                        <span style="color:<?php echo $totais['saldo'] >= 0 ? '#0a8f3c' : '#c62828'; ?>; font-size:15px;">Saldo: <?php echo $fmt($totais['saldo']); ?></span>
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card" style="background:#fff; border-radius:12px; padding:40px; text-align:center; color:#667085; box-shadow:0 1px 6px rgba(18,60,107,.10); border:1px solid #e6ebf2;">
        <i class="fa-solid fa-magnifying-glass" style="font-size:42px; color:#c9d4e3;"></i>
        <p style="margin-top:14px; font-size:15px;">Use a barra de pesquisa acima para procurar movimentos por <strong>descrição</strong> e <strong>tipo</strong>.<br>
        Pode combinar com os filtros de categoria, empresa, filial e período (<em>diário, mensal, anual ou por filial</em>).</p>
    </div>
<?php endif; ?>

<script>
(function () {
    // Mostrar/ocultar campos conforme o período escolhido
    var selPeriodo = document.getElementById('selPeriodo');
    function atualizarCampos() {
        var v = selPeriodo.value;
        document.getElementById('campoData').style.display = (v === 'diario') ? '' : 'none';
        var mostrarMesAno = (v === 'mensal' || v === 'anual' || v === 'filial');
        document.querySelectorAll('.camposMesAno').forEach(function (el) {
            el.style.display = mostrarMesAno ? '' : 'none';
        });
        document.getElementById('campoFilial').style.display = (v === 'filial') ? '' : 'none';
    }
    if (selPeriodo) { selPeriodo.addEventListener('change', atualizarCampos); atualizarCampos(); }

    // Filtro rápido por categoria (no cliente)
    var selCat = document.getElementById('selCategoria');
    if (selCat) {
        selCat.addEventListener('change', function () {
            var alvo = this.value;
            document.querySelectorAll('#tblResultados tbody tr').forEach(function (tr) {
                var cat = tr.getAttribute('data-categoria') || '';
                tr.style.display = (!alvo || cat === alvo) ? '' : 'none';
            });
        });
    }
})();
</script>
