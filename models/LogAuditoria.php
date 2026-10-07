<?php
require_once CAMINHO_RAIZ . '/core/Model.php';
require_once CAMINHO_RAIZ . '/helpers/AuditoriaHelper.php';
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
    /**
     * As empresas não podem ver as ações do Super Administrador nos seus
     * registos. Este filtro é aplicado automaticamente em todas as listagens,
     * exportações e pesquisas (mesmo com o utilizador desligado da sessão).
     */
    private function idsSuperAdmin(): array
    {
        static $ids = null;
        if ($ids === null) {
            $ids = array_map('intval', $this->bd->query("SELECT id FROM usuarios WHERE perfil = 'super_admin'")->fetchAll(PDO::FETCH_COLUMN)) ?: [0];
        }
        return $ids;
    }

    private function clauseExcluirSuperAdmin(string &$sql, array &$p): void
    {
        $ids = $this->idsSuperAdmin();
        $ph = [];
        foreach ($ids as $i => $id) { $k = 'exc_sa_'.$i; $ph[] = ':'.$k; $p[$k] = $id; }
        $sql .= ' AND (l.usuario_id IS NULL OR l.usuario_id NOT IN ('.implode(',', $ph).'))';
    }

    public function paginar(array $filtros, ?int $empresaId, int $pagina, int $porPagina = 25): array
    {
        $sql=' FROM logs_auditoria l LEFT JOIN usuarios u ON u.id=l.usuario_id WHERE 1=1'; $p=[];
        $this->clauseExcluirSuperAdmin($sql, $p);
        if ($empresaId !== null) { $sql.=' AND u.empresa_id=:empresa_id'; $p['empresa_id']=$empresaId; }
        foreach (['usuario_id'=>'usuario_id','acao'=>'acao'] as $entrada=>$coluna) if (!empty($filtros[$entrada])) { $sql.=" AND l.$coluna=:$entrada"; $p[$entrada]=$filtros[$entrada]; }
        if (!empty($filtros['modulo'])) {
            $rotulosModulos = AuditoriaHelper::prefixosModulos();
            $nomeModulo = $rotulosModulos[$filtros['modulo']] ?? null;
            $condicoes = ['l.acao LIKE :modulo_prefixo'];
            $p['modulo_prefixo'] = $filtros['modulo'].'_%';
            if ($nomeModulo !== null) {
                $placeholders = [];
                foreach (array_keys(AuditoriaHelper::tabelasPorModulo($nomeModulo)) as $i => $tabela) {
                    $chave = 'modulo_tab_' . $i;
                    $placeholders[] = ':'.$chave;
                    $p[$chave] = $tabela;
                }
                if ($placeholders) $condicoes[] = 'l.tabela_afetada IN ('.implode(',', $placeholders).')';
            }
            $sql .= ' AND ('.implode(' OR ', $condicoes).')';
        }
        if (!empty($filtros['busca'])) { $sql.=' AND (l.acao LIKE :busca OR l.motivo LIKE :busca OR l.ip_origem LIKE :busca OR u.nome LIKE :busca)'; $p['busca']='%'.$filtros['busca'].'%'; }
        if (!empty($filtros['inicio'])) {$sql.=' AND l.criado_em >= :inicio';$p['inicio']=$filtros['inicio'].' 00:00:00';}
        if (!empty($filtros['fim'])) {$sql.=' AND l.criado_em <= :fim';$p['fim']=$filtros['fim'].' 23:59:59';}
        $st=$this->bd->prepare('SELECT COUNT(*)'.$sql);$st->execute($p);$total=(int)$st->fetchColumn();
        $st=$this->bd->prepare('SELECT l.*,u.nome usuario_nome'.$sql.' ORDER BY l.criado_em DESC LIMIT :limite OFFSET :offset'); foreach($p as $k=>$v)$st->bindValue(':'.$k,$v); $st->bindValue(':limite',$porPagina,PDO::PARAM_INT);$st->bindValue(':offset',max(0,($pagina-1)*$porPagina),PDO::PARAM_INT);$st->execute();
        return ['itens'=>$st->fetchAll(),'total'=>$total,'paginas'=>max(1,(int)ceil($total/$porPagina))];
    }
    public function encontrarVisivel(int $id, ?int $empresaId): array|false
    {
        $sql='SELECT l.*,u.nome usuario_nome FROM logs_auditoria l LEFT JOIN usuarios u ON u.id=l.usuario_id WHERE l.id=:id';
        $p=['id'=>$id];
        $this->clauseExcluirSuperAdmin($sql, $p);
        if($empresaId!==null){$sql.=' AND u.empresa_id=:empresa_id';$p['empresa_id']=$empresaId;}
        $s=$this->bd->prepare($sql);$s->execute($p);return $s->fetch();
    }

    /** IDs de utilizadores super_admin (usado por listagens de outras áreas para os ocultar das empresas). */
    public static function idsSuperAdminPublicos(): array
    {
        static $ids = null;
        if ($ids === null) {
            $bd = Database::obterLigacao();
            $ids = array_map('intval', $bd->query("SELECT id FROM usuarios WHERE perfil = 'super_admin'")->fetchAll(PDO::FETCH_COLUMN));
        }
        return $ids;
    }
}
