@extends('layouts.app')

@section('title', 'Research Call Summary - RTS')

@section('content')
<div class="no-print" style="margin-bottom:12px;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
        <form method="GET" action="{{ route('reports.cycle-progress') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:4px;">
                <label for="cycle_id" style="font-size:11px;font-weight:500;color:var(--ink-600,#4c4553);white-space:nowrap;">Cycle:</label>
                <select name="cycle_id" id="cycle_id" onchange="this.form.submit()" style="padding:4px 8px;border:1px solid var(--ink-200,#d8d6dc);border-radius:4px;font-size:11px;font-family:inherit;color:var(--ink-700,#38333e);background:#fff;min-width:180px;">
                    <option value="">Select a Cycle</option>
                    @foreach($cycles as $c)
                    <option value="{{ $c->id }}" {{ request('cycle_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->title }} ({{ $c->year }})
                    </option>
                    @endforeach
                </select>
            </div>
            @if(request('cycle_id'))
            <a href="{{ route('reports.cycle-progress') }}" style="font-size:10px;color:var(--ink-400,#8b8592);text-decoration:none;">&times; Clear</a>
            @endif
        </form>
        @if($cycleId)
        <button onclick="window.print()" style="display:inline-flex;align-items:center;gap:4px;background:var(--brand-500,#8d1b3d);color:#fff;border:none;border-radius:4px;padding:5px 10px;font-size:11px;font-weight:500;cursor:pointer;">
            <i class="fas fa-print" style="font-size:11px;"></i> Print / PDF
        </button>
        @endif
    </div>
</div>

@if(!$cycleId)
<div class="a4-report-page" style="text-align:center;padding:40px 20px;">
    <i class="fas fa-chart-bar" style="font-size:36px;color:var(--ink-200,#d8d6dc);margin-bottom:12px;"></i>
    <div style="font-size:14px;font-weight:600;color:var(--ink-700,#38333e);margin-bottom:4px;">Select a Cycle</div>
    <div style="font-size:11px;color:var(--ink-400,#8b8592);">Choose a research cycle from the dropdown above to view project progress.</div>
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
            <div class="a4-report-title">Research Call Summary</div>
            <div class="a4-report-desc">
                @if($cycle){{ $cycle->title }} ({{ $cycle->year }}) — @endif
                {{ $totalProjects }} project{{ $totalProjects !== 1 ? 's' : '' }} across all research calls
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
                    <th colspan="3" class="a4-group-project">Project Info</th>
                    <th colspan="4" class="a4-group-lpi">LPI</th>
                    <th colspan="3" class="a4-group-admin">Admin</th>
                    <th colspan="3" class="a4-group-reviewer">Reviewer</th>
                </tr>
                <tr>
                    <th class="a4-col-id">#</th>
                    <th class="a4-col-title">Project Title</th>
                    <th>LPI Name</th>
                    <th class="a4-col-center">Registration</th>
                    <th class="a4-col-center">Outcomes</th>
                    <th class="a4-col-center">Students</th>
                    <th class="a4-col-center">Contributions</th>
                    <th class="a4-col-center">Progress Report</th>
                    <th class="a4-col-center">Final Report</th>
                    <th class="a4-col-center">Readiness Report</th>
                    <th class="a4-col-center">Reviewers Assigned</th>
                    <th class="a4-col-center">Progress Grading</th>
                    <th class="a4-col-center">Final Grading</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="a4-cell-num">{{ $row['id'] }}</td>
                    <td class="a4-cell-title">{{ $row['title'] }}</td>
                    <td class="a4-cell-lpi">{{ $row['lpi_name'] }}</td>
                    <td class="a4-cell-center">
                        @if($row['registration'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">{{ $row['outcomes_count'] }}</td>
                    <td class="a4-cell-center">{{ $row['students_count'] }}</td>
                    <td class="a4-cell-center">{{ $row['contributions_count'] }}</td>
                    <td class="a4-cell-center">
                        @if($row['has_progress_report'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">
                        @if($row['has_final_report'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">
                        @if($row['has_readiness_report'])
                            <i class="fas fa-check-circle a4-icon-yes"></i>
                        @else
                            <i class="fas fa-times-circle a4-icon-no"></i>
                        @endif
                    </td>
                    <td class="a4-cell-center">{{ $row['reviewer_count'] }}</td>
                    <td class="a4-cell-center">{{ $row['progress_grading_count'] }}</td>
                    <td class="a4-cell-center">{{ $row['final_grading_count'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="a4-cell-empty">No projects found in this cycle.</td>
                </tr>
                @endforelse
            </tbody>
            @if($totalProjects > 0)
            <tfoot>
                <tr class="a4-foot-summary">
                    <td colspan="3" class="a4-foot-label">Summary — Total: {{ $totalProjects }}</td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['registration']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['registration']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['outcomes']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['outcomes']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['students']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['students']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['contributions']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['contributions']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['progress_report']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['progress_report']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['final_report']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['final_report']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['readiness_report']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['readiness_report']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['reviewer_count']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['reviewer_count']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['progress_grading']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['progress_grading']['pending'] }}</div></td>
                    <td class="a4-foot-cell"><div class="a4-foot-c">Completed: {{ $footer['final_grading']['completed'] }}</div><div class="a4-foot-p">Pending: {{ $footer['final_grading']['pending'] }}</div></td>
                </tr>
                <tr class="a4-foot-email no-print-col">
                    <td colspan="3" class="a4-foot-label">Send Email Reminders</td>
                    <td class="a4-foot-cell">@if($footer['registration']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('registration')" data-column="registration"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['outcomes']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('outcomes')" data-column="outcomes"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['students']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('students')" data-column="students"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['contributions']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('contributions')" data-column="contributions"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['progress_report']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('progress_report')" data-column="progress_report"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['final_report']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('final_report')" data-column="final_report"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['readiness_report']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('readiness_report')" data-column="readiness_report"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['reviewer_count']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('reviewer_count')" data-column="reviewer_count"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['progress_grading']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('progress_grading')" data-column="progress_grading"><i class="fas fa-envelope"></i> Send</button>@endif</td>
                    <td class="a4-foot-cell">@if($footer['final_grading']['pending'] > 0)<button class="btn-send-email" onclick="sendReminder('final_grading')" data-column="final_grading"><i class="fas fa-envelope"></i> Send</button>@endif</td>
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
            <span>Research Call Summary</span>
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
.a4-col-id{width:32px;text-align:center}
.a4-col-title{width:auto}
.a4-col-center{width:80px;text-align:center}
.a4-cell-num{text-align:center;font-variant-numeric:tabular-nums}
.a4-cell-title{font-weight:500;color:var(--ink-800,#241f2a)}
.a4-cell-lpi{font-size:9px;color:var(--ink-600,#4c4553);max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.a4-cell-center{text-align:center;font-variant-numeric:tabular-nums}
.a4-cell-empty{text-align:center;padding:16px;color:var(--ink-400,#8b8592)}
.a4-group-header th{text-align:center;font-size:9px;font-weight:700;letter-spacing:0.06em;padding:4px 4px;border:0.25px solid var(--ink-200,#d8d6dc)}
.a4-group-project{background:#e8e4de;color:var(--ink-800,#241f2a)}
.a4-group-lpi{background:#f0e8d8;color:#8d6b20}
.a4-group-admin{background:#dce8f0;color:#1a5276}
.a4-group-reviewer{background:#d8f0e0;color:#1a6b3a}
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
.btn-send-email .spinner{display:none;width:10px;height:10px;border:1.5px solid var(--brand-500,#8d1b3d);border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite}
.btn-send-email.loading .spinner{display:inline-block}
.btn-send-email.loading i{display:none}
@keyframes spin{to{transform:rotate(360deg)}}
.a4-toast{position:fixed;bottom:24px;right:24px;background:var(--ink-800,#241f2a);color:#fff;padding:10px 16px;border-radius:6px;font-size:12px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,.2);z-index:10000;opacity:0;transform:translateY(10px);transition:all .3s ease}
.a4-toast.show{opacity:1;transform:translateY(0)}
.a4-toast.success{background:#1f8a5f}
.a4-toast.error{background:#c0392b}
.a4-report-footer{margin-top:10px}
.a4-footer-line{border-top:0.5px solid var(--ink-200,#d8d6dc);margin-bottom:4px}
.a4-footer-text{text-align:center;font-size:8px;color:var(--ink-400,#8b8592)}
.a4-footer-sep{margin:0 4px;color:var(--ink-200,#d8d6dc)}

/* =============================================================
   PRINT STYLES — A4 Optimized
   ============================================================= */
@media print{
  @page{
    size:A4 landscape;
    margin:10mm 12mm 12mm 12mm;
  }

  /* ── Reset page wrapper ── */
  .a4-report-page{
    max-width:100%!important;margin:0!important;padding:0!important;
    border:none!important;border-radius:0!important;box-shadow:none!important;
    background:#fff!important;font-size:9px!important;
  }

  /* ── Hide screen-only elements ── */
  .no-print,.no-print-col,.a4-foot-email,
  .btn-send-email,.a4-toast,
  .fluent-command-bar,.fluent-sidebar,.fluent-footer,
  .sidebar-overlay,.fluent-dropdown,.app-shell>aside,
  .page-actions,.role-switcher,.notif-dot,
  #notifDropdown,#userDropdown,.fluent-alert,
  #workflowModal,.modal,.modal-backdrop,.toastify{
    display:none!important;
  }

  /* ── Body reset ── */
  body{
    background:#fff!important;margin:0!important;padding:0!important;
    font-family:'Inter','Segoe UI',Tahoma,Geneva,Verdana,sans-serif!important;
    -webkit-print-color-adjust:exact!important;
    print-color-adjust:exact!important;
    color-adjust:exact!important;
  }
  .app-shell,.fluent-content,.fluent-content-body{
    margin:0!important;padding:0!important;max-width:100%!important;
    width:100%!important;display:block!important;background:#fff!important;
    border:none!important;box-shadow:none!important;overflow:visible!important;
  }

  /* ── Header ── */
  .a4-report-header{
    border-bottom:1px solid #8d1b3d!important;
    padding-bottom:6px!important;margin-bottom:8px!important;
  }
  .a4-brand-mark{
    background:#8d1b3d!important;color:#fff!important;
    -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
  }
  .a4-header-divider{background:#ccc!important}
  .a4-report-title{font-weight:700!important;font-size:13px!important;color:#1a1a1a!important}
  .a4-report-desc{font-size:8px!important;color:#666!important}
  .a4-org-name{font-size:9px!important;font-weight:600!important;color:#333!important}
  .a4-org-sub{font-size:7px!important;color:#888!important}
  .a4-meta-table td{font-size:7px!important}
  .a4-meta-label{color:#999!important}
  .a4-meta-value{color:#555!important;font-weight:500!important}

  /* ── Table ── */
  .a4-table-section{margin-bottom:6px!important;overflow:visible!important}
  .a4-data-table{
    font-size:8px!important;font-weight:400!important;
    table-layout:fixed!important;width:100%!important;
  }
  .a4-data-table th{
    background:#f2ead6!important;color:#444!important;
    font-weight:600!important;font-size:7px!important;
    border:0.4px solid #aaa!important;
    border-bottom:0.6px solid #888!important;
    padding:3px 3px!important;
    -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
  }
  .a4-data-table td{
    background:none!important;color:#333!important;
    font-weight:400!important;font-size:7.5px!important;
    border:0.3px solid #ccc!important;padding:2px 3px!important;
  }
  .a4-data-table tbody tr:nth-child(even) td{
    background:#faf7f0!important;
    -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
  }
  .a4-group-header th{
    font-size:7.5px!important;font-weight:700!important;
    border:0.4px solid #aaa!important;
  }
  .a4-group-project,.a4-group-lpi,.a4-group-admin,.a4-group-reviewer{
    background:#f0ece4!important;color:#333!important;
    -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
  }
  .a4-icon-yes{color:#2e7d32!important;font-size:9px!important}
  .a4-icon-no{color:#999!important;font-size:9px!important}
  .a4-cell-title{font-weight:500!important;color:#222!important}
  .a4-cell-lpi{color:#555!important}
  .a4-cell-empty{color:#999!important}

  /* ── Footer summary inside tfoot ── */
  .a4-data-table tfoot td{
    border-top:1px solid #8d1b3d!important;
    background:#f2ead6!important;
    -webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;
  }
  .a4-foot-label{color:#333!important;font-size:7.5px!important}
  .a4-foot-c{color:#2e7d32!important;font-size:7px!important}
  .a4-foot-p{color:#888!important;font-size:7px!important}

  /* ── Report footer ── */
  .a4-report-footer{
    margin-top:8px!important;
    page-break-inside:avoid!important;
  }
  .a4-footer-line{border-top:0.4px solid #ccc!important}
  .a4-footer-text{font-size:6.5px!important;color:#999!important}
  .a4-footer-sep{color:#ccc!important}

  /* ── Page numbering ── */
  .a4-page-number::after{
    counter-increment:page 1;
    content:counter(page);
  }

  /* ── Page break control ── */
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
const CYCLE_ID = {{ $cycleId ?: 'null' }};
const SEND_URL = '{{ route("reports.cycle-progress.send-reminder") }}';

function sendReminder(columnKey) {
    if (!CYCLE_ID) return;
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
        body: JSON.stringify({ cycle_id: CYCLE_ID, column_key: columnKey }),
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
