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
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
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
    private const COR_AZUL       = '3B82F6'; // "Monana" na marca (visível sobre o fundo navy)
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

        // Logotipo da empresa sobre a faixa do cabeçalho (se carregado em Configurações)
        $this->inserirLogotipo($sheet);

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
        // Logotipo resolvido primeiro: quando existe, reserva a coluna A e
        // recua o texto do cabeçalho para a coluna B (evita sobreposição).
        $temLogotipo = $this->resolverLogotipo() !== null;

        // Faixa de marca "MonanaFinancial" ("Monana" azul + "Financial" verde)
        $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
        $sheet->setCellValue("A{$linha}", 'MonanaFinancial');
        $sheet->getStyle("A{$linha}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => self::BRANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY_DEEP]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        // Aplica cores mistas na faixa de marca: "Monana" em azul e "Financial" em verde.
        // Usa RichText com runs de fonte coloridas (o construtor de Color recebe apenas o valor ARGB).
        try {
            $richMonana = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $runMonana = $richMonana->createTextRun('Monana');
            $runMonana->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FF' . self::COR_AZUL);
            $runFinancial = $richMonana->createTextRun('Financial');
            $runFinancial->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FF' . self::COR_GREEN_DEEP);
            $sheet->getCell("A{$linha}")->setValue($richMonana);
        } catch (\Throwable $e) {
            // se RichText falhar, mantém-se o texto branco simples já definido
        }
        $sheet->getRowDimension($linha)->setRowHeight(24);
        $linha++;

        // Nome da empresa + NIF/endereço (reservamos coluna A para o logotipo)
        $empresaNome = $this->empresa['nome'] ?? '';
        if ($empresaNome !== '') {
            $inicioTexto = $temLogotipo ? 'B' : 'A';
            $sheet->mergeCells("{$inicioTexto}{$linha}:{$ultimaColuna}{$linha}");
            $sheet->setCellValue("{$inicioTexto}{$linha}", $empresaNome);
            $sheet->getStyle("{$inicioTexto}{$linha}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => self::BRANCO]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
            ]);
            // fundo navy também na célula do logotipo
            $sheet->getStyle("A{$linha}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
            ]);
            $sheet->getRowDimension($linha)->setRowHeight(18);
            $linha++;

            $infoPartes = [];
            if (!empty($this->empresa['nif'])) {
                $infoPartes[] = 'NIF: ' . $this->empresa['nif'];
            }
            if (!empty($this->empresa['endereco'])) {
                $infoPartes[] = $this->empresa['endereco'];
            }
            if ($infoPartes) {
                $sheet->mergeCells("{$inicioTexto}{$linha}:{$ultimaColuna}{$linha}");
                $sheet->setCellValue("{$inicioTexto}{$linha}", implode('   •   ', $infoPartes));
                $sheet->getStyle("{$inicioTexto}{$linha}")->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => self::BRANCO]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
                ]);
                $sheet->getStyle("A{$linha}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COR_NAVY]],
                ]);
                $sheet->getRowDimension($linha)->setRowHeight(16);
                $linha++;
            }
            $sheet->getDefaultColumnDimension()->setWidth(14);
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

        // Data de geração
        $sheet->mergeCells("A{$linha}:{$ultimaColuna}{$linha}");
        $sheet->setCellValue("A{$linha}", 'Gerado em ' . date('d/m/Y H:i'));
        $sheet->getStyle("A{$linha}")->applyFromArray([
            'font' => ['size' => 8, 'italic' => true, 'color' => ['rgb' => self::COR_MUTED]],
        ]);
        $linha++;

        return $linha + 1; // linha em branco de respiro
    }

    /**
     * Resolve o caminho absoluto do logotipo da empresa (upload feito em
     * Configurações), tolerando diferentes formatos de valor gravado na BD:
     * "uploads/logos/x.png", "/uploads/...", "public/uploads/..." ou caminho
     * completo. Converte SVG/WebP para PNG quando necessário, pois o
     * PhpSpreadsheet só aceita PNG/JPEG/GIF/bmp como desenho embutido.
     */
    private function resolverLogotipo(): ?string
    {
        $valores = [];
        $principal = trim((string) ($this->empresa['logotipo'] ?? ''));
        if ($principal !== '') {
            $valores[] = $principal;
        }
        foreach ($this->empresa as $chave => $valor) {
            if (is_string($valor) && preg_match('/^logotipo(_\d+)?$/', (string) $chave) && trim($valor) !== '') {
                $valores[] = trim($valor);
            }
        }

        $raiz = rtrim(str_replace('\\', '/', defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2)), '/');

        foreach ($valores as $logo) {
            // aceita URLs completas (http(s)://host/uploads/...) gravadas na BD
            if (preg_match('#^https?://#i', $logo)) {
                $logo = '/' . ltrim((string) parse_url($logo, PHP_URL_PATH), '/');
            }
            // normaliza separadores (o upload é gravado com "/" mesmo no Windows)
            $logo = str_replace('\\', '/', $logo);

            // valores absolutos do tipo "/monana-financial/public/uploads/logos/x.png"
            // (URL_BASE com prefixo de projecto no XAMPP): remover o primeiro segmento
            $relativo = ltrim($logo, '/');
            $variantes = [$relativo];
            if (count(explode('/', $relativo)) > 1) {
                $variantes[] = implode('/', array_slice(explode('/', $relativo), 1));
            }

            $caminhos = [];
            foreach ($variantes as $v) {
                $semPublic = preg_replace('#^public/#', '', $v);
                foreach (array_unique(['public/' . $semPublic, $semPublic]) as $rel) {
                    $caminhos[] = $raiz . '/' . $rel;
                }
            }

            foreach ($caminhos as $cand) {
                if (!is_file($cand)) {
                    continue;
                }

                $extensao = strtolower(pathinfo($cand, PATHINFO_EXTENSION));
                if (in_array($extensao, ['png', 'jpg', 'jpeg', 'gif', 'bmp'], true)) {
                    return realpath($cand) ?: $cand;
                }
                if (in_array($extensao, ['svg', 'webp'], true)) {
                    $png = $this->converterParaPng($cand, $extensao);
                    if ($png !== null) {
                        return $png;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Converte SVG/WebP para PNG temporário (via Imagick/Gmagick c/ fallback GD).
     * Devolve null quando não for possível — nesse caso o relatório é gerado
     * simplesmente sem logotipo.
     */
    private function converterParaPng(string $caminho, string $extensao): ?string
    {
        $destino = sys_get_temp_dir() . '/monana_logo_' . md5($caminho . filemtime($caminho)) . '.png';
        if (is_file($destino)) {
            return $destino;
        }

        try {
            if ($extensao === 'webp' && function_exists('imagewebp') && function_exists('imagecreatefromwebp')) {
                $img = @imagecreatefromwebp($caminho);
                if ($img) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                    $ok = imagepng($img, $destino);
                    imagedestroy($img);
                    if ($ok && is_file($destino)) {
                        return $destino;
                    }
                }
            }
            if ($extensao === 'svg' && extension_loaded('imagick')) {
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($caminho);
                $imagick->setImageBackgroundColor('white');
                if (method_exists($imagick, 'flattenImages')) {
                    $imagick = $imagick->flattenImages();
                }
                $imagick->setImageFormat('png');
                $imagick->writeImage($destino);
                $imagick->destroy();
                return is_file($destino) ? $destino : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Insere o logotipo da empresa (upload feito em Configurações) sobre a
     * faixa do cabeçalho, se existir ficheiro de imagem válido.
     */
    private function inserirLogotipo($sheet): void
    {
        $caminho = $this->resolverLogotipo();
        if ($caminho === null) {
            return;
        }

        try {
            $drawing = new Drawing();
            $drawing->setName('Logotipo da Empresa');
            $drawing->setDescription('Logotipo carregado nas configurações');
            $drawing->setPath($caminho);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(4);
            $drawing->setWidthAndHeight(70, 55);
            $drawing->setWorksheet($sheet);
        } catch (\Throwable $e) {
            // logotipo é decorativo — nunca deve bloquear a exportação
        }
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
