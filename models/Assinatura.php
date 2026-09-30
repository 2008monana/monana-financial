<?php
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/models/Plano.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
require_once CAMINHO_RAIZ . '/helpers/NotificacaoHelper.php';

/**
 * Assinatura — modelo do módulo de assinaturas.
 *
 * O estado de acesso (gratuita/activa/carencia/bloqueada) NUNCA é
 * guardado nem posto em sessão: é calculado a partir das datas em
 * cada pedido (estadoDaEmpresa), para que trocar plano/registar
 * pagamento tenha efeito imediato e o sistema não dependa do cron.
 */
class Assinatura extends Model
{
    protected string $tabela = 'assinaturas';

    /**
     * Calcula o estado efectivo da empresa a partir das datas.
     *
     * Algoritmo (por esta ordem):
     *  1. linha `activa` com bloqueada_manual = 1            -> bloqueada
     *  2. linha `activa` dentro do prazo (ou sem fim)        -> gratuita/activa
     *  3. linha `pendente_pagamento`                          -> carencia ou bloqueada
     *  4. `activa` expirada com maior `fim`                   -> carencia ou bloqueada
     *  5. sem nenhuma linha                                   -> gratuita (aviso no error_log)
     *
     * @return array{estado:string,acesso:bool,plano:?array,inicio:?string,fim:?string,
     *               lim_carencia:?string,segundos_restantes:int,bloqueada_manual:bool,livro:?array}
     */
    public function estadoDaEmpresa(int $empresaId, int $carenciaHoras = 48): array
    {
        $agora = time(); // fuso PHP (Africa/Luanda) — comparado com strtotime das colunas DATETIME
        $linhas = $this->linhasDaEmpresa($empresaId);

        $nulo = [
            'estado' => 'gratuita', 'acesso' => true, 'plano' => null,
            'inicio' => null, 'fim' => null, 'limite_carencia' => null,
            'segundos_restantes' => 0, 'bloqueada_manual' => false, 'linha' => null,
        ];

        if (empty($linhas)) {
            // A migração deve ter evitado este caso; nunca bloquear por falta de dados.
            error_log('[Assinaturas] Empresa #' . $empresaId . ' sem nenhum registo de assinatura.');
            return $nulo;
        }

        $activaVigente = null;   // linha activa com inicio <= agora e (sem fim ou agora <= fim)
        $expirada      = null;   // linha activa já vencida com o maior fim
        $pendente      = null;   // linha pendente_pagamento mais recente
        $manual        = null;   // linha activa bloqueada manualmente

        foreach ($linhas as $l) {
            $estado = $l['estado'];
            if ($estado === 'activa') {
                $ini = $l['inicio'] !== null ? strtotime((string) $l['inicio']) : null;
                $fim = $l['fim'] !== null ? strtotime((string) $l['fim']) : null;
                if ((int) $l['bloqueada_manual'] === 1 && $ini !== null && $ini <= $agora && ($fim === null || $agora <= $fim)) {
                    $manual = $l; // só faz sentido bloquear uma linha vigente
                }
                if ($ini !== null && $ini <= $agora && ($fim === null || $agora <= $fim)) {
                    if ($activaVigente === null || strtotime((string) $l['criado_em']) > strtotime((string) $activaVigente['criado_em'])) {
                        $activaVigente = $l;
                    }
                } elseif ($fim !== null) {
                    if ($expirada === null || $fim > strtotime((string) $expirada['fim'])) {
                        $expirada = $l;
                    }
                }
            } elseif ($estado === 'pendente_pagamento') {
                if ($pendente === null || strtotime((string) $l['criado_em']) > strtotime((string) $pendente['criado_em'])) {
                    $pendente = $l;
                }
            }
        }

        // 1) Bloqueio manual
        if ($manual !== null) {
            return $this->montar('bloqueada', false, $manual, $agora, null, 0, true);
        }

        // 2) Linha activa vigente
        if ($activaVigente !== null) {
            $codigo = $activaVigente['plano_codigo'] ?? '';
            $estado = $codigo === 'gratuito' ? 'gratuita' : 'activa';
            $fimTs  = $activaVigente['fim'] !== null ? strtotime((string) $activaVigente['fim']) : null;
            return $this->montar($estado, true, $activaVigente, $agora, null,
                $fimTs === null ? 0 : max(0, $fimTs - $agora));
        }

        // 3) Pendente de pagamento (carência desde a criação)
        if ($pendente !== null) {
            $limite = $pendente['carencia_ate'] !== null
                ? strtotime((string) $pendente['carencia_ate'])
                : strtotime((string) $pendente['criado_em']) + $carenciaHoras * 3600;
            if ($agora <= $limite) {
                return $this->montar('carencia', true, $pendente, $agora, date('Y-m-d H:i:s', $limite), $limite - $agora);
            }
            return $this->montar('bloqueada', false, $pendente, $agora, date('Y-m-d H:i:s', $limite), 0);
        }

        // 4) Activa expirada -> carência após o fim
        if ($expirada !== null) {
            $fimTs  = strtotime((string) $expirada['fim']);
            $limite = $fimTs + $carenciaHoras * 3600;
            if ($agora <= $limite) {
                return $this->montar('carencia', true, $expirada, $agora, date('Y-m-d H:i:s', $limite), $limite - $agora);
            }
            return $this->montar('bloqueada', false, $expirada, $agora, date('Y-m-d H:i:s', $limite), 0);
        }

        // Sem linha utilizável (ex.: tudo encerrado sem sucessora) — não bloquear.
        error_log('[Assinaturas] Empresa #' . $empresaId . ' sem assinatura utilizável; tratado como gratuito.');
        return $nulo;
    }

