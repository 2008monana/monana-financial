<?php
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
        $empresas = $this->empresaModel->todos('nome');

        // Conta filiais de cada empresa para exibir na listagem
        foreach ($empresas as &$empresa) {
            $empresa['total_filiais'] = count($this->filialModel->porEmpresa((int) $empresa['id']));
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
        $this->renderizar('empresas/form', [
            'tituloPagina' => 'Nova Empresa',
            'paginaAtiva'  => 'empresas',
            'empresa'      => null,
            'erros'        => [],
        ]);
    }

    public function gravar(): void
    {
        $dados = $this->dadosValidados();

        if (!empty($dados['erros'])) {
            $this->renderizar('empresas/form', [
                'tituloPagina' => 'Nova Empresa',
                'paginaAtiva'  => 'empresas',
                'empresa'      => $_POST,
                'erros'        => $dados['erros'],
            ]);
            return;
        }

        $this->empresaModel->inserir($dados['campos']);
        definirFlash('sucesso', 'Empresa criada com sucesso.');
        $this->redirecionar('empresas/index');
    }

    public function editar(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if (!$empresa) {
            definirFlash('erro', 'Empresa não encontrada.');
            $this->redirecionar('empresas/index');
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

        $this->empresaModel->atualizar((int) $id, $dados['campos']);
        definirFlash('sucesso', 'Empresa atualizada com sucesso.');
        $this->redirecionar('empresas/index');
    }

    /** Alterna activa/inactiva (eliminação lógica, para preservar histórico financeiro) */
    public function alternarEstado(string $id): void
    {
        $empresa = $this->empresaModel->encontrarPorId((int) $id);

        if ($empresa) {
            $this->empresaModel->atualizar((int) $id, ['ativa' => $empresa['ativa'] ? 0 : 1]);
            definirFlash('sucesso', $empresa['ativa'] ? 'Empresa desativada.' : 'Empresa reativada.');
        }

        $this->redirecionar('empresas/index');
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
