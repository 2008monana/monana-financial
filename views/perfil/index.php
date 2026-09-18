<?php
/**
 * Página de Perfil do Utilizador
 */
?>
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-cog"></i> Meu Perfil</h1>
        <p class="page-subtitle">Gerencie os seus dados pessoais e preferências</p>
    </div>
</div>

<!-- ============================================
CARD 1: DADOS PESSOAIS
============================================ -->
<div class="form-card" style="max-width: 820px;">
    <div class="form-card-header">
        <div class="form-card-icon" style="background:linear-gradient(135deg, var(--navy), var(--navy-light));">
            <i class="fas fa-user"></i>
        </div>
        <div>
            <h3>Dados Pessoais</h3>
            <p>Atualize as suas informações de contacto</p>
        </div>
    </div>

    <div class="form-card-body">
        <form method="POST" action="<?php echo URL_BASE; ?>/perfil/atualizar">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nome <span class="required">*</span></label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                    <small style="color:var(--muted);font-size:11px;">O email não pode ser alterado</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Telefone</label>
                    <input type="text" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" placeholder="+244 9XX XXX XXX">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Cargo</label>
                    <input type="text" name="cargo" value="<?php echo htmlspecialchars($usuario['cargo'] ?? ''); ?>" placeholder="Ex: Gerente, Caixa, ...">
                </div>
            </div>

            <div class="form-row" style="margin-top:8px;">
                <div class="form-group">
                    <label><i class="fas fa-building"></i> Empresa</label>
                    <input type="text" value="<?php echo htmlspecialchars($empresaNome ?? 'N/A'); ?>" disabled>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Perfil</label>
                    <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $usuario['perfil'])); ?>" disabled>
                </div>
            </div>

            <div class="form-actions" style="border-top:none;padding-top:0;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
CARD 2: ALTERAR SENHA
============================================ -->
<div class="form-card" style="max-width: 820px; margin-top:20px;">
    <div class="form-card-header" style="background:#fef2f2; border-color:#fecaca;">
        <div class="form-card-icon" style="background:linear-gradient(135deg, var(--red), var(--red-dark));">
            <i class="fas fa-lock"></i>
        </div>
        <div>
            <h3>Alterar Senha</h3>
            <p>Atualize a sua palavra-passe de acesso</p>
        </div>
    </div>

    <div class="form-card-body">
        <form method="POST" action="<?php echo URL_BASE; ?>/perfil/alterarSenha">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label><i class="fas fa-key"></i> Senha Atual <span class="required">*</span></label>
                <input type="password" name="senha_atual" placeholder="Digite a sua senha atual" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nova Senha <span class="required">*</span></label>
                    <input type="password" name="nova_senha" placeholder="Mínimo 8 caracteres" required minlength="8">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirmar Nova Senha <span class="required">*</span></label>
                    <input type="password" name="confirmar_senha" placeholder="Confirme a nova senha" required minlength="8">
                </div>
            </div>

            <div class="info-box info-box-warning" style="margin-bottom:0;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Atenção:</strong> A senha deve ter pelo menos 8 caracteres.
                </div>
            </div>

            <div class="form-actions" style="border-top:none;padding-top:16px;">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-key"></i> Alterar Senha
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
CARD 3: PERMISSÕES
============================================ -->
<div class="form-card" style="max-width: 820px; margin-top:20px; opacity:0.85;">
    <div class="form-card-header" style="background:#f0fdf4; border-color:#bbf7d0;">
        <div class="form-card-icon" style="background:linear-gradient(135deg, var(--green), var(--green-dark));">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div>
            <h3>Minhas Permissões</h3>
            <p>Módulos a que tem acesso no sistema</p>
        </div>
    </div>

    <div class="form-card-body">
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px,1fr)); gap:8px;">
            <?php foreach ($permissoes as $modulo): ?>
                <div style="display:flex; align-items:center; gap:8px; padding:8px 12px; background:<?php echo $modulo['permitido'] ? '#f0fdf4' : '#f1f3f6'; ?>; border-radius:8px; border:1px solid <?php echo $modulo['permitido'] ? '#bbf7d0' : '#e6eaf0'; ?>;">
                    <i class="fas <?php echo $modulo['icone'] ?? 'fa-circle'; ?>" style="color:<?php echo $modulo['permitido'] ? 'var(--green)' : 'var(--muted)'; ?>;"></i>
                    <span style="font-size:13px; font-weight:500; color:<?php echo $modulo['permitido'] ? 'var(--ink)' : 'var(--muted)'; ?>;">
                        <?php echo htmlspecialchars($modulo['descricao'] ?? $modulo['nome'] ?? 'Módulo'); ?>
                    </span>
                    <?php if ($modulo['permitido']): ?>
                        <span style="margin-left:auto; font-size:11px; color:var(--green-dark); font-weight:600;">
                            <i class="fas fa-check-circle"></i>
                        </span>
                    <?php else: ?>
                        <span style="margin-left:auto; font-size:11px; color:var(--muted);">
                            <i class="fas fa-lock"></i>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS DO PERFIL
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

.form-card {
    background: var(--white);
    border-radius: 14px;
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.3s ease;
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

.form-row {
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

.form-group input:disabled {
    background: #f1f3f6;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-group small {
    display: block;
    margin-top: 4px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 8px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

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

.info-box-warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: var(--orange-dark);
}

.info-box-warning i {
    color: var(--orange);
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

.btn-danger {
    background: linear-gradient(135deg, var(--red), var(--red-dark));
    color: var(--white);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.35);
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