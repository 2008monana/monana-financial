<?php
$ehEdicao = !empty($metodo['id']);
$titulo = $ehEdicao ? 'Editar Método de Pagamento' : 'Novo Método de Pagamento';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas <?php echo $ehEdicao ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $titulo; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $ehEdicao ? 'Atualize os dados do método' : 'Registe um novo método de pagamento'; ?>
        </p>
    </div>
    <a href="<?php echo URL_BASE; ?>/metodos-pagamento/index" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="form-container">
    <form method="post" action="<?php echo URL_BASE . '/metodos-pagamento/' . ($ehEdicao ? 'atualizar/' . (int) $metodo['id'] : 'gravar'); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="empresa_id" value="<?php echo (int) $empresaId; ?>">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon" style="background:linear-gradient(135deg, var(--navy), var(--navy-light));">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div>
                    <h3>Dados do Método</h3>
                    <p>Preencha as informações do método de pagamento</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-tag"></i> Nome <span class="required">*</span>
                        </label>
                        <input type="text" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($metodo['nome'] ?? ''); ?>" 
                               placeholder="Ex: TPA - BFA" required>
                    </div>

                    <div class="form-group">
                        <label for="codigo">
                            <i class="fas fa-code"></i> Código
                        </label>
                        <input type="text" id="codigo" name="codigo" 
                               value="<?php echo htmlspecialchars($metodo['codigo'] ?? ''); ?>" 
                               placeholder="Ex: tpa_bfa (deixe em branco para gerar automático)">
                        <small style="color:var(--muted); font-size:11px;">Usado internamente. Deixe em branco para gerar automaticamente.</small>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="tipo">
                            <i class="fas fa-arrow-right"></i> Tipo <span class="required">*</span>
                        </label>
                        <select id="tipo" name="tipo" required>
                            <option value="entrada" <?php echo ($metodo['tipo'] ?? '') === 'entrada' ? 'selected' : ''; ?>>Entrada (Receita)</option>
                            <option value="saida" <?php echo ($metodo['tipo'] ?? '') === 'saida' ? 'selected' : ''; ?>>Saída (Despesa)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="categoria">
                            <i class="fas fa-folder"></i> Categoria <span class="required">*</span>
                        </label>
                        <select id="categoria" name="categoria" required>
                            <?php foreach ($categorias as $valor => $rotulo): ?>
                            <option value="<?php echo $valor; ?>" <?php echo ($metodo['categoria'] ?? '') === $valor ? 'selected' : ''; ?>>
                                <?php echo $rotulo; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="icone">
                            <i class="fas fa-icons"></i> Ícone
                        </label>
                        <select id="icone" name="icone">
                            <?php foreach ($icones as $valor => $rotulo): ?>
                            <option value="<?php echo $valor; ?>" <?php echo ($metodo['icone'] ?? '') === $valor ? 'selected' : ''; ?>>
                                <i class="fas <?php echo $valor; ?>"></i> <?php echo $rotulo; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div style="margin-top:4px; font-size:24px;">
                            <i class="fas <?php echo $metodo['icone'] ?? 'fa-credit-card'; ?>" id="iconePreview"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cor">
                            <i class="fas fa-palette"></i> Cor
                        </label>
                        <div class="cor-input-group">
                            <input type="color" id="cor" name="cor" 
                                   value="<?php echo htmlspecialchars($metodo['cor'] ?? '#64748b'); ?>">
                            <input type="text" id="cor_hex" 
                                   value="<?php echo htmlspecialchars($metodo['cor'] ?? '#64748b'); ?>" 
                                   placeholder="#RRGGBB">
                        </div>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="ordem">
                            <i class="fas fa-sort"></i> Ordem
                        </label>
                        <input type="number" id="ordem" name="ordem" 
                               value="<?php echo $metodo['ordem'] ?? 0; ?>" 
                               placeholder="0" min="0">
                        <small style="color:var(--muted); font-size:11px;">Números menores aparecem primeiro.</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-toggle-on"></i> Status
                        </label>
                        <div style="display:flex; align-items:center; gap:10px; padding-top:4px;">
                            <label style="display:flex; align-items:center; gap:6px; font-weight:400; font-size:14px; cursor:pointer;">
                                <input type="checkbox" name="ativo" value="1" <?php echo (!isset($metodo['ativo']) || $metodo['ativo']) ? 'checked' : ''; ?>>
                                Método ativo
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas <?php echo $ehEdicao ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $ehEdicao ? 'Guardar Alterações' : 'Criar Método'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/metodos-pagamento/index" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
// Sincronizar color picker com input hex
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('cor');
    const colorHex = document.getElementById('cor_hex');
    const iconeSelect = document.getElementById('icone');
    const iconePreview = document.getElementById('iconePreview');
    
    // Cor
    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
        
        colorHex.addEventListener('input', function() {
            if (/^#[0-9a-f]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
    }
    
    // Ícone
    if (iconeSelect && iconePreview) {
        iconeSelect.addEventListener('change', function() {
            iconePreview.className = 'fas ' + this.value;
        });
    }
});
</script>

<style>
/* ============================================
   FORMULÁRIO - MÉTODOS DE PAGAMENTO
   ============================================ */

.form-container {
    max-width: 820px;
}

.form-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 20px;
}

.form-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: #fafbfc;
    border-bottom: 1px solid var(--border);
}

.form-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: var(--white);
    flex-shrink: 0;
}

.form-card-header h3 {
    font-size: 15px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.form-card-header p {
    font-size: 13px;
    color: var(--muted);
    margin: 0;
}

.form-card-body {
    padding: 20px;
}

.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 20px;
}

.form-group {
    margin-bottom: 4px;
}

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
}

.form-group .required {
    color: var(--red);
    font-weight: 700;
    margin-left: 2px;
}

.form-group input,
.form-group select {
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
.form-group select:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

.form-group input::placeholder {
    color: #a3adba;
}

.form-group small {
    display: block;
    margin-top: 4px;
}

.cor-input-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cor-input-group input[type="color"] {
    width: 48px;
    height: 48px;
    padding: 2px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    flex-shrink: 0;
}

.cor-input-group input[type="text"] {
    flex: 1;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 8px;
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

.btn i { font-size: 15px; }

.btn-primary {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(14, 39, 72, 0.25);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(14, 39, 72, 0.35);
}

.btn-secondary {
    background: var(--bg);
    color: var(--ink);
    border: 1.5px solid var(--border);
}

.btn-secondary:hover {
    background: var(--border);
}

@media (max-width: 768px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .form-card-body {
        padding: 16px;
    }
    .form-actions {
        flex-direction: column;
    }
    .form-actions .btn {
        justify-content: center;
        width: 100%;
    }
}
</style>