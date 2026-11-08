<div class="backup-page">
    <header class="backup-hero">
        <div class="backup-hero__content">
            <span class="backup-eyebrow"><i class="fa-solid fa-shield-halved"></i> Proteção de dados</span>
            <h1>Backups</h1>
            <p><?= $escopoGlobal ? 'Gere, restaure e acompanhe cópias de segurança de toda a base de dados.' : 'Gere e acompanhe cópias de segurança dos dados da sua empresa.' ?></p>
        </div>
        <form method="post" action="<?= URL_BASE ?>/backups/gerar" onsubmit="return confirm('Gerar um backup manual agora?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <button type="submit" class="btn backup-primary"><i class="fa-solid fa-plus"></i> Gerar backup agora</button>
        </form>
    </header>

    <section class="backup-stats" aria-label="Resumo dos backups">
        <article><span class="backup-stat-icon success"><i class="fa-solid fa-clock-rotate-left"></i></span><div><small>Último backup</small><strong><?= $ultimoBackup ? htmlspecialchars(date('d/m/Y H:i', strtotime($ultimoBackup))) : 'Ainda não gerado' ?></strong></div></article>
        <article><span class="backup-stat-icon blue"><i class="fa-solid fa-box-archive"></i></span><div><small>Histórico</small><strong><?= count($backups) ?> backup(s)</strong></div></article>
        <article><span class="backup-stat-icon purple"><i class="fa-solid fa-globe"></i></span><div><small>Âmbito atual</small><strong><?= $escopoGlobal ? 'Base de dados completa' : 'Dados da empresa' ?></strong></div></article>
    </section>

    <section class="backup-grid">
        <article class="backup-card">
            <div class="backup-card__title"><span class="backup-card-icon"><i class="fa-solid fa-arrows-rotate"></i></span><div><h2>Backup automático</h2><p>Defina quando o sistema deve gerar a próxima cópia.</p></div></div>
            <form method="post" action="<?= URL_BASE ?>/backups/configurarAutomatico" class="backup-settings">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <label>Estado<select name="ativo"><option value="1" <?= $automaticoAtivo ? 'selected' : '' ?>>Ativado</option><option value="0" <?= !$automaticoAtivo ? 'selected' : '' ?>>Desativado</option></select></label>
                <label>Frequência<select name="frequencia_dias"><?php for ($d = 1; $d <= 7; $d++): ?><option value="<?= $d ?>" <?= $frequenciaDias === $d ? 'selected' : '' ?>>A cada <?= $d ?> dia<?= $d > 1 ? 's' : '' ?></option><?php endfor; ?></select></label>
                <button class="btn btn-outline"><i class="fa-solid fa-check"></i> Guardar</button>
            </form>
            <p class="backup-hint"><i class="fa-solid fa-circle-info"></i> O sistema verifica backups em atraso ao abrir esta página. Para maior fiabilidade, configure também o cron diário.</p>
        </article>

        <?php if ($escopoGlobal): ?>
        <article class="backup-card backup-card--restore">
            <div class="backup-card__title"><span class="backup-card-icon warning"><i class="fa-solid fa-upload"></i></span><div><h2>Restaurar backup</h2><p>Importe um ZIP global gerado pelo MonanaFinancial.</p></div></div>
            <form method="post" enctype="multipart/form-data" action="<?= URL_BASE ?>/backups/importar" onsubmit="return confirm('A restauração substitui os dados atuais. Deseja continuar?');" class="backup-import">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <label class="backup-file"><i class="fa-solid fa-file-zipper"></i><span>Selecionar ficheiro .zip</span><input type="file" name="backup" accept=".zip,application/zip" required></label>
                <button class="btn btn-warning" type="submit"><i class="fa-solid fa-triangle-exclamation"></i> Importar e restaurar</button>
            </form>
            <p class="backup-hint"><i class="fa-solid fa-lock"></i> Apenas backups globais, com a estrutura original, podem ser restaurados.</p>
        </article>
        <?php endif; ?>
    </section>

    <section class="backup-history">
        <div class="backup-history__header"><div><h2>Histórico de backups</h2><p>Descarregue ou elimine cópias armazenadas no servidor.</p></div><span><?= count($backups) ?> registo(s)</span></div>
        <div class="backup-table-wrap"><table><thead><tr><th>Data</th><?php if ($escopoGlobal): ?><th>Âmbito</th><?php endif; ?><th>Tipo</th><th>Ficheiro</th><th>Tamanho</th><th>Estado</th><th>Gerado por</th><th class="text-right">Ações</th></tr></thead><tbody>
        <?php if (!$backups): ?><tr><td colspan="8" class="backup-empty"><i class="fa-solid fa-box-open"></i> Ainda não existem backups no histórico.</td></tr><?php endif; ?>
        <?php foreach ($backups as $b): ?><tr><td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($b['criado_em']))) ?></td><?php if ($escopoGlobal): ?><td><?= $b['empresa_id'] ? htmlspecialchars($b['empresa_nome'] ?? ('Empresa #' . $b['empresa_id'])) : '<span class="backup-tag blue">Global</span>' ?></td><?php endif; ?><td><span class="backup-tag"><?= $b['tipo'] === 'automatico' ? 'Automático' : 'Manual' ?></span></td><td class="backup-name"><i class="fa-solid fa-file-zipper"></i> <?= htmlspecialchars($b['nome_arquivo']) ?></td><td><?= $b['tamanho_bytes'] ? number_format($b['tamanho_bytes'] / 1048576, 2, ',', '.') . ' MB' : '—' ?></td><td><?= $b['status'] === 'concluido' ? '<span class="backup-status success">Concluído</span>' : '<span class="backup-status error" title="' . htmlspecialchars($b['mensagem_erro'] ?? '') . '">Falhou</span>' ?></td><td><?= htmlspecialchars($b['usuario_nome'] ?? 'Sistema') ?></td><td class="backup-actions"><?php if ($b['status'] === 'concluido'): ?><a class="btn btn-outline btn-sm" title="Descarregar" href="<?= URL_BASE ?>/backups/download/<?= $b['id'] ?>"><i class="fa-solid fa-download"></i></a><?php endif; ?><form method="post" action="<?= URL_BASE ?>/backups/eliminar/<?= $b['id'] ?>" onsubmit="return confirm('Eliminar este backup?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"><button type="submit" title="Eliminar" class="btn btn-outline btn-sm backup-delete"><i class="fa-solid fa-trash-alt"></i></button></form></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</div>
