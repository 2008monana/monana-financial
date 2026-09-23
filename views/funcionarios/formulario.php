<?php
/**
 * View: Formulário de Funcionário (Criar/Editar)
 */
$titulo_pagina = $funcionario ? 'Editar Funcionário' : 'Novo Funcionário';
include __DIR__ . '/../includes/cabecalho.php';

$usuario = $_SESSION['usuario'] ?? [
    'id'         => $_SESSION['usuario_id']     ?? 0,
    'nome'       => $_SESSION['usuario_nome']   ?? 'Utilizador',
    'email'      => $_SESSION['usuario_email']  ?? '',
    'perfil'     => $_SESSION['usuario_perfil'] ?? 'visualizador',
    'empresa_id' => $_SESSION['empresa_id']     ?? null,
];
$erros = $_SESSION['erros'] ?? [];
$dados_form = $_SESSION['dados_form'] ?? [];
unset($_SESSION['erros'], $_SESSION['dados_form']);

// Preencher dados no modo de edição
if ($funcionario) {
    $dados_form = array_merge($funcionario, $dados_form);
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-user-edit me-2"></i><?= $titulo_pagina ?>
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="<?= $funcionario ? URL_BASE . "/funcionarios/atualizar/{$funcionario['id']}" : URL_BASE . '/funcionarios/salvar' ?>" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          class="row g-3">
                        
                        <!-- Dados Pessoais -->
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary">
                                <i class="fas fa-user me-2"></i>Dados Pessoais
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="nome" class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" id="nome" class="form-control <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($dados_form['nome'] ?? '') ?>" required>
                            <?php if (isset($erros['nome'])): ?>
                                <div class="invalid-feedback"><?= $erros['nome'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['data_nascimento'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="genero" class="form-label">Gênero</label>
                            <select name="genero" id="genero" class="form-select">
                                <option value="M" <?= ($dados_form['genero'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                                <option value="F" <?= ($dados_form['genero'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino</option>
                                <option value="O" <?= ($dados_form['genero'] ?? '') === 'O' ? 'selected' : '' ?>>Outro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="nacionalidade" class="form-label">Nacionalidade</label>
                            <input type="text" name="nacionalidade" id="nacionalidade" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['nacionalidade'] ?? 'Angolana') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="bi_passaporte" class="form-label">BI/Passaporte</label>
                            <input type="text" name="bi_passaporte" id="bi_passaporte" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['bi_passaporte'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
                            <?php if (isset($erros['foto'])): ?>
                                <div class="invalid-feedback d-block"><?= $erros['foto'] ?></div>
                            <?php endif; ?>
                            <?php if ($funcionario && !empty($funcionario['foto'])): ?>
                                <div class="mt-2">
                                    <img src="/<?= htmlspecialchars($funcionario['foto']) ?>" 
                                         alt="Foto atual" 
                                         class="rounded-circle" 
                                         width="80" height="80" 
                                         style="object-fit: cover;">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="endereco" class="form-label">Endereço</label>
                            <textarea name="endereco" id="endereco" class="form-control" rows="2"><?= htmlspecialchars($dados_form['endereco'] ?? '') ?></textarea>
                        </div>
                        
                        <!-- Dados de Contacto -->
                        <div class="col-md-12 mt-4">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary">
                                <i class="fas fa-phone me-2"></i>Dados de Contacto
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="telefone" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['telefone'] ?? '') ?>" placeholder="+244 9XX XXX XXX">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" name="email" id="email" class="form-control <?= isset($erros['email']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($dados_form['email'] ?? '') ?>">
                            <?php if (isset($erros['email'])): ?>
                                <div class="invalid-feedback"><?= $erros['email'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Dados Profissionais -->
                        <div class="col-md-12 mt-4">
                            <h6 class="border-bottom pb-2 mb-3 text-secondary">
                                <i class="fas fa-briefcase me-2"></i>Dados Profissionais
                            </h6>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="filial_id" class="form-label">Filial *</label>
                            <select name="filial_id" id="filial_id" class="form-select <?= isset($erros['filial_id']) ? 'is-invalid' : '' ?>" required>
                                <option value="">Selecione uma filial</option>
                                <?php foreach ($filiais as $filial): ?>
                                    <option value="<?= $filial['id'] ?>" 
                                            <?= ($dados_form['filial_id'] ?? '') == $filial['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($filial['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($erros['filial_id'])): ?>
                                <div class="invalid-feedback"><?= $erros['filial_id'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="cargo" class="form-label">Cargo</label>
                            <input type="text" name="cargo" id="cargo" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['cargo'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="departamento" class="form-label">Departamento</label>
                            <input type="text" name="departamento" id="departamento" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['departamento'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="data_admissao" class="form-label">Data de Admissão</label>
                            <input type="date" name="data_admissao" id="data_admissao" class="form-control" 
                                   value="<?= htmlspecialchars($dados_form['data_admissao'] ?? date('Y-m-d')) ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="salario_base" class="form-label">Salário Base</label>
                            <div class="input-group">
                                <span class="input-group-text">Kz</span>
                                <input type="number" name="salario_base" id="salario_base" class="form-control" 
                                       value="<?= htmlspecialchars($dados_form['salario_base'] ?? '0') ?>" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select">
                                <option value="activo" <?= ($dados_form['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="inactivo" <?= ($dados_form['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                <option value="suspenso" <?= ($dados_form['estado'] ?? '') === 'suspenso' ? 'selected' : '' ?>>Suspenso</option>
                            </select>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="col-md-12 mt-4 pt-3 border-top">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i><?= $funcionario ? 'Atualizar' : 'Cadastrar' ?>
                                </button>
                                <a href="<?= URL_BASE ?>/funcionarios" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
