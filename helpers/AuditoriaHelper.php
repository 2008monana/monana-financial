<?php
/** Registo imutável, normalizado e centralizado de eventos auditáveis. */
class AuditoriaHelper
{
    private const CAMPOS_SENSIVEIS = ['senha', 'senha_hash', 'smtp_senha', 'token', 'csrf_token'];
    private const PRIORIDADES = ['baixa', 'media', 'alta'];

    /** Catálogo central de acções auditáveis: chave => [rótulo legível, tipo para estilo do selo]. */
    private const CATALOGO_ACOES = [
        'transacao_criada'             => ['Transação criada', 'sucesso'],
        'transacao_editada'            => ['Transação editada', 'info'],
        'transacao_eliminada'          => ['Transação eliminada', 'perigo'],
        'transacao_valor_alterado'     => ['Valor da transação alterado', 'aviso'],
        'transacao_tipo_alterado'      => ['Tipo da transação alterado', 'aviso'],
        'categoria_criada'             => ['Categoria criada', 'sucesso'],
        'categoria_editada'            => ['Categoria editada', 'info'],
        'categoria_ativada'            => ['Categoria ativada', 'sucesso'],
        'categoria_desativada'         => ['Categoria desativada', 'aviso'],
        'categoria_eliminada'          => ['Categoria eliminada', 'perigo'],
        'categoria_saida_criada'       => ['Categoria de saída criada', 'sucesso'],
        'categoria_saida_ativada'      => ['Categoria de saída ativada', 'sucesso'],
        'categoria_saida_desativada'   => ['Categoria de saída desativada', 'aviso'],
        'categoria_saida_eliminada'    => ['Categoria de saída eliminada', 'perigo'],
        'metodo_criado'                => ['Método de pagamento criado', 'sucesso'],
        'metodo_editado'               => ['Método de pagamento editado', 'info'],
        'metodo_estado_alterado'       => ['Estado do método alterado', 'aviso'],
        'metodo_eliminado'             => ['Método de pagamento eliminado', 'perigo'],
        'filial_criada'                => ['Filial criada', 'sucesso'],
        'filial_editada'               => ['Filial editada', 'info'],
        'filial_ativada'               => ['Filial ativada', 'sucesso'],
        'filial_desativada'            => ['Filial desativada', 'aviso'],
        'empresa_criada'               => ['Empresa criada', 'sucesso'],
        'empresa_editada'              => ['Empresa editada', 'info'],
        'empresa_ativada'              => ['Empresa ativada', 'sucesso'],
        'empresa_desativada'           => ['Empresa desativada', 'aviso'],
        'usuario_criado'               => ['Utilizador criado', 'sucesso'],
        'usuario_editado'              => ['Utilizador editado', 'info'],
        'usuario_ativado'              => ['Utilizador ativado', 'sucesso'],
        'usuario_desativado'           => ['Utilizador desativado', 'aviso'],
        'usuario_senha_redefinida'     => ['Senha redefinida por administrador', 'perigo'],
        'perfil_atualizado'            => ['Perfil atualizado', 'info'],
        'senha_alterada'               => ['Senha alterada', 'aviso'],
        'senha_recuperacao_solicitada' => ['Recuperação de senha solicitada', 'aviso'],
        'login_sucesso'                => ['Início de sessão', 'sucesso'],
        'login_falhado'                => ['Tentativa de início de sessão falhada', 'perigo'],
        'logout'                       => ['Fim de sessão', 'info'],
        'permissao_negada'             => ['Acesso negado por permissões', 'perigo'],
        'importacao_concluida'         => ['Importação de Excel concluída', 'sucesso'],
        'importacao_revertida'         => ['Importação revertida', 'aviso'],
        'configuracao_alterada'        => ['Configurações alteradas', 'info'],
        'logs_exportados'              => ['Logs exportados', 'info'],
        'logs_visualizados'            => ['Logs visualizados', 'info'],
        'notificacao_lida'             => ['Notificação marcada como lida', 'info'],
        'backup_gerado'                => ['Backup gerado', 'sucesso'],
        'backup_falhou'                => ['Backup falhou', 'perigo'],
        'backup_eliminado'             => ['Backup eliminado', 'perigo'],
        'backup_descarregado'          => ['Backup descarregado', 'info'],
        'backup_agendamento_alterado'  => ['Agendamento de backup alterado', 'info'],
    ];

    /** Catálogo central das tabelas afetadas: chave na base de dados => rótulo legível. */
    private const CATALOGO_TABELAS = [
        'transacoes'        => 'Transações',
        'categorias'        => 'Categorias',
        'categorias_saida'  => 'Categorias de saída',
        'metodos_pagamento' => 'Métodos de pagamento',
        'filiais'           => 'Filiais',
        'empresas'          => 'Empresas',
        'usuarios'          => 'Utilizadores',
        'configuracoes'     => 'Configurações',
        'logs_auditoria'    => 'Logs de auditoria',
        'notificacoes'      => 'Notificações',
        'backups'           => 'Backups',
    ];

    public static function registar(
        string $acao,
        ?string $tabelaAfetada = null,
        ?int $registoId = null,
        ?array $dadosAntigos = null,
        ?array $dadosNovos = null,
        string $prioridade = 'media',
        ?string $motivo = null,
        ?int $usuarioId = null
    ): void {
        try {
            require_once CAMINHO_RAIZ . '/models/LogAuditoria.php';
            (new LogAuditoria())->registar(
                $usuarioId ?? (isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null),
                $acao,
                $tabelaAfetada,
                $registoId,
                self::ocultarSensivel($dadosAntigos),
                self::ocultarSensivel($dadosNovos),
                in_array($prioridade, self::PRIORIDADES, true) ? $prioridade : 'media',
                $motivo
            );
        } catch (Throwable $e) {
            // A auditoria nunca deve interromper o fluxo principal da aplicação.
            error_log('[Auditoria] ' . $e->getMessage());
        }
    }

    /** Rótulo legível de uma acção; devolve algo razoável mesmo para acções fora do catálogo. */
    public static function rotuloAcao(string $acao): string
    {
        return self::CATALOGO_ACOES[$acao][0] ?? ucfirst(str_replace('_', ' ', $acao));
    }

    /** Tipo visual da acção (sucesso, info, aviso, perigo), usado para colorir o selo nos logs. */
    public static function tipoAcao(string $acao): string
    {
        return self::CATALOGO_ACOES[$acao][1] ?? 'info';
    }

    /** Rótulo legível da tabela afetada. */
    public static function rotuloTabela(?string $tabela): string
    {
        if (!$tabela) return '—';
        return self::CATALOGO_TABELAS[$tabela] ?? ucfirst(str_replace('_', ' ', $tabela));
    }

    /** Opções [chave => rótulo] para o filtro de acção, ordenadas alfabeticamente pelo rótulo. */
    public static function opcoesAcoes(): array
    {
        $opcoes = [];
        foreach (self::CATALOGO_ACOES as $chave => [$rotulo, ]) $opcoes[$chave] = $rotulo;
        asort($opcoes);
        return $opcoes;
    }

    /** Opções [chave => rótulo] para o filtro de tabela, ordenadas alfabeticamente pelo rótulo. */
    public static function opcoesTabelas(): array
    {
        $opcoes = self::CATALOGO_TABELAS;
        asort($opcoes);
        return $opcoes;
    }

    private static function ocultarSensivel(?array $dados): ?array
    {
        if ($dados === null) return null;
        foreach (self::CAMPOS_SENSIVEIS as $campo) {
            if (array_key_exists($campo, $dados)) $dados[$campo] = '[OCULTO]';
        }
        return $dados;
    }
}
