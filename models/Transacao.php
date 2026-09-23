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
     * Movimentos agregados por dia e categoria para a planilha dinâmica.
     * A categoria define se o lançamento é uma entrada ou uma saída.
     */
    public function buscarPlanilhaPorCategoria(int $filialId, string $inicio, string $fim): array
    {
        $sql = "SELECT
                    DAY(t.data_transacao) AS dia,
                    c.id AS categoria_id,
                    c.nome AS categoria_nome,
                    c.tipo AS categoria_tipo,
                    SUM(t.valor) AS valor
                FROM transacoes t
                INNER JOIN categorias c ON c.id = t.categoria_id
                WHERE t.filial_id = :filial_id
                  AND t.data_transacao BETWEEN :inicio AND :fim
                GROUP BY DAY(t.data_transacao), c.id, c.nome, c.tipo
                ORDER BY dia, c.tipo, c.nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['filial_id' => $filialId, 'inicio' => $inicio, 'fim' => $fim]);
        return $stmt->fetchAll();
    }

    /** Saldo acumulado antes do período, usando as categorias de entrada e saída. */
    public function buscarSaldoAnteriorPlanilha(int $filialId, string $inicio): float
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN c.tipo = 'entrada' THEN t.valor ELSE -t.valor END), 0)
                FROM transacoes t
                INNER JOIN categorias c ON c.id = t.categoria_id
                WHERE t.filial_id = :filial_id AND t.data_transacao < :inicio";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['filial_id' => $filialId, 'inicio' => $inicio]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Buscar resumo diário de um mês inteiro (estilo planilha).
     *
     * A tabela transacoes armazena movimentos normalizados (tipo, valor e
     * método de pagamento), e não as antigas colunas da planilha. As colunas
     * apresentadas abaixo são, por isso, calculadas sem depender de campos
     * legados como tpa_bca ou saldo_final.
     */
    public function buscarResumoMensalPlanilha(int $filialId, int $ano, int $mes): array
    {
        $sql = "SELECT
                    DAY(data_transacao) AS dia,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'tpa' THEN valor ELSE 0 END), 0) AS tpa_bca,
                    0 AS tpa_keve,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'transferencia' THEN valor ELSE 0 END), 0) AS transferencias,
                    COALESCE(SUM(CASE WHEN tipo IN ('compra', 'custo') THEN valor ELSE 0 END), 0) AS despesas,
                    COALESCE(SUM(CASE WHEN tipo = 'devolucao' THEN valor ELSE 0 END), 0) AS devolucao,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'numerario' THEN valor ELSE 0 END), 0) AS dinheiro,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor ELSE 0 END), 0) AS total_vendas,
                    0 AS deposito,
                    0 AS saidas_extra,
                    0 AS gastos_diario,
                    0 AS gastos_extra,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor WHEN tipo IN ('compra', 'custo', 'devolucao') THEN -valor ELSE 0 END), 0) AS saldo_final,
                    COUNT(*) AS total_registros
                FROM transacoes
                WHERE filial_id = :filial_id
                  AND YEAR(data_transacao) = :ano
                  AND MONTH(data_transacao) = :mes
                GROUP BY DAY(data_transacao)
                ORDER BY dia ASC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['filial_id' => $filialId, 'ano' => $ano, 'mes' => $mes]);
        return $stmt->fetchAll();
    }

    /** Buscar o consolidado mensal usando os campos efetivamente existentes em transacoes. */
    public function buscarResumoMensalConsolidado(int $filialId, int $ano, int $mes): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'tpa' THEN valor ELSE 0 END), 0) AS total_tpa_bca,
                    0 AS total_tpa_keve,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'transferencia' THEN valor ELSE 0 END), 0) AS total_transferencias,
                    COALESCE(SUM(CASE WHEN tipo IN ('compra', 'custo') THEN valor ELSE 0 END), 0) AS total_despesas,
                    COALESCE(SUM(CASE WHEN tipo = 'devolucao' THEN valor ELSE 0 END), 0) AS total_devolucao,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' AND metodo_pagamento = 'numerario' THEN valor ELSE 0 END), 0) AS total_dinheiro,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor ELSE 0 END), 0) AS total_vendas,
                    0 AS total_deposito,
                    0 AS total_saidas_extra,
                    0 AS total_gastos_diario,
                    0 AS total_gastos_extra,
                    COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor WHEN tipo IN ('compra', 'custo', 'devolucao') THEN -valor ELSE 0 END), 0) AS saldo_final,
                    COUNT(*) AS total_registros,
                    COUNT(DISTINCT DAY(data_transacao)) AS dias_com_movimento
                FROM transacoes
                WHERE filial_id = :filial_id
                  AND YEAR(data_transacao) = :ano
                  AND MONTH(data_transacao) = :mes";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['filial_id' => $filialId, 'ano' => $ano, 'mes' => $mes]);
        return $stmt->fetch();
    }

    /** Buscar resumo por filial para o relatório consolidado. */
    public function buscarResumoPorFilial(int $empresaId, int $ano, int $mes): array
    {
        $sql = "SELECT
                    f.id AS filial_id,
                    f.nome AS filial_nome,
                    COALESCE(SUM(CASE WHEN t.tipo = 'venda' AND t.metodo_pagamento = 'tpa' THEN t.valor ELSE 0 END), 0) AS tpa_bca,
                    0 AS tpa_keve,
                    COALESCE(SUM(CASE WHEN t.tipo = 'venda' AND t.metodo_pagamento = 'transferencia' THEN t.valor ELSE 0 END), 0) AS transferencias,
                    COALESCE(SUM(CASE WHEN t.tipo IN ('compra', 'custo') THEN t.valor ELSE 0 END), 0) AS despesas,
                    COALESCE(SUM(CASE WHEN t.tipo = 'devolucao' THEN t.valor ELSE 0 END), 0) AS devolucao,
                    COALESCE(SUM(CASE WHEN t.tipo = 'venda' AND t.metodo_pagamento = 'numerario' THEN t.valor ELSE 0 END), 0) AS dinheiro,
                    COALESCE(SUM(CASE WHEN t.tipo = 'venda' THEN t.valor ELSE 0 END), 0) AS total_vendas,
                    0 AS deposito, 0 AS saidas_extra, 0 AS gastos_diario, 0 AS gastos_extra,
                    COALESCE(SUM(CASE WHEN t.tipo = 'venda' THEN t.valor WHEN t.tipo IN ('compra', 'custo', 'devolucao') THEN -t.valor ELSE 0 END), 0) AS saldo_final,
                    COUNT(t.id) AS total_registros
                FROM filiais f
                LEFT JOIN transacoes t ON t.filial_id = f.id
                    AND YEAR(t.data_transacao) = :ano
                    AND MONTH(t.data_transacao) = :mes
                WHERE f.empresa_id = :empresa_id
                GROUP BY f.id, f.nome
                ORDER BY f.nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'ano' => $ano, 'mes' => $mes]);
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
     * Nota: Calcula o saldo acumulado até o dia anterior baseado nas transações existentes
     */
    public function buscarSaldoAnterior(int $filialId, string $data): float
    {
        if ($filialId <= 0) {
            return 0;
        }

        $dataObj = new DateTime($data);
        $dataObj->modify('-1 day');
        
        $dataAnterior = $dataObj->format('Y-m-d');
        
        // Calcular saldo acumulado até o dia anterior usando categorias
        $sql = "SELECT COALESCE(SUM(CASE WHEN c.tipo = 'entrada' THEN t.valor ELSE -t.valor END), 0)
                FROM transacoes t
                INNER JOIN categorias c ON c.id = t.categoria_id
                WHERE t.filial_id = :filial_id 
                AND t.data_transacao <= :data";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'data' => $dataAnterior
        ]);
        $resultado = $stmt->fetchColumn();
        
        return $resultado ? (float) $resultado : 0;
    }

    /**
     * Obter a ligação PDO (para consultas personalizadas)
     */
    public function getBd(): PDO
    {
        return $this->bd;
    }
}