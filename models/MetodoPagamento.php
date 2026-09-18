<?php
/**
 * Model MetodoPagamento
 * Gerencia os métodos de pagamento dinâmicos (entradas e saídas)
 */

require_once CAMINHO_RAIZ . '/core/Model.php';

class MetodoPagamento extends Model
{
    protected string $tabela = 'metodos_pagamento';

    /**
     * Buscar métodos por empresa
     */
    public function porEmpresa(int $empresaId, bool $apenasAtivos = true): array
    {
        $sql = "SELECT * FROM metodos_pagamento 
                WHERE empresa_id = :empresa_id";
        
        if ($apenasAtivos) {
            $sql .= " AND ativo = 1";
        }
        
        $sql .= " ORDER BY tipo, ordem, nome";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar métodos por tipo (entrada/saida)
     */
    public function porTipo(int $empresaId, string $tipo): array
    {
        $sql = "SELECT * FROM metodos_pagamento 
                WHERE empresa_id = :empresa_id 
                AND tipo = :tipo 
                AND ativo = 1 
                ORDER BY ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar métodos por categoria
     */
    public function porCategoria(int $empresaId, string $categoria): array
    {
        $sql = "SELECT * FROM metodos_pagamento 
                WHERE empresa_id = :empresa_id 
                AND categoria = :categoria 
                AND ativo = 1 
                ORDER BY ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'categoria' => $categoria]);
        return $stmt->fetchAll();
    }

    /**
     * Criar método de pagamento com código automático
     */
    public function criar(array $dados): int
    {
        // Gerar código a partir do nome se não for fornecido
        if (empty($dados['codigo'])) {
            $dados['codigo'] = strtolower(trim($dados['nome']));
            $dados['codigo'] = preg_replace('/[^a-z0-9_]/', '_', $dados['codigo']);
            $dados['codigo'] = preg_replace('/_+/', '_', $dados['codigo']);
        }

        // Verificar se o código já existe para esta empresa
        $existente = $this->buscarUmPor('codigo', $dados['codigo']);
        if ($existente && (int)$existente['empresa_id'] === (int)$dados['empresa_id']) {
            $dados['codigo'] = $dados['codigo'] . '_' . time();
        }

        return $this->inserir($dados);
    }

    /**
     * Buscar método por código
     */
    public function porCodigo(int $empresaId, string $codigo): array|false
    {
        $sql = "SELECT * FROM metodos_pagamento 
                WHERE empresa_id = :empresa_id 
                AND codigo = :codigo 
                LIMIT 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'codigo' => $codigo
        ]);
        return $stmt->fetch();
    }

    /**
     * Obter métodos formatados para usar no formulário de fecho diário
     */
    public function getParaFechoDiario(int $empresaId): array
    {
        $metodos = $this->porEmpresa($empresaId);
        
        $entradas = [];
        $saidas = [];
        
        foreach ($metodos as $m) {
            if ($m['tipo'] === 'entrada') {
                $entradas[] = $m;
            } else {
                $saidas[] = $m;
            }
        }
        
        return [
            'entradas' => $entradas,
            'saidas' => $saidas
        ];
    }

    /**
     * Obter categorias para o select (agrupado)
     */
    public function getCategorias(): array
    {
        return [
            'tpa' => 'TPA (Terminal de Pagamento)',
            'transferencia' => 'Transferência Bancária',
            'dinheiro' => 'Dinheiro',
            'deposito' => 'Depósito',
            'gasto' => 'Gasto/Despesa',
            'outro' => 'Outro'
        ];
    }

    /**
     * Cores disponíveis para os métodos
     */
    public function getCoresDisponiveis(): array
    {
        return [
            '#0e2748' => 'Navio Escuro',
            '#173a67' => 'Navio Claro',
            '#3b82f6' => 'Azul',
            '#22c55e' => 'Verde',
            '#16a34a' => 'Verde Escuro',
            '#8b5cf6' => 'Roxo',
            '#f59e0b' => 'Laranja',
            '#ef4444' => 'Vermelho',
            '#dc2626' => 'Vermelho Escuro',
            '#ec4899' => 'Rosa',
            '#14b8a6' => 'Teal',
            '#64748b' => 'Cinza',
            '#101828' => 'Preto'
        ];
    }

    /**
     * Ícones disponíveis para os métodos
     */
    public function getIconesDisponiveis(): array
    {
        return [
            'fa-credit-card' => 'Cartão de Crédito',
            'fa-money-bill' => 'Dinheiro',
            'fa-university' => 'Banco',
            'fa-building-columns' => 'Prédio',
            'fa-receipt' => 'Recibo',
            'fa-clock' => 'Relógio',
            'fa-exclamation-triangle' => 'Alerta',
            'fa-truck' => 'Caminhão',
            'fa-users' => 'Pessoas',
            'fa-water' => 'Água',
            'fa-home' => 'Casa',
            'fa-tools' => 'Ferramentas',
            'fa-pen' => 'Caneta',
            'fa-gas-pump' => 'Bomba de Gasolina',
            'fa-ellipsis-h' => 'Outros'
        ];
    }
}