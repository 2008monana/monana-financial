<div class="modulo-cabecalho">
    <div><h1><i class="fa-solid fa-table-list"></i> Importar Relatório Diário</h1><p>Para planilhas com várias folhas (uma por mês/filial) e colunas de receita e despesa lado a lado, como o modelo "Relatório Diário de Finanças".</p></div>
    <a class="btn btn-outline" href="<?php echo URL_BASE; ?>/importacao/index"><i class="fa-solid fa-arrow-left"></i> Importador simples</a>
</div>
<div class="importacao-grid">
    <section class="cartao-importacao">
        <div class="icone-importacao"><i class="fa-solid fa-cloud-arrow-up"></i></div>
        <h2>Enviar ficheiro</h2>
        <p>Formatos aceites: Excel (.xlsx e .xls). O limite é de 15&nbsp;MB. Todas as folhas do livro serão analisadas automaticamente.</p>
        <form method="post" action="<?php echo URL_BASE; ?>/importacao/modelo-upload" enctype="multipart/form-data" class="form-importacao">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <label class="zona-arrastar" for="ficheiro">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span class="zona-titulo">Clique para escolher a planilha</span>
                <span class="zona-nome" data-placeholder="Nenhum ficheiro selecionado">Nenhum ficheiro selecionado</span>
            </label>
            <input required id="ficheiro" type="file" name="ficheiro" accept=".xlsx,.xls" hidden>
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-arrow-up-from-bracket"></i> Analisar planilha</button>
        </form>
    </section>
    <aside class="cartao-ajuda">
        <h3><i class="fa-solid fa-circle-info"></i> Como funciona</h3>
        <ol>
            <li><span class="passo-numero">1</span> Cada folha do livro é analisada: a coluna com as datas do mês identifica onde começa a tabela diária.</li>
            <li><span class="passo-numero">2</span> Colunas de totais/saldos calculados (fórmulas) são detetadas e ignoradas automaticamente — não geram transações duplicadas.</li>
            <li><span class="passo-numero">3</span> Cada coluna de receita ou despesa restante vira uma categoria (criada automaticamente se não existir) e cada célula preenchida vira uma transação.</li>
            <li><span class="passo-numero">4</span> No ecrã seguinte pode rever, desmarcar colunas e escolher a filial de cada folha antes de confirmar — nada é gravado antes disso.</li>
        </ol>
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
.form-importacao{max-width:480px;margin:0 auto;text-align:left;display:grid;gap:14px}
.zona-arrastar{display:grid;place-items:center;gap:6px;text-align:center;padding:32px 16px;border:1.5px dashed var(--border);border-radius:12px;background:var(--bg);cursor:pointer;transition:border-color .15s,background .15s}
.zona-arrastar:hover{border-color:var(--green);background:rgba(34,197,94,.05)}
.zona-arrastar i{font-size:26px;color:var(--green-dark);margin-bottom:4px}
.zona-titulo{font-weight:700;font-size:14px;color:var(--navy-deep)}
.zona-nome{font-size:12px;color:var(--muted)}
.zona-nome.escolhido{color:var(--green-dark);font-weight:700}
.cartao-ajuda h3{margin-top:0;color:var(--navy-deep);font-size:15px}
.cartao-ajuda ol{list-style:none;padding:0;margin:0;display:grid;gap:12px}
.cartao-ajuda li{display:flex;gap:10px;color:var(--muted);font-size:13px;line-height:1.6}
.passo-numero{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--navy-deep);color:#fff;font-size:11px;font-weight:700;display:grid;place-items:center;margin-top:1px}
@media(max-width:760px){.importacao-grid{grid-template-columns:1fr}.modulo-cabecalho{flex-direction:column}}
</style>
<script>
document.getElementById('ficheiro').addEventListener('change', function () {
    const nomeEl = document.querySelector('.zona-nome');
    nomeEl.textContent = this.files.length ? this.files[0].name : nomeEl.dataset.placeholder;
    nomeEl.classList.toggle('escolhido', this.files.length > 0);
});
</script>
