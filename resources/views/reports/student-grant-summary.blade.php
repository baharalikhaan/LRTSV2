@extends('layouts.app')

@section('title', 'Student Grant Summary - RTS')

@section('content')
<div class="no-print" style="margin-bottom:12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
        <form method="GET" action="{{ route('reports.student-grant-summary') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:4px;">
                <label for="program_id" style="font-size:11px;font-weight:500;color:var(--ink-600,#4c4553);white-space:nowrap;">Research Call:</label>
                <select name="program_id" id="program_id" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--ink-200,#d8d6dc);border-radius:4px;font-size:11px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;min-width:220px;">
                    <option value="">Select a Research Call</option>
                    @foreach($programs as $p)
                    <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->program_title }} @if($p->grant)({{ $p->grant->grant_code }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>
            @if($programId)
            <a href="{{ route('reports.student-grant-summary') }}" style="font-size:10px;color:var(--ink-400,#8b8592);text-decoration:none;">&times; Clear</a>
            @endif
        </form>
        @if($programId)
        <button onclick="window.print()" style="display:inline-flex;align-items:center;gap:4px;background:var(--brand-500,#8d1b3d);color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:11px;font-weight:500;cursor:pointer;">
            <i class="fas fa-print" style="font-size:11px;"></i> Print / PDF
        </button>
        @endif
    </div>
</div>

@if(!$programId)
<div class="a4-report-page" style="text-align:center;padding:40px 20px;">
    <i class="fas fa-user-graduate" style="font-size:36px;color:var(--ink-200,#d8d6dc);margin-bottom:12px;"></i>
    <div style="font-size:14px;font-weight:600;color:var(--ink-700,#38333e);margin-bottom:4px;">Select a Research Call</div>
    <div style="font-size:11px;color:var(--ink-400,#8b8592);">Choose a research call from the dropdown above to view student grant summary.</div>
</div>
@else
<div class="a4-report-page">

    <div class="a4-report-header">
        <div class="a4-header-brand">
            <div class="a4-brand-mark">QU</div>
            <div class="a4-brand-text">
                <div class="a4-org-name">Qatar University</div>
                <div class="a4-org-sub">Research Tracking System</div>
            </div>
        </div>
        <div class="a4-header-divider"></div>
        <div class="a4-header-title-block">
            <div class="a4-report-title">Student Grant Summary</div>
            <div class="a4-report-desc">
                @if($program){{ $program->program_title }} — @endif
                {{ $totalProjects }} student project{{ $totalProjects !== 1 ? 's' : '' }}
            </div>
        </div>
        <div class="a4-header-meta">
            <table class="a4-meta-table">
                <tr><td class="a4-meta-label">Generated:</td><td class="a4-meta-value">{{ now()->format('d M Y, h:i A') }}</td></tr>
                <tr><td class="a4-meta-label">Prepared by:</td><td class="a4-meta-value">RTS</td></tr>
            </table>
        </div>
    </div>

    <div class="a4-table-section">
        <table class="a4-data-table" id="reportTable">
            <thead>
                <tr class="a4-group-header">
                    <th colspan="1" class="a4-group-project">Project Info</th>
                    <th colspan="7" class="a4-group-lpi">LPI Student Project Form</th>
                </tr>
                <tr>
                    <th class="a4-col-id">Project ID</th>
                    <th class="a4-col-center">Form Saved</th>
                    <th class="a4-col-center">Qatari Students</th>
                    <th class="a4-col-center">Non-Qatari Students</th>
                    <th class="a4-col-center">Engagement</th>
                    <th class="a4-col-center">Publications</th>
                    <th class="a4-col-center">Ethical Approval</th>
                    <th class="a4-col-center">Spending (QAR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="a4-cell-id">
                        <div style="font-weight:600;">{{ $row['old_project_id'] }}</div>
                        <div style="font-size:8px;color:teal;font-style:italic;margin-top:1px;">{{ $row['lpi_email'] ?? '—' }}</div>
                    </td>
                    <td class="a4-cell-center">
                        @if($row['form_saved'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">{{ $row['qatari_count'] }}</td>
                    <td class="a4-cell-center">{{ $row['non_qatari_count'] }}</td>
                    <td class="a4-cell-center">
                        @if($row['has_engagement'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">
                        @if($row['has_publications'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">
                        @if($row['has_ethical_approval'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <span style="font-size:10px;color:var(--color-ink-400);">N/A</span>
                        @endif
                    </td>
                    <td class="a4-cell-center">
                        @if($row['spending_status'] === 'exceeded')
                            <span style="color:#dc2626;font-size:10px;">{{ $row['utilization_pct'] }}% - Exceeded</span>
                        @elseif($row['spending_status'] === 'under')
                            <span style="color:#f59e0b;font-size:10px;">{{ $row['utilization_pct'] }}% - Under Utilized</span>
                        @elseif($row['spending_status'] === 'full')
                            <span style="color:#10b981;font-size:10px;">{{ $row['utilization_pct'] }}%</span>
                        @elseif($row['spending_status'] === 'no_spending')
                            <span style="color:#f97316;font-size:10px;">0% - No Spending</span>
                        @else
                            <span style="font-size:10px;color:var(--color-ink-400);">N/A</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="a4-cell-empty">No student grant projects found in this cycle.</td>
                </tr>
                @endforelse
            </tbody>
            @if($totalProjects > 0)
            <tfoot>
                <tr class="a4-foot-summary">
                    <td class="a4-foot-label">Summary — Total: {{ $totalProjects }}</td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['form_saved']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['form_saved']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c" style="font-size:9px;">Total: {{ $footer['qatari_total'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c" style="font-size:9px;">Total: {{ $footer['non_qatari_total'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['engagement']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['engagement']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['publications']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['publications']['pending'] }}</div></td>
                    <td class="a4-foot-cell"></td>
                    <td class="a4-foot-cell"></td>
                </tr>
                <tr class="a4-foot-email no-print-col">
                    <td class="a4-foot-label">Send Email Reminders</td>
                    <td class="a4-foot-cell">@if($footer['form_saved']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('form_saved')" data-column="form_saved"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell" colspan="2">@if($footer['qatari_total'] == 0 && $footer['non_qatari_total'] == 0)<button class="btn-send-email" onclick="sendReminder('students')" data-column="students"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['engagement']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('engagement')" data-column="engagement"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['publications']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('publications')" data-column="publications"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell"></td>
                    <td class="a4-foot-cell"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <div class="a4-report-footer">
        <div class="a4-footer-line"></div>
        <div class="a4-footer-text">
            <span>&copy; {{ date('Y') }} Qatar University — RTS</span>
            <span class="a4-footer-sep">|</span>
            <span>Student Grant Summary</span>
            <span class="a4-footer-sep">|</span>
            <span>Page <span class="a4-page-number"></span></span>
            <span class="a4-footer-sep">|</span>
            <span>{{ now()->format('d M Y') }}</span>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.a4-report-page{max-width:100%;margin:0 auto;background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:6px;box-shadow:var(--fluent-depth-4,0 2px 4px rgba(22,19,26,.09),0 0px 2px rgba(22,19,26,.07));padding:18px 20px 14px;font-family:'Inter','Segoe UI Variable','Segoe UI',ui-sans-serif,system-ui,sans-serif;color:var(--ink-800,#241f2a);font-size:11px;line-height:1.4}
.a4-report-header{display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;padding-bottom:8px;border-bottom:1.5px solid var(--brand-500,#8d1b3d)}
.a4-header-brand{display:flex;align-items:center;gap:6px;flex-shrink:0}
.a4-brand-mark{width:24px;height:24px;background:var(--brand-500,#8d1b3d);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:10px}
.a4-brand-text{line-height:1.15}
.a4-org-name{font-size:11px;font-weight:700;color:var(--ink-800,#241f2a)}
.a4-org-sub{font-size:9px;color:var(--ink-400,#8b8592)}
.a4-header-divider{width:1px;height:24px;background:var(--ink-200,#d8d6dc);flex-shrink:0}
.a4-header-title-block{flex:1;min-width:0}
.a4-report-title{font-size:15px;font-weight:700;color:var(--ink-900,#16131a);margin:0 0 1px;line-height:1.2}
.a4-report-desc{font-size:9.5px;color:var(--ink-400,#8b8592);margin:0}
.a4-header-meta{flex-shrink:0}
.a4-meta-table{border-collapse:collapse;font-size:9px}
.a4-meta-table td{padding:0 0 0 6px;line-height:1.35}
.a4-meta-label{color:var(--ink-400,#8b8592);text-align:right;font-weight:500}
.a4-meta-value{color:var(--ink-700,#38333e);font-weight:600}
.a4-table-section{margin-bottom:8px;overflow-x:auto}
.a4-data-table{width:100%;border-collapse:collapse;font-size:9.5px;table-layout:fixed}
.a4-data-table th{background:var(--sand-50,#faf7f0);color:var(--ink-600,#4c4553);font-weight:600;font-size:8.5px;text-transform:uppercase;letter-spacing:0.04em;padding:4px 4px;text-align:left;white-space:nowrap;border:0.25px solid var(--ink-200,#d8d6dc)}
.a4-data-table td{padding:3px 4px;vertical-align:middle;border:0.25px solid var(--ink-100,#eeedf0)}
.a4-data-table tbody tr:nth-child(even){background:#fafafa}
.a4-col-id{width:120px;text-align:left}
.a4-col-center{width:100px;text-align:center}
.a4-cell-id{text-align:left;font-size:9px;line-height:1.3}
.a4-cell-center{text-align:center;font-variant-numeric:tabular-nums}
.a4-cell-empty{text-align:center;padding:16px;color:var(--ink-400,#8b8592)}
.a4-group-header th{text-align:center;font-size:9px;font-weight:700;letter-spacing:0.06em;padding:4px 4px;border:0.25px solid var(--ink-200,#d8d6dc)}
.a4-group-project{background:#e8e4de;color:var(--ink-800,#241f2a)}
.a4-group-lpi{background:#f0e8d8;color:#8d6b20}
.a4-icon-yes{color:var(--success,#1f8a5f);font-size:12px}
.a4-icon-no{color:var(--ink-300,#b4b0ba);font-size:12px}
.a4-data-table tfoot td{border-top:1.5px solid var(--brand-500,#8d1b3d);background:var(--sand-50,#faf7f0);padding:4px 4px}
.a4-foot-summary td,.a4-foot-email td{border-top:0.25px solid var(--ink-200,#d8d6dc)}
.a4-foot-label{text-align:right;font-weight:600;color:var(--ink-700,#38333e);font-size:8.5px}
.a4-foot-cell{text-align:center;vertical-align:middle}
.a4-foot-c{font-size:8px;color:var(--success,#1f8a5f);font-weight:600;margin-bottom:1px}
.a4-foot-p{font-size:8px;color:var(--ink-400,#8b8592);font-weight:500}
.btn-send-email{display:inline-flex;align-items:center;gap:4px;background:#fff;color:var(--brand-500,#8d1b3d);border:1px solid var(--brand-500,#8d1b3d);border-radius:4px;padding:4px 10px;font-size:9px;font-weight:600;font-family:inherit;cursor:pointer;transition:all .15s ease;white-space:nowrap}
.btn-send-email:hover{background:var(--brand-500,#8d1b3d);color:#fff}
.btn-send-email:disabled{opacity:.5;cursor:not-allowed}
.a4-report-footer{margin-top:10px}
.a4-footer-line{border-top:0.5px solid var(--ink-200,#d8d6dc);margin-bottom:4px}
.a4-footer-text{text-align:center;font-size:8px;color:var(--ink-400,#8b8592)}
.a4-footer-sep{margin:0 4px;color:var(--ink-200,#d8d6dc)}

@media print{
  @page{size:A4 landscape;margin:10mm 12mm 12mm 12mm;}
  .a4-report-page{max-width:100%!important;margin:0!important;padding:0!important;border:none!important;border-radius:0!important;box-shadow:none!important;background:#fff!important;font-size:9px!important;}
  .no-print,.no-print-col,.a4-foot-email,.btn-send-email,.a4-toast,.fluent-command-bar,.fluent-sidebar,.fluent-footer,.sidebar-overlay,.fluent-dropdown,.app-shell>aside,.page-actions,.role-switcher,.notif-dot,#notifDropdown,#userDropdown,.fluent-alert,#workflowModal,.modal,.modal-backdrop,.toastify{display:none!important;}
  body{background:#fff!important;margin:0!important;padding:0!important;font-family:'Inter','Segoe UI',Tahoma,Geneva,Verdana,sans-serif!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important;}
  .a4-report-header{border-bottom:1px solid #8d1b3d!important;padding-bottom:6px!important;margin-bottom:8px!important;}
  .a4-brand-mark{background:#8d1b3d!important;color:#fff!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  .a4-report-title{font-weight:700!important;font-size:13px!important;color:#1a1a1a!important}
  .a4-data-table{font-size:8px!important;font-weight:400!important;width:100%!important;}
  .a4-data-table th{background:#f2ead6!important;color:#444!important;font-weight:600!important;font-size:7px!important;border:0.4px solid #aaa!important;padding:3px 3px!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  .a4-data-table td{background:none!important;color:#333!important;font-weight:400!important;font-size:7.5px!important;border:0.3px solid #ccc!important;padding:2px 3px!important;}
  .a4-data-table tbody tr:nth-child(even) td{background:#faf7f0!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  .a4-group-header th{font-size:7.5px!important;font-weight:700!important;border:0.4px solid #aaa!important;}
  .a4-group-project,.a4-group-lpi{background:#f0ece4!important;color:#333!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  .a4-icon-yes{color:#2e7d32!important;font-size:9px!important}
  .a4-icon-no{color:#999!important;font-size:9px!important}
  .a4-data-table tfoot td{border-top:1px solid #8d1b3d!important;background:#f2ead6!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  .a4-foot-label{color:#333!important;font-size:7.5px!important}
  .a4-foot-c{color:#2e7d32!important;font-size:7px!important}
  .a4-foot-p{color:#888!important;font-size:7px!important}
  .a4-report-footer{margin-top:8px!important;page-break-inside:avoid!important;}
  .a4-footer-line{border-top:0.4px solid #ccc!important}
  .a4-footer-text{font-size:6.5px!important;color:#999!important}
  .a4-page-number::after{counter-increment:page 1;content:counter(page);}
  .a4-data-table tr{page-break-inside:avoid!important}
  .a4-data-table thead{display:table-header-group!important}
  .a4-data-table tfoot{display:table-footer-group!important}
}
.a4-page-number::after{content:"1"}
</style>
@endpush

@push('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const PROGRAM_ID = {{ $programId ?: 'null' }};
const SEND_URL = '{{ route("reports.cycle-progress.send-reminder") }}';

function sendReminder(columnKey) {
    if (!PROGRAM_ID) return;
    const btn = document.querySelector(`.btn-send-email[data-column="${columnKey}"]`);
    if (!btn || btn.disabled) return;

    btn.disabled = true;
    btn.classList.add('loading');

    fetch(SEND_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ program_id: PROGRAM_ID, column_key: columnKey }),
    })
    .then(r => r.json())
    .then(data => {
        showToast(data.success ? (data.message || 'Done.') : (data.message || 'Failed.'), data.success ? 'success' : 'error');
    })
    .catch(() => showToast('An error occurred.', 'error'))
    .finally(() => { btn.disabled = false; btn.classList.remove('loading'); });
}

function showToast(message, type) {
    let t = document.getElementById('a4-toast');
    if (!t) { t = document.createElement('div'); t.id = 'a4-toast'; document.body.appendChild(t); }
    t.textContent = message;
    t.className = 'a4-toast ' + type;
    requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => t.classList.remove('show'), 3500);
}
</script>
@endpush
