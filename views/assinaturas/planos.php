<?php
/**
 * Planos de assinatura (Super Admin).
 * Variáveis: $planos, $csrf_token
 */
require_once CAMINHO_RAIZ . '/views/assinaturas/_estilos.php';
?>

<div class="assin-view">
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-tags"></i> Planos de Assinatura</h1>
        <p class="page-subtitle">Preços e durações dos planos vendidos às empresas</p>
    </div>
    <div class="page-header-right">
        <a href="<?= URL_BASE ?>/assinaturas" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<p class="assin-nota"><i class="fas fa-circle-info"></i> Alterar o preço não afecta assinaturas já existentes — cada assinatura guarda o valor acordado no momento da criação.</p>

<section class="assin-planos-grid">
    <?php foreach ($planos as $p): ?>
        <?php $ehGratuito = $p['codigo'] === 'gratuito'; ?>
        <article class="assin-plano-card">
            <?php if ($p['codigo'] === 'anual'): ?><span class="assin-fita">Melhor preço</span><?php endif; ?>
            <div class="assin-plano-cabecalho">
                <div class="quadrado"><i class="fas <?= $ehGratuito ? 'fa-gift' : 'fa-crown' ?>"></i></div>
                <h3><?= htmlspecialchars($p['nome']) ?></h3>
            </div>
            <div class="assin-plano-corpo">
                <div class="assin-preco"><?= $ehGratuito ? 'Grátis' : htmlspecialchars(number_format((float) $p['preco'], 0, ',', '.')) . ' Kz' ?></div>
                <div class="assin-duracao"><?= $ehGratuito ? 'sem fim (ou data definida)' : 'por ' . (int) $p['duracao_dias'] . ' dias' ?></div>
                <?php if (!$p['ativo']): ?><div class="assin-duracao" style="color:var(--red-dark)"><i class="fas fa-eye-slash"></i> Inactivo</div><?php endif; ?>
            </div>
            <form class="assin-rodape-form" method="post" action="<?= URL_BASE ?>/assinaturas/guardarPlano/<?= (int) $p['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <?php if ($ehGratuito): ?>
                    <input type="hidden" name="preco" value="0">
                    <input type="hidden" name="duracao_dias" value="">
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);flex:1">
                        <input type="checkbox" name="ativo" value="1" <?= $p['ativo'] ? 'checked' : '' ?> style="flex:0 0 auto;width:auto;min-width:0"> Activo
                    </label>
                <?php else: ?>
                    <input type="number" name="preco" min="0" step="0.01" value="<?= htmlspecialchars((string) $p['preco']) ?>" title="Preço (Kz)" required>
                    <input type="number" name="duracao_dias" min="1" max="3650" value="<?= (int) $p['duracao_dias'] ?>" title="Duração (dias)" required>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)">
                        <input type="checkbox" name="ativo" value="1" <?= $p['ativo'] ? 'checked' : '' ?> style="width:auto;min-width:0">
                    </label>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </article>
    <?php endforeach; ?>
</section>
    </div><!-- /assin-view -->
