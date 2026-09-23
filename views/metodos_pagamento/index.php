<?php
/**
 * Listagem de Métodos de Pagamento
 */
$usuario = $this->auth->usuario();
$pode_criar = in_array($usuario['perfil'], ['super_admin', 'admin_empresa']);
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title"><i class="fas fa-credit-card"></i> Métodos de Pagamento</h1>
        <p class="page-subtitle">Gerencie os métodos de pagamento disponíveis</p>
    </div>
    <div class="page-header-right">
        <?php if ($pode_criar): ?>
        <a href="<?php echo URL_BASE; ?>/metodos-pagamento/criar" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Novo Método
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['sucesso'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['erro'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Empresa</th>
                        <th>Ordem</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($metodos)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Nenhum método de pagamento cadastrado.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($metodos as $metodo): ?>
                    <tr>
                        <td><?php echo $metodo['id']; ?></td>
                        <td>
                            <?php if (!empty($metodo['icone'])): ?>
                                <i class="<?php echo htmlspecialchars($metodo['icone']); ?> me-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($metodo['nome']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($metodo['descricao'] ?? '-'); ?></td>
                        <td>
                            <?php if ($metodo['empresa_id'] === null): ?>
                                <span class="badge bg-info">Padrão do Sistema</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Empresa <?php echo $metodo['empresa_id']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $metodo['ordem'] ?? 0; ?></td>
                        <td>
                            <?php if ($metodo['ativo']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($pode_criar): ?>
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo URL_BASE; ?>/metodos-pagamento/editar?id=<?php echo $metodo['id']; ?>" 
                                   class="btn btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="excluirMetodo(<?php echo $metodo['id']; ?>, '<?php echo addslashes($metodo['nome']); ?>')"
                                        title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o método <strong id="nomeMetodoExcluir"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formExcluir" method="POST" action="<?php echo URL_BASE; ?>/metodos-pagamento/excluir">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">
                    <input type="hidden" name="id" id="idMetodoExcluir">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function excluirMetodo(id, nome) {
    document.getElementById('nomeMetodoExcluir').textContent = nome;
    document.getElementById('idMetodoExcluir').value = id;
    var modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
    modal.show();
}
</script>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}
.page-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 4px 0 0 0;
}
.card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.table thead th {
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    color: #64748b;
    border-bottom: 2px solid #e2e8f0;
}
.table tbody td {
    vertical-align: middle;
    font-size: 14px;
}
.badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
}
</style>
