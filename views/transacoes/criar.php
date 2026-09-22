<?php
/**
 * Formulário de criação de transação
 */
$dadosAntigos = $_SESSION['dados_antigos'] ?? [];
$erros = $_SESSION['erros'] ?? [];
unset($_SESSION['dados_antigos'], $_SESSION['erros']);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Novo Lançamento</h1>
        <p class="page-subtitle">Registe uma nova transação financeira</p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/transacoes/index" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="<?php echo URL_BASE; ?>/transacoes/armazenar" class="form-transacao">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="data_transacao">
                    <i class="fas fa-calendar-alt"></i> Data <span class="required">*</span>
                </label>
                <input type="date" id="data_transacao" name="data_transacao" 
                       value="<?php echo $dadosAntigos['data_transacao'] ?? date('Y-m-d'); ?>" required>
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
        <?php foreach ($tipos as $valor => $rotulo): ?>
        <option value="<?php echo $valor; ?>" <?php echo ($dadosAntigos['tipo'] ?? '') === $valor ? 'selected' : ''; ?>><?php echo $rotulo; ?></option>
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
                    <option value="<?php echo $filial['id']; ?>" <?php echo ($dadosAntigos['filial_id'] ?? '') == $filial['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($filial['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['filial_id'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['filial_id']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="categoria_id">
                    <i class="fas fa-tags"></i> Categoria <span class="required">*</span>
                </label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($dadosAntigos['categoria_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['categoria_id'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['categoria_id']; ?></span>
                <?php endif; ?>
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
                           value="<?php echo $dadosAntigos['valor'] ?? ''; ?>" 
                           placeholder="0,00" required>
                </div>
                <?php if (!empty($erros['valor'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['valor']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="metodo_pagamento">
                    <i class="fas fa-credit-card"></i> Método de Pagamento <span class="required">*</span>
                </label>
                <select id="metodo_pagamento" name="metodo_pagamento" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($metodos_pagamento as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo ($dadosAntigos['metodo_pagamento'] ?? '') === $value ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($erros['metodo_pagamento'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['metodo_pagamento']; ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="descricao">
                <i class="fas fa-align-left"></i> Descrição
            </label>
            <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição do lançamento..."><?php echo htmlspecialchars($dadosAntigos['descricao'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar Lançamento
            </button>
            <a href="<?php echo URL_BASE; ?>/transacoes/index" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
// Máscara para valor monetário
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
    max-width: 820px;
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

.form-group input.error,
.form-group select.error {
    border-color: var(--red);
}

.form-group input.error:focus,
.form-group select.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.10);
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