    /** Todas as linhas da empresa com o plano em joined. */
    public function linhasDaEmpresa(int $empresaId): array
    {
        $stmt = $this->bd->prepare(
            "SELECT a.*, p.codigo AS plano_codigo, p.nome AS plano_nome, p.duracao_dias, p.preco AS plano_preco
             FROM assinaturas a
             JOIN planos p ON p.id = a.plano_id
             WHERE a.empresa_id = :empresa_id
             ORDER BY a.criado_em, a.id"
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /** Histórico completo (para a ficha da empresa). */
    public function historico(int $empresaId): array
    {
        $stmt = $this->bd->prepare(
            "SELECT a.*, p.nome AS plano_nome, p.codigo AS plano_codigo,
                    u.nome AS criado_por_nome
             FROM assinaturas a
             JOIN planos p ON p.id = a.plano_id
             LEFT JOIN usuarios u ON u.id = a.criado_por
             WHERE a.empresa_id = :empresa_id
             ORDER BY a.criado_em DESC, a.id DESC"
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    public function pagamentos(int $empresaId): array
    {
        $stmt = $this->bd->prepare(
            "SELECT pg.*, p.nome AS plano_nome, u.nome AS registado_por
             FROM assinatura_pagamentos pg
             JOIN assinaturas a ON a.id = pg.assinatura_id
             JOIN planos p ON p.id = a.plano_id
             LEFT JOIN usuarios u ON u.id = pg.criado_por
             WHERE pg.empresa_id = :empresa_id
             ORDER BY pg.data_pagamento DESC, pg.id DESC"
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Troca o plano de uma empresa (operação transaccional).
     *
     * $opcoes:
     *  - plano_id           int   (obrigatório)
     *  - valor              ?float (null = preço do plano; permite desconto negociado)
     *  - pago               bool  (true = pagamento recebido; false = iniciar carência)
     *  - inicio             ?string 'YYYY-MM-DD' (default hoje)
     *  - fim_gratuito       ?string 'YYYY-MM-DD' (só no plano gratuito, opcional)
     *  - metodo/referencia/data_pagamento/observacoes (quando pago)
     *
     * @return array{ok:bool,erro:?string,assistencia?:string}
     */
    public function trocarPlano(int $empresaId, array $opcoes): array
    {
        require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

        $planoModel = new Plano();
        $plano = $planoModel->encontrarPorId((int) ($opcoes['plano_id'] ?? 0));
        if (!$plano) {
            return ['ok' => false, 'erro' => 'Plano inválido ou inexistente.'];
        }

        $valor = $opcoes['valor'] ?? null;
        $valor = $valor === null || $valor === '' ? (float) $plano['preco'] : (float) $valor;
        if ($valor < 0) {
            return ['ok' => false, 'erro' => 'O valor acordado não pode ser negativo.'];
        }

        $pago   = !empty($opcoes['pago']);
        $ehGratuito = $plano['codigo'] === 'gratuito';
        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        $agora  = time();
        $agoraSql = date('Y-m-d H:i:s', $agora);

        $inicioPedido = null;
        if (!empty($opcoes['inicio'])) {
            $inicioPedido = $this->dataValida((string) $opcoes['inicio'], 'Data de início inválida.');
            if ($inicioPedido === null) {
                return ['ok' => false, 'erro' => 'Data de início inválida.'];
            }
        }

        $fimGratuito = null;
        if ($ehGratuito && !empty($opcoes['fim_gratuito'])) {
            $fimGratuito = $this->dataValida((string) $opcoes['fim_gratuito'], null, true);
            if ($fimGratuito === null) {
                return ['ok' => false, 'erro' => 'Data de fim do período gratuito inválida.'];
            }
        }

        $observacoes = isset($opcoes['observacoes']) ? trim((string) $opcoes['observacoes']) : null;
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0) ?: null;

        $bd = $this->bd;
        $novaLinhaId = 0;
        $transacçãoIniciada = false;

        try {
            $bd->beginTransaction();
            $transacçãoIniciada = true;

            // Bloqueia as linhas correntes da empresa (evita duas linhas activas sobrepostas)
            $stmt = $bd->prepare("SELECT id, estado, plano_id, fim, inicio FROM assinaturas WHERE empresa_id = :e FOR UPDATE");
            $stmt->execute(['e' => $empresaId]);
            $correntes = $stmt->fetchAll();

            // Descobre a última assinatura paga relevante para a regra de renovação:
            // se ainda está dentro do prazo OU dentro da carência, o novo período
            // começa no fim do anterior (não se perdem nem se ganham dias).
            $inicioNovo = $inicioPedido ? $inicioPedido . ' 00:00:00' : $agoraSql;
            if (!$ehGratuito && $pago) {
                $fimAnteriorTs = null;
                foreach ($correntes as $c) {
                    if ($c['estado'] !== 'activa' || $c['fim'] === null) continue;
                    $planoAnt = $planoModel->encontrarPorId((int) $c['plano_id']);
                    if (!$planoAnt || $planoAnt['codigo'] === 'gratuito') continue;
                    $f = strtotime((string) $c['fim']);
                    if ($f === false) continue;
                    if ($agora <= $f + $carenciaHoras * 3600) {
                        if ($fimAnteriorTs === null || $f > $fimAnteriorTs) $fimAnteriorTs = $f;
                    }
                }
                if ($fimAnteriorTs !== null) {
                    $inicioNovo = date('Y-m-d H:i:s', $fimAnteriorTs);
                }
            }

            // Encerra todas as linhas correntes (activa/pendente)
            $encerrar = $bd->prepare("UPDATE assinaturas SET estado = 'encerrada' WHERE empresa_id = :e AND estado IN ('activa','pendente_pagamento')");
            $encerrar->execute(['e' => $empresaId]);

            if ($ehGratuito) {
                $fimSql = $fimGratuito !== null ? $fimGratuito . ' 23:59:59' : null;
                $ins = $bd->prepare(
                    "INSERT INTO assinaturas (empresa_id, plano_id, estado, valor_acordado, inicio, fim, carencia_ate, observacoes, criado_por)
                     VALUES (:e, :p, 'activa', :v, :ini, :fim, NULL, :obs, :u)"
                );
                $ins->execute([
                    'e' => $empresaId, 'p' => (int) $plano['id'], 'v' => 0.00,
                    'ini' => $inicioNovo, 'fim' => $fimSql,
                    'obs' => $observacoes ?: 'Mudança para o plano gratuito.', 'u' => $usuarioId,
                ]);
                $novaLinhaId = (int) $bd->lastInsertId();
            } elseif ($pago) {
                $fimTs = strtotime($inicioNovo) + (max(1, (int) $plano['duracao_dias'])) * 86400;
                $fimSql = date('Y-m-d H:i:s', $fimTs);
                $ins = $bd->prepare(
                    "INSERT INTO assinaturas (empresa_id, plano_id, estado, valor_acordado, inicio, fim, carencia_ate, observacoes, criado_por)
                     VALUES (:e, :p, 'activa', :v, :ini, :fim, NULL, :obs, :u)"
                );
                $ins->execute([
                    'e' => $empresaId, 'p' => (int) $plano['id'], 'v' => $valor,
                    'ini' => $inicioNovo, 'fim' => $fimSql,
                    'obs' => $observacoes, 'u' => $usuarioId,
                ]);
                $novaLinhaId = (int) $bd->lastInsertId();

                $dataPag = !empty($opcoes['data_pagamento'])
                    ? $this->dataValida((string) $opcoes['data_pagamento'], null) ?? date('Y-m-d')
                    : date('Y-m-d');
                $insPg = $bd->prepare(
                    "INSERT INTO assinatura_pagamentos (assinatura_id, empresa_id, valor, metodo, referencia, data_pagamento, observacoes, criado_por)
                     VALUES (:a, :e, :v, :m, :r, :d, :o, :u)"
                );
                $insPg->execute([
                    'a' => $novaLinhaId, 'e' => $empresaId, 'v' => $valor,
                    'm' => mb_substr(trim((string) ($opcoes['metodo'] ?? 'Numerário')) ?: 'Numerário', 0, 50),
                    'r' => $opcoes['referencia'] !== null ? mb_substr(trim((string) $opcoes['referencia']), 0, 100) : null,
                    'd' => $dataPag, 'o' => $observacoes, 'u' => $usuarioId,
                ]);
            } else {
                // Pago sem recebimento -> pendente com carência
                $limiteSql = date('Y-m-d H:i:s', $agora + $carenciaHoras * 3600);
                $ins = $bd->prepare(
                    "INSERT INTO assinaturas (empresa_id, plano_id, estado, valor_acordado, inicio, fim, carencia_ate, observacoes, criado_por)
                     VALUES (:e, :p, 'pendente_pagamento', :v, NULL, NULL, :car, :obs, :u)"
                );
                $ins->execute([
                    'e' => $empresaId, 'p' => (int) $plano['id'], 'v' => $valor,
                    'car' => $limiteSql, 'obs' => $observacoes, 'u' => $usuarioId,
                ]);
                $novaLinhaId = (int) $bd->lastInsertId();
            }

            $bd->commit();
        } catch (Throwable $e) {
            if ($transacçãoIniciada && $bd->inTransaction()) {
                $bd->rollBack();
            }
            error_log('[Assinaturas] trocarPlano falhou: ' . $e->getMessage());
            return ['ok' => false, 'erro' => 'Não foi possível aplicar o plano. Tente novamente.'];
        }

        // Fora da transacção: auditoria + notificações (nunca devem rebolar a BD)
        AuditoriaHelper::registar(
            'assinatura_plano_alterado', 'assinaturas', $novaLinhaId,
            ['linhas_encerradas' => count($correntes)],
            ['empresa_id' => $empresaId, 'plano' => $plano['codigo'], 'valor' => $valor,
             'pago' => $pago, 'estado' => $ehGratuito ? 'activa(gratuita)' : ($pago ? 'activa' : 'pendente_pagamento')],
            'media',
            'Plano "' . $plano['nome'] . '" aplicado à empresa #' . $empresaId
        );

        $resumo = ['ok' => true, 'erro' => null, 'assinatura_id' => $novaLinhaId];

        if (!$ehGratuito && !$pago) {
            // Regra 3.3: entrou em carência por mudança para plano pago sem pagamento
            $resumo['aviso'] = 'Carência de ' . $carenciaHoras . 'h iniciada; admins da empresa notificados.';
            AssinaturaHelper::notificarCarencia($empresaId, $novaLinhaId, 'gratuita cessou', $carenciaHoras);
        }

        return $resumo;
    }

    /**
     * Regista um pagamento numa linha `pendente_pagamento`: passa a `activa`,
     * inicio = agora, fim = inicio + duracao_dias.
     */
    public function registarPagamento(int $assinaturaId, array $dados): array
    {
        require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

        $valor = isset($dados['valor']) && $dados['valor'] !== '' ? (float) $dados['valor'] : null;
        if ($valor === null || $valor < 0) {
            return ['ok' => false, 'erro' => 'Valor do pagamento inválido.'];
        }
        $dataPag = !empty($dados['data_pagamento'])
            ? $this->dataValida((string) $dados['data_pagamento'], null)
            : date('Y-m-d');
        if ($dataPag === null) {
            return ['ok' => false, 'erro' => 'Data do pagamento inválida.'];
        }

        $planoModel = new Plano();
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0) ?: null;
        $bd = $this->bd;

        try {
            $bd->beginTransaction();

            $stmt = $bd->prepare("SELECT * FROM assinaturas WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $assinaturaId]);
            $linha = $stmt->fetch();
            if (!$linha) {
                $bd->rollBack();
                return ['ok' => false, 'erro' => 'Assinatura não encontrada.'];
            }
            if ($linha['estado'] !== 'pendente_pagamento') {
                $bd->rollBack();
                return ['ok' => false, 'erro' => 'Só é possível registar pagamento em assinaturas pendentes.'];
            }

            $plano = $planoModel->encontrarPorId((int) $linha['plano_id']);
            if (!$plano) {
                $bd->rollBack();
                return ['ok' => false, 'erro' => 'Plano da assinatura não encontrado.'];
            }

            $agoraSql = date('Y-m-d H:i:s');
            $inicioTs = strtotime($agoraSql);
            $fimSql = date('Y-m-d H:i:s', $inicioTs + max(1, (int) $plano['duracao_dias']) * 86400);

            $upd = $bd->prepare(
                "UPDATE assinaturas SET estado = 'activa', inicio = :ini, fim = :fim, valor_acordado = :v,
                 carencia_ate = NULL, bloqueada_manual = 0 WHERE id = :id"
            );
            $upd->execute(['ini' => $agoraSql, 'fim' => $fimSql, 'v' => $valor, 'id' => $assinaturaId]);

            $insPg = $bd->prepare(
                "INSERT INTO assinatura_pagamentos (assinatura_id, empresa_id, valor, metodo, referencia, data_pagamento, observacoes, criado_por)
                 VALUES (:a, :e, :v, :m, :r, :d, :o, :u)"
            );
            $insPg->execute([
                'a' => $assinaturaId, 'e' => (int) $linha['empresa_id'], 'v' => $valor,
                'm' => mb_substr(trim((string) ($dados['metodo'] ?? 'Numerário')) ?: 'Numerário', 0, 50),
                'r' => isset($dados['referencia']) ? mb_substr(trim((string) $dados['referencia']), 0, 100) : null,
                'd' => $dataPag,
                'o' => isset($dados['observacoes']) ? trim((string) $dados['observacoes']) : null,
                'u' => $usuarioId,
            ]);

            $bd->commit();
        } catch (Throwable $e) {
            if ($bd->inTransaction()) $bd->rollBack();
            error_log('[Assinaturas] registarPagamento falhou: ' . $e->getMessage());
            return ['ok' => false, 'erro' => 'Não foi possível registar o pagamento.'];
        }

        AuditoriaHelper::registar('assinatura_pagamento_registado', 'assinatura_pagamentos', $assinaturaId,
            ['estado' => 'pendente_pagamento'],
            ['estado' => 'activa', 'valor' => $valor, 'metodo' => $dados['metodo'] ?? 'Numerário'],
            'media', 'Pagamento registado na assinatura #' . $assinaturaId);

        AssinaturaHelper::notificarPagamentoRecebido((int) $linha['empresa_id'], $plano['nome']);

        return ['ok' => true, 'erro' => null];
    }

    /** Bloqueio manual / remoção do bloqueio na linha corrente da empresa. */
    public function alternarBloqueioManual(int $empresaId, bool $bloquear): array
    {
        require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

        $bd = $this->bd;
        try {
            $bd->beginTransaction();
            $stmt = $bd->prepare(
                "SELECT * FROM assinaturas WHERE empresa_id = :e AND estado IN ('activa','pendente_pagamento')
                 ORDER BY FIELD(estado,'activa','pendente_pagamento'), criado_em DESC LIMIT 1 FOR UPDATE"
            );
            $stmt->execute(['e' => $empresaId]);
            $linha = $stmt->fetch();
            if (!$linha) {
                $bd->rollBack();
                return ['ok' => false, 'erro' => 'Esta empresa não tem assinatura corrente para bloquear.'];
            }
            $upd = $bd->prepare("UPDATE assinaturas SET bloqueada_manual = :b WHERE id = :id");
            $upd->execute(['b' => $bloquear ? 1 : 0, 'id' => (int) $linha['id']]);
            $bd->commit();
        } catch (Throwable $e) {
            if ($bd->inTransaction()) $bd->rollBack();
            error_log('[Assinaturas] alternarBloqueioManual falhou: ' . $e->getMessage());
            return ['ok' => false, 'erro' => 'Não foi possível alterar o bloqueio.'];
        }

        AuditoriaHelper::registar(
            $bloquear ? 'assinatura_bloqueio_manual' : 'assinatura_bloqueio_removido',
            'assinaturas', (int) $linha['id'],
            ['bloqueada_manual' => (int) $linha['bloqueada_manual']],
            ['bloqueada_manual' => $bloquear ? 1 : 0],
            'alta',
            ($bloquear ? 'Bloqueio manual aplicado à empresa #' : 'Bloqueio manual removido da empresa #') . $empresaId
        );

        if ($bloquear) {
            AssinaturaHelper::garantirAviso((int) $linha['id'], 'bloqueio');
        }

        return ['ok' => true, 'erro' => null];
    }

    /** Cria a primeira assinatura de uma empresa recém-criada (mesma transacção do EmpresasController). */
    public function criarInicial(int $empresaId, int $planoId, float $valor, bool $pago, ?string $inicio, ?string $fimGratuito, ?string $observacoes, ?int $usuarioId): int
    {
        require_once CAMINHO_RAIZ . '/helpers/AssinaturaHelper.php';

        $planoModel = new Plano();
        $plano = $planoModel->encontrarPorId($planoId);
        if (!$plano) {
            throw new RuntimeException('Plano inicial inválido.');
        }
        $ehGratuito = $plano['codigo'] === 'gratuito';
        $agoraSql = $inicio ? $inicio . ' 00:00:00' : date('Y-m-d H:i:s');

        if ($ehGratuito) {
            $id = $this->inserir([
                'empresa_id' => $empresaId, 'plano_id' => $planoId, 'estado' => 'activa',
                'valor_acordado' => 0.00, 'inicio' => $agoraSql,
                'fim' => $fimGratuito !== null ? $fimGratuito . ' 23:59:59' : null,
                'carencia_ate' => null, 'observacoes' => $observacoes ?: 'Assinatura inicial da empresa.',
                'criado_por' => $usuarioId,
            ]);
            return $id;
        }

        if ($pago) {
            $fimSql = date('Y-m-d H:i:s', strtotime($agoraSql) + max(1, (int) $plano['duracao_dias']) * 86400);
            $id = $this->inserir([
                'empresa_id' => $empresaId, 'plano_id' => $planoId, 'estado' => 'activa',
                'valor_acordado' => $valor, 'inicio' => $agoraSql, 'fim' => $fimSql,
                'carencia_ate' => null, 'observacoes' => $observacoes, 'criado_por' => $usuarioId,
            ]);
            $this->registarPagamentoLinha($id, $empresaId, $valor, 'Numerário', null, date('Y-m-d'), $observacoes, $usuarioId);
            return $id;
        }

        $carenciaHoras = AssinaturaHelper::carenciaHoras();
        $id = $this->inserir([
            'empresa_id' => $empresaId, 'plano_id' => $planoId, 'estado' => 'pendente_pagamento',
            'valor_acordado' => $valor, 'inicio' => null, 'fim' => null,
            'carencia_ate' => date('Y-m-d H:i:s', time() + $carenciaHoras * 3600),
            'observacoes' => $observacoes, 'criado_por' => $usuarioId,
        ]);
        return $id;
    }

    /** Insere linha de pagamento (usado dentro de transacções externas). */
    public function registarPagamentoLinha(int $assinaturaId, int $empresaId, float $valor, string $metodo, ?string $referencia, string $dataPagamento, ?string $observacoes, ?int $usuarioId): void
    {
        $stmt = $this->bd->prepare(
            "INSERT INTO assinatura_pagamentos (assinatura_id, empresa_id, valor, metodo, referencia, data_pagamento, observacoes, criado_por)
             VALUES (:a, :e, :v, :m, :r, :d, :o, :u)"
        );
        $stmt->execute([
            'a' => $assinaturaId, 'e' => $empresaId, 'v' => $valor,
            'm' => mb_substr($metodo !== '' ? $metodo : 'Numerário', 0, 50),
            'r' => $referencia !== null ? mb_substr($referencia, 0, 100) : null,
            'd' => $dataPagamento, 'o' => $observacoes, 'u' => $usuarioId,
        ]);
    }

    /** Lista paginada para a página de lista (Super Admin), com filtros opcionais. */
    public function listarParaAdmin(array $filtros = []): array
    {
        $sql = "SELECT a.*, e.nome AS empresa_nome, e.nif AS empresa_nif, e.telefone AS empresa_telefone,
                       p.nome AS plano_nome, p.codigo AS plano_codigo
                FROM assinaturas a
                JOIN empresas e ON e.id = a.empresa_id
                JOIN planos p ON p.id = a.plano_id
                WHERE a.estado IN ('activa','pendente_pagamento')";
        $params = [];

        if (!empty($filtros['plano_id'])) {
            $sql .= " AND a.plano_id = :plano_id";
            $params['plano_id'] = (int) $filtros['plano_id'];
        }
        if (!empty($filtros['pesquisa'])) {
            $sql .= " AND (e.nome LIKE :pesq OR e.nif LIKE :pesq)";
            $params['pesq'] = '%' . $filtros['pesquisa'] . '%';
        }

        $sql .= " GROUP BY a.empresa_id"; // uma linha por empresa (a mais recente entre correntes)
        $sql .= " ORDER BY a.criado_em DESC, a.id DESC";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Receita (soma dos pagamentos) do mês corrente. */
    public function receitaMes(): float
    {
        $stmt = $this->bd->query(
            "SELECT COALESCE(SUM(valor),0) FROM assinatura_pagamentos
             WHERE data_pagamento >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        return (float) $stmt->fetchColumn();
    }

    /** Total de pagamentos de uma empresa (para a vista "minha"). */
    public function totalPagoEmpresa(int $empresaId): float
    {
        $stmt = $this->bd->prepare("SELECT COALESCE(SUM(valor),0) FROM assinatura_pagamentos WHERE empresa_id = :e");
        $stmt->execute(['e' => $empresaId]);
        return (float) $stmt->fetchColumn();
    }

    private function montar(string $estado, bool $acesso, array $linha, int $agora, ?string $limite, int $segundosRestantes, bool $manual = false): array
    {
        return [
            'estado' => $estado,
            'acesso' => $acesso,
            'plano' => [
                'id' => (int) $linha['plano_id'],
                'codigo' => $linha['plano_codigo'] ?? '',
                'nome' => $linha['plano_nome'] ?? '',
                'duracao_dias' => isset($linha['duracao_dias']) ? $linha['duracao_dias'] : null,
            ],
            'inicio' => $linha['inicio'],
            'fim' => $linha['fim'],
            'limite_carencia' => $limite,
            'segundos_restantes' => max(0, $segundosRestantes),
            'bloqueada_manual' => $manual,
            'linha' => $linha,
        ];
    }

    /** Valida 'YYYY-MM-DD'; devolve a data normalizada ou null. */
    private function dataValida(string $data, ?string $erro, bool $permitirFutura = true): ?string
    {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        if (!$d || $d->format('Y-m-d') !== $data) {
            return null;
        }
        return $d->format('Y-m-d');
    }
}
