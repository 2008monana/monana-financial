<?php
/**
 * Formulário de Categoria (Criar/Editar)
 */
$ehEdicao = !empty($categoria['id']);
$titulo = $ehEdicao ? 'Editar Categoria' : 'Nova Categoria';
$subtitulo = $ehEdicao ? 'Atualize os dados da categoria' : 'Registe uma nova categoria';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas <?php echo $ehEdicao ? 'fa-edit' : 'fa-tag'; ?>"></i>
            <?php echo $titulo; ?>
        </h1>
        <p class="page-subtitle"><?php echo $subtitulo; ?></p>
    </div>
    <a href="<?php echo URL_BASE; ?>/categorias/index<?php echo $empresaId ? '?empresa_id=' . $empresaId : ''; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="form-container">
    <form method="post" action="<?php echo URL_BASE . '/categorias/' . ($ehEdicao ? 'atualizar/' . (int) $categoria['id'] : 'armazenar'); ?>" class="form-modern">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="empresa_id" value="<?php echo (int) $empresaId; ?>">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon" style="background:linear-gradient(135deg, var(--navy), var(--navy-light));">
                    <i class="fas fa-tag"></i>
                </div>
                <div>
                    <h3>Dados da Categoria</h3>
                    <p>Preencha as informações da categoria</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-tag"></i> Nome <span class="required">*</span>
                    </label>
                    <input type="text" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($categoria['nome'] ?? ''); ?>" 
                           placeholder="Ex: Vendas ao Balcão, Fornecedores, ..." required>
                    <?php if (!empty($erros['nome'])): ?>
                        <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['nome']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">
                            <i class="fas fa-arrow-right"></i> Tipo <span class="required">*</span>
                        </label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecione...</option>
                            <option value="entrada" <?php echo ($categoria['tipo'] ?? '') === 'entrada' ? 'selected' : ''; ?>>Entrada (Receita)</option>
                            <option value="saida" <?php echo ($categoria['tipo'] ?? '') === 'saida' ? 'selected' : ''; ?>>Saída (Despesa)</option>
                        </select>
                        <?php if (!empty($erros['tipo'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['tipo']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="cor">
                            <i class="fas fa-palette"></i> Cor
                        </label>
                        <div class="cor-input-group">
                            <input type="color" id="cor" name="cor" 
                                   value="<?php echo htmlspecialchars($categoria['cor'] ?? '#64748b'); ?>">
                            <input type="text" id="cor_hex" 
                                   value="<?php echo htmlspecialchars($categoria['cor'] ?? '#64748b'); ?>" 
                                   placeholder="#RRGGBB">
                        </div>
                        <?php if (!empty($erros['cor'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['cor']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <div style="display:flex; align-items:center; gap:10px; padding-top:4px;">
                        <label style="display:flex; align-items:center; gap:6px; font-weight:400; font-size:14px; cursor:pointer;">
                            <input type="checkbox" name="ativa" value="1" <?php echo (!isset($categoria['ativa']) || $categoria['ativa']) ? 'checked' : ''; ?>>
                            Categoria ativa
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas <?php echo $ehEdicao ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $ehEdicao ? 'Guardar Alterações' : 'Criar Categoria'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/categorias/index<?php echo $empresaId ? '?empresa_id=' . $empresaId : ''; ?>" class="btn btn-secondary">
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
});
</script>

<style>
/* ============================================
   CATEGORIAS - FORMULÁRIO
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
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
}

.page-subtitle {
    font-size: 14px;
    color: var(--muted);
    margin: 4px 0 0;
}

.form-container {
    max-width: 640px;
}

.form-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 20px;
}

.form-card:hover {
    box-shadow: var(--shadow-md);
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

.form-group {
    margin-bottom: 18px;
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
   INPUT DE COR
   ============================================ */

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
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    padding: 11px 14px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    color: var(--ink);
    background: var(--white);
    transition: all 0.3s ease;
}

.cor-input-group input[type="text"]:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

/* ============================================
   FORM ROW
   ============================================ */

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 20px;
}

/* ============================================
   BOTÕES
   ============================================ */

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

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .form-row {
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
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-title {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .form-card-body {
        padding: 12px;
    }
    .page-title {
        font-size: 18px;
    }
}
</style>