<?php
/**
 * Página do Dashboard
 */
$titulo = 'Dashboard';
?>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-label">VENDAS TOTAIS</div>
        </div>
        <div class="kpi-value">0 Kz</div>
        <div class="kpi-delta up">↑ 0% <span>vs mês anterior</span></div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue">
                <i class="fas fa-undo"></i>
            </div>
            <div class="kpi-label">DEVOLUÇÕES</div>
        </div>
        <div class="kpi-value">0 Kz</div>
        <div class="kpi-delta down">↓ 0% <span>vs mês anterior</span></div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon orange">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="kpi-label">COMPRAS</div>
        </div>
        <div class="kpi-value">0 Kz</div>
        <div class="kpi-delta up">↑ 0% <span>vs mês anterior</span></div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon red">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="kpi-label">DESPESAS</div>
        </div>
        <div class="kpi-value">0 Kz</div>
        <div class="kpi-delta up">↑ 0% <span>vs mês anterior</span></div>
    </div>

    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon navy">
                <i class="fas fa-coins"></i>
            </div>
            <div class="kpi-label">RESULTADO LÍQUIDO</div>
        </div>
        <div class="kpi-value">0 Kz</div>
        <div class="kpi-delta up">↑ 0% <span>vs mês anterior</span></div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-head">
        <div class="card-title">
            <i class="fas fa-store"></i> Bem-vindo ao MonanaFinancial
        </div>
    </div>
    <div style="padding:20px 0; text-align:center; color:var(--muted);">
        <i class="fas fa-check-circle" style="font-size:48px; color:var(--green); margin-bottom:16px; display:block;"></i>
        <p style="font-size:16px;">
            Olá, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>!
        </p>
        <p style="font-size:14px;">
            Perfil: <strong><?php echo ucfirst(str_replace('_', ' ', $usuario_perfil)); ?></strong>
        </p>
        <p style="font-size:13px; margin-top:8px;">
            O sistema está pronto para uso. Comece a adicionar os seus lançamentos financeiros.
        </p>
        <div style="margin-top:16px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
            <a href="/transacoes/criar" class="btn btn-success">
                <i class="fas fa-plus"></i> Novo Lançamento
            </a>
            <a href="/relatorios/diario" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> Ver Relatórios
            </a>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-head">
        <div class="card-title">
            <i class="fas fa-info-circle"></i> Dicas Rápidas
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; padding:8px 0;">
        <div style="background:#f4f6fa; padding:16px; border-radius:10px; text-align:center;">
            <i class="fas fa-plus-circle" style="font-size:24px; color:var(--green); margin-bottom:8px;"></i>
            <p style="font-size:13px; color:var(--ink);"><strong>Adicione Transações</strong></p>
            <p style="font-size:12px; color:var(--muted);">Registe vendas, compras e despesas.</p>
        </div>
        <div style="background:#f4f6fa; padding:16px; border-radius:10px; text-align:center;">
            <i class="fas fa-chart-pie" style="font-size:24px; color:var(--blue); margin-bottom:8px;"></i>
            <p style="font-size:13px; color:var(--ink);"><strong>Veja Relatórios</strong></p>
            <p style="font-size:12px; color:var(--muted);">Acompanhe o desempenho da sua empresa.</p>
        </div>
        <div style="background:#f4f6fa; padding:16px; border-radius:10px; text-align:center;">
            <i class="fas fa-users" style="font-size:24px; color:var(--purple); margin-bottom:8px;"></i>
            <p style="font-size:13px; color:var(--ink);"><strong>Gestão de Usuários</strong></p>
            <p style="font-size:12px; color:var(--muted);">Adicione colaboradores e defina permissões.</p>
        </div>
    </div>
</div>