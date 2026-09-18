<?php
/**
 * Model ResumoMensal
 * Gerencia a tabela de resumos mensais (cache de relatórios)
 */

require_once CAMINHO_RAIZ . '/core/Model.php';

class ResumoMensal extends Model
{
    protected string $tabela = 'resumos_mensais';

    /**
     * Buscar resumo de um mês específico
     */
    public function buscarPorMes(int $filialId, int $ano, int $mes): array|false
    {
        $sql = "SELECT * FROM resumos_mensais 
                WHERE filial_id = :filial_id 
                  AND ano = :ano 
                  AND mes = :mes 
                LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes
        ]);
        return $stmt->fetch();
    }

    /**
     * Buscar resumos de um ano inteiro por filial
     */
    public function buscarPorAno(int $filialId, int $ano): array
    {
        $sql = "SELECT * FROM resumos_mensais 
                WHERE filial_id = :filial_id 
                  AND ano = :ano 
                ORDER BY mes ASC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'filial_id' => $filialId,
            'ano' => $ano
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar resumos por empresa (todas as filiais)
     */
    public function buscarPorEmpresa(int $empresaId, int $ano, ?int $mes = null): array
    {
        $sql = "SELECT rm.*, f.nome as filial_nome 
                FROM resumos_mensais rm
                INNER JOIN filiais f ON rm.filial_id = f.id
                WHERE f.empresa_id = :empresa_id 
                  AND rm.ano = :ano";
        $params = ['empresa_id' => $empresaId, 'ano' => $ano];

        if ($mes !== null) {
            $sql .= " AND rm.mes = :mes";
            $params['mes'] = $mes;
        }

        $sql .= " ORDER BY f.nome, rm.mes";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Buscar resumo consolidado por empresa (soma de todas as filiais)
     */
    public function buscarConsolidadoPorEmpresa(int $empresaId, int $ano, int $mes): array|false
    {
        $sql = "SELECT 
                    SUM(total_vendas) as total_vendas,
                    SUM(total_devolucoes) as total_devolucoes,
                    SUM(total_compras) as total_compras,
                    SUM(total_custos) as total_custos,
                    SUM(saldo) as saldo
                FROM resumos_mensais rm
                INNER JOIN filiais f ON rm.filial_id = f.id
                WHERE f.empresa_id = :empresa_id 
                  AND rm.ano = :ano 
                  AND rm.mes = :mes";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'ano' => $ano,
            'mes' => $mes
        ]);
        return $stmt->fetch();
    }

    /**
     * Calcular e atualizar resumo de um mês
     */
    public function calcularEAtualizar(int $filialId, int $ano, int $mes): bool
    {
        // Buscar totais das transações
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor ELSE 0 END), 0) as total_vendas,
                    COALESCE(SUM(CASE WHEN tipo = 'devolucao' THEN valor ELSE 0 END), 0) as total_devolucoes,
                    COALESCE(SUM(CASE WHEN tipo = 'compra' THEN valor ELSE 0 END), 0) as total_compras,
                    COALESCE(SUM(CASE WHEN tipo = 'custo' THEN valor ELSE 0 END), 0) as total_custos
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
        $resultado = $stmt->fetch();

        // Calcular saldo
        $totalVendas = (float) $resultado['total_vendas'];
        $totalDevolucoes = (float) $resultado['total_devolucoes'];
        $totalCompras = (float) $resultado['total_compras'];
        $totalCustos = (float) $resultado['total_custos'];
        $saldo = $totalVendas - ($totalDevolucoes + $totalCompras + $totalCustos);

        // Buscar empresa_id da filial
        $sqlFilial = "SELECT empresa_id FROM filiais WHERE id = :filial_id";
        $stmtFilial = $this->bd->prepare($sqlFilial);
        $stmtFilial->execute(['filial_id' => $filialId]);
        $filial = $stmtFilial->fetch();

        if (!$filial) {
            return false;
        }

        // Inserir ou atualizar resumo
        $sqlInsert = "INSERT INTO resumos_mensais 
                      (empresa_id, filial_id, ano, mes, total_vendas, total_devolucoes, total_compras, total_custos, saldo)
                      VALUES (:empresa_id, :filial_id, :ano, :mes, :total_vendas, :total_devolucoes, :total_compras, :total_custos, :saldo)
                      ON DUPLICATE KEY UPDATE
                      total_vendas = VALUES(total_vendas),
                      total_devolucoes = VALUES(total_devolucoes),
                      total_compras = VALUES(total_compras),
                      total_custos = VALUES(total_custos),
                      saldo = VALUES(saldo),
                      atualizado_em = CURRENT_TIMESTAMP";
        
        $stmtInsert = $this->bd->prepare($sqlInsert);
        return $stmtInsert->execute([
            'empresa_id' => (int) $filial['empresa_id'],
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes,
            'total_vendas' => $totalVendas,
            'total_devolucoes' => $totalDevolucoes,
            'total_compras' => $totalCompras,
            'total_custos' => $totalCustos,
            'saldo' => $saldo
        ]);
    }

    /**
     * Recalcular todos os meses de um ano para uma filial
     */
    public function recalcularAno(int $filialId, int $ano): void
    {
        for ($mes = 1; $mes <= 12; $mes++) {
            $this->calcularEAtualizar($filialId, $ano, $mes);
        }
    }

    /**
     * Recalcular todos os anos para uma filial (migração)
     */
    public function recalcularTudo(int $filialId): void
    {
        $sql = "SELECT DISTINCT YEAR(data_transacao) as ano 
                FROM transacoes 
                WHERE filial_id = :filial_id 
                ORDER BY ano ASC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['filial_id' => $filialId]);
        $anos = $stmt->fetchAll();

        foreach ($anos as $ano) {
            $this->recalcularAno($filialId, (int) $ano['ano']);
        }
    }

    /**
     * Eliminar resumos de um mês específico
     */
    public function eliminarPorMes(int $filialId, int $ano, int $mes): bool
    {
        $sql = "DELETE FROM resumos_mensais 
                WHERE filial_id = :filial_id 
                  AND ano = :ano 
                  AND mes = :mes";
        $stmt = $this->bd->prepare($sql);
        return $stmt->execute([
            'filial_id' => $filialId,
            'ano' => $ano,
            'mes' => $mes
        ]);
    }
}