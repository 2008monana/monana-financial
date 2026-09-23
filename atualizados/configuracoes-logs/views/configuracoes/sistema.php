<div class="modulo-cabecalho"><div><h1><i class="fa-solid fa-gear"></i> Configurações do sistema</h1><p>Defina parâmetros globais, correio eletrónico e preferências da plataforma.</p></div></div>
<form method="post" action="<?php echo URL_BASE; ?>/configuracoes/guardar" class="config-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="contexto" value="sistema">
    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-sliders"></i></div><h2>Geral</h2></div>
        <div class="config-grid">
            <?php foreach(['nome_sistema'=>'Nome do sistema','fuso_horario'=>'Fuso horário','idioma'=>'Idioma padrão','moeda'=>'Moeda padrão','sessao_expiracao'=>'Expiração da sessão (segundos)'] as $k=>$l): ?>
                <label><?php echo $l; ?><input name="configuracoes[<?php echo $k; ?>]" value="<?php echo htmlspecialchars($configuracoes[$k]??''); ?>"></label>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-envelope"></i></div><h2>SMTP</h2></div>
        <p>A senha fica encriptada na base de dados e nunca é apresentada novamente.</p>
        <div class="config-grid">
            <?php foreach(['smtp_host'=>'Servidor SMTP','smtp_porta'=>'Porta SMTP','smtp_usuario'=>'Utilizador SMTP','smtp_senha'=>'Senha SMTP'] as $k=>$l): ?>
                <label><?php echo $l; ?><input type="<?php echo $k==='smtp_senha'?'password':'text'; ?>" name="configuracoes[<?php echo $k; ?>]" value="<?php echo $k==='smtp_senha'?'':htmlspecialchars($configuracoes[$k]??''); ?>" autocomplete="off"></label>
            <?php endforeach; ?>
        </div>
    </section>
    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar configurações</button>
</form>
<?php require CAMINHO_RAIZ.'/views/configuracoes/_estilos.php'; ?>
