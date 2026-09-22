<?php
require_once CAMINHO_RAIZ.'/core/Model.php';
class Configuracao extends Model {
 protected string $tabela='configuracoes';
 public function obterTodas(?int $empresaId): array {$s=$this->bd->prepare('SELECT chave,valor,tipo FROM configuracoes WHERE empresa_id '.($empresaId===null?'IS NULL':'=:empresa_id'));$s->execute($empresaId===null?[]:['empresa_id'=>$empresaId]);$r=[];foreach($s->fetchAll() as $l)$r[$l['chave']]=$l['valor'];return $r;}
 public function guardar(?int $empresaId,string $chave,string $valor,string $tipo='texto'): void {$sql='INSERT INTO configuracoes (empresa_id,chave,valor,tipo) VALUES (:empresa,:chave,:valor,:tipo) ON DUPLICATE KEY UPDATE valor=VALUES(valor),tipo=VALUES(tipo)';$s=$this->bd->prepare($sql);$s->execute(['empresa'=>$empresaId,'chave'=>$chave,'valor'=>$valor,'tipo'=>$tipo]);}
}
