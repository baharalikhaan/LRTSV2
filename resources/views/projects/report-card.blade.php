<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $project->old_project_id }}</title>
    <style>
        :root {
            --ink: #27232a;
            --gray: #68636d;
            --hairline: #dedbe0;
            --bg-row: #f0eef1;
            --accent: #8d1b3d;
            --accent-soft: #f4e5ea;
            --ok: #28724e;
            --ok-tint: #edf6f0;
            --no: #a93434;
            --no-tint: #fbefef;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 10mm; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px; color: var(--ink); line-height: 1.42;
            background: #ececea; padding: 24px 12px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        /* ─── A4 page ─── */
        .rc-page {
            width: 210mm; max-width: 100%; min-height: 297mm;
            margin: 0 auto; background: #fff;
            box-shadow: 0 3px 18px rgba(34,22,29,.12);
            display: flex; flex-direction: column;
        }
        .rc-page-inner { padding: 0 24px 20px; flex: 1; display: flex; flex-direction: column; }
        .rc-page-inner > *:not(.rc-notes):not(.rc-footer) { flex-shrink: 0; }

        /* ─── Print styles ─── */
        @media print {
            body { background: #fff; padding: 0; font-size: 9px; }
            .no-print { display: none !important; }
            .rc-page { box-shadow: none; width: 100%; min-height: 100vh; }
            .rc-section, table, .rc-summary { page-break-inside: avoid; }
            .rc-header-doc { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        /* ─── Header ─── */
        .rc-header {
            display: grid; grid-template-columns: 1.5fr 1fr; align-items: stretch;
            min-height: 88px; overflow: hidden;
            position: relative;
            margin: 14px 24px 0;
        }
        .rc-header::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: 0;
            height: 1px; background: var(--accent);
        }
        .rc-header-brand { display: flex; align-items: center; padding: 10px 20px 10px 0; }
        .rc-header-brand img {
            display: block; width: 100%; max-width: none; max-height: none; height: auto;
            object-fit: contain;
        }
        .rc-header-doc {
            text-align: right; color: #fff; background: var(--accent);
            clip-path: polygon(13% 0, 100% 0, 100% 100%, 0 100%);
            padding: 13px 16px 12px 30px; display: flex; flex-direction: column;
            justify-content: center; align-items: flex-end;
            align-self: end;
        }
        .rc-doc-label {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px; color: rgba(255,255,255,.84); text-transform: uppercase;
            letter-spacing: 1.5px; font-weight: 600;
        }
        .rc-doc-title {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 25px; font-weight: 600; letter-spacing: .2px;
            text-transform: uppercase; color: #fff; line-height: 1.12; margin: 0 0 2px;
        }
        .rc-doc-ref {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px; color: rgba(255,255,255,.95); letter-spacing: .6px;
        }

        /* ─── Project info ─── */
        .rc-info { width: 100%; border-collapse: collapse; margin-top: 16px; margin-bottom: 18px; background: #e8e7e9; }
        .rc-info th {
            text-align: left; padding: 8px 12px; font-weight: 700; font-size: 10px;
            color: var(--ink); width: 185px; border-right: 1px solid #d6d3d8;
            border-bottom: 1px solid #d6d3d8;
        }
        .rc-info td { padding: 8px 12px; border-bottom: 1px solid #d6d3d8; background: transparent; font-size: 10px; font-weight: 400; }
        .rc-info tr:last-child th, .rc-info tr:last-child td { border-bottom: none; }
        .rc-project-title { text-transform: uppercase; font-weight: 500 !important; }

        /* ─── Section headings ─── */
        .rc-section { margin-bottom: 18px; }
        .rc-section-title {
            display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
            font-size: 15px; font-weight: 700; color: var(--accent);
            text-transform: none; letter-spacing: 0;
            margin-bottom: 7px; padding-bottom: 2px;
        }
        .rc-section-title .rc-count {
            font-size: 8px; font-weight: 500; color: var(--gray);
            background: var(--accent-soft); color: var(--accent); padding: 2px 9px; border-radius: 2px;
            margin-left: auto;
        }

        /* ─── Remarks tables ─── */
        .rc-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid var(--hairline); }
        .rc-table th {
            text-align: left; padding: 6px 9px; font-size: 9px;
            font-weight: 700; color: var(--ink); letter-spacing: .2px;
            border: 1px solid var(--hairline); background: var(--accent-soft);
        }
        .rc-table td { padding: 6px 9px; border: 1px solid var(--hairline); vertical-align: top; background: #fff; }
        .rc-table td.rc-crit { font-weight: 500; font-size: 9.5px; }
        .rc-table td.rc-num { color: var(--gray); font-weight: 400; }
        .rc-table td.rc-rating { font-weight: 600; color: var(--accent); white-space: nowrap; text-align: center; }
        .rc-table tr.rc-rec td { background: var(--accent-soft); font-weight: 500; }
        .rc-table .rc-ok { color: var(--ok); font-weight: 600; }
        .rc-table .rc-no { color: var(--no); font-weight: 600; }

        /* Rotated reviewer label */
        .rc-reviewer {
            writing-mode: vertical-rl; transform: rotate(180deg);
            text-align: center; white-space: nowrap;
            font-weight: 600; color: var(--ink); background: var(--accent-soft);
            font-size: 8px; letter-spacing: .5px; width: 24px;
            border: 1px solid var(--hairline) !important; vertical-align: middle;
        }

        /* ─── Badges ─── */
        .rc-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 1px 9px; border-radius: 2px;
            font-size: 8px; font-weight: 500; border: 1px solid var(--hairline);
        }
        .rc-badge::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: currentColor; }
        .rc-badge-ok { background: var(--ok-tint); color: var(--ok); border-color: #d8e8df; }
        .rc-badge-no { background: var(--no-tint); color: var(--no); border-color: #ebd8d3; }
        .rc-badge-amber { background: var(--bg-row); color: var(--gray); border-color: var(--hairline); }

        /* ─── Final score summary ─── */
        .rc-summary {
            display: flex; justify-content: flex-end;
            margin: 14px 0 12px; padding-bottom: 12px;
        }
        .rc-score-summary { width: 320px; max-width: 100%; border-collapse: collapse; }
        .rc-score-summary th, .rc-score-summary td { padding: 7px 9px; border: 1px solid var(--hairline); }
        .rc-score-summary th { width: 68%; background: #e8e7e9; text-align: left; font-size: 9px; }
        .rc-score-summary td { background: #fff; text-align: center; font-weight: 700; color: var(--accent); }
        .rc-empty-evaluation { padding: 9px 10px; background: var(--bg-row); color: var(--gray); font-style: italic; }

        /* ─── Notes / footer ─── */
        .rc-notes {
            margin-top: auto; padding: 8px 0 0;
            border-top: 1px solid var(--hairline);
            font-size: 9px; color: var(--gray); line-height: 1.5;
        }
        .rc-notes b { color: var(--ink); font-weight: 600; }
        .rc-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 11px; padding-top: 7px; border-top: 1px solid var(--hairline);
            font-size: 8px; color: var(--gray);
        }

        @media screen and (max-width: 640px) {
            .rc-header { grid-template-columns: 1fr; }
            .rc-header-doc { clip-path: none; padding: 10px 14px; }
            .rc-page-inner { padding: 0 14px 16px; }
            .rc-info th { width: 120px; }
        }

        /* ─── Action buttons ─── */
        .rc-actions { position: fixed; top: 14px; right: 14px; display: flex; gap: 8px; z-index: 50; }
        .rc-btn {
            display: inline-flex; align-items: center; gap: 6px;
            border: 1px solid var(--hairline); cursor: pointer; font-family: inherit;
            padding: 7px 15px; border-radius: 4px; font-size: 11px; font-weight: 500;
            box-shadow: 0 1px 6px rgba(0,0,0,.08); transition: transform .12s;
        }
        .rc-btn:hover { transform: translateY(-1px); }
        .rc-btn-primary { background: var(--accent); color: #fff; border-color: var(--accent); }
        .rc-btn-secondary { background: #fff; color: var(--gray); }
    </style>
</head>
<body>
    <div class="rc-actions no-print">
        <button class="rc-btn rc-btn-secondary" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('reports.report-cards') }}'; }">&#8592; Back</button>
        <button class="rc-btn rc-btn-primary" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>

    <div class="rc-page">
        <div class="rc-header">
            <div class="rc-header-brand">
                <img src="{{ asset('images/research_logo.png') }}" alt="Qatar University — Office of VP for Research and Graduate Studies">
            </div>
            <div class="rc-header-doc">
                <div class="rc-doc-title">Report Card</div>
                <div class="rc-doc-ref">Detailed Project Evaluation</div>
            </div>
        </div>

        <div class="rc-page-inner">

            {{-- ═══════════ REPORT STATUS ═══════════ --}}
            @php
                $hasFinal = $finalGradings->count() > 0;
                $hasProgress = $progressGradings->count() > 0;
                $hasProgress2 = $progress2Gradings->count() > 0;
                $finalAccepted = $hasFinal && $finalGradings->first()->isAccepted == 1;
                $progressAccepted = $hasProgress2
                    ? $progress2Gradings->first()->isAccepted == 1
                    : ($hasProgress && $progressGradings->first()->isAccepted == 1);
                $overallLabel = $hasFinal ? ($finalAccepted ? 'Accepted' : 'Rejected')
                    : (($hasProgress || $hasProgress2) ? ($progressAccepted ? 'Accepted' : 'Rejected') : 'In Progress');
                $overallOk = $hasFinal ? $finalAccepted : $progressAccepted;
            @endphp

            {{-- ═══════════ PROJECT INFO ═══════════ --}}
            <table class="rc-info">
                <tbody>
                    <tr>
                        <th>Project ID</th>
                        <td>{{ $project->old_project_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Project Title</th>
                        <td class="rc-project-title">{{ $project->title }}</td>
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
                                <td class="rc-num">1</td>
                                <td class="rc-crit">Progress Toward Achieving Outcomes</td>
                                <td class="rc-rating">{{ $g->achievementsRatingRef->rating ?? $g->achievementsRating ?? '—' }}</td>
                                <td>{{ $g->achievementsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">2</td>
                                <td class="rc-crit">Progress in Publications</td>
                                <td class="rc-rating">{{ $g->publicationsRatingRef->rating ?? $g->publicationsRating ?? '—' }}</td>
                                <td>{{ $g->publicationsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">3</td>
                                <td class="rc-crit">Student Involvement &amp; Capacity Building</td>
                                <td class="rc-rating">{{ $g->studentsRatingRef->rating ?? $g->studentsRating ?? '—' }}</td>
                                <td>{{ $g->studentsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">4</td>
                                <td class="rc-crit">Budget Utilization</td>
                                <td class="rc-rating">{{ $g->budgetRatingRef->rating ?? $g->budgetRating ?? '—' }}</td>
                                <td>{{ $g->budgetComments ?? '—' }}</td>
                            </tr>
                            <tr class="rc-rec">
                                <td class="rc-num">5</td>
                                <td class="rc-crit">Recommendation for Continuation</td>
                                <td colspan="2">
                                    <span class="rc-badge {{ $g->isAccepted == 1 ? 'rc-badge-ok' : 'rc-badge-no' }}">
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
                                <td class="rc-num">1</td>
                                <td class="rc-crit">Progress Toward Achieving Outcomes</td>
                                <td class="rc-rating">{{ $g->achievementsRatingRef->rating ?? $g->achievementsRating ?? '—' }}</td>
                                <td>{{ $g->achievementsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">2</td>
                                <td class="rc-crit">Progress in Publications</td>
                                <td class="rc-rating">{{ $g->publicationsRatingRef->rating ?? $g->publicationsRating ?? '—' }}</td>
                                <td>{{ $g->publicationsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">3</td>
                                <td class="rc-crit">Student Involvement &amp; Capacity Building</td>
                                <td class="rc-rating">{{ $g->studentsRatingRef->rating ?? $g->studentsRating ?? '—' }}</td>
                                <td>{{ $g->studentsComments ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">4</td>
                                <td class="rc-crit">Budget Utilization</td>
                                <td class="rc-rating">{{ $g->budgetRatingRef->rating ?? $g->budgetRating ?? '—' }}</td>
                                <td>{{ $g->budgetComments ?? '—' }}</td>
                            </tr>
                            <tr class="rc-rec">
                                <td class="rc-num">5</td>
                                <td class="rc-crit">Recommendation for Continuation</td>
                                <td colspan="2">
                                    <span class="rc-badge {{ $g->isAccepted == 1 ? 'rc-badge-ok' : 'rc-badge-no' }}">
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
            <div class="rc-section">
                <div class="rc-section-title">
                    Final Report Evaluation
                    <span class="rc-count">{{ $finalGradings->count() ? $finalGradings->count() . ' reviewer(s)' : 'Not yet graded' }}</span>
                </div>
                @if($finalGradings->count())
                    @foreach($finalGradings as $g)
                    <table class="rc-table">
                        <tbody>
                            <tr>
                                <th class="rc-reviewer" rowspan="6" style="text-align:center;">Reviewer {{ $loop->iteration }}</th>
                                <th style="width:24px;">#</th>
                                <th style="width:230px;">Criteria</th>
                                <th style="width:52px;">Score</th>
                                <th>Comment</th>
                            </tr>
                            <tr>
                                <td class="rc-num">1</td>
                                <td class="rc-crit">Achievements against Objectives</td>
                                <td class="rc-rating">{{ $g->gradeA ?? '—' }}/5</td>
                                <td>{{ $g->commentA ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">2</td>
                                <td class="rc-crit">Publications &amp; IP</td>
                                <td class="rc-rating">{{ $g->gradeB ?? '—' }}/5</td>
                                <td>{{ $g->commentB ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">3</td>
                                <td class="rc-crit">Student &amp; Young Researcher Involvement</td>
                                <td class="rc-rating">{{ $g->gradeC ?? '—' }}/5</td>
                                <td>{{ $g->commentC ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="rc-num">4</td>
                                <td class="rc-crit">Project Impact</td>
                                <td class="rc-rating">{{ $g->gradeD ?? '—' }}/5</td>
                                <td>{{ $g->commentD ?? '—' }}</td>
                            </tr>
                            <tr class="rc-rec">
                                <td class="rc-num">5</td>
                                <td class="rc-crit">Overall Recommendation</td>
                                <td colspan="2">
                                    <span class="rc-badge {{ $g->isAccepted == 1 ? 'rc-badge-ok' : 'rc-badge-no' }}">
                                        {{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @endforeach
                @else
                    <p class="rc-empty-evaluation">No final report evaluation has been recorded.</p>
                @endif
            </div>

            {{-- ═══════════ SCORE SUMMARY ═══════════ --}}
            @php
                $finalScoreSum = 0;
                $finalScoreCount = 0;
                foreach ($finalGradings as $finalGrading) {
                    foreach ([$finalGrading->gradeA, $finalGrading->gradeB, $finalGrading->gradeC, $finalGrading->gradeD] as $score) {
                        if (is_numeric($score)) {
                            $finalScoreSum += (float) $score;
                            $finalScoreCount++;
                        }
                    }
                }
                $finalScoreAverage = $finalScoreCount ? $finalScoreSum / $finalScoreCount : null;
            @endphp
            <div class="rc-summary">
                <table class="rc-score-summary">
                    <tbody>
                        <tr>
                            <th>Sum of Grades</th>
                            <td>{{ $finalScoreCount ? rtrim(rtrim(number_format($finalScoreSum, 2, '.', ''), '0'), '.') : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Average Grades</th>
                            <td>{{ $finalScoreAverage !== null ? number_format($finalScoreAverage, 2) : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Overall Recommendation</th>
                            <td>
                                <span class="rc-badge {{ $overallOk ? 'rc-badge-ok' : ($overallLabel === 'Rejected' ? 'rc-badge-no' : 'rc-badge-amber') }}">{{ $overallLabel }}</span>
                            </td>
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
                <span>Generated {{ now()->format('d M Y') }} &middot; Confidential</span>
            </div>

        </div>
    </div>
</body>
</html>
