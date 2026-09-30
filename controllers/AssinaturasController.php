<?php
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Assinatura.php';
require_once CAMINHO_RAIZ . '/models/Plano.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/FormatacaoHelper.php';

/**
 * Controller do módulo de Assinaturas.
 *  - Acções de gestão: apenas Super Admin (como o EmpresasController).
 *  - `minha`: leitura para o admin_empresa da sua própria empresa.
 *  - `bloqueada`: página sem layout para todos os perfis duma empresa bloqueada.
 */
class AssinaturasController extends Controller
{
    private Assinatura $assinaturas;
    private Plano $planos;

    public function __construct()
    {
        $this->assinaturas = new Assinatura();
        $this->planos = new Plano();
    }

    // =====================================================
    // SUPER ADMIN — lista
    // =====================================================
    public function index(): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);

        $filtros = [
            'estado'   => trim($_GET['estado'] ?? ''),
            'plano_id' => (int) ($_GET['plano_id'] ?? 0),
            'vencimento' => trim($_GET['vencimento'] ?? ''),
            'pesquisa' => trim($_GET['pesquisa'] ?? ''),
        ];

        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        $linhas = [];
        foreach ($this->assinaturas->listarParaAdmin($filtros) as $reg) {
            $empresaId = (int) $reg['empresa_id'];
            $estado = $this->assinaturas->estadoDaEmpresa($empresaId, $carenciaHoras);
            $st = $estado['estado'];

            if ($filtros['estado'] !== '' && $st !== $filtros['estado']) continue;

            $fimTs = $estado['fim'] !== null ? strtotime((string) $estado['fim']) : null;
            if ($filtros['vencimento'] === '7d') {
                if ($st !== 'activa' || $fimTs === null || $fimTs - time() > 7 * 86400) continue;
            } elseif ($filtros['vencimento'] === 'vencidas') {
                if (!in_array($st, ['carencia', 'bloqueada'], true)) continue;
            }

            $reg['estado_calc'] = $st;
            $reg['estado_data'] = $estado;
            $linhas[] = $reg;
        }

        // Ordenação por defeito: bloqueadas, depois em carência, depois as que vencem primeiro
        $prioridade = ['bloqueada' => 0, 'carencia' => 1, 'activa' => 2, 'gratuita' => 3];
        usort($linhas, static function ($a, $b) use ($prioridade) {
            $pa = $prioridade[$a['estado_calc']] ?? 4;
            $pb = $prioridade[$b['estado_calc']] ?? 4;
            if ($pa !== $pb) return $pa <=> $pb;
            $fa = $a['estado_data']['fim'] !== null ? strtotime((string) $a['estado_data']['fim']) : PHP_INT_MAX;
            $fb = $b['estado_data']['fim'] !== null ? strtotime((string) $b['estado_data']['fim']) : PHP_INT_MAX;
            return $fa <=> $fb;
        });

        // Paginação (mesmo estilo dos logs)
        $porPagina = 15;
        $paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
        $total = count($linhas);
        $paginas = max(1, (int) ceil($total / $porPagina));
        $paginaActual = min($paginaActual, $paginas);
        $itens = array_slice($linhas, ($paginaActual - 1) * $porPagina, $porPagina);

        $resumoEmpresas = [];
        foreach ($linhas as $l) {
            $resumoEmpresas[(int) $l['empresa_id']] = $l['estado_calc'];
        }

        $this->renderizar('assinaturas/index', [
            'tituloPagina'  => 'Assinaturas',
            'paginaAtiva'   => 'assinaturas',
            'itens'         => $itens,
            'todos'         => $linhas,
            'empresas'      => $resumoEmpresas,
            'receita_mes'   => $this->assinaturas->receitaMes(),
            'planos'        => $this->planos->todosOrdenados(),
            'filtros'       => $filtros,
            'paginacao'     => ['total' => $total, 'pagina' => $paginaActual, 'paginas' => $paginas],
            'carencia_horas' => $carenciaHoras,
            'csrf_token'    => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    // =====================================================
    // SUPER ADMIN — ficha da empresa
    // =====================================================
    public function empresa(int $idEmpresa = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);

        $empresaModel = new Empresa();
        $empresa = $empresaModel->encontrarPorId($idEmpresa);
        if (!$empresa) {
            definirFlash('erro', 'Empresa não encontrada.');
            $this->redirecionar('assinaturas');
            return;
        }

        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        $estado        = $this->assinaturas->estadoDaEmpresa($idEmpresa, $carenciaHoras);
        $planos        = $this->planos->activos();

        // Plano actualmente em vigor (para pré-seleccionar no formulário).
        $planoActual = null;
        if (!empty($estado['linha']['plano_id'])) {
            $planoActual = (int) $estado['linha']['plano_id'];
        } else {
            $linhaActual = $this->assinaturas->buscarUmPor('empresa_id', $idEmpresa);
            if ($linhaActual && !empty($linhaActual['plano_id'])) {
                $planoActual = (int) $linhaActual['plano_id'];
            }
        }
        // Garante que o plano actual está disponível no selector mesmo se foi desactivado entretanto.
        if ($planoActual !== null) {
            $temNoSelector = false;
            foreach ($planos as $p) {
                if ((int) $p['id'] === $planoActual) { $temNoSelector = true; break; }
            }
            if (!$temNoSelector) {
                $registo = $this->planos->encontrarPorId($planoActual);
                if ($registo) {
                    array_unshift($planos, $registo);
                }
            }
        }

        $this->renderizar('assinaturas/empresa', [
            'tituloPagina'   => 'Assinatura — ' . $empresa['nome'],
            'paginaAtiva'    => 'assinaturas',
            'empresa'        => $empresa,
            'estado'         => $estado,
            'historico'      => $this->assinaturas->historico($idEmpresa),
            'pagamentos'     => $this->assinaturas->pagamentos($idEmpresa),
            'planos'         => $planos,
            'plano_actual'   => $planoActual,
            'carencia_horas' => $carenciaHoras,
            'csrf_token'     => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    // =====================================================
    // SUPER ADMIN — trocar plano (POST)
    // =====================================================
    public function trocarPlano(int $idEmpresa = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        if (!$this->validarCsrfPost()) return;

        $opcoes = [
            'plano_id'        => (int) ($_POST['plano_id'] ?? 0),
            'valor'           => $_POST['valor'] ?? null,
            'pago'            => (($_POST['pagamento'] ?? '') === 'recebido'),
            'inicio'          => $_POST['inicio'] ?? '',
            'fim_gratuito'    => $_POST['fim_gratuito'] ?? '',
            'metodo'          => $_POST['metodo'] ?? '',
            'referencia'      => $_POST['referencia'] ?? '',
            'data_pagamento'  => $_POST['data_pagamento'] ?? '',
            'observacoes'     => $_POST['observacoes'] ?? '',
        ];

        $r = $this->assinaturas->trocarPlano($idEmpresa, $opcoes);
        if (!$r['ok']) {
            definirFlash('erro', $r['erro']);
        } else {
            definirFlash('sucesso', 'Plano aplicado com sucesso.' . (isset($r['aviso']) ? ' ' . $r['aviso'] : ''));
        }
        $this->redirecionar('assinaturas/empresa/' . $idEmpresa);
    }

    // =====================================================
    // SUPER ADMIN — registar pagamento numa linha pendente (POST)
    // =====================================================
    public function registarPagamento(int $idAssinatura = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        if (!$this->validarCsrfPost()) return;

        $linha = $this->assinaturas->encontrarPorId($idAssinatura);
        if (!$linha) {
            definirFlash('erro', 'Assinatura não encontrada.');
            $this->redirecionar('assinaturas');
            return;
        }

        $r = $this->assinaturas->registarPagamento($idAssinatura, [
            'valor'          => $_POST['valor'] ?? null,
            'metodo'         => $_POST['metodo'] ?? '',
            'referencia'     => $_POST['referencia'] ?? '',
            'data_pagamento' => $_POST['data_pagamento'] ?? '',
            'observacoes'    => $_POST['observacoes'] ?? '',
        ]);

        definirFlash($r['ok'] ? 'sucesso' : 'erro', $r['ok'] ? 'Pagamento registado e assinatura activada.' : $r['erro']);
        $this->redirecionar('assinaturas/empresa/' . (int) $linha['empresa_id']);
    }

    // =====================================================
    // SUPER ADMIN — bloqueio manual (POST)
    // =====================================================
    public function bloquear(int $idEmpresa = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        if (!$this->validarCsrfPost()) return;

        $r = $this->assinaturas->alternarBloqueioManual($idEmpresa, true);
        definirFlash($r['ok'] ? 'sucesso' : 'erro', $r['ok'] ? 'Acesso da empresa bloqueado imediatamente.' : $r['erro']);
        $this->redirecionar('assinaturas/empresa/' . $idEmpresa);
    }

    public function desbloquear(int $idEmpresa = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        if (!$this->validarCsrfPost()) return;

        $r = $this->assinaturas->alternarBloqueioManual($idEmpresa, false);
        definirFlash($r['ok'] ? 'sucesso' : 'erro', $r['ok'] ? 'Bloqueio manual removido. O acesso obedece agora às datas da assinatura.' : $r['erro']);
        $this->redirecionar('assinaturas/empresa/' . $idEmpresa);
    }

    // =====================================================
    // SUPER ADMIN — gestão de planos
    // =====================================================
    public function planos(): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        $this->renderizar('assinaturas/planos', [
            'tituloPagina' => 'Planos de Assinatura',
            'paginaAtiva'  => 'assinaturas',
            'planos'       => $this->planos->todosOrdenados(),
            'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    public function guardarPlano(int $id = 0): void
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        if (!$this->validarCsrfPost()) return;

        $plano = $this->planos->encontrarPorId($id);
        if (!$plano) {
            definirFlash('erro', 'Plano não encontrado.');
            $this->redirecionar('assinaturas/planos');
            return;
        }

        $ehGratuito = $plano['codigo'] === 'gratuito';
        if ($ehGratuito) {
            $preco = 0.0; // o plano gratuito não tem preço editável
        } else {
            $bruto = str_replace([' ', '.'], ['', ''], trim((string) ($_POST['preco'] ?? '0')));
            $bruto = str_replace(',', '.', $bruto);
            if (!is_numeric($bruto)) {
                definirFlash('erro', 'Preço inválido.');
                $this->redirecionar('assinaturas/planos');
                return;
            }
            $preco = (float) $bruto;
        }
        if ($preco < 0) {
            definirFlash('erro', 'O preço não pode ser negativo.');
            $this->redirecionar('assinaturas/planos');
            return;
        }

        $duracao = null;
        if (!$ehGratuito) {
            $duracao = (int) ($_POST['duracao_dias'] ?? 0);
            if ($duracao <= 0 || $duracao > 3650) {
                definirFlash('erro', 'A duração deve ser um número de dias entre 1 e 3650.');
                $this->redirecionar('assinaturas/planos');
                return;
            }
        }

        $antes = ['preco' => $plano['preco'], 'duracao_dias' => $plano['duracao_dias']];
        $ok = $this->planos->guardar($id, $preco, $duracao, !empty($_POST['ativo']));
        if ($ok) {
            AuditoriaHelper::registar('plano_editado', 'planos', $id, $antes,
                ['preco' => $preco, 'duracao_dias' => $duracao, 'ativo' => !empty($_POST['ativo']) ? 1 : 0],
                'baixa', 'Plano "' . $plano['nome'] . '" actualizado');
            definirFlash('sucesso', 'Plano actualizado. As assinaturas existentes mantêm os valores acordados.');
        } else {
            definirFlash('erro', 'Não foi possível guardar o plano.');
        }
        $this->redirecionar('assinaturas/planos');
    }

    // =====================================================
    // ADMIN EMPRESA — minha assinatura (só leitura)
    // =====================================================
    public function minha(): void
    {
        (new AuthMiddleware())->exigirPerfil(['admin_empresa']);

        $empresaId = (int) ($_SESSION['empresa_id'] ?? 0);
        if ($empresaId <= 0) {
            definirFlash('erro', 'Nenhuma empresa associada ao seu utilizador.');
            $this->redirecionar('dashboard');
            return;
        }

        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        $empresaModel = new Empresa();
        $empresa = $empresaModel->encontrarPorId($empresaId);

        $this->renderizar('assinaturas/minha', [
            'tituloPagina'   => 'Minha Assinatura',
            'paginaAtiva'    => 'minha_assinatura',
            'layout'         => 'principal',
            'empresa'        => $empresa ?: ['id' => $empresaId, 'nome' => $_SESSION['empresa_nome'] ?? ''],
            'estado'         => AssinaturaHelper::detalharParaFaixa($this->assinaturas->estadoDaEmpresa($empresaId, $carenciaHoras)),
            'pagamentos'     => $this->assinaturas->pagamentos($empresaId),
            'planos'         => $this->planos->activos(),
            'carencia_horas' => $carenciaHoras,
            'csrf_token'     => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * POST — esconde a faixa azul de "vence em 7 dias" até ao fim da sessão.
     * Qualquer perfil autenticado da empresa pode usar (não altera dados).
     */
    public function esconderFaixa(): void
    {
        $_SESSION['assin_faixa_7d_fechada'] = true;
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            $this->json(['sucesso' => true]);
            return;
        }
        $this->redirecionar('dashboard');
    }

    // =====================================================
    // TODOS OS PERFIS — página de bloqueio (sem layout)
    // =====================================================
    public function bloqueada(): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? '';
        $empresaId = (int) ($_SESSION['empresa_id'] ?? 0);

        // Super Admin ou empresa sem bloqueio -> dashboard
        if ($perfil === 'super_admin' || $empresaId <= 0) {
            $this->redirecionar('dashboard');
            return;
        }

        try {
            $estado = AssinaturaHelper::calcularEMemorizar($empresaId);
        } catch (Throwable $e) {
            error_log('[Assinaturas] bloqueada: ' . $e->getMessage());
            $this->redirecionar('dashboard'); // falha aberta: nunca prender alguém na página de bloqueio
            return;
        }

        if ($estado === null || $estado['acesso']) {
            $this->redirecionar('dashboard');
            return;
        }

        $empresaModel = new Empresa();
        $empresa = $empresaModel->encontrarPorId($empresaId);

        $this->renderizarSemLayout('assinaturas/bloqueada', [
            'empresa_nome' => $empresa['nome'] ?? ($_SESSION['empresa_nome'] ?? ''),
            'estado'       => $estado,
        ]);
    }

    /** CSRF para POSTs; responde JSON 403 em pedidos AJAX. */
    private function validarCsrfPost(): bool
    {
        if (!$this->ehPost()) {
            definirFlash('erro', 'Método não permitido.');
            $this->redirecionar('dashboard');
            return false;
        }
        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $ajx = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
            if ($ajx) {
                $this->json(['sucesso' => false, 'erro' => 'Token CSRF inválido ou sessão expirada.'], 419);
                return false;
            }
            definirFlash('erro', 'Sessão expirada. Volte a tentar.');
            $voltar = $_POST['_destino'] ?? 'dashboard';
            $this->redirecionar($voltar);
            return false;
        }
        return true;
    }
}
