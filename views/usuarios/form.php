<?php
$ehEdicao = !empty($usuario['id']);
$titulo = $ehEdicao ? 'Editar Utilizador' : 'Novo Utilizador';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas <?php echo $ehEdicao ? 'fa-user-edit' : 'fa-user-plus'; ?>"></i>
            <?php echo $titulo; ?>
        </h1>
        <p class="page-subtitle">
            <?php echo $ehEdicao ? 'Atualize os dados do utilizador' : 'Registe um novo utilizador no sistema'; ?>
        </p>
    </div>
    <a href="<?php echo URL_BASE; ?>/usuarios/index<?php echo empty($_SESSION['empresa_id']) && $empresaId ? '?empresa_id=' . (int) $empresaId : ''; ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<div class="form-container">
    <form method="post" action="<?php echo URL_BASE . '/usuarios/' . ($ehEdicao ? 'atualizar/' . (int) $usuario['id'] : 'gravar'); ?>">
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

        <!-- Card: Dados Pessoais -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3>Dados Pessoais</h3>
                    <p>Informações básicas do utilizador</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="nome">
                            <i class="fas fa-user"></i> Nome Completo <span class="required">*</span>
                        </label>
                        <input type="text" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" 
                               placeholder="Nome do utilizador" required>
                        <?php if (!empty($erros['nome'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['nome']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> E-mail <span class="required">*</span>
                        </label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" 
                               placeholder="email@empresa.co.ao" required>
                        <?php if (!empty($erros['email'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['email']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="perfil">
                            <i class="fas fa-user-tag"></i> Perfil <span class="required">*</span>
                        </label>
                        <select id="perfil" name="perfil" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($perfisGeriveis as $valor => $rotulo): ?>
                                <option value="<?php echo $valor; ?>" <?php echo ($usuario['perfil'] ?? '') === $valor ? 'selected' : ''; ?>>
                                    <?php echo $rotulo; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($erros['perfil'])): ?>
                            <span class="error-text"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erros['perfil']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="cargo">
                            <i class="fas fa-briefcase"></i> Cargo
                        </label>
                        <input type="text" id="cargo" name="cargo" 
                               value="<?php echo htmlspecialchars($usuario['cargo'] ?? ''); ?>" 
                               placeholder="Ex: Gerente, Caixa, ...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefone">
                        <i class="fas fa-phone"></i> Telefone
                    </label>
                    <input type="text" id="telefone" name="telefone" 
                           value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" 
                           placeholder="+244 9XX XXX XXX">
                </div>

                <?php if (!$ehEdicao): ?>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        Uma senha de acesso aleatória será gerada automaticamente e mostrada após criar o utilizador.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Filiais -->
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon" style="background:linear-gradient(135deg, var(--purple), var(--purple-dark));">
                    <i class="fas fa-store-alt"></i>
                </div>
                <div>
                    <h3>Acesso a Filiais</h3>
                    <p>Selecione as filiais que este utilizador pode aceder</p>
                </div>
            </div>

            <div class="form-card-body">
                <?php if (empty($filiaisEmpresa)): ?>
                    <div class="empty-filiais">
                        <i class="fas fa-store-alt"></i>
                        <p>Esta empresa ainda não tem filiais registadas.</p>
                    </div>
                <?php else: ?>
                    <div class="filiais-checkbox-grid">
                        <?php foreach ($filiaisEmpresa as $filial): ?>
                            <label class="checkbox-card">
                                <input type="checkbox" name="filiais[]" value="<?php echo $filial['id']; ?>"
                                       <?php echo in_array($filial['id'], $filiaisIds) ? 'checked' : ''; ?>>
                                <span class="checkbox-label">
                                    <i class="fas fa-store"></i>
                                    <?php echo htmlspecialchars($filial['nome']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Permissões por Módulo -->
        <div class="form-card">
            <div class="form-card-header" style="background:linear-gradient(135deg, #fef3c7, #fde68a); border-color:#f59e0b;">
                <div class="form-card-icon" style="background:linear-gradient(135deg, var(--orange), var(--orange-dark));">
                    <i class="fas fa-lock"></i>
                </div>
                <div>
                    <h3>Permissões por Módulo</h3>
                    <p>Selecione os módulos que este utilizador pode aceder</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="info-box info-box-warning" style="margin-bottom:16px;">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Nota:</strong> O módulo <strong>"Perfil"</strong> é sempre acessível a todos os utilizadores.
                        Utilizadores sem permissão para um módulo serão redirecionados para o Perfil.
                    </div>
                </div>

                <div class="modulos-grid">
                    <?php foreach ($modulos as $modulo): ?>
                        <?php if ($modulo['nome'] === 'perfil') continue; ?>
                        <?php $permitido = in_array($modulo['id'], $modulosPermitidosIds); ?>
                        <label class="modulo-item <?php echo $permitido ? 'selected' : ''; ?>">
                            <input type="checkbox" name="modulos[]" value="<?php echo $modulo['id']; ?>"
                                   <?php echo $permitido ? 'checked' : ''; ?>
                                   onchange="this.parentElement.classList.toggle('selected')">
                            <span class="modulo-icon">
                                <i class="fas <?php echo $modulo['icone'] ?? 'fa-circle'; ?>"></i>
                            </span>
                            <span class="modulo-nome">
                                <?php echo ucfirst($modulo['nome']); ?>
                            </span>
                            <span class="modulo-check">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Perfil sempre ativo -->
                <div class="modulo-perfil-sempre">
                    <i class="fas fa-user-circle"></i>
                    <span><strong>Perfil</strong> — sempre acessível</span>
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas <?php echo $ehEdicao ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $ehEdicao ? 'Guardar Alterações' : 'Criar Utilizador'; ?>
            </button>
            <a href="<?php echo URL_BASE; ?>/usuarios/index<?php echo empty($_SESSION['empresa_id']) && $empresaId ? '?empresa_id=' . (int) $empresaId : ''; ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<style>
/* ============================================
   UTILIZADORES - FORMULÁRIO COMPLETO
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

.error-text {
    display: block;
    font-size: 12px;
    color: var(--red);
    margin-top: 5px;
    font-weight: 500;
}

.error-text i { margin-right: 4px; }

.info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.5;
}

.info-box i {
    font-size: 18px;
    margin-top: 1px;
    flex-shrink: 0;
}

.info-box-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: var(--orange-dark);
}

.info-box-warning i {
    color: var(--orange);
}

.empty-filiais {
    text-align: center;
    padding: 20px;
    color: var(--muted);
}

.empty-filiais i {
    font-size: 28px;
    display: block;
    margin-bottom: 8px;
}

.empty-filiais p { margin: 0; }

.filiais-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.checkbox-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.checkbox-card:hover {
    border-color: var(--green);
    background: #fafbfc;
}

.checkbox-card input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--green);
    cursor: pointer;
    flex-shrink: 0;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
    cursor: pointer;
}

.checkbox-label i { color: var(--muted); }

/* ============================================
   PERMISSÕES POR MÓDULO
   ============================================ */

.modulos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.modulo-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--white);
}

.modulo-item:hover {
    border-color: var(--muted);
    background: #fafbfc;
}

.modulo-item.selected {
    border-color: var(--green);
    background: #f0fdf4;
}

.modulo-item input[type="checkbox"] {
    display: none;
}

.modulo-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: 14px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.modulo-item.selected .modulo-icon {
    background: var(--green);
    color: var(--white);
}

.modulo-nome {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
}

.modulo-check {
    color: transparent;
    font-size: 18px;
    transition: all 0.3s ease;
}

.modulo-item.selected .modulo-check {
    color: var(--green);
}

.modulo-perfil-sempre {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f1f3f6;
    border-radius: 10px;
    margin-top: 12px;
    font-size: 13px;
    color: var(--muted);
}

.modulo-perfil-sempre i {
    font-size: 18px;
    color: var(--navy);
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

@media (max-width: 768px) {
    .form-grid-2 { grid-template-columns: 1fr; gap: 0; }
    .modulos-grid { grid-template-columns: 1fr 1fr; }
    .filiais-checkbox-grid { grid-template-columns: 1fr 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-title { font-size: 22px; }
    .form-card-body { padding: 16px; }
}

@media (max-width: 480px) {
    .modulos-grid { grid-template-columns: 1fr; }
    .filiais-checkbox-grid { grid-template-columns: 1fr; }
    .form-actions { flex-direction: column; }
    .form-actions .btn { justify-content: center; width: 100%; }
}
</style>