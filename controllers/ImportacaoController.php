<?php
require_once CAMINHO_RAIZ.'/core/Controller.php';require_once CAMINHO_RAIZ.'/middleware/AuthMiddleware.php';require_once CAMINHO_RAIZ.'/helpers/SegurancaHelper.php';require_once CAMINHO_RAIZ.'/helpers/AuditoriaHelper.php';require_once CAMINHO_RAIZ.'/helpers/ImportadorModeloDiario.php';require_once CAMINHO_RAIZ.'/models/Filial.php';
class ImportacaoController extends Controller {
 public function __construct(){(new AuthMiddleware())->exigirPerfil(['super_admin','admin_empresa']);}
 private function empresa(): int {if(($_SESSION['usuario_perfil']??'')==='super_admin' && !empty($_REQUEST['empresa_id']))return (int)$_REQUEST['empresa_id'];return (int)($_SESSION['empresa_id']??0);}
 private function csrf(): bool{return SegurancaHelper::validarTokenCSRF($_POST['csrf_token']??'');}
 public function index():void{$this->renderizar('importacao/index',['tituloPagina'=>'Importar Excel','paginaAtiva'=>'importacao','csrf_token'=>SegurancaHelper::gerarTokenCSRF()]);}
 public function upload():void {if(!$this->csrf()||empty($_FILES['ficheiro'])){$this->falhar('Pedido de importação inválido.');return;}$f=$_FILES['ficheiro'];$ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));if($f['error']!==UPLOAD_ERR_OK||$f['size']>10*1024*1024||!in_array($ext,['xlsx','xls','csv'],true)){$this->falhar('Seleccione um ficheiro .xlsx, .xls ou .csv com no máximo 10 MB.');return;}if(!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')){$this->falhar('PhpSpreadsheet não está instalado. Execute composer install.');return;}$dir=CAMINHO_UPLOADS.'/importacoes';if(!is_dir($dir))mkdir($dir,0750,true);$nome=bin2hex(random_bytes(12)).'.'.$ext;$destino=$dir.'/'.$nome;if(!move_uploaded_file($f['tmp_name'],$destino)){$this->falhar('Não foi possível guardar o ficheiro enviado.');return;}$planilha=\PhpOffice\PhpSpreadsheet\IOFactory::load($destino)->getActiveSheet();$linhas=$planilha->toArray(null,true,true,true);$cabecalhos=array_shift($linhas);$_SESSION['importacao_pendente']=['ficheiro'=>$destino,'nome_original'=>$f['name'],'cabecalhos'=>array_values($cabecalhos),'linhas'=>array_slice(array_values($linhas),0,5000)];$this->renderizar('importacao/mapeamento',['tituloPagina'=>'Mapear colunas','paginaAtiva'=>'importacao','cabecalhos'=>array_values($cabecalhos),'preview'=>array_slice($linhas,0,5),'csrf_token'=>SegurancaHelper::gerarTokenCSRF()]);}
 public function mapearColunas(): void {
  $pendente = $_SESSION['importacao_pendente'] ?? null;
  if (!$pendente) { $this->falhar('Envie primeiro um ficheiro para importar.'); return; }
  $this->renderizar('importacao/mapeamento', ['tituloPagina'=>'Mapear colunas','paginaAtiva'=>'importacao','cabecalhos'=>$pendente['cabecalhos'],'preview'=>array_slice($pendente['linhas'],0,5),'csrf_token'=>SegurancaHelper::gerarTokenCSRF()]);
 }
 public function validar(): void {
  if (!$this->csrf() || empty($_SESSION['importacao_pendente'])) { $this->json(['sucesso'=>false,'mensagem'=>'A sessão de importação expirou.'], 422); }
  $mapa=$_POST['mapa']??[]; $erros=[]; foreach(array_slice($_SESSION['importacao_pendente']['linhas'],0,1000) as $indice=>$linha){$dados=[];foreach($mapa as $campo=>$coluna)$dados[$campo]=trim((string)($linha[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex((int)$coluna+1)]??''));$dados=$this->normalizarLinha($dados);if($this->invalida($dados,$this->empresa()))$erros[]=['linha'=>$indice+2,'mensagem'=>'Dados obrigatórios inválidos ou erro de fórmula Excel.'];}
  $this->json(['sucesso'=>empty($erros),'erros'=>$erros]);
 }
 public function processar():void {if(!$this->csrf()||empty($_SESSION['importacao_pendente'])){$this->falhar('A sessão de importação expirou.');return;}$p=$_SESSION['importacao_pendente'];$mapa=$_POST['mapa']??[];$obrig=['data_transacao','valor','tipo','filial_id','categoria_id'];foreach($obrig as $c)if(!array_key_exists($c,$mapa)||$mapa[$c]===''||$mapa[$c]===null){$this->falhar('Mapeie o campo obrigatório: '.$c.'.');return;}$ini=microtime(true);$bd=Database::obterLigacao();$empresa=$this->empresa();$token=bin2hex(random_bytes(16));$bd->beginTransaction();try{$s=$bd->prepare('INSERT INTO importacoes (empresa_id,usuario_id,nome_arquivo,total_linhas,status,token_reversao) VALUES (:e,:u,:n,:t,"processando",:token)');$s->execute(['e'=>$empresa,'u'=>(int)$_SESSION['usuario_id'],'n'=>$p['nome_original'],'t'=>count($p['linhas']),'token'=>$token]);$id=(int)$bd->lastInsertId();$ins=$bd->prepare('INSERT INTO transacoes (empresa_id,filial_id,categoria_id,usuario_id,tipo,descricao,valor,metodo_pagamento,data_transacao) VALUES (:empresa,:filial,:categoria,:usuario,:tipo,:descricao,:valor,:metodo,:data)');$ok=0;$falhas=0;foreach($p['linhas'] as $n=>$linha){$d=[];foreach($mapa as $campo=>$indice)$d[$campo]=trim((string)($linha[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex((int)$indice+1)]??''));$d=$this->normalizarLinha($d);if($this->invalida($d,$empresa)){$falhas++;continue;}$ins->execute(['empresa'=>$empresa,'filial'=>(int)$d['filial_id'],'categoria'=>(int)$d['categoria_id'],'usuario'=>(int)$_SESSION['usuario_id'],'tipo'=>$d['tipo'],'descricao'=>$d['descricao']??'Importação Excel','valor'=>(float)$d['valor'],'metodo'=>($d['metodo_pagamento']??'')!==''?$d['metodo_pagamento']:'numerario','data'=>$d['data_transacao']]);$ok++;}$bd->prepare('UPDATE importacoes SET linhas_importadas=?,linhas_falhadas=?,status="concluida",tempo_processamento_ms=? WHERE id=?')->execute([$ok,$falhas,(int)((microtime(true)-$ini)*1000),$id]);$bd->commit();AuditoriaHelper::registar('importacao_concluida','importacoes',$id,null,['linhas_importadas'=>$ok,'linhas_falhadas'=>$falhas]);unset($_SESSION['importacao_pendente']);definirFlash('sucesso',"Importação concluída: $ok linhas importadas e $falhas rejeitadas.");$this->redirecionar('importacao/historico');}catch(Throwable $e){$bd->rollBack();$this->falhar('A importação foi anulada por um erro crítico.');}}
 public function historico():void{$s=Database::obterLigacao()->prepare('SELECT * FROM importacoes WHERE empresa_id=:e ORDER BY criado_em DESC');$s->execute(['e'=>$this->empresa()]);$this->renderizar('importacao/historico',['tituloPagina'=>'Histórico de Importações','paginaAtiva'=>'importacao','importacoes'=>$s->fetchAll(),'csrf_token'=>SegurancaHelper::gerarTokenCSRF()]);}
 public function reverter(string $id):void{if(!$this->csrf()){$this->falhar('Token de segurança inválido.');return;}$bd=Database::obterLigacao();$s=$bd->prepare('SELECT * FROM importacoes WHERE id=:id AND empresa_id=:e');$s->execute(['id'=>(int)$id,'e'=>$this->empresa()]);$i=$s->fetch();if(!$i||$i['status']!=='concluida'){$this->falhar('Importação não disponível para reversão.');return;}$bd->prepare('UPDATE importacoes SET status="revertida" WHERE id=?')->execute([$i['id']]);AuditoriaHelper::registar('importacao_revertida','importacoes',(int)$i['id'],$i,null);definirFlash('sucesso','A importação foi marcada como revertida.');$this->redirecionar('importacao/historico');}

 // =============================================
 // MODELO "RELATÓRIO DIÁRIO DE FINANÇAS" (várias folhas, várias transações por dia)
 // =============================================
 public function modelo(): void { $this->renderizar('importacao/modelo', ['tituloPagina'=>'Importar Relatório Diário','paginaAtiva'=>'importacao','csrf_token'=>SegurancaHelper::gerarTokenCSRF()]); }

 public function modeloUpload(): void {
  if (!$this->csrf() || empty($_FILES['ficheiro'])) { $this->falharModelo('Pedido de importação inválido.'); return; }
  $f = $_FILES['ficheiro']; $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 15 * 1024 * 1024 || !in_array($ext, ['xlsx', 'xls'], true)) { $this->falharModelo('Selecione um ficheiro .xlsx ou .xls com no máximo 15 MB.'); return; }
  if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) { $this->falharModelo('PhpSpreadsheet não está instalado. Execute composer install.'); return; }
  $dir = CAMINHO_UPLOADS . '/importacoes'; if (!is_dir($dir)) mkdir($dir, 0750, true);
  $nome = bin2hex(random_bytes(12)) . '.' . $ext; $destino = $dir . '/' . $nome;
  if (!move_uploaded_file($f['tmp_name'], $destino)) { $this->falharModelo('Não foi possível guardar o ficheiro enviado.'); return; }
  try { $plano = (new ImportadorModeloDiario($destino))->analisar(); }
  catch (Throwable $e) { $this->falharModelo('Não foi possível ler o ficheiro: ' . $e->getMessage()); return; }
  if (empty($plano['folhas'])) { $this->falharModelo('Não foi possível reconhecer nenhuma folha com o formato esperado (coluna de datas + colunas de receita/despesa).'); return; }
  $_SESSION['importacao_modelo_pendente'] = ['ficheiro' => $destino, 'nome_original' => $f['name'], 'plano' => $plano];
  $filiais = (new Filial())->porEmpresaTodas($this->empresa());
  $this->renderizar('importacao/modelo-revisao', ['tituloPagina' => 'Rever importação', 'paginaAtiva' => 'importacao', 'folhas' => $plano['folhas'], 'filiais' => $filiais, 'csrf_token' => SegurancaHelper::gerarTokenCSRF()]);
 }

 public function modeloConfirmar(): void {
  if (!$this->csrf() || empty($_SESSION['importacao_modelo_pendente'])) { $this->falharModelo('A sessão de importação expirou. Envie o ficheiro novamente.'); return; }
  $pendente = $_SESSION['importacao_modelo_pendente'];
  $folhas = $pendente['plano']['folhas'];
  $incluir = $_POST['incluir'] ?? []; // incluir[NomeDaFolha][indiceColuna] = '1'
  $filialPorFolha = $_POST['filial'] ?? []; // filial[NomeDaFolha] = id

  foreach ($folhas as &$folha) {
   foreach ($folha['colunas'] as &$col) {
    $col['incluir'] = !empty($incluir[$folha['nome']][$col['indice']]);
   }
   unset($col);
  }
  unset($folha);

  $filiaisPorFolha = [];
  foreach ($filialPorFolha as $nomeFolha => $id) { if ((int) $id > 0) $filiaisPorFolha[$nomeFolha] = (int) $id; }

  if (empty($filiaisPorFolha)) { $this->falharModelo('Escolha a filial de pelo menos uma folha para importar.'); return; }

  $bd = Database::obterLigacao(); $empresa = $this->empresa(); $ini = microtime(true);
  $bd->beginTransaction();
  try {
   $importador = new ImportadorModeloDiario($pendente['ficheiro']);
   $resultado = $importador->executar($folhas, $filiaisPorFolha, $empresa, (int) $_SESSION['usuario_id'], $bd);
   $s = $bd->prepare('INSERT INTO importacoes (empresa_id,usuario_id,nome_arquivo,total_linhas,linhas_importadas,linhas_falhadas,status,tempo_processamento_ms) VALUES (:e,:u,:n,:t,:t,0,"concluida",:ms)');
   $s->execute(['e' => $empresa, 'u' => (int) $_SESSION['usuario_id'], 'n' => $pendente['nome_original'], 't' => $resultado['transacoes_inseridas'], 'ms' => (int) ((microtime(true) - $ini) * 1000)]);
   $id = (int) $bd->lastInsertId();
   $bd->commit();
   AuditoriaHelper::registar('importacao_concluida', 'importacoes', $id, null, $resultado, 'alta');
   unset($_SESSION['importacao_modelo_pendente']);
   definirFlash('sucesso', "Importação concluída: {$resultado['transacoes_inseridas']} transações criadas, {$resultado['categorias_criadas']} categorias novas.");
   $this->redirecionar('importacao/historico');
  } catch (Throwable $e) {
   $bd->rollBack();
   $this->falharModelo('A importação foi anulada por um erro: ' . $e->getMessage());
  }
 }

 private function falharModelo(string $m): void { definirFlash('erro', $m); $this->redirecionar('importacao/modelo'); }
 private function invalida(array $d,int $empresa):bool{return preg_match('/#(REF!|DIV\/0!|VALUE!|N\/A)/i',implode(' ',$d))||!ValidacaoHelper::data($d['data_transacao']??'')||!is_numeric($d['valor']??'')||(float)($d['valor']??-1)<0||!in_array($d['tipo']??'',['venda','compra','custo','devolucao'],true);}
 /** Normaliza os campos sensíveis de uma linha da planilha (data, valor, tipo, método) antes de validar/gravar, para aceitar os formatos típicos de planilhas financeiras angolanas. */
 private function normalizarLinha(array $d): array {
  if (array_key_exists('data_transacao',$d)) $d['data_transacao']=$this->normalizarData($d['data_transacao']);
  if (array_key_exists('valor',$d)) { $v=$this->normalizarValor($d['valor']); $d['valor']=$v===null?'':$v; }
  if (array_key_exists('tipo',$d)) $d['tipo']=$this->normalizarEnum((string)$d['tipo'],['venda','compra','custo','devolucao']);
  if (array_key_exists('metodo_pagamento',$d) && trim((string)$d['metodo_pagamento'])!=='') { $m=$this->normalizarEnum((string)$d['metodo_pagamento'],['numerario','transferencia','tpa','outro']); $d['metodo_pagamento']=$m!==''?$m:'outro'; }
  return $d;
 }
 /** Aceita datas já em Y-m-d, formato d/m/Y ou d-m-Y, e datas seriais do Excel. Devolve '' se não conseguir interpretar. */
 private function normalizarData($v): string {
  $v=trim((string)$v);
  if ($v==='') return '';
  if (is_numeric($v)) { try { return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$v)->format('Y-m-d'); } catch (Throwable $e) { return ''; } }
  if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/',$v,$m)) return sprintf('%04d-%02d-%02d',$m[1],$m[2],$m[3]);
  if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})#',$v,$m)) return sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1]);
  $ts=strtotime($v);
  return $ts?date('Y-m-d',$ts):'';
 }
 /** Aceita "1.234,56" (formato angolano/europeu), "1,234.56" (formato americano), símbolos de moeda e espaços. Devolve null se não for um valor válido. */
 private function normalizarValor($v): ?float {
  $v=trim((string)$v);
  if ($v==='') return null;
  $v=preg_replace('/[^0-9,.\-]/','',$v);
  if ($v===''||$v==='-') return null;
  $ultimaVirgula=strrpos($v,',');
  $ultimoPonto=strrpos($v,'.');
  if ($ultimaVirgula!==false && $ultimoPonto!==false) {
   if ($ultimaVirgula>$ultimoPonto) { $v=str_replace(',','#',$v); $v=str_replace('.','',$v); $v=str_replace('#','.',$v); }
   else { $v=str_replace(',','',$v); }
  } elseif ($ultimaVirgula!==false) {
   $v=str_replace(',','.',$v);
  }
  return is_numeric($v)?(float)$v:null;
 }
 /** Compara ignorando maiúsculas/minúsculas e acentos; devolve a opção reconhecida ou ''. */
 private function normalizarEnum(string $v,array $opcoes): string {
  $v=trim($v);
  $v=function_exists('mb_strtolower')?mb_strtolower($v,'UTF-8'):strtolower($v);
  $v=strtr($v,['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c','Á'=>'a','À'=>'a','Â'=>'a','Ã'=>'a','É'=>'e','Ê'=>'e','Í'=>'i','Ó'=>'o','Ô'=>'o','Õ'=>'o','Ú'=>'u','Ç'=>'c']);
  return in_array($v,$opcoes,true)?$v:'';
 }
 private function falhar(string $m):void{definirFlash('erro',$m);$this->redirecionar('importacao/index');}
}
