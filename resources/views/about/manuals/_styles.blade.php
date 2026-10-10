@push('styles')
<style>
    /* ── Shared manual layout (Help Center tabs + standalone manual pages) ── */
    .manual-layout { display:grid; grid-template-columns: 250px minmax(0,1fr); gap:22px; align-items:start; }
    .manual-toc { position: sticky; top: 14px; }
    .manual-toc .panel-body { display:flex; flex-direction:column; gap:1px; }
    .manual-toc a { display:block; padding:7px 10px; font-size:12.5px; color:var(--ink-600); text-decoration:none; border-radius:6px; transition:background .15s, color .15s; }
    .manual-toc a:hover { background:var(--ink-50); color:var(--brand-500); }
    .manual-section { margin-bottom:18px; scroll-margin-top:14px; }
    .manual-body .panel-head h2 { font-size:14.5px; }
    .manual-body .panel-head i { color:var(--brand-500); margin-right:6px; }
    .manual-lead { font-size:14px; color:var(--ink-700); line-height:1.6; margin:0 0 10px; }
    .manual-body p { font-size:13.5px; color:var(--ink-600); line-height:1.65; margin:0 0 10px; }
    .manual-steps { margin:6px 0 10px; padding-left:20px; font-size:13.5px; color:var(--ink-600); line-height:1.7; }
    .manual-steps li { margin-bottom:5px; }
    .manual-callout { background:var(--sand-50); border-left:3px solid var(--gold-400); border-radius:6px; padding:10px 14px; font-size:12.5px; color:var(--ink-600); margin:12px 0 0; line-height:1.6; }
    .manual-callout i { color:var(--gold-600); margin-right:6px; }
    .manual-callout a { color:var(--brand-500); }
    /* Procedure heading (step-by-step how-to blocks) */
    .proc-heading { font-size:13.5px; font-weight:700; color:var(--ink-800,#241f2a); margin:18px 0 8px; padding-bottom:5px; border-bottom:1px solid var(--ink-100,#eeedf0); }
    .proc-heading i { color:var(--brand-500); margin-right:6px; }
    .manual-body .manual-steps > li > strong:first-child { color:var(--ink-800,#241f2a); }
    .table-wrap { overflow-x:auto; margin:10px 0; border:1px solid var(--ink-100); border-radius:8px; }
    .manual-body .fluent-table tbody td { font-size:13px; vertical-align:top; }
    .manual-body .fluent-table thead th { font-size:11px; }
    .formula { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:12.5px; color:var(--ink-700); background:var(--ink-50); border:1px solid var(--ink-100); border-radius:8px; padding:10px 14px; margin:8px 0 12px; overflow-x:auto; }
    html { scroll-behavior: smooth; }
    @media (max-width: 900px) { .manual-layout { grid-template-columns: 1fr; } .manual-toc { position: static; } }

    /* ── Light, outlined mini-flow illustration ── */
    .mini-flow { display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:14px 0; }
    .mini-step { display:flex; align-items:center; gap:9px; padding:8px 12px; border:1px solid var(--ink-200); border-radius:9px; background:transparent; }
    .mini-step .ms-num { width:22px; height:22px; border-radius:50%; border:1.5px solid var(--brand-300); color:var(--brand-500); font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; background:#fff; flex:0 0 auto; }
    .mini-step .ms-text { line-height:1.25; }
    .mini-step .ms-label { display:block; font-size:12.5px; font-weight:600; color:var(--ink-700); }
    .mini-step .ms-sub { display:block; font-size:11px; color:var(--ink-400); }
    .mini-arrow { color:var(--ink-300); font-size:13px; flex:0 0 auto; }
    .mini-decision { display:flex; flex-direction:column; gap:8px; margin:14px 0; }
    .mini-decision .md-row { display:flex; align-items:flex-start; gap:10px; padding:9px 12px; border:1px solid var(--ink-200); border-radius:9px; background:transparent; }
    .md-tag { flex:0 0 auto; display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:700; padding:3px 10px; border-radius:999px; border:1px solid var(--ink-200); }
    .md-tag.accept { color:var(--success); border-color:#b6e0cc; }
    .md-tag.reject { color:var(--danger); border-color:#f0c2c0; }
    .md-tag.loop { color:var(--brand-600); border-color:var(--brand-300); }
    .md-text { font-size:12.5px; color:var(--ink-600); line-height:1.5; }
    @media (max-width: 640px) {
        .mini-flow { flex-direction:column; align-items:flex-start; }
        .mini-arrow { transform:rotate(90deg); margin-left:10px; }
    }

    /* ── Help Center underline tabs (settings-page style) ── */
    .help-tabs { display:flex; gap:4px; margin-bottom:22px; border-bottom:1px solid var(--ink-200,#d8d6dc); overflow-x:auto; }
    .help-tab { position:relative; display:inline-flex; align-items:center; gap:8px; padding:11px 18px; font-size:13px; font-weight:600; color:var(--ink-500); text-decoration:none; white-space:nowrap; border-bottom:2.5px solid transparent; margin-bottom:-1px; transition:color .15s,border-color .15s; }
    .help-tab i { font-size:12.5px; color:var(--ink-400); transition:color .15s; }
    .help-tab:hover { color:var(--ink-800); }
    .help-tab:hover i { color:var(--ink-600); }
    .help-tab.active { color:var(--brand-600); border-bottom-color:var(--brand-500); }
    .help-tab.active i { color:var(--brand-500); }
    @media (max-width:600px){ .help-tab{padding:10px 12px;font-size:12px;} }
</style>
@endpush
