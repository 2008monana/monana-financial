<?php
/**
 * Formulário de edição de transação
 */
$erros = $_SESSION['erros'] ?? [];
unset($_SESSION['erros']);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-edit"></i> Editar Lançamento</h1>
        <p class="page-subtitle">Altere os dados da transação #<?php echo $transacao['id']; ?></p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/transacoes" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="<?php echo URL_BASE; ?>/transacoes/atualizar/<?php echo $transacao['id']; ?>" class="form-transacao">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="data_transacao">
                    <i class="fas fa-calendar-alt"></i> Data <span class="required">*</span>
                </label>
                <input type="date" id="data_transacao" name="data_transacao" 
                       value="<?php echo $transacao['data_transacao']; ?>" required>
                <?php if (!empty($erros['data_transacao'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['data_transacao']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="tipo">
                    <i class="fas fa-tag"></i> Tipo <span class="required">*</span>
                </label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($tipos as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo $transacao['tipo'] === $value ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['tipo'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['tipo']; ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="filial_id">
                    <i class="fas fa-store"></i> Filial <span class="required">*</span>
                </label>
                <select id="filial_id" name="filial_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($filiais as $filial): ?>
                    <option value="<?php echo $filial['id']; ?>" <?php echo $transacao['filial_id'] == $filial['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($filial['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['filial_id'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['filial_id']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="categoria_grupo">
                    <i class="fas fa-folder"></i> Grupo / Tipo de Categoria
                </label>
                <div class="search-select" data-search-select>
                    <input type="text" id="categoria_grupo" class="search-select-input"
                           placeholder="Escreva para procurar o grupo..." autocomplete="off">
                    <input type="hidden" id="categoria_grupo_id" value="">
                    <div class="search-select-list" id="lista_grupos"></div>
                </div>
                <small class="field-hint">Primeiro escolha o grupo (ex: Gastos com Pessoal)</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="categoria_nome">
                    <i class="fas fa-tags"></i> Categoria <span class="required">*</span>
                </label>
                <div class="search-select" data-search-select>
                    <input type="text" id="categoria_nome" class="search-select-input"
                           placeholder="Escreva para procurar a categoria..." autocomplete="off" required>
                    <input type="hidden" id="categoria_id" name="categoria_id"
                           value="<?php echo (int)($transacao['categoria_id'] ?? 0); ?>" required>
                    <div class="search-select-list" id="lista_categorias"></div>
                </div>
                <?php if (!empty($erros['categoria_id'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['categoria_id']; ?></span>
                <?php endif; ?>
                <small class="field-hint">Depois escolha a categoria específica</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="valor">
                    <i class="fas fa-money-bill-wave"></i> Valor (Kz) <span class="required">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-prepend">Kz</span>
                    <input type="text" id="valor" name="valor" 
                           value="<?php echo number_format($transacao['valor'], 2, ',', '.'); ?>" 
                           placeholder="0,00" required>
                </div>
                <?php if (!empty($erros['valor'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['valor']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group full-width">
                <label for="descricao">
                    <i class="fas fa-align-left"></i> Descrição
                </label>
                <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição do lançamento..."><?php echo htmlspecialchars($transacao['descricao'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="metodo_pagamento">
                    <i class="fas fa-credit-card"></i> Método de Pagamento <span class="optional-tag">opcional</span>
                </label>
                <select id="metodo_pagamento" name="metodo_pagamento">
                    <option value="">— Não indicar —</option>
                    <?php foreach ($metodos_pagamento as $metodo): ?>
                    <option value="<?php echo htmlspecialchars($metodo['nome']); ?>" 
                            <?php echo ($transacao['metodo_pagamento'] ?? '') === $metodo['nome'] ? 'selected' : ''; ?>>
                        <?php if (!empty($metodo['icone'])): ?>
                            <i class="<?php echo htmlspecialchars($metodo['icone']); ?>"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($metodo['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['metodo_pagamento'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['metodo_pagamento']; ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Atualizar Lançamento
            </button>
            <a href="<?php echo URL_BASE; ?>/transacoes" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
document.getElementById('valor')?.addEventListener('input', function(e) {
    let value = this.value.replace(/[^0-9,]/g, '');
    if (value) {
        let parts = value.split(',');
        let integer = parts[0];
        let decimal = parts[1] || '';
        integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        if (decimal) {
            this.value = integer + ',' + decimal.substring(0, 2);
        } else {
            this.value = integer;
        }
    }
});
</script>

<!-- O CSS é o mesmo do criar.php - pode ser reutilizado -->
<style>
/* ============================================
   ESTILOS COMPLETOS - FORMULÁRIO DE TRANSAÇÕES
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.page-title {
    font-family: 'Sora', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--navy-deep);
    margin: 0;
}

.page-title i {
    color: var(--green);
    margin-right: 12px;
    font-size: 24px;
}

.page-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 0;
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ============================================
   CARD DO FORMULÁRIO
   ============================================ */

.form-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 32px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
}

.form-card:hover {
    box-shadow: var(--shadow-md);
}

/* ============================================
   LINHAS DO FORMULÁRIO
   ============================================ */

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 4px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

/* ============================================
   LABELS
   ============================================ */

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
}

.form-group label i {
    color: var(--muted);
    width: 18px;
    margin-right: 4px;
    font-size: 14px;
}

.form-group .required {
    color: var(--red);
    font-weight: 700;
    margin-left: 2px;
}

/* ============================================
   INPUTS, SELECTS E TEXTAREA
   ============================================ */

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: var(--ink);
    background: var(--white);
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
    border-color: var(--muted);
}

.form-group textarea {
    resize: vertical;
    min-height: 90px;
    line-height: 1.6;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a3adba;
}

/* ============================================
   INPUT GROUP (Kz)
   ============================================ */

.input-group {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    background: var(--white);
    transition: all 0.3s ease;
    overflow: hidden;
}

.input-group:focus-within {
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

.input-group-prepend {
    padding: 11px 14px;
    background: var(--bg);
    color: var(--muted);
    font-weight: 700;
    font-size: 14px;
    border-right: 1px solid var(--border);
    flex-shrink: 0;
}

.input-group input {
    border: none !important;
    border-radius: 0 !important;
    padding: 11px 14px;
    flex: 1;
    min-width: 0;
}

.input-group input:focus {
    box-shadow: none !important;
}

/* ============================================
   ERROS
   ============================================ */

.error-text {
    display: block;
    font-size: 12px;
    color: var(--red);
    margin-top: 5px;
    font-weight: 500;
}

.error-text i {
    margin-right: 4px;
}

/* ============================================
   BOTÕES
   ============================================ */

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 8px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i {
    font-size: 15px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(14, 39, 72, 0.25);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14, 39, 72, 0.35);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-outline {
    background: transparent;
    color: var(--muted);
    border: 1.5px solid var(--border);
}

.btn-outline:hover {
    background: var(--bg);
    color: var(--ink);
    border-color: var(--muted);
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .form-card {
        padding: 20px;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-title {
        font-size: 22px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        justify-content: center;
        width: 100%;
    }
    
    .input-group-prepend {
        padding: 9px 12px;
        font-size: 13px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 9px 12px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .form-card {
        padding: 16px;
    }
    
    .page-title {
        font-size: 18px;
    }
    
    .page-title i {
        font-size: 18px;
        margin-right: 8px;
    }
    
    .page-subtitle {
        font-size: 12px;
    }
    
    .form-group label {
        font-size: 12px;
    }
    
    .btn {
        padding: 9px 16px;
        font-size: 13px;
    }
}
</style>


<script>
(function(){
  const arvore = <?= json_encode($arvoreCategorias ?? [], JSON_UNESCAPED_UNICODE) ?>;
  const grupos = [];
  const filhosPorPai = {};
  const folhasSemPai = [];
  arvore.forEach(function(n){
    const filhos = n.filhos || [];
    if (filhos.length) {
      grupos.push({id: n.id, nome: n.nome, tipo: n.tipo});
      filhosPorPai[n.id] = filhos.map(f => ({id: f.id, nome: f.nome, tipo: f.tipo, pai: n.nome}));
    } else {
      folhasSemPai.push({id: n.id, nome: n.nome, tipo: n.tipo, pai: null});
    }
  });
  const inputGrupo = document.getElementById('categoria_grupo');
  const hiddenGrupo = document.getElementById('categoria_grupo_id');
  const listGrupos = document.getElementById('lista_grupos');
  const inputCat = document.getElementById('categoria_nome');
  const hiddenCat = document.getElementById('categoria_id');
  const listCats = document.getElementById('lista_categorias');
  function filtrar(lista, termo){
    termo = (termo || '').toLowerCase().trim();
    if (!termo) return lista;
    return lista.filter(i => (i.nome || '').toLowerCase().includes(termo) || (i.pai || '').toLowerCase().includes(termo));
  }
  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }
  function renderLista(el, items, onPick){
    if (!items.length) {
      el.innerHTML = '<div class="ssl-empty">Nenhum resultado</div>';
      el.classList.add('open');
      return;
    }
    el.innerHTML = items.map(i => {
      const sub = i.pai ? `<span class="ssl-sub">${escapeHtml(i.pai)}</span>` : (i.tipo ? `<span class="ssl-sub">${i.tipo}</span>` : '');
      return `<button type="button" class="ssl-item" data-id="${i.id}" data-nome="${escapeHtml(i.nome)}"><span>${escapeHtml(i.nome)}</span>${sub}</button>`;
    }).join('');
    el.classList.add('open');
    el.querySelectorAll('.ssl-item').forEach(btn => {
      btn.addEventListener('mousedown', function(e){
        e.preventDefault();
        onPick({id: this.dataset.id, nome: this.dataset.nome});
        el.classList.remove('open');
      });
    });
  }
  function opcoesCategoria(){
    const gid = hiddenGrupo.value;
    if (gid && filhosPorPai[gid]) return filhosPorPai[gid];
    let all = [].concat(folhasSemPai);
    Object.keys(filhosPorPai).forEach(k => { all = all.concat(filhosPorPai[k]); });
    return all;
  }
  inputGrupo.addEventListener('focus', () => renderLista(listGrupos, filtrar(grupos, inputGrupo.value), pickGrupo));
  inputGrupo.addEventListener('input', () => { hiddenGrupo.value=''; renderLista(listGrupos, filtrar(grupos, inputGrupo.value), pickGrupo); });
  inputGrupo.addEventListener('blur', () => setTimeout(() => listGrupos.classList.remove('open'), 150));
  function pickGrupo(item){
    inputGrupo.value = item.nome;
    hiddenGrupo.value = item.id;
    inputCat.value = '';
    hiddenCat.value = '';
    inputCat.focus();
  }
  inputCat.addEventListener('focus', () => renderLista(listCats, filtrar(opcoesCategoria(), inputCat.value), pickCat));
  inputCat.addEventListener('input', () => { hiddenCat.value=''; renderLista(listCats, filtrar(opcoesCategoria(), inputCat.value), pickCat); });
  inputCat.addEventListener('blur', () => setTimeout(() => listCats.classList.remove('open'), 150));
  function pickCat(item){
    inputCat.value = item.nome;
    hiddenCat.value = item.id;
  }
  const preId = hiddenCat.value;
  if (preId) {
    let found = null, parentName = '';
    Object.keys(filhosPorPai).forEach(k => {
      filhosPorPai[k].forEach(f => { if (String(f.id) === String(preId)) { found = f; parentName = f.pai; } });
    });
    folhasSemPai.forEach(f => { if (String(f.id) === String(preId)) found = f; });
    if (found) {
      inputCat.value = found.nome;
      if (parentName) {
        inputGrupo.value = parentName;
        const g = grupos.find(x => x.nome === parentName);
        if (g) hiddenGrupo.value = g.id;
      }
    }
  }
})();
</script>
<style>
.search-select { position: relative; }
.search-select-input {
  width: 100%; padding: 11px 13px; border: 1.5px solid var(--border, #e2e8f0);
  border-radius: 10px; font-size: 14px; font-family: inherit; box-sizing: border-box; background: #fff;
}
.search-select-input:focus { outline: none; border-color: #0e2748; box-shadow: 0 0 0 3px rgba(14,39,72,.1); }
.search-select-list {
  display: none; position: absolute; z-index: 40; left: 0; right: 0; top: calc(100% + 4px);
  background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; max-height: 220px; overflow-y: auto;
  box-shadow: 0 12px 30px -12px rgba(15,23,42,.25);
}
.search-select-list.open { display: block; }
.ssl-item {
  width: 100%; text-align: left; border: none; background: transparent; padding: 10px 12px; cursor: pointer;
  display: flex; flex-direction: column; gap: 2px; font-size: 13.5px; color: #0f172a;
}
.ssl-item:hover { background: #f1f5f9; }
.ssl-sub { font-size: 11px; color: #94a3b8; }
.ssl-empty { padding: 12px; color: #94a3b8; font-size: 13px; }
.field-hint { display:block; margin-top:5px; font-size:12px; color:#94a3b8; }
</style>

<style>
.optional-tag {
  display: inline-block;
  margin-left: 6px;
  padding: 1px 7px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .03em;
  text-transform: uppercase;
  background: #f1f5f9;
  color: #64748b;
  vertical-align: middle;
}
</style>
