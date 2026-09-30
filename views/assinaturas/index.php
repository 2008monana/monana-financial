<?php
require_once CAMINHO_RAIZ . '/views/assinaturas/_estilos.php';

$agora = time();
$activasCount = $carenciaCount = $bloqueadasCount = 0;
foreach ($todos as $l) {
    $st = $l['estado_calc'];
    if ($st === 'gratuita' || $st === 'activa') $activasCount++;
    elseif ($st === 'carencia') $carenciaCount++;
    elseif ($st === 'bloqueada') $bloqueadasCount++;
}
$linkBase = URL_BASE . '/assinaturas';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-crown"></i> Assinaturas</h1>
        <p class="page-subtitle">Gerir planos, pagamentos e acessos das empresas</p>
    </div>
    <div class="page-header-right">
        <a href="<?= $linkBase ?>/planos" class="btn btn-secondary"><i class="fas fa-tags"></i> Planos</a>
    </div>
</div>

<section class="stats-grid">
    <article class="stat-card">
        <div class="stat-icon green"><i class="fas fa-coins"></i></div>
        <div><span class="stat-valor"><?= htmlspecialchars(FormatacaoHelper::moeda($receita_mes)) ?></span><span class="stat-legenda">Receita do mês</span></div>
    </article>
    <article class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-circle-check"></i></div>
        <div><span class="stat-valor"><?= (int) $activasCount ?></span><span class="stat-legenda">Empresas activas</span></div>
    </article>
    <article class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        <div><span class="stat-valor"><?= (int) $carenciaCount ?></span><span class="stat-legenda">A vencer / em carência</span></div>
    </article>
    <article class="stat-card">
        <div class="stat-icon red"><i class="fas fa-lock"></i></div>
        <div><span class="stat-valor"><?= (int) $bloqueadasCount ?></span><span class="stat-legenda">Bloqueadas</span></div>
    </article>
</section>

<section class="filtros-logs">
    <form method="get" action="<?= $linkBase ?>">
        <label>Estado
            <select name="estado">
                <option value="">Todos</option>
                <option value="gratuita" <?= $filtros['estado']==='gratuita'?'selected':'' ?>>Gratuita</option>
                <option value="activa" <?= $filtros['estado']==='activa'?'selected':'' ?>>Activa</option>
                <option value="carencia" <?= $filtros['estado']==='carencia'?'selected':'' ?>>Em carência</option>
                <option value="bloqueada" <?= $filtros['estado']==='bloqueada'?'selected':'' ?>>Bloqueada</option>
            </select>
        </label>
        <label>Plano
            <select name="plano_id">
                <option value="0">Todos</option>
                <?php foreach ($planos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (int) $filtros['plano_id']===(int) $p['id']?'selected':'' ?>><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Vencimento
            <select name="vencimento">
                <option value="">Todos</option>
                <option value="7d" <?= $filtros['vencimento']==='7d'?'selected':'' ?>>Vence em 7 dias</option>
                <option value="vencidas" <?= $filtros['vencimento']==='vencidas'?'selected':'' ?>>Já vencidas</option>
            </select>
        </label>
        <label>Pesquisar empresa
            <input type="text" name="pesquisa" value="<?= htmlspecialchars($filtros['pesquisa']) ?>" placeholder="Nome ou NIF">
        </label>
        <div class="filtros-acoes">
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="<?= $linkBase ?>" class="btn btn-secondary"><i class="fas fa-rotate-left"></i> Limpar</a>
        </div>
    </form>
</section>

<?php if (!$itens): ?>
    <div class="empty-state">
        <i class="fas fa-crown"></i>
        <h3>Nenhuma assinatura encontrada</h3>
        <p>Ajuste os filtros ou aguarde que a migração crie as assinaturas iniciais das empresas.</p>
        <a href="<?= URL_BASE ?>/empresas/criar" class="btn btn-primary"><i class="fas fa-plus"></i> Nova Empresa</a>
    </div>
<?php else: ?>
<section class="tabela-logs">
    <div class="tabela-logs-cabecalho"><span><?= (int) $paginacao['total'] ?> empresa(s)</span></div>
    <div class="tabela-scroll">
        <table class="assin-tabela">
            <thead>
                <tr>
                    <th>Empresa</th><th>Plano</th><th>Estado</th><th>Início</th><th>Vencimento</th><th>Valor</th><th class="col-acoes">Acções</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($itens as $l):
                $st = $l['estado_calc'];
                $ed = $l['estado_data'];
                $cls = $st === 'carencia' ? 'assin-linha-carencia' : ($st === 'bloqueada' ? 'assin-linha-bloqueada' : '');
                $linkWa = null;
                if (!empty($l['empresa_telefone'])) {
                    $tel = preg_replace('/\D/', '', (string) $l['empresa_telefone']);
                    if (strlen((string) $tel) >= 9) {
                        $linkWa = 'https://wa.me/' . (strlen($tel) === 9 && str_starts_with($tel, '9') ? '244' . $tel : $tel)
                                . '?text=' . urlencode('Olá! Falo da empresa ' . $l['empresa_nome'] . '.');
                    }
                }
            ?>
                <tr class="<?= $cls ?>">
                    <td>
                        <div class="assin-empresa-cell">
                            <span class="assin-avatar"><?= htmlspecialchars(iniciaisNome($l['empresa_nome'])) ?></span>
                            <span>
                                <span class="nome"><?= htmlspecialchars($l['empresa_nome']) ?></span>
                                <span class="nif"><?= htmlspecialchars($l['empresa_nif'] ?: '—') ?></span>
                            </span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($l['plano_nome'] ?? '') ?></td>
                    <td><?= assinSeloEstado($st) ?></td>
                    <td><?= assinDataBr($ed['inicio']) ?></td>
                    <td>
                        <?php if ($st === 'gratuita'): ?>
                            —<span class="assin-sub">sem fim</span>
                        <?php elseif ($ed['fim'] !== null): ?>
                            <?= assinDataBr($ed['fim']) ?>
                            <?php if ($st === 'activa'): ?>
                                <span class="assin-sub">faltam <?= assinTempoLegivel((int) $ed['segundos_restantes']) ?></span>
                            <?php elseif ($st === 'carencia'): ?>
                                <span class="assin-sub laranja">bloqueia em <?= assinTempoLegivel((int) $ed['segundos_restantes']) ?></span>
                            <?php else: ?>
                                <span class="assin-sub laranja">vencida</span>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(FormatacaoHelper::moeda((float) ($ed['linha']['valor_acordado'] ?? 0))) ?></td>
                    <td class="col-acoes">
                        <a class="btn btn-sm btn-primary" href="<?= $linkBase ?>/empresa/<?= (int) $l['empresa_id'] ?>"><i class="fas fa-sliders"></i> Gerir</a>
                        <?php if ($linkWa !== null): ?>
                            <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($linkWa) ?>" target="_blank" rel="noopener" title="Falar no WhatsApp com a empresa"><i class="fa-brands fa-whatsapp"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (($paginacao['paginas'] ?? 1) > 1): ?>
        <nav class="paginacao"><?php for($p=1;$p<=$paginacao['paginas'];$p++):?><a class="<?= ((int)$paginacao['pagina']===$p)?'ativo':'' ?>" href="<?= $linkBase ?>?<?= htmlspecialchars(http_build_query(array_merge($filtros, ['pagina'=>$p]))) ?>"><?= $p ?></a><?php endfor?></nav>
    <?php endif; ?>
</section>
<?php endif; ?>
