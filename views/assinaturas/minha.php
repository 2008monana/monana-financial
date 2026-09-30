<?php
/**
 * "Minha Assinatura" — vista de leitura para o admin da empresa.
 * Variáveis: $empresa, $estado, $pagamentos, $planos, $carencia_horas, $csrf_token
 */
require_once CAMINHO_RAIZ . '/views/assinaturas/_estilos.php';

$estadoStr = $estado['estado'] ?? 'gratuita';
$segRest   = (int) ($estado['segundos_restantes'] ?? 0);
$diasRest  = (int) ceil($segRest / 86400);
$temFim    = !empty($estado['fim']);
$corBarra  = '#22c55e';
if ($temFim || $estadoStr === 'carencia') {
    if ($segRest <= 86400)            $corBarra = '#ef4444';
    elseif ($segRest <= 3 * 86400)    $corBarra = '#f59e0b';
}
// Percentagem aproximada da barra (base: 1 ano se sem referência melhor)
$periodoTotal = 365 * 86400;
if (!empty($estado['linha']['id'])) {
    $iniTs = !empty($estado['inicio']) ? strtotime($estado['inicio']) : null;
    $fimTs = $temFim ? strtotime($estado['fim']) : null;
    if ($iniTs && $fimTs && $fimTs > $iniTs) $periodoTotal = $fimTs - $iniTs;
}
$pct = $temFim || $estadoStr === 'carencia'
     ? max(0, min(100, round($segRest / max(1, $periodoTotal) * 100)))
     : 100;

$linkWhats = AssinaturaHelper::linkWhatsapp(
    AssinaturaHelper::mensagemNegociacao($empresa['nome'] ?? '', $estadoStr === 'gratuita' ? 'cessou' : 'expirou')
);

// Poupança do anual face a 12 meses mensais (a partir dos preços reais na BD)
$precoMensal = 0.0; $precoAnual = 0.0;
foreach ($planos as $pp) {
    if ($pp['codigo'] === 'mensal') $precoMensal = (float) $pp['preco'];
    if ($pp['codigo'] === 'anual')  $precoAnual  = (float) $pp['preco'];
}
$poupanca = max(0, $precoMensal * 12 - $precoAnual);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-id-card"></i> Minha Assinatura</h1>
        <p class="page-subtitle">Estado do plano da empresa <?= htmlspecialchars($empresa['nome']) ?></p>
    </div>
</div>

<!-- Cartão principal -->
<section class="assin-hero">
    <div style="flex:1;min-width:220px">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:8px">
            <strong style="font-family:'Sora',sans-serif;font-size:20px;color:var(--navy-deep)">
                <?= htmlspecialchars(is_array($estado['plano'] ?? null) ? ($estado['plano']['nome'] ?? 'Gratuito') : ($estado['plano'] ?? 'Gratuito')) ?>
            </strong>
            <?= assinSeloEstado($estadoStr) ?>
        </div>
        <div style="font-size:13px;color:var(--muted)">
            <i class="fas fa-calendar-day"></i> Início: <?= assinDataBr($estado['inicio'] ?? null) ?>
            &nbsp;·&nbsp;
            <i class="fas fa-calendar-xmark"></i> Vencimento: <?= $temFim ? assinDataBr($estado['fim']) : 'Sem fim' ?>
        </div>
        <div class="assin-progresso"><span style="width:<?= (int) $pct ?>%;background:<?= $corBarra ?>"></span></div>
    </div>
    <div style="text-align:center">
        <?php if ($estadoStr === 'gratuita' && !$temFim): ?>
            <div class="dias">∞<small>sem limite</small></div>
        <?php elseif ($estadoStr === 'bloqueada'): ?>
            <div class="dias" style="color:var(--red-dark)">0<small>dias restantes</small></div>
        <?php else: ?>
            <div class="dias"><?= $diasRest ?><small>dias restantes</small></div>
        <?php endif; ?>
    </div>
</section>

