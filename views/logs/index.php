<?php if (!empty($modoImpressao)): ?><table><thead><tr><th>Data</th><th>Utilizador</th><th>Ação</th><th>Tabela</th><th>Registo</th><th>IP</th></tr></thead><tbody><?php foreach($logs as $log): ?><tr><td><?=htmlspecialchars($log['criado_em'])?></td><td><?=htmlspecialchars($log['usuario_nome']??'Sistema')?></td><td><?=htmlspecialchars(AuditoriaHelper::rotuloAcao($log['acao']))?></td><td><?=htmlspecialchars(AuditoriaHelper::rotuloTabela($log['tabela_afetada']))?></td><td><?=htmlspecialchars($log['registo_id']??'')?></td><td><?=htmlspecialchars($log['ip_origem']??'')?></td></tr><?php endforeach?></tbody></table><?php return; endif; ?>
<div class="modulo-cabecalho logs-cabecalho">
    <div><h1><i class="fa-solid fa-clipboard-list"></i> Logs de auditoria</h1><p>Histórico completo de operações registadas no sistema. Os registos não podem ser alterados ou eliminados.</p></div>
    <a class="btn btn-success" href="<?=URL_BASE?>/logs/exportar?<?=htmlspecialchars(http_build_query($filtros))?>"><i class="fa-solid fa-file-csv"></i> Exportar CSV</a>
</div>

<section class="filtros-logs">
    <form method="get" action="<?=URL_BASE?>/logs/index">
        <label>Utilizador
            <select name="usuario_id"><option value="">Todos</option><?php foreach($usuarios as $u):?><option value="<?=$u['id']?>" <?=$filtros['usuario_id']==$u['id']?'selected':''?>><?=htmlspecialchars($u['nome'])?></option><?php endforeach?></select>
        </label>
        <label>Ação
            <select name="acao"><option value="">Todas</option><?php foreach($opcoesAcoes as $chave=>$rotulo):?><option value="<?=htmlspecialchars($chave)?>" <?=$filtros['acao']===$chave?'selected':''?>><?=htmlspecialchars($rotulo)?></option><?php endforeach?></select>
        </label>
        <label>Tabela
            <select name="tabela_afetada"><option value="">Todas</option><?php foreach($opcoesTabelas as $chave=>$rotulo):?><option value="<?=htmlspecialchars($chave)?>" <?=$filtros['tabela_afetada']===$chave?'selected':''?>><?=htmlspecialchars($rotulo)?></option><?php endforeach?></select>
        </label>
        <label>Prioridade
            <select name="prioridade"><option value="">Todas</option><?php foreach(['baixa'=>'Baixa','media'=>'Média','alta'=>'Alta'] as $chave=>$rotulo):?><option value="<?=$chave?>" <?=$filtros['prioridade']===$chave?'selected':''?>><?=$rotulo?></option><?php endforeach?></select>
        </label>
        <label>De<input type="date" name="inicio" value="<?=htmlspecialchars($filtros['inicio'])?>"></label>
        <label>Até<input type="date" name="fim" value="<?=htmlspecialchars($filtros['fim'])?>"></label>
        <div class="filtros-acoes">
            <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <?php if(array_filter($filtros)):?><a class="btn btn-outline" href="<?=URL_BASE?>/logs/index">Limpar</a><?php endif;?>
        </div>
    </form>
</section>

<section class="tabela-logs">
    <div class="tabela-logs-cabecalho"><span><?=(int)($paginacao['total']??0)?> registo(s) encontrado(s)</span></div>
    <div class="tabela-scroll">
        <table>
            <thead><tr><th>Data e hora</th><th>Utilizador</th><th>Ação</th><th>Tabela</th><th>Registo</th><th>Prioridade</th><th></th></tr></thead>
            <tbody>
            <?php if(empty($logs)):?>
                <tr><td colspan="7" class="sem-logs"><i class="fa-solid fa-inbox"></i><span>Não foram encontrados logs para estes filtros.</span></td></tr>
            <?php endif; foreach($logs as $log): $tipo = AuditoriaHelper::tipoAcao($log['acao']); ?>
                <tr>
                    <td><?=htmlspecialchars(date('d/m/Y H:i',strtotime($log['criado_em'])))?></td>
                    <td><?=htmlspecialchars($log['usuario_nome']??'Sistema')?></td>
                    <td><span class="selo selo-<?=$tipo?>"><?=htmlspecialchars(AuditoriaHelper::rotuloAcao($log['acao']))?></span></td>
                    <td><?=htmlspecialchars(AuditoriaHelper::rotuloTabela($log['tabela_afetada']))?></td>
                    <td>#<?=htmlspecialchars($log['registo_id']??'—')?></td>
                    <td><span class="prioridade prioridade-<?=htmlspecialchars($log['prioridade'])?>"><?=htmlspecialchars(ucfirst($log['prioridade']))?></span></td>
                    <td><button class="btn btn-outline btn-sm" onclick="verDetalhes(<?= (int)$log['id']?>)"><i class="fa-solid fa-eye"></i> Detalhes</button></td>
                </tr>
            <?php endforeach?>
            </tbody>
        </table>
    </div>
    <?php if(($paginacao['paginas']??1)>1):?>
        <nav class="paginacao"><?php for($p=1;$p<=$paginacao['paginas'];$p++):?><a class="<?=((int)($_GET['pagina']??1)===$p)?'ativo':''?>" href="<?=URL_BASE?>/logs/index?<?=htmlspecialchars(http_build_query(array_merge($filtros,['pagina'=>$p])))?>"><?=$p?></a><?php endfor?></nav>
    <?php endif?>
