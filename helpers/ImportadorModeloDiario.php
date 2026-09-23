<?php
/**
 * Importador dedicado ao modelo "Relatório Diário de Finanças" (uma folha por mês/filial,
 * cabeçalho em 2-3 linhas, colunas de receita e colunas de despesa lado a lado, com
 * colunas calculadas/duplicadas no meio). O importador de colunas genérico (ImportacaoController)
 * não serve para este modelo porque cada dia gera VÁRIAS transações (uma por coluna preenchida),
 * não uma transação por linha.
 *
 * Estratégia:
 *  - Uma folha só é processada se tiver uma coluna A com datas reais a partir de certa linha
 *    (isso exclui automaticamente folhas de resumo/pivot como "Resumo" ou "Planilha1").
 *  - O rótulo de cada coluna é o texto não vazio mais próximo, procurando para cima a partir
 *    da linha imediatamente anterior à primeira linha de dados (até 3 linhas acima).
 *  - A 2ª coluna cujo rótulo normalizado é "data" marca a fronteira entre o bloco de receitas
 *    (à esquerda) e o bloco de despesas (à direita).
 *  - Colunas que contêm fórmulas Excel (totais, saldos, ligações) são automaticamente ignoradas
 *    — não podem ser lançadas como transação porque seriam duplicação de valores já contados
 *    noutra coluna.
 *  - Colunas cujo conteúdo é maioritariamente texto (notas/observações) também são ignoradas.
 */
class ImportadorModeloDiario
{
    private string $caminho;
    /** Rótulos que nunca são coluna de valor (colunas estruturais/etiquetas de linha). */
    private const ROTULOS_IGNORADOS = ['data', 'dia', 'obs', 'ajuste', ''];

    public function __construct(string $caminhoFicheiro)
    {
        $this->caminho = $caminhoFicheiro;
    }

