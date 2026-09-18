<?php
/**
 * Listagem de Utilizadores - Versão Melhorada
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">
            <i class="fas fa-users"></i> Utilizadores
        </h1>
        <p class="page-subtitle">
            <?php if (!empty($empresa)): ?>
                <span class="empresa-tag">
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($empresa['nome']); ?>
                </span>
            <?php else: ?>
                Selecione uma empresa para gerir os utilizadores
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-right">
        <?php if (empty($_SESSION['empresa_id'])): ?>
            <a href="<?php echo URL_BASE; ?>/usuarios/index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Trocar Empresa
            </a>
        <?php endif; ?>
        <a href="<?php echo URL_BASE; ?>/usuarios/criar<?php echo empty($_SESSION['empresa_id']) ? '?empresa_id=' . (int)($empresa['id'] ?? 0) : ''; ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Novo Utilizador
        </a>
    </div>
</div>

<!-- ============================================
     ESTATÍSTICAS
     ============================================ -->
<?php 
$totalUsuarios = count($usuarios);
$ativos = count(array_filter($usuarios, fn($u) => $u['ativo']));
$inativos = $totalUsuarios - $ativos;

$perfis = [];
foreach ($usuarios as $u) {
    $perfil = $u['perfil'] ?? 'usuario_interno';
    $perfis[$perfil] = ($perfis[$perfil] ?? 0) + 1;
}

$rotulosPerfil = [
    'super_admin' => 'Super Admin',
    'admin_empresa' => 'Admin Empresa',
    'usuario_interno' => 'Interno',
    'visualizador' => 'Visualizador'
];

$coresPerfil = [
    'super_admin' => 'purple',
    'admin_empresa' => 'navy',
    'usuario_interno' => 'blue',
    'visualizador' => 'gray'
];

$iconesPerfil = [
    'super_admin' => 'fa-crown',
    'admin_empresa' => 'fa-user-tie',
    'usuario_interno' => 'fa-user',
    'visualizador' => 'fa-eye'
];
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon navy">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $totalUsuarios; ?></span>
            <span class="stat-label">Total Utilizadores</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $ativos; ?></span>
            <span class="stat-label">Utilizadores Ativos</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-user-slash"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo $inativos; ?></span>
            <span class="stat-label">Utilizadores Inativos</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-user-tag"></i>
        </div>
        <div class="stat-info">
            <span class="stat-value"><?php echo count($perfis); ?></span>
            <span class="stat-label">Perfis Diferentes</span>
        </div>
    </div>
</div>

<!-- ============================================
     FILTRO POR PERFIL (Badges Interativos)
     ============================================ -->
<?php if (!empty($perfis)): ?>
    <div class="perfil-filters">
        <span class="filter-label"><i class="fas fa-filter"></i> Filtrar por perfil:</span>
        <button class="perfil-filter-btn active" data-perfil="todos" onclick="filtrarPerfil('todos')">
            <i class="fas fa-users"></i> Todos (<?php echo $totalUsuarios; ?>)
        </button>
        <?php foreach ($perfis as $perfil => $count): ?>
            <button class="perfil-filter-btn" data-perfil="<?php echo $perfil; ?>" onclick="filtrarPerfil('<?php echo $perfil; ?>')">
                <i class="fas <?php echo $iconesPerfil[$perfil] ?? 'fa-user'; ?>"></i>
                <?php echo $rotulosPerfil[$perfil] ?? ucfirst($perfil); ?> (<?php echo $count; ?>)
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============================================
     GRID DE UTILIZADORES
     ============================================ -->
<?php if (empty($usuarios)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-users"></i>
        </div>
        <h3>Nenhum utilizador registado</h3>
        <p>Comece por criar o primeiro utilizador para esta empresa.</p>
        <a href="<?php echo URL_BASE; ?>/usuarios/criar<?php echo empty($_SESSION['empresa_id']) ? '?empresa_id=' . (int)($empresa['id'] ?? 0) : ''; ?>" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Criar Utilizador
        </a>
    </div>
<?php else: ?>
    <div class="usuarios-grid" id="usuariosGrid">
        <?php foreach ($usuarios as $usuario): ?>
            <div class="usuario-card <?php echo $usuario['ativo'] ? '' : 'inativo'; ?>" 
                 data-perfil="<?php echo $usuario['perfil']; ?>">
                
                <!-- Cabeçalho do Card -->
                <div class="usuario-card-header">
                    <div class="usuario-avatar" style="background: linear-gradient(135deg, <?php 
                        echo $usuario['perfil'] === 'super_admin' ? 'var(--purple), var(--purple-dark)' :
                            ($usuario['perfil'] === 'admin_empresa' ? 'var(--navy), var(--navy-light)' :
                            ($usuario['perfil'] === 'usuario_interno' ? 'var(--blue), var(--blue-dark)' :
                            'var(--muted), #94a3b8')); 
                    ?>);">
                        <?php echo strtoupper(substr($usuario['nome'], 0, 2)); ?>
                    </div>
                    <div class="usuario-info">
                        <h3><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                        <span class="usuario-email">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['email']); ?>
                        </span>
                        <?php if ($usuario['cargo']): ?>
                            <span class="usuario-cargo">
                                <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($usuario['cargo']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="usuario-status">
                        <span class="status-badge <?php echo $usuario['ativo'] ? 'ativo' : 'inativo'; ?>">
                            <i class="fas fa-circle"></i> <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </div>
                </div>

                <!-- Corpo do Card -->
                <div class="usuario-card-body">
                    <div class="usuario-details">
                        <div class="usuario-perfil">
                            <span class="perfil-badge badge-<?php echo $coresPerfil[$usuario['perfil']] ?? 'gray'; ?>">
                                <i class="fas <?php echo $iconesPerfil[$usuario['perfil']] ?? 'fa-user'; ?>"></i>
                                <?php echo $rotulosPerfil[$usuario['perfil']] ?? ucfirst($usuario['perfil']); ?>
                            </span>
                        </div>
                        <?php if ($usuario['telefone']): ?>
                            <span class="detail-item">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($usuario['telefone']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="detail-item">
                            <i class="fas fa-clock"></i> 
                            <?php if ($usuario['ultimo_login']): ?>
                                Último acesso: <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_login'])); ?>
                            <?php else: ?>
                                Nunca acedeu
                            <?php endif; ?>
                        </span>
                        <?php if ($usuario['primeiro_acesso']): ?>
                            <span class="detail-item warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Primeiro acesso - senha não alterada
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rodapé do Card - Ações -->
                <div class="usuario-card-footer">
                    <a href="<?php echo URL_BASE; ?>/usuarios/editar/<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <form method="post" action="<?php echo URL_BASE; ?>/usuarios/redefinirSenha/<?php echo $usuario['id']; ?>" style="display:inline;">
                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Gerar nova senha para este utilizador?')" title="Redefinir senha">
                            <i class="fas fa-key"></i> Nova Senha
                        </button>
                    </form>
                    <form method="post" action="<?php echo URL_BASE; ?>/usuarios/alternarEstado/<?php echo $usuario['id']; ?>" style="display:inline;">
                        <button type="submit" class="btn btn-sm <?php echo $usuario['ativo'] ? 'btn-danger' : 'btn-success'; ?>" 
                                onclick="return confirm('<?php echo $usuario['ativo'] ? 'Deseja desativar' : 'Deseja reativar'; ?> este utilizador?')">
                            <i class="fas <?php echo $usuario['ativo'] ? 'fa-ban' : 'fa-undo'; ?>"></i>
                            <?php echo $usuario['ativo'] ? 'Desativar' : 'Reativar'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============================================
     SCRIPT DE FILTRO
     ============================================ -->
<script>
function filtrarPerfil(perfil) {
    // Atualizar botões
    document.querySelectorAll('.perfil-filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.perfil === perfil);
    });

    // Filtrar cards
    const cards = document.querySelectorAll('.usuario-card');
    cards.forEach(card => {
        if (perfil === 'todos' || card.dataset.perfil === perfil) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<style>
/* ============================================
   UTILIZADORES - ESTILOS COMPLETOS
   ============================================ */

