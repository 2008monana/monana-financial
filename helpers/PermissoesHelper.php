<?php
/**
 * PermissoesHelper
 * Fonte única da verdade sobre quais páginas (módulos) existem no sidebar
 * e quem pode conceder/remover esses acessos.
 *
 * Regra: apenas o Super Admin e o Admin de Empresa podem conceder ou remover
 * acesso às páginas do sidebar. Todos os restantes utilizadores (interno /
 * visualizador) só acedem às páginas que lhes forem atribuídas.
 */

require_once CAMINHO_RAIZ . '/models/Modulo.php';

class PermissoesHelper
{
    /** Perfis que podem gerir permissões de acesso às páginas. */
    public const PERFIS_GESTORES = ['super_admin', 'admin_empresa'];

    /** Módulos que NÃO devem aparecer na grelha de acessos (exclusivos do Super Admin). */
    private const EXCLUSIVOS_SUPER_ADMIN = ['empresas'];

    /** Páginas adicionais que não constam no catálogo mas existem nas rotas. */
    private const PAGINAS_ADICIONAIS = [
        'fecho_diario'      => 'Fecho Diário',
        'metodos_pagamento' => 'Métodos de Pagamento',
    ];

    /** O gestor pode conceder/remover acessos? */
    public static function podeGerirPermissoes(?string $perfil): bool
    {
        return in_array((string) $perfil, self::PERFIS_GESTORES, true);
    }

    /**
     * Lista de módulos editáveis num formulário de acessos.
     * Remove os exclusivos do Super Admin e garante as páginas extra.
     *
     * @return array<int, array{id:int|string, nome:string, descricao:string, icone:string}>
     */
    public static function modulosEditaveis(array $modulosBd): array
    {
        $lista = [];
        $nomesExistentes = [];

        foreach ($modulosBd as $modulo) {
            if (in_array($modulo['nome'], self::EXCLUSIVOS_SUPER_ADMIN, true)) {
                continue; // página exclusiva do Super Admin — não é atribuível
            }
            $nomesExistentes[] = $modulo['nome'];
            $lista[] = [
                'id'        => $modulo['id'],
                'nome'      => $modulo['nome'],
                'descricao' => $modulo['descricao'] ?? ucfirst(str_replace('_', ' ', $modulo['nome'])),
                'icone'     => $modulo['icone'] ?? 'fa-circle',
            ];
        }

        // Garante que páginas reais do sidebar (mesmo sem registo na tabela) são visíveis.
        foreach (self::PAGINAS_ADICIONAIS as $nome => $rotulo) {
            if (!in_array($nome, $nomesExistentes, true)) {
                $lista[] = [
                    'id'        => 'pagina_' . $nome,
                    'nome'      => $nome,
                    'descricao' => $rotulo,
                    'icone'     => 'fa-circle',
                ];
            }
        }

        return $lista;
    }

    /**
     * Filtra ids de módulos submetidos pelo formulário, removendo qualquer
     * tentativa de atribuir módulos exclusivos do Super Admin.
     */
    public static function filtrarIdsPermitidos(array $idsPost): array
    {
        $modelo = new Modulo();
        $excludentes = array_flip(self::EXCLUSIVOS_SUPER_ADMIN);
        $resultado = [];

        foreach ($idsPost as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue; // entradas sintéticas (pagina_*) não se guardam
            }
            $modulo = $modelo->encontrarPorId($id);
            if ($modulo && isset($excludentes[$modulo['nome']])) {
                continue;
            }
            $resultado[] = $id;
        }

        return $resultado;
    }
}
