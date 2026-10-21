<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details — {{ $project->old_project_id ?? $project->id }}</title>
    <style>
        :root {
            --ink: #27232a;
            --gray: #6d6875;
            --line: #e2e0e6;
            --line-soft: #edecef;
            --accent: #8d1b3d;
            --accent-soft: #f7ecef;
            --ok: #28724e;    --ok-soft: #edf6f0;
            --no: #a93434;    --no-soft: #fbefef;
            --amber: #8a6d1f; --amber-soft: #f7f2e2;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Printer-friendly stack: Arial/Helvetica first (no font substitution surprises) */
        body, table, th, td, .btn { font-family: Arial, Helvetica, "Segoe UI", sans-serif; }

        @page { size: A4 portrait; margin: 10mm 9mm; }

        body {
            font-size: 9px; font-weight: 400; color: var(--ink); line-height: 1.4;
            background: #ececea; padding: 18px 10px;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }

        .sheet {
            width: 210mm; max-width: 100%; margin: 0 auto; background: #fff;
            box-shadow: 0 2px 14px rgba(34,22,29,.12); padding: 9mm;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; width: 100%; margin: 0; padding: 0; }
        }

        /* ─── Header (one compact line) ─── */
        .pj-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding-bottom: 6px; }
        .pj-head img { width: 190px; max-width: 40%; height: auto; display: block; }
        .pj-head-r { text-align: right; line-height: 1.3; }
        .pj-eyebrow { font-size: 7.5px; letter-spacing: .16em; text-transform: uppercase; color: var(--gray); font-weight: 600; }
        .pj-title { font-size: 12px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: .05em; }
        .pj-ref { font-size: 8.5px; color: var(--gray); }
        .pj-rule { height: 1px; background: var(--accent); margin-bottom: 8px; }   /* hairline */

        /* ─── Section headings (light, with breathing room between sections) ─── */
        .pj-h {
            font-size: 10px; font-weight: 700; color: var(--accent);
            letter-spacing: .06em; text-transform: uppercase;
            border-bottom: 1px solid var(--accent);          /* hairline */
            padding-bottom: 3px;
            margin: 14px 0 6px;                               /* margin between sections */
            display: flex; align-items: baseline; gap: 6px;
        }
        .pj-h:first-child { margin-top: 0; }
        .pj-h .pj-n { margin-left: auto; font-size: 7.5px; font-weight: 500; color: var(--gray); text-transform: none; letter-spacing: 0; }
        .pj-sub { font-size: 8.5px; font-weight: 700; color: var(--gray); margin: 7px 0 3px; }
        .pj-h, .pj-sub { break-after: avoid; page-break-after: avoid; }

        /* ─── 2-column layout ─── */
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; align-items: start; }
        .row2 > * { min-width: 0; }

        /* ─── Identity grid: 3 pairs per row ─── */
        .pj-kv { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0 16px; }
        .pj-kv .kv { display: flex; justify-content: space-between; gap: 8px;
            padding: 2px 0; border-bottom: 1px dotted var(--line); }
        .pj-kv .kv .k { color: var(--gray); }
        .pj-kv .kv .v { font-weight: 700; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ─── Project name line ─── */
        .pj-name { font-size: 11.5px; font-weight: 700; line-height: 1.25; margin: 2px 0 6px; }
        .pj-id { font-size: 8px; color: var(--gray); letter-spacing: .06em; }

        /* ─── Tables: hairline rules, flow across pages with repeated headers ─── */
        table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-bottom: 4px; }
        th, td { border: 1px solid var(--line); padding: 3px 6px; text-align: left; vertical-align: top; line-height: 1.35; }
        th { background: var(--accent-soft); color: var(--accent); font-weight: 700; font-size: 7.5px;
             text-transform: uppercase; letter-spacing: .04em; }
        td.n { text-align: right; color: var(--gray); font-variant-numeric: tabular-nums; }
        td.strong { font-weight: 700; }
        tr:last-child td { background: #fff; }
        /* Long tables split across pages cleanly; the header row repeats. */
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        thead tr { break-inside: avoid; }
        /* Short tables stay whole so they never straddle a page break. */
        table.keep { break-inside: avoid; page-break-inside: avoid; }

        /* ─── Badges ─── */
        .badge { display: inline-flex; align-items: center; gap: 3px; padding: 0 6px; border-radius: 2px;
            font-size: 7.5px; font-weight: 600; border: 1px solid var(--line); line-height: 1.5; }
        .badge::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: currentColor; }
        .b-ok { background: var(--ok-soft); color: var(--ok); border-color: #d5e7dd; }
        .b-no { background: var(--no-soft); color: var(--no); border-color: #eed7d4; }
        .b-amber { background: var(--amber-soft); color: var(--amber); border-color: #e8dcba; }
        .b-info { background: var(--accent-soft); color: var(--accent); border-color: #ecd8de; }

        /* ─── Misc ─── */
        .empty { padding: 4px 6px; background: #f5f4f7; color: var(--gray); font-style: italic; margin-bottom: 4px; }
        .foot { margin-top: 8px; padding-top: 5px; border-top: 1px solid var(--line);
            display: flex; justify-content: space-between; font-size: 7.5px; color: var(--gray); }

        /* ─── Toolbar (screen only) ─── */
        .bar { position: fixed; top: 12px; right: 12px; display: flex; gap: 7px; z-index: 50; }
        .btn { display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--line);
            cursor: pointer; font-family: inherit; padding: 6px 13px; border-radius: 4px;
            font-size: 11px; font-weight: 500; background: #fff; color: var(--gray);
            box-shadow: 0 1px 5px rgba(0,0,0,.08); transition: transform .12s; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--accent); color: #fff; border-color: var(--accent); }
    </style>
</head>
<body>
    <div class="bar no-print">
        <button class="btn" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('projects.show', $project->id) }}'; }">&#8592; Back</button>
        <button class="btn btn-primary" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>

    @php
        $outcomes = $project->outcomes()->orderBy('created_at', 'desc')->get();
        $isViewer = auth()->user()->isReviewer() && !auth()->user()->isLPI() && !auth()->user()->isAdmin();

        $scholarlyTypes = [
            'journal_q1' => 'Journal articles (Web of Science — Q1)', 'journal_q2' => 'Journal articles (Web of Science — Q2)',
            'journal_q3' => 'Journal articles (Web of Science — Q3)', 'journal_q4' => 'Journal articles (Web of Science — Q4)',
            'conference' => 'Indexed international conferences', 'book' => 'Published Books',
            'edited_book' => 'Edited Books (collection)', 'book_chapter' => 'Book Chapters',
        ];
        $ipTypes = [
            'ip_disclosure' => 'Intellectual Property Disclosure', 'provisional_patent' => 'Provisional Patent',
            'patent_granted' => 'Patents Granted', 'open_source_sw' => 'Open Source Software',
            'startup' => 'Start-Up Created',
        ];
        $contribTypeKeys = array_merge(array_keys($ipTypes), ['cross_college', 'research_awards']);
        $scholarlyOutcomes = $outcomes->filter(fn($o) => !in_array($o->type, $contribTypeKeys));
        $ipOutcomes = $outcomes->whereIn('type', array_keys($ipTypes));
        $students = $project->students()->orderBy('role')->get();
        $researchers = $project->researchers()->orderBy('created_at')->get();
        $submissions = $project->submissions()->orderBy('created_at', 'desc')->get();
        $progressSubs = $submissions->whereIn('type', ['progress', 'progress2'])->values();
        $readinessSubs = $submissions->where('type', 'readiness')->values();
        $finalSubs = $submissions->where('type', 'final')->values();
        $ethicalSubs = $submissions->where('type', 'ethical')->values();

        $statusLabel = [
            'imported' => 'Imported', 'registered' => 'Registered', 'Assigned' => 'Assigned',
            'Claimed' => 'Claimed', 'proposal_rejected' => 'Proposal Rejected',
            'reviewer_unassigned' => 'Reviewer Un-assigned', 'progress_added' => 'Progress Report Added',
            'progress_reviewed' => 'Progress Report Reviewed', 'progress_rejected' => 'Progress Report Rejected',
            'progress2_added' => 'Progress Report 2 Added', 'progress2_reviewed' => 'Progress Report 2 Reviewed',
            'progress2_rejected' => 'Progress Report 2 Rejected', 'final_added' => 'Final Report Added',
            'final_rejected' => 'Final Report Rejected', 'Graded' => 'Graded',
        ];
        $flowStatus = $project->currentWorkflowStatus();
        $flowLabel = $statusLabel[$flowStatus] ?? ucfirst(str_replace('_', ' ', (string) ($flowStatus ?: 'imported')));

        $progressRows = fn($g) => [
            ['label' => 'Progress toward outcomes', 'rating' => $g->achievementsRatingRef->rating ?? $g->achievementsRating, 'comments' => $g->achievementsComments],
            ['label' => 'Progress in publications', 'rating' => $g->publicationsRatingRef->rating ?? $g->publicationsRating, 'comments' => $g->publicationsComments],
            ['label' => 'Student involvement', 'rating' => $g->studentsRatingRef->rating ?? $g->studentsRating, 'comments' => $g->studentsComments],
            ['label' => 'Budget utilization', 'rating' => $g->budgetRatingRef->rating ?? $g->budgetRating, 'comments' => $g->budgetComments],
        ];
        $commitmentStatus = static function (array $item) {
            $commit = $item['commit'] ?? $item['committed'] ?? $item['expected'] ?? null;
            if ($commit === null) return '—';

            if (array_key_exists('count', $item) && !array_key_exists('total', $item)) {
                return $item['count'] >= $commit ? 'On Track' : 'Behind';
            }

            $total = $item['total'] ?? $item['count'] ?? 0;
            $verified = $item['verified'] ?? $item['actual'] ?? 0;
            if ($verified < $commit) {
                return $total >= $commit ? 'In Progress' : 'Behind';
            }

            return 'On Track';
        };
        $verifiedLabel = static function ($value) {
            if ($value === true || $value === 1 || $value === '1') return 'Yes';
            if ($value === false || $value === 0 || $value === '0') return 'No';
            return $value ?? '—';
        };

        $allFiles = [];
        if ($project->proposal_filename) {
            $allFiles[] = ['type' => 'Proposal', 'name' => $project->proposal_filename, 'date' => null];
        }
        foreach ($progressSubs as $sub) {
            $allFiles[] = ['type' => ($sub->type === 'progress2' ? 'Progress Report 2' : 'Progress Report 1') . ($sub->version > 1 ? ' (v'.$sub->version.')' : ''), 'name' => $sub->stored_filename, 'date' => $sub->created_at?->format('d M Y')];
        }
        foreach ($finalSubs as $sub) {
            $allFiles[] = ['type' => 'Final Report' . ($sub->version > 1 ? ' (v'.$sub->version.')' : ''), 'name' => $sub->stored_filename, 'date' => $sub->created_at?->format('M d, Y')];
        }
        foreach ($readinessSubs as $sub) {
            $allFiles[] = ['type' => 'Readiness Report' . ($sub->version > 1 ? ' (v'.$sub->version.')' : ''), 'name' => $sub->stored_filename, 'date' => $sub->created_at?->format('M d, Y')];
        }
        foreach ($ethicalSubs as $sub) {
            $allFiles[] = ['type' => 'Ethical Approval' . ($sub->version > 1 ? ' (v'.$sub->version.')' : ''), 'name' => $sub->stored_filename, 'date' => $sub->created_at?->format('M d, Y')];
        }

        $gradingCount = $progressGradings->count() + $progress2Gradings->count() + $finalGradings->count();
    @endphp

    <div class="sheet">

        {{-- ═══ HEADER ═══ --}}
        <div class="pj-head">
            <img src="{{ asset('images/research_logo.png') }}" alt="Qatar University — Office of VP for Research and Graduate Studies">
            <div class="pj-head-r">
                <div class="pj-eyebrow">Research Tracking System</div>
                <div class="pj-title">Project Details</div>
                <div class="pj-ref">{{ $project->old_project_id ?? $project->id }} &middot; {{ now()->format('d M Y') }}</div>
            </div>
        </div>
        <div class="pj-rule"></div>

        {{-- ═══ IDENTITY ═══ --}}
        <div class="pj-id">{{ $project->old_project_id ?? '—' }}</div>
        <div class="pj-name">{{ $project->title }}</div>

        <div class="pj-kv">
            <div class="kv"><span class="k">Status</span><span class="v"><span class="badge b-info">{{ $flowLabel }}</span></span></div>
            <div class="kv"><span class="k">Lead PI</span><span class="v">{{ $project->lpi->name ?? '—' }}</span></div>
            <div class="kv"><span class="k">LPI email</span><span class="v">{{ $project->lpi?->email ?? '—' }}</span></div>
            <div class="kv"><span class="k">Grant</span><span class="v">{{ $project->grant->grant_code ?? $project->program?->grant?->grant_code ?? '—' }}</span></div>
            <div class="kv"><span class="k">Research call</span><span class="v">{{ $project->program?->program_title ?? '—' }}</span></div>
            <div class="kv"><span class="k">Cycle</span><span class="v">{{ $project->program?->cycle?->title ?? '—' }}{{ $project->program?->cycle?->year ? ' ('.$project->program->cycle->year.')' : '' }}</span></div>
            <div class="kv"><span class="k">Progress deadline</span><span class="v">{{ $project->program?->prog_rpt_deadline?->format('d M Y') ?? '—' }}</span></div>
            <div class="kv"><span class="k">Progress 2 deadline</span><span class="v">{{ $project->program?->prog_rpt2_deadline?->format('d M Y') ?? '—' }}</span></div>
            <div class="kv"><span class="k">Final deadline</span><span class="v">{{ $project->program?->final_rpt_deadline?->format('d M Y') ?? '—' }}</span></div>
        </div>

        {{-- ═══ COMMITMENTS — full width ═══ --}}
        <div class="pj-h">Commitments</div>
        <div class="row2">
            <div>
                @php $items = $commitmentsData['pubItems'] ?? []; @endphp
                <div class="pj-sub">Publications</div>
                @if(count($items))
                <table class="keep">
                    <thead><tr><th style="width:44%;">Item</th><th style="width:18%; text-align:right;">Committed</th><th style="width:18%; text-align:right;">Verified</th><th style="width:20%;">Status</th></tr></thead>
                    <tbody>
                        @foreach($items as $it)
                        @php
                            $st = $commitmentStatus($it);
                            $stColor = $st === 'On Track' ? 'var(--ok)' : ($st === 'In Progress' ? 'var(--amber)' : 'var(--gray)');
                        @endphp
                        <tr>
                            <td class="strong">{{ $it['label'] ?? $it['name'] ?? '—' }}</td>
                            <td class="n">{{ $it['commit'] ?? $it['committed'] ?? $it['expected'] ?? '—' }}</td>
                            <td style="text-align:center;">{{ $verifiedLabel($it['verified'] ?? $it['actual'] ?? $it['count'] ?? null) }}</td>
                            <td style="font-weight:600;color:{{ $stColor }};">{{ $st }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="empty">No publication commitments recorded.</p>
                @endif
            </div>
            <div>
                @foreach([
                    'IP & innovation' => $commitmentsData['ipItems'] ?? [],
                    'Students & training' => $commitmentsData['studentItems'] ?? [],
                ] as $groupName => $items)
                    <div class="pj-sub">{{ $groupName }}</div>
                    @if(count($items))
                    <table class="keep">
                        <thead><tr><th style="width:44%;">Item</th><th style="width:18%; text-align:right;">Committed</th><th style="width:18%; text-align:right;">Verified</th><th style="width:20%;">Status</th></tr></thead>
                        <tbody>
                            @foreach($items as $it)
                            @php
                                $st = $commitmentStatus($it);
                                $stColor = $st === 'On Track' ? 'var(--ok)' : ($st === 'In Progress' ? 'var(--amber)' : 'var(--gray)');
                            @endphp
                            <tr>
                                <td class="strong">{{ $it['label'] ?? $it['name'] ?? '—' }}</td>
                                <td class="n">{{ $it['commit'] ?? $it['committed'] ?? $it['expected'] ?? '—' }}</td>
                                <td style="text-align:center;">{{ $verifiedLabel($it['verified'] ?? $it['actual'] ?? $it['count'] ?? null) }}</td>
                                <td style="font-weight:600;color:{{ $stColor }};">{{ $st }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p class="empty">No {{ strtolower($groupName) }} commitments recorded.</p>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ═══ PROJECT FILES — full width, immediately after commitments ═══ --}}
        <div class="pj-h">Project files<span class="pj-n">{{ count($allFiles) }}</span></div>
        @if(count($allFiles))
        <table class="keep">
            <thead><tr><th style="width:36%;">Type</th><th style="width:44%;">File name</th><th style="width:20%;">Uploaded</th></tr></thead>
            <tbody>
                @foreach($allFiles as $f)
                <tr><td class="strong">{{ $f['type'] }}</td><td>{{ $f['name'] }}</td><td class="n">{{ $f['date'] ?? '—' }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty">No files uploaded.</p>
        @endif

        @if(!$isViewer)
        {{-- ═══ OUTCOMES — publications full-width; other outputs in columns ═══ --}}
        <div class="pj-h">Outcomes — scholarly articles<span class="pj-n">{{ $scholarlyOutcomes->count() }} recorded</span></div>
        @if($scholarlyOutcomes->count())
        <table class="keep">
            <thead><tr><th style="width:30%;">Type</th><th style="width:52%;">Identifier / title</th><th style="width:18%;">Verified</th></tr></thead>
            <tbody>
                @foreach($scholarlyOutcomes as $o)
                <tr>
                    <td class="strong">{{ $scholarlyTypes[$o->type] ?? ucfirst(str_replace('_', ' ', $o->type)) }}</td>
                    <td>{{ $o->publication->publication_title ?? $o->identifier ?? '—' }}</td>
                    @php $v = ($o->verifcation_by_reviewer ?? '') === 'verified' || ($o->verifcation_by_system ?? '') === 'verified'; @endphp
                    <td style="{{ $v ? 'color:var(--ok);' : 'color:var(--gray);' }}font-weight:600;">{{ $v ? 'Verified' : 'Not verified' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty">No scholarly articles recorded.</p>
        @endif

        <div class="row2">
            <div>
                @if($students->count())
                <div class="pj-sub">Students &amp; trainings</div>
                <table class="keep">
                    <thead><tr><th style="width:14%;">Level</th><th style="width:24%;">Student ID</th><th style="width:38%;">Name</th><th style="width:24%;">Verified</th></tr></thead>
                    <tbody>
                        @foreach($students as $s)
                        <tr>
                            <td class="strong">{{ strtoupper($s->type ?? '') }}</td>
                            <td class="n" style="text-align:left;">{{ $s->std_id ?? '—' }}</td>
                            <td>{{ $s->student_name ?: (trim(($s->details->first_name ?? '') . ' ' . ($s->details->last_name ?? '')) ?: '—') }}</td>
                            @php $sv = ($s->verifcation_by_reviewer ?? '') === 'verified'; @endphp
                            <td style="{{ $sv ? 'color:var(--ok);' : 'color:var(--gray);' }}font-weight:600;">{{ $sv ? 'Verified' : 'Not verified' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif

                @if($researchers->count())
                <div class="pj-sub">Hired researchers</div>
                <table class="keep">
                    <thead><tr><th style="width:60%;">Name</th><th style="width:20%;">Category</th><th style="width:20%; text-align:right;">Score</th></tr></thead>
                    <tbody>
                        @foreach($researchers as $r)
                        <tr><td class="strong">{{ $r->name ?? '—' }}</td><td>{{ $r->category ?? '—' }}</td><td class="n">{{ $r->score ?? '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div>
                @if($ipOutcomes->count())
                <div class="pj-sub">Intellectual property &amp; innovation</div>
                <table class="keep">
                    <thead><tr><th style="width:44%;">Type</th><th style="width:34%;">Detail</th><th style="width:22%;">Verified</th></tr></thead>
                    <tbody>
                        @foreach($ipOutcomes as $o)
                        @php $v = ($o->verifcation_by_reviewer ?? '') === 'verified' || ($o->verifcation_by_system ?? '') === 'verified'; @endphp
                        <tr>
                            <td class="strong">{{ $ipTypes[$o->type] ?? ucfirst(str_replace('_', ' ', $o->type)) }}</td>
                            <td>{{ $o->identifier ?: '—' }}</td>
                            <td style="{{ $v ? 'color:var(--ok);' : 'color:var(--gray);' }}font-weight:600;">{{ $v ? 'Verified' : 'Not verified' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
        @endif

        @if(!$isViewer)
        {{-- ═══ GRADING ═══ --}}
        <div class="pj-h">Grading<span class="pj-n">{{ $gradingCount ? $gradingCount.' set(s)' : 'not yet graded' }}</span></div>

        @foreach([
            ['title' => 'Progress Report 1', 'rows' => $progressGradings],
            ['title' => 'Progress Report 2', 'rows' => $progress2Gradings],
        ] as $block)
            @foreach($block['rows'] as $g)
            <div class="pj-sub">
                {{ $block['title'] }}
                <span class="badge {{ $g->isAccepted == 1 ? 'b-ok' : 'b-no' }}">{{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}</span>
                @if($g->created_at)<span style="color:var(--gray);">{{ $g->created_at->format('d M Y') }}</span>@endif
            </div>
            <table class="keep">
                <thead><tr><th style="width:4%;">#</th><th style="width:22%;">Criteria</th><th style="width:9%; text-align:center;">Rating</th><th style="width:65%;">Comment</th></tr></thead>
                <tbody>
                    @foreach($progressRows($g) as $i => $row)
                    <tr>
                        <td class="n">{{ $i + 1 }}</td>
                        <td class="strong">{{ $row['label'] }}</td>
                        <td class="n" style="text-align:center;color:var(--accent);font-weight:700;">{{ $row['rating'] ?? '—' }}</td>
                        <td style="overflow-wrap:anywhere;white-space:normal;">{{ $row['comments'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        @endforeach

        @if($finalGradings->count())
        <div class="pj-h">Final report grading</div>
        @foreach($finalGradings as $g)
        <div class="pj-sub">
            Review {{ $loop->iteration }}
            <span class="badge {{ $g->isAccepted == 1 ? 'b-ok' : 'b-no' }}">{{ $g->isAccepted == 1 ? 'Accepted' : 'Rejected' }}</span>
            @if($g->created_at)<span style="color:var(--gray);">{{ $g->created_at->format('d M Y') }}</span>@endif
        </div>
        <table class="keep">
            <thead><tr><th style="width:4%;">#</th><th style="width:25%;">Criteria</th><th style="width:9%; text-align:right;">Score</th><th style="width:62%;">Comment</th></tr></thead>
            <tbody>
                @php $finalSections = [
                    ['Achievements against objectives', $g->gradeA, $g->commentA],
                    ['Publications & IP', $g->gradeB, $g->commentB],
                    ['Student & researcher involvement', $g->gradeC, $g->commentC],
                    ['Project impact', $g->gradeD, $g->commentD],
                ]; @endphp
                @foreach($finalSections as $i => $s)
                <tr>
                    <td class="n">{{ $i + 1 }}</td>
                    <td class="strong">{{ $s[0] }}</td>
                    <td class="n" style="color:var(--accent);font-weight:700;">{{ $s[1] ?? '—' }}/5</td>
                    <td style="overflow-wrap:anywhere;white-space:normal;">{{ $s[2] ?? '—' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" class="strong" style="background:var(--accent-soft);">Total score</td>
                    <td class="n" style="background:var(--accent-soft);color:var(--accent);font-weight:700;">{{ $g->total ?? '—' }}</td>
                    <td style="background:var(--accent-soft);"></td>
                </tr>
            </tbody>
        </table>
        @endforeach
        @endif

        @if(!$gradingCount)
        <p class="empty">No grading recorded yet.</p>
        @endif
        @endif

        {{-- ═══ FOOTER ═══ --}}
        <div class="foot">
            <span>Research Tracking System &middot; Project Details</span>
            <span>{{ $project->old_project_id ?? $project->id }} &middot; {{ now()->format('d M Y') }} &middot; Confidential</span>
        </div>

    </div>
</body>
</html>
