<?php
/**
 * RelatorioPdfBuilder
 *
 * Construtor genérico e reutilizável de PDFs de relatório, com o estilo
 * visual oficial do MonanaFinancial (paleta navy/verde, cabeçalho da
 * empresa, tabela com zebra, cartões de resumo e rodapé paginado).
 *
 * Qualquer módulo do sistema (Transações, Dashboard, Filiais, etc.) pode
 * usar esta classe para exportar um relatório em PDF com a mesma
 * identidade visual — basta descrever o conteúdo (colunas, linhas,
 * cartões de resumo) e chamar stream()/salvar().
 *
 * Requer dompdf/dompdf (composer.json já inclui a dependência).
 */

if (!class_exists('Dompdf\Dompdf')) {
    $autoload = (defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2)) . '/vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}

class RelatorioPdfBuilder
{
    /** Paleta oficial do MonanaFinancial (public/css/estilo.css) */
    private const COR_NAVY_DEEP  = '#0a1930';
    private const COR_NAVY       = '#0e2748';
    private const COR_NAVY_LIGHT = '#173a67';
    private const COR_GREEN      = '#22c55e';
    private const COR_GREEN_DEEP = '#16a34a';
    private const COR_RED        = '#ef4444';
    private const COR_ORANGE     = '#f59e0b';
    private const COR_MUTED      = '#64748b';
    private const COR_BORDER     = '#e6eaf0';
    private const COR_BG         = '#f4f6fa';

    private string $titulo = 'Relatório';
    private string $subtitulo = '';
    private array $empresa = [];
    private array $colunas = [];
    private array $linhas = [];
    private array $cartoesResumo = [];
    private array $tabelasResumo = [];
    private string $orientacao = 'landscape';
    private string $nomeFicheiro;
    private string $rodapeExtra = '';

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

    /**
     * Dados da empresa exibidos no cabeçalho: nome, nif, endereco, filial, logotipo.
     */
    public function definirEmpresa(array $empresa): static
    {
        $this->empresa = $empresa;
        return $this;
    }

    /**
     * Resolve o caminho absoluto do logotipo da empresa (upload feito em
     * Configurações). Retorna null quando não existe ficheiro de imagem válido.
     */
    private function resolverLogotipo(): ?string
    {
        $logo = trim((string) ($this->empresa['logotipo'] ?? ''));
        if ($logo === '') {
            return null;
        }

        $raiz = defined('CAMINHO_RAIZ') ? CAMINHO_RAIZ : dirname(__DIR__, 2);
        $caminho = $raiz . '/public/' . ltrim($logo, '/');

        if (!is_file($caminho)) {
            // tolera caminhos gravados com "public/" na frente
            $alternativo = $raiz . '/' . ltrim($logo, '/');
            $caminho = is_file($alternativo) ? $alternativo : $caminho;
        }

        if (!is_file($caminho)) {
            return null;
        }

        $extensao = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
        // SVG pode falhar no Dompdf; só aceitar raster
        return in_array($extensao, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true) ? $caminho : null;
    }

    /**
     * @param array $colunas Lista de cabeçalhos das colunas, ex: ['Data', 'Descrição', 'Valor (Kz)']
     */
    public function definirColunas(array $colunas): static
    {
        $this->colunas = $colunas;
        return $this;
    }

    /**
     * @param array $linhas Cada linha é um array de células. Cada célula pode ser:
     *   - uma string/número simples; ou
     *   - ['texto' => '...', 'classe' => 'positivo|negativo|badge-sucesso|badge-perigo|numero', 'alinhar' => 'direita']
     */
    public function definirLinhas(array $linhas): static
    {
        $this->linhas = $linhas;
        return $this;
    }

    /**
     * Cartões de resumo mostrados acima do rodapé, ex:
     * [['rotulo' => 'Total Entradas', 'valor' => '1.234.000 Kz', 'cor' => 'verde'], ...]
     * cor: verde|vermelho|navy|laranja
     */
    public function definirCartoesResumo(array $cartoes): static
    {
        $this->cartoesResumo = $cartoes;
        return $this;
    }

    /**
     * Tabelas de resumo adicionais (ex: resumo por filial/empresa), no formato:
     * ['titulo' => 'Resumo por Filial', 'colunas' => [...], 'linhas' => [...]]
     */
    public function adicionarTabelaResumo(string $titulo, array $colunas, array $linhas): static
    {
        $this->tabelasResumo[] = compact('titulo', 'colunas', 'linhas');
        return $this;
    }

