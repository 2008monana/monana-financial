<?php
$ehEdicao = !empty($filial['id']);
$titulo = $ehEdicao ? 'Editar Filial' : 'Nova Filial';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas <?php echo $ehEdicao ? 'fa-edit' : 'fa-store-alt'; ?>"></i>
            <?php echo $titulo; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $ehEdicao ? 'Atualize os dados da filial' : 'Registe uma nova filial'; ?>
        </p>
    </div>
    <a href="<?php echo URL_BASE; ?>/filiais/index<?php echo empty($_SESSION['empresa_id']) && $empresaId ? '?empresa_id=' . (int) $empresaId : ''; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="form-container">
    <form method="post" action="<?php echo URL_BASE . '/filiais/' . ($ehEdicao ? 'atualizar/' . (int) $filial['id'] : 'gravar'); ?>">
        <input type="hidden" name="empresa_id" value="<?php echo (int) $empresaId; ?>">

        <!-- Informação da Empresa -->
        <div class="info-banner">
            <div class="info-banner-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="info-banner-text">
                <strong>Empresa:</strong> 
                <?php 
                    $empresa = (new Empresa())->encontrarPorId($empresaId);
                    echo htmlspecialchars($empresa['nome'] ?? 'Empresa não encontrada');
                ?>
            </div>
        </div>

        <!-- Card do Formulário -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-store-alt"></i>
                </div>
                <div>
                    <h3>Dados da Filial</h3>
                    <p>Preencha as informações da filial</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="form-group">
                    <label for="nome">
                        <i class="fas fa-tag"></i> Nome da Filial <span class="required">*</span>
                    </label>
                    <input type="text" id="nome" name="nome" 
                           value="<?php echo htmlspecialchars($filial['nome'] ?? ''); ?>" 
                           placeholder="Ex: Prenda, Viana, ..." required>
                    <?php if (!empty($erros['nome'])): ?>
                        <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['nome']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="telefone">
                            <i class="fas fa-phone"></i> Telefone
                        </label>
                        <input type="text" id="telefone" name="telefone" 
                               value="<?php echo htmlspecialchars($filial['telefone'] ?? ''); ?>" 
                               placeholder="+244 9XX XXX XXX">
                    </div>

                    <div class="form-group">
                        <label for="endereco">
                            <i class="fas fa-map-marker-alt"></i> Endereço
                        </label>
                        <input type="text" id="endereco" name="endereco" 
                               value="<?php echo htmlspecialchars($filial['endereco'] ?? ''); ?>" 
                               placeholder="Endereço da filial">
                    </div>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas <?php echo $ehEdicao ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $ehEdicao ? 'Guardar Alterações' : 'Criar Filial'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/filiais/index<?php echo empty($_SESSION['empresa_id']) && $empresaId ? '?empresa_id=' . (int) $empresaId : ''; ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<style>
/* ============================================
   FILIAIS - FORMULÁRIO COMPLETO
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
    max-width: 720px;
}

.info-banner {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: var(--radius);
    margin-bottom: 20px;
}

.info-banner-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--green), var(--green-dark));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.info-banner-text {
    font-size: 14px;
    color: var(--ink);
}

.info-banner-text strong {
    color: var(--navy);
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
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
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

.form-group input {
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

.form-group input:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

.form-group input::placeholder {
    color: #a3adba;
}

.error-text {
    display: block;
    font-size: 12px;
    color: var(--red);
    margin-top: 5px;
    font-weight: 500;
}

.error-text i { margin-right: 4px; }

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

@media (max-width: 768px) {
    .form-grid-2 { grid-template-columns: 1fr; gap: 0; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-title { font-size: 22px; }
}

@media (max-width: 480px) {
    .form-actions { flex-direction: column; }
    .form-actions .btn { justify-content: center; width: 100%; }
    .form-card-body { padding: 16px; }
}
</style>