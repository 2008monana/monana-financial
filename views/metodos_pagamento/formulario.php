<?php
/**
 * Formulário de Método de Pagamento (Criar/Editar)
 */

$edicao = isset($metodo) && !empty($metodo);
$dadosForm = $_SESSION['dados_form'] ?? ($edicao ? $metodo : []);
$erros = $_SESSION['erros'] ?? [];
unset($_SESSION['dados_form'], $_SESSION['erros']);

$titulo = $edicao ? 'Editar Método de Pagamento' : 'Novo Método de Pagamento';
$acao = $edicao ? URL_BASE . '/metodos-pagamento/atualizar' : URL_BASE . '/metodos-pagamento/salvar';
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title mp-page-title"><i class="fas fa-credit-card"></i> <?php echo $titulo; ?></h1>
        <p class="page-subtitle">Preencha os dados abaixo</p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/metodos-pagamento" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="<?php echo $acao; ?>" class="form-metodo-pagamento">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
        <?php if ($edicao): ?>
        <input type="hidden" name="id" value="<?php echo $metodo['id']; ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label for="nome">
                    <i class="fas fa-tag"></i> Nome <span class="required">*</span>
                </label>
                <input type="text" id="nome" name="nome" 
                       value="<?php echo htmlspecialchars($dadosForm['nome'] ?? ''); ?>" 
                       placeholder="Ex: Numerário, Transferência, TPA" required autofocus>
                <?php if (!empty($erros['nome'])): ?>
                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo $erros['nome']; ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="icone">
                    <i class="fas fa-image"></i> Ícone (FontAwesome)
                </label>
                <input type="text" id="icone" name="icone" 
                       value="<?php echo htmlspecialchars($dadosForm['icone'] ?? ''); ?>" 
                       placeholder="Ex: fas fa-money-bill-wave">
                <small class="form-text">Use classes do FontAwesome para ícones.</small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group full-width">
                <label for="descricao">
                    <i class="fas fa-align-left"></i> Descrição
                </label>
                <textarea id="descricao" name="descricao" rows="3" 
                          placeholder="Descrição opcional do método de pagamento..."><?php echo htmlspecialchars($dadosForm['descricao'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ordem">
                    <i class="fas fa-sort-numeric-down"></i> Ordem
                </label>
                <input type="number" id="ordem" name="ordem" 
                       value="<?php echo htmlspecialchars($dadosForm['ordem'] ?? '0'); ?>" 
                       min="0" placeholder="0">
                <small class="form-text">Ordem de exibição na lista (menor primeiro).</small>
            </div>

            <div class="form-group">
                <label for="ativo">
                    <i class="fas fa-toggle-on"></i> Status
                </label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="ativo" value="1" 
                               <?php echo !isset($dadosForm['ativo']) || $dadosForm['ativo'] ? 'checked' : ''; ?>>
                        <span>Método ativo</span>
                    </label>
                </div>
            </div>

            <?php if ($usuario['perfil'] === 'super_admin'): ?>
            <div class="form-group">
                <label for="empresa_id">
                    <i class="fas fa-building"></i> Empresa
                </label>
                <select id="empresa_id" name="empresa_id">
                    <option value="">Padrão do Sistema (todas as empresas)</option>
                    <?php if (!empty($empresas)): ?>
                    <?php foreach ($empresas as $empresa): ?>
                    <option value="<?php echo $empresa['id']; ?>" 
                            <?php echo ($dadosForm['empresa_id'] ?? '') == $empresa['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($empresa['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="form-text">Deixe em branco para ser padrão do sistema.</small>
            </div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?php echo $edicao ? 'Atualizar' : 'Salvar'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/metodos-pagamento" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<style>
/* ============================================
   FORMULÁRIO MÉTODO DE PAGAMENTO — padrão navy/verde do sistema
   ============================================ */

/* ---- HEADER ---- */
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px; }
.mp-page-title { font-family:'Sora',sans-serif; font-size:26px; font-weight:700; color:var(--navy-deep); margin:0; }
.mp-page-title i { color:var(--green); margin-right:12px; }
.page-subtitle { font-size:14px; color:var(--muted); margin:0; }
.page-header-right { display:flex; gap:10px; flex-wrap:wrap; }

/* ---- CARTÃO DO FORMULÁRIO ---- */
.form-card {
    background:var(--white); border-radius:var(--radius); border:1px solid var(--border);
    padding:28px; margin-bottom:24px; overflow:hidden;
}

/* ---- CAMPOS ---- */
.form-row {
    display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
    gap:20px; margin-bottom:20px;
}
.form-group { display:flex; flex-direction:column; }
.form-group.full-width { grid-column:1 / -1; }
.form-group label {
    font-size:12px; font-weight:600; color:var(--muted);
    text-transform:uppercase; letter-spacing:.03em; margin-bottom:6px;
}
.form-group label i { color:var(--blue); margin-right:6px; }
.required { color:var(--red); }
.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
    width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:10px;
    font-family:'Inter',sans-serif; font-size:14px; background:#fff; color:var(--ink); outline:none;
    transition:border-color .2s, box-shadow .2s;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color:var(--navy); box-shadow:0 0 0 3px rgba(14,39,72,.1);
}
.form-text { font-size:12px; color:var(--muted); margin-top:4px; }
.error-text { font-size:13px; color:var(--red); margin-top:4px; }

/* ---- CHECKBOX (toggle visual) ---- */
.checkbox-group { display:flex; align-items:center; gap:8px; padding:8px 0; }
.checkbox-label {
    display:inline-flex; align-items:center; gap:10px; cursor:pointer;
    font-size:14px; font-weight:500; color:var(--ink); text-transform:none; letter-spacing:normal;
}
.checkbox-label input[type="checkbox"] { width:18px; height:18px; accent-color:var(--green); cursor:pointer; }

/* ---- ACÇÕES ---- */
.form-actions {
    display:flex; gap:12px; margin-top:24px; padding-top:20px;
    border-top:1px solid var(--border);
}
.btn {
    display:inline-flex; align-items:center; gap:8px; padding:10px 18px; border-radius:10px;
    font-size:14px; font-weight:600; font-family:'Inter',sans-serif; text-decoration:none;
    transition:all .3s ease; border:none; cursor:pointer;
}
.btn i { font-size:14px; }
.btn-primary {
    background:linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color:var(--white); box-shadow:0 4px 14px rgba(14,39,72,.25);
}
.btn-primary:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(14,39,72,.35); }
.btn-outline { background:transparent; color:var(--ink); border:1.5px solid var(--border); }
.btn-outline:hover { background:var(--bg); }
</style>
