<div class="modulo-cabecalho">
    <div><h1><i class="fa-solid fa-magnifying-glass-chart"></i> Rever antes de importar</h1><p>Nada foi gravado ainda. Confira a filial de cada folha e desmarque colunas que não devem virar transações.</p></div>
</div>

<form method="post" action="<?php echo URL_BASE; ?>/importacao/modelo-confirmar">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

    <?php foreach ($folhas as $folha): ?>
    <section class="cartao-folha">
        <div class="cartao-folha-cabecalho">
            <h2><i class="fa-solid fa-file-excel"></i> <?php echo htmlspecialchars($folha['nome']); ?></h2>
            <label class="filial-select">Filial
                <select name="filial[<?php echo htmlspecialchars($folha['nome']); ?>]" required>
                    <option value="">— selecione —</option>
                    <?php foreach ($filiais as $f): $sel = ($folha['filial_sugerida'] === 'viana' && stripos($f['nome'], 'viana') !== false) || ($folha['filial_sugerida'] === 'principal' && stripos($f['nome'], 'viana') === false); ?>
                        <option value="<?php echo (int) $f['id']; ?>" <?php echo $sel ? 'selected' : ''; ?>><?php echo htmlspecialchars($f['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="tabela-scroll">
            <table class="tabela-colunas">
                <thead><tr><th></th><th>Coluna</th><th>Bloco</th><th>Vira</th><th>Método</th><th>Soma detetada</th><th>Nº de lançamentos</th></tr></thead>
                <tbody>
                <?php foreach ($folha['colunas'] as $col): ?>
                    <tr>
                        <td><input type="checkbox" name="incluir[<?php echo htmlspecialchars($folha['nome']); ?>][<?php echo (int) $col['indice']; ?>]" value="1" checked></td>
                        <td><strong><?php echo htmlspecialchars($col['rotulo']); ?></strong></td>
                        <td><span class="selo selo-<?php echo $col['bloco'] === 'entrada' ? 'sucesso' : 'perigo'; ?>"><?php echo $col['bloco'] === 'entrada' ? 'Receita' : 'Despesa'; ?></span></td>
                        <td><?php echo ['venda' => 'Venda', 'devolucao' => 'Devolução', 'compra' => 'Compra', 'custo' => 'Custo'][$col['tipo_transacao']] ?? $col['tipo_transacao']; ?></td>
                        <td><?php echo ['numerario' => 'Numerário', 'transferencia' => 'Transferência', 'tpa' => 'TPA', 'outro' => 'Outro'][$col['metodo_pagamento']] ?? $col['metodo_pagamento']; ?></td>
                        <td><?php echo number_format($col['soma'], 2, ',', '.'); ?> Kz</td>
                        <td><?php echo (int) $col['contagem']; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endforeach; ?>

    <div class="acoes-confirmacao">
        <a class="btn btn-outline" href="<?php echo URL_BASE; ?>/importacao/modelo"><i class="fa-solid fa-arrow-left"></i> Cancelar</a>
        <button class="btn btn-primary" type="submit" onclick="return confirm('Confirma a importação? Serão criadas transações e categorias novas na base de dados.');"><i class="fa-solid fa-check"></i> Confirmar importação</button>
    </div>
</form>

<style>
.modulo-cabecalho{margin-bottom:20px}
.modulo-cabecalho h1{margin:0;color:var(--navy-deep);font-size:24px}
.modulo-cabecalho h1 i{color:var(--green);margin-right:8px}
.modulo-cabecalho p{margin:6px 0 0;color:var(--muted);font-size:13px}
.cartao-folha{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:16px;box-shadow:var(--shadow-sm,0 1px 3px rgba(16,24,40,.04))}
.cartao-folha-cabecalho{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap}
.cartao-folha-cabecalho h2{margin:0;font-size:16px;color:var(--navy-deep)}
.cartao-folha-cabecalho h2 i{color:var(--green);margin-right:6px}
.filial-select{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:700;color:var(--ink)}
.filial-select select{padding:7px 10px;border:1px solid var(--border);border-radius:8px;font:inherit}
.tabela-scroll{overflow:auto}
.tabela-colunas{width:100%;border-collapse:collapse;font-size:13px}
.tabela-colunas th{background:var(--navy-deep);color:#fff;text-align:left;padding:9px;white-space:nowrap}
.tabela-colunas td{padding:9px;border-bottom:1px solid var(--border);white-space:nowrap}
.selo{display:inline-block;border-radius:20px;padding:3px 9px;font-size:11px;font-weight:700}
.selo-sucesso{background:rgba(34,197,94,.12);color:var(--green-dark)}
.selo-perigo{background:rgba(239,68,68,.1);color:var(--red)}
.acoes-confirmacao{display:flex;justify-content:flex-end;gap:10px;margin-top:10px;padding-bottom:30px}
</style>
