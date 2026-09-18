<?php 
$titulo = 'Recuperar Senha'; 
$baseDir = dirname($_SERVER['SCRIPT_NAME']);
?>
<?php $this->layout = 'login'; ?>

<!-- LADO ESQUERDO - IMAGEM DE FUNDO (versão mais compacta) -->
<div class="lado-imagem" style="min-height:500px;">
    <img class="bg-imagem" 
         src="<?php echo APP_URL; ?>/images/background-monana.png" 
         alt="MonanaFinancial">
    <div class="overlay"></div>
    
    <div class="logo-sobre-imagem">
        <div class="icone-logo">
            <i class="fas fa-chart-line"></i>
        </div>
        <div>
            <div class="nome-logo">Monana<span>Financial</span></div>
            <div class="subtitulo-logo">GESTÃO FINANCEIRA INTELIGENTE</div>
        </div>
    </div>
    
    <div class="conteudo-central">
        <div class="badge">
            <i class="fas fa-key" style="margin-right:6px;"></i> Recuperação de Senha
        </div>
        <h2>Recupere o acesso à sua conta</h2>
        <p>Enviaremos um link para redefinir a sua palavra-passe.</p>
    </div>
</div>

<!-- LADO DIREITO - FORMULÁRIO -->
<div class="lado-formulario">
    <div class="cabecalho">
        <p class="saudacao">
            <a href="<?php echo $baseDir; ?>/login" style="color:var(--muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Voltar ao login
            </a>
        </p>
        <h2 class="titulo">Recuperar <span>Senha</span></h2>
        <p class="subtitulo">Digite seu email para receber as instruções.</p>
    </div>
    
    <?php if ($erro): ?>
        <div class="alerta alerta-erro">
            <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="alerta alerta-sucesso">
            <i class="fas fa-check-circle"></i> <?php echo $sucesso; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo $baseDir; ?>/esqueci-senha">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <div class="campo">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <div class="input-com-icone">
                <span class="icone-input"><i class="fas fa-envelope"></i></span>
                <input type="email" id="email" name="email" placeholder="Digite seu email cadastrado" required>
            </div>
        </div>
        
        <button class="btn-submit" type="submit">
            <i class="fas fa-paper-plane"></i> Enviar Link de Recuperação
        </button>
    </form>
    
    <div class="rodape-form">
        © <?php echo date('Y'); ?> <a href="<?php echo $baseDir; ?>/">MonanaFinancial</a> — Todos os direitos reservados.
    </div>
</div>