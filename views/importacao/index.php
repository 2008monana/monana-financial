<div class="modulo-cabecalho">
    <div><h1><i class="fa-solid fa-file-import"></i> Importar movimentos</h1><p>Envie uma planilha e associe as suas colunas aos campos financeiros do MonanaFinancial.</p></div>
    <a class="btn btn-outline" href="<?php echo URL_BASE; ?>/importacao/historico"><i class="fa-solid fa-clock-rotate-left"></i> Histórico</a>
</div>
<div class="cartao-ajuda-nota" style="margin-bottom:18px"><i class="fa-solid fa-table-list"></i> A sua planilha tem várias secções/folhas por mês, com colunas lado a lado de receita e despesa (tipo "Relatório Diário de Finanças")? <a href="<?php echo URL_BASE; ?>/importacao/modelo"><strong>Use o importador de Relatório Diário</strong></a> em vez do formulário abaixo — este aqui é só para uma tabela simples (uma transação por linha).</div>
<div class="importacao-grid">
    <section class="cartao-importacao">
        <div class="icone-importacao"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h2>Enviar ficheiro</h2>
        <p>Formatos aceites: Excel (.xlsx e .xls) ou CSV. O limite é de 10&nbsp;MB.</p>
        <div class="formatos-aceites">
            <span class="pilula"><i class="fa-solid fa-file-excel"></i> .xlsx</span>
            <span class="pilula"><i class="fa-solid fa-file-excel"></i> .xls</span>
            <span class="pilula"><i class="fa-solid fa-file-csv"></i> .csv</span>
        </div>
        <form method="post" action="<?php echo URL_BASE; ?>/importacao/upload" enctype="multipart/form-data" class="form-importacao">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <label class="zona-arrastar" for="ficheiro">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span class="zona-titulo">Clique para escolher a planilha</span>
                <span class="zona-nome" data-placeholder="Nenhum ficheiro selecionado">Nenhum ficheiro selecionado</span>
            </label>
            <input required id="ficheiro" type="file" name="ficheiro" accept=".xlsx,.xls,.csv" hidden>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-arrow-up-from-bracket"></i> Enviar e continuar</button>
        </form>
    </section>
    <aside class="cartao-ajuda">
        <h3><i class="fa-solid fa-circle-info"></i> Antes de importar</h3>
        <ol>
            <li><span class="passo-numero">1</span> A primeira linha deve conter os cabeçalhos das colunas.</li>
            <li><span class="passo-numero">2</span> No passo seguinte associe Data, Valor, Tipo, Filial e Categoria.</li>
            <li><span class="passo-numero">3</span> Linhas com valores inválidos ou erros de fórmula como <code>#REF!</code>, <code>#DIV/0!</code> ou <code>#VALUE!</code> não são gravadas.</li>
            <li><span class="passo-numero">4</span> Confirme a pré-visualização antes de processar a importação.</li>
        </ol>
        <div class="cartao-ajuda-nota"><i class="fa-solid fa-triangle-exclamation"></i> Se a sua planilha tiver várias secções, subtotais ou colunas mescladas, mantenha apenas uma tabela simples com cabeçalhos na primeira linha para melhores resultados.</div>
    </aside>
</div>
<style>
.modulo-cabecalho{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px}
.modulo-cabecalho h1{margin:0;color:var(--navy-deep);font-size:26px}
.modulo-cabecalho h1 i{color:var(--green);margin-right:9px}
.modulo-cabecalho p{margin:6px 0 0;color:var(--muted);font-size:13px}
.importacao-grid{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(280px,1fr);gap:20px;align-items:start}
.cartao-importacao,.cartao-ajuda{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:28px;box-shadow:var(--shadow-sm,0 1px 3px rgba(16,24,40,.04))}
.cartao-importacao{text-align:center}
.icone-importacao{width:58px;height:58px;display:grid;place-items:center;border-radius:14px;background:rgba(34,197,94,.12);color:var(--green-dark);font-size:25px;margin:0 auto 14px}
.cartao-importacao h2{margin:0 0 8px;color:var(--navy-deep)}
.cartao-importacao>p{color:var(--muted);font-size:13px;line-height:1.6;margin-bottom:14px}
.formatos-aceites{display:flex;justify-content:center;gap:8px;margin-bottom:22px}
.pilula{display:inline-flex;align-items:center;gap:6px;background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:5px 12px;font-size:12px;font-weight:700;color:var(--navy)}
.form-importacao{max-width:480px;margin:0 auto;text-align:left;display:grid;gap:14px}
.zona-arrastar{display:grid;place-items:center;gap:6px;text-align:center;padding:32px 16px;border:1.5px dashed var(--border);border-radius:12px;background:var(--bg);cursor:pointer;transition:border-color .15s,background .15s}
.zona-arrastar:hover{border-color:var(--green);background:rgba(34,197,94,.05)}
.zona-arrastar i{font-size:26px;color:var(--green-dark);margin-bottom:4px}
.zona-titulo{font-weight:700;font-size:14px;color:var(--navy-deep)}
.zona-nome{font-size:12px;color:var(--muted)}
.zona-nome.escolhido{color:var(--green-dark);font-weight:700}
.cartao-ajuda h3{margin-top:0;color:var(--navy-deep);font-size:15px}
.cartao-ajuda ol{list-style:none;padding:0;margin:0 0 16px;display:grid;gap:12px}
.cartao-ajuda li{display:flex;gap:10px;color:var(--muted);font-size:13px;line-height:1.6}
.passo-numero{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--navy-deep);color:#fff;font-size:11px;font-weight:700;display:grid;place-items:center;margin-top:1px}
.cartao-ajuda li code{background:var(--bg);padding:1px 5px;border-radius:4px;font-size:11px}
.cartao-ajuda-nota{display:flex;gap:8px;background:#fff8e6;border:1px solid #f5e3ad;color:#8a6510;border-radius:10px;padding:12px;font-size:12px;line-height:1.5}
.cartao-ajuda-nota i{margin-top:2px}
@media(max-width:760px){.importacao-grid{grid-template-columns:1fr}.modulo-cabecalho{flex-direction:column}}
</style>
<script>
document.getElementById('ficheiro').addEventListener('change', function () {
    const nomeEl = document.querySelector('.zona-nome');
    nomeEl.textContent = this.files.length ? this.files[0].name : nomeEl.dataset.placeholder;
    nomeEl.classList.toggle('escolhido', this.files.length > 0);
});
</script>