    /** Analisa o ficheiro inteiro e devolve o plano de importação (sem tocar na base de dados). */
    public function analisar(): array
    {
        $livro = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->caminho);
        $folhas = [];
        foreach ($livro->getAllSheets() as $folha) {
            $planoFolha = $this->analisarFolha($folha);
            if ($planoFolha) $folhas[] = $planoFolha;
        }
        return ['folhas' => $folhas];
    }

    /** Reexecuta a análise de uma folha específica (usado na confirmação, para reler os valores). */
    private function obterFolha(string $nome): ?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        $livro = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->caminho);
        $f = $livro->getSheetByName($nome);
        return $f ?: null;
    }

    private function analisarFolha(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $folha): ?array
    {
        $linhaInicio = $this->encontrarLinhaInicioDados($folha);
        if (!$linhaInicio) return null; // folha sem coluna de datas reconhecível — ignorada (ex: Resumo)

        $ultimaColuna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($folha->getHighestColumn());
        $ultimaLinha = $folha->getHighestRow();

        // 1ª passagem: rótulo de cada coluna
        $rotulos = [];
        for ($c = 2; $c <= $ultimaColuna; $c++) {
            $rotulos[$c] = $this->rotuloColuna($folha, $c, $linhaInicio);
        }

        // fronteira receitas/despesas = 2ª coluna cujo rótulo normalizado é "data"
        $fronteira = null;
        $vistaData = false;
        foreach ($rotulos as $c => $r) {
            if ($this->normalizar($r) === 'data') {
                if ($vistaData) { $fronteira = $c; break; }
                $vistaData = true;
            }
        }

        $colunas = [];
        for ($c = 2; $c <= $ultimaColuna; $c++) {
            $rotulo = trim((string) ($rotulos[$c] ?? ''));
            if (in_array($this->normalizar($rotulo), self::ROTULOS_IGNORADOS, true)) continue;

            $bloco = $fronteira ? ($c < $fronteira ? 'entrada' : 'saida') : null;
            if ($bloco === null) continue; // sem fronteira reconhecida — coluna fica de fora por segurança

            [$soma, $contagem, $formula, $textos] = $this->amostrarColuna($folha, $c, $linhaInicio, $ultimaLinha);
            if ($formula) continue;     // coluna calculada/total — seria duplicação
            if ($contagem === 0) continue; // sem valores numéricos
            if ($textos > $contagem) continue; // coluna de notas/texto, não de valores

            [$tipo, $metodo] = $this->classificar($rotulo, $bloco);
            $colunas[] = [
                'indice' => $c,
                'rotulo' => $rotulo,
                'bloco' => $bloco,
                'tipo_transacao' => $tipo,
                'metodo_pagamento' => $metodo,
                'soma' => round($soma, 2),
                'contagem' => $contagem,
                'incluir' => true,
            ];
        }

        if (!$colunas) return null;

        $nomeFolha = $folha->getTitle();
        return [
            'nome' => $nomeFolha,
            'linha_inicio' => $linhaInicio,
            'ultima_linha' => $ultimaLinha,
            'filial_sugerida' => (stripos($nomeFolha, 'viana') !== false) ? 'viana' : 'principal',
            'colunas' => $colunas,
        ];
    }

    /** Primeira linha, a partir do topo, cuja coluna A contém uma data real. */
    private function encontrarLinhaInicioDados(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $folha): ?int
    {
        $limite = min(20, $folha->getHighestRow());
        for ($r = 1; $r <= $limite; $r++) {
            $cel = $folha->getCellByColumnAndRow(1, $r);
            if ($cel->getValue() !== null && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cel)) {
                return $r;
            }
        }
        return null;
    }

    /** Rótulo mais próximo (procurando para cima, até 3 linhas) para a coluna $c. */
    private function rotuloColuna(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $folha, int $c, int $linhaInicio): string
    {
        for ($offset = 1; $offset <= 3; $offset++) {
            $r = $linhaInicio - $offset;
            if ($r < 1) break;
            $v = $folha->getCellByColumnAndRow($c, $r)->getCalculatedValue();
            if ($v !== null && trim((string) $v) !== '') return trim((string) $v);
        }
        return '';
    }

    /** Percorre a coluna nas linhas de dados: devolve [soma, contagem_numerica, tem_formula, contagem_texto]. */
    private function amostrarColuna(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $folha, int $c, int $linhaInicio, int $ultimaLinha): array
    {
        $soma = 0.0; $contagem = 0; $textos = 0; $formula = false;
        for ($r = $linhaInicio; $r <= $ultimaLinha; $r++) {
            $dataCel = $folha->getCellByColumnAndRow(1, $r);
            if ($dataCel->getValue() === null || !\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($dataCel)) continue; // linha fora da tabela diária
            $cel = $folha->getCellByColumnAndRow($c, $r);
            if ($cel->getDataType() === \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA) { $formula = true; }
            $v = $cel->getCalculatedValue();
            if ($v === null || $v === '') continue;
            if (is_numeric($v)) { if ((float) $v != 0) { $soma += (float) $v; $contagem++; } }
            else { $textos++; }
        }
        return [$soma, $contagem, $formula, $textos];
    }

    /** Classifica uma coluna em tipo de transação + método de pagamento, a partir do seu rótulo e bloco. */
    private function classificar(string $rotulo, string $bloco): array
    {
        $n = $this->normalizar($rotulo);
        if (str_contains($n, 'devol')) return ['devolucao', 'outro'];
        if ($bloco === 'entrada') {
            if (str_contains($n, 'despes') || str_contains($n, 'gasto')) return ['custo', 'numerario'];
            $metodo = 'outro';
            if (str_contains($n, 'tpa')) $metodo = 'tpa';
            elseif (str_contains($n, 'transf')) $metodo = 'transferencia';
            elseif (str_contains($n, 'dinheiro') || str_contains($n, 'numerar')) $metodo = 'numerario';
            return ['venda', $metodo];
        }
        // bloco saída
        if (str_contains($n, 'compra')) return ['compra', 'numerario'];
        return ['custo', 'numerario'];
    }

    /** minúsculas, sem acentos, sem espaços nas pontas — para comparações robustas. */
    private function normalizar(string $s): string
    {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $s = strtr($s, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c']);
        return $s;
    }

    /**
     * Executa a importação real (dentro de uma transação BD, gerida pelo chamador) a partir de um
     * plano já revisto/confirmado pelo utilizador. $planoFolhas vem da sessão (saída de analisar(),
     * possivelmente com colunas desmarcadas via 'incluir'=false) e $filiaisPorFolha mapeia nome da
     * folha -> filial_id escolhida na revisão.
     * Devolve ['transacoes_inseridas'=>int,'categorias_criadas'=>int].
     */
    public function executar(array $planoFolhas, array $filiaisPorFolha, int $empresaId, int $usuarioId, PDO $bd): array
    {
        $inseridas = 0;
        $categoriasCache = []; // "tipo|nome" => id
        $insCategoria = $bd->prepare('INSERT INTO categorias (empresa_id, nome, tipo, ativa) VALUES (:empresa,:nome,:tipo,1)');
        $selCategoria = $bd->prepare('SELECT id FROM categorias WHERE empresa_id=:empresa AND nome=:nome AND tipo=:tipo LIMIT 1');
        $insTransacao = $bd->prepare('INSERT INTO transacoes (empresa_id,filial_id,categoria_id,usuario_id,tipo,descricao,valor,metodo_pagamento,data_transacao) VALUES (:empresa,:filial,:categoria,:usuario,:tipo,:descricao,:valor,:metodo,:data)');
        $categoriasCriadas = 0;

        foreach ($planoFolhas as $planoFolhaOriginal) {
            $filialId = (int) ($filiaisPorFolha[$planoFolhaOriginal['nome']] ?? 0);
            if (!$filialId) continue; // sem filial escolhida para esta folha — salta
            $colunasIncluidas = array_filter($planoFolhaOriginal['colunas'], fn($col) => !empty($col['incluir']));
            if (!$colunasIncluidas) continue;

            $folha = $this->obterFolha($planoFolhaOriginal['nome']);
            if (!$folha) continue;
            $linhaInicio = (int) $planoFolhaOriginal['linha_inicio'];
            $ultimaLinha = (int) $planoFolhaOriginal['ultima_linha'];

            for ($r = $linhaInicio; $r <= $ultimaLinha; $r++) {
                $dataCel = $folha->getCellByColumnAndRow(1, $r);
                if ($dataCel->getValue() === null || !\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($dataCel)) continue;
                $data = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dataCel->getCalculatedValue())->format('Y-m-d');

                foreach ($colunasIncluidas as $col) {
                    $v = $folha->getCellByColumnAndRow((int) $col['indice'], $r)->getCalculatedValue();
                    if ($v === null || $v === '' || !is_numeric($v) || (float) $v == 0) continue;

                    $tipoCategoria = in_array($col['tipo_transacao'], ['venda', 'devolucao'], true) ? 'entrada' : 'saida';
                    $chave = $tipoCategoria . '|' . $col['rotulo'];
                    if (!isset($categoriasCache[$chave])) {
                        $selCategoria->execute(['empresa' => $empresaId, 'nome' => $col['rotulo'], 'tipo' => $tipoCategoria]);
                        $idCat = $selCategoria->fetchColumn();
                        if (!$idCat) {
                            $insCategoria->execute(['empresa' => $empresaId, 'nome' => $col['rotulo'], 'tipo' => $tipoCategoria]);
                            $idCat = (int) $bd->lastInsertId();
                            $categoriasCriadas++;
                        }
                        $categoriasCache[$chave] = (int) $idCat;
                    }

                    $insTransacao->execute([
                        'empresa' => $empresaId,
                        'filial' => $filialId,
                        'categoria' => $categoriasCache[$chave],
                        'usuario' => $usuarioId,
                        'tipo' => $col['tipo_transacao'],
                        'descricao' => 'Importado (' . $planoFolhaOriginal['nome'] . ') — ' . $col['rotulo'],
                        'valor' => (float) $v,
                        'metodo' => $col['metodo_pagamento'],
                        'data' => $data,
                    ]);
                    $inseridas++;
                }
            }
        }

        return ['transacoes_inseridas' => $inseridas, 'categorias_criadas' => $categoriasCriadas];
    }
}
