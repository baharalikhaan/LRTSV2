<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Report Booklet — {{ $program->program_title }}</title>
<style>
    :root {
        --ink:#27232a; --ink2:#3d3842; --muted:#68636d; --line:#e2e0e6;
        --soft:#f7ecef; --zebra:#faf9fa;
        --brand:#8d1b3d; --brand-d:#63102b;
        --green:#28724e; --gold:#8a6d1f; --no:#a93434;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    html,body { margin:0; padding:0; }
    body {
        font-family:Arial,Helvetica,"Segoe UI",sans-serif;
        color:var(--ink); background:#ececea; font-size:9px; line-height:1.4;
        -webkit-print-color-adjust:exact; print-color-adjust:exact;
    }

    /* ---------- Print toolbar (screen only) ---------- */
    .toolbar{position:fixed;top:12px;right:12px;z-index:50;display:flex;align-items:center;gap:7px;}
    .toolbar .tb-left{display:flex;align-items:center;gap:7px;min-width:0;}
    .toolbar .tb-title,.toolbar .tb-sub{display:none;}
    .toolbar a.back,.btn-print{display:inline-flex;align-items:center;gap:5px;border:1px solid var(--line);
        border-radius:4px;padding:6px 13px;background:#fff;color:var(--muted);font-size:11px;
        font-weight:500;text-decoration:none;cursor:pointer;white-space:nowrap;box-shadow:0 1px 5px rgba(0,0,0,.08);}
    .toolbar a.back:hover{color:var(--brand);}
    .btn-print{background:var(--brand);color:#fff;border-color:var(--brand);}
    .btn-print:hover{background:var(--brand-d);}

    /* ---------- A4 sheet ---------- */
    .booklet{padding:18px 10px;}
    .sheet{width:210mm;max-width:100%;margin:0 auto 16px;background:#fff;padding:9mm;
        box-shadow:0 2px 14px rgba(34,22,29,.12);}
    .pad{padding:0;}

    /* ---------- Print rules (single clean break between sheets) ---------- */
    @page{size:A4 portrait;margin:10mm 9mm;}
    @media print{
        body{background:#fff;padding:0;font-size:9px;}
        .toolbar{display:none!important;}
        .booklet{padding:0;}
        .sheet{box-shadow:none;margin:0;width:100%;max-width:none;padding:0;break-inside:auto;page-break-inside:auto;}
        .avoid{break-inside:avoid;page-break-inside:avoid;}
    }

    /* ---------- Compact report header, matching the project print page ---------- */
    .rc-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-bottom:6px;}
    .rc-head img{width:190px;max-width:40%;height:auto;display:block;}
    .rc-head-r{text-align:right;line-height:1.3;min-width:0;}
    .rc-eyebrow{font-size:7.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);font-weight:600;}
    .rc-title{font-size:12px;font-weight:700;color:var(--brand);text-transform:uppercase;letter-spacing:.05em;}
    .rc-ref{font-size:8.5px;color:var(--muted);overflow-wrap:anywhere;}
    .rc-rule{height:1px;background:var(--brand);margin-bottom:8px;}

    /* ---------- Typography ---------- */
    h2.sec{font-size:10px;margin:14px 0 6px;color:var(--brand);font-weight:700;text-transform:uppercase;
        letter-spacing:.06em;padding-bottom:3px;border-bottom:1px solid var(--brand);}
    .sec-rule{display:none;}
    h3{font-size:8.5px;margin:7px 0 3px;color:var(--muted);font-weight:700;}
    .muted{color:var(--muted);}
    .empty{color:var(--muted);font-style:italic;padding:4px 6px;background:#f5f4f7;}
    .sec{margin-bottom:12px;}

    /* ---------- Two-column body ---------- */
    .body-cols{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(0,1fr);gap:16px;align-items:start;}
    .col-left,.col-right{min-width:0;}

    /* ---------- Summary sidebar ---------- */
    .summary-box{border:0;border-radius:0;padding:0;background:#fff;}
    .summary-box .s-head{font-size:9px;font-weight:700;color:var(--brand);margin:0 0 4px;
        padding-bottom:3px;border-bottom:1px solid var(--brand);text-transform:uppercase;letter-spacing:.05em;}
    .s-row{display:flex;justify-content:space-between;gap:8px;padding:2px 0;border-bottom:1px dotted var(--line);font-size:8.5px;}
    .s-row:last-child{border-bottom:0;}
    .s-row .s-lab{color:var(--muted);flex:0 0 auto;}
    .s-row .s-val{font-weight:700;text-align:right;color:var(--ink);}
    .s-sep{margin:5px 0 3px;border-top:1px solid var(--line);}

    /* ---------- Notes / paragraphs ---------- */
    .para{color:var(--ink2);font-size:9px;margin:0 0 5px;text-align:left;}
    .note{background:#faf9fa;border-left:2px solid var(--brand);padding:4px 7px;margin:0 0 4px;font-size:8.5px;color:var(--ink2);}
    .note b,.note strong{color:var(--ink);}

    /* ---------- Tables ---------- */
    table{width:100%;border-collapse:collapse;font-size:8.5px;margin-bottom:4px;}
    th,td{border:1px solid var(--line);padding:3px 5px;text-align:left;vertical-align:top;line-height:1.35;}
    th{background:var(--soft);font-weight:700;color:var(--brand);font-size:7.5px;text-transform:uppercase;letter-spacing:.04em;}
    td.num,th.num{text-align:right;font-variant-numeric:tabular-nums;}
    .tight th,.tight td{padding:2px 4px;}
    thead{display:table-header-group;}
    tr{break-inside:avoid;page-break-inside:avoid;}

    /* ---------- Bars ---------- */
    .bar{background:var(--soft);border-radius:2px;height:6px;overflow:hidden;}
    .bar>span{display:block;height:6px;background:var(--brand);border-radius:2px;}
    .bar.alt>span{background:var(--green);}
    .legend{font-size:7.5px;color:var(--muted);margin:0 0 4px;}
    .legend i{display:inline-block;width:7px;height:7px;border-radius:1px;margin-right:3px;vertical-align:-1px;}

    /* ---------- Line chart ---------- */
    .chart-wrap{border:1px solid var(--line);border-radius:2px;padding:3px 4px 1px;background:#fff;}
    .chart-wrap svg{display:block;width:100%;height:auto;}

    /* ---------- Misc ---------- */
    .cols{display:flex;gap:12px;align-items:flex-start;}
    .c50{flex:1 1 50%;min-width:0;}
    .people-cols{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;align-items:start;}
    .people-col{min-width:0;}
    .stat-line{display:flex;justify-content:space-between;gap:8px;border-bottom:1px dotted var(--line);padding:3px 0;font-size:8.5px;}
    .stat-line b{font-variant-numeric:tabular-nums;}
    .sheet-foot{margin-top:8px;padding-top:4px;border-top:1px solid var(--line);font-size:7.5px;color:var(--muted);
        display:flex;justify-content:space-between;}
    @media screen and (max-width:720px){.people-cols{grid-template-columns:1fr;}}
</style>
</head>
<body>

<div class="toolbar no-print">
    <div class="tb-left">
        <a class="back" href="{{ route('programs.index') }}">&larr; Research Calls</a>
        <div style="min-width:0;">
            <div class="tb-title">{{ $program->program_title }}</div>
            <div class="tb-sub">Report Booklet &middot; A4 printable &middot; {{ $kpis[0]['value'] }} project(s)</div>
        </div>
    </div>
    <button class="btn-print" type="button" onclick="window.print();">&#128424; Print / Save as PDF</button>
</div>

<div class="booklet">

<!-- ================= SHEET 1 : OVERVIEW, CHART, SUMMARY ================= -->
<section class="sheet">
    <div class="rc-head">
        <img src="{{ asset('images/research_logo.png') }}" alt="Qatar University — Office of VP for Research and Graduate Studies">
        <div class="rc-head-r">
            <div class="rc-eyebrow">Research Tracking System</div>
            <div class="rc-title">Research Call Report Booklet</div>
            <div class="rc-ref">{{ $program->program_title }} &middot; {{ now()->format('d M Y') }}</div>
        </div>
    </div>
    <div class="rc-rule"></div>

    <div class="pad">
        <div class="body-cols">
            <div class="col-left">
                <div class="sec">
                    <h2 class="sec">Description</h2><div class="sec-rule"></div>
                    <p class="para">
                        @if($program->description)
                            {{ $program->description }}
                        @else
                            This booklet summarises the research call <strong>{{ $program->program_title }}</strong>
                            under cycle {{ $program->cycle->year ?? '—' }} and grant
                            {{ $program->grant->grant_code ?? '—' }}. It covers the workflow pipeline,
                            grading outcomes, budget, research output and the people involved, generated
                            automatically from the Research Tracking System.
                        @endif
                    </p>
                </div>

                <div class="sec">
                    <h2 class="sec">Cumulative Chart</h2><div class="sec-rule"></div>
                    <div class="legend">
                        <i style="background:var(--brand);"></i>Cumulative registered
                        &nbsp;&nbsp;<i style="background:var(--green);"></i>Cumulative graded
                    </div>
                    @php
                        $months = $trend['months'];
                        $cumReg = []; $cumGrd = []; $cr = 0; $cg = 0;
                        foreach ($months as $i => $m) { $cr += $trend['reg'][$i]; $cg += $trend['graded'][$i]; $cumReg[] = $cr; $cumGrd[] = $cg; }
                        $cMax = max(1, (int) max($cumReg ?: [1], $cumGrd ?: [1]));
                        $W = 420; $H = 140; $PL = 32; $PR = 8; $PT = 6; $PB = 20;
                        $cw = $W - $PL - $PR; $ch = $H - $PT - $PB;
                        $n = max(1, count($months));
                        $xAt = function ($i) use ($n, $PL, $cw) { return $n === 1 ? $PL + $cw / 2 : $PL + ($i / ($n - 1)) * $cw; };
                        $yAt = function ($v) use ($cMax, $PT, $ch) { return $PT + $ch - ($v / $cMax) * $ch; };
                        $pts = function ($series) use ($xAt, $yAt) {
                            $p = []; foreach ($series as $i => $v) { $p[] = round($xAt($i), 1) . ',' . round($yAt($v), 1); } return implode(' ', $p);
                        };
                    @endphp
                    <div class="chart-wrap avoid">
                        <svg viewBox="0 0 {{ $W }} {{ $H }}" xmlns="http://www.w3.org/2000/svg">
                            @for($g = 0; $g <= 4; $g++)
                                @php $gy = $PT + ($ch / 4) * $g; $gv = round($cMax - ($cMax / 4) * $g); @endphp
                                <line x1="{{ $PL }}" y1="{{ $gy }}" x2="{{ $W - $PR }}" y2="{{ $gy }}" stroke="#dedbe0" stroke-width="1"/>
                                <text x="{{ $PL - 5 }}" y="{{ $gy + 3 }}" font-size="8" fill="#68636d" text-anchor="end">{{ $gv }}</text>
                            @endfor
                            @if(count($months) > 0)
                                <polyline points="{{ $pts($cumReg) }}" fill="none" stroke="#8d1b3d" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                                <polyline points="{{ $pts($cumGrd) }}" fill="none" stroke="#28724e" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                                @foreach($cumReg as $i => $v)
                                    <circle cx="{{ round($xAt($i),1) }}" cy="{{ round($yAt($v),1) }}" r="2.1" fill="#8d1b3d"/>
                                @endforeach
                                @foreach($cumGrd as $i => $v)
                                    <circle cx="{{ round($xAt($i),1) }}" cy="{{ round($yAt($v),1) }}" r="2.1" fill="#28724e"/>
                                @endforeach
                                @foreach($months as $i => $m)
                                    <text x="{{ round($xAt($i),1) }}" y="{{ $H - 5 }}" font-size="7.5" fill="#68636d" text-anchor="middle">{{ $m }}</text>
                                @endforeach
                            @else
                                <text x="{{ $W/2 }}" y="{{ $H/2 }}" font-size="10" fill="#68636d" text-anchor="middle">No activity recorded</text>
                            @endif
                        </svg>
                    </div>
                </div>
            </div>

            <div class="col-right">
                <div class="summary-box">
                    <div class="s-head">Summary</div>
                    <div class="s-row"><span class="s-lab">Cycle</span><span class="s-val">{{ $program->cycle->title ?? '—' }} ({{ $program->cycle->year ?? '—' }})</span></div>
                    <div class="s-row"><span class="s-lab">Grant</span><span class="s-val">{{ $program->grant->grant_code ?? '—' }}</span></div>
                    <div class="s-row"><span class="s-lab">Grant name</span><span class="s-val">{{ $program->grant->grant_name ?? '—' }}</span></div>
                    <div class="s-row"><span class="s-lab">Category</span><span class="s-val">{{ ucfirst($program->grant->category ?? '—') }}</span></div>
                    <div class="s-sep"></div>
                    <div class="s-row"><span class="s-lab">Progress deadline</span><span class="s-val">{{ optional($program->prog_rpt_deadline)->format('d M Y') ?? '—' }}</span></div>
                    <div class="s-row"><span class="s-lab">Progress 2 deadline</span><span class="s-val">{{ optional($program->prog_rpt2_deadline)->format('d M Y') ?? '—' }}</span></div>
                    <div class="s-row"><span class="s-lab">Final deadline</span><span class="s-val">{{ optional($program->final_rpt_deadline)->format('d M Y') ?? '—' }}</span></div>
                    <div class="s-row"><span class="s-lab">Generated</span><span class="s-val">{{ $generatedAt }}</span></div>
                </div>

                <div class="summary-box" style="margin-top:9px;">
                    <div class="s-head">Key Figures</div>
                    @foreach($kpis as $kpi)
                    <div class="s-row"><span class="s-lab">{{ $kpi['label'] }}</span><span class="s-val">{{ $kpi['value'] }}</span></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="sec" style="margin-top:16px;">
            <h2 class="sec">Overview</h2><div class="sec-rule"></div>
            @foreach($analysis as $note)
            <div class="note">{!! $note !!}</div>
            @endforeach
        </div>

    </div>
</section>

<!-- ================= SHEET 2 : WORKFLOW, GRADING, BUDGET, OUTPUT ================= -->
<section class="sheet">
    <div class="pad">
        <div class="sec">
            <h2 class="sec">Workflow Distribution</h2><div class="sec-rule"></div>
            <table>
                <tr><th style="width:34%;">Status</th><th class="num" style="width:11%;">Projects</th>
                    <th class="num" style="width:11%;">Share</th><th style="width:44%;">Distribution</th></tr>
                @forelse($statusDistribution as $row)
                <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="num">{{ $row['count'] }}</td>
                    <td class="num">{{ $row['pct'] }}%</td>
                    <td><div class="bar"><span style="width:{{ max(2, $row['pct']) }}%;"></span></div></td>
                </tr>
                @empty
                <tr><td colspan="4" class="empty">No projects in this research call.</td></tr>
                @endforelse
            </table>
        </div>

        @php $pRate = $grading['progress_total'] > 0 ? round($grading['progress_accepted'] / $grading['progress_total'] * 100, 1) : 0; @endphp
        @php $fRate = $grading['final_total'] > 0 ? round($grading['final_accepted'] / $grading['final_total'] * 100, 1) : 0; @endphp
        <div class="sec">
            <h2 class="sec">Grading Outcomes</h2><div class="sec-rule"></div>
            <table>
                <tr><th style="width:32%;">Stage</th><th class="num">Completed sets</th>
                    <th class="num">Accepted</th><th class="num">Rejected</th><th class="num">Acceptance rate</th></tr>
                <tr><td>Progress report</td><td class="num">{{ $grading['progress_total'] }}</td>
                    <td class="num">{{ $grading['progress_accepted'] }}</td>
                    <td class="num">{{ $grading['progress_rejected'] }}</td>
                    <td class="num"><strong>{{ $pRate }}%</strong></td></tr>
                <tr><td>Final report</td><td class="num">{{ $grading['final_total'] }}</td>
                    <td class="num">{{ $grading['final_accepted'] }}</td>
                    <td class="num">{{ $grading['final_rejected'] }}</td>
                    <td class="num"><strong>{{ $fRate }}%</strong></td></tr>
            </table>

            <h3>Average final-report scores <span class="muted" style="font-weight:400;">(out of 5)</span></h3>
            <table class="tight avoid" style="width:72%;">
                <tr><th>Section A</th><th>Section B</th><th>Section C</th><th>Section D</th><th>Total</th></tr>
                <tr>
                    <td class="num">{{ $grading['avg_a'] ?: '—' }}</td>
                    <td class="num">{{ $grading['avg_b'] ?: '—' }}</td>
                    <td class="num">{{ $grading['avg_c'] ?: '—' }}</td>
                    <td class="num">{{ $grading['avg_d'] ?: '—' }}</td>
                    <td class="num"><strong style="color:var(--brand);">{{ $grading['avg_total'] ?: '—' }}</strong></td>
                </tr>
            </table>

            <div class="cols">
                <div class="c50">
                    <h3>Section A grade distribution</h3>
                    @if($gradeDistribution->isNotEmpty())
                    @php $gMax = max(1, (int) $gradeDistribution->max('count')); @endphp
                    <table class="tight">
                        <tr><th style="width:30%;">Grade</th><th class="num" style="width:16%;">Count</th><th>Share</th></tr>
                        @foreach($gradeDistribution as $g)
                        <tr><td>{{ $g['grade'] }}</td><td class="num">{{ $g['count'] }}</td>
                            <td><div class="bar"><span style="width:{{ max(2, round($g['count'] / $gMax * 100)) }}%;"></span></div></td></tr>
                        @endforeach
                    </table>
                    @else
                    <div class="empty">No final gradings recorded yet.</div>
                    @endif
                </div>
                <div class="c50">
                    <h3>Rejection reasons recorded</h3>
                    @if($rejectionTypes->isNotEmpty())
                    <table class="tight">
                        <tr><th style="width:62%;">Type</th><th class="num">Count</th></tr>
                        @foreach($rejectionTypes as $r)
                        <tr><td>{{ $r['type'] }}</td><td class="num">{{ $r['count'] }}</td></tr>
                        @endforeach
                    </table>
                    @else
                    <div class="empty">No rejections recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="sec">
            <h2 class="sec">Budget</h2><div class="sec-rule"></div>
            <div class="stat-line"><span>Total requested budget</span><b>{{ number_format($budgetTotal) }} QAR</b></div>
            <div class="stat-line"><span>Projects with a declared budget</span><b>{{ $budgetProjects }}</b></div>
            <div class="stat-line"><span>Average per project (all {{ $kpis[0]['value'] }})</span><b>{{ number_format($budgetTotal / max(1, $kpis[0]['value'])) }} QAR</b></div>
            <div class="stat-line"><span>Average per funded project</span><b>{{ $budgetProjects > 0 ? number_format($budgetTotal / $budgetProjects) : '—' }} QAR</b></div>

            @if($topBudget->isNotEmpty())
            <h3>Top requested budgets</h3>
            <table>
                <tr><th style="width:19%;">Project</th><th style="width:41%;">Title</th>
                    <th style="width:23%;">LPI</th><th class="num">Requested (QAR)</th></tr>
                @foreach($topBudget as $b)
                <tr><td>{{ $b['id'] }}</td><td>{{ $b['title'] }}</td><td>{{ $b['lpi'] }}</td>
                    <td class="num">{{ number_format($b['budget']) }}</td></tr>
                @endforeach
            </table>
            @endif
        </div>

        <div class="sec">
            <h2 class="sec">Research Output &amp; Impact</h2><div class="sec-rule"></div>
            <div class="cols">
                <div class="c50">
                    <div class="stat-line"><span>Publications (Q1–Q4, conference, books)</span><b>{{ $output['publications'] }}</b></div>
                    <div class="stat-line"><span>IP &amp; innovation outputs</span><b>{{ $output['ip'] }}</b></div>
                    <div class="stat-line"><span>All recorded outcomes</span><b>{{ $output['total'] }}</b></div>
                    <div class="stat-line"><span>Verified outcomes</span><b style="color:var(--green);">{{ $output['verified'] }}</b></div>
                    <div class="stat-line"><span>Awaiting verification</span><b style="color:var(--gold);">{{ $output['pending'] }}</b></div>
                </div>
            </div>

            @if($output['by_type']->isNotEmpty())
            <h3>Outcomes by type</h3>
            @php $oMax = max(1, (int) $output['by_type']->first()['count']); @endphp
            <table>
                <tr><th style="width:34%;">Type</th><th class="num" style="width:11%;">Count</th><th>Share of outcomes</th></tr>
                @foreach($output['by_type'] as $o)
                <tr><td>{{ $o['label'] }}</td><td class="num">{{ $o['count'] }}</td>
                    <td><div class="bar"><span style="width:{{ max(2, round($o['count'] / $oMax * 100)) }}%;"></span></div></td></tr>
                @endforeach
            </table>
            @endif
        </div>

    </div>
</section>

<!-- ================= SHEET 3 : PEOPLE, PIPELINE, APPENDIX ================= -->
<section class="sheet">
    <div class="pad">
        <div class="sec">
            <h2 class="sec">People</h2><div class="sec-rule"></div>
            <div class="people-cols">
                <div class="people-col">
                    <h3>Students by level</h3>
                    @php $sMax = max(1, (int) ($studentsByType->first()['count'] ?? 1)); @endphp
                    <table class="tight">
                        <tr><th style="width:44%;">Level</th><th class="num" style="width:16%;">Count</th><th>Share</th></tr>
                        @forelse($studentsByType as $s)
                        <tr><td>{{ $s['label'] }}</td><td class="num">{{ $s['count'] }}</td>
                            <td><div class="bar alt"><span style="width:{{ max(2, round($s['count'] / $sMax * 100)) }}%;"></span></div></td></tr>
                        @empty
                        <tr><td colspan="3" class="empty">No students recorded.</td></tr>
                        @endforelse
                    </table>

                    @if($lpiRows->isNotEmpty())
                    <h3>Lead Principal Investigators <span class="muted" style="font-weight:400;">(by projects)</span></h3>
                    <table class="tight">
                        <tr><th style="width:76%;">LPI</th><th class="num">Projects</th></tr>
                        @foreach($lpiRows as $l)
                        <tr><td>{{ $l['name'] }}</td><td class="num">{{ $l['count'] }}</td></tr>
                        @endforeach
                    </table>
                    @endif

                    @if($collegeDistribution->isNotEmpty())
                    <h3>By college</h3>
                    <table class="tight">
                        <tr><th style="width:72%;">College</th><th class="num">Projects</th></tr>
                        @foreach($collegeDistribution as $c)
                        <tr><td>{{ $c['label'] }}</td><td class="num">{{ $c['count'] }}</td></tr>
                        @endforeach
                    </table>
                    @endif
                </div>

                <div class="people-col">
                    <h3>Students by nationality</h3>
                    <table class="tight">
                        <tr><th style="width:60%;">Nationality</th><th class="num">Count</th></tr>
                        @forelse($studentsByNationality as $n)
                        <tr><td>{{ $n['label'] }}</td><td class="num">{{ $n['count'] }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="empty">Not recorded.</td></tr>
                        @endforelse
                    </table>
                    <div class="stat-line" style="margin-top:5px;"><span>Researchers engaged</span><b>{{ $researchersCount }}</b></div>

                    @if($reviewerRows->isNotEmpty())
                    <h3>Reviewer workload <span class="muted" style="font-weight:400;">(assigned projects)</span></h3>
                    <table class="tight">
                        <tr><th style="width:76%;">Reviewer</th><th class="num">Projects</th></tr>
                        @foreach($reviewerRows as $r)
                        <tr><td>{{ $r['name'] }}</td><td class="num">{{ $r['count'] }}</td></tr>
                        @endforeach
                    </table>
                    @endif

                    @if($pillarDistribution->isNotEmpty())
                    <h3>By research pillar</h3>
                    <table class="tight">
                        <tr><th style="width:72%;">Pillar</th><th class="num">Projects</th></tr>
                        @foreach($pillarDistribution as $p)
                        <tr><td>{{ $p['label'] }}</td><td class="num">{{ $p['count'] }}</td></tr>
                        @endforeach
                    </table>
                    @endif
                </div>
            </div>
        </div>

        <div class="sec">
            <h2 class="sec">Project Appendix</h2><div class="sec-rule"></div>
            <table class="tight">
                <tr><th style="width:18%;">Project ID</th><th style="width:36%;">Title</th>
                    <th style="width:20%;">LPI</th><th style="width:14%;">Status</th><th class="num">Budget (QAR)</th></tr>
                @forelse($appendix as $row)
                <tr><td>{{ $row['id'] }}</td><td>{{ $row['title'] }}</td>
                    <td>{{ $row['lpi'] }}</td><td>{{ $row['status'] }}</td>
                    <td class="num">{{ $row['budget'] > 0 ? number_format($row['budget']) : '—' }}</td></tr>
                @empty
                <tr><td colspan="5" class="empty">No projects.</td></tr>
                @endforelse
            </table>
        </div>

        <div class="sheet-foot">
            <span>Research Tracking System &middot; Research Call Report Booklet</span>
            <span>{{ $program->program_title }} &middot; {{ $generatedAt }}</span>
        </div>
    </div>
</section>

</div>

</body>
</html>
