<?php
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/models/UsuarioPermissao.php';
require_once CAMINHO_RAIZ . '/models/UsuarioFilial.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Filial.php';
require_once CAMINHO_RAIZ . '/models/Modulo.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';

/**
 * UsuariosController
 * - Super Administrador: escolhe a empresa (?empresa_id=) para gerir os utilizadores dela.
 * - Administrador da Empresa: gere directamente os utilizadores da sua própria empresa.
 * Perfis geríveis aqui: admin_empresa, usuario_interno, visualizador (nunca super_admin).
 */
class UsuariosController extends Controller
{
    private Usuario $usuarioModel;
    private UsuarioPermissao $permissaoModel;
    private UsuarioFilial $usuarioFilialModel;
    private Empresa $empresaModel;
    private Filial $filialModel;
    private Modulo $moduloModel;

    private array $perfisGeriveis = [
        'admin_empresa'   => 'Administrador da Empresa',
        'usuario_interno' => 'Utilizador Interno',
        'visualizador'    => 'Visualizador',
    ];

    public function __construct()
    {
        (new AuthMiddleware())->exigirPerfil(['super_admin', 'admin_empresa']);
        $this->usuarioModel       = new Usuario();
        $this->permissaoModel     = new UsuarioPermissao();
        $this->usuarioFilialModel = new UsuarioFilial();
        $this->empresaModel       = new Empresa();
        $this->filialModel        = new Filial();
        $this->moduloModel        = new Modulo();
    }

    private function empresaAtualId(): ?int
    {
        // Se for super_admin, pode escolher empresa via parâmetro
        if (($_SESSION['usuario_perfil'] ?? '') === 'super_admin') {
            $valor = $_GET['empresa_id'] ?? $_POST['empresa_id'] ?? null;
            return $valor ? (int) $valor : null;
        }
        
        // Admin de empresa só vê a sua própria empresa
        if (!empty($_SESSION['empresa_id'])) {
            return (int) $_SESSION['empresa_id'];
        }
        
        return null;
    }

    public function index(): void
    {
        $empresaId = $this->empresaAtualId();

        if ($empresaId === null) {
            // Buscar empresas com contagem de utilizadores (mesmo estilo das filiais)
            $empresas = $this->empresaModel->ativas();
            foreach ($empresas as &$emp) {
                $emp['total_usuarios'] = count($this->usuarioModel->porEmpresa((int) $emp['id']));
            }
            unset($emp);

            $this->renderizar('usuarios/selecionar-empresa', [
                'tituloPagina' => 'Utilizadores — Selecionar Empresa',
                'paginaAtiva'  => 'usuarios',
                'empresas'     => $empresas,
            ]);
            return;
        }

        $empresa = $this->empresaModel->encontrarPorId($empresaId);
        $usuarios = $this->usuarioModel->porEmpresa($empresaId);

        $this->renderizar('usuarios/index', [
            'tituloPagina'   => 'Utilizadores',
            'paginaAtiva'    => 'usuarios',
            'empresa'        => $empresa,
            'usuarios'       => $usuarios,
            'perfisGeriveis' => $this->perfisGeriveis,
        ]);
    }

    public function criar(): void
    {
        $empresaId = $this->empresaAtualId();
        if ($empresaId === null) {
            $this->redirecionar('usuarios/index');
        }

        $modulos = $this->moduloModel->todosAtivos();

        $this->renderizar('usuarios/form', [
            'tituloPagina'   => 'Novo Utilizador',
            'paginaAtiva'    => 'usuarios',
            'empresaId'      => $empresaId,
            'usuario'        => null,
            'permissoes'     => array_fill_keys(UsuarioPermissao::camposDisponiveis(), 0),
            'filiaisIds'     => [],
            'filiaisEmpresa' => $this->filialModel->porEmpresa($empresaId),
            'perfisGeriveis' => $this->perfisGeriveis,
            'modulos'        => $modulos,
            'modulosPermitidosIds' => [],
            'erros'          => [],
        ]);
    }