    public function definirOrientacao(string $orientacao): static
    {
        $this->orientacao = $orientacao === 'portrait' ? 'portrait' : 'landscape';
        return $this;
    }

    public function definirRodapeExtra(string $texto): static
    {
        $this->rodapeExtra = $texto;
        return $this;
    }

    /**
     * Gera o PDF e envia diretamente para o browser (download).
     */
    public function stream(): void
    {
        $html = $this->gerarHtml();

        if (class_exists('Dompdf\Dompdf')) {
            $opcoes = new \Dompdf\Options();
            $opcoes->set('isRemoteEnabled', false);
            $opcoes->set('isHtml5ParserEnabled', true);
            $opcoes->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($opcoes);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', $this->orientacao);
            $dompdf->render();

            // Numeração de páginas nativa do dompdf ("Página X de Y")
            $canvas = $dompdf->getCanvas();
            $canvas->page_text(
                $this->orientacao === 'landscape' ? 780 : 520,
                $canvas->get_height() - 30,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                null,
                8,
                [0.39, 0.45, 0.55]
            );

            $dompdf->stream($this->nomeFicheiro . '.pdf', ['Attachment' => true]);
            exit;
        }

        // Sem Dompdf disponível: mostra o HTML pronto para impressão/"Guardar como PDF"
        $this->streamFallback($html);
        exit;
    }

    private function streamFallback(string $html): void
    {
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($this->titulo) . '</title></head><body>'
            . $html
            . '<div class="no-print" style="text-align:center;margin:24px;padding:20px;background:' . self::COR_BG . ';border-radius:12px;font-family:sans-serif;">
                <p style="color:' . self::COR_MUTED . ';">A biblioteca Dompdf não está instalada. Clique em "Imprimir" e escolha "Guardar como PDF".</p>
                <button onclick="window.print()" style="padding:10px 30px;background:' . self::COR_NAVY . ';color:#fff;border:none;border-radius:8px;font-size:14px;cursor:pointer;">🖨️ Imprimir / Guardar como PDF</button>
              </div>
              <style>@media print{.no-print{display:none;}}</style>
            </body></html>';
    }

    /**
     * Monta o documento HTML completo com o estilo de marca.
     */
    private function gerarHtml(): string
    {
        $css = $this->gerarCss();
        $cabecalho = $this->gerarCabecalho();
        $tabelaPrincipal = $this->gerarTabelaPrincipal();
        $cartoes = $this->gerarCartoesResumo();
        $tabelasResumo = $this->gerarTabelasResumo();
        $rodape = $this->gerarRodape();

        return "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><style>{$css}</style></head><body>
            {$cabecalho}
            <div class=\"conteudo\">
                {$tabelaPrincipal}
                {$cartoes}
                {$tabelasResumo}
            </div>
            {$rodape}
        </body></html>";
    }