<?php if ($estadoStr === 'carencia'): ?>
    <!-- Alerta de carência -->
    <section class="assin-alerta-carencia">
        <i class="fas fa-hourglass-half ico"></i>
        <div class="txt">
            A sua assinatura terminou e está em período de tolerância.
            <strong>Bloqueio em <span id="contagemCarencia"><?= assinTempoLegivel($segRest) ?></span></strong>.
        </div>
        <?php if ($linkWhats): ?>
            <a href="<?= htmlspecialchars($linkWhats) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                <i class="fa-brands fa-whatsapp"></i> Negociar no WhatsApp
            </a>
        <?php else: ?>
            <span style="font-size:12px">Contacte o administrador do sistema.</span>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if ($estadoStr === 'bloqueada'): ?>
    <section class="assin-alerta-carencia" style="background:#fee2e2;border-color:#fecaca;border-left-color:var(--red);color:#b91c1c">
        <i class="fas fa-lock ico"></i>
        <div class="txt">O acesso da sua empresa está suspenso por falta de regularização. Contacte o administrador principal para negociar a renovação.</div>
        <?php if ($linkWhats): ?>
            <a href="<?= htmlspecialchars($linkWhats) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                <i class="fa-brands fa-whatsapp"></i> Falar no WhatsApp
            </a>
        <?php endif; ?>
    </section>
<?php endif; ?>

<!-- Planos disponíveis -->
<div class="form-card" style="margin-bottom:20px">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-crown"></i></div>
        <div><h3>Planos disponíveis</h3><p>Para subscrever ou renovar, fale connosco pelo WhatsApp</p></div>
    </div>
    <div class="form-card-body">
        <div class="assin-mini-planos">
            <?php foreach ($planos as $p): ?>
                <?php
                if ($p['codigo'] === 'gratuito') continue; // só os 4 pagos
                $msg = 'Olá! Sou da empresa ' . ($empresa['nome'] ?? '') . '. Gostaria de subscrever o plano ' . $p['nome'] . ' do Monana Financial.';
                $lw  = AssinaturaHelper::linkWhatsapp($msg);
                ?>
                <article class="assin-mini-plano">
                    <?php if ($p['codigo'] === 'anual'): ?><span class="assin-fita">Melhor preço</span><?php endif; ?>
                    <div class="nome"><?= htmlspecialchars($p['nome']) ?></div>
                    <div class="preco"><?= htmlspecialchars(number_format((float) $p['preco'], 0, ',', '.')) ?> Kz</div>
                    <div class="desc"><?= (int) $p['duracao_dias'] ?> dias de acesso</div>
                    <?php if ($p['codigo'] === 'anual' && $poupanca > 0): ?>
                        <div class="desc" style="color:var(--green-dark);font-weight:700">
                            <i class="fas fa-tag"></i> Poupa <?= htmlspecialchars(number_format($poupanca, 0, ',', '.')) ?> Kz face a 12 meses mensais
                        </div>
                    <?php endif; ?>
                    <?php if ($lw): ?>
                        <a href="<?= htmlspecialchars($lw) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                            <i class="fa-brands fa-whatsapp"></i> Contactar para subscrever
                        </a>
                    <?php else: ?>
                        <span class="desc">Contacte o administrador do sistema.</span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Pagamentos -->
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-receipt"></i></div>
        <div><h3>Pagamentos</h3><p>Histórico dos pagamentos registados pela administração</p></div>
    </div>
    <div class="form-card-body">
        <div class="tabela-scroll">
            <table class="assin-tabela">
                <thead><tr><th>Data</th><th>Valor</th><th>Método</th><th>Referência</th></tr></thead>
                <tbody>
                <?php if (empty($pagamentos)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">Sem pagamentos registados.</td></tr>
                <?php else: foreach ($pagamentos as $pg): ?>
                    <tr>
                        <td><?= assinDataBr($pg['data_pagamento']) ?></td>
                        <td><?= htmlspecialchars(FormatacaoHelper::moeda((float) $pg['valor'])) ?></td>
                        <td><?= htmlspecialchars(ucfirst($pg['metodo'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars($pg['referencia'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($estadoStr === 'carencia' && $segRest > 0): ?>
<script>
(function () {
    var alvo = Date.now() + <?= $segRest ?> * 1000;
    var el = document.getElementById('contagemCarencia');
    function actualizar() {
        var s = Math.max(0, Math.floor((alvo - Date.now()) / 1000));
        var d = Math.floor(s / 86400), h = Math.floor((s % 86400) / 3600), m = Math.floor((s % 3600) / 60);
        el.textContent = (d > 0 ? d + 'd ' : '') + (d > 0 || h > 0 ? h + 'h ' : '') + m + 'min';
    }
    actualizar();
    setInterval(actualizar, 60000);
})();
</script>
<?php endif; ?>
