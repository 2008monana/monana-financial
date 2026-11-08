<?php
require_once CAMINHO_RAIZ . '/app/Core/Model.php';

/**
 * Model Categoria
 * Suporta hierarquia (pai_id) e categorias padrão do sistema.
 */
class Categoria extends Model
{
    protected string $tabela = 'categorias';

    /**
     * Buscar categorias por empresa (inclui as do sistema se existirem com empresa_id NULL)
     */
    public function porEmpresa(int $empresaId): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND ativa = 1 
                ORDER BY tipo, ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar categorias ativas (todas)
     */
    public function ativas(): array
    {
        $stmt = $this->bd->query("SELECT * FROM categorias WHERE ativa = 1 ORDER BY tipo, ordem, nome");
        return $stmt->fetchAll();
    }

    /**
     * Buscar categorias por tipo (lista plana)
     */
    public function porTipo(int $empresaId, string $tipo): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND tipo = :tipo 
                AND ativa = 1 
                ORDER BY ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar categorias de topo (sem pai) por tipo
     */
    public function paisPorTipo(int $empresaId, string $tipo): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND tipo = :tipo 
                AND pai_id IS NULL
                AND ativa = 1 
                ORDER BY ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'tipo' => $tipo]);
        return $stmt->fetchAll();
    }

    /**
     * Buscar subcategorias de um pai
     */
    public function filhas(int $paiId): array
    {
        $sql = "SELECT * FROM categorias 
                WHERE pai_id = :pai_id 
                AND ativa = 1 
                ORDER BY ordem, nome";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['pai_id' => $paiId]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna a árvore completa de categorias de uma empresa
     * Estrutura: [ ['categoria' => ..., 'filhos' => [...] ], ... ]
     */
    public function arvore(int $empresaId, ?string $tipo = null): array
    {
        $params = ['empresa_id' => $empresaId];
        $sql = "SELECT * FROM categorias 
                WHERE (empresa_id = :empresa_id OR empresa_id IS NULL) 
                AND ativa = 1";

        if ($tipo !== null) {
            $sql .= " AND tipo = :tipo";
            $params['tipo'] = $tipo;
        }

        $sql .= " ORDER BY tipo, ordem, nome";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        $todas = $stmt->fetchAll();

        // Indexar por id
        $porId = [];
        foreach ($todas as $cat) {
            $cat['filhos'] = [];
            $porId[$cat['id']] = $cat;
        }

        // Montar árvore
        $raiz = [];
        foreach ($porId as $id => &$cat) {
            if ($cat['pai_id'] && isset($porId[$cat['pai_id']])) {
                $porId[$cat['pai_id']]['filhos'][] = &$cat;
            } else {
                $raiz[] = &$cat;
            }
        }
        unset($cat); // quebrar referência

        return $raiz;
    }

    /**
     * Lista plana com indentação visual (útil para selects)
     * Retorna array com campo extra 'nivel' e 'nome_indentado'
     */
    public function listaComIndentacao(int $empresaId, ?string $tipo = null): array
    {
        $arvore = $this->arvore($empresaId, $tipo);
        $lista = [];

        $achatar = function (array $nos, int $nivel = 0) use (&$achatar, &$lista) {
            foreach ($nos as $no) {
                $prefixo = $nivel > 0 ? str_repeat('— ', $nivel) : '';
                $item = $no;
                $item['nivel'] = $nivel;
                $item['nome_indentado'] = $prefixo . $no['nome'];
                unset($item['filhos']);
                $lista[] = $item;

                if (!empty($no['filhos'])) {
                    $achatar($no['filhos'], $nivel + 1);
                }
            }
        };

        $achatar($arvore);
        return $lista;
    }

    /**
     * Verifica se uma categoria tem filhos
     */
    public function temFilhos(int $categoriaId): bool
    {
        $sql = "SELECT COUNT(*) FROM categorias WHERE pai_id = :id AND ativa = 1";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['id' => $categoriaId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Copia o template de categorias de uma empresa origem para uma nova empresa
     * (usado ao criar nova empresa)
     */
    public function copiarTemplate(int $empresaOrigemId, int $empresaDestinoId): int
    {
        // Buscar todas as categorias da origem ordenadas (pais primeiro)
        $sql = "SELECT * FROM categorias 
                WHERE empresa_id = :empresa_id 
                ORDER BY pai_id IS NOT NULL, ordem, id";
        $stmt = $this->bd->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaOrigemId]);
        $origem = $stmt->fetchAll();

        if (empty($origem)) {
            return 0;
        }

        $mapaIds = []; // id_antigo => id_novo
        $inseridas = 0;

        foreach ($origem as $cat) {
            $novoPaiId = null;
            if ($cat['pai_id'] && isset($mapaIds[$cat['pai_id']])) {
                $novoPaiId = $mapaIds[$cat['pai_id']];
            }

            $sqlInsert = "INSERT INTO categorias 
                (empresa_id, pai_id, nome, descricao, tipo, cor, icone, ordem, ativa, sistema)
                VALUES 
                (:empresa_id, :pai_id, :nome, :descricao, :tipo, :cor, :icone, :ordem, 1, 1)";

            $stmtInsert = $this->bd->prepare($sqlInsert);
            $stmtInsert->execute([
                'empresa_id' => $empresaDestinoId,
                'pai_id'     => $novoPaiId,
                'nome'       => $cat['nome'],
                'descricao'  => $cat['descricao'] ?? null,
                'tipo'       => $cat['tipo'],
                'cor'        => $cat['cor'] ?? null,
                'icone'      => $cat['icone'] ?? 'fa-tag',
                'ordem'      => $cat['ordem'] ?? 0,
            ]);

            $novoId = (int) $this->bd->lastInsertId();
            $mapaIds[$cat['id']] = $novoId;
            $inseridas++;
        }

        return $inseridas;
    }
}
