<?php
/**
 * Categorias — listagem hierárquica (visual melhorado)
 */
$perfil         = $perfil ?? 'visualizador';
$empresaId      = $empresaId ?? null;
$empresaNome    = $empresaNome ?? '';
$empresas       = $empresas ?? [];
$arvoreEntradas = $arvoreEntradas ?? [];
$arvoreSaidas   = $arvoreSaidas ?? [];

function contagemNos(array $nos): int {
    $n = count($nos);
    foreach ($nos as $no) {
        $n += contagemNos($no['filhos'] ?? []);
    }
    return $n;
}

function renderNoCategoria(array $cat, string $perfil, int $nivel = 0): string
{
    $ativa     = !empty($cat['ativa']);
    $sistema   = !empty($cat['sistema']);
    $filhos    = $cat['filhos'] ?? [];
    $temFilhos = !empty($filhos);
    $pad       = 12 + ($nivel * 20);

    $html  = '<div class="cn' . ($ativa ? '' : ' cn-off') . ($temFilhos ? '' : ' cn-leaf') . '">';
    $html .= '<div class="cn-row" style="padding-left:' . $pad . 'px">';

    if ($temFilhos) {
        $html .= '<button type="button" class="cn-tw" onclick="this.closest(\'.cn\').classList.toggle(\'is-closed\')"><i class="fas fa-chevron-down"></i></button>';
    } else {
        $html .= '<span class="cn-tw-sp"></span>';
    }

    $html .= '<div class="cn-body">';
    $html .= '<div class="cn-title">';
    $html .= '<span class="cn-name">' . htmlspecialchars($cat['nome']) . '</span>';
    if ($sistema) $html .= '<span class="cn-badge cn-sys">padrão</span>';
    if (!$ativa)  $html .= '<span class="cn-badge cn-offb">inactiva</span>';
    $html .= '</div>';
    if (!empty($cat['descricao'])) {
        $html .= '<div class="cn-desc">' . htmlspecialchars($cat['descricao']) . '</div>';
    }
    $html .= '</div>';

    $html .= '<div class="cn-acts">';
    $html .= '<a class="cn-btn" href="' . URL_BASE . '/categorias/editar/' . (int)$cat['id'] . '" title="Editar"><i class="fas fa-pen"></i></a>';
    $html .= '<a class="cn-btn" href="' . URL_BASE . '/categorias/alternarEstado/' . (int)$cat['id'] . '" title="' . ($ativa ? 'Desactivar' : 'Activar') . '"><i class="fas fa-' . ($ativa ? 'eye-slash' : 'eye') . '"></i></a>';
    if ($perfil === 'super_admin') {
        $html .= '<a class="cn-btn cn-btn-danger" href="' . URL_BASE . '/categorias/eliminar/' . (int)$cat['id'] . '" title="Eliminar" onclick="return confirm(\'Eliminar esta categoria?\');"><i class="fas fa-trash"></i></a>';
    }
    $html .= '</div>';

    $html .= '</div>';

    if ($temFilhos) {
        $html .= '<div class="cn-kids">';
        foreach ($filhos as $f) {
            $html .= renderNoCategoria($f, $perfil, $nivel + 1);
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}

$totalIn  = contagemNos($arvoreEntradas);
$totalOut = contagemNos($arvoreSaidas);
?>

<div class="cpage">
    <div class="cpage-head">
        <div>
            <h1><i class="fas fa-layer-group"></i> Categorias</h1>
            <p>
                Organize receitas e despesas da empresa
                <?php if ($empresaNome): ?>
                    <span class="cpage-emp"><i class="fas fa-building"></i> <?= htmlspecialchars($empresaNome) ?></span>
                <?php endif; ?>
            </p>
        </div>
        <div class="cpage-actions">
            <?php if ($perfil === 'super_admin' && !empty($empresas)): ?>
            <select class="cpage-select" onchange="location.href='<?= URL_BASE ?>/categorias?empresa_id='+this.value">
                <option value="">— Empresa —</option>
                <?php foreach ($empresas as $emp): ?>
                <option value="<?= (int)$emp['id'] ?>" <?= (int)($empresaId ?? 0) === (int)$emp['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($emp['nome']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <?php if ($empresaId): ?>
            <a class="cpage-btn" href="<?= URL_BASE ?>/categorias/criar?empresa_id=<?= (int)$empresaId ?>">
                <i class="fas fa-plus"></i> Nova
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$empresaId): ?>
        <div class="cpage-empty">
            <i class="fas fa-building"></i>
            <h3>Seleccione uma empresa</h3>
            <p>Escolha a empresa para ver as categorias padrão e personalizadas.</p>
        </div>
    <?php elseif ($totalIn === 0 && $totalOut === 0): ?>
        <div class="cpage-empty">
            <i class="fas fa-folder-open"></i>
            <h3>Sem categorias</h3>
            <p>Ainda não existem categorias para esta empresa.</p>
            <a class="cpage-btn" href="<?= URL_BASE ?>/categorias/criar?empresa_id=<?= (int)$empresaId ?>">Criar categoria</a>
        </div>
    <?php else: ?>

    <div class="cpage-stats">
        <div class="cstat cstat-in">
            <span class="cstat-n"><?= $totalIn ?></span>
            <span class="cstat-l">Receitas</span>
        </div>
        <div class="cstat cstat-out">
            <span class="cstat-n"><?= $totalOut ?></span>
            <span class="cstat-l">Despesas</span>
        </div>
    </div>

    <div class="cpage-grid">
        <section class="cpanel">
            <header class="cpanel-h cpanel-in">
                <div>
                    <strong><i class="fas fa-arrow-trend-up"></i> Receitas</strong>
                    <small><?= count($arvoreEntradas) ?> grupo(s) · <?= $totalIn ?> no total</small>
                </div>
            </header>
            <div class="cpanel-b">
                <?php if (!$arvoreEntradas): ?>
                    <p class="cpanel-empty">Nenhuma receita.</p>
                <?php else: foreach ($arvoreEntradas as $c) { echo renderNoCategoria($c, $perfil); } endif; ?>
            </div>
        </section>

        <section class="cpanel">
            <header class="cpanel-h cpanel-out">
                <div>
                    <strong><i class="fas fa-arrow-trend-down"></i> Despesas</strong>
                    <small><?= count($arvoreSaidas) ?> grupo(s) · <?= $totalOut ?> no total</small>
                </div>
            </header>
            <div class="cpanel-b">
                <?php if (!$arvoreSaidas): ?>
                    <p class="cpanel-empty">Nenhuma despesa.</p>
                <?php else: foreach ($arvoreSaidas as $c) { echo renderNoCategoria($c, $perfil); } endif; ?>
            </div>
        </section>
    </div>

    <p class="cpage-note">
        <i class="fas fa-info-circle"></i>
        Os itens com indentação são exemplos/subtipos do grupo acima. Pode usar o grupo ou o subtipo nos lançamentos, conforme a necessidade.
    </p>

    <?php endif; ?>
</div>

<style>
.cpage { max-width: 1100px; margin: 0 auto; }
.cpage-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    gap: 16px; flex-wrap: wrap; margin-bottom: 18px;
}
.cpage-head h1 {
    margin: 0; font-size: 22px; font-weight: 750; color: #0f172a;
    display: flex; align-items: center; gap: 10px;
}
.cpage-head p { margin: 6px 0 0; color: #64748b; font-size: 13.5px; }
.cpage-emp {
    display: inline-flex; align-items: center; gap: 6px;
    margin-left: 8px; padding: 2px 10px; border-radius: 999px;
    background: #f1f5f9; color: #334155; font-weight: 600; font-size: 12px;
}
.cpage-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.cpage-select {
    min-width: 200px; padding: 10px 12px; border-radius: 11px;
    border: 1.5px solid #e2e8f0; background: #fff; font-size: 13px;
}
.cpage-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 11px; text-decoration: none;
    background: linear-gradient(135deg, #0e2748, #1e3a5f); color: #fff;
    font-weight: 650; font-size: 13.5px; border: none; cursor: pointer;
}
.cpage-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px -10px rgba(14,39,72,.5); }

.cpage-stats { display: flex; gap: 12px; margin-bottom: 16px; }
.cstat {
    flex: 1; background: #fff; border: 1.5px solid #e2e8f0; border-radius: 14px;
    padding: 14px 16px; display: flex; flex-direction: column; gap: 2px;
}
.cstat-n { font-size: 22px; font-weight: 800; color: #0f172a; }
.cstat-l { font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.cstat-in { border-top: 3px solid #16a34a; }
.cstat-out { border-top: 3px solid #dc2626; }

.cpage-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
}
@media (max-width: 900px) { .cpage-grid { grid-template-columns: 1fr; } }

.cpanel {
    background: #fff; border: 1.5px solid #e2e8f0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 10px 30px -20px rgba(15,23,42,.18);
}
.cpanel-h {
    padding: 14px 16px; border-bottom: 1.5px solid #eef2f7; background: #fafbfc;
}
.cpanel-h strong {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; color: #0f172a;
}
.cpanel-h small { display: block; margin-top: 3px; color: #94a3b8; font-size: 12px; }
.cpanel-in strong { color: #15803d; }
.cpanel-out strong { color: #b91c1c; }
.cpanel-b { max-height: 62vh; overflow: auto; padding: 6px 0 10px; }
.cpanel-empty { margin: 0; padding: 24px; text-align: center; color: #94a3b8; font-size: 13px; }

.cn-row {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 12px 10px 0; min-height: 44px;
}
.cn-row:hover { background: #f8fafc; }
.cn-row:hover .cn-acts { opacity: 1; }
.cn-tw {
    width: 24px; height: 24px; border: 0; background: transparent; color: #94a3b8;
    border-radius: 7px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
}
.cn-tw:hover { background: #e2e8f0; color: #0f172a; }
.cn.is-closed > .cn-row .cn-tw { transform: rotate(-90deg); }
.cn.is-closed > .cn-kids { display: none; }
.cn-tw-sp { width: 24px; flex-shrink: 0; }
.cn-body { flex: 1; min-width: 0; }
.cn-title { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cn-name { font-size: 13.5px; font-weight: 650; color: #0f172a; }
.cn-desc { margin-top: 2px; font-size: 12px; color: #94a3b8; }
.cn-badge {
    font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 999px;
    text-transform: uppercase; letter-spacing: .03em;
}
.cn-sys { background: #eef2ff; color: #4338ca; }
.cn-offb { background: #fee2e2; color: #b91c1c; }
.cn-off { opacity: .55; }
.cn-acts { display: flex; gap: 4px; opacity: 0; transition: opacity .12s; }
.cn-btn {
    width: 30px; height: 30px; border-radius: 8px; display: inline-flex;
    align-items: center; justify-content: center; background: #f1f5f9; color: #64748b;
    text-decoration: none; font-size: 12px;
}
.cn-btn:hover { background: #e2e8f0; color: #0f172a; }
.cn-btn-danger:hover { background: #fee2e2; color: #dc2626; }

.cpage-empty {
    text-align: center; padding: 48px 20px; background: #fff;
    border: 1.5px dashed #e2e8f0; border-radius: 16px; color: #64748b;
}
.cpage-empty i { font-size: 28px; color: #cbd5e1; margin-bottom: 10px; }
.cpage-empty h3 { margin: 0 0 6px; color: #0f172a; }
.cpage-empty p { margin: 0 0 14px; }

.cpage-note {
    margin: 14px 2px 0; font-size: 12.5px; color: #64748b;
    display: flex; gap: 8px; align-items: flex-start;
}
.cpage-note i { color: #94a3b8; margin-top: 2px; }
</style>
