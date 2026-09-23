<?php
require_once CAMINHO_RAIZ . '/core/Model.php';
class LogAuditoria extends Model
{
    protected string $tabela = 'logs_auditoria';
    public function registar(?int $usuarioId, string $acao, ?string $tabelaAfetada = null, ?int $registoId = null, ?array $antigos = null, ?array $novos = null, string $prioridade = 'media', ?string $motivo = null): int
    {
        return $this->inserir(['usuario_id'=>$usuarioId,'acao'=>$acao,'tabela_afetada'=>$tabelaAfetada,'registo_id'=>$registoId,
            'dados_antigos'=>$antigos ? json_encode($antigos, JSON_UNESCAPED_UNICODE) : null,
            'dados_novos'=>$novos ? json_encode($novos, JSON_UNESCAPED_UNICODE) : null,
            'prioridade'=>$prioridade, 'motivo'=>$motivo,
            'ip_origem'=>$_SERVER['REMOTE_ADDR'] ?? null,'user_agent'=>substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)]);
    }
    public function paginar(array $filtros, ?int $empresaId, int $pagina, int $porPagina = 25): array
    {
        $sql=' FROM logs_auditoria l LEFT JOIN usuarios u ON u.id=l.usuario_id WHERE 1=1'; $p=[];
        if ($empresaId !== null) { $sql.=' AND u.empresa_id=:empresa_id'; $p['empresa_id']=$empresaId; }
        foreach (['usuario_id'=>'usuario_id','acao'=>'acao','tabela_afetada'=>'tabela_afetada','prioridade'=>'prioridade'] as $entrada=>$coluna) if (!empty($filtros[$entrada])) { $sql.=" AND l.$coluna=:$entrada"; $p[$entrada]=$filtros[$entrada]; }
        if (!empty($filtros['inicio'])) {$sql.=' AND l.criado_em >= :inicio';$p['inicio']=$filtros['inicio'].' 00:00:00';}
        if (!empty($filtros['fim'])) {$sql.=' AND l.criado_em <= :fim';$p['fim']=$filtros['fim'].' 23:59:59';}
        $st=$this->bd->prepare('SELECT COUNT(*)'.$sql);$st->execute($p);$total=(int)$st->fetchColumn();
        $st=$this->bd->prepare('SELECT l.*,u.nome usuario_nome'.$sql.' ORDER BY l.criado_em DESC LIMIT :limite OFFSET :offset'); foreach($p as $k=>$v)$st->bindValue(':'.$k,$v); $st->bindValue(':limite',$porPagina,PDO::PARAM_INT);$st->bindValue(':offset',max(0,($pagina-1)*$porPagina),PDO::PARAM_INT);$st->execute();
        return ['itens'=>$st->fetchAll(),'total'=>$total,'paginas'=>max(1,(int)ceil($total/$porPagina))];
    }
    public function encontrarVisivel(int $id, ?int $empresaId): array|false { $sql='SELECT l.*,u.nome usuario_nome FROM logs_auditoria l LEFT JOIN usuarios u ON u.id=l.usuario_id WHERE l.id=:id';$p=['id'=>$id];if($empresaId!==null){$sql.=' AND u.empresa_id=:empresa_id';$p['empresa_id']=$empresaId;}$s=$this->bd->prepare($sql);$s->execute($p);return $s->fetch(); }
}
