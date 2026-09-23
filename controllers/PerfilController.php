<?php
/**
 * PerfilController
 * Gerencia o perfil do utilizador logado
 */

require_once CAMINHO_RAIZ . '/core/Controller.php';
require_once CAMINHO_RAIZ . '/models/Usuario.php';
require_once CAMINHO_RAIZ . '/models/Empresa.php';
require_once CAMINHO_RAIZ . '/models/Modulo.php';
require_once CAMINHO_RAIZ . '/middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/helpers/flash.php';
require_once CAMINHO_RAIZ . '/helpers/SegurancaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';

class PerfilController extends Controller
{
    private Usuario $usuarioModel;
    private Empresa $empresaModel;
    private Modulo $moduloModel;

    public function __construct()
    {
        (new AuthMiddleware())->verificar();
        $this->usuarioModel = new Usuario();
        $this->empresaModel = new Empresa();
        $this->moduloModel = new Modulo();
    }

    /**
     * Página principal do perfil
     */
    public function index(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];
        $usuario = $this->usuarioModel->encontrarPorId($usuarioId);
        
        if (!$usuario) {
            definirFlash('erro', 'Utilizador não encontrado.');
            $this->redirecionar('dashboard/index');
            return;
        }

        // Buscar nome da empresa
        $empresaNome = '';
        if (!empty($usuario['empresa_id'])) {
            $empresa = $this->empresaModel->encontrarPorId((int) $usuario['empresa_id']);
            $empresaNome = $empresa['nome'] ?? '';
        }

        // Buscar permissões do utilizador
        $permissoes = $this->moduloModel->getPermissoesUsuario($usuarioId);

        $this->renderizar('perfil/index', [
            'tituloPagina' => 'Meu Perfil',
            'paginaAtiva' => 'perfil',
            'usuario' => $usuario,
            'empresaNome' => $empresaNome,
            'permissoes' => $permissoes,
            'csrf_token' => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Atualizar dados do perfil
     */
    public function atualizar(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('perfil/index');
            return;
        }

        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $cargo = trim($_POST['cargo'] ?? '');

        if ($nome === '') {
            definirFlash('erro', 'O nome é obrigatório.');
            $this->redirecionar('perfil/index');
            return;
        }

        $atualizado = $this->usuarioModel->atualizar($usuarioId, [
            'nome' => $nome,
            'telefone' => $telefone ?: null,
            'cargo' => $cargo ?: null,
        ]);

        if ($atualizado) {
            $_SESSION['usuario_nome'] = $nome;
            AuditoriaHelper::registar('perfil_atualizado', 'usuarios', $usuarioId, null, ['nome' => $nome, 'telefone' => $telefone, 'cargo' => $cargo], 'baixa');
            definirFlash('sucesso', 'Perfil atualizado com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao atualizar perfil.');
        }

        $this->redirecionar('perfil/index');
    }

    /**
     * Alterar senha do utilizador
     */
    public function alterarSenha(): void
    {
        $usuarioId = (int) $_SESSION['usuario_id'];

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('perfil/index');
            return;
        }

        $senhaAtual = $_POST['senha_atual'] ?? '';
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';

        // Buscar utilizador
        $usuario = $this->usuarioModel->encontrarPorId($usuarioId);
        if (!$usuario) {
            definirFlash('erro', 'Utilizador não encontrado.');
            $this->redirecionar('perfil/index');
            return;
        }

        // Validar senha atual
        if (!$this->usuarioModel->verificarSenha($senhaAtual, $usuario['senha_hash'])) {
            definirFlash('erro', 'Senha atual incorreta.');
            $this->redirecionar('perfil/index');
            return;
        }

        // Validar nova senha
        if (strlen($novaSenha) < 8) {
            definirFlash('erro', 'A nova senha deve ter pelo menos 8 caracteres.');
            $this->redirecionar('perfil/index');
            return;
        }

        if ($novaSenha !== $confirmarSenha) {
            definirFlash('erro', 'As senhas não coincidem.');
            $this->redirecionar('perfil/index');
            return;
        }

        // Atualizar senha
        $atualizado = $this->usuarioModel->atualizar($usuarioId, [
            'senha_hash' => $this->usuarioModel->criarHashSenha($novaSenha),
            'primeiro_acesso' => 0,
        ]);

        if ($atualizado) {
            AuditoriaHelper::registar('senha_alterada', 'usuarios', $usuarioId, null, null, 'alta');
            definirFlash('sucesso', 'Senha alterada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao alterar senha.');
        }

        $this->redirecionar('perfil/index');
    }
}