</section>

<section id="detalhes-log" class="detalhes-log" hidden>
    <div><h2><i class="fa-solid fa-code-compare"></i> Alterações registadas</h2><button type="button" onclick="document.getElementById('detalhes-log').hidden=true">×</button></div>
    <pre></pre>
</section>

<style>
.logs-cabecalho{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:22px}
.modulo-cabecalho h1{margin:0;color:var(--navy-deep);font-size:26px}
.modulo-cabecalho h1 i{color:var(--green);margin-right:8px}
.modulo-cabecalho p{color:var(--muted);margin:6px 0 0;font-size:13px}
.filtros-logs,.tabela-logs{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:18px;box-shadow:var(--shadow-sm,0 1px 3px rgba(16,24,40,.04))}
.filtros-logs form{display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:14px 16px;align-items:end}
.filtros-logs label{display:grid;gap:6px;font-size:12px;font-weight:700;color:var(--ink)}
.filtros-logs input,.filtros-logs select{padding:9px 10px;border:1px solid var(--border);border-radius:8px;background:#fff;font:inherit}
.filtros-logs select{cursor:pointer}
.filtros-acoes{display:flex;gap:8px;grid-column:span 2}
.tabela-logs-cabecalho{display:flex;justify-content:flex-end;font-size:12px;color:var(--muted);font-weight:700;margin-bottom:10px}
.tabela-scroll{overflow:auto}
.tabela-logs table{width:100%;border-collapse:collapse;font-size:13px}
.tabela-logs th{background:var(--navy-deep);color:#fff;text-align:left;padding:11px;white-space:nowrap}
.tabela-logs td{padding:12px 11px;border-bottom:1px solid var(--border);vertical-align:middle}
.tabela-logs tbody tr:hover{background:var(--bg)}
.selo{display:inline-block;border-radius:20px;padding:4px 10px;font-size:11px;font-weight:700;white-space:nowrap}
.selo-sucesso{background:rgba(34,197,94,.12);color:var(--green-dark)}
.selo-info{background:rgba(37,99,235,.1);color:var(--blue)}
.selo-aviso{background:rgba(245,158,11,.14);color:#a3640a}
.selo-perigo{background:rgba(239,68,68,.1);color:var(--red)}
.prioridade{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.02em}
.prioridade-baixa{color:var(--muted)}
.prioridade-media{color:#a3640a}
.prioridade-alta{color:var(--red)}
.sem-logs{text-align:center!important;color:var(--muted);padding:36px!important}
.sem-logs i{display:block;font-size:26px;margin-bottom:8px;opacity:.5}
.paginacao{display:flex;gap:6px;padding-top:15px}
.paginacao a{border:1px solid var(--border);padding:6px 10px;border-radius:6px;text-decoration:none;color:var(--ink);font-size:13px}
.paginacao .ativo{background:var(--green);border-color:var(--green);color:#fff}
.detalhes-log{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow-sm,0 1px 3px rgba(16,24,40,.04))}
.detalhes-log>div{display:flex;justify-content:space-between;align-items:center}
.detalhes-log h2{margin:0;font-size:16px;color:var(--navy-deep)}
.detalhes-log h2 i{color:var(--green);margin-right:6px}
.detalhes-log button{border:0;background:none;font-size:22px;cursor:pointer;color:var(--muted);line-height:1}
.detalhes-log pre{padding:14px;background:#10263f;color:#d7f3e0;border-radius:8px;overflow:auto;margin-top:14px;font-size:12px}
@media(max-width:900px){.filtros-logs form{grid-template-columns:repeat(2,1fr)}.filtros-acoes{grid-column:span 2}.logs-cabecalho{flex-direction:column}}
</style>
<script>
function verDetalhes(id){
    fetch('<?=URL_BASE?>/logs/detalhes/'+id)
        .then(r=>r.json())
        .then(d=>{
            const el=document.getElementById('detalhes-log');
            el.hidden=false;
            el.querySelector('pre').textContent=JSON.stringify({dados_antigos:d.dados_antigos,dados_novos:d.dados_novos},null,2);
            el.scrollIntoView({behavior:'smooth',block:'nearest'});
        })
        .catch(()=>alert('Não foi possível carregar os detalhes do log.'));
}
</script>