/* ---- HEADER ---- */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
}

.page-header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
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
    margin: 0;
}

.empresa-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: rgba(14, 39, 72, 0.08);
    border-radius: 20px;
    font-weight: 600;
    color: var(--navy);
    font-size: 13px;
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ---- ESTATÍSTICAS ---- */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--white);
    flex-shrink: 0;
}

.stat-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
.stat-icon.green { background: linear-gradient(135deg, var(--green), var(--green-dark)); }
.stat-icon.red { background: linear-gradient(135deg, var(--red), var(--red-dark)); }
.stat-icon.purple { background: linear-gradient(135deg, var(--purple), var(--purple-dark)); }

.stat-info {
    display: flex;
    flex-direction: column;
}

.stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.stat-label {
    font-size: 12px;
    color: var(--muted);
    font-weight: 500;
}

/* ---- FILTROS POR PERFIL ---- */
.perfil-filters {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding: 12px 16px;
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
    margin-right: 8px;
}

.filter-label i { margin-right: 4px; }

.perfil-filter-btn {
    padding: 6px 14px;
    border: 1.5px solid var(--border);
    border-radius: 20px;
    background: var(--white);
    font-size: 12px;
    font-weight: 500;
    color: var(--muted);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Inter', sans-serif;
}

.perfil-filter-btn:hover {
    border-color: var(--navy);
    color: var(--navy);
}

