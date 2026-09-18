<?php
/**
 * Fecho Diário - Formulário estilo planilha Excel
 */
?>

<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">
            <i class="fas fa-file-invoice-day"></i> Fecho Diário
        </h1>
        <p class="page-subtitle">
            Registe o fecho financeiro do dia, igual à planilha
        </p>
    </div>
    <div class="page-header-right">
        <a href="<?php echo URL_BASE; ?>/transacoes/index" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="fecho-container">
    <!-- Filtro de Data e Filial -->
    <div class="fecho-filtros">
        <form method="GET" action="<?php echo URL_BASE; ?>/transacoes/fechoDiario" class="filtro-form">
            <div class="filter-group">
                <label><i class="fas fa-calendar"></i> Data</label>
                <input type="date" name="data" value="<?php echo $data; ?>" onchange="this.form.submit()">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-store"></i> Filial</label>
                <select name="filial_id" onchange="this.form.submit()" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($filiais as $filial): ?>
                    <option value="<?php echo $filial['id']; ?>" <?php echo $filialId == $filial['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($filial['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <a href="<?php echo URL_BASE; ?>/transacoes/fechoDiario" class="btn btn-outline btn-sm">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <?php if ($filialId > 0): ?>
    <!-- Formulário do Fecho -->
    <div class="fecho-card">
        <div class="fecho-header">
            <div class="fecho-header-left">
                <h3>
                    <i class="fas fa-calendar-day"></i> 
                    Fecho de <?php echo date('d/m/Y', strtotime($data)); ?>
                    <span class="filial-badge">
                        <i class="fas fa-store"></i> 
                        <?php 
                            $filialAtual = array_filter($filiais, fn($f) => $f['id'] == $filialId);
                            $filialAtual = reset($filialAtual);
                            echo htmlspecialchars($filialAtual['nome'] ?? '');
                        ?>
                    </span>
                </h3>
                <?php if ($fecho): ?>
                    <span class="status-badge sucesso">
                        <i class="fas fa-check-circle"></i> Fecho registado
                    </span>
                <?php else: ?>
                    <span class="status-badge pendente">
                        <i class="fas fa-clock"></i> Pendente
                    </span>
                <?php endif; ?>
            </div>
            <div class="fecho-header-right">
                <span class="info-tag">
                    <i class="fas fa-arrow-right"></i> Saldo Anterior: 
                    <strong><?php echo number_format($saldoAnterior, 0, ',', '.'); ?> Kz</strong>
                </span>
            </div>
        </div>

        <form method="POST" action="<?php echo URL_BASE; ?>/transacoes/salvarFecho">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="filial_id" value="<?php echo $filialId; ?>">
            <input type="hidden" name="data_transacao" value="<?php echo $data; ?>">

            <div class="fecho-grid">
                <!-- Coluna 1: Entradas (Vendas) -->
                <div class="fecho-coluna">
                    <div class="coluna-header entrada">
                        <i class="fas fa-arrow-right"></i> ENTRADAS
                    </div>
                    
                    <div class="campo-row">
                        <label>TPA - BCA</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="tpa_bca" class="valor-input" 
                                   value="<?php echo number_format($fecho['tpa_bca'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>TPA - Keve</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="tpa_keve" class="valor-input" 
                                   value="<?php echo number_format($fecho['tpa_keve'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Transferências</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="transferencias" class="valor-input" 
                                   value="<?php echo number_format($fecho['transferencias'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Despesas</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="despesas" class="valor-input" 
                                   value="<?php echo number_format($fecho['despesas'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Devolução</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="devolucao" class="valor-input" 
                                   value="<?php echo number_format($fecho['devolucao'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row destaque">
                        <label>Dinheiro</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="dinheiro" class="valor-input" 
                                   value="<?php echo number_format($fecho['dinheiro'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row total">
                        <label><strong>Total Vendas</strong></label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" id="total_vendas" class="valor-input total-input" 
                                   value="<?php echo number_format($fecho['total_vendas'] ?? 0, 2, ',', '.'); ?>"
                                   readonly>
                        </div>
                    </div>
                </div>

                <!-- Coluna 2: Saídas e Saldo -->
                <div class="fecho-coluna">
                    <div class="coluna-header saida">
                        <i class="fas fa-arrow-left"></i> SAÍDAS
                    </div>

                    <div class="campo-row">
                        <label>Depósito</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="deposito" class="valor-input" 
                                   value="<?php echo number_format($fecho['deposito'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Saídas Extra</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="saidas_extra" class="valor-input" 
                                   value="<?php echo number_format($fecho['saidas_extra'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Gastos Diário</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="gastos_diario" class="valor-input" 
                                   value="<?php echo number_format($fecho['gastos_diario'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row">
                        <label>Gastos Extra</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="gastos_extra" class="valor-input" 
                                   value="<?php echo number_format($fecho['gastos_extra'] ?? 0, 2, ',', '.'); ?>"
                                   placeholder="0,00">
                        </div>
                    </div>

                    <div class="campo-row total">
                        <label><strong>Saldo Final</strong></label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" id="saldo_final" class="valor-input total-input saldo-final" 
                                   value="<?php echo number_format($fecho['saldo_final'] ?? 0, 2, ',', '.'); ?>"
                                   readonly>
                        </div>
                    </div>

                    <div class="campo-row info-row">
                        <label>Saldo Anterior</label>
                        <div class="input-wrap">
                            <span class="prefix">Kz</span>
                            <input type="text" name="saldo_anterior" class="valor-input" 
                                   value="<?php echo number_format($saldoAnterior, 2, ',', '.'); ?>"
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="fecho-obs">
                <label><i class="fas fa-comment"></i> Observações</label>
                <textarea name="descricao" rows="2" placeholder="Observações do fecho..."><?php echo htmlspecialchars($fecho['descricao'] ?? 'Fecho diário - ' . date('d/m/Y', strtotime($data))); ?></textarea>
            </div>

            <!-- Ações -->
            <div class="fecho-acoes">
                <button type="submit" class="btn btn-primary">
                    <i class="fas <?php echo $fecho ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $fecho ? 'Atualizar Fecho' : 'Registar Fecho'; ?>
                </button>
                <a href="<?php echo URL_BASE; ?>/relatorios/diario-planilha?filial_id=<?php echo $filialId; ?>&data=<?php echo $data; ?>" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-file-alt"></i> Ver Relatório
                </a>
            </div>
        </form>
    </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-file-invoice"></i></div>
            <h3>Selecione uma filial</h3>
            <p>Escolha uma filial e uma data para começar o fecho diário.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Máscara de moeda e cálculo automático
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.valor-input:not([readonly])');
    
    // Formatar valor ao digitar
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^0-9,]/g, '');
            if (value) {
                let parts = value.split(',');
                let integer = parts[0];
                let decimal = parts[1] || '';
                integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                if (decimal) {
                    this.value = integer + ',' + decimal.substring(0, 2);
                } else {
                    this.value = integer;
                }
            }
            calcularTotais();
        });
    });

    // Calcular totais automaticamente
    function calcularTotais() {
        // Ler valores
        const tpa_bca = parseFloat(document.querySelector('input[name="tpa_bca"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const tpa_keve = parseFloat(document.querySelector('input[name="tpa_keve"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const transferencias = parseFloat(document.querySelector('input[name="transferencias"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const despesas = parseFloat(document.querySelector('input[name="despesas"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const devolucao = parseFloat(document.querySelector('input[name="devolucao"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const dinheiro = parseFloat(document.querySelector('input[name="dinheiro"]').value.replace(/\./g, '').replace(',', '.') || 0);
        
        const deposito = parseFloat(document.querySelector('input[name="deposito"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const saidas_extra = parseFloat(document.querySelector('input[name="saidas_extra"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const gastos_diario = parseFloat(document.querySelector('input[name="gastos_diario"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const gastos_extra = parseFloat(document.querySelector('input[name="gastos_extra"]').value.replace(/\./g, '').replace(',', '.') || 0);
        const saldo_anterior = parseFloat(document.querySelector('input[name="saldo_anterior"]').value.replace(/\./g, '').replace(',', '.') || 0);

        // Calcular total vendas
        const totalVendas = tpa_bca + tpa_keve + transferencias + despesas + devolucao + dinheiro;
        
        // Calcular saldo final
        const saldoFinal = saldo_anterior + totalVendas - deposito - saidas_extra - gastos_diario - gastos_extra;

        // Atualizar campos
        document.getElementById('total_vendas').value = formatarMoeda(totalVendas);
        document.getElementById('saldo_final').value = formatarMoeda(saldoFinal);

        // Cor do saldo final
        const saldoInput = document.getElementById('saldo_final');
        if (saldoFinal >= 0) {
            saldoInput.style.color = '#16a34a';
        } else {
            saldoInput.style.color = '#dc2626';
        }
    }

    function formatarMoeda(valor) {
        return valor.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Calcular ao carregar a página
    setTimeout(calcularTotais, 100);
});
</script>

<style>
/* ============================================
   FECHO DIÁRIO - ESTILOS COMPLETOS
   ============================================ */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
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
}

.page-header-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.fecho-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Filtros */
.fecho-filtros {
    background: var(--white);
    border-radius: var(--radius);
    padding: 16px 20px;
    border: 1px solid var(--border);
    margin-bottom: 20px;
}

.filtro-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filter-group label {
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filter-group input,
.filter-group select {
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    color: var(--ink);
    background: var(--white);
    min-width: 140px;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

/* Card do Fecho */
.fecho-card {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.fecho-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #fafbfc;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 12px;
}

.fecho-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.fecho-header-left h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.fecho-header-left h3 i {
    color: var(--green);
    margin-right: 8px;
}

.filial-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 12px;
    background: rgba(14, 39, 72, 0.08);
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: var(--navy);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.sucesso {
    background: #dcfce7;
    color: var(--green-dark);
}

.status-badge.pendente {
    background: #fef3c7;
    color: var(--orange-dark);
}

.status-badge i {
    font-size: 12px;
}

.info-tag {
    font-size: 13px;
    color: var(--muted);
}

.info-tag strong {
    color: var(--ink);
}

/* Grid do Fecho */
.fecho-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: 20px;
}

.fecho-coluna {
    padding: 0 20px;
}

.fecho-coluna:first-child {
    border-right: 1px solid var(--border);
}

.coluna-header {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 16px;
    text-align: center;
}

.coluna-header.entrada {
    background: #dcfce7;
    color: var(--green-dark);
}

.coluna-header.saida {
    background: #fee2e2;
    color: var(--red-dark);
}

.coluna-header i {
    margin-right: 6px;
}

.campo-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 0;
    border-bottom: 1px solid #f1f5f9;
}

.campo-row:last-child {
    border-bottom: none;
}

.campo-row label {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink);
    min-width: 100px;
    flex-shrink: 0;
}

.campo-row.destaque label {
    font-weight: 600;
    color: var(--blue);
}

.campo-row.total label {
    font-weight: 700;
    color: var(--navy);
    font-size: 14px;
}

.campo-row.info-row label {
    font-weight: 400;
    color: var(--muted);
    font-size: 12px;
}

.input-wrap {
    display: flex;
    align-items: center;
    flex: 1;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    background: var(--white);
    transition: all 0.3s ease;
}

.input-wrap:focus-within {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

.input-wrap .prefix {
    padding: 8px 12px;
    background: var(--bg);
    color: var(--muted);
    font-weight: 600;
    font-size: 13px;
    border-right: 1px solid var(--border);
    flex-shrink: 0;
}

.input-wrap input {
    border: none !important;
    border-radius: 0 !important;
    padding: 8px 12px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: var(--ink);
    background: var(--white);
    flex: 1;
    min-width: 0;
}

.input-wrap input:focus {
    box-shadow: none !important;
    outline: none !important;
}

.input-wrap input.total-input {
    font-weight: 700;
    font-size: 15px;
    background: #f8fafc;
}

.input-wrap input.saldo-final {
    font-size: 16px;
}

/* Observações */
.fecho-obs {
    padding: 0 20px 16px;
}

.fecho-obs label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 6px;
}

.fecho-obs label i {
    color: var(--muted);
    margin-right: 4px;
}

.fecho-obs textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    color: var(--ink);
    background: var(--white);
    resize: vertical;
    min-height: 60px;
}

.fecho-obs textarea:focus {
    outline: none;
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

/* Ações */
.fecho-acoes {
    display: flex;
    gap: 12px;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    background: #fafbfc;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

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
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
}

/* Empty State */
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

/* Responsivo */
@media (max-width: 768px) {
    .fecho-grid {
        grid-template-columns: 1fr;
        padding: 12px;
    }
    
    .fecho-coluna:first-child {
        border-right: none;
        border-bottom: 1px solid var(--border);
        padding-bottom: 16px;
        margin-bottom: 16px;
    }
    
    .fecho-coluna {
        padding: 0;
    }
    
    .campo-row {
        flex-wrap: wrap;
        gap: 4px 8px;
        padding: 4px 0;
    }
    
    .campo-row label {
        min-width: 80px;
        font-size: 12px;
    }
    
    .input-wrap {
        flex: 1;
        min-width: 120px;
    }
    
    .input-wrap .prefix {
        padding: 6px 8px;
        font-size: 12px;
    }
    
    .input-wrap input {
        padding: 6px 8px;
        font-size: 13px;
    }
    
    .fecho-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .fecho-acoes {
        flex-direction: column;
    }
    
    .fecho-acoes .btn {
        justify-content: center;
        width: 100%;
    }
    
    .filtro-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-group input,
    .filter-group select {
        min-width: auto;
        width: 100%;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 20px;
    }
    
    .fecho-header-left h3 {
        font-size: 14px;
    }
    
    .campo-row label {
        min-width: 60px;
        font-size: 11px;
    }
    
    .input-wrap input {
        font-size: 12px;
    }
    
    .coluna-header {
        font-size: 11px;
        padding: 6px 10px;
    }
    
    .info-tag {
        font-size: 12px;
    }
}
</style>