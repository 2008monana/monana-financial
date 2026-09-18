<?php
/**
 * RelatorioExcelBuilder
 *
 * Construtor genérico e reutilizável de ficheiros Excel (.xlsx) de
 * relatório, com o estilo visual oficial do MonanaFinancial (cabeçalho
 * da empresa em faixa navy, tabela com zebra, cores para entradas/saídas
 * e um bloco de resumo final destacado).
 *
 * Qualquer módulo do sistema pode usar esta classe para exportar dados
 * em Excel com a mesma identidade visual — basta descrever o conteúdo
 * (colunas, linhas, resumo) e chamar stream().
 *
 * Requer phpoffice/phpspreadsheet (composer.json já inclui a dependência).
 */

if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
    $autoload = (defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2)) . '/vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RelatorioExcelBuilder
{
    /** Paleta oficial do MonanaFinancial (public/css/estilo.css), sem "#" para o PhpSpreadsheet */
    private const COR_NAVY_DEEP  = '0A1930';
    private const COR_NAVY       = '0E2748';
    private const COR_GREEN_DEEP = '16A34A';
    private const COR_GREEN_BG   = 'DCFCE7';
    private const COR_RED        = 'DC2626';
    private const COR_RED_BG     = 'FEE2E2';
    private const COR_MUTED      = '64748B';
    private const COR_BORDER     = 'E2E8F0';
    private const COR_ZEBRA      = 'F4F6FA';
    private const BRANCO         = 'FFFFFF';

    private string $titulo;
    private string $subtitulo = '';
    private array $empresa = [];
    private array $colunas = [];
    private array $linhas = [];
    private array $resumo = [];
    private string $nomeFolha = 'Relatório';
    private string $nomeFicheiro;
    private string $orientacao = 'landscape';

    public function __construct(string $titulo, string $nomeFicheiro = 'relatorio')
    {
        $this->titulo = $titulo;
        $this->nomeFicheiro = $nomeFicheiro;
    }

    public function definirSubtitulo(string $subtitulo): static
    {
        $this->subtitulo = $subtitulo;
        return $this;
    }

    public function definirEmpresa(array $empresa): static
    {
        $this->empresa = $empresa;
        return $this;
    }

    /** @param array $colunas Cabeçalhos das colunas, ex: ['Data', 'Descrição', 'Valor (Kz)'] */
    public function definirColunas(array $colunas): static
    {
        $this->colunas = $colunas;
        return $this;
    }

    /**
     * @param array $linhas Cada linha é um array de células. Cada célula pode ser:
     *   - um valor simples (string/número); ou
     *   - ['valor' => ..., 'tipo' => 'moeda|texto', 'estilo' => 'positivo|negativo']
     */
    public function definirLinhas(array $linhas): static
    {
        $this->linhas = $linhas;
        return $this;
    }

    /**
     * Linhas de resumo final (destacadas), formato:
     * [['rotulo' => 'Total Entradas', 'valor' => 1234000, 'estilo' => 'positivo'], ...]
     */
    public function definirResumo(array $resumo): static
    {
        $this->resumo = $resumo;
        return $this;
    }

    public function definirNomeFolha(string $nome): static
    {
        // Excel limita o nome da folha a 31 caracteres e não aceita alguns símbolos
        $this->nomeFolha = substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $nome), 0, 31);
        return $this;
    }

    public function definirOrientacao(string $orientacao): static
    {
        $this->orientacao = $orientacao === 'portrait' ? 'portrait' : 'landscape';
        return $this;
    }

    /**
     * Gera o ficheiro .xlsx e envia diretamente para download.
     */
    public function stream(): void
    {
        $spreadsheet = $this->construir();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $this->nomeFicheiro . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function construir(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->nomeFolha);

        $totalColunas = max(count($this->colunas), 1);
        $ultimaColuna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColunas);

        $linhaAtual = 1;

        $linhaAtual = $this->escreverCabecalho($sheet, $ultimaColuna, $linhaAtual);
        $linhaAtual = $this->escreverTabela($sheet, $ultimaColuna, $linhaAtual);
        $this->escreverResumo($sheet, $ultimaColuna, $linhaAtual);

        // Configuração de impressão
        $sheet->getPageSetup()->setOrientation(
            $this->orientacao === 'landscape' ? PageSetup::ORIENTATION_LANDSCAPE : PageSetup::ORIENTATION_PORTRAIT
        );
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.6)->setBottom(0.6)->setLeft(0.5)->setRight(0.5);

        foreach (range('A', $ultimaColuna) as $coluna) {
            $sheet->getColumnDimension($coluna)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    private function escreverCabecalho($sheet, string $ultimaColuna, int $linha): int
    {
        // Faixa de marca "MonanaFinancial"
        $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
        $sheet->setCellValue("A{$linha}", 'MonanaFinancial');
        $sheet->getStyle("A{$linha}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::BRANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY_DEEP]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($linha)->setRowHeight(24);
        $linha++;

        // Nome da empresa + NIF/endereço
        $empresaNome = $this->empresa['nome'] ?? '';
        if ($empresaNome !== '') {
            $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
            $sheet->setCellValue("A{$linha}", $empresaNome);
            $sheet->getStyle("A{$linha}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => self::BRANCO]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
            ]);
            $linha++;

            $infoPartes = [];
            if (!empty($this->empresa['nif'])) {
                $infoPartes[] = 'NIF: ' . $this->empresa['nif'];
            }
            if (!empty($this->empresa['endereco'])) {
                $infoPartes[] = $this->empresa['endereco'];
            }
            if ($infoPartes) {
                $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
                $sheet->setCellValue("A{$linha}", implode('   •   ', $infoPartes));
                $sheet->getStyle("A{$linha}")->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => self::BRANCO]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
                ]);
                $linha++;
            }
        }

        // Título do relatório
        $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
        $sheet->setCellValue("A{$linha}", $this->titulo);
        $sheet->getStyle("A{$linha}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => self::COR_NAVY]],
        ]);
        $linha++;

        // Subtítulo (período/filtros)
        if ($this->subtitulo !== '') {
            $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
            $sheet->setCellValue("A{$linha}", $this->subtitulo);
            $sheet->getStyle("A{$linha}")->applyFromArray([
                'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => self::COR_MUTED]],
            ]);
            $linha++;
        }

        return $linha + 1; // linha em branco de respiro
    }

    private function escreverTabela($sheet, string $ultimaColuna, int $linha): int
    {
        if (empty($this->colunas)) {
            return $linha;
        }

        $linhaCabecalho = $linha;
        $coluna = 1;
        foreach ($this->colunas as $titulo) {
            $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($coluna);
            $sheet->setCellValue("{$letra}{$linha}", $titulo);
            $coluna++;
        }
        $sheet->getStyle("A{$linha}:{$ultimaColuna}{$linha}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => self::BRANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COR_NAVY]]],
        ]);
        $sheet->getRowDimension($linha)->setRowHeight(20);
        $linha++;

        if (empty($this->linhas)) {
            $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
            $sheet->setCellValue("A{$linha}", 'Nenhum registo encontrado para os filtros selecionados.');
            $sheet->getStyle("A{$linha}")->applyFromArray([
                'font' => ['italic' => true, 'color' => ['rgb' => self::COR_MUTED]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            return $linha + 1;
        }

        $primeiraLinhaDados = $linha;
        foreach ($this->linhas as $indice => $linhaDados) {
            $coluna = 1;
            foreach ($linhaDados as $celula) {
                $letra = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($coluna);
                $ref = "{$letra}{$linha}";

                if (is_array($celula)) {
                    $valor = $celula['valor'] ?? '';
                    $tipo = $celula['tipo'] ?? 'texto';
                    $estilo = $celula['estilo'] ?? null;
                } else {
                    $valor = $celula;
                    $tipo = 'texto';
                    $estilo = null;
                }

                if ($tipo === 'moeda' && is_numeric($valor)) {
                    $sheet->setCellValue($ref, (float) $valor);
                    $sheet->getStyle($ref)->getNumberFormat()->setFormatCode('#,##0 "Kz"');
                    $sheet->getStyle($ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                } elseif ($tipo === 'numero' && is_numeric($valor)) {
                    $sheet->setCellValue($ref, (float) $valor);
                    $sheet->getStyle($ref)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle($ref)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                } else {
                    $sheet->setCellValue($ref, $valor);
                }

                $corFonte = match ($estilo) {
                    'positivo' => self::COR_GREEN_DEEP,
                    'negativo' => self::COR_RED,
                    default => null,
                };
                $estiloArray = [
                    'font' => ['size' => 9.5],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COR_BORDER]]],
                ];
                if ($corFonte) {
                    $estiloArray['font']['color'] = ['rgb' => $corFonte];
                    $estiloArray['font']['bold'] = true;
                }
                $sheet->getStyle($ref)->applyFromArray($estiloArray);

                $coluna++;
            }

            // Zebra (linhas pares em cinza claro)
            if ($indice % 2 === 1) {
                $sheet->getStyle("A{$linha}:{$ultimaColuna}{$linha}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_ZEBRA]],
                ]);
            }

            $linha++;
        }

        // Congela o cabeçalho da tabela ao rolar
        $sheet->freezePane('A' . ($linhaCabecalho + 1));
        $sheet->setAutoFilter("A{$linhaCabecalho}:{$ultimaColuna}" . ($linha - 1));

        return $linha + 1; // linha em branco antes do resumo
    }

    private function escreverResumo($sheet, string $ultimaColuna, int $linha): void
    {
        if (empty($this->resumo)) {
            return;
        }

        foreach ($this->resumo as $item) {
            $rotulo = $item['rotulo'] ?? '';
            $valor = $item['valor'] ?? 0;
            $estilo = $item['estilo'] ?? null;

            $sheet->setCellValue("A{$linha}", $rotulo);
            $sheet->getStyle("A{$linha}")->applyFromArray(['font' => ['bold' => true, 'size' => 10]]);

            $letraValor = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2);
            $refValor = "{$letraValor}{$linha}";
            if (is_numeric($valor)) {
                $sheet->setCellValue($refValor, (float) $valor);
                $sheet->getStyle($refValor)->getNumberFormat()->setFormatCode('#,##0 "Kz"');
            } else {
                $sheet->setCellValue($refValor, $valor);
            }

            $corFundo = match ($estilo) {
                'positivo' => self::COR_GREEN_BG,
                'negativo' => self::COR_RED_BG,
                default => self::COR_ZEBRA,
            };
            $corFonte = match ($estilo) {
                'positivo' => self::COR_GREEN_DEEP,
                'negativo' => self::COR_RED,
                default => self::COR_NAVY,
            };

            $sheet->getStyle("A{$linha}:{$letraValor}{$linha}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $corFundo]],
                'font' => ['bold' => true, 'color' => ['rgb' => $corFonte], 'size' => 10.5],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COR_BORDER]]],
            ]);
            $sheet->getRowDimension($linha)->setRowHeight(20);

            $linha++;
        }

        $linha++;
        $sheet->setCellValue("A{$linha}", 'Gerado em ' . date('d/m/Y H:i') . ' • MonanaFinancial');
        $sheet->getStyle("A{$linha}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => self::COR_MUTED]],
        ]);
    }
}
