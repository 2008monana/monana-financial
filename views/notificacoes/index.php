<?php
/**
 * Página de Notificações
 */
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-bell"></i> Notificações</h1>
        <p class="page-subtitle">
            Centro de notificações
            <?php if ($naoLidas > 0): ?>
                <span class="badge badge-danger" style="margin-left:8px;">
                    <i class="fas fa-circle"></i> <?php echo $naoLidas; ?> não lidas
                </span>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-right">
        <?php if (!empty($notificacoes)): ?>
            <form method="POST" action="<?php echo URL_BASE; ?>/notificacoes/marcarTodasComoLidas" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-check-double"></i> Marcar todas como lidas
                </button>
            </form>
        <?php endif; ?>
        <a href="<?php echo URL_BASE; ?>/dashboard/index" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<!-- ==========================================
LISTA DE NOTIFICAÇÕES
========================================== -->
<div class="notificacoes-container">
    <?php if (empty($notificacoes)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
            <h3>Nenhuma notificação</h3>
            <p>Está tudo em ordem! Não há notificações para mostrar.</p>
        </div>
    <?php else: ?>
        <div class="notificacoes-list">
            <?php foreach ($notificacoes as $notificacao): 
                $lida = $notificacao['lida'] ? true : false;
                $tipo = $notificacao['tipo'] ?? 'info';
                $icones = [
                    'alerta' => 'fa-circle-exclamation',
                    'sucesso' => 'fa-circle-check',
                    'aviso' => 'fa-triangle-exclamation',
                    'erro' => 'fa-circle-xmark',
                    'info' => 'fa-info-circle',
                ];
                $cores = [
                    'alerta' => 'var(--orange)',
                    'sucesso' => 'var(--green)',
                    'aviso' => 'var(--orange)',
                    'erro' => 'var(--red)',
                    'info' => 'var(--blue)',
                ];
                $icone = $icones[$tipo] ?? 'fa-bell';
                $cor = $cores[$tipo] ?? 'var(--muted)';
            ?>
                <div class="notificacao-item <?php echo $lida ? 'lida' : 'nao-lida'; ?>">
                    <div class="notificacao-icon" style="color:<?php echo $cor; ?>;">
                        <i class="fas <?php echo $icone; ?>"></i>
                    </div>
                    <div class="notificacao-conteudo">
                        <div class="notificacao-titulo">
                            <?php echo htmlspecialchars($notificacao['titulo']); ?>
                            <?php if (!$lida): ?>
                                <span class="badge badge-primary">Nova</span>
                            <?php endif; ?>
                        </div>
                        <div class="notificacao-mensagem">
                            <?php echo htmlspecialchars($notificacao['mensagem']); ?>
                        </div>
                        <div class="notificacao-data">
                            <i class="far fa-clock"></i>
                            <?php echo date('d/m/Y H:i', strtotime($notificacao['criado_em'])); ?>
                        </div>
                    </div>
                    <div class="notificacao-acoes">
                        <?php if (!$lida): ?>
                            <a href="<?php echo URL_BASE; ?>/notificacoes/marcarComoLida/<?php echo $notificacao['id']; ?>" 
                               class="btn btn-sm btn-secondary" title="Marcar como lida">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($notificacao['link'])): ?>
                            <a href="<?php echo htmlspecialchars($notificacao['link']); ?>" 
                               class="btn btn-sm btn-primary" title="Ver detalhes">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* ============================================
   NOTIFICAÇÕES - ESTILOS COMPLETOS
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
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ============================================
   BADGES
   ============================================ */

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.badge-danger {
    background: rgba(239, 68, 68, 0.12);
    color: var(--red-dark);
}

.badge-danger i {
    color: var(--red);
    font-size: 6px;
}

.badge-primary {
    background: rgba(59, 130, 246, 0.12);
    color: var(--blue);
}

.badge-success {
    background: rgba(34, 197, 94, 0.12);
    color: var(--green-dark);
}

/* ============================================
   NOTIFICAÇÕES
   ============================================ */

.notificacoes-container {
    max-width: 820px;
    margin: 0 auto;
}

.notificacoes-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.notificacao-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 20px;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.notificacao-item:hover {
    box-shadow: var(--shadow-md);
    border-color: transparent;
}

.notificacao-item.nao-lida {
    border-left: 4px solid var(--blue);
    background: #f8faff;
}

.notificacao-item.lida {
    opacity: 0.7;
}

.notificacao-item.lida:hover {
    opacity: 1;
}

.notificacao-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
    background: var(--bg);
}

.notificacao-conteudo {
    flex: 1;
    min-width: 0;
}

.notificacao-titulo {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.notificacao-mensagem {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.5;
}

.notificacao-data {
    font-size: 11px;
    color: var(--muted);
    margin-top: 6px;
}

.notificacao-data i {
    margin-right: 4px;
}

.notificacao-acoes {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

/* ============================================
   EMPTY STATE
   ============================================ */

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--white);
    border-radius: var(--radius);
    border: 2px dashed var(--border);
}

.empty-icon {
    font-size: 56px;
    color: var(--muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 22px;
    margin: 0 0 8px;
    color: var(--ink);
}

.empty-state p {
    font-size: 15px;
    color: var(--muted);
    margin-bottom: 24px;
}

/* ============================================
   BOTÕES
   ============================================ */

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i { font-size: 13px; }

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

.btn-outline {
    background: transparent;
    color: var(--muted);
    border: 1.5px solid var(--border);
}

.btn-outline:hover {
    background: var(--bg);
    color: var(--ink);
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 6px;
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 768px) {
    .notificacao-item {
        flex-direction: column;
        align-items: stretch;
        padding: 14px 16px;
    }
    .notificacao-acoes {
        margin-top: 8px;
        justify-content: flex-end;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .page-header-right {
        width: 100%;
        flex-wrap: wrap;
    }
    .page-header-right .btn {
        flex: 1;
        justify-content: center;
    }
    .page-title {
        font-size: 22px;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 18px;
    }
    .notificacao-item {
        padding: 12px;
    }
    .notificacao-titulo {
        font-size: 13px;
    }
    .notificacao-mensagem {
        font-size: 12px;
    }
}
</style>