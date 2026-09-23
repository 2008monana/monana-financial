<div class="modulo-cabecalho"><div><h1><i class="fa-solid fa-building-gear"></i> Configurações da empresa</h1><p>Dados fiscais, moeda, período fiscal, logotipo e preferências da sua empresa.</p></div></div>
<form method="post" action="<?php echo URL_BASE; ?>/configuracoes/guardar" class="config-form" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="contexto" value="empresa">

    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-image"></i></div><h2>Logotipo</h2></div>
        <div class="config-grid">
            <label>Logotipo actual
                <div style="display:flex;align-items:center;gap:12px;">
                    <?php if (!empty($configuracoes['logotipo'])): ?>
                        <img src="<?php echo URL_BASE . '/' . htmlspecialchars($configuracoes['logotipo']); ?>" alt="Logotipo" style="max-height:60px;max-width:180px;background:#fff;border:1px solid #ddd;border-radius:8px;padding:4px;">
                    <?php else: ?>
                        <span style="color:#888;">Sem logotipo definido.</span>
                    <?php endif; ?>
                </div>
            </label>
            <label>Carregar novo logotipo (PNG/JPG/SVG/WebP, máx. 2 MB)
                <input type="file" name="logotipo_arquivo" accept=".png,.jpg,.jpeg,.svg,.webp,image/png,image/jpeg,image/svg+xml,image/webp">
            </label>
            <?php if (!empty($configuracoes['logotipo'])): ?>
            <label>&nbsp;
                <label style="font-weight:normal;display:flex;align-items:center;gap:6px;"><input type="checkbox" name="remover_logotipo" value="1" style="width:auto;"> Remover logotipo actual</label>
            </label>
            <?php endif; ?>
        </div>
    </section>

    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-file-invoice"></i></div><h2>Dados fiscais e contacto</h2></div>
        <div class="config-grid">
            <label>NIF
                <input name="configuracoes[nif]" placeholder="5410000000" value="<?php echo htmlspecialchars($configuracoes['nif'] ?? ''); ?>">
            </label>
            <label>Endereço
                <input name="configuracoes[endereco]" placeholder="Rua, bairro, cidade — Província" value="<?php echo htmlspecialchars($configuracoes['endereco'] ?? ''); ?>">
            </label>
        </div>
    </section>

    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-coins"></i></div><h2>Moeda e período fiscal</h2></div>
        <div class="config-grid">
            <label>Moeda padrão
                <select name="configuracoes[moeda_padrao]">
                    <?php $moeda = $configuracoes['moeda_padrao'] ?? 'AOA'; ?>
                    <?php foreach (['AOA' => 'Kwanza (AOA)', 'USD' => 'Dólar (USD)', 'EUR' => 'Euro (EUR)', 'BRL' => 'Real (BRL)', 'GBP' => 'Libra (GBP)'] as $sigla => $rotulo): ?>
                        <option value="<?php echo $sigla; ?>" <?php echo $moeda === $sigla ? 'selected' : ''; ?>><?php echo $rotulo; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Início do período fiscal (mês)
                <select name="configuracoes[periodo_fiscal_inicio]">
                    <?php $inicio = (int)($configuracoes['periodo_fiscal_inicio'] ?? 1); ?>
                    <?php foreach (['janeiro'=>1,'fevereiro'=>2,'março'=>3,'abril'=>4,'maio'=>5,'junho'=>6,'julho'=>7,'agosto'=>8,'setembro'=>9,'outubro'=>10,'novembro'=>11,'dezembro'=>12] as $nome => $num): ?>
                        <option value="<?php echo $num; ?>" <?php echo $inicio === $num ? 'selected' : ''; ?>><?php echo ucfirst($nome); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Duração do período fiscal
                <select name="configuracoes[periodo_fiscal_duracao]">
                    <?php $dur = (int)($configuracoes['periodo_fiscal_duracao'] ?? 12); ?>
                    <option value="12" <?php echo $dur === 12 ? 'selected' : ''; ?>>12 meses (ano completo)</option>
                    <option value="6" <?php echo $dur === 6 ? 'selected' : ''; ?>>6 meses (semestre)</option>
                    <option value="3" <?php echo $dur === 3 ? 'selected' : ''; ?>>3 meses (trimestre)</option>
                </select>
            </label>
        </div>
    </section>

    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-bell"></i></div><h2>Preferências de notificação</h2></div>
        <div class="config-grid">
            <?php foreach (['notificacoes_email'=>'Notificações por e-mail','notificacoes_sistema'=>'Notificações no sistema','notificacoes_saldo_baixo'=>'Alerta de saldo baixo','notificacoes_vencimentos'=>'Alerta de vencimentos/prazos','notificacoes_relatorios'=>'Resumo periódico de relatórios'] as $k=>$l): ?>
                <label><?php echo $l; ?><select name="configuracoes[<?php echo $k; ?>]"><option value="1" <?php echo ($configuracoes[$k] ?? '1') === '1' ? 'selected' : ''; ?>>Ativadas</option><option value="0" <?php echo ($configuracoes[$k] ?? '') === '0' ? 'selected' : ''; ?>>Desativadas</option></select></label>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (($_SESSION['usuario_perfil'] ?? '') === 'super_admin'): ?>
    <section class="config-cartao">
        <div class="config-cartao-cabecalho"><div class="config-cartao-icone"><i class="fa-solid fa-palette"></i></div><h2>Aparência</h2></div>
        <div class="config-grid">
            <?php foreach(['cor_primaria'=>'Cor principal','cor_secundaria'=>'Cor secundária'] as $k=>$l): ?>
                <label><?php echo $l; ?><input name="configuracoes[<?php echo $k; ?>]" placeholder="#0e2748" value="<?php echo htmlspecialchars($configuracoes[$k] ?? ''); ?>"></label>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar configurações</button>
</form>
<?php require CAMINHO_RAIZ.'/views/configuracoes/_estilos.php'; ?>
