<?php
/**
 * Ficha de assinatura de uma empresa (Super Admin).
 * Variáveis: $empresa, $estado, $historico, $pagamentos, $planos, $carencia_horas, $csrf_token
 */
require_once CAMINHO_RAIZ . '/views/assinaturas/_estilos.php';

$estadoStr    = $estado['estado'] ?? 'gratuita';
$iniciais     = strtoupper(mb_substr(preg_replace('/\s+/', '', $empresa['nome'] ?? ''), 0, 2));
$whatsEmpresa = AssinaturaHelper::linkWhatsapp(
    'Olá! Sou do Monana Financial. Contacto a respeito da assinatura da empresa ' . ($empresa['nome'] ?? '') . '.'
);
?>

    <div class="assin-view">
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-crown"></i> Assinatura — <?= htmlspecialchars($empresa['nome']) ?></h1>
        <p class="page-subtitle">Gestão do plano, pagamentos e bloqueio desta empresa</p>
    </div>
    <div class="page-header-right">
        <a href="<?= URL_BASE ?>/assinaturas" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<!-- Cartão-resumo -->
<section class="assin-resumo">
    <div class="avatar-lg"><?= htmlspecialchars($iniciais) ?></div>
    <div>
        <h2><?= htmlspecialchars($empresa['nome']) ?></h2>
        <div class="meta">
            <span><i class="fas fa-id-card"></i> NIF: <?= htmlspecialchars($empresa['nif'] ?? '—') ?></span>
            <?php if (!empty($empresa['telefone'])): ?>
                <span><i class="fas fa-phone"></i> <?= htmlspecialchars($empresa['telefone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($empresa['email'])): ?>
                <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($empresa['email']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="selo-dir"><?= assinSeloEstado($estadoStr, true) ?></div>
</section>

<section class="assin-mini-grid">
    <div class="assin-mini">
        <div class="valor"><?= htmlspecialchars(is_array($estado['plano'] ?? null) ? ($estado['plano']['nome'] ?? 'Gratuito') : ($estado['plano'] ?? 'Gratuito')) ?></div>
        <div class="legenda">Plano actual</div>
    </div>
    <div class="assin-mini">
        <div class="valor"><?= assinDataBr($estado['inicio'] ?? null) ?></div>
        <div class="legenda">Início</div>
    </div>
    <div class="assin-mini">
        <div class="valor"><?= $estado['fim'] ? assinDataBr($estado['fim']) : 'Sem fim' ?></div>
        <div class="legenda">Vencimento</div>
    </div>
    <div class="assin-mini">
        <div class="valor">
            <?php if ($estadoStr === 'bloqueada'): ?>
                Bloqueada
            <?php elseif ($estadoStr === 'carencia'): ?>
                Bloqueio em <?= assinTempoLegivel((int) ($estado['segundos_restantes'] ?? 0)) ?>
            <?php elseif ($estadoStr === 'gratuita' && empty($estado['fim'])): ?>
                Sem limite
            <?php else: ?>
                Faltam <?= assinTempoLegivel((int) ($estado['segundos_restantes'] ?? 0)) ?>
            <?php endif; ?>
        </div>
        <div class="legenda">Tempo restante</div>
    </div>
</section>

<div class="assin-grelha">
    <!-- Coluna esquerda: trocar plano -->
    <form class="form-modern" method="post" action="<?= URL_BASE ?>/assinaturas/trocarPlano/<?= (int) $empresa['id'] ?>" id="formTrocarPlano">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-right-left"></i></div>
                <div>
                    <h3>Trocar plano</h3>
                    <p>Aplica um novo plano a esta empresa</p>
                </div>
            </div>
            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Plano</label>
                        <select name="plano_id" id="selPlano" required>
                            <?php foreach ($planos as $p): ?>
                                <option value="<?= (int) $p['id'] ?>"
                                        <?= (isset($plano_actual) && $plano_actual !== null && (int) $p['id'] === (int) $plano_actual) ? 'selected' : '' ?>
                                        data-preco="<?= htmlspecialchars((string) $p['preco']) ?>"
                                        data-nome="<?= htmlspecialchars($p['nome']) ?>"
                                        data-duracao="<?= $p['duracao_dias'] !== null ? (int) $p['duracao_dias'] : 0 ?>"
                                        data-gratuito="<?= $p['codigo'] === 'gratuito' ? 1 : 0 ?>">
                                    <?= htmlspecialchars($p['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Valor acordado (Kz)</label>
                        <input type="number" name="valor" id="inpValor" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar-day"></i> Data de início</label>
                        <input type="date" name="inicio" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" id="grpFimGratuito" style="display:none">
                        <label><i class="fas fa-calendar-xmark"></i> Fim do período gratuito (opcional)</label>
                        <input type="date" name="fim_gratuito">
                    </div>
                    <div class="form-group" id="grpPagamento">
                        <label><i class="fas fa-hand-holding-dollar"></i> Pagamento</label>
                        <select name="pagamento" id="selPagamento">
                            <option value="recebido">Recebido — activar já</option>
                            <option value="pendente">Ainda por receber — iniciar carência de <?= (int) $carencia_horas ?> h</option>
                        </select>
                    </div>
                    <div id="grpDetalhesPg" style="display:contents">
                        <div class="form-group">
                            <label><i class="fas fa-wallet"></i> Método</label>
                            <select name="metodo">
                                <option value="transferencia">Transferência</option>
                                <option value="multicaixa">Multicaixa Express</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-receipt"></i> Referência</label>
                            <input type="text" name="referencia" maxlength="100" placeholder="Nº da transacção">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Data do pagamento</label>
                            <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label><i class="fas fa-note-sticky"></i> Observações</label>
                        <textarea name="observacoes" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
                <div style="margin-top:16px">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Aplicar plano</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Coluna direita: acções -->
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-shield-halved"></i></div>
            <div>
                <h3>Acções</h3>
                <p>Bloqueio manual e contacto</p>
            </div>
        </div>
        <div class="form-card-body">
            <div class="assin-cartao-acoes">
                <?php $bloqManual = !empty($estado['linha']['bloqueada_manual']); ?>
                <?php if ($bloqManual): ?>
                    <form method="post" action="<?= URL_BASE ?>/assinaturas/desbloquear/<?= (int) $empresa['id'] ?>"
                          data-confirmar="Esta acção remove o bloqueio manual e devolve o acesso normal a todos os utilizadores desta empresa."
                          data-confirmar-titulo="Remover bloqueio?"
                          data-confirmar-tipo="sucesso"
                          data-confirmar-texto="Sim, desbloquear">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button class="btn btn-success" style="width:100%"><i class="fas fa-lock-open"></i> Remover bloqueio</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= URL_BASE ?>/assinaturas/bloquear/<?= (int) $empresa['id'] ?>"
                          data-confirmar="Ao bloquear agora, <strong>todos os utilizadores desta empresa ficam imediatamente sem acesso</strong> ao sistema. Esta acção pode ser revertida a qualquer momento."
                          data-confirmar-titulo="Bloquear acesso da empresa?"
                          data-confirmar-tipo="perigo"
                          data-confirmar-texto="Sim, bloquear agora">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <button class="btn btn-danger" style="width:100%"><i class="fas fa-lock"></i> Bloquear agora</button>
                    </form>
                <?php endif; ?>

                <?php if ($whatsEmpresa): ?>
                    <a href="<?= htmlspecialchars($whatsEmpresa) ?>" target="_blank" rel="noopener" class="btn btn-success" style="width:100%">
                        <i class="fa-brands fa-whatsapp"></i> Falar no WhatsApp
                    </a>
                <?php else: ?>
                    <p class="assin-nota" style="margin:0"><i class="fas fa-phone-slash"></i> Contacte o administrador do sistema.</p>
                <?php endif; ?>

                <?php if (!empty($estado['linha']) && $estado['linha']['estado'] === 'pendente_pagamento'): ?>
                    <div class="form-card" style="box-shadow:none;border:1px dashed var(--border)">
                        <div class="form-card-body" style="padding:14px">
                            <h4 style="margin:0 0 10px;font-size:14px"><i class="fas fa-coins"></i> Registar pagamento da carência</h4>
                            <form method="post" action="<?= URL_BASE ?>/assinaturas/registarPagamento/<?= (int) $estado['linha']['id'] ?>"
                                  data-confirmar="Confirma o registo deste pagamento? A assinatura passa a <strong>activa</strong> imediatamente."
                                  data-confirmar-titulo="Registar pagamento?"
                                  data-confirmar-tipo="sucesso"
                                  data-confirmar-texto="Sim, registar">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <div class="form-group">
                                    <label>Valor (Kz)</label>
                                    <input type="number" name="valor" min="0" step="0.01" required value="<?= htmlspecialchars((string) ($estado['linha']['valor_acordado'] ?? '')) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Método</label>
                                    <select name="metodo">
                                        <option value="transferencia">Transferência</option>
                                        <option value="multicaixa">Multicaixa Express</option>
                                        <option value="dinheiro">Dinheiro</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Referência</label>
                                    <input type="text" name="referencia" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label>Data</label>
                                    <input type="date" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <button class="btn btn-success" style="width:100%"><i class="fas fa-check"></i> Registar pagamento</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Histórico -->
<div class="form-card" style="margin-bottom:20px">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-clock-rotate-left"></i></div>
        <div><h3>Histórico de assinaturas</h3><p>Nunca é apagado — todas as mudanças ficam registadas</p></div>
    </div>
    <div class="form-card-body">
        <div class="tabela-scroll">
            <table class="assin-tabela">
                <thead><tr><th>Plano</th><th>Período</th><th>Valor</th><th>Estado</th><th>Criado por</th></tr></thead>
                <tbody>
                <?php if (empty($historico)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Sem registos.</td></tr>
                <?php else: foreach ($historico as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars($h['plano_nome'] ?? '—') ?></td>
                        <td><?= assinDataBr($h['inicio']) ?> → <?= $h['fim'] ? assinDataBr($h['fim']) : 'sem fim' ?>
                            <?php if (!empty($h['carencia_ate'])): ?><span class="assin-sub laranja">carência até <?= htmlspecialchars($h['carencia_ate']) ?></span><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(FormatacaoHelper::moeda((float) $h['valor_acordado'])) ?></td>
                        <td><span class="status-badge <?= $h['estado'] === 'activa' ? 'assin-activa' : ($h['estado'] === 'pendente_pagamento' ? 'assin-carencia' : '') ?>">
                            <?= htmlspecialchars($h['estado'] === 'pendente_pagamento' ? 'Pendente pagamento' : ucfirst($h['estado'])) ?></span></td>
                        <td><?= htmlspecialchars($h['criado_por_nome'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagamentos -->
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-receipt"></i></div>
        <div><h3>Pagamentos</h3><p>Registos manuais de pagamentos recebidos</p></div>
    </div>
    <div class="form-card-body">
        <div class="tabela-scroll">
            <table class="assin-tabela">
                <thead><tr><th>Data</th><th>Valor</th><th>Método</th><th>Referência</th><th>Registado por</th></tr></thead>
                <tbody>
                <?php if (empty($pagamentos)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Sem pagamentos registados.</td></tr>
                <?php else: foreach ($pagamentos as $pg): ?>
                    <tr>
                        <td><?= assinDataBr($pg['data_pagamento']) ?></td>
                        <td><?= htmlspecialchars(FormatacaoHelper::moeda((float) $pg['valor'])) ?></td>
                        <td><?= htmlspecialchars(ucfirst($pg['metodo'] ?? '—')) ?></td>
                        <td><?= htmlspecialchars($pg['referencia'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($pg['criado_por_nome'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var sel = document.getElementById('selPlano');
    var inpValor = document.getElementById('inpValor');
    var grpFimGratuito = document.getElementById('grpFimGratuito');
    var grpPagamento = document.getElementById('grpPagamento');
    var grpDetalhesPg = document.getElementById('grpDetalhesPg');
    var selPg = document.getElementById('selPagamento');

    function formatarKz(v) {
        return 'Kz ' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.').replace('.', ',');
    }

    function actualizar() {
        var opt = sel.options[sel.selectedIndex];
        if (!opt) return;
        var gratuito = opt.getAttribute('data-gratuito') === '1';
        var preco = parseFloat(opt.getAttribute('data-preco') || '0');
        inpValor.value = preco.toFixed(2);
        grpFimGratuito.style.display = gratuito ? '' : 'none';
        grpPagamento.style.display = gratuito ? 'none' : '';
        var pendente = !gratuito && selPg.value === 'pendente';
        grpDetalhesPg.querySelectorAll('.form-group').forEach(function (g) {
            g.style.display = pendente ? 'none' : '';
        });
    }
    sel.addEventListener('change', actualizar);
    selPg.addEventListener('change', actualizar);
    actualizar();

    document.getElementById('formTrocarPlano').addEventListener('submit', function (ev) {
        if (this.dataset.aConfirmar === 'ok') return; // já confirmado no modal
        ev.preventDefault();
        var form = this;
        var opt = sel.options[sel.selectedIndex];
        var gratuito = opt.getAttribute('data-gratuito') === '1';
        var pendente = !gratuito && selPg.value === 'pendente';
        var nomePlano = opt.getAttribute('data-nome') || opt.text;
        var duracao = parseInt(opt.getAttribute('data-duracao') || '0', 10);
        var valor = parseFloat(inpValor.value || '0');

        var titulo, msg, tipo, texto;
        if (gratuito) {
            titulo = 'Aplicar plano gratuito';
            msg = 'Vamos aplicar o plano <strong>Gratuito</strong> a esta empresa.' +
                  (duracao > 0 ? ' O período terminará em <strong>' + duracao + ' dias</strong>.' : ' Sem data de término.') +
                  ' Confirma?';
            tipo = 'info';
            texto = 'Sim, aplicar';
        } else if (pendente) {
            titulo = 'Activar com carência';
            msg = 'Vamos aplicar o plano <strong>' + nomePlano + '</strong> (<em>' + formatarKz(valor) + '</em>) ' +
                  '<strong>sem registo de pagamento</strong>: a empresa entra em período de carência e será notificada. Confirma?';
            tipo = 'aviso';
            texto = 'Sim, aplicar';
        } else {
            titulo = 'Activar assinatura';
            msg = 'Vamos aplicar o plano <strong>' + nomePlano + '</strong> (<em>' + formatarKz(valor) + '</em>) com ' +
                  '<strong>pagamento recebido</strong> e activar já a assinatura desta empresa. Confirma?';
            tipo = 'sucesso';
            texto = 'Sim, aplicar plano';
        }

        assinConfirmar({ titulo: titulo, mensagem: msg, tipo: tipo, textoConfirmar: texto })
            .then(function (sim) {
                if (sim) {
                    form.dataset.aConfirmar = 'ok';
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
    });
})();
</script>
    </div><!-- /assin-view -->
