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
        <h1 class="page-title"><i class="fas fa-credit-card"></i> <?php echo $titulo; ?></h1>
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
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}
.page-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 4px 0 0 0;
}
.form-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group.full-width {
    grid-column: 1 / -1;
}
label {
    font-weight: 600;
    font-size: 14px;
    color: #334155;
    margin-bottom: 8px;
}
.required {
    color: #ef4444;
}
input[type="text"],
input[type="number"],
textarea,
select {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s;
}
input:focus,
textarea:focus,
select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.form-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 4px;
}
.error-text {
    font-size: 13px;
    color: #ef4444;
    margin-top: 4px;
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 8px;
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: normal;
}
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.btn-primary {
    background: #3b82f6;
    color: #fff;
}
.btn-primary:hover {
    background: #2563eb;
}
.btn-outline {
    background: transparent;
    color: #64748b;
    border: 1px solid #cbd5e1;
}
.btn-outline:hover {
    background: #f1f5f9;
}
</style>