<style>
.backup-page{max-width:1240px;margin:0 auto}.backup-hero{display:flex;justify-content:space-between;align-items:center;gap:24px;padding:30px 34px;border-radius:18px;background:linear-gradient(120deg,#062e36,#075e54 58%,#0f766e);color:#fff;box-shadow:0 16px 35px rgba(6,78,59,.18)}.backup-eyebrow{display:inline-flex;gap:8px;align-items:center;color:#a7f3d0;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}.backup-hero h1{margin:7px 0 5px;font-size:30px}.backup-hero p{margin:0;color:#d1fae5}.backup-primary{background:#fff!important;color:#047857!important;border:0!important}.backup-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:22px 0}.backup-stats article,.backup-card,.backup-history{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 5px 18px rgba(15,23,42,.04)}.backup-stats article{padding:18px;display:flex;align-items:center;gap:12px}.backup-stat-icon,.backup-card-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:11px;background:#dcfce7;color:#15803d}.backup-stat-icon.blue{background:#dbeafe;color:#2563eb}.backup-stat-icon.purple{background:#ede9fe;color:#7c3aed}.backup-stats small{display:block;color:#64748b;font-size:12px}.backup-stats strong{display:block;margin-top:3px;color:#0f172a;font-size:14px}.backup-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.backup-card{padding:23px}.backup-card__title{display:flex;gap:12px;align-items:flex-start}.backup-card-icon{background:#dbeafe;color:#2563eb}.backup-card-icon.warning{background:#fef3c7;color:#b45309}.backup-card h2,.backup-history h2{font-size:16px;margin:0;color:#0f172a}.backup-card p,.backup-history p{margin:4px 0 0;color:#64748b;font-size:13px}.backup-settings{display:flex;align-items:end;gap:10px;flex-wrap:wrap;margin-top:20px}.backup-settings label{display:grid;gap:5px;flex:1;color:#475569;font-size:12px;font-weight:700}.backup-settings select{padding:10px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;font:inherit}.backup-hint{padding-top:14px;border-top:1px solid #f1f5f9!important}.backup-import{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:20px}.backup-file{cursor:pointer;display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px dashed #94a3b8;border-radius:8px;color:#475569;font-size:13px}.backup-file input{max-width:170px}.btn-warning{background:#b45309!important;border-color:#b45309!important;color:white!important}.backup-history{margin-top:20px;overflow:hidden}.backup-history__header{display:flex;align-items:center;justify-content:space-between;padding:20px 22px;border-bottom:1px solid #e5e7eb}.backup-history__header>span,.backup-tag{background:#f1f5f9;color:#475569;border-radius:999px;padding:4px 9px;font-size:11px;font-weight:700}.backup-table-wrap{overflow:auto}.backup-history table{width:100%;border-collapse:collapse;font-size:13px}.backup-history th{background:#f8fafc;color:#64748b;text-transform:uppercase;letter-spacing:.04em;font-size:10px}.backup-history th,.backup-history td{padding:14px 16px;border-bottom:1px solid #f1f5f9;text-align:left;white-space:nowrap}.backup-name{max-width:230px;overflow:hidden;text-overflow:ellipsis}.backup-name i{color:#f59e0b}.backup-tag.blue{background:#dbeafe;color:#1d4ed8}.backup-status{padding:4px 9px;border-radius:999px;font-size:11px;font-weight:700}.backup-status.success{background:#dcfce7;color:#15803d}.backup-status.error{background:#fee2e2;color:#b91c1c}.backup-actions{display:flex;justify-content:flex-end;gap:6px}.backup-actions form{display:inline}.backup-delete{color:#b91c1c;border-color:#fecaca}.text-right{text-align:right!important}.backup-empty{text-align:center!important;color:#64748b;padding:42px!important}.backup-empty i{margin-right:8px}@media(max-width:800px){.backup-hero{align-items:flex-start;flex-direction:column}.backup-stats,.backup-grid{grid-template-columns:1fr}.backup-settings label{min-width:130px}}
</style>
