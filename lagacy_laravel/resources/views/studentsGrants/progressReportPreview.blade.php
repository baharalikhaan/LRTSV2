<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Progress Report Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f0f0f0; font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.3; }
        .report-page {
            width: 210mm;
            min-height: 297mm;
            margin: 25px auto;
            background: #fff;
            padding: 15mm 25mm 15mm 25mm;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            position: relative;
        }
        /* === REPEATING HEADER ON EVERY PAGE (like Word header) === */
        .page-header {
            position: relative;
            min-height: 55px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ddd;
        }
        .page-header .header-logo {
            position: absolute;
            top: -5px;
            right: 0;
            width: 80px;
        }
        .page-header .header-logo img { width: 80px; }
        .page-header .header-text { margin-right: 95px; }
        .page-header .header-text .qu-name { font-size: 12pt; font-weight: bold; letter-spacing: 0.5px; }
        .page-header .header-text .dept-name { font-size: 10pt; margin-top: 2px; }

        /* === FIRST PAGE SPECIAL - remove header border for cleaner look === */
        .first-page .page-header { border-bottom: 1px solid #ddd; }

        /* === FIRST PAGE (COVER) - centered vertically === */
        .cover-page {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: calc(297mm - 15mm - 15mm - 70px);
        }
        /* === MAIN TITLE - centered === */
        .title-area { text-align: center; margin: 30px 0 20px 0; }
        .title-area .form-ref { font-size: 14pt; font-weight: bold; }
        .title-area .report-title { font-size: 22pt; font-weight: bold; letter-spacing: 2px; margin-top: 5px; }
        .title-area .project-title-label { font-size: 12pt; font-weight: bold; margin-top: 15px; }
        .title-area .project-title-value { font-size: 11pt; margin-top: 3px; }
        /* === INFO BLOCK - tabular style === */
        .info-block { margin: 20px auto; max-width: 500px; }
        .info-row { display: flex; margin: 3px 0; }
        .info-row .lbl { min-width: 220px; font-weight: bold; }
        .info-row .val { flex: 1; }
        /* === TABLE OF CONTENTS === */
        .toc-block { margin: 30px 0; }
        .toc-block .toc-title { font-size: 13pt; font-weight: bold; margin-bottom: 10px; }
        .toc-block p { margin: 2px 0; font-size: 10pt; }
        /* === SECTION HEADINGS (RED) === */
        h2.sec-title { font-size: 13pt; font-weight: bold; color: #c00000; margin: 22px 0 10px 0; }
        h3.sec-sub { font-size: 11pt; font-weight: bold; color: #c00000; margin: 14px 0 8px 0; }
        h4.sec-label { font-size: 11pt; font-weight: bold; margin-top: 12px; margin-bottom: 6px; }
        /* === TABLES - keep together, don't break across pages === */
        table.doc-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10pt; page-break-inside: avoid; }
        table.doc-table, table.doc-table th, table.doc-table td { border: 1px solid #000; }
        table.doc-table th { background: #f0f0f0; text-align: center; font-weight: bold; padding: 4px 5px; font-size: 9pt; }
        table.doc-table td { padding: 3px 5px; vertical-align: top; }
        /* Keep section titles with their content */
        h2.sec-title, h3.sec-sub, h4.sec-label { page-break-after: avoid; }
        .toc-block, .sig-block { page-break-inside: avoid; }
        ol.listing, ul.listing { page-break-inside: avoid; }
        /* === CONTENT === */
        p.para { margin: 5px 0; text-align: justify; font-size: 10pt; }
        p.para-small { margin: 4px 0; font-size: 9pt; font-style: italic; }
        ol.listing { margin: 5px 0 10px 22px; padding-left: 10px; }
        ol.listing li { margin: 2px 0; font-size: 10pt; }
        /* === SIGNATURE === */
        .sig-block { margin-top: 30px; }
        .sig-block p { margin: 3px 0; }
        .sig-table { border: none !important; width: auto; }
        .sig-table tr, .sig-table td { border: none !important; padding: 4px 10px 4px 0; }
        .sig-line { border-bottom: 1px solid #000; min-width: 200px; display: inline-block; }
        /* === FOOTER === */
        .doc-footer { margin-top: 35px; font-size: 9pt; border-top: 1px solid #ccc; padding-top: 6px; }
        /* === PRINT BUTTON === */
        .action-bar { text-align: center; margin: 15px 0; }
        .action-bar button, .action-bar a {
            padding: 8px 25px; background: teal; color: white; border: none;
            border-radius: 4px; cursor: pointer; font-size: 11pt; text-decoration: none;
            display: inline-block; margin: 0 5px;
        }
        .action-bar button:hover, .action-bar a:hover { background: #005959; }
        /* === PRINT STYLES - header repeats on every page === */
        @media print {
            body { background: #fff; }
            .report-page {
                box-shadow: none;
                margin: 0;
                padding: 18mm 25mm 15mm 25mm !important;
                page-break-after: always;
                position: relative;
            }
            .action-bar { display: none; }
            /* Print running header */
            .page-header {
                position: running(pageHeader);
                border-bottom: 1px solid #ddd;
                margin-bottom: 10px;
            }
            @page {
                margin: 18mm 25mm 15mm 25mm;
                @top-left {
                    content: element(pageHeader);
                    width: 100%;
                }
            }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <button onclick="window.print()">Print / Save as PDF</button>
    <a href="{{ route('progressReport.download', ['project_id' => $project->id]) }}">Download DOCX</a>
</div>

<!-- ========== PAGE 1 (COVER) ========== -->
<div class="report-page first-page">
    <!-- HEADER (visible on every page - top margin/edge) -->
    <div class="page-header" id="runningHeader">
        <div class="header-logo">
            <img src="{{ asset('QU_logo.png') }}" alt="QU Logo">
        </div>
        <div class="header-text">
            <div class="qu-name">QATAR UNIVERSITY</div>
            <div class="dept-name">Research Support Department (Grants & Contracts) – Progress Report ORS-F16</div>
        </div>
    </div>

    <!-- COVER PAGE CONTENT (centered vertically) -->
    <div class="cover-page">
        <!-- MAIN TITLE AREA -->
        <div class="title-area">
            <div class="form-ref">ORS-F16</div>
            <div class="report-title">PROGRESS REPORT</div>
            <div class="project-title-label">Project Title:</div>
            <div class="project-title-value">{{ $project->title ?? '' }}</div>
        </div>

        <!-- INFO BLOCK -->
        <div class="info-block">
            <div class="info-row">
                <span class="lbl">Grant ID:</span>
                <span class="val">{{ $report->grant_id ?? '--------' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">LPI name:</span>
                <span class="val">{{ $report->lpi_name ?? '----------' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Collaborator Institute (if applicable):</span>
                <span class="val">{{ $report->collaborator_institute ?? '----------' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Collaborator LPI name (if applicable):</span>
                <span class="val">{{ $report->collaborator_lpi_name ?? '----------' }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Report period:</span>
                <span class="val">{{ ($report->report_period_from ?? 'dd/mm/yyyy') . ' until ' . ($report->report_period_to ?? 'dd/mm/yyyy') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- ========== PAGE 2 (TABLE OF CONTENTS) ========== -->
<div class="report-page">
    <div class="page-header">
        <div class="header-logo">
            <img src="{{ asset('QU_logo.png') }}" alt="QU Logo">
        </div>
        <div class="header-text">
            <div class="qu-name">QATAR UNIVERSITY</div>
            <div class="dept-name">Research Support Department (Grants & Contracts) – Progress Report ORS-F16</div>
        </div>
    </div>

    <div class="toc-block">
        <p class="toc-title">Contents</p>
        <p>I. GRANT INFORMATION ..................................................................... 2</p>
        <p>II. SCOPE, PURPOSE AND PROGRESS .......................................................... 3</p>
        <p>III. RESULTS ACHIEVED ...................................................................... 4</p>
        <p>IV. REMAINING RESEARCH QUESTIONS .......................................................... 4</p>
        <p>V. GRANT OUTPUTS AND PROGRESS AGAINST GRANT PROPOSAL COMMITMENTS ........................... 4</p>
        <p>VI. ACTION PLAN FOR THE NEXT SIX MONTHS ................................................... 5</p>
        <p>VII. ETHICAL AND REGULATORY REQUIREMENTS .................................................. 5</p>
        <p>VIII. POTENTIAL DIFFICULTIES (IF ANY) ..................................................... 5</p>
        <p>IX. CONTRIBUTION OF COLLABORATOR (IF APPLICABLE) .......................................... 5</p>
        <p>X. APPENDIX ............................................................................... 5</p>
    </div>
</div>

<!-- ========== PAGE 3+ (SECTIONS) ========== -->
<div class="report-page">
    <div class="page-header">
        <div class="header-logo">
            <img src="{{ asset('QU_logo.png') }}" alt="QU Logo">
        </div>
        <div class="header-text">
            <div class="qu-name">QATAR UNIVERSITY</div>
            <div class="dept-name">Research Support Department (Grants & Contracts) – Progress Report ORS-F16</div>
        </div>
    </div>

    <h2 class="sec-title">I. GRANT INFORMATION</h2>
    <table class="doc-table">
        <tr><th style="width:180px;">Grant ID</th><td>{{ $report->grant_id ?? 'N/A' }}</td></tr>
        <tr><th>Funding duration</th><td>{{ $report->funding_duration ?? 'N/A' }}</td></tr>
        <tr><th>Current year (1st, 2nd or 3rd)</th><td>{{ $report->current_year ?? 'N/A' }}</td></tr>
    </table>

    <h4 class="sec-label">Budget (QAR)</h4>
    <table class="doc-table">
        <thead>
            <tr>
                <th rowspan="2"></th>
                <th colspan="2">Year 1</th>
                <th colspan="2">Year 2</th>
                <th colspan="2">Year 3</th>
            </tr>
            <tr>
                <th>Qatar University</th>
                <th>Collaborator (if applicable)</th>
                <th>Qatar University</th>
                <th>Collaborator (if applicable)</th>
                <th>Qatar University</th>
                <th>Collaborator (if applicable)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Awarded Budget</strong></td>
                <td style="text-align:right;">{{ number_format($report->year1_qu_awarded ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year1_collab_awarded ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year2_qu_awarded ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year2_collab_awarded ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year3_qu_awarded ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year3_collab_awarded ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Actual Expenses</strong></td>
                <td style="text-align:right;">{{ number_format($report->year1_qu_actual ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year1_collab_actual ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year2_qu_actual ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year2_collab_actual ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year3_qu_actual ?? 0, 2) }}</td>
                <td style="text-align:right;">{{ number_format($report->year3_collab_actual ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- SECTION II -->
    <h2 class="sec-title">II. SCOPE, PURPOSE AND PROGRESS</h2>
    <h3 class="sec-sub">Specific Aims</h3>
    @php $aims = $specific_aims ?? []; @endphp
    @if(count($aims) > 0)
    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Aim</th>
                <th style="width:55px;">Not Started</th>
                <th style="width:55px;">In Progress</th>
                <th style="width:55px;">Completed</th>
                <th style="width:65px;">On "date"</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aims as $i => $aim)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ $aim['aim'] ?? '' }}</td>
                <td style="text-align:center;">{{ ($aim['status'] ?? '') == 'not_started' ? 'X' : '' }}</td>
                <td style="text-align:center;">{{ ($aim['status'] ?? '') == 'in_progress' ? 'X' : '' }}</td>
                <td style="text-align:center;">{{ ($aim['status'] ?? '') == 'completed' ? 'X' : '' }}</td>
                <td>{{ $aim['date'] ?? '' }}</td>
                <td>{{ $aim['comments'] ?? '' }}</td>
            </tr>
            @endforeach
            <tr><td colspan="7" style="font-style:italic; border-top:2px solid #000;">*please add more rows if applicable</td></tr>
        </tbody>
    </table>
    @else
    <p class="para"><em>No specific aims data entered.</em></p>
    @endif

    <h2 class="sec-title">III. RESULTS ACHIEVED</h2>
    <p class="para">{{ $report->results_achieved ?? 'N/A' }}</p>

    <h2 class="sec-title">IV. REMAINING RESEARCH QUESTIONS</h2>
    <p class="para">{{ $report->remaining_questions ?? 'N/A' }}</p>

    <!-- SECTION V -->
    <h2 class="sec-title">V. GRANT OUTPUTS AND PROGRESS AGAINST GRANT PROPOSAL COMMITMENTS</h2>
    <p class="para-small">Kindly summarize in the table below the grant outcomes committed to in the proposal and indicate the progress made. Under the table, please add a list for publications and capacity building.</p>
    @php $outcomes = $committed_outcomes ?? []; @endphp
    @if(count($outcomes) > 0)
    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>Committed Outcomes in the Grant Proposal</th>
                <th style="width:80px;">Number of Committed Outcomes</th>
                <th style="width:80px;">Number of Achieved Outcomes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($outcomes as $i => $oc)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ $oc['outcome'] ?? '' }}</td>
                <td style="text-align:center;">{{ $oc['committed'] ?? '' }}</td>
                <td style="text-align:center;">{{ $oc['achieved'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="para"><em>No committed outcomes data entered.</em></p>
    @endif

    <h4 class="sec-label">Publications</h4>
    <p class="para-small">Add publication(s) (if any), including those under preparation (indicate journal impact factor and Clarivate Analytics quartile ranking Q1, Q2..etc). Please add all citation details.</p>
    @php $pubs = $publications_list ?? []; @endphp
    @if(count($pubs) > 0)
    <ol class="listing">
        @foreach($pubs as $pub)
            @if(!empty($pub['text']))
            <li>{{ $pub['text'] }}</li>
            @endif
        @endforeach
    </ol>
    @else
    <p class="para"><em>No publications data entered.</em></p>
    @endif

    <h4 class="sec-label">Capacity Building</h4>
    <p class="para"><strong>RAs recruited:</strong> Add names of RAs recruited (dates of recruitment) and their job numbers.</p>
    @php $ras = $capacity_ras ?? []; @endphp
    @if(count($ras) > 0)
    <ol class="listing">
        @foreach($ras as $ra)
            @if(!empty($ra['name']))
            <li>{{ $ra['name'] }} - {{ $ra['details'] ?? '' }}</li>
            @endif
        @endforeach
    </ol>
    @else
    <p class="para"><em>N/A</em></p>
    @endif

    <p class="para"><strong>Students involved:</strong> Add names of students involved and their QUID numbers.</p>
    @php $students = $capacity_students ?? []; @endphp
    @if(count($students) > 0)
    <ol class="listing">
        @foreach($students as $s)
            @if(!empty($s['name']))
            <li>{{ $s['name'] }} - {{ $s['details'] ?? '' }}</li>
            @endif
        @endforeach
    </ol>
    @else
    <p class="para"><em>N/A</em></p>
    @endif

    <!-- SECTION VI -->
    <h2 class="sec-title">VI. ACTION PLAN FOR THE NEXT SIX MONTHS</h2>
    @php $plans = $action_plan ?? []; @endphp
    @if(count($plans) > 0)
    <table class="doc-table">
        <thead>
            <tr>
                <th>List of Aims</th>
                <th>Action Plan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $i => $ap)
            <tr>
                <td><strong>Aim ({{ $i + 1 }}):</strong> {{ $ap['aim'] ?? '' }}</td>
                <td>{{ $ap['plan'] ?? '' }}</td>
            </tr>
            @endforeach
            <tr><td colspan="2" style="font-style:italic; border-top:2px solid #000;">*please add more rows if applicable</td></tr>
        </tbody>
    </table>
    @else
    <p class="para"><em>No action plan data entered.</em></p>
    @endif

    <!-- SECTIONS VII-X -->
    <h2 class="sec-title">VII. ETHICAL AND REGULATORY REQUIREMENTS</h2>
    <p class="para">{{ $report->ethical_requirements ?? 'N/A' }}</p>
    <p class="para-small">Provide evidence of any ethical or institutional approvals that you have secured for the project. Attach the approval letters in appendix.</p>

    <h2 class="sec-title">VIII. POTENTIAL DIFFICULTIES (IF ANY)</h2>
    <p class="para">{{ $report->potential_difficulties ?? 'N/A' }}</p>

    <h2 class="sec-title">IX. CONTRIBUTION OF COLLABORATOR (IF APPLICABLE)</h2>
    <p class="para">{{ $report->collaborator_contribution ?? 'N/A' }}</p>

    <h2 class="sec-title">X. APPENDIX</h2>
    <p class="para">{{ $report->appendix ?? 'N/A' }}</p>
    <p class="para-small">An appendix is optional. If included, appendices should be used to present material that is supplementary, but not vital, to the understanding or interpretation of the main report.</p>

    <!-- SIGNATURE -->
    <div class="sig-block">
        <p><strong>Recommendation of Associate Dean for Research & Graduate Studies/Center Director for the above report:</strong></p>
        <p style="margin:12px 0 0 30px;">
            <span style="margin-right:60px;">&nbsp;&nbsp;&nbsp;&nbsp;Approve</span>
            <span>Disapprove</span>
        </p>
        <p style="margin-top:14px;"><strong>Comments:</strong></p>
        <p style="margin:30px 0;">&nbsp;</p>
        <table class="sig-table">
            <tr><td><strong>Name:</strong></td><td><span class="sig-line">&nbsp;</span></td></tr>
            <tr><td><strong>Signature:</strong></td><td><span class="sig-line">&nbsp;</span></td></tr>
            <tr><td><strong>Date:</strong></td><td><span class="sig-line">&nbsp;</span></td></tr>
        </table>
    </div>

    <div class="doc-footer">Issue 3 dated 29/09/2024</div>
</div>

<div class="action-bar">
    <button onclick="window.print()">Print / Save as PDF</button>
    <a href="{{ route('progressReport.download', ['project_id' => $project->id]) }}">Download DOCX</a>
</div>
</body>
</html>
