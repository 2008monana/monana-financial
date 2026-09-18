<?php
$ehEdicao = !empty($empresa['id']);
$titulo = $ehEdicao ? 'Editar Empresa' : 'Nova Empresa';
$subtitulo = $ehEdicao ? 'Atualize os dados da empresa' : 'Registe uma nova empresa e o seu administrador';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas <?php echo $ehEdicao ? 'fa-edit' : 'fa-building'; ?>"></i>
            <?php echo $titulo; ?>
        </h1>
        <p class="page-subtitle"><?php echo $subtitulo; ?></p>
    </div>
    <a href="<?php echo URL_BASE; ?>/empresas/index" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="form-container">
    <form method="post" action="<?php echo URL_BASE . '/empresas/' . ($ehEdicao ? 'atualizar/' . (int) $empresa['id'] : 'gravar'); ?>" class="form-modern" id="formEmpresa">
        
        <!-- ==========================================
        CARD 1: DADOS DA EMPRESA
        ========================================== -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h3>Dados da Empresa</h3>
                    <p>Informações principais da empresa</p>
                </div>
            </div>
            
            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-building"></i> Nome da Empresa <span class="required">*</span>
                        </label>
                        <input type="text" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>" 
                               placeholder="Ex: MAMABAR (SU), Lda" required>
                        <?php if (!empty($erros['nome'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['nome']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="nif">
                            <i class="fas fa-id-card"></i> NIF
                        </label>
                        <input type="text" id="nif" name="nif" 
                               value="<?php echo htmlspecialchars($empresa['nif'] ?? ''); ?>" 
                               placeholder="Número de identificação fiscal">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="email_contacto">
                            <i class="fas fa-envelope"></i> E-mail de Contacto
                        </label>
                        <input type="email" id="email_contacto" name="email_contacto" 
                               value="<?php echo htmlspecialchars($empresa['email_contacto'] ?? ''); ?>" 
                               placeholder="contacto@empresa.co.ao">
                        <?php if (!empty($erros['email_contacto'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['email_contacto']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="telefone">
                            <i class="fas fa-phone"></i> Telefone
                        </label>
                        <input type="text" id="telefone" name="telefone" 
                               value="<?php echo htmlspecialchars($empresa['telefone'] ?? ''); ?>" 
                               placeholder="+244 9XX XXX XXX">
                    </div>
                </div>

                <div class="form-group">
                    <label for="endereco">
                        <i class="fas fa-map-marker-alt"></i> Endereço
                    </label>
                    <textarea id="endereco" name="endereco" rows="2" 
                              placeholder="Endereço completo da empresa"><?php echo htmlspecialchars($empresa['endereco'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- ==========================================
        CARD 2: ADMINISTRADOR DA EMPRESA (APENAS CRIAÇÃO)
        ========================================== -->
        <?php if (!$ehEdicao): ?>
            <div class="form-card" id="cardAdmin">
                <div class="form-card-header" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-color: #bbf7d0;">
                    <div class="form-card-icon" style="background: linear-gradient(135deg, var(--green), var(--green-dark));">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h3>Administrador da Empresa <span style="font-size:12px; font-weight:400; color:var(--green-dark);">(criação automática)</span></h3>
                        <p>O administrador terá acesso total à empresa e poderá gerir utilizadores, filiais e transações</p>
                    </div>
                </div>

                <div class="form-card-body">
                    <!-- Info -->
                    <div class="info-box info-box-green">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Senha automática:</strong> Será gerada uma senha aleatória e mostrada ao criar a empresa.
                            O administrador deverá alterá-la no primeiro acesso.
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="admin_nome">
                                <i class="fas fa-user"></i> Nome do Administrador <span class="required">*</span>
                            </label>
                            <input type="text" id="admin_nome" name="admin_nome" 
                                   value="<?php echo htmlspecialchars($_POST['admin_nome'] ?? ''); ?>" 
                                   placeholder="Nome do administrador" required>
                            <?php if (!empty($erros['admin_nome'])): ?>
                                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['admin_nome']); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="admin_email">
                                <i class="fas fa-envelope"></i> E-mail do Administrador <span class="required">*</span>
                            </label>
                            <input type="email" id="admin_email" name="admin_email" 
                                   value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>" 
                                   placeholder="admin@empresa.co.ao" required>
                            <?php if (!empty($erros['admin_email'])): ?>
                                <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['admin_email']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="admin_telefone">
                            <i class="fas fa-phone"></i> Telefone do Administrador
                        </label>
                        <input type="text" id="admin_telefone" name="admin_telefone" 
                               value="<?php echo htmlspecialchars($_POST['admin_telefone'] ?? ''); ?>" 
                               placeholder="+244 9XX XXX XXX">
                    </div>

                    <!-- Opção para definir senha manualmente (avançado) -->
                    <details class="advanced-options">
                        <summary><i class="fas fa-cog"></i> Opções avançadas (definir senha manualmente)</summary>
                        <div class="advanced-content">
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="admin_senha">
                                        <i class="fas fa-lock"></i> Senha (opcional)
                                    </label>
                                    <input type="text" id="admin_senha" name="admin_senha" 
                                           value="<?php echo htmlspecialchars($_POST['admin_senha'] ?? ''); ?>" 
                                           placeholder="Deixe em branco para gerar automaticamente">
                                </div>
                                <div class="form-group">
                                    <label for="admin_senha_confirmar">
                                        <i class="fas fa-check-circle"></i> Confirmar Senha
                                    </label>
                                    <input type="text" id="admin_senha_confirmar" name="admin_senha_confirmar" 
                                           value="<?php echo htmlspecialchars($_POST['admin_senha_confirmar'] ?? ''); ?>" 
                                           placeholder="Confirme a senha">
                                </div>
                            </div>
                            <div class="info-box info-box-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Atenção:</strong> Se definir uma senha, certifique-se de que tem pelo menos 8 caracteres.
                                    Se deixar em branco, será gerada automaticamente.
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        <?php else: ?>
            <!-- Em edição, mostrar apenas o admin atual -->
            <div class="form-card" style="opacity:0.7;">
                <div class="form-card-header" style="background: #f1f3f6;">
                    <div class="form-card-icon" style="background: var(--muted);">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <h3>Administrador da Empresa</h3>
                        <p>Para alterar o administrador, utilize a gestão de utilizadores</p>
                    </div>
                </div>
                <div class="form-card-body">
                    <div class="info-box info-box-gray">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            O administrador atual pode ser gerido em 
                            <a href="<?php echo URL_BASE; ?>/usuarios/index?empresa_id=<?php echo (int) $empresa['id']; ?>" style="color:var(--green-dark); font-weight:600;">
                                Utilizadores → <?php echo htmlspecialchars($empresa['nome'] ?? ''); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ==========================================
        AÇÕES
        ========================================== -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas <?php echo $ehEdicao ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $ehEdicao ? 'Guardar Alterações' : 'Criar Empresa'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/empresas/index" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<style>
/* ============================================
   EMPRESAS - FORMULÁRIO COMPLETO
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
    max-width: 820px;
}

/* ============================================
   CARD DO FORMULÁRIO
   ============================================ */

.form-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    margin-bottom: 20px;
    transition: var(--transition);
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

/* ============================================
   GRID E CAMPOS
   ============================================ */

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
.form-group textarea,
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
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.10);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a3adba;
}

.form-group textarea {
    resize: vertical;
    min-height: 60px;
    line-height: 1.6;
}

.error-text {
    display: block;
    font-size: 12px;
    color: var(--red);
    margin-top: 5px;
    font-weight: 500;
}

.error-text i { margin-right: 4px; }

/* ============================================
   INFO BOXES
   ============================================ */

.info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 16px;
}

.info-box i {
    font-size: 18px;
    margin-top: 1px;
    flex-shrink: 0;
}

.info-box-green {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: var(--green-dark);
}

.info-box-green i {
    color: var(--green);
}

.info-box-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: var(--orange-dark);
}

.info-box-warning i {
    color: var(--orange);
}

.info-box-gray {
    background: #f1f3f6;
    border: 1px solid var(--border);
    color: var(--muted);
}

.info-box-gray i {
    color: var(--muted);
}

.info-box a {
    color: var(--green-dark);
    font-weight: 600;
    text-decoration: none;
}

.info-box a:hover {
    text-decoration: underline;
}

/* ============================================
   OPÇÕES AVANÇADAS
   ============================================ */

.advanced-options {
    margin-top: 8px;
    cursor: pointer;
}

.advanced-options summary {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    font-size: 13px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    list-style: none;
    user-select: none;
}

.advanced-options summary::-webkit-details-marker {
    display: none;
}

.advanced-options summary i {
    color: var(--muted);
    transition: transform 0.3s ease;
}

.advanced-options[open] summary i {
    transform: rotate(90deg);
}

.advanced-options summary:hover {
    color: var(--ink);
}

.advanced-content {
    padding: 12px 0 4px;
    border-top: 1px dashed var(--border);
    margin-top: 8px;
}

/* ============================================
   AÇÕES
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

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .form-grid-2 { grid-template-columns: 1fr; gap: 0; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .form-card-body { padding: 16px; }
}

@media (max-width: 480px) {
    .form-actions { flex-direction: column; }
    .form-actions .btn { justify-content: center; width: 100%; }
    .page-title { font-size: 20px; }
    .info-box { flex-direction: column; align-items: flex-start; gap: 4px; }
}
</style>