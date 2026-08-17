<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card — {{ $project->old_project_id }}</title>
    <style>
        :root {
            --ink: #1b1b1b;
            --gray: #6b6b6b;
            --hairline: #e6e6e4;
            --bg-row: #f7f7f6;
            --accent: #8d1b3d;
            --accent-soft: #f6f1f3;
            --ok: #2e7d57;
            --ok-tint: #eef7f2;
            --no: #b8432f;
            --no-tint: #faf1ef;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4; margin: 13mm 12mm; }

        body {
            font-family: -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 10px; color: var(--ink); line-height: 1.5;
            background: #ececea; padding: 24px 12px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        /* ─── A4 page ─── */
        .rc-page {
            width: 210mm; max-width: 100%; min-height: 297mm;
            margin: 0 auto; background: #fff;
            box-shadow: 0 3px 18px rgba(0,0,0,.12);
            display: flex; flex-direction: column;
        }
        .rc-page-inner { padding: 0 30px 24px; flex: 1; }

        /* ─── Print styles ─── */
        @media print {
            body { background: #fff; padding: 0; font-size: 9.5px; }
            .no-print { display: none !important; }
            .rc-page { box-shadow: none; width: 100%; min-height: 0; }
            .rc-section, table, .rc-verdict { page-break-inside: avoid; }
        }

        /* ─── Header ─── */
        .rc-header {
            padding: 22px 0 12px;
            display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;
            border-bottom: 1.5px solid var(--ink);
            position: relative;
        }
        .rc-header::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: -4px;
            height: 1px; background: var(--hairline);
        }
        .rc-header-brand { display: flex; align-items: center; gap: 11px; }
        .rc-header-brand img { width: 46px; height: auto; }
        .rc-brand-name { font-size: 13.5px; font-weight: 600; letter-spacing: .2px; }
        .rc-brand-sub { font-size: 8px; color: var(--gray); letter-spacing: 1.2px; text-transform: uppercase; margin-top: 2px; }
        .rc-header-doc { text-align: right; }
        .rc-doc-label { font-size: 8px; color: var(--gray); text-transform: uppercase; letter-spacing: 2px; }
        .rc-doc-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 24px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: var(--ink); line-height: 1.15; margin: 2px 0;
        }
        .rc-doc-ref { font-size: 9px; color: var(--gray); }

        /* ─── Meta strip ─── */
        .rc-meta {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 7px 0; margin: 14px 0 16px;
            border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline);
            font-size: 9px; color: var(--gray);
        }
        .rc-meta b { font-weight: 600; color: var(--ink); }
        .rc-meta .rc-confidential { letter-spacing: 1.5px; text-transform: uppercase; font-size: 8px; }

        /* ─── Project info ─── */
        .rc-info { width: 100%; border-collapse: collapse; margin-bottom: 18px; border: 1px solid var(--hairline); }
        .rc-info th {
            text-align: left; padding: 6px 12px; font-weight: 600; font-size: 8px;
            color: var(--ink); width: 140px; letter-spacing: .4px; text-transform: uppercase;
            background: var(--bg-row); border-bottom: 1px solid var(--hairline);
        }
        .rc-info td { padding: 6px 12px; border-bottom: 1px solid var(--hairline); background: #fff; font-size: 10px; font-weight: 400; }
        .rc-info tr:last-child th, .rc-info tr:last-child td { border-bottom: none; }

        /* ─── Section headings ─── */
        .rc-section { margin-bottom: 15px; }
        .rc-section-title {
            display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
            font-size: 10px; font-weight: 600; color: var(--ink);
            text-transform: uppercase; letter-spacing: .8px;
            margin-bottom: 8px; padding-bottom: 5px;
            border-bottom: 1px solid var(--hairline);
        }
        .rc-section-title .rc-count {
            font-size: 8px; font-weight: 500; color: var(--gray);
            background: var(--bg-row); padding: 2px 9px; border-radius: 2px;
            margin-left: auto;
        }

        /* ─── Remarks tables ─── */
        .rc-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid var(--hairline); }
        .rc-table th {
            text-align: left; padding: 5px 9px; font-size: 7.5px;
            font-weight: 600; color: var(--ink); letter-spacing: .4px; text-transform: uppercase;
            border: 1px solid var(--hairline); background: var(--bg-row);
        }
        .rc-table td { padding: 5px 9px; border: 1px solid var(--hairline); vertical-align: top; background: #fff; }
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
            font-weight: 500; color: var(--ink); background: var(--bg-row);
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

        /* ─── Final evaluation totals ─── */
        .rc-totals { display: flex; justify-content: flex-end; gap: 8px; margin: -2px 0 12px; }
        .rc-chip {
            display: inline-flex; align-items: center; gap: 5px;
            border: 1px solid var(--hairline); background: var(--bg-row);
            color: var(--ink); border-radius: 2px;
            padding: 2px 10px; font-size: 8.5px; font-weight: 500;
        }
        .rc-chip b { color: var(--accent); font-weight: 600; }

        /* ─── Summary / verdict ─── */
        .rc-verdict {
            display: flex; align-items: stretch; margin-top: 6px;
            border: 1px solid var(--hairline); border-top: 2px solid var(--accent);
        }
        .rc-verdict-side {
            flex: 0 0 116px; padding: 12px 10px;
            display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 6px;
            border-right: 1px solid var(--hairline);
        }
        .rc-verdict-side img { width: 58px; height: 58px; border: 1px solid var(--hairline); }
        .rc-qr-label { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: var(--gray); }
        .rc-verdict-main { flex: 1; padding: 10px 14px; display: flex; flex-direction: column; justify-content: center; }
        .rc-verdict-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 5px 0; border-bottom: 1px solid var(--hairline); font-size: 9.5px; }
        .rc-verdict-row:last-child { border-bottom: none; }
        .rc-verdict-row .rc-label { font-weight: 500; color: var(--gray); text-transform: uppercase; letter-spacing: .4px; font-size: 8px; }
        .rc-verdict-row .rc-value { font-weight: 500; color: var(--ink); text-align: right; }
        .rc-verdict-row .rc-badge { font-size: 8.5px; }

        /* ─── Notes / footer ─── */
        .rc-notes {
            margin-top: 13px; padding: 7px 12px;
            border: 1px solid var(--hairline); background: var(--bg-row);
            font-size: 8.5px; color: var(--gray); line-height: 1.5;
        }
        .rc-notes b { color: var(--ink); font-weight: 600; }
        .rc-footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 13px; padding-top: 8px; border-top: 1px solid var(--ink);
            font-size: 8px; color: var(--gray);
        }
        .rc-footer img { width: 28px; opacity: .45; }

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
        <button class="rc-btn rc-btn-secondary" onclick="window.history.back()">&#8592; Back</button>
        <button class="rc-btn rc-btn-primary" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>

    <div class="rc-page">
        <div class="rc-header">
            <div class="rc-header-brand">
                <img src="{{ asset('images/research_logo.png') }}" alt="Research Logo" onerror="this.style.display='none'">
                <div>
                    <div class="rc-brand-name">Research Tracking System</div>
                    <div class="rc-brand-sub">Institutional Research &amp; Innovation</div>
                </div>
            </div>
            <div class="rc-header-doc">
                <div class="rc-doc-label">Evaluation Report</div>
                <div class="rc-doc-title">Report Card</div>
                <div class="rc-doc-ref">Ref: {{ $project->old_project_id ?? '—' }}</div>
            </div>
        </div>

        <div class="rc-page-inner">

            {{-- ═══════════ META STRIP ═══════════ --}}
            @php
                $hasFinal = $finalGradings->count() > 0;
                $hasProgress = $progressGradings->count() > 0;
                $finalAccepted = $hasFinal && $finalGradings->first()->isAccepted == 1;
                $progressAccepted = $hasProgress && $progressGradings->first()->isAccepted == 1;
                $overallLabel = $hasFinal ? ($finalAccepted ? 'Accepted' : 'Rejected')
                    : ($hasProgress ? ($progressAccepted ? 'Accepted' : 'Rejected') : 'In Progress');
                $overallOk = $hasFinal ? $finalAccepted : $progressAccepted;
            @endphp
            <div class="rc-meta">
                <span><b>Project ID:</b> {{ $project->old_project_id ?? '—' }}</span>
                <span><b>Generated:</b> {{ now()->format('d M Y, h:i A') }}</span>
                <span>
                    <b>Status:</b>
                    <span class="rc-badge {{ $overallOk ? 'rc-badge-ok' : ($overallLabel === 'Rejected' ? 'rc-badge-no' : 'rc-badge-amber') }}">{{ $overallLabel }}</span>
                </span>
                <span class="rc-confidential">Confidential</span>
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
                        </tbody>
                    </table>
                    <div class="rc-totals">
                        <span class="rc-chip"><b>Sum:</b> {{ $sumGrades }}</span>
                        <span class="rc-chip"><b>Average:</b> {{ $avgGrades }}</span>
                        <span class="rc-chip">
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

            {{-- ═══════════ SUMMARY / VERDICT ═══════════ --}}
            <div class="rc-verdict">
                <div class="rc-verdict-side">
                    @php
                        $qrUrl = route('projects.report-card', $project->id);
                        $qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrUrl);
                    @endphp
                    <img src="{{ $qrImg }}" alt="Verification QR" onerror="this.style.display='none'">
                    <div class="rc-qr-label">Scan to Verify</div>
                </div>
                <div class="rc-verdict-main">
                    <div class="rc-verdict-row">
                        <span class="rc-label">Document ID</span>
                        <span class="rc-value">{{ $project->old_project_id ?? '—' }}</span>
                    </div>
                    <div class="rc-verdict-row">
                        <span class="rc-label">Overall Status</span>
                        <span class="rc-badge {{ $overallOk ? 'rc-badge-ok' : ($overallLabel === 'Rejected' ? 'rc-badge-no' : 'rc-badge-amber') }}">{{ $overallLabel }}</span>
                    </div>
                    <div class="rc-verdict-row">
                        <span class="rc-label">Generated On</span>
                        <span class="rc-value">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>

            {{-- ═══════════ NOTES ═══════════ --}}
            <div class="rc-notes">
                <b>NOTES:</b> Please do not share the details contained within this document with unauthorized individuals.
            </div>

            {{-- ═══════════ FOOTER ═══════════ --}}
            <div class="rc-footer">
                <span>Research Tracking System &middot; Project Report Card</span>
                <span>Page 1 of 1</span>
                <img src="{{ asset('images/research_logo.png') }}" alt="" onerror="this.style.display='none'">
            </div>

        </div>
    </div>
</body>
</html>