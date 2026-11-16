<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Call — {{ $program->program_title }}</title>
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

        /* ─── Section headings ─── */
        .pj-h {
            font-size: 10px; font-weight: 700; color: var(--accent);
            letter-spacing: .06em; text-transform: uppercase;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 3px;
            margin: 14px 0 6px;
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

        /* ─── Name line ─── */
        .pj-name { font-size: 12px; font-weight: 700; line-height: 1.25; margin: 2px 0 6px; }
        .pj-id { font-size: 8px; color: var(--gray); letter-spacing: .06em; }

        /* ─── Tables ─── */
        table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-bottom: 4px; }
        th, td { border: 1px solid var(--line); padding: 3px 6px; text-align: left; vertical-align: top; line-height: 1.35; }
        th { background: var(--accent-soft); color: var(--accent); font-weight: 700; font-size: 7.5px;
             text-transform: uppercase; letter-spacing: .04em; }
        td.n { text-align: right; color: var(--gray); font-variant-numeric: tabular-nums; }
        td.strong { font-weight: 700; }
        tr:last-child td { background: #fff; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        thead tr { break-inside: avoid; }
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
        <button class="btn" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('programs.show', $program->id) }}'; }">&#8592; Back</button>
        <button class="btn btn-primary" onclick="window.print()">&#128424; Print / Save PDF</button>
    </div>

    @php
        $grant = $program->grant;
        $cycle = $program->cycleConfig ?? $program->cycle;
        $totalProjects = $program->projects->count();
        $isActive = $program->isActive();

        $deadlines = [
            ['label' => 'Progress Report 1', 'deadline' => $program->prog_rpt_deadline],
            ['label' => 'Progress Report 2', 'deadline' => $program->prog_rpt2_deadline],
            ['label' => 'Final Report', 'deadline' => $program->final_rpt_deadline],
        ];

        // Status badge colour, mirroring the on-screen research call page.
        $statusBadge = static function (?string $status) {
            switch ($status) {
                case 'registered':                 return 'b-info';
                case 'Assigned': case 'assigned':   return 'b-amber';
                case 'Claimed': case 'claimed':     return 'b-ok';
                case 'progress_added': case 'progress_reviewed':
                case 'progress2_added': case 'progress2_reviewed':
                case 'final_added':                 return 'b-info';
                case 'progress_rejected': case 'progress2_rejected':
                case 'final_rejected': case 'proposal_rejected':
                case 'rejected':                    return 'b-no';
                case 'Graded': case 'graded':       return 'b-ok';
                case 'reviewer_unassigned':         return 'b-amber';
                default:                            return 'b-info';
            }
        };
        $statusText = static function (?string $status) {
            $map = [
                'registered' => 'Registered', 'Assigned' => 'Assigned', 'Claimed' => 'Claimed',
                'proposal_rejected' => 'Proposal Rejected', 'progress_added' => 'Progress Added',
                'progress_reviewed' => 'Progress Reviewed', 'progress_rejected' => 'Progress Rejected',
                'final_added' => 'Final Added', 'Graded' => 'Graded', 'final_rejected' => 'Final Rejected',
                'reviewer_unassigned' => 'Reviewer Unassigned', 'progress2_added' => 'Progress 2 Added',
                'progress2_reviewed' => 'Progress 2 Reviewed', 'progress2_rejected' => 'Progress 2 Rejected',
            ];
            return $map[$status] ?? ucfirst(str_replace('_', ' ', (string) ($status ?: 'imported')));
        };
    @endphp

    <div class="sheet">

        {{-- ═══ HEADER ═══ --}}
        <div class="pj-head">
            <img src="{{ asset('images/research_logo.png') }}" alt="Qatar University — Office of VP for Research and Graduate Studies">
            <div class="pj-head-r">
                <div class="pj-eyebrow">Research Tracking System</div>
                <div class="pj-title">Research Call</div>
                <div class="pj-ref">{{ $program->program_title }} &middot; {{ now()->format('d M Y') }}</div>
            </div>
        </div>
        <div class="pj-rule"></div>

        {{-- ═══ IDENTITY ═══ --}}
        <div class="pj-name">{{ $program->program_title }}</div>

        <div class="pj-kv">
            <div class="kv"><span class="k">Grant code</span><span class="v">{{ $grant->grant_code ?? '—' }}</span></div>
            <div class="kv"><span class="k">Grant category</span><span class="v">{{ ucfirst($grant->category ?? '—') }}</span></div>
            <div class="kv"><span class="k">Funding agency</span><span class="v">{{ $grant->funding_agency ?? '—' }}</span></div>
            <div class="kv"><span class="k">Cycle</span><span class="v">{{ $cycle->title ?? '—' }}{{ $cycle->year ? ' (' . $cycle->year . ')' : '' }}</span></div>
            <div class="kv"><span class="k">Status</span><span class="v"><span class="badge {{ $isActive ? 'b-ok' : 'b-no' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span></span></div>
            <div class="kv"><span class="k">Visibility</span><span class="v">{{ $program->is_visible ? 'Visible' : 'Hidden' }}</span></div>
            <div class="kv"><span class="k">Projects</span><span class="v">{{ $totalProjects }}</span></div>
            <div class="kv"><span class="k">Projects reported</span><span class="v">{{ $statusTotal }}</span></div>
            <div class="kv"><span class="k">Created</span><span class="v">{{ $program->created_at ? $program->created_at->format('d M Y') : '—' }}</span></div>
        </div>

        {{-- ═══ DEADLINES + STATUS SUMMARY ═══ --}}
        <div class="pj-h">Deadlines</div>
        <table class="keep">
            <thead>
                <tr><th style="width:34%;">Report type</th><th style="width:26%;">Deadline</th><th style="width:40%;">Status</th></tr>
            </thead>
            <tbody>
                @foreach($deadlines as $d)
                    @php $past = $d['deadline'] ? now()->greaterThan($d['deadline']) : null; @endphp
                    <tr>
                        <td class="strong">{{ $d['label'] }}</td>
                        <td>{{ $d['deadline'] ? $d['deadline']->format('d M Y H:i') : '—' }}</td>
                        <td>
                            @if(!$d['deadline'])
                                <span class="badge b-info">Not set</span>
                            @elseif($past)
                                <span class="badge b-no">Passed</span>
                            @else
                                <span class="badge b-ok">{{ now()->diffInDays($d['deadline']) }} days left</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pj-h">Project status summary <span class="pj-n">{{ $statusTotal }} reported</span></div>
        @if($statusTotal > 0)
            <table class="keep">
                <thead>
                    <tr><th style="width:60%;">Status</th><th style="width:20%; text-align:right;">Count</th><th style="width:20%; text-align:right;">%</th></tr>
                </thead>
                <tbody>
                    @foreach($orderedStatuses as $cfg)
                        <tr>
                            <td><span class="badge {{ $statusBadge(str_replace(' ', '_', strtolower($cfg[0]))) }}">{{ $cfg[0] }}</span></td>
                            <td class="n strong">{{ $cfg['count'] }}</td>
                            <td class="n">{{ round(($cfg['count'] / $statusTotal) * 100, 1) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No project has reported a status yet.</div>
        @endif

        {{-- ═══ PROJECTS ═══ --}}
        <div class="pj-h">Projects <span class="pj-n">{{ $totalProjects }} total</span></div>
        @if($totalProjects > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width:4%;">#</th>
                        <th style="width:16%;">Project ID</th>
                        <th style="width:38%;">Title</th>
                        <th style="width:16%;">Lead PI</th>
                        <th style="width:14%;">Status</th>
                        <th style="width:12%;">Files</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($program->projects as $cp)
                        @php
                            $flowStatus = $cp->currentWorkflowStatus();
                            $fileCount = $cp->submissions()->count() + ($cp->proposal_filename ? 1 : 0);
                        @endphp
                        <tr>
                            <td class="n">{{ $loop->iteration }}</td>
                            <td class="strong">{{ $cp->old_project_id ?? $cp->id }}</td>
                            <td>{{ $cp->project_title ?? $cp->title }}</td>
                            <td>{{ $cp->lpi->name ?? '—' }}</td>
                            <td><span class="badge {{ $statusBadge($flowStatus) }}">{{ $statusText($flowStatus) }}</span></td>
                            <td class="n">{{ $fileCount }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">No projects have been imported for this research call yet.</div>
        @endif

        {{-- ═══ FOOTER ═══ --}}
        <div class="foot">
            <span>Research Tracking System &middot; Research Call</span>
            <span>{{ $program->program_title }} &middot; {{ now()->format('d M Y') }} &middot; Confidential</span>
        </div>

    </div>
</body>
</html>
