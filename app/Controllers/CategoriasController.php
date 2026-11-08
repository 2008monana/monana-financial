<?php
/**
 * CategoriasController
 * Gestão de categorias de entrada e saída com suporte a hierarquia.
 * Superadmin pode gerir qualquer empresa e o template padrão.
 */

require_once CAMINHO_RAIZ . '/app/Core/Controller.php';
require_once CAMINHO_RAIZ . '/app/Helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/app/Models/Categoria.php';
require_once CAMINHO_RAIZ . '/app/Models/Empresa.php';
require_once CAMINHO_RAIZ . '/app/Middleware/AuthMiddleware.php';
require_once CAMINHO_RAIZ . '/app/Helpers/flash.php';
require_once CAMINHO_RAIZ . '/app/Helpers/SegurancaHelper.php';

class CategoriasController extends Controller
{
    private Categoria $categoriaModel;
    private Empresa $empresaModel;

    public function __construct()
    {
        (new AuthMiddleware())->verificar();

        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';
        if (in_array($perfil, ['visualizador', 'usuario_interno'], true)) {
            definirFlash('erro', 'Não tem permissão para aceder a esta página.');
            $this->redirecionar('dashboard');
        }

        $this->categoriaModel = new Categoria();
        $this->empresaModel   = new Empresa();
    }

    /**
     * Listagem com árvore hierárquica
     */
    public function index(): void
    {
        $perfil    = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_GET['empresa_id'] ?? $empresaId);
        }

        $empresas = [];
        if ($perfil === 'super_admin') {
            $empresas = $this->empresaModel->todos();
        }

        $arvoreEntradas = [];
        $arvoreSaidas   = [];
        $empresaNome    = '';

        if ($empresaId) {
            // Se a empresa ainda não tem categorias, aplica o template padrão
            $existentes = $this->categoriaModel->porEmpresa((int) $empresaId);
            if (empty($existentes)) {
                try {
                    $bd = \Database::obterLigacao();
                    $stmtTpl = $bd->query(
                        "SELECT empresa_id FROM categorias WHERE sistema = 1 AND ativa = 1 LIMIT 1"
                    );
                    $tplRow = $stmtTpl->fetch();
                    $empresaTemplateId = $tplRow ? (int) $tplRow['empresa_id'] : 0;
                    if ($empresaTemplateId > 0 && $empresaTemplateId !== (int) $empresaId) {
                        $this->categoriaModel->copiarTemplate($empresaTemplateId, (int) $empresaId);
                    }
                } catch (\Throwable $e) {
                    error_log('[Categorias] Falha ao aplicar template: ' . $e->getMessage());
                }
            }

            $arvoreEntradas = $this->categoriaModel->arvore((int) $empresaId, 'entrada');
            $arvoreSaidas   = $this->categoriaModel->arvore((int) $empresaId, 'saida');
            $empresa        = $this->empresaModel->encontrarPorId((int) $empresaId);
            $empresaNome    = $empresa['nome'] ?? '';
        }

        $this->renderizar('categorias/index', [
            'tituloPagina'   => 'Categorias',
            'paginaAtiva'    => 'categorias',
            'arvoreEntradas' => $arvoreEntradas,
            'arvoreSaidas'   => $arvoreSaidas,
            'empresaId'      => $empresaId,
            'empresaNome'    => $empresaNome,
            'empresas'       => $empresas,
            'perfil'         => $perfil,
            'csrf_token'     => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Formulário de criação
     */
    public function criar(): void
    {
        $perfil    = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_GET['empresa_id'] ?? $empresaId);
        }

        if (!$empresaId) {
            definirFlash('erro', 'Selecione uma empresa primeiro.');
            $this->redirecionar('categorias');
            return;
        }

        $paisEntrada = $this->categoriaModel->porTipo((int) $empresaId, 'entrada');
        $paisSaida   = $this->categoriaModel->porTipo((int) $empresaId, 'saida');

        $this->renderizar('categorias/form', [
            'tituloPagina' => 'Nova Categoria',
            'paginaAtiva'  => 'categorias',
            'categoria'    => null,
            'empresaId'    => $empresaId,
            'paisEntrada'  => $paisEntrada,
            'paisSaida'    => $paisSaida,
            'erros'        => [],
            'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Guardar nova categoria
     */
    public function armazenar(): void
    {
        $perfil    = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = $_SESSION['empresa_id'] ?? null;

        if ($perfil === 'super_admin') {
            $empresaId = (int) ($_POST['empresa_id'] ?? $empresaId);
        }

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('categorias/criar' . ($empresaId ? '?empresa_id=' . $empresaId : ''));
            return;
        }

        $dados = $this->validarDados($_POST);

        if (!empty($dados['erros'])) {
            $paisEntrada = $this->categoriaModel->porTipo((int) $empresaId, 'entrada');
            $paisSaida   = $this->categoriaModel->porTipo((int) $empresaId, 'saida');

            $this->renderizar('categorias/form', [
                'tituloPagina' => 'Nova Categoria',
                'paginaAtiva'  => 'categorias',
                'categoria'    => $_POST,
                'empresaId'    => $empresaId,
                'paisEntrada'  => $paisEntrada,
                'paisSaida'    => $paisSaida,
                'erros'        => $dados['erros'],
                'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
            ]);
            return;
        }

        $dados['campos']['empresa_id'] = $empresaId;
        $dados['campos']['sistema']    = ($perfil === 'super_admin' && !empty($_POST['sistema'])) ? 1 : 0;

        $id = $this->categoriaModel->inserir($dados['campos']);

        if ($id) {
            AuditoriaHelper::registar('categoria_criada', 'categorias', (int) $id, null, $dados['campos'], 'media');
            definirFlash('sucesso', 'Categoria criada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao criar categoria.');
        }

        $this->redirecionar('categorias' . ($perfil === 'super_admin' && $empresaId ? '?empresa_id=' . $empresaId : ''));
    }

    /**
     * Formulário de edição
     */
    public function editar(string $id): void
    {
        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if (!$categoria) {
            definirFlash('erro', 'Categoria não encontrada.');
            $this->redirecionar('categorias');
            return;
        }

        $empresaId   = (int) $categoria['empresa_id'];
        $paisEntrada = $this->categoriaModel->porTipo($empresaId, 'entrada');
        $paisSaida   = $this->categoriaModel->porTipo($empresaId, 'saida');

        $paisEntrada = array_filter($paisEntrada, fn($p) => (int)$p['id'] !== (int)$id);
        $paisSaida   = array_filter($paisSaida,   fn($p) => (int)$p['id'] !== (int)$id);

        $this->renderizar('categorias/form', [
            'tituloPagina' => 'Editar Categoria',
            'paginaAtiva'  => 'categorias',
            'categoria'    => $categoria,
            'empresaId'    => $empresaId,
            'paisEntrada'  => $paisEntrada,
            'paisSaida'    => $paisSaida,
            'erros'        => [],
            'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
        ]);
    }

    /**
     * Actualizar categoria
     */
    public function atualizar(string $id): void
    {
        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if (!$categoria) {
            definirFlash('erro', 'Categoria não encontrada.');
            $this->redirecionar('categorias');
            return;
        }

        if (!SegurancaHelper::validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            definirFlash('erro', 'Token de segurança inválido.');
            $this->redirecionar('categorias/editar/' . $id);
            return;
        }

        $perfil    = $_SESSION['usuario_perfil'] ?? 'visualizador';
        $empresaId = (int) $categoria['empresa_id'];

        $dados = $this->validarDados($_POST, (int) $id);

        if (!empty($dados['erros'])) {
            $paisEntrada = $this->categoriaModel->porTipo($empresaId, 'entrada');
            $paisSaida   = $this->categoriaModel->porTipo($empresaId, 'saida');

            $this->renderizar('categorias/form', [
                'tituloPagina' => 'Editar Categoria',
                'paginaAtiva'  => 'categorias',
                'categoria'    => array_merge($categoria, $_POST),
                'empresaId'    => $empresaId,
                'paisEntrada'  => $paisEntrada,
                'paisSaida'    => $paisSaida,
                'erros'        => $dados['erros'],
                'csrf_token'   => SegurancaHelper::gerarTokenCSRF(),
            ]);
            return;
        }

        if ($perfil === 'super_admin') {
            $dados['campos']['sistema'] = !empty($_POST['sistema']) ? 1 : 0;
        }

        $ok = $this->categoriaModel->atualizar((int) $id, $dados['campos']);

        if ($ok) {
            AuditoriaHelper::registar('categoria_atualizada', 'categorias', (int) $id, $categoria, $dados['campos'], 'media');
            definirFlash('sucesso', 'Categoria actualizada com sucesso!');
        } else {
            definirFlash('erro', 'Erro ao actualizar categoria.');
        }

        $this->redirecionar('categorias' . ($perfil === 'super_admin' ? '?empresa_id=' . $empresaId : ''));
    }

    /**
     * Alternar estado (activo/inactivo)
     */
    public function alternarEstado(string $id): void
    {
        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if ($categoria) {
            $novoEstado = $categoria['ativa'] ? 0 : 1;
            $this->categoriaModel->atualizar((int) $id, ['ativa' => $novoEstado]);
            AuditoriaHelper::registar(
                $novoEstado ? 'categoria_ativada' : 'categoria_desativada',
                'categorias',
                (int) $id,
                ['ativa' => $categoria['ativa']],
                ['ativa' => $novoEstado],
                $novoEstado ? 'baixa' : 'media'
            );
            definirFlash('sucesso', $categoria['ativa'] ? 'Categoria desactivada.' : 'Categoria reactivada.');
        }

        $perfil    = $_SESSION['usuario_perfil'] ?? '';
        $empresaId = $categoria['empresa_id'] ?? null;
        $this->redirecionar('categorias' . ($perfil === 'super_admin' && $empresaId ? '?empresa_id=' . $empresaId : ''));
    }

    /**
     * Eliminar categoria (apenas Super Admin)
     */
    public function eliminar(string $id): void
    {
        $perfil = $_SESSION['usuario_perfil'] ?? 'visualizador';

        if ($perfil !== 'super_admin') {
            definirFlash('erro', 'Apenas o Super Administrador pode eliminar categorias.');
            $this->redirecionar('categorias');
            return;
        }

        $categoria = $this->categoriaModel->encontrarPorId((int) $id);

        if (!$categoria) {
            definirFlash('erro', 'Categoria não encontrada.');
            $this->redirecionar('categorias');
            return;
        }

        if ($this->categoriaModel->temFilhos((int) $id)) {
            definirFlash('erro', 'Não é possível eliminar uma categoria que tem subcategorias. Elimine primeiro as subcategorias.');
            $this->redirecionar('categorias?empresa_id=' . $categoria['empresa_id']);
            return;
        }

        $this->categoriaModel->eliminar((int) $id);
        AuditoriaHelper::registar('categoria_eliminada', 'categorias', (int) $id, $categoria, null, 'alta');
        definirFlash('sucesso', 'Categoria eliminada com sucesso.');

        $this->redirecionar('categorias?empresa_id=' . $categoria['empresa_id']);
    }

    /**
     * Validação dos dados do formulário
     */
    private function validarDados(array $dados, ?int $idAtual = null): array
    {
        $erros = [];
        $nome  = trim($dados['nome'] ?? '');
        $tipo  = $dados['tipo'] ?? '';
        $paiId = !empty($dados['pai_id']) ? (int) $dados['pai_id'] : null;
        $descricao = trim($dados['descricao'] ?? '');

        if ($nome === '') {
            $erros['nome'] = 'O nome da categoria é obrigatório.';
        }

        if (!in_array($tipo, ['entrada', 'saida'], true)) {
            $erros['tipo'] = 'Selecione um tipo válido.';
        }

        // Cor e ícone automáticos (já não são pedidos no formulário)
        $cor   = $tipo === 'entrada' ? '#16a34a' : '#dc2626';
        $icone = 'fa-tag';
        $ordem = 0;

        if ($paiId) {
            $pai = $this->categoriaModel->encontrarPorId($paiId);
            if (!$pai || $pai['tipo'] !== $tipo) {
                $erros['pai_id'] = 'A categoria pai deve ser do mesmo tipo.';
            }
            if ($idAtual && $paiId === $idAtual) {
                $erros['pai_id'] = 'Uma categoria não pode ser pai de si mesma.';
            }
        }

        return [
            'erros'  => $erros,
            'campos' => [
                'nome'      => $nome,
                'descricao' => $descricao ?: null,
                'tipo'      => $tipo,
                'pai_id'    => $paiId,
                'cor'       => $cor,
                'icone'     => $icone,
                'ordem'     => $ordem,
                'ativa'     => isset($dados['ativa']) ? 1 : 0,
            ],
        ];
    }
}
