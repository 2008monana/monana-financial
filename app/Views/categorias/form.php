<?php
/**
 * Formulário de Categoria (Criar / Editar) — centralizado e simplificado
 */
$ehEdicao    = !empty($categoria['id']);
$titulo      = $ehEdicao ? 'Editar Categoria' : 'Nova Categoria';
$subtitulo   = $ehEdicao ? 'Actualize os dados da categoria' : 'Registe uma nova categoria';
$paisEntrada = $paisEntrada ?? [];
$paisSaida   = $paisSaida ?? [];
$perfil      = $_SESSION['usuario_perfil'] ?? 'visualizador';
?>

<div class="cat-form-page">
    <div class="cat-form-wrap">
        <div class="cat-form-header">
            <div class="cat-form-header-icon">
                <i class="fas <?= $ehEdicao ? 'fa-pen' : 'fa-plus' ?>"></i>
            </div>
            <div>
                <h1><?= $titulo ?></h1>
                <p><?= $subtitulo ?></p>
            </div>
            <a href="<?= URL_BASE ?>/categorias<?= $empresaId ? '?empresa_id=' . (int)$empresaId : '' ?>" class="cat-form-back">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <form method="post"
              action="<?= URL_BASE ?>/categorias/<?= $ehEdicao ? 'atualizar/' . (int)$categoria['id'] : 'armazenar' ?>"
              class="cat-form">

            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="empresa_id" value="<?= (int)$empresaId ?>">

            <div class="cat-form-body">
                <div class="form-group">
                    <label for="nome">Nome <span class="required">*</span></label>
                    <input type="text" id="nome" name="nome"
                           value="<?= htmlspecialchars($categoria['nome'] ?? '') ?>"
                           placeholder="Ex: Combustível e Lubrificantes" required autofocus>
                    <?php if (!empty($erros['nome'])): ?>
                        <span class="error-text"><?= htmlspecialchars($erros['nome']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <input type="text" id="descricao" name="descricao"
                           value="<?= htmlspecialchars($categoria['descricao'] ?? '') ?>"
                           placeholder="Opcional">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo <span class="required">*</span></label>
                        <select id="tipo" name="tipo" required onchange="actualizarPais()">
                            <option value="entrada" <?= ($categoria['tipo'] ?? '') === 'entrada' ? 'selected' : '' ?>>Entrada (Receita)</option>
                            <option value="saida"   <?= ($categoria['tipo'] ?? 'saida') === 'saida' ? 'selected' : '' ?>>Saída (Despesa/Custo)</option>
                        </select>
                        <?php if (!empty($erros['tipo'])): ?>
                            <span class="error-text"><?= htmlspecialchars($erros['tipo']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="pai_id">Categoria Pai (Grupo)</label>
                        <select id="pai_id" name="pai_id">
                            <option value="">— Nenhuma (categoria de topo) —</option>
                        </select>
                        <?php if (!empty($erros['pai_id'])): ?>
                            <span class="error-text"><?= htmlspecialchars($erros['pai_id']) ?></span>
                        <?php endif; ?>
                        <small>Deixe vazio para criar um grupo principal.</small>
                    </div>
                </div>

                <div class="form-group check-row">
                    <label class="check-label">
                        <input type="checkbox" name="ativa" value="1"
                               <?= !isset($categoria['ativa']) || !empty($categoria['ativa']) ? 'checked' : '' ?>>
                        Categoria activa
                    </label>
                </div>

                <?php if ($perfil === 'super_admin'): ?>
                <div class="form-group check-row">
                    <label class="check-label">
                        <input type="checkbox" name="sistema" value="1"
                               <?= !empty($categoria['sistema']) ? 'checked' : '' ?>>
                        Marcar como categoria do sistema (template)
                    </label>
                    <small>Usada como modelo ao criar novas empresas.</small>
                </div>
                <?php endif; ?>
            </div>

            <div class="cat-form-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $ehEdicao ? 'Guardar Alterações' : 'Criar Categoria' ?>
                </button>
                <a href="<?= URL_BASE ?>/categorias<?= $empresaId ? '?empresa_id=' . (int)$empresaId : '' ?>" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const paisPorTipo = {
    entrada: <?= json_encode(array_values($paisEntrada), JSON_UNESCAPED_UNICODE) ?>,
    saida:   <?= json_encode(array_values($paisSaida), JSON_UNESCAPED_UNICODE) ?>
};
const paiAtual = <?= json_encode($categoria['pai_id'] ?? null) ?>;

function actualizarPais() {
    const tipo = document.getElementById('tipo').value;
    const select = document.getElementById('pai_id');
    const lista = paisPorTipo[tipo] || [];
    select.innerHTML = '<option value="">— Nenhuma (categoria de topo) —</option>';
    lista.forEach(function(p) {
        const opt = document.createElement('option');
        opt.value = p.id;
        // Mostra todas; se tiver pai_id, marca como subcategoria na lista
        const prefixo = p.pai_id ? '↳ ' : '';
        opt.textContent = prefixo + p.nome;
        if (paiAtual && parseInt(paiAtual) === parseInt(p.id)) opt.selected = true;
        select.appendChild(opt);
    });
}
document.addEventListener('DOMContentLoaded', actualizarPais);
</script>

<style>
.cat-form-page {
    display: flex;
    justify-content: center;
    padding: 12px 16px 40px;
}
.cat-form-wrap {
    width: 100%;
    max-width: 560px;
}
.cat-form-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.cat-form-header-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0e2748, #1e3a5f);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.cat-form-header h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--ink, #0f172a);
}
.cat-form-header p {
    margin: 2px 0 0;
    font-size: 13px;
    color: #94a3b8;
}
.cat-form-back {
    margin-left: auto;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1.5px solid var(--border, #e2e8f0);
    background: #fff;
}
.cat-form-back:hover { background: #f8fafc; color: #0f172a; }

.cat-form {
    background: #fff;
    border: 1.5px solid var(--border, #e2e8f0);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px -12px rgba(15, 23, 42, 0.12);
}
.cat-form-body { padding: 24px; }
.cat-form-footer {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1.5px solid var(--border, #e2e8f0);
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 6px;
}
.form-group .required { color: #dc2626; }
.form-group input[type="text"],
.form-group select {
    width: 100%;
    padding: 11px 13px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
}
.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #0e2748;
    box-shadow: 0 0 0 3px rgba(14, 39, 72, 0.1);
}
.form-group small {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #94a3b8;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
@media (max-width: 560px) {
    .form-row { grid-template-columns: 1fr; }
    .cat-form-header { flex-direction: column; align-items: flex-start; }
    .cat-form-back { margin-left: 0; }
}
.error-text {
    display: block;
    margin-top: 5px;
    font-size: 12.5px;
    color: #dc2626;
}
.check-row { margin-bottom: 12px; }
.check-label {
    display: flex !important;
    align-items: center;
    gap: 8px;
    font-weight: 500 !important;
    cursor: pointer;
}
.check-label input { width: 17px; height: 17px; }

.btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all .15s;
}
.btn-primary {
    background: linear-gradient(135deg, #0e2748, #1e3a5f);
    color: #fff;
}
.btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(14,39,72,.25); }
.btn-secondary {
    background: #fff;
    color: #0f172a;
    border: 1.5px solid #e2e8f0;
}
.btn-secondary:hover { background: #f1f5f9; }
</style>