    public function gravar(): void
    {
        $empresaId = $this->empresaAtualId();
        $dados = $this->dadosValidados(null);

        if ($empresaId === null || !empty($dados['erros'])) {
            $modulos = $this->moduloModel->todosAtivos();
            $this->renderizar('usuarios/form', [
                'tituloPagina'   => 'Novo Utilizador',
                'paginaAtiva'    => 'usuarios',
                'empresaId'      => $empresaId,
                'usuario'        => $_POST,
                'permissoes'     => array_fill_keys(UsuarioPermissao::camposDisponiveis(), 0),
                'filiaisIds'     => $_POST['filiais'] ?? [],
                'filiaisEmpresa' => $empresaId ? $this->filialModel->porEmpresa($empresaId) : [],
                'perfisGeriveis' => $this->perfisGeriveis,
                'modulos'        => $modulos,
                'modulosPermitidosIds' => array_map('intval', $_POST['modulos'] ?? []),
                'erros'          => $empresaId === null ? ['nome' => 'Selecione uma empresa válida.'] : $dados['erros'],
            ]);
            return;
        }

        // Gera senha aleatória
        $senhaGerada = $this->usuarioModel->gerarSenhaAleatoria();

        $usuarioId = $this->usuarioModel->inserir(array_merge($dados['campos'], [
            'empresa_id'      => $empresaId,
            'senha_hash'      => $this->usuarioModel->criarHashSenha($senhaGerada),
            'primeiro_acesso' => 1,
            'ativo'           => 1,
        ]));

        AuditoriaHelper::registar('usuario_criado', 'usuarios', (int) $usuarioId, null, $dados['campos']);
        NotificacaoHelper::paraAdministradoresEmpresa(
            $empresaId, 'sucesso', 'Novo utilizador',
            $dados['campos']['nome'] . ' foi adicionado como ' . ($this->perfisGeriveis[$dados['campos']['perfil']] ?? 'utilizador') . '.',
            URL_BASE . '/usuarios/editar/' . $usuarioId
        );
        $this->permissaoModel->salvar($usuarioId, $_POST['permissoes'] ?? []);
        $this->usuarioFilialModel->substituir($usuarioId, array_map('intval', $_POST['filiais'] ?? []));

        // Salvar permissões por módulo
        $modulosIds = array_map('intval', $_POST['modulos'] ?? []);
        $this->moduloModel->salvarPermissoes($usuarioId, $modulosIds);

        definirFlash('sucesso', 'Utilizador criado com sucesso. Senha de acesso: "' . $senhaGerada . '"');
        $this->redirecionar('usuarios/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . $empresaId));
    }

    public function editar(string $id): void
    {
        $usuario = $this->usuarioModel->encontrarPorId((int) $id);

        if (!$usuario) {
            definirFlash('erro', 'Utilizador não encontrado.');
            $this->redirecionar('usuarios/index');
        }

        $modulos = $this->moduloModel->todosAtivos();
        $modulosUsuario = $this->moduloModel->porUsuario((int) $usuario['id']);
        $modulosPermitidosIds = array_column(
            array_filter($modulosUsuario, fn($m) => $m['permitido']),
            'id'
        );

        $this->renderizar('usuarios/form', [
            'tituloPagina'   => 'Editar Utilizador',
            'paginaAtiva'    => 'usuarios',
            'empresaId'      => $usuario['empresa_id'],
            'usuario'        => $usuario,
            'permissoes'     => $this->permissaoModel->obterPorUsuario((int) $usuario['id']),
            'filiaisIds'     => $this->usuarioFilialModel->idsPorUsuario((int) $usuario['id']),
            'filiaisEmpresa' => $this->filialModel->porEmpresa((int) $usuario['empresa_id']),
            'perfisGeriveis' => $this->perfisGeriveis,
            'modulos'        => $modulos,
            'modulosPermitidosIds' => $modulosPermitidosIds,
            'erros'          => [],
        ]);
    }

    public function atualizar(string $id): void
    {
        $dados = $this->dadosValidados((int) $id);

        if (!empty($dados['erros'])) {
            $usuario = $this->usuarioModel->encontrarPorId((int) $id);
            $modulos = $this->moduloModel->todosAtivos();
            $modulosPermitidosIds = array_map('intval', $_POST['modulos'] ?? []);

            $this->renderizar('usuarios/form', [
                'tituloPagina'   => 'Editar Utilizador',
                'paginaAtiva'    => 'usuarios',
                'empresaId'      => $usuario['empresa_id'] ?? null,
                'usuario'        => array_merge(['id' => $id], $_POST),
                'permissoes'     => array_fill_keys(UsuarioPermissao::camposDisponiveis(), 0),
                'filiaisIds'     => $_POST['filiais'] ?? [],
                'filiaisEmpresa' => isset($usuario['empresa_id']) ? $this->filialModel->porEmpresa((int) $usuario['empresa_id']) : [],
                'perfisGeriveis' => $this->perfisGeriveis,
                'modulos'        => $modulos,
                'modulosPermitidosIds' => $modulosPermitidosIds,
                'erros'          => $dados['erros'],
            ]);
            return;
        }

        $antes = $this->usuarioModel->encontrarPorId((int) $id);
        $this->usuarioModel->atualizar((int) $id, $dados['campos']);
        AuditoriaHelper::registar('usuario_editado', 'usuarios', (int) $id, $antes ?: null, $dados['campos']);
        $this->permissaoModel->salvar((int) $id, $_POST['permissoes'] ?? []);
        $this->usuarioFilialModel->substituir((int) $id, array_map('intval', $_POST['filiais'] ?? []));

        // Salvar permissões por módulo
        $modulosIds = array_map('intval', $_POST['modulos'] ?? []);
        $this->moduloModel->salvarPermissoes((int) $id, $modulosIds);

        definirFlash('sucesso', 'Utilizador atualizado com sucesso.');
        $this->redirecionar('usuarios/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . ($_POST['empresa_id'] ?? '')));
    }

    public function alternarEstado(string $id): void
    {
        $usuario = $this->usuarioModel->encontrarPorId((int) $id);

        if ($usuario) {
            $novoEstado = $usuario['ativo'] ? 0 : 1;
            $this->usuarioModel->atualizar((int) $id, ['ativo' => $novoEstado]);
            AuditoriaHelper::registar($novoEstado ? 'usuario_ativado' : 'usuario_desativado', 'usuarios', (int) $id, ['ativo' => $usuario['ativo']], ['ativo' => $novoEstado], $novoEstado ? 'baixa' : 'alta');
            definirFlash('sucesso', $usuario['ativo'] ? 'Utilizador desativado.' : 'Utilizador reativado.');
        }

        $this->redirecionar('usuarios/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . ($usuario['empresa_id'] ?? '')));
    }

    /** Gera uma nova senha aleatória para o utilizador */
    public function redefinirSenha(string $id): void
    {
        $usuario = $this->usuarioModel->encontrarPorId((int) $id);

        if ($usuario) {
            $novaSenha = $this->usuarioModel->gerarSenhaAleatoria();
            $this->usuarioModel->atualizar((int) $id, [
                'senha_hash'      => $this->usuarioModel->criarHashSenha($novaSenha),
                'primeiro_acesso' => 1,
            ]);
            AuditoriaHelper::registar('usuario_senha_redefinida', 'usuarios', (int) $id, null, null, 'alta');
            definirFlash('sucesso', 'Nova senha gerada para ' . $usuario['nome'] . ': "' . $novaSenha . '"');
        }

        $this->redirecionar('usuarios/index' . ($_SESSION['empresa_id'] ? '' : '?empresa_id=' . ($usuario['empresa_id'] ?? '')));
    }

    private function dadosValidados(?int $idAtual): array
    {
        $erros = [];
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? '';
        $cargo = trim($_POST['cargo'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome é obrigatório.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Introduza um e-mail válido.';
        } else {
            $existente = $this->usuarioModel->encontrarPorEmail($email);
            if ($existente && (int) $existente['id'] !== (int) $idAtual) {
                $erros['email'] = 'Já existe um utilizador com este e-mail.';
            }
        }
        if (!array_key_exists($perfil, $this->perfisGeriveis)) {
            $erros['perfil'] = 'Selecione um perfil válido.';
        }

        return [
            'erros'  => $erros,
            'campos' => [
                'nome'     => $nome,
                'email'    => $email,
                'perfil'   => $perfil ?: 'usuario_interno',
                'cargo'    => $cargo ?: null,
                'telefone' => $telefone ?: null,
            ],
        ];
    }
}