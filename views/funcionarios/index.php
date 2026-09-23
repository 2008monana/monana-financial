<?php
/**
 * View: Listagem de Funcionários
 */
$titulo_pagina = 'Funcionários';
include __DIR__ . '/../includes/cabecalho.php';

$usuario = $_SESSION['usuario'] ?? [
    'id'         => $_SESSION['usuario_id']     ?? 0,
    'nome'       => $_SESSION['usuario_nome']   ?? 'Utilizador',
    'email'      => $_SESSION['usuario_email']  ?? '',
    'perfil'     => $_SESSION['usuario_perfil'] ?? 'visualizador',
    'empresa_id' => $_SESSION['empresa_id']     ?? null,
];
$mensagem_sucesso = $_SESSION['sucesso'] ?? null;
$mensagem_erro = $_SESSION['erro'] ?? null;
unset($_SESSION['sucesso'], $_SESSION['erro']);
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-users me-2"></i>Gestão de Funcionários
                        </h5>
                        <?php if (in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])): ?>
                            <a href="<?= URL_BASE ?>/funcionarios/criar" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Novo Funcionário
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if ($mensagem_sucesso): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($mensagem_sucesso) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($mensagem_erro): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($mensagem_erro) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filtros -->
                    <form method="GET" action="<?= URL_BASE ?>/funcionarios" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" name="busca" class="form-control" 
                                   placeholder="Nome, cargo ou email" 
                                   value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filial</label>
                            <select name="filial_id" class="form-select">
                                <option value="">Todas as filiais</option>
                                <?php foreach ($filiais as $filial): ?>
                                    <option value="<?= $filial['id'] ?>" 
                                            <?= (($_GET['filial_id'] ?? '') == $filial['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($filial['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="fas fa-filter me-1"></i>Filtrar
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="<?= URL_BASE ?>/funcionarios" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i>Limpar
                            </a>
                        </div>
                    </form>
                    
                    <!-- Tabela de Funcionários -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Foto</th>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Filial</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th width="120" class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($funcionarios)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>Nenhum funcionário encontrado.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($funcionarios as $funcionario): ?>
                                        <tr>
                                            <td>
                                                <?php if ($funcionario['foto']): ?>
                                                    <img src="/<?= htmlspecialchars($funcionario['foto']) ?>" 
                                                         alt="Foto" 
                                                         class="rounded-circle" 
                                                         width="50" height="50" 
                                                         style="object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($funcionario['nome']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    Admissão: <?= date('d/m/Y', strtotime($funcionario['data_admissao'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($funcionario['cargo'] ?? '-') ?>
                                                <?php if ($funcionario['departamento']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($funcionario['departamento']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($funcionario['filial_nome']) ?></td>
                                            <td><?= htmlspecialchars($funcionario['telefone'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($funcionario['email'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $funcionario['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($funcionario['estado']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (in_array($usuario['perfil'], ['super_admin', 'admin_empresa'])): ?>
                                                        <a href="<?= URL_BASE ?>/funcionarios/editar/<?= $funcionario['id'] ?>" 
                                                           class="btn btn-outline-primary" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="confirmarExclusao(<?= $funcionario['id'] ?>, '<?= htmlspecialchars($funcionario['nome']) ?>')"
                                                                title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="8" class="text-end">
                                        <small class="text-muted">
                                            Total: <strong><?= $total ?></strong> funcionário(s)
                                        </small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExclusao" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o funcionário <strong id="nome_funcionario"></strong>?</p>
                <p class="text-danger mb-0"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<script>
let funcionarioIdParaExcluir = null;

function confirmarExclusao(id, nome) {
    funcionarioIdParaExcluir = id;
    document.getElementById('nome_funcionario').textContent = nome;
    new bootstrap.Modal(document.getElementById('modalExclusao')).show();
}

document.getElementById('btnConfirmarExclusao').addEventListener('click', function() {
    if (funcionarioIdParaExcluir) {
        fetch(`<?= URL_BASE ?>/funcionarios/excluir/${funcionarioIdParaExcluir}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao excluir funcionário.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir funcionário.');
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