.perfil-filter-btn.active {
    background: var(--navy);
    color: var(--white);
    border-color: var(--navy);
}

.perfil-filter-btn i { margin-right: 4px; }

/* ---- GRID DE UTILIZADORES ---- */
.usuarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 20px;
}

/* ---- CARD DO UTILIZADOR ---- */
.usuario-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    transition: all 0.3s ease;
}

.usuario-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.usuario-card.inativo {
    opacity: 0.7;
    border-color: #f1f3f6;
}

.usuario-card.inativo .usuario-card-header {
    background: #f8fafc;
}

/* ---- Cabeçalho do Card ---- */
.usuario-card-header {
    padding: 18px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    border-bottom: 1px solid var(--border);
    background: #fafbfc;
}

.usuario-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 20px;
    flex-shrink: 0;
}

.usuario-info {
    flex: 1;
    min-width: 0;
}

.usuario-info h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.usuario-email {
    display: block;
    font-size: 13px;
    color: var(--muted);
    margin-top: 2px;
}

.usuario-email i { margin-right: 4px; }

.usuario-cargo {
    display: block;
    font-size: 12px;
    color: var(--muted);
    margin-top: 1px;
}

.usuario-cargo i { margin-right: 4px; }

.usuario-status {
    flex-shrink: 0;
}

/* ---- Status Badge ---- */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.ativo {
    background: #dcfce7;
    color: var(--green-dark);
}

.status-badge.ativo i { color: var(--green); font-size: 7px; }

.status-badge.inativo {
    background: #fee2e2;
    color: var(--red-dark);
}

.status-badge.inativo i { color: var(--red); font-size: 7px; }

/* ---- Corpo do Card ---- */
.usuario-card-body {
    padding: 14px 20px;
}

.usuario-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.usuario-perfil {
    margin-bottom: 4px;
}

.perfil-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 600;
}

.perfil-badge i { font-size: 12px; }

.badge-navy { background: rgba(14, 39, 72, 0.12); color: var(--navy); }
.badge-purple { background: rgba(139, 92, 246, 0.12); color: var(--purple); }
.badge-blue { background: rgba(59, 130, 246, 0.12); color: var(--blue); }
.badge-gray { background: #f1f3f6; color: var(--muted); }

.detail-item {
    font-size: 13px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-item i {
    width: 16px;
    color: var(--muted);
}

.detail-item.warning {
    color: var(--orange-dark);
}

.detail-item.warning i {
    color: var(--orange);
}

/* ---- Rodapé do Card ---- */
.usuario-card-footer {
    padding: 12px 20px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.usuario-card-footer .btn {
    flex: 1;
    justify-content: center;
    min-width: 60px;
    font-size: 12px;
    padding: 6px 10px;
}

/* ---- BOTÕES ---- */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn i { font-size: 14px; }

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

.btn-success {
    background: linear-gradient(135deg, var(--green), var(--green-dark));
    color: var(--white);
    box-shadow: 0 4px 14px rgba(34, 197, 94, 0.25);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
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

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
}

/* ---- EMPTY STATE ---- */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: var(--white);
    border-radius: var(--radius);
    border: 2px dashed var(--border);
}

.empty-icon {
    font-size: 48px;
    color: var(--muted);
    margin-bottom: 16px;
}

.empty-state h3 {
    font-size: 20px;
    margin: 0 0 8px;
    color: var(--ink);
}

.empty-state p {
    color: var(--muted);
    margin-bottom: 20px;
}

/* ============================================
   RESPONSIVO
   ============================================ */

@media (max-width: 992px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .usuarios-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .perfil-filters {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .perfil-filter-btn {
        font-size: 11px;
        padding: 4px 10px;
    }
    
    .usuario-card-footer .btn {
        font-size: 11px;
        padding: 4px 8px;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .usuario-card-header {
        flex-wrap: wrap;
    }
    
    .usuario-status {
        margin-left: auto;
    }
    
    .usuario-card-footer {
        flex-direction: column;
    }
    
    .usuario-card-footer .btn {
        justify-content: center;
        width: 100%;
    }
}
</style>