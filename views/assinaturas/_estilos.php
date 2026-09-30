<?php
/**
 * Estilos do módulo de Assinaturas.
 * Inclua com: <?php require CAMINHO_RAIZ . '/views/assinaturas/_estilos.php'; ?>
 * (mesmo padrão de views/configuracoes/_estilos.php). Não altera estilos existentes;
 * define apenas classes novas com prefixo `assin-` e os modificadores novos de `.status-badge`.
 */
?>
<style>
/* ---- Selos de estado da assinatura (modificadores de .status-badge) ---- */
.status-badge.assin-gratuita { background:#e0f2fe; color:#0369a1; }
.status-badge.assin-gratuita i { font-size:10px; }
.status-badge.assin-activa   { background:#dcfce7; color:#15803d; }
.status-badge.assin-activa i { font-size:10px; }
.status-badge.assin-carencia { background:#fef3c7; color:#b45309; }
.status-badge.assin-carencia i { font-size:10px; }
.status-badge.assin-bloqueada{ background:#fee2e2; color:#b91c1c; }
.status-badge.assin-bloqueada i { font-size:10px; }
.status-badge.assin-xl { padding:8px 20px; font-size:14px; border-radius:26px; }

/* ---- Ícone laranja para os cartões de estatística ---- */
.stat-icon.orange { background: linear-gradient(135deg, var(--orange), var(--orange-dark)); }

/* ---- Tabela de assinaturas ---- */
.assin-tabela-wrap{background:#fff;border:1px solid var(--border);border-radius:14px;padding:0;overflow:hidden;margin-bottom:18px}
.assin-tabela{width:100%;border-collapse:collapse;font-size:13px}
.assin-tabela thead th{text-align:left;padding:12px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);background:#fafbfc;border-bottom:1px solid var(--border);white-space:nowrap}
.assin-tabela tbody td{padding:12px 14px;border-bottom:1px solid #f1f3f6;vertical-align:middle}
.assin-tabela tbody tr:last-child td{border-bottom:none}
.assin-tabela .col-acoes{white-space:nowrap;text-align:right}
.assin-linha-carencia td{background:#fffbeb}
.assin-linha-carencia td:first-child{box-shadow:inset 3px 0 0 var(--orange)}
.assin-linha-bloqueada td{background:#fef2f2}
.assin-linha-bloqueada td:first-child{box-shadow:inset 3px 0 0 var(--red)}
.assin-avatar{width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--navy),var(--navy-light));color:#fff;display:inline-flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:700;font-size:13px;flex-shrink:0}
.assin-empresa-cell{display:flex;align-items:center;gap:10px;min-width:180px}
.assin-empresa-cell .nome{font-weight:600;color:var(--ink);display:block;line-height:1.3}
.assin-empresa-cell .nif{font-size:11px;color:var(--muted)}
.assin-sub{display:block;font-size:11px;color:var(--muted);margin-top:2px}
.assin-sub.laranja{color:var(--orange-dark);font-weight:600}
.tabela-scroll{overflow-x:auto}
.paginacao{display:flex;gap:6px;justify-content:flex-end;padding:12px 16px;flex-wrap:wrap}
.paginacao a{min-width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);border-radius:8px;font-size:12px;font-weight:600;color:var(--ink);text-decoration:none;background:#fff}
.paginacao a.ativo{background:var(--navy);border-color:var(--navy);color:#fff}

/* ---- Ficha da empresa ---- */
.assin-resumo{display:flex;align-items:center;gap:18px;flex-wrap:wrap;background:#fff;border:1px solid var(--border);border-radius:14px;padding:22px 24px;margin-bottom:20px}
.assin-resumo .avatar-lg{width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,var(--navy),var(--navy-light));color:#fff;display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;font-size:22px;flex-shrink:0}
.assin-resumo h2{margin:0;font-family:'Sora',sans-serif;font-size:20px;color:var(--navy-deep)}
.assin-resumo .meta{font-size:12px;color:var(--muted);margin-top:4px;display:flex;gap:14px;flex-wrap:wrap}
.assin-resumo .selo-dir{margin-left:auto}
.assin-mini-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:20px}
.assin-mini .valor{font-family:'Sora',sans-serif;font-size:18px;font-weight:700;color:var(--navy-deep)}
.assin-mini .legenda{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px}
.assin-grelha{display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start}
@media(max-width:992px){.assin-grelha{grid-template-columns:1fr}}
.assin-cartao-acoes{display:flex;flex-direction:column;gap:10px}
.assin-cartao-acoes .btn{justify-content:center}

/* ---- Grelha de planos ---- */
.assin-planos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px}
.assin-plano-card{position:relative;background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:var(--transition)}
.assin-plano-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg)}
.assin-plano-cabecalho{display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border);background:#fafbfc}
.assin-plano-cabecalho .quadrado{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--navy),var(--navy-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px}
.assin-plano-cabecalho h3{margin:0;font-size:15px;font-weight:600}
.assin-plano-corpo{padding:18px}
.assin-preco{font-family:'Sora',sans-serif;font-size:28px;font-weight:800;color:var(--navy-deep)}
.assin-duracao{font-size:13px;color:var(--muted);margin-top:2px}
.assin-rodape-form{display:flex;gap:8px;padding:14px 18px;border-top:1px solid var(--border);background:#fcfcfd;flex-wrap:wrap}
.assin-rodape-form input{flex:1;min-width:70px;padding:8px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:'Inter',sans-serif}
.assin-rodape-form input:focus{outline:none;border-color:var(--green)}
.assin-fita{position:absolute;top:12px;right:-30px;background:var(--green);color:#fff;font-size:10px;font-weight:700;padding:4px 36px;transform:rotate(45deg);box-shadow:0 2px 6px rgba(0,0,0,.15)}
.assin-nota{font-size:12px;color:var(--muted);margin:0 0 18px}

/* ---- Minha assinatura ---- */
.assin-hero{background:#fff;border:1px solid var(--border);border-radius:14px;padding:26px 28px;margin-bottom:20px;display:flex;gap:26px;align-items:center;flex-wrap:wrap}
.assin-hero .dias{font-family:'Sora',sans-serif;font-size:36px;font-weight:800;color:var(--navy-deep);line-height:1}
.assin-hero .dias small{display:block;font-family:'Inter',sans-serif;font-size:12px;font-weight:600;color:var(--muted);margin-top:4px}
.assin-progresso{height:8px;border-radius:999px;background:#eef1f6;overflow:hidden;margin-top:14px;width:100%}
.assin-progresso span{display:block;height:100%;border-radius:999px;transition:width .3s}
.assin-alerta-carencia{background:#fef3c7;border:1px solid #fde68a;border-left:4px solid var(--orange);color:#b45309;border-radius:14px;padding:18px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.assin-alerta-carencia i.ico{font-size:26px}
.assin-alerta-carencia .txt{flex:1;min-width:200px;font-size:14px;font-weight:600}
.assin-mini-planos{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.assin-mini-plano{position:relative;background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px;display:flex;flex-direction:column;gap:6px;transition:var(--transition)}
.assin-mini-plano:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.assin-mini-plano .nome{font-weight:700;font-family:'Sora',sans-serif;color:var(--navy-deep);font-size:14px}
.assin-mini-plano .preco{font-family:'Sora',sans-serif;font-size:18px;font-weight:700}
.assin-mini-plano .desc{font-size:11px;color:var(--muted)}
.assin-mini-plano .btn{margin-top:8px;justify-content:center}
@media(max-width:480px){.assin-hero{padding:18px}.assin-resumo{padding:16px}}

/* =========================================================
   FICHA DA EMPRESA — cabeçalho, cartões, formulário e tabelas
   (classes usadas por views/assinaturas/*.php; o layout
   principal não define estas classes globalmente)
   ========================================================= */
.assin-view .page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:20px}
.assin-view .page-title{font-family:'Sora',sans-serif;font-size:23px;font-weight:700;color:var(--navy-deep);margin:0;display:flex;align-items:center;gap:10px;line-height:1.3}
.assin-view .page-title > i{color:var(--orange)}
.assin-view .page-subtitle{font-size:13px;color:var(--muted);margin:4px 0 0}
.assin-view .page-header-right{display:flex;gap:10px;flex-wrap:wrap}

/* Botões base (garantidos mesmo fora do layout principal) */
.assin-view .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:10px 16px;border-radius:9px;font-size:13.5px;font-weight:600;font-family:'Inter',sans-serif;border:none;cursor:pointer;text-decoration:none;transition:transform .1s ease,opacity .15s ease,box-shadow .15s ease}
.assin-view .btn:active{transform:translateY(1px)}
.assin-view .btn-primary{background:linear-gradient(135deg,var(--navy),var(--navy-deep));color:#fff}
.assin-view .btn-primary:hover{opacity:.92;box-shadow:0 6px 16px -8px rgba(10,25,48,.5)}
.assin-view .btn-secondary{background:#eef2f7;color:var(--navy)}
.assin-view .btn-secondary:hover{background:#e2e8f1}
.assin-view .btn-success{background:linear-gradient(135deg,var(--green),var(--green-dark));color:#fff}
.assin-view .btn-success:hover{opacity:.92;box-shadow:0 6px 16px -8px rgba(22,163,74,.55)}
.assin-view .btn-danger{background:linear-gradient(135deg,var(--red),var(--red-dark));color:#fff}
.assin-view .btn-danger:hover{opacity:.92;box-shadow:0 6px 16px -8px rgba(220,38,38,.55)}

/* Selo de estado genérico (usado nas tabelas de histórico/pagamentos) */
.assin-view .status-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:#f1f5f9;color:var(--muted);white-space:nowrap}

/* Cartões com cabeçalho (form-card "moderno") */
.assin-view .form-card{width:100%;max-width:none;margin:0 auto 20px}
.assin-view .assin-grelha > .form-card,.assin-view .assin-grelha > form.form-modern{min-width:0}
.assin-view .assin-grelha > form.form-modern{margin:0}
.assin-view .form-card-header{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--border);background:#fafbfc}
.assin-view .form-card-header h3{margin:0;font-family:'Sora',sans-serif;font-size:15px;font-weight:600;color:var(--navy-deep)}
.assin-view .form-card-header p{margin:2px 0 0;font-size:12px;color:var(--muted)}
.assin-view .form-card-icon{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,var(--navy),var(--navy-light));color:#fff;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.assin-view .form-card-body{padding:20px}

/* Campos de formulário */
.assin-view .form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}
@media(max-width:640px){.assin-view .form-grid-2{grid-template-columns:1fr}}
.assin-view .form-group{margin-bottom:16px}
.assin-view .form-group label{display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink);margin-bottom:6px}
.assin-view .form-group label i{color:var(--muted);font-size:12px;width:14px;text-align:center}
.assin-view .form-group input,.assin-view .form-group select,.assin-view .form-group textarea{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;font-family:'Inter',sans-serif;color:var(--ink);background:var(--white)}
.assin-view .form-group input:focus,.assin-view .form-group select:focus,.assin-view .form-group textarea:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 3px rgba(34,197,94,.14)}
.assin-view .form-group textarea{resize:vertical;min-height:60px}

/* Subsecção "registar pagamento" dentro das acções */
.assin-view .assin-cartao-acoes .form-card{margin-bottom:0}
.assin-view .assin-cartao-acoes .form-group{margin-bottom:12px}

/* Tabelas do histórico / pagamentos */
.assin-view .assin-tabela thead th:first-child,.assin-view .assin-tabela tbody td:first-child{padding-left:20px}
.assin-view .assin-tabela thead th:last-child,.assin-view .assin-tabela tbody td:last-child{padding-right:20px}
.assin-view .assin-tabela tbody tr{transition:background .15s ease}
.assin-view .assin-tabela tbody tr:hover{background:#f8fafc}

/* ---- MODAL DE CONFIRMAÇÃO (substitui window.confirm) ---- */
.assin-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:20px}
.assin-modal.aberto{display:flex}
.assin-modal-fundo{position:absolute;inset:0;background:rgba(10,25,48,.55);backdrop-filter:blur(3px);animation:assinFade .18s ease}
.assin-modal-caixa{position:relative;background:#fff;border-radius:16px;max-width:430px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.3);text-align:center;padding:30px 28px 24px;animation:assinPop .2s cubic-bezier(.34,1.4,.64,1)}
.assin-modal-icone{width:64px;height:64px;margin:0 auto 16px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px}
.assin-modal-icone.perigo{background:#fee2e2;color:#dc2626}
.assin-modal-icone.aviso{background:#fef3c7;color:#b45309}
.assin-modal-icone.info{background:#e0f2fe;color:#0369a1}
.assin-modal-icone.sucesso{background:#dcfce7;color:#15803d}
.assin-modal h3{margin:0 0 8px;font-family:'Sora',sans-serif;font-size:18px;color:var(--navy-deep,#0a1930)}
.assin-modal .assin-modal-msg{margin:0 0 22px;font-size:14px;line-height:1.55;color:#64748b}
.assin-modal-acoes{display:flex;gap:12px}
.assin-modal-btn{flex:1;padding:11px 16px;border-radius:10px;font-size:14px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;border:1px solid transparent;transition:filter .15s ease,background .15s ease}
.assin-modal-btn.nao{background:#f8fafc;border-color:#e2e8f0;color:#334155}
.assin-modal-btn.nao:hover{background:#eef2f7}
.assin-modal-btn.sim.perigo{background:#dc2626;color:#fff}
.assin-modal-btn.sim.aviso{background:#d97706;color:#fff}
.assin-modal-btn.sim.info{background:#0e2748;color:#fff}
.assin-modal-btn.sim.sucesso{background:#16a34a;color:#fff}
.assin-modal-btn.sim:hover{filter:brightness(1.08)}
@keyframes assinFade{from{opacity:0}to{opacity:1}}
@keyframes assinPop{from{opacity:0;transform:translateY(14px) scale(.96)}to{opacity:1;transform:none}}
</style>

<?php /* Modal de confirmação partilhado pelo módulo (substitui window.confirm). */ ?>
<div class="assin-modal" id="assinModalConfirm" role="dialog" aria-modal="true" aria-labelledby="assinModalTitulo">
    <div class="assin-modal-fundo" data-assin-fechar></div>
    <div class="assin-modal-caixa">
        <div class="assin-modal-icone perigo" id="assinModalIcone"><i class="fas fa-triangle-exclamation"></i></div>
        <h3 id="assinModalTitulo">Confirmar acção</h3>
        <p class="assin-modal-msg" id="assinModalMsg"></p>
        <div class="assin-modal-acoes">
            <button type="button" class="assin-modal-btn nao" data-assin-fechar>Cancelar</button>
            <button type="button" class="assin-modal-btn sim perigo" id="assinModalSim">Confirmar</button>
        </div>
    </div>
</div>

<script>
/* ===== Modal de confirmação do módulo de Assinaturas =====
   Uso: assinConfirmar({ titulo, mensagem, tipo, textoConfirmar }) -> Promise<boolean>
   Também intercepta automaticamente formulários com [data-confirmar]. */
(function () {
    var MODAL_ID = 'assinModalConfirm';
    var ICONES = {
        perigo:  'fa-lock',
        aviso:   'fa-triangle-exclamation',
        info:    'fa-circle-question',
        sucesso: 'fa-circle-check'
    };
    var ultimoFoco = null;

    function el(id) { return document.getElementById(id); }

    function abrir(opcoes) {
        return new Promise(function (resolver) {
            var modal = el(MODAL_ID);
            if (!modal) { // fallback se o modal não existir na página
                resolver(window.confirm((opcoes.mensagem || '').replace(/<[^>]*>/g, '')));
                return;
            }
            var tipo = opcoes.tipo || 'aviso';
            var icone = el('assinModalIcone');
            var btnSim = el('assinModalSim');

            el('assinModalTitulo').textContent = opcoes.titulo || 'Confirmar acção';
            el('assinModalMsg').innerHTML = opcoes.mensagem || 'Tem a certeza?';
            icone.className = 'assin-modal-icone ' + tipo;
            icone.innerHTML = '<i class="fas ' + (ICONES[tipo] || ICONES.aviso) + '"></i>';
            btnSim.className = 'assin-modal-btn sim ' + tipo;
            btnSim.textContent = opcoes.textoConfirmar || 'Confirmar';

            ultimoFoco = document.activeElement;
            modal.classList.add('aberto');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { btnSim.focus(); }, 30);

            function fechar(resultado) {
                modal.classList.remove('aberto');
                document.body.style.overflow = '';
                modal.removeEventListener('click', aoClicar);
                document.removeEventListener('keydown', aoTeclado);
                if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus();
                resolver(resultado);
            }
            function aoClicar(ev) {
                if (ev.target.closest('[data-assin-fechar]')) { fechar(false); return; }
                if (ev.target === btnSim) { fechar(true); }
            }
            function aoTeclado(ev) {
                if (ev.key === 'Escape') fechar(false);
                else if (ev.key === 'Enter' && document.activeElement !== modal) fechar(true);
            }
            modal.addEventListener('click', aoClicar);
            document.addEventListener('keydown', aoTeclado);
        });
    }

    window.assinConfirmar = abrir;

    /* Interceita formulários marcados com data-confirmar (e variantes por tipo). */
    document.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!form.matches || !form.matches('[data-confirmar]')) return;
        if (form.dataset.aConfirmar === 'ok') return; // já confirmado
        ev.preventDefault();
        var tipo = form.getAttribute('data-confirmar-tipo') || 'aviso';
        abrir({
            titulo: form.getAttribute('data-confirmar-titulo') || 'Confirmar acção',
            mensagem: form.getAttribute('data-confirmar') || 'Tem a certeza?',
            tipo: tipo,
            textoConfirmar: form.getAttribute('data-confirmar-texto') || 'Confirmar'
        }).then(function (sim) {
            if (sim) {
                form.dataset.aConfirmar = 'ok';
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    }, true);
})();
</script>
<?php
/**
 * Funções auxiliares de apresentação partilhadas pelas views do módulo.
 */
if (!function_exists('assinSeloEstado')) {
    /** Selo .status-badge para um estado calculado ('gratuita'|'activa'|'carencia'|'bloqueada'). */
    function assinSeloEstado(string $estado, bool $grande = false): string
    {
        $mapa = [
            'gratuita'  => ['assin-gratuita',  'fa-gift',           'Gratuita'],
            'activa'    => ['assin-activa',    'fa-circle-check',   'Activa'],
            'carencia'  => ['assin-carencia',  'fa-hourglass-half', 'Em carência'],
            'bloqueada' => ['assin-bloqueada', 'fa-lock',           'Bloqueada'],
        ];
        [$cls, $icone, $rotulo] = $mapa[$estado] ?? ['assin-gratuita', 'fa-question', ucfirst($estado)];
        return '<span class="status-badge ' . $cls . ($grande ? ' assin-xl' : '') . '">'
             . '<i class="fas ' . $icone . '"></i> ' . htmlspecialchars($rotulo) . '</span>';
    }
}

if (!function_exists('assinTempoLegivel')) {
    /** Formata segundos como "31h 12min", "5 dias", "45min". */
    function assinTempoLegivel(int $segundos): string
    {
        if ($segundos <= 0) return '0min';
        $d = intdiv($segundos, 86400);
        $h = intdiv($segundos % 86400, 3600);
        $m = intdiv($segundos % 3600, 60);
        if ($d >= 2) return $d . ' dias';
        if ($d === 1) return '1 dia';
        if ($h > 0) return $h . 'h ' . sprintf('%02d', $m) . 'min';
        return max(1, $m) . 'min';
    }
}

if (!function_exists('assinDataBr')) {
    /** 'YYYY-MM-DD HH:MM:SS' -> 'DD/MM/YYYY' (null-safe). */
    function assinDataBr(?string $data): string
    {
        if ($data === null || $data === '') return '—';
        $ts = strtotime($data);
        return $ts ? date('d/m/Y', $ts) : '—';
    }
}