    private function gerarCss(): string
    {
        return '
            @page { margin: 18mm 12mm 20mm 12mm; }
            * { box-sizing: border-box; }
            body { font-family: "DejaVu Sans", Arial, sans-serif; color: #101828; font-size: 9.5pt; margin: 0; }

            .cabecalho {
                background: linear-gradient(90deg, ' . self::COR_NAVY_DEEP . ' 0%, ' . self::COR_NAVY . ' 100%);
                color: #ffffff;
                padding: 18px 22px 0 22px;
                border-radius: 10px;
                margin-bottom: 18px;
            }
            .cabecalho .linha-topo { width: 100%; }
            .cabecalho .logotipo { max-height: 55px; max-width: 110px; background: #ffffff; border-radius: 8px; padding: 4px; }
            .cabecalho .marca { font-family: "DejaVu Sans", sans-serif; font-weight: bold; font-size: 15pt; letter-spacing: 0.3px; }
            .cabecalho .marca .destaque { color: ' . self::COR_GREEN . '; }
            .cabecalho .empresa-nome { font-size: 11pt; font-weight: bold; margin-top: 6px; }
            .cabecalho .empresa-info { font-size: 8pt; color: #cbd5e1; margin-top: 2px; }
            .cabecalho .titulo-relatorio { font-size: 14pt; font-weight: bold; text-align: right; }
            .cabecalho .subtitulo-relatorio { font-size: 8.5pt; color: #cbd5e1; text-align: right; margin-top: 4px; }
            .cabecalho .meta-relatorio { font-size: 7.5pt; color: #94a3b8; text-align: right; margin-top: 4px; font-style: italic; }
            .faixa-verde { height: 4px; background: linear-gradient(90deg, ' . self::COR_GREEN . ' 0%, ' . self::COR_GREEN_DEEP . ' 100%); border-radius: 0 0 10px 10px; margin: 14px -22px 0 -22px; }

            .conteudo { padding: 0 2px; }

            table.dados { width: 100%; border-collapse: collapse; margin-top: 4px; }
            table.dados thead th {
                background: ' . self::COR_NAVY . ';
                color: #ffffff;
                text-align: left;
                padding: 7px 8px;
                font-size: 8.5pt;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                border-bottom: 2px solid ' . self::COR_GREEN . ';
            }
            table.dados thead th:first-child { border-radius: 4px 0 0 0; }
            table.dados thead th:last-child { border-radius: 0 4px 0 0; }
            table.dados tbody td {
                padding: 6px 8px;
                font-size: 9pt;
                border-bottom: 1px solid ' . self::COR_BORDER . ';
            }
            table.dados tbody tr:nth-child(even) { background: ' . self::COR_BG . '; }
            table.dados .numero { text-align: right; font-variant-numeric: tabular-nums; }
            table.dados .positivo { color: ' . self::COR_GREEN_DEEP . '; font-weight: bold; }
            table.dados .negativo { color: ' . self::COR_RED . '; font-weight: bold; }
            .badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 7.5pt; font-weight: bold; }
            .badge-sucesso { background: #dcfce7; color: ' . self::COR_GREEN_DEEP . '; }
            .badge-perigo { background: #fee2e2; color: ' . self::COR_RED . '; }

            .sem-dados { text-align: center; padding: 40px 0; color: ' . self::COR_MUTED . '; font-size: 10pt; }

            .cartoes { width: 100%; margin-top: 18px; }
            .cartoes table { width: 100%; border-collapse: separate; border-spacing: 8px 0; }
            .cartoes td { width: 25%; }
            .cartao {
                border-radius: 10px;
                padding: 12px 14px;
                border: 1px solid ' . self::COR_BORDER . ';
            }
            .cartao .rotulo { font-size: 7.5pt; color: ' . self::COR_MUTED . '; text-transform: uppercase; letter-spacing: 0.4px; }
            .cartao .valor { font-size: 12pt; font-weight: bold; margin-top: 4px; }
            .cartao-verde { background: #f0fdf4; border-color: #bbf7d0; }
            .cartao-verde .valor { color: ' . self::COR_GREEN_DEEP . '; }
            .cartao-vermelho { background: #fef2f2; border-color: #fecaca; }
            .cartao-vermelho .valor { color: ' . self::COR_RED . '; }
            .cartao-navy { background: #eef3fb; border-color: #cbd9ef; }
            .cartao-navy .valor { color: ' . self::COR_NAVY . '; }
            .cartao-laranja { background: #fffbeb; border-color: #fde68a; }
            .cartao-laranja .valor { color: ' . self::COR_ORANGE . '; }

            .tabela-resumo { margin-top: 20px; }
            .tabela-resumo h3 {
                font-size: 10pt; color: ' . self::COR_NAVY . '; margin: 0 0 6px 0;
                border-left: 4px solid ' . self::COR_GREEN . '; padding-left: 8px;
            }

            .rodape {
                position: fixed;
                bottom: -12mm;
                left: 0; right: 0;
                text-align: center;
                font-size: 7.5pt;
                color: ' . self::COR_MUTED . ';
                border-top: 1px solid ' . self::COR_BORDER . ';
                padding-top: 6px;
            }
        ';
    }

    private function gerarCabecalho(): string
    {
        $empresaNome = htmlspecialchars($this->empresa['nome'] ?? '');
        $infoPartes = [];
        if (!empty($this->empresa['nif'])) {
            $infoPartes[] = 'NIF: ' . htmlspecialchars($this->empresa['nif']);
        }
        if (!empty($this->empresa['endereco'])) {
            $infoPartes[] = htmlspecialchars($this->empresa['endereco']);
        }
        $infoLinha = implode(' &nbsp;•&nbsp; ', $infoPartes);

        // Logotipo da empresa (upload em Configurações). Dompdf aceita caminho absoluto local.
        $logoCaminho = $this->resolverLogotipo();
        $blocoLogo = '';
        if ($logoCaminho !== null) {
            $blocoLogo = '<img src="' . htmlspecialchars($logoCaminho, ENT_QUOTES) . '" alt="Logotipo" class="logotipo" />';
        }

        $blocoEmpresa = '';
        if ($empresaNome !== '') {
            $blocoEmpresa = '<div class="empresa-nome">' . $empresaNome . '</div>';
            if ($infoLinha !== '') {
                $blocoEmpresa .= '<div class="empresa-info">' . $infoLinha . '</div>';
            }
        }

        $geradoEm = date('d/m/Y \à\s H:i');

        return '
            <div class="cabecalho">
                <table class="linha-topo" style="border-collapse:collapse;">
                    <tr>
                        <td style="width:8%; vertical-align:middle;">' . $blocoLogo . '</td>
                        <td style="width:52%; vertical-align:middle;">
                            <div class="marca">Monana<span class="destaque">Financial</span></div>
                            ' . $blocoEmpresa . '
                        </td>
                        <td style="width:40%; vertical-align:middle;">
                            <div class="titulo-relatorio">' . htmlspecialchars($this->titulo) . '</div>
                            <div class="subtitulo-relatorio">' . htmlspecialchars($this->subtitulo) . '</div>
                            <div class="meta-relatorio">Gerado em ' . $geradoEm . '</div>
                        </td>
                    </tr>
                </table>
                <div class="faixa-verde"></div>
            </div>';
    }

    private function gerarTabelaPrincipal(): string
    {
        if (empty($this->colunas)) {
            return '';
        }

        if (empty($this->linhas)) {
            return '<div class="sem-dados">Nenhum registo encontrado para os filtros selecionados.</div>';
        }

        $html = '<table class="dados"><thead><tr>';
        foreach ($this->colunas as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($this->linhas as $linha) {
            $html .= '<tr>';
            foreach ($linha as $celula) {
                if (is_array($celula)) {
                    $texto = $celula['texto'] ?? '';
                    $classes = [];
                    if (!empty($celula['classe'])) {
                        $classes[] = $celula['classe'];
                    }
                    if (($celula['alinhar'] ?? '') === 'direita') {
                        $classes[] = 'numero';
                    }
                    $classeAttr = $classes ? ' class="' . implode(' ', $classes) . '"' : '';

                    if (($celula['tipo'] ?? '') === 'badge') {
                        $texto = '<span class="badge ' . htmlspecialchars($celula['badge_classe'] ?? 'badge-sucesso') . '">' . htmlspecialchars($texto) . '</span>';
                        $html .= "<td{$classeAttr}>{$texto}</td>";
                    } else {
                        $html .= "<td{$classeAttr}>" . htmlspecialchars((string) $texto) . '</td>';
                    }
                } else {
                    $html .= '<td>' . htmlspecialchars((string) $celula) . '</td>';
                }
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    private function gerarCartoesResumo(): string
    {
        if (empty($this->cartoesResumo)) {
            return '';
        }

        $html = '<div class="cartoes"><table><tr>';
        foreach ($this->cartoesResumo as $cartao) {
            $cor = 'cartao-' . ($cartao['cor'] ?? 'navy');
            $html .= '<td><div class="cartao ' . $cor . '">
                <div class="rotulo">' . htmlspecialchars($cartao['rotulo'] ?? '') . '</div>
                <div class="valor">' . htmlspecialchars($cartao['valor'] ?? '') . '</div>
            </div></td>';
        }
        $html .= '</tr></table></div>';
        return $html;
    }

    private function gerarTabelasResumo(): string
    {
        if (empty($this->tabelasResumo)) {
            return '';
        }

        $html = '';
        foreach ($this->tabelasResumo as $tabela) {
            $html .= '<div class="tabela-resumo"><h3>' . htmlspecialchars($tabela['titulo']) . '</h3>';
            $html .= '<table class="dados"><thead><tr>';
            foreach ($tabela['colunas'] as $col) {
                $html .= '<th>' . htmlspecialchars($col) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($tabela['linhas'] as $linha) {
                $html .= '<tr>';
                foreach ($linha as $celula) {
                    if (is_array($celula)) {
                        $classe = $celula['classe'] ?? '';
                        $classeAttr = $classe ? ' class="' . $classe . (($celula['alinhar'] ?? '') === 'direita' ? ' numero' : '') . '"' : (($celula['alinhar'] ?? '') === 'direita' ? ' class="numero"' : '');
                        $html .= "<td{$classeAttr}>" . htmlspecialchars((string) ($celula['texto'] ?? '')) . '</td>';
                    } else {
                        $html .= '<td>' . htmlspecialchars((string) $celula) . '</td>';
                    }
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div>';
        }
        return $html;
    }

    private function gerarRodape(): string
    {
        $extra = $this->rodapeExtra ? ' &nbsp;•&nbsp; ' . htmlspecialchars($this->rodapeExtra) : '';
        return '<div class="rodape">Gerado em ' . date('d/m/Y H:i') . ' &nbsp;•&nbsp; MonanaFinancial' . $extra . '</div>';
    }
}
