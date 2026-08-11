@php
    // QR code data — encode project URL for verification
    $qrData = url('/projects/' . $project->id);

    // Base64-encode the logo for reliable display (avoids asset() path issues)
    $logoPath = public_path('images/research_logo.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoData = file_get_contents($logoPath);
        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
    }
@endphp

<style>
    /* ─── Modal header override ─── */
    #workflowModal .report-card-header {
        display: flex; align-items: center; gap: 10px;
        padding: 0 0 8px 0;
        border-bottom: 2px solid var(--brand-500, #8d1b3d);
        margin-bottom: 10px;
    }
    #workflowModal .report-card-header .rc-header-logo {
        flex-shrink: 0;
    }
    #workflowModal .report-card-header .rc-header-logo img {
        width: 72px; height: auto;
    }
    #workflowModal .report-card-header .rc-title-area {
        flex: 1; text-align: right;
    }
    #workflowModal .report-card-header .rc-title-area h2 {
        font-size: 14px; font-weight: 700; margin: 0; line-height: 1.2;
        color: var(--ink-900, #16131a); letter-spacing: -.01em;
    }
    #workflowModal .report-card-header .rc-title-area .rc-sub {
        font-size: 9px; text-transform: uppercase; letter-spacing: .06em;
        color: var(--ink-400, #8b8592); font-weight: 600;
    }
    /* ─── Meta info row ─── */
    #workflowModal .rc-meta {
        display: flex; flex-wrap: wrap; gap: 2px 18px;
        margin-bottom: 10px; padding: 8px 12px;
        background: var(--sand-50, #faf7f0);
        border-radius: var(--fluent-radius-md, 6px);
        border: 1px solid var(--ink-100, #eeedf0);
    }
    #workflowModal .rc-meta-item {
        font-size: 10.5px; color: var(--ink-600, #4c4553); line-height: 1.35;
    }
    #workflowModal .rc-meta-item strong {
        color: var(--ink-800, #241f2a); font-weight: 600;
    }
    #workflowModal .rc-meta-item .rc-project-id {
        display: inline-block;
        background: var(--brand-100, #f3d2da); color: var(--brand-700, #63102b);
        font-size: 9px; font-weight: 700; padding: 1px 6px; border-radius: 4px;
        font-family: "Courier New", monospace; letter-spacing: .02em;
    }

    /* ─── Section headers ─── */
    #workflowModal .rc-section {
        font-size: 10px; font-weight: 700; color: var(--brand-600, #7a1636);
        padding: 5px 0 3px; margin-top: 12px; margin-bottom: 4px;
        border-bottom: 1.5px solid var(--brand-500, #8d1b3d);
        display: flex; align-items: center; gap: 6px;
    }
    #workflowModal .rc-section:first-of-type { margin-top: 0; }
    #workflowModal .rc-section .section-count {
        font-size: 8.5px; font-weight: 600; color: var(--ink-400, #8b8592);
        background: var(--ink-100, #eeedf0); padding: 1px 6px; border-radius: 999px;
        text-transform: none; letter-spacing: normal;
    }

    /* ─── Tables ─── */
    #workflowModal .rc-table {
        width: 100%; border-collapse: collapse; margin-bottom: 1px;
        border: 1px solid var(--ink-100, #eeedf0);
        border-radius: var(--fluent-radius-md, 6px);
        overflow: hidden;
    }
    #workflowModal .rc-table th {
        font-size: 8.5px; font-weight: 600; color: var(--ink-500, #675f6e);
        text-transform: uppercase; letter-spacing: .04em;
        padding: 4px 8px; background: var(--sand-50, #faf7f0);
        border-bottom: 1px solid var(--ink-200, #d8d6dc);
        text-align: left; vertical-align: middle;
    }
    #workflowModal .rc-table td {
        font-size: 10px; color: var(--ink-700, #38333e);
        padding: 4px 8px; border-bottom: 1px solid var(--ink-50, #f7f7f8);
        vertical-align: top;
    }
    #workflowModal .rc-table tbody tr:last-child td {
        border-bottom: none;
    }
    #workflowModal .rc-table tbody tr:hover td {
        background: var(--brand-50, #fbeef1);
    }
    #workflowModal .rc-table .reviewer-label {
        font-weight: 700; color: var(--brand-600, #7a1636);
        background: var(--brand-50, #fbeef1);
        vertical-align: middle; font-size: 9px;
        white-space: nowrap; width: 80px;
    }

    /* ─── Rating badges ─── */
    #workflowModal .rating-badge {
        display: inline-block; font-size: 9px; font-weight: 600;
        padding: 1px 6px; border-radius: 999px;
        white-space: nowrap;
    }
    #workflowModal .rating-badge.vs { background: #dcfce7; color: #166534; }
    #workflowModal .rating-badge.s  { background: #dbeafe; color: #1e40af; }
    #workflowModal .rating-badge.n  { background: #fef9c3; color: #854d0e; }
    #workflowModal .rating-badge.d  { background: #fee2e2; color: #991b1b; }
    #workflowModal .rating-badge.vd { background: #fecaca; color: #7f1d1d; }
    #workflowModal .rating-badge.accepted  { background: #dcfce7; color: #166534; }
    #workflowModal .rating-badge.rejected  { background: #fee2e2; color: #991b1b; }

    /* ─── Summary area - QR left, Summary table floats right ─── */
    #workflowModal .rc-summary-wrapper {
        display: flex; align-items: stretch; justify-content: space-between; margin-top: 8px;
    }
    #workflowModal .rc-summary-wrapper .rc-summary-qr {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 4px; flex-shrink: 0;
    }
    #workflowModal .rc-summary-wrapper .rc-summary-qr img {
        width: 60px; height: 60px;
    }
    #workflowModal .rc-summary-wrapper .rc-summary-qr .qr-label {
        font-size: 7px; color: var(--ink-400, #8b8592);
        text-transform: uppercase; letter-spacing: .04em; font-weight: 600;
        text-align: center; line-height: 1.2;
    }
    #workflowModal .rc-summary-table {
        border-collapse: collapse; flex: 1; max-width: 260px;
        border: 1px solid var(--ink-100, #eeedf0);
        border-radius: var(--fluent-radius-md, 6px);
        overflow: hidden;
    }
    #workflowModal .rc-summary-table td {
        border-bottom: 1px solid var(--ink-50, #f7f7f8);
        padding: 4px 10px; font-size: 10.5px; color: var(--ink-700, #38333e);
    }
    #workflowModal .rc-summary-table tr:last-child td {
        border-bottom: none;
    }
    #workflowModal .rc-summary-table td:first-child {
        background: var(--sand-50, #faf7f0); font-weight: 600;
        color: var(--ink-600, #4c4553); width: 120px;
    }
    #workflowModal .rc-summary-table td:last-child {
        font-weight: 700; color: var(--brand-600, #7a1636);
        text-align: center;
    }
    #workflowModal .rc-summary-table .sum-score {
        font-family: "Fraunces", serif;
        font-size: 16px; font-weight: 600;
        color: var(--brand-500, #8d1b3d);
    }

    /* ─── Empty state ─── */
    #workflowModal .rc-empty {
        text-align: center; padding: 20px 16px; color: var(--ink-400, #8b8592);
    }
    #workflowModal .rc-empty i {
        font-size: 24px; color: var(--ink-200, #d8d6dc);
        margin-bottom: 4px; display: block;
    }
    #workflowModal .rc-empty p {
        font-size: 11px; margin: 0; color: var(--ink-500, #675f6e);
    }

    /* ─── Footer notes ─── */
    #workflowModal .rc-notes {
        margin-top: 10px; padding-top: 6px;
        border-top: 1px solid var(--ink-100, #eeedf0);
        font-size: 9px; color: var(--ink-400, #8b8592); line-height: 1.3;
    }
    #workflowModal .rc-notes strong { color: var(--ink-600, #4c4553); }

    /* ─── Footer branding area (logo only, QR moved to summary) ─── */
    #workflowModal .rc-footer {
        display: flex; align-items: flex-end; justify-content: space-between;
        margin-top: 8px; padding-top: 6px;
        border-top: 1px solid var(--ink-100, #eeedf0);
    }
    #workflowModal .rc-footer .rc-footer-left {
        display: flex; align-items: center; gap: 14px;
    }
    #workflowModal .rc-footer .rc-logo {
        width: 52px; height: auto; opacity: .8;
    }
    #workflowModal .rc-footer .rc-footer-right {
        font-size: 8px; color: var(--ink-400, #8b8592);
        text-align: right; line-height: 1.3;
    }

    /* ─── Actions bar ─── */
    #workflowModal .rc-actions {
        display: flex; gap: 6px; justify-content: flex-end;
        padding: 8px 0 0; margin-top: 10px;
    }
    #workflowModal .rc-actions .btn-print {
        display: inline-flex; align-items: center; gap: 5px;
        background: var(--brand-500, #8d1b3d); color: #fff;
        font-size: 10px; font-weight: 600; padding: 5px 12px;
        border-radius: var(--fluent-radius-md, 6px);
        border: 1px solid var(--brand-600, #7a1636);
        box-shadow: var(--fluent-depth-2, 0 1px 2px rgba(22,19,26,.07));
        cursor: pointer; transition: background .12s, box-shadow .12s;
    }
    #workflowModal .rc-actions .btn-print:hover {
        background: var(--brand-600, #7a1636);
        box-shadow: var(--fluent-depth-4, 0 2px 4px rgba(22,19,26,.09));
    }
    #workflowModal .rc-actions .btn-close-modal {
        display: inline-flex; align-items: center; gap: 5px;
        background: #fff; color: var(--ink-700, #38333e);
        font-size: 10px; font-weight: 600; padding: 5px 12px;
        border-radius: var(--fluent-radius-md, 6px);
        border: 1px solid var(--ink-200, #d8d6dc);
        box-shadow: var(--fluent-depth-2, 0 1px 2px rgba(22,19,26,.07));
        cursor: pointer; transition: background .12s, box-shadow .12s;
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
            padding: 28px 32px !important;
        }
    }
</style>

<div class="rc-report-card-inner" style="padding: 20px 24px;">

    {{-- ===== HEADER: Logo left, Title right ===== --}}
    <div class="report-card-header">
        <div class="rc-header-logo">
            @if($logoBase64)
            <img src="{{ $logoBase64 }}" alt="Research Logo" />
            @endif
        </div>
        <div class="rc-title-area">
            <h2>Report Card</h2>
            <div class="rc-sub">Project Evaluation Summary</div>
        </div>
    </div>

    {{-- ===== PROJECT INFO ===== --}}
    <div class="rc-meta">
        <span class="rc-meta-item">
            <strong>Institution:</strong> Qatar University
        </span>
        <span class="rc-meta-item">
            <strong>Project ID:</strong>
            <span class="rc-project-id">{{ $project->old_project_id ?? 'PROJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
        </span>
        <span class="rc-meta-item" style="flex:1 1 100%;">
            <strong>Project Title:</strong> {{ $project->title }}
        </span>
        @if(isset($project->lpi) && $project->lpi)
        <span class="rc-meta-item"><strong>PI:</strong> {{ $project->lpi->name }} ({{ $project->lpi->email ?? '' }})</span>
        @endif
        @if(isset($project->program) && $project->program)
        <span class="rc-meta-item"><strong>Research Call:</strong> {{ $project->program->program_title ?? $project->program->title }}</span>
        @endif
        <span class="rc-meta-item"><strong>Grant:</strong> {{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</span>
        @if(isset($project->pillars) && $project->pillars->count())
        <span class="rc-meta-item"><strong>Pillars:</strong> {{ $project->pillars->pluck('pillar_name')->implode(', ') }}</span>
        @endif
        @if(isset($project->colleges) && $project->colleges->count())
        <span class="rc-meta-item"><strong>Colleges:</strong> {{ $project->colleges->pluck('name')->implode(', ') }}</span>
        @endif
        <span class="rc-meta-item">
            <strong>Status:</strong>
            @php $status = $project->latestStatus->status ?? 'Unknown'; @endphp
            <span class="rating-badge {{ in_array($status, ['Graded', 'Completed']) ? 'vs' : (in_array($status, ['rejected', 'progress_rejected']) ? 'd' : 's') }}">{{ $status }}</span>
        </span>
    </div>

    {{-- ===== CORE INFO GRID ===== --}}
    <div class="rc-section">Core Information</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:4px;">
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Project Title:</strong><br>{{ $project->title }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Program:</strong><br>{{ $project->program->program_title ?? '—' }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Grant:</strong><br>{{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</div>
        </div>
    </div>

    {{-- ===== COMMITMENTS ===== --}}
    @if($commitment)
    <div class="rc-section">Commitments</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:4px;">
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Journal Articles (Q1-Q4):</strong><br>{{ ($commitment->q1article ?? 0) + ($commitment->q2article ?? 0) + ($commitment->q3article ?? 0) + ($commitment->q4article ?? 0) }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Conference Papers:</strong><br>{{ $commitment->confArticle ?? 0 }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Books:</strong><br>{{ ($commitment->books ?? 0) + ($commitment->editBooks ?? 0) }}</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:4px;">
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Book Chapters:</strong><br>{{ $commitment->chapters ?? 0 }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>IP Disclosure:</strong><br>{{ $commitment->ip ?? 0 }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Patents:</strong><br>{{ ($commitment->filedPatent ?? 0) + ($commitment->grantedPatent ?? 0) }}</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:4px;">
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Open Source SW:</strong><br>{{ $commitment->openSourceSW ?? 0 }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Startups:</strong><br>{{ $commitment->startUp ?? 0 }}</div>
        </div>
        <div class="rc-meta" style="margin:0;padding:6px 8px;">
            <div class="rc-meta-item"><strong>Students:</strong><br>M: {{ $commitment->master ?? 0 }} | UG: {{ $commitment->UG ?? 0 }} | PhD: {{ $commitment->Phd ?? 0 }}</div>
        </div>
    </div>
    @endif

    {{-- ===== PUBLICATIONS ===== --}}
    @php
        $pubTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
        $publicationOutcomes = $outcomes->filter(fn($o) => in_array($o->type, $pubTypes));
    @endphp
    @if($publicationOutcomes->count())
    <div class="rc-section">
        Publications <span class="section-count">{{ $publicationOutcomes->count() }}</span>
    </div>
    <table class="rc-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Title</th>
                <th>Journal/Book</th>
                <th>Year</th>
                <th>DOI</th>
                <th>Verified</th>
            </tr>
        </thead>
        <tbody>
            @foreach($publicationOutcomes as $o)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><span class="rating-badge s">{{ strtoupper(str_replace('_', ' ', $o->type)) }}</span></td>
                <td>{{ $o->publication->publication_title ?? $o->identifier ?? '—' }}</td>
                <td>{{ $o->publication->journal ?? '—' }}</td>
                <td>{{ $o->publication->year ?? '—' }}</td>
                <td>{{ $o->publication->doi ?? '—' }}</td>
                <td>
                    @if($o->publication)
                        <span style="color:var(--color-success,#2e7d32);font-weight:700;">&#10003;</span>
                    @else
                        <span style="color:var(--color-gold-500,#ef6c00);">&#9675;</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ===== STUDENTS ===== --}}
    @if(isset($students) && $students->count())
    <div class="rc-section">
        Students <span class="section-count">{{ $students->count() }}</span>
    </div>
    <table class="rc-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Type</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Major</th>
                <th>College</th>
                <th>Days</th>
                <th>Verified</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $s)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><span class="rating-badge s">{{ $s->type }}</span></td>
                <td>{{ $s->std_id ?? '—' }}</td>
                <td>{{ $s->details->full_name ?? '—' }}</td>
                <td>{{ $s->details->major ?? '—' }}</td>
                <td>{{ $s->details->college ?? '—' }}</td>
                <td>{{ $s->days ?? '—' }}</td>
                <td>
                    @if($s->details)
                        <span style="color:var(--color-success,#2e7d32);font-weight:700;">&#10003;</span>
                    @else
                        <span style="color:var(--color-gold-500,#ef6c00);">&#9675;</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ===== RESEARCHERS ===== --}}
    @if(isset($researchers) && $researchers->count())
    <div class="rc-section">
        Researchers <span class="section-count">{{ $researchers->count() }}</span>
    </div>
    <table class="rc-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Category</th>
                <th>Days</th>
            </tr>
        </thead>
        <tbody>
            @foreach($researchers as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $r->name ?? '—' }}</td>
                <td>{{ $r->category ?? '—' }}</td>
                <td>{{ $r->days ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ===== PROGRESS REPORT GRADING ===== --}}
    @if($progressGrading && $progressGrading->publish !== 'pending')
    <div class="rc-section">Progress Report Grading</div>
    <div class="rc-meta" style="margin:0 0 6px;padding:6px 8px;">
        <div class="rc-meta-item">
            <strong>Status:</strong>
            <span class="rating-badge {{ $progressGrading->isAccepted == 1 ? 'vs' : 'd' }}">{{ $progressGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}</span>
        </div>
        <div class="rc-meta-item">
            <strong>Ethical Approval:</strong>
            <span class="rating-badge {{ $progressGrading->ethical ? 'vs' : 'n' }}">{{ $progressGrading->ethical ? 'Yes' : 'No' }}</span>
        </div>
    </div>
    <table class="rc-table">
        <thead>
            <tr>
                <th>Section</th>
                <th>Rating</th>
                <th>Comments</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="reviewer-label">Achievements</td>
                <td><strong>{{ $progressGrading->achievementsRating ?? '—' }}/5</strong></td>
                <td>{{ $progressGrading->achievementsComments ?? '—' }}</td>
            </tr>
            <tr>
                <td class="reviewer-label">Publications</td>
                <td><strong>{{ $progressGrading->publicationsRating ?? '—' }}/5</strong></td>
                <td>{{ $progressGrading->publicationsComments ?? '—' }}</td>
            </tr>
            <tr>
                <td class="reviewer-label">Students</td>
                <td><strong>{{ $progressGrading->studentsRating ?? '—' }}/5</strong></td>
                <td>{{ $progressGrading->studentsComments ?? '—' }}</td>
            </tr>
            <tr>
                <td class="reviewer-label">Budget</td>
                <td><strong>{{ $progressGrading->budgetRating ?? '—' }}/5</strong></td>
                <td>{{ $progressGrading->budgetComments ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
    @if($progressGrading->analysis)
    <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
        <div class="rc-meta-item" style="flex:1;"><strong>Analysis:</strong> {{ $progressGrading->analysis }}</div>
    </div>
    @endif
    @if($progressGrading->comments)
    <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
        <div class="rc-meta-item" style="flex:1;"><strong>Comments:</strong> {{ $progressGrading->comments }}</div>
    </div>
    @endif
    @if($progressGrading->recommendation)
    <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
        <div class="rc-meta-item" style="flex:1;"><strong>Recommendation:</strong> {{ $progressGrading->recommendation }}</div>
    </div>
    @endif
    @endif

    {{-- ===== FINAL REPORT GRADING ===== --}}
    @if($finalGrading && $finalGrading->publish !== 'pending')
    <div class="rc-section">Final Report Grading</div>
    <div class="rc-meta" style="margin:0 0 6px;padding:6px 8px;">
        <div class="rc-meta-item">
            <strong>Status:</strong>
            <span class="rating-badge {{ $finalGrading->isAccepted == 1 ? 'vs' : 'd' }}">{{ $finalGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}</span>
        </div>
        <div class="rc-meta-item">
            <strong>Total Score:</strong>
            <span class="rating-badge s" style="font-size:12px;">{{ $finalGrading->total ?? '—' }}</span>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:4px;">
        <div>
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Grade</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="reviewer-label">A. Achievements</td>
                        <td><strong>{{ $finalGrading->gradeA ?? '—' }}/5</strong></td>
                        <td>{{ $finalGrading->commentA ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="reviewer-label">B. Publications</td>
                        <td><strong>{{ $finalGrading->gradeB ?? '—' }}/5</strong></td>
                        <td>{{ $finalGrading->commentB ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="reviewer-label">C. Students</td>
                        <td><strong>{{ $finalGrading->gradeC ?? '—' }}/5</strong></td>
                        <td>{{ $finalGrading->commentC ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="reviewer-label">D. Impact</td>
                        <td><strong>{{ $finalGrading->gradeD ?? '—' }}/5</strong></td>
                        <td>{{ $finalGrading->commentD ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <div class="rc-meta" style="margin:0;padding:10px;text-align:center;">
                <div class="rc-meta-item">
                    <strong>Score Breakdown</strong><br>
                    <span style="font-size:14px;font-weight:700;color:var(--brand-700,#63102b);">{{ $finalGrading->total ?? '—' }}</span>
                </div>
            </div>
            <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
                <div class="rc-meta-item"><strong>A (Achievements):</strong> {{ $finalGrading->scoreA ?? '—' }} / {{ $finalGrading->autoGradeA ?? '—' }}</div>
            </div>
            <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
                <div class="rc-meta-item"><strong>B (Publications):</strong> {{ $finalGrading->scoreB ?? '—' }} / {{ $finalGrading->autoGradeB ?? '—' }}</div>
            </div>
            <div class="rc-meta" style="margin:6px 0 0;padding:6px 8px;">
                <div class="rc-meta-item"><strong>C (Students):</strong> {{ $finalGrading->scoreC ?? '—' }} / {{ $finalGrading->autoGradeC ?? '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== NOTES / DISCLAIMER ===== --}}
    <div class="rc-notes">
        <strong>NOTES:</strong> Please do not share the details contained within this document with unauthorized individuals.
    </div>

    {{-- ===== FOOTER: Document info only (logo is in header) ===== --}}
    <div class="rc-footer">
        <div class="rc-footer-right" style="width:100%;">
            Document ID: {{ $project->old_project_id ?? 'PROJ-' . str_pad($project->id, 4, '0', STR_PAD_LEFT) }}<br>
            Generated {{ now()->format('d M Y, h:i A') }}
        </div>
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
    printWindow.document.write('.rc-report-card-inner { padding: 28px 32px !important; max-width: 800px; margin: 0 auto; }');
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
