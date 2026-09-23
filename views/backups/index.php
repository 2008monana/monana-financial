<div class="modulo-cabecalho logs-cabecalho">
    <div>
        <h1><i class="fa-solid fa-database"></i> Backups</h1>
        <p>
            <?php if ($escopoGlobal): ?>
                Cópias de segurança de <strong>toda a base de dados</strong> do sistema (âmbito Super Admin).
            <?php else: ?>
                Cópias de segurança apenas com os dados <strong>da sua empresa</strong>.
            <?php endif; ?>
        </p>
    </div>
    <form method="post" action="<?=URL_BASE?>/backups/gerar" onsubmit="return confirm('Gerar um backup manual agora?');">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Gerar backup manual</button>
    </form>
</div>

<section class="filtros-logs">
    <form method="post" action="<?=URL_BASE?>/backups/configurarAutomatico" class="form-agendamento">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
        <label>Backup automático
            <select name="ativo">
                <option value="1" <?=$automaticoAtivo ? 'selected' : ''?>>Ativado</option>
                <option value="0" <?=!$automaticoAtivo ? 'selected' : ''?>>Desativado</option>
            </select>
        </label>
        <label>Frequência
            <select name="frequencia_dias">
                <?php for ($d = 1; $d <= 7; $d++): ?>
                    <option value="<?=$d?>" <?=$frequenciaDias === $d ? 'selected' : ''?>>
                        A cada <?=$d?> dia<?=$d > 1 ? 's' : ''?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>
        <div class="filtros-acoes">
            <button class="btn btn-primary"><i class="fa-solid fa-clock"></i> Guardar agendamento</button>
        </div>
        <p class="agendamento-info">
            <i class="fa-solid fa-circle-info"></i>
            Último backup automático concluído:
            <strong><?=$ultimoBackup ? htmlspecialchars(date('d/m/Y H:i', strtotime($ultimoBackup))) : 'nenhum ainda'?></strong>.
            O sistema verifica e gera o backup em atraso automaticamente sempre que esta página é aberta
            (recomenda-se também configurar uma tarefa <code>cron</code> a chamar <code>cron/backup_automatico.php</code>
            uma vez por dia para não depender de visitas à página).
        </p>
    </form>
</section>

<section class="tabela-logs">
    <div class="tabela-logs-cabecalho"><span><?=count($backups)?> backup(s) no histórico</span></div>
    <div class="tabela-scroll">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <?php if ($escopoGlobal): ?><th>Âmbito</th><?php endif; ?>
                    <th>Tipo</th>
                    <th>Ficheiro</th>
                    <th>Tamanho</th>
                    <th>Estado</th>
                    <th>Gerado por</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($backups)): ?>
                <tr><td colspan="8" class="sem-logs"><i class="fa-solid fa-inbox"></i><span>Ainda não existem backups.</span></td></tr>
            <?php endif; ?>
            <?php foreach ($backups as $b): ?>
                <tr>
                    <td><?=htmlspecialchars(date('d/m/Y H:i', strtotime($b['criado_em'])))?></td>
                    <?php if ($escopoGlobal): ?>
                        <td><?=$b['empresa_id'] ? htmlspecialchars($b['empresa_nome'] ?? ('Empresa #' . $b['empresa_id'])) : '<span class="selo selo-info">Global</span>'?></td>
                    <?php endif; ?>
                    <td><?=$b['tipo'] === 'automatico' ? 'Automático' : 'Manual'?></td>
                    <td><?=htmlspecialchars($b['nome_arquivo'])?></td>
                    <td><?=$b['tamanho_bytes'] ? number_format($b['tamanho_bytes'] / 1048576, 2, ',', '.') . ' MB' : '—'?></td>
                    <td>
                        <?php if ($b['status'] === 'concluido'): ?>
                            <span class="selo selo-sucesso">Concluído</span>
                        <?php else: ?>
                            <span class="selo selo-perigo" title="<?=htmlspecialchars($b['mensagem_erro'] ?? '')?>">Falhou</span>
                        <?php endif; ?>
                    </td>
                    <td><?=htmlspecialchars($b['usuario_nome'] ?? 'Sistema')?></td>
                    <td class="acoes-linha">
                        <?php if ($b['status'] === 'concluido'): ?>
                            <a class="btn btn-outline btn-sm" href="<?=URL_BASE?>/backups/download/<?=$b['id']?>"><i class="fa-solid fa-download"></i></a>
                        <?php endif; ?>
                        <form method="post" action="<?=URL_BASE?>/backups/eliminar/<?=$b['id']?>" onsubmit="return confirm('Eliminar este backup?');" style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
                            <button type="submit" class="btn btn-outline btn-sm btn-perigo"><i class="fa-solid fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.form-agendamento{display:flex;flex-wrap:wrap;gap:16px;align-items:end}
.form-agendamento label{display:grid;gap:6px;font-size:12px;font-weight:700;color:var(--ink)}
.form-agendamento select{padding:9px 10px;border:1px solid var(--border);border-radius:8px;background:#fff;font:inherit}
.agendamento-info{flex-basis:100%;font-size:12px;color:var(--muted);margin-top:8px}
.agendamento-info code{background:var(--bg);padding:1px 6px;border-radius:4px}
.selo-info{background:rgba(14,165,233,.12);color:#0369a1}
.selo-perigo{background:rgba(239,68,68,.12);color:#b91c1c}
.acoes-linha{display:flex;gap:6px}
.btn-perigo{color:#b91c1c;border-color:#f3c2c2}
</style>
