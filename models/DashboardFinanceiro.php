<?php
/**
 * Consultas de leitura usadas pelo dashboard. Os valores são sempre
 * calculados a partir das transações persistidas, nunca de arrays de exemplo.
 */
require_once CAMINHO_RAIZ . '/core/Model.php';

class DashboardFinanceiro extends Model
{
    protected string $tabela = 'transacoes';

    public function totais(string $inicio, string $fim, ?int $empresaId = null, ?int $filialId = null): array
    {
        $sql = "SELECT COALESCE(SUM(CASE WHEN tipo = 'venda' THEN valor ELSE 0 END), 0) vendas,
                       COALESCE(SUM(CASE WHEN tipo = 'compra' THEN valor ELSE 0 END), 0) compras,
                       COALESCE(SUM(CASE WHEN tipo = 'custo' THEN valor ELSE 0 END), 0) custos,
                       COALESCE(SUM(CASE WHEN tipo = 'devolucao' THEN valor ELSE 0 END), 0) devolucoes,
                       COUNT(*) total
                FROM transacoes WHERE data_transacao BETWEEN :inicio AND :fim";
        $params = ['inicio' => $inicio, 'fim' => $fim];
        if ($empresaId) { $sql .= ' AND empresa_id = :empresa_id'; $params['empresa_id'] = $empresaId; }
        if ($filialId) { $sql .= ' AND filial_id = :filial_id'; $params['filial_id'] = $filialId; }
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: [];
    }

    public function porEmpresa(string $inicio, string $fim): array
    {
        $dados = $this->agrupar('e.id, e.nome', 'e.nome', 'empresas e', 't.empresa_id = e.id', $inicio, $fim);
        foreach ($dados as &$empresa) {
            $empresa['filiais'] = $this->contar('filiais', (int) $empresa['id_empresa']);
            $empresa['usuarios'] = $this->contar('usuarios', (int) $empresa['id_empresa']);
        }
        return $dados;
    }

    public function porFilial(int $empresaId, string $inicio, string $fim): array
    {
        $dados = $this->agrupar('f.id, f.nome', 'f.nome', 'filiais f', 't.filial_id = f.id', $inicio, $fim, $empresaId);
        foreach ($dados as &$filial) {
            $filial['usuarios'] = $this->contarUsuariosFilial((int) $filial['id_filial']);
        }
        return $dados;
    }

    public function porCategoria(int $empresaId, string $inicio, string $fim, array $tipos): array
    {
        $ph = implode(',', array_fill(0, count($tipos), '?'));
        $sql = "SELECT c.nome, SUM(t.valor) valor FROM transacoes t INNER JOIN categorias c ON c.id = t.categoria_id
                WHERE t.empresa_id = ? AND t.data_transacao BETWEEN ? AND ? AND t.tipo IN ($ph)
                GROUP BY c.id, c.nome ORDER BY valor DESC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(array_merge([$empresaId, $inicio, $fim], $tipos));
        return $stmt->fetchAll();
    }

    public function recentes(?int $empresaId, int $limite = 5): array
    {
        $sql = 'SELECT t.tipo, t.descricao, t.valor, t.data_transacao, f.nome filial, e.nome empresa, u.nome usuario
                FROM transacoes t LEFT JOIN filiais f ON f.id = t.filial_id LEFT JOIN empresas e ON e.id = t.empresa_id
                LEFT JOIN usuarios u ON u.id = t.usuario_id';
        $params = [];
        if ($empresaId) { $sql .= ' WHERE t.empresa_id = :empresa_id'; $params['empresa_id'] = $empresaId; }
        $sql .= ' ORDER BY t.data_transacao DESC, t.id DESC LIMIT ' . (int) $limite;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        foreach ($dados as &$dado) {
            $dado['data'] = date('d/m', strtotime($dado['data_transacao']));
            if ($dado['tipo'] !== 'venda') {
                $dado['valor'] = -(float) $dado['valor'];
            }
        }
        return $dados;
    }

    private function agrupar(string $grupo, string $nome, string $tabela, string $juncao, string $inicio, string $fim, ?int $empresaId = null): array
    {
        $id = str_starts_with($tabela, 'empresas') ? 'e.id id_empresa' : 'f.id id_filial';
        $sql = "SELECT $id, $nome nome, COUNT(t.id) total, COALESCE(SUM(CASE WHEN t.tipo = 'venda' THEN t.valor ELSE 0 END), 0) vendas,
                       COALESCE(SUM(CASE WHEN t.tipo IN ('compra', 'custo', 'devolucao') THEN t.valor ELSE 0 END), 0) saidas
                FROM $tabela LEFT JOIN transacoes t ON $juncao AND t.data_transacao BETWEEN :inicio AND :fim";
        $params = ['inicio' => $inicio, 'fim' => $fim];
        if ($empresaId) { $sql .= ' WHERE f.empresa_id = :empresa_id'; $params['empresa_id'] = $empresaId; }
        $sql .= " GROUP BY $grupo ORDER BY vendas DESC";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function contar(string $tabela, int $empresaId): int
    {
        $stmt = $this->bd->prepare("SELECT COUNT(*) FROM $tabela WHERE empresa_id = :empresa_id");
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    private function contarUsuariosFilial(int $filialId): int
    {
        $stmt = $this->bd->prepare('SELECT COUNT(*) FROM usuario_filiais WHERE filial_id = :filial_id');
        $stmt->execute(['filial_id' => $filialId]);
        return (int) $stmt->fetchColumn();
    }
}
