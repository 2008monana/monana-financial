<style>
.modulo-cabecalho{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:26px}
.modulo-cabecalho h1{margin:0;color:var(--navy-deep);font-size:26px}
.modulo-cabecalho h1 i{color:var(--green);margin-right:8px}
.modulo-cabecalho p{margin:6px 0 0;color:var(--muted);font-size:13px}
.config-form{max-width:920px;margin:0 auto;display:grid;gap:18px}
.config-cartao{background:#fff;border:1px solid var(--border);border-radius:14px;padding:24px 26px;box-shadow:var(--shadow-sm,0 1px 3px rgba(16,24,40,.04));transition:box-shadow .15s}
.config-cartao:hover{box-shadow:var(--shadow,0 2px 10px rgba(0,0,0,.08))}
.config-cartao-cabecalho{display:flex;align-items:center;gap:12px;margin-bottom:18px}
.config-cartao-icone{width:38px;height:38px;flex:0 0 auto;display:grid;place-items:center;border-radius:10px;background:rgba(34,197,94,.12);color:var(--green-dark);font-size:16px}
.config-cartao h2{margin:0;color:var(--navy-deep);font-size:17px}
.config-cartao>p{margin:-8px 0 18px;color:var(--muted);font-size:13px}
.config-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 18px}
.config-grid label{display:grid;gap:6px;font-size:13px;font-weight:700;color:var(--ink)}
.config-grid input,.config-grid select{padding:10px 12px;border:1px solid var(--border);border-radius:8px;font:inherit;background:#fff;transition:border-color .15s,box-shadow .15s}
.config-grid input:focus,.config-grid select:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 3px rgba(34,197,94,.14)}
.config-form>.btn{justify-self:start}
@media(max-width:650px){.config-grid{grid-template-columns:1fr}.modulo-cabecalho{flex-direction:column}}
</style>
