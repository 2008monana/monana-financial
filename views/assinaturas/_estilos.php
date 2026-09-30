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
</style>
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
