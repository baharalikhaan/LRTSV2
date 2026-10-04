@php
    // QR code data — encode project report card URL for verification
    $qrUrl = route('projects.report-card', $project->id);
    $qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrUrl);

    // Base64-encode the logo for reliable display (avoids asset() path issues)
    $logoPath = public_path('images/research_logo.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
@endphp

<style>
    #workflowModal .rc-page-inner { padding: 12px 16px; }

    /* ─── Header ─── */
    #workflowModal .rc-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 10px; padding-bottom: 8px;
        border-bottom: 1px solid #eee3e7;
    }
    #workflowModal .rc-header-left { display: flex; align-items: center; gap: 8px; }
    #workflowModal .rc-header-left img { width: 48px; height: auto; }
    #workflowModal .rc-header-right { text-align: right; }
    #workflowModal .rc-doc-title {
        font-size: 15px; font-weight: 600; letter-spacing: 1px;
        color: var(--brand-600, #8d1b3d); text-transform: uppercase; line-height: 1.1;
    }
    #workflowModal .rc-doc-sub {
        font-size: 7.5px; color: var(--ink-400, #9a9ea5); text-transform: uppercase;
        letter-spacing: 1.5px; font-weight: 500; margin-top: 2px;
    }

    /* ─── Project info table ─── */
    #workflowModal .rc-info { width: 100%; border-collapse: collapse; margin-bottom: 10px; background: #fafbfc; }
    #workflowModal .rc-info th {
        text-align: left; padding: 5px 8px; font-weight: 600; font-size: 9px;
        color: var(--brand-600, #8d1b3d); width: 110px; border: 1px solid #eceff1;
    }
    #workflowModal .rc-info td { padding: 5px 8px; border: 1px solid #eceff1; font-weight: 400; font-size: 10px; }

    /* ─── Section headings ─── */
    #workflowModal .rc-section { margin-bottom: 8px; }
    #workflowModal .rc-section-title {
        font-size: 9.5px; font-weight: 600; color: var(--brand-600, #8d1b3d);
        padding: 2px 0; margin-bottom: 5px;
        border-bottom: 1px solid #eee3e7;
        display: flex; align-items: center; justify-content: space-between;
    }
    #workflowModal .rc-section-title .rc-count {
        font-size: 7.5px; font-weight: 500; color: var(--ink-400, #9a9ea5);
        background: var(--ink-50, #f3f4f6); padding: 1px 6px; border-radius: 999px;
    }

    /* ─── Remarks tables ─── */
    #workflowModal .rc-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
    #workflowModal .rc-table th {
        text-align: left; padding: 4px 7px; font-size: 8px;
        font-weight: 600; color: var(--ink-500, #6b7078); border: 1px solid #eceff1;
        background: var(--sand-50, #fafbfc);
    }
    #workflowModal .rc-table td { padding: 4px 7px; border: 1px solid #eceff1; vertical-align: top; font-size: 9.5px; }
    #workflowModal .rc-table td.rc-crit { font-weight: 500; color: var(--ink-700, #3d3d3d); }
    #workflowModal .rc-table td.rc-rating { font-weight: 600; color: var(--brand-600, #8d1b3d); white-space: nowrap; text-align: center; }
    #workflowModal .rc-table tr.rc-rec td { background: var(--sand-50, #fafbfc); font-weight: 500; }
    #workflowModal .rc-table .rc-ok { color: var(--success, #2e8b57); font-weight: 600; }
    #workflowModal .rc-table .rc-no { color: var(--danger, #c0392b); font-weight: 600; }

    /* Rotated reviewer label */
    #workflowModal .rc-reviewer {
        writing-mode: vertical-rl; transform: rotate(180deg);
        text-align: center; white-space: nowrap;
        font-weight: 600; color: var(--brand-600, #8d1b3d); background: #fbeef1;
        font-size: 8px; letter-spacing: .4px; width: 20px;
        padding: 6px 3px; border-bottom: 1px solid #e9d3d9;
    }

    /* ─── Summary row (QR + totals) ─── */
    #workflowModal .rc-summary { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 4px; }
    #workflowModal .rc-summary-qr { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    #workflowModal .rc-summary-qr img { width: 44px; height: 44px; border: 1px solid #eceff1; border-radius: 4px; }
    #workflowModal .rc-summary-qr .rc-qr-label {
        font-size: 6px; color: var(--ink-400, #9a9ea5); text-transform: uppercase;
        letter-spacing: .4px; font-weight: 600; line-height: 1.2;
    }
    #workflowModal .rc-summary-table { border-collapse: collapse; flex: 1; max-width: 240px; }
    #workflowModal .rc-summary-table td {
        padding: 4px 8px; border: 1px solid #eceff1; font-size: 9px;
    }
    #workflowModal .rc-summary-table td:first-child {
        background: var(--sand-50, #fafbfc); font-weight: 600; color: var(--ink-600, #6b7078); width: 110px;
    }
    #workflowModal .rc-summary-table td:last-child {
        font-weight: 600; color: var(--brand-600, #8d1b3d); text-align: center;
    }

    /* ─── Notes / footer ─── */
    #workflowModal .rc-notes {
        margin-top: 8px; padding-top: 5px; border-top: 1px solid #eceff1;
        font-size: 8px; color: var(--ink-400, #9a9ea5); line-height: 1.4;
    }
    #workflowModal .rc-notes b { color: var(--ink-600, #6b7078); font-weight: 600; }
    #workflowModal .rc-footer {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 5px; padding-top: 5px; border-top: 1px solid #eceff1;
        font-size: 7.5px; color: var(--ink-400, #9a9ea5);
    }

    /* ─── Actions bar ─── */
    #workflowModal .rc-actions {
        display: flex; gap: 6px; justify-content: flex-end;
        padding: 8px 0 0; margin-top: 10px; border-top: 1px solid #eceff1;
    }
    #workflowModal .rc-actions .btn-print {
        display: inline-flex; align-items: center; gap: 5px;
        background: var(--brand-500, #8d1b3d); color: #fff;
        font-size: 10px; font-weight: 500; padding: 5px 14px;
        border-radius: var(--fluent-radius-md, 6px);
        border: 1px solid var(--brand-600, #7a1636);
        box-shadow: var(--fluent-depth-2, 0 1px 2px rgba(22,19,26,.07));
        cursor: pointer; transition: background .12s;
    }
    #workflowModal .rc-actions .btn-print:hover { background: var(--brand-600, #7a1636); }
    #workflowModal .rc-actions .btn-close-modal {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fff; color: var(--ink-700, #38333e);
        font-size: 10px; font-weight: 500; padding: 5px 14px;
        border-radius: var(--fluent-radius-md, 6px);
        border: 1px solid var(--ink-200, #d8d6dc);
        box-shadow: var(--fluent-depth-2, 0 1px 2px rgba(22,19,26,.07));
        cursor: pointer; transition: background .12s;
    }
    #workflowModal .rc-actions .btn-close-modal:hover {
        background: var(--sand-50, #faf7f0);
        border-color: var(--brand-300, #d3738f); color: var(--brand-600, #7a1636);
    }

    /* ─── Modal style override ─── */
    #workflowModal .modal-content {
        border: none !important;
        border-radius: var(--fluent-radius-lg, 8px) !important;
        overflow: hidden;
    }

    /* ─── Print styles ─── */
    @media print {
        body * { visibility: hidden; }
        #workflowModal .modal-content,
        #workflowModal .modal-content * { visibility: visible; }
        #workflowModal .modal-content {
            position: absolute; left: 0; top: 0; width: 100% !important;
            border: none !important; box-shadow: none !important; padding: 0 !important;
            margin: 0 !important;
        }
        #workflowModal .rc-actions { display: none !important; }
        #workflowModal .rc-notes { margin-bottom: 0; }

        /* Print-specific page margins */
        .rc-report-card-inner {
            padding: 20px 24px !important;
        }
    }
</style>

<div class="rc-report-card-inner" style="padding: 12px 16px;">

    {{-- ===== HEADER: Logo left, Title right ===== --}}
    <div class="rc-header">
        <div class="rc-header-left">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Research Logo" />
            @endif
        </div>
        <div class="rc-header-right">
            <div class="rc-doc-title">Report Card</div>
            <div class="rc-doc-sub">Project Evaluation</div>
        </div>
    </div>

    {{-- ===== PROJECT INFO ===== --}}
    <table class="rc-info">
        <tbody>
            <tr>
                <th>Project ID</th>
                <td>{{ $project->old_project_id ?? '—' }}</td>
            </tr>
            <tr>
                <th>Project Title</th>
                <td>{{ $project->title }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ===== PROGRESS REPORT 1 REMARKS ===== --}}
    @if($progressGradings->count())
    <div class="rc-section">
        <div class="rc-section-title">Progress Report 1 Remarks <span class="rc-count">{{ $progressGradings->count() }} reviewer(s)</span></div>
        @foreach($progressGradings as $g)
            <table class="rc-table">
                <tbody>
                    <tr>
                        <th class="rc-reviewer" rowspan="6" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                        <th style="width:22px;">#</th>
                        <th style="width:200px;">Criteria</th>
                        <th style="width:46px;">Rating</th>
                        <th>Comment</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="rc-crit">Progress Toward Achieving Outcomes</td>
                        <td class="rc-rating">{{ $g->achievementsRatingRef->rating ?? $g->achievementsRating ?? '—' }}</td>
                        <td>{{ $g->achievementsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="rc-crit">Progress in Publications</td>
                        <td class="rc-rating">{{ $g->publicationsRatingRef->rating ?? $g->publicationsRating ?? '—' }}</td>
                        <td>{{ $g->publicationsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td class="rc-crit">Student Involvement &amp; Capacity Building</td>
                        <td class="rc-rating">{{ $g->studentsRatingRef->rating ?? $g->studentsRating ?? '—' }}</td>
                        <td>{{ $g->studentsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td class="rc-crit">Budget Utilization</td>
                        <td class="rc-rating">{{ $g->budgetRatingRef->rating ?? $g->budgetRating ?? '—' }}</td>
                        <td>{{ $g->budgetComments ?? '—' }}</td>
                    </tr>
                    <tr class="rc-rec">
                        <td>5</td>
                        <td class="rc-crit">Recommendation for Continuation</td>
                        <td colspan="2">
                            <span class="{{ $g->isAccepted == 1 ? 'rc-ok' : 'rc-no' }}">
                                {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    </div>
    @endif

    {{-- ===== PROGRESS REPORT 2 REMARKS ===== --}}
    @if($progress2Gradings->count())
    <div class="rc-section">
        <div class="rc-section-title">Progress Report 2 Remarks <span class="rc-count">{{ $progress2Gradings->count() }} reviewer(s)</span></div>
        @foreach($progress2Gradings as $g)
            <table class="rc-table">
                <tbody>
                    <tr>
                        <th class="rc-reviewer" rowspan="6" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                        <th style="width:22px;">#</th>
                        <th style="width:200px;">Criteria</th>
                        <th style="width:46px;">Rating</th>
                        <th>Comment</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="rc-crit">Progress Toward Achieving Outcomes</td>
                        <td class="rc-rating">{{ $g->achievementsRatingRef->rating ?? $g->achievementsRating ?? '—' }}</td>
                        <td>{{ $g->achievementsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="rc-crit">Progress in Publications</td>
                        <td class="rc-rating">{{ $g->publicationsRatingRef->rating ?? $g->publicationsRating ?? '—' }}</td>
                        <td>{{ $g->publicationsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td class="rc-crit">Student Involvement &amp; Capacity Building</td>
                        <td class="rc-rating">{{ $g->studentsRatingRef->rating ?? $g->studentsRating ?? '—' }}</td>
                        <td>{{ $g->studentsComments ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td class="rc-crit">Budget Utilization</td>
                        <td class="rc-rating">{{ $g->budgetRatingRef->rating ?? $g->budgetRating ?? '—' }}</td>
                        <td>{{ $g->budgetComments ?? '—' }}</td>
                    </tr>
                    <tr class="rc-rec">
                        <td>5</td>
                        <td class="rc-crit">Recommendation for Continuation</td>
                        <td colspan="2">
                            <span class="{{ $g->isAccepted == 1 ? 'rc-ok' : 'rc-no' }}">
                                {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    </div>
    @endif

    {{-- ===== FINAL REPORT EVALUATION ===== --}}
    @if($finalGradings->count())
    <div class="rc-section">
        <div class="rc-section-title">Final Report Evaluation <span class="rc-count">{{ $finalGradings->count() }} reviewer(s)</span></div>
        @foreach($finalGradings as $g)
            @php
                $grades = [$g->gradeA ?? 0, $g->gradeB ?? 0, $g->gradeC ?? 0, $g->gradeD ?? 0];
                $sumGrades = array_sum($grades);
                $avgGrades = count($grades) ? round($sumGrades / count($grades), 2) : 0;
            @endphp
            <table class="rc-table">
                <tbody>
                    <tr>
                        <th class="rc-reviewer" rowspan="5" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                        <th style="width:22px;">#</th>
                        <th style="width:200px;">Criteria</th>
                        <th style="width:46px;">Score</th>
                        <th>Comment</th>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td class="rc-crit">Achievements against Objectives</td>
                        <td class="rc-rating">{{ $g->gradeA ?? '—' }}/5</td>
                        <td>{{ $g->commentA ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="rc-crit">Publications &amp; IP</td>
                        <td class="rc-rating">{{ $g->gradeB ?? '—' }}/5</td>
                        <td>{{ $g->commentB ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td class="rc-crit">Student &amp; Young Researcher Involvement</td>
                        <td class="rc-rating">{{ $g->gradeC ?? '—' }}/5</td>
                        <td>{{ $g->commentC ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td class="rc-crit">Project Impact</td>
                        <td class="rc-rating">{{ $g->gradeD ?? '—' }}/5</td>
                        <td>{{ $g->commentD ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin:-2px 0 8px;font-size:9px;flex-wrap:wrap;">
                <span><b>Sum:</b> <span style="color:var(--brand-600,#8d1b3d);font-weight:600;">{{ $sumGrades }}</span></span>
                <span><b>Average:</b> <span style="color:var(--brand-600,#8d1b3d);font-weight:600;">{{ $avgGrades }}</span></span>
                <span>
                    <b>Status:</b>
                    @if($g->isAccepted == 1)
                        <span class="rc-badge-ok" style="display:inline-block;padding:1px 7px;border-radius:999px;font-size:8px;font-weight:600;border:1px solid #cfeade;background:#e8f5ee;color:#2e8b57;">Accepted</span>
                    @else
                        <span class="rc-badge-no" style="display:inline-block;padding:1px 7px;border-radius:999px;font-size:8px;font-weight:600;border:1px solid #f6d3d4;background:#fdecec;color:#c0392b;">Rejected</span>
                    @endif
                </span>
            </div>
        @endforeach
    </div>
    @endif

    {{-- ===== SUMMARY (QR + Totals) ===== --}}
    <div class="rc-summary">
        <div class="rc-summary-qr">
            <img src="{{ $qrImg }}" alt="Verification QR" onerror="this.style.display='none'">
            <div class="rc-qr-label">Scan to<br>Verify</div>
        </div>
        <table class="rc-summary-table">
            <tbody>
                <tr>
                    <td>Document ID</td>
                    <td>{{ $project->old_project_id ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Overall Status</td>
                    <td>
                        @php
                            $overallOk = ($finalGradings->count() && $finalGradings->first()->isAccepted == 1)
                                || ($finalGradings->isEmpty() && $progressGradings->count() && $progressGradings->first()->isAccepted == 1);
                        @endphp
                        @if(($finalGradings->count() && $finalGradings->first()->isAccepted == 1) || ($finalGradings->isEmpty() && $progressGradings->count() && $progressGradings->first()->isAccepted == 1))
                            <span style="display:inline-block;padding:1px 7px;border-radius:999px;font-size:8px;font-weight:600;border:1px solid #cfeade;background:#e8f5ee;color:#2e8b57;">{{ $finalGradings->count() ? ($finalGradings->first()->isAccepted == 1 ? 'Accepted' : 'Rejected') : 'Accepted' }}</span>
                        @else
                            <span style="display:inline-block;padding:1px 7px;border-radius:999px;font-size:8px;font-weight:600;border:1px solid #f3e3bd;background:#fdf6e6;color:#a67c18;">{{ $finalGradings->count() ? ($finalGradings->first()->isAccepted == 1 ? 'Accepted' : 'Rejected') : ($progressGradings->count() ? ($progressGradings->first()->isAccepted == 1 ? 'Accepted' : 'Rejected') : 'In Progress') }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Generated On</td>
                    <td>{{ now()->format('d M Y, h:i A') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ===== NOTES / DISCLAIMER ===== --}}
    <div class="rc-notes">
        <b>NOTES:</b> Please do not share the details contained within this document with unauthorized individuals.
    </div>

    {{-- ===== FOOTER: Document info only (logo is in header) ===== --}}
    <div class="rc-footer">
        <span>Research Tracking System &middot; Project Report Card</span>
        <span>Document ID: {{ $project->old_project_id ?? 'PROJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>

    {{-- ===== ACTIONS ===== --}}
    <div class="rc-actions">
        <button type="button" class="btn-print" onclick="printReportCard()">
            <i class="fas fa-print"></i> Print
        </button>
        <button type="button" class="btn-close-modal" data-bs-dismiss="modal">
            <i class="fas fa-check"></i> Close
        </button>
    </div>

</div>

{{-- Print Script: prints exactly the modal content --}}
<script>
function printReportCard() {
    var printContents = document.querySelector('#workflowModal .rc-report-card-inner');
    if (!printContents) return;

    var originalContents = document.body.innerHTML;

    // Create a print-only stylesheet in the popup
    var printWindow = window.open('', '_blank', 'width=900,height=700');
    if (!printWindow) {
        // Fallback: if popup blocked, try direct print
        window.print();
        return;
    }

    // Get all styles from the parent document
    var styles = '';
    document.querySelectorAll('style, link[rel="stylesheet"]').forEach(function(el) {
        if (el.tagName === 'STYLE') {
            styles += el.innerHTML + '\n';
        } else if (el.tagName === 'LINK' && el.rel === 'stylesheet') {
            styles += '<link rel="stylesheet" href="' + el.href + '">\n';
        }
    });

    printWindow.document.write('<!DOCTYPE html><html><head><title>Report Card</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { margin: 20px; }');
    printWindow.document.write('.rc-report-card-inner { padding: 20px 24px !important; max-width: 800px; margin: 0 auto; }');
    printWindow.document.write('@page { margin: 15mm; }');
    printWindow.document.write('</style>');
    printWindow.document.write(styles);
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContents.outerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();

    // Wait for images to load then print
    printWindow.onload = function() {
        setTimeout(function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}
</script>

{{-- Modal Footer fallback (hidden by default) --}}
<div style="display:none;"></div>