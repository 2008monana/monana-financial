<?php
/**
 * Model CategoriaSaida
 * Gerencia as categorias de saída (tipos de gasto)
 */

require_once CAMINHO_RAIZ . '/core/Model.php';

class CategoriaSaida extends Model
{
    protected string $tabela = 'categorias_saida';

    /**
     * Buscar categorias por empresa
     */
    public function porEmpresa(int $empresaId, bool $apenasAtivos = true): array
    {
        $sql = "SELECT * FROM categorias_saida 
                WHERE empresa_id = :empresa_id";
        
        if ($apenasAtivos) {
            $sql .= " AND ativo = 1";
        }
        
        $sql .= " ORDER BY nome";
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Criar categoria com código automático
     */
    public function criar(array $dados): int
    {
        if (empty($dados['codigo'])) {
            $dados['codigo'] = strtolower(trim($dados['nome']));
            $dados['codigo'] = preg_replace('/[^a-z0-9_]/', '_', $dados['codigo']);
            $dados['codigo'] = preg_replace('/_+/', '_', $dados['codigo']);
        }

        return $this->inserir($dados);
    }

    /**
     * Obter cores disponíveis
     */
    public function getCoresDisponiveis(): array
    {
        return [
            '#f59e0b' => 'Laranja',
            '#8b5cf6' => 'Roxo',
            '#3b82f6' => 'Azul',
            '#0e2748' => 'Navio',
            '#ef4444' => 'Vermelho',
            '#22c55e' => 'Verde',
            '#f97316' => 'Laranja Claro',
            '#64748b' => 'Cinza',
            '#ec4899' => 'Rosa',
            '#14b8a6' => 'Teal'
        ];
    }

    /**
     * Obter ícones disponíveis
     */
    public function getIconesDisponiveis(): array
    {
        return [
            'fa-truck' => 'Caminhão',
            'fa-users' => 'Pessoas',
            'fa-water' => 'Água',
            'fa-home' => 'Casa',
            'fa-tools' => 'Ferramentas',
            'fa-pen' => 'Caneta',
            'fa-gas-pump' => 'Gasolina',
            'fa-ellipsis-h' => 'Outros',
            'fa-tag' => 'Etiqueta',
            'fa-shopping-cart' => 'Carrinho',
            'fa-medkit' => 'Kit Médico',
            'fa-lightbulb' => 'Lâmpada'
        ];
    }
}