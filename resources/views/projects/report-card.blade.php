<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $project->old_project_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 10.5px; color: #3d3d3d; line-height: 1.45;
            background: #f4f5f7; padding: 14px;
        }

        /* ─── Print styles ─── */
        @media print {
            body { background: #fff; padding: 0; font-size: 10px; }
            .no-print { display: none !important; }
            .rc-page { box-shadow: none; border: none; margin: 0; }
            .rc-section { page-break-inside: avoid; }
            table { page-break-inside: avoid; }
        }

        /* ─── Invoice page ─── */
        .rc-page {
            max-width: 800px; margin: 0 auto;
            background: #fff; border: 1px solid #e4e7eb;
            border-radius: 8px; overflow: hidden;
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }
        .rc-page-inner { padding: 20px 26px; }

        /* ─── Header ─── */
        .rc-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .rc-header-left { display: flex; align-items: center; gap: 10px; }
        .rc-header-left img { width: 62px; height: auto; }
        .rc-header-right { text-align: right; }
        .rc-header-right .rc-doc-title {
            font-size: 18px; font-weight: 600; letter-spacing: 1px;
            color: #8d1b3d; text-transform: uppercase; line-height: 1.1;
        }
        .rc-header-right .rc-doc-sub {
            font-size: 8px; color: #9a9ea5; text-transform: uppercase;
            letter-spacing: 2px; font-weight: 500; margin-top: 3px;
        }

        /* ─── Project info table ─── */
        .rc-info { width: 100%; border-collapse: collapse; margin-bottom: 14px; background: #fafbfc; }
        .rc-info th {
            text-align: left; padding: 6px 10px; font-weight: 600; font-size: 9.5px;
            color: #8d1b3d; width: 120px; border: 1px solid #eceff1;
        }
        .rc-info td { padding: 6px 10px; border: 1px solid #eceff1; font-weight: 400; }

        /* ─── Section headings ─── */
        .rc-section { margin-bottom: 10px; }
        .rc-section-title {
            font-size: 10px; font-weight: 600; color: #8d1b3d;
            padding: 3px 0; margin-bottom: 6px;
            border-bottom: 1px solid #eee3e7;
            display: flex; align-items: center; justify-content: space-between;
        }
        .rc-section-title .rc-count {
            font-size: 8px; font-weight: 500; color: #9a9ea5;
            background: #f3f4f6; padding: 1px 6px; border-radius: 999px;
        }

        /* ─── Remarks tables (per reviewer) ─── */
        .rc-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .rc-table th {
            text-align: left; padding: 5px 8px; font-size: 8.5px;
            font-weight: 600; color: #6b7078; border: 1px solid #eceff1;
            background: #fafbfc;
        }
        .rc-table td { padding: 5px 8px; border: 1px solid #eceff1; vertical-align: top; }
        .rc-table td.rc-crit { font-weight: 500; color: #3d3d3d; }
        .rc-table td.rc-rating { font-weight: 600; color: #8d1b3d; white-space: nowrap; text-align: center; }
        .rc-table tr.rc-rec td { background: #fafbfc; font-weight: 500; }
        .rc-table .rc-ok { color: #2e8b57; font-weight: 600; }
        .rc-table .rc-no { color: #c0392b; font-weight: 600; }

        /* Rotated reviewer label */
        .rc-reviewer {
            writing-mode: vertical-rl; transform: rotate(180deg);
            text-align: center; white-space: nowrap;
            font-weight: 600; color: #8d1b3d; background: #fbeef1;
            font-size: 8.5px; letter-spacing: .5px; width: 22px;
            padding: 6px 3px; border-bottom: 1px solid #e9d3d9;
        }

        /* ─── Grid helpers ─── */
        .rc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .rc-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; }
        .rc-box {
            border: 1px solid #eceff1; border-radius: 6px; padding: 5px 8px;
            background: #fafbfc;
        }
        .rc-box .rc-box-label {
            font-size: 7.5px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .3px; color: #9a9ea5; margin-bottom: 1px;
        }
        .rc-box .rc-box-value { font-size: 10px; font-weight: 500; color: #3d3d3d; }

        /* ─── Status badge ─── */
        .rc-badge {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            font-size: 8.5px; font-weight: 600; border: 1px solid transparent;
        }
        .rc-badge-ok { background: #e8f5ee; color: #2e8b57; border-color: #cfeade; }
        .rc-badge-no { background: #fdecec; color: #c0392b; border-color: #f6d3d4; }
        .rc-badge-info { background: #eaf1fb; color: #3d6db5; border-color: #d3e0f4; }
        .rc-badge-amber { background: #fdf6e6; color: #a67c18; border-color: #f3e3bd; }

        /* ─── Summary row (QR + totals) ─── */
        .rc-summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 6px; }
        .rc-summary-qr { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .rc-summary-qr img { width: 52px; height: 52px; border: 1px solid #eceff1; border-radius: 4px; }
        .rc-summary-qr .rc-qr-label {
            font-size: 6.5px; color: #9a9ea5; text-transform: uppercase;
            letter-spacing: .5px; font-weight: 600; line-height: 1.2;
        }
        .rc-summary-table { border-collapse: collapse; flex: 1; max-width: 260px; }
        .rc-summary-table td { padding: 5px 9px; border: 1px solid #eceff1; font-size: 9.5px; }
        .rc-summary-table td:first-child {
            background: #fafbfc; font-weight: 600; color: #6b7078; width: 120px;
        }
        .rc-summary-table td:last-child {
            font-weight: 600; color: #8d1b3d; text-align: center;
        }

        /* ─── Notes / footer ─── */
        .rc-notes {
            margin-top: 10px; padding-top: 6px; border-top: 1px solid #eceff1;
            font-size: 8.5px; color: #9a9ea5; line-height: 1.4;
        }
        .rc-notes b { color: #6b7078; font-weight: 600; }
        .rc-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 6px; padding-top: 6px; border-top: 1px solid #eceff1;
            font-size: 8px; color: #9a9ea5;
        }
        .rc-footer img { width: 32px; opacity: .5; }

        /* ─── Print / action buttons ─── */
        .rc-actions { position: fixed; top: 12px; right: 12px; display: flex; gap: 6px; z-index: 50; }
        .rc-btn {
            display: inline-flex; align-items: center; gap: 6px;
            border: none; cursor: pointer; font-family: inherit;
            padding: 6px 14px; border-radius: 6px; font-size: 11px; font-weight: 500;
            box-shadow: 0 1px 6px rgba(0,0,0,.1); transition: transform .1s;
        }
        .rc-btn:hover { transform: translateY(-1px); }
        .rc-btn-primary { background: #8d1b3d; color: #fff; }
        .rc-btn-secondary { background: #fff; color: #6b7078; border: 1px solid #e4e7eb; }
    </style>
</head>
<body>
    <div class="rc-actions no-print">
        <button class="rc-btn rc-btn-secondary" onclick="window.history.back()">&#8592; Back</button>
        <button class="rc-btn rc-btn-primary" onclick="window.print()">&#128424; Print</button>
    </div>

    <div class="rc-page">
        <div class="rc-page-inner">

            {{-- ═══════════ HEADER ═══════════ --}}
            <div class="rc-header">
                <div class="rc-header-left">
                    <img src="{{ asset('images/research_logo.png') }}" alt="Research Logo" onerror="this.style.display='none'">
                </div>
                <div class="rc-header-right">
                    <div class="rc-doc-title">Report Card</div>
                    <div class="rc-doc-sub">Project Evaluation</div>
                </div>
            </div>

            {{-- ═══════════ PROJECT INFO ═══════════ --}}
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

            {{-- ═══════════ PROGRESS REPORT 1 REMARKS ═══════════ --}}
            @if($progressGradings->count())
            <div class="rc-section">
                <div class="rc-section-title">Progress Report 1 Remarks <span class="rc-count">{{ $progressGradings->count() }} reviewer(s)</span></div>
                @foreach($progressGradings as $g)
                    <table class="rc-table">
                        <tbody>
                            <tr>
                                <th class="rc-reviewer" rowspan="6" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                                <th style="width:24px;">#</th>
                                <th style="width:230px;">Criteria</th>
                                <th style="width:52px;">Rating</th>
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

            {{-- ═══════════ PROGRESS REPORT 2 REMARKS ═══════════ --}}
            @if($progress2Gradings->count())
            <div class="rc-section">
                <div class="rc-section-title">Progress Report 2 Remarks <span class="rc-count">{{ $progress2Gradings->count() }} reviewer(s)</span></div>
                @foreach($progress2Gradings as $g)
                    <table class="rc-table">
                        <tbody>
                            <tr>
                                <th class="rc-reviewer" rowspan="6" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                                <th style="width:24px;">#</th>
                                <th style="width:230px;">Criteria</th>
                                <th style="width:52px;">Rating</th>
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

            {{-- ═══════════ FINAL REPORT EVALUATION ═══════════ --}}
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
                                <th style="width:24px;">#</th>
                                <th style="width:230px;">Criteria</th>
                                <th style="width:52px;">Score</th>
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
                    <div style="display:flex;justify-content:flex-end;gap:12px;margin:-3px 0 10px;font-size:9.5px;">
                        <span><b>Sum:</b> <span style="color:#8d1b3d;font-weight:600;">{{ $sumGrades }}</span></span>
                        <span><b>Average:</b> <span style="color:#8d1b3d;font-weight:600;">{{ $avgGrades }}</span></span>
                        <span>
                            <b>Status:</b>
                            @if($g->isAccepted == 1)
                                <span class="rc-badge rc-badge-ok">Accepted</span>
                            @else
                                <span class="rc-badge rc-badge-no">Rejected</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- ═══════════ SUMMARY ═══════════ --}}
            <div class="rc-summary">
                <div class="rc-summary-qr">
                    @php
                        $qrUrl = route('projects.report-card', $project->id);
                        $qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrUrl);
                    @endphp
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
                                <span class="rc-badge {{ $overallOk ? 'rc-badge-ok' : 'rc-badge-amber' }}">
                                    {{ $finalGradings->count() ? ($finalGradings->first()->isAccepted == 1 ? 'Accepted' : 'Rejected') : ($progressGradings->count() ? ($progressGradings->first()->isAccepted == 1 ? 'Accepted' : 'Rejected') : 'In Progress') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Generated On</td>
                            <td>{{ now()->format('d M Y, h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ═══════════ NOTES ═══════════ --}}
            <div class="rc-notes">
                <b>NOTES:</b> Please do not share the details contained within this document with unauthorized individuals.
            </div>

            {{-- ═══════════ FOOTER ═══════════ --}}
            <div class="rc-footer">
                <span>Research Tracking System &middot; Project Report Card</span>
                <img src="{{ asset('images/research_logo.png') }}" alt="" onerror="this.style.display='none'">
            </div>

        </div>
    </div>
</body>
</html>