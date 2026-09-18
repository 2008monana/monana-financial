<?php
/**
 * Model Transacao - COMPLETO
 * Gerencia as transações financeiras com todos os campos da planilha
 */

require_once CAMINHO_RAIZ . '/core/Model.php';

class Transacao extends Model
{
    protected string $tabela = 'transacoes';

    /**
     * Buscar transações com filtros
     */
    public function buscarComFiltros(
        ?int $empresaId,
        ?array $filiaisIds,
        string $dataInicio,
        string $dataFim,
        string $tipo = '',
        int $categoriaId = 0,
        int $filialId = 0,
        string $busca = ''
    ): array {
        $sql = "SELECT t.*, 
                       f.nome as filial_nome, 
                       f.empresa_id,
                       e.nome as empresa_nome,
                       c.nome as categoria_nome,
                       u.nome as usuario_nome
                FROM transacoes t
                LEFT JOIN filiais f ON t.filial_id = f.id
                LEFT JOIN empresas e ON t.empresa_id = e.id
                LEFT JOIN categorias c ON t.categoria_id = c.id
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.data_transacao BETWEEN :data_inicio AND :data_fim";

        $params = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ];

        // Filtrar por empresa
        if ($empresaId !== null && $empresaId > 0) {
            $sql .= " AND t.empresa_id = :empresa_id";
            $params['empresa_id'] = $empresaId;
        }

        // Filtrar por filiais (utilizador interno)
        if (!empty($filiaisIds)) {
            $placeholders = implode(',', array_fill(0, count($filiaisIds), '?'));
            $sql .= " AND t.filial_id IN ({$placeholders})";
            $params = array_merge($params, $filiaisIds);
        }

        // Filtrar por tipo
        if (!empty($tipo)) {
            $sql .= " AND t.tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        // Filtrar por categoria
        if ($categoriaId > 0) {
            $sql .= " AND t.categoria_id = :categoria_id";
            $params['categoria_id'] = $categoriaId;
        }

        // Filtrar por filial específica
        if ($filialId > 0) {
            $sql .= " AND t.filial_id = :filial_id";
            $params['filial_id'] = $filialId;
        }

        // Busca por descrição
        if (!empty($busca)) {
            $sql .= " AND (t.descricao LIKE :busca OR u.nome LIKE :busca OR e.nome LIKE :busca)";
            $params['busca'] = "%{$busca}%";
        }

        $sql .= " ORDER BY t.data_transacao DESC, t.id DESC";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Buscar transações por usuário
     */
    public function porUsuario(int $usuarioId, int $limite = 10): array
    {
        $sql = "SELECT t.*, f.nome as filial_nome, c.nome as categoria_nome
                FROM transacoes t
                LEFT JOIN filiais f ON t.filial_id = f.id
                LEFT JOIN categorias c ON t.categoria_id = c.id
                WHERE t.usuario_id = :usuario_id
                ORDER BY t.data_transacao DESC, t.id DESC
                LIMIT :limite";

        $stmt = $this->bd->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Inserir um fecho diário completo (todos os campos da planilha)
     */
    public function inserirFechoDiario(array $dados): int
    {
        // Calcular total vendas
        $totalVendas = ($dados['tpa_bca'] ?? 0) + ($dados['tpa_keve'] ?? 0) + 
                       ($dados['transferencias'] ?? 0) + ($dados['despesas'] ?? 0) + 
                       ($dados['devolucao'] ?? 0) + ($dados['dinheiro'] ?? 0);
        
        // Calcular saldo final
        $saldoFinal = ($dados['saldo_anterior'] ?? 0) + $totalVendas - 
                      ($dados['gastos_diario'] ?? 0) - ($dados['gastos_extra'] ?? 0) - 
                      ($dados['deposito'] ?? 0) - ($dados['saidas_extra'] ?? 0);

        $dados['total_vendas'] = $totalVendas;
        $dados['saldo_final'] = $saldoFinal;

        return $this->inserir($dados);
    }

    /**
     * Buscar transações de um dia específico com todos os campos
     */
    public function buscarPorDia(int $filialId, string $data): array|false
    {
        $sql = "SELECT * FROM transacoes 
                WHERE filial_id = :filial_id 
                AND DATE(data_transacao) = :data 
                ORDER BY criado_em DESC 
                LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'data' => $data
        ]);
        return $stmt->fetch();
    }

    /**
     * Buscar resumo diário de um mês inteiro (estilo planilha)
     */
    public function buscarResumoMensalPlanilha(int $filialId, int $ano, int $mes): array
    {
        $sql = "SELECT 
                    DAY(data_transacao) as dia,
                    SUM(tpa_bca) as tpa_bca,
                    SUM(tpa_keve) as tpa_keve,
                    SUM(transferencias) as transferencias,
                    SUM(despesas) as despesas,
                    SUM(devolucao) as devolucao,
                    SUM(dinheiro) as dinheiro,
                    SUM(total_vendas) as total_vendas,
                    SUM(deposito) as deposito,
                    SUM(saidas_extra) as saidas_extra,
                    SUM(gastos_diario) as gastos_diario,
                    SUM(gastos_extra) as gastos_extra,
                    SUM(saldo_final) as saldo_final,
                    COUNT(*) as total_registros
                FROM transacoes 
                WHERE filial_id = :filial_id 
                AND YEAR(data_transacao) = :ano 
                AND MONTH(data_transacao) = :mes
                GROUP BY DAY(data_transacao)
                ORDER BY dia ASC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar resumo mensal consolidado (igual à planilha)
     */
    public function buscarResumoMensalConsolidado(int $filialId, int $ano, int $mes): array
    {
        $sql = "SELECT 
                    COALESCE(SUM(tpa_bca), 0) as total_tpa_bca,
                    COALESCE(SUM(tpa_keve), 0) as total_tpa_keve,
                    COALESCE(SUM(transferencias), 0) as total_transferencias,
                    COALESCE(SUM(despesas), 0) as total_despesas,
                    COALESCE(SUM(devolucao), 0) as total_devolucao,
                    COALESCE(SUM(dinheiro), 0) as total_dinheiro,
                    COALESCE(SUM(total_vendas), 0) as total_vendas,
                    COALESCE(SUM(deposito), 0) as total_deposito,
                    COALESCE(SUM(saidas_extra), 0) as total_saidas_extra,
                    COALESCE(SUM(gastos_diario), 0) as total_gastos_diario,
                    COALESCE(SUM(gastos_extra), 0) as total_gastos_extra,
                    COALESCE(SUM(saldo_final), 0) as saldo_final,
                    COUNT(*) as total_registros,
                    COUNT(DISTINCT DAY(data_transacao)) as dias_com_movimento
                FROM transacoes 
                WHERE filial_id = :filial_id 
                AND YEAR(data_transacao) = :ano 
                AND MONTH(data_transacao) = :mes";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes
        ]);
        return $stmt->fetch();
    }

    /**
     * Buscar resumo por filial (para relatório consolidado)
     */
    public function buscarResumoPorFilial(int $empresaId, int $ano, int $mes): array
    {
        $sql = "SELECT 
                    f.id as filial_id,
                    f.nome as filial_nome,
                    COALESCE(SUM(t.tpa_bca), 0) as tpa_bca,
                    COALESCE(SUM(t.tpa_keve), 0) as tpa_keve,
                    COALESCE(SUM(t.transferencias), 0) as transferencias,
                    COALESCE(SUM(t.despesas), 0) as despesas,
                    COALESCE(SUM(t.devolucao), 0) as devolucao,
                    COALESCE(SUM(t.dinheiro), 0) as dinheiro,
                    COALESCE(SUM(t.total_vendas), 0) as total_vendas,
                    COALESCE(SUM(t.deposito), 0) as deposito,
                    COALESCE(SUM(t.saidas_extra), 0) as saidas_extra,
                    COALESCE(SUM(t.gastos_diario), 0) as gastos_diario,
                    COALESCE(SUM(t.gastos_extra), 0) as gastos_extra,
                    COALESCE(SUM(t.saldo_final), 0) as saldo_final,
                    COUNT(*) as total_registros
                FROM filiais f
                LEFT JOIN transacoes t ON t.filial_id = f.id 
                    AND YEAR(t.data_transacao) = :ano 
                    AND MONTH(t.data_transacao) = :mes
                WHERE f.empresa_id = :empresa_id
                GROUP BY f.id, f.nome
                ORDER BY f.nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'ano' => $ano,
            'mes' => $mes
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Atualizar fecho diário
     */
    public function atualizarFechoDiario(int $id, array $dados): bool
    {
        // Recalcular total vendas e saldo final
        $totalVendas = ($dados['tpa_bca'] ?? 0) + ($dados['tpa_keve'] ?? 0) + 
                       ($dados['transferencias'] ?? 0) + ($dados['despesas'] ?? 0) + 
                       ($dados['devolucao'] ?? 0) + ($dados['dinheiro'] ?? 0);
        
        $saldoFinal = ($dados['saldo_anterior'] ?? 0) + $totalVendas - 
                      ($dados['gastos_diario'] ?? 0) - ($dados['gastos_extra'] ?? 0) - 
                      ($dados['deposito'] ?? 0) - ($dados['saidas_extra'] ?? 0);

        $dados['total_vendas'] = $totalVendas;
        $dados['saldo_final'] = $saldoFinal;

        return $this->atualizar($id, $dados);
    }

    /**
     * Buscar um registo por ID com todos os detalhes
     */
    public function encontrarPorId(int $id): array|false
    {
        $sql = "SELECT t.*, 
                       f.nome as filial_nome, 
                       e.nome as empresa_nome,
                       c.nome as categoria_nome,
                       u.nome as usuario_nome
                FROM transacoes t
                LEFT JOIN filiais f ON t.filial_id = f.id
                LEFT JOIN empresas e ON t.empresa_id = e.id
                LEFT JOIN categorias c ON t.categoria_id = c.id
                LEFT JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.id = :id
                LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Buscar o saldo final do dia anterior (para o fecho diário)
     */
    public function buscarSaldoAnterior(int $filialId, string $data): float
    {
        if ($filialId <= 0) {
            return 0;
        }

        $dataObj = new DateTime($data);
        $dataObj->modify('first day of this month');
        $dataObj->modify('-1 day');
        
        $dataAnterior = $dataObj->format('Y-m-d');
        
        $sql = "SELECT saldo_final FROM transacoes 
                WHERE filial_id = :filial_id 
                AND data_transacao = :data 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'data' => $dataAnterior
        ]);
        $resultado = $stmt->fetch();
        
        return $resultado ? (float) $resultado['saldo_final'] : 0;
    }

    /**
     * Obter a ligação PDO (para consultas personalizadas)
     */
    public function getBd(): PDO
    {
        return $this->bd;
    }
}