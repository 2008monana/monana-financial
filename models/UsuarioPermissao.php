<?php
require_once CAMINHO_RAIZ . '/core/Model.php';

/**
 * Model UsuarioPermissao
 * Permissões granulares de um utilizador interno/visualizador.
 */
class UsuarioPermissao extends Model
{
    protected string $tabela = 'usuario_permissoes';

    private const CAMPOS = [
        'ver_todas_filiais',
        'criar_lancamentos',
        'editar_proprios_lancamentos',
        'eliminar_proprios_lancamentos',
        'ver_relatorios_consolidados',
        'ver_relatorios_filial',
        'exportar_relatorios',
        'importar_excel',
    ];

    public function obterPorUsuario(int $usuarioId): array
    {
        $registo = $this->buscarUmPor('usuario_id', $usuarioId);
        if ($registo) {
            return $registo;
        }
        // Sem registo ainda: devolve tudo a 0 por omissão
        return array_fill_keys(self::CAMPOS, 0) + ['usuario_id' => $usuarioId];
    }

    /** Cria ou actualiza (upsert) as permissões de um utilizador a partir de um array de checkboxes ($_POST) */
    public function salvar(int $usuarioId, array $marcadas): void
    {
        $dados = ['usuario_id' => $usuarioId];
        foreach (self::CAMPOS as $campo) {
            $dados[$campo] = in_array($campo, $marcadas, true) ? 1 : 0;
        }

        $existente = $this->buscarUmPor('usuario_id', $usuarioId);
        if ($existente) {
            unset($dados['usuario_id']);
            $this->atualizar((int) $existente['id'], $dados);
        } else {
            $this->inserir($dados);
        }
    }

    public static function camposDisponiveis(): array
    {
        return self::CAMPOS;
    }
}
