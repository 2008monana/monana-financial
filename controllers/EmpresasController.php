<?php
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * EmpresasController
 * Gestão de empresas — funcionalidade exclusiva do Super Administrador,
 * já que cada empresa é um "tenant" independente do sistema.
 */
class EmpresasController extends Controller
{
    private Empresa $empresaModel;
    private Filial $filialModel;

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin']);
        $this->empresaModel = new Empresa();
        $this->filialModel  = new Filial();
    }

    public function index(): void
    {
        require_once CAMINHO_RAIZ . '/models/Assinatura.php';
        require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

        $empresas = $this->empresaModel->todos('nome');

        // Conta filiais e calcula o estado da assinatura de cada empresa
        $assinaturas = new Assinatura();
        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        foreach ($empresas as &$empresa) {
            $empresa['total_filiais'] = count($this->filialModel->porEmpresa((int) $empresa['id']));
            try {
                $est = $assinaturas->estadoDaEmpresa((int) $empresa['id'], $carenciaHoras);
                $empresa['assinatura_estado'] = $est['estado'];
                $empresa['assinatura_fim']    = $est['fim'];
                $empresa['assinatura_plano']  = $est['plano']['nome'] ?? null;
            } catch (Throwable $e) {
                error_log('[Empresas] Estado de assinatura indisponível: ' . $e->getMessage());
                $empresa['assinatura_estado'] = null; // falha aberta: não bloqueia a listagem
                $empresa['assinatura_fim']    = null;
                $empresa['assinatura_plano']  = null;
            }
        }
        unset($empresa);

        $this->renderizar('empresas/index', [
            'tituloPagina' => 'Empresas',
            'paginaAtiva'  => 'empresas',
            'empresas'     => $empresas,
        ]);
    }

    public function criar(): void
    {
        require_once CAMINHO_RAIZ . '/models/Plano.php';
        $this->renderizar('empresas/form', [
            'tituloPagina' => 'Nova Empresa',
            'paginaAtiva'  => 'empresas',
            'empresa'      => null,
            'erros'        => [],
            'planos'       => (new Plano())->todosOrdenados(),
        ]);
    }

    public function gravar(): void
    {
        require_once CAMINHO_RAIZ . '/models/Plano.php';
        require_once CAMINHO_RAIZ . '/models/Assinatura.php';

        $dados = $this->dadosValidados();
        $erros = $dados['erros'];

        // ---- Cartão "Assinatura" (apenas na criação) ----
        $planoModel  = new Plano();
        $assinaturas = new Assinatura();
        $planoId     = (int) ($_POST['plano_id'] ?? 0);
        $plano       = $planoId > 0 ? $planoModel->encontrarPorId($planoId) : null;
        if (!$plano) {
            $plano = $planoModel->porCodigo('gratuito') ?: null;
        }
        if (!$plano) {
            $erros['plano_id'] = 'Nenhum plano disponível. Corra a migração de assinaturas.';
        }
        $ehGratuito = $plano && $plano['codigo'] === 'gratuito';
        $pago       = !$ehGratuito && !empty($_POST['pagamento_recebido']);
        $valor      = $ehGratuito ? 0.00 : max(0.0, (float) str_replace('.', '', (string) ($_POST['valor_acordado'] ?? $plano['preco'])));
        $inicioData = trim($_POST['data_inicio'] ?? '');
        if ($inicioData !== '' && !DateTime::createFromFormat('Y-m-d', $inicioData)) {
            $erros['data_inicio'] = 'Data de início inválida.';
        }
        $fimGratis  = trim($_POST['fim_gratuito'] ?? '');
        if ($ehGratuito && $fimGratis !== '') {
            $d = DateTime::createFromFormat('Y-m-d', $fimGratis);
            if (!$d || $d->format('Y-m-d') !== $fimGratis) {
                $erros['fim_gratuito'] = 'Data de fim do período gratuito inválida.';
            }
        } elseif (!$ehGratuito) {
            $fimGratis = '';
        }

        if (!empty($erros)) {
            $this->renderizar('empresas/form', [
                'tituloPagina' => 'Nova Empresa',
                'paginaAtiva'  => 'empresas',
                'empresa'      => $_POST,
                'erros'        => $erros,
                'planos'       => $planoModel->todosOrdenados(),
            ]);
            return;
        }

        // Empresa + primeira assinatura na mesma transacção (tudo ou nada).
        $bd = Database::obterLigacao();
        try {
            $bd->beginTransaction();
            $id = $this->empresaModel->inserir($dados['campos']);
            $idAssinatura = $assinaturas->criarInicial(
                (int) $id,
                (int) $plano['id'],
                $valor,
                $pago,
                $inicioData !== '' ? $inicioData : null,
                $ehGratuito && $fimGratis !== '' ? $fimGratis : null,
                trim($_POST['observacoes_assinatura'] ?? '') ?: null,
                (int) ($_SESSION['usuario_id'] ?? 0) ?: null
            );
            $bd->commit();
        } catch (Throwable $e) {
            if ($bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log('[Empresas] Falha ao criar empresa+assinatura: ' . $e->getMessage());
            definirFlash('erro', 'Não foi possível criar a empresa com a assinatura. Tente novamente.');
            $this->redirecionar('empresas/criar');
            return;
        }

        AuditoriaHelper::registar('empresa_criada', 'empresas', $id, null, $dados['campos']);
        AuditoriaHelper::registar('assinatura_criada', 'assinaturas', $idAssinatura, null,
            ['empresa_id' => $id, 'plano' => $plano['nome'], 'estado' => $ehGratuito ? 'activa(gratuita)' : ($pago ? 'activa' : 'pendente_pagamento')]);
        if (!$ehGratuito && !$pago) {
            // Regra 3.3: entrou em carência por troca para plano pago sem pagamento.
            AssinaturaHelper::notificarCarencia((int) $id, $idAssinatura, 'gratuita cessou', AssinaturaHelper::carenciaHoras());
        }
        NotificacaoHelper::paraSuperAdministradores(
            'sucesso', 'Nova empresa registada', $dados['campos']['nome'] . ' foi adicionada ao sistema.',
            URL_BASE . '/empresas/editar/' . $id
        );
        definirFlash('sucesso', 'Empresa criada com sucesso.');
        $this->redirecionar('empresas');
    }

    public function editar(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if (!$empresa) {
            definirFlash('erro', 'Empresa não encontrada.');
            $this->redirecionar('empresas');
        }

        $this->renderizar('empresas/form', [
            'tituloPagina' => 'Editar Empresa',
            'paginaAtiva'  => 'empresas',
            'empresa'      => $empresa,
            'erros'        => [],
        ]);
    }

    public function atualizar(string $id): void
    {
        $dados = $this->dadosValidados();

        if (!empty($dados['erros'])) {
            $this->renderizar('empresas/form', [
                'tituloPagina' => 'Editar Empresa',
                'paginaAtiva'  => 'empresas',
                'empresa'      => array_merge(['id' => $id], $_POST),
                'erros'        => $dados['erros'],
            ]);
            return;
        }

        $antes = $this->empresaModel->encontrarPorId((int) $id);
        $this->empresaModel->atualizar((int) $id, $dados['campos']);
        AuditoriaHelper::registar('empresa_editada', 'empresas', (int) $id, $antes ?: null, $dados['campos']);
        definirFlash('sucesso', 'Empresa atualizada com sucesso.');
        $this->redirecionar('empresas');
    }

    /** Alterna activa/inactiva (eliminação lógica, para preservar histórico financeiro) */
    public function alternarEstado(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if ($empresa) {
            $novoEstado = $empresa['ativa'] ? 0 : 1;
            $this->empresaModel->atualizar((int) $id, ['ativa' => $novoEstado]);
            AuditoriaHelper::registar($novoEstado ? 'empresa_ativada' : 'empresa_desativada', 'empresas', (int) $id, ['ativa' => $empresa['ativa']], ['ativa' => $novoEstado], $novoEstado ? 'baixa' : 'alta');
            definirFlash('sucesso', $empresa['ativa'] ? 'Empresa desativada.' : 'Empresa reativada.');
        }

        $this->redirecionar('empresas');
    }

    private function dadosValidados(): array
    {
        $erros = [];
        $nome = trim($_POST['nome'] ?? '');
        $nif = trim($_POST['nif'] ?? '');
        $email = trim($_POST['email_contacto'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome da empresa é obrigatório.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email_contacto'] = 'Introduza um e-mail válido.';
        }

        return [
            'erros'  => $erros,
            'campos' => [
                'nome'           => $nome,
                'nif'            => $nif ?: null,
                'email_contacto' => $email ?: null,
                'telefone'       => $telefone ?: null,
                'endereco'       => $endereco ?: null,
            ],
        ];
    }
}
