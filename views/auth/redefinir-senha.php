<?php $titulo = 'Redefinir Senha'; ?>
<?php $this->layout = 'login'; ?>

<div class="lado-marca lado-marca-menor">
    <div class="marca">
        <div class="icone-marca">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>
            <div class="nome-marca">Monana<span>Financial</span></div>
            <div class="subtitulo-marca">GESTÃO FINANCEIRA INTELIGENTE</div>
        </div>
    </div>
    <div class="hero-marca" style="margin-top:20px;">
        <h1 style="font-size:24px;">Crie uma nova senha</h1>
        <p>Defina uma nova palavra-passe para sua conta.</p>
    </div>
</div>

<div class="lado-formulario lado-formulario-central">
    <div class="boas-vindas">
        <p class="saudacao"><i class="fas fa-arrow-left"></i> <a href="/login" style="color:var(--muted);text-decoration:none;">Voltar ao login</a></p>
        <h2>Nova <span>Senha</span></h2>
        <p class="subtitulo">A senha deve ter pelo menos 8 caracteres.</p>
    </div>

    <?php if ($erro): ?>
        <div class="alerta alerta-erro">
            <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/redefinir-senha">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        
        <div class="campo">
            <label for="senha"><i class="fas fa-lock"></i> Nova Senha</label>
            <input type="password" id="senha" name="senha" placeholder="Digite a nova senha" required minlength="8">
        </div>

        <div class="campo">
            <label for="confirmar_senha"><i class="fas fa-check-circle"></i> Confirmar Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha" required minlength="8">
        </div>

        <button class="btn-submit" type="submit">
            <i class="fas fa-save"></i> Redefinir Senha
        </button>
    </form>
</div>