@php
    $archive = $archive ?? null;
    $reportType = $reportType ?? 'progress';
    $reportTitle = $reportType === 'final' ? 'Final Report' : ($reportType === 'progress2' ? 'Progress Report 2' : 'Progress Report');

    $rejStatus = $reportType === 'final'
        ? \App\Models\Project::STATUS_FINAL_REJECTED
        : ($reportType === 'progress2'
            ? \App\Models\Project::STATUS_PROGRESS2_REJECTED
            : \App\Models\Project::STATUS_PROGRESS_REJECTED);
    $lastRejection = $project->statusHistories()
        ->where('status', $rejStatus)
        ->latest()->first();
    $rejectionReason = $lastRejection->metadata['comment'] ?? null;

    $isAccepted = ($archive['isAccepted'] ?? 0) == 1;
@endphp

@if($archive)
<div style="margin-top:12px;padding:12px 14px;background:#f8fafc;border:1px solid var(--ink-200);border-radius:8px;">
    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px;">
        <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">
            <i class="fas fa-history" style="margin-right:4px;"></i> Previous Grading — {{ $reportTitle }}
        </span>
        <span style="display:inline-flex;align-items:center;gap:4px;font-weight:500;font-size:12px;color:{{ $isAccepted ? 'var(--success)' : 'var(--danger)' }};">
            @if($isAccepted)
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Accepted
            @else
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Rejected
            @endif
        </span>
    </div>

    {{-- Rejection reason (latest recorded against this report type) --}}
    @if(!$isAccepted && $rejectionReason)
        <div style="margin-bottom:8px;padding:7px 10px;background:#fdf3f4;border:1px solid #f5c6cb;border-radius:6px;font-size:12px;line-height:1.5;">
            <strong style="color:var(--danger,#c62828);">Rejection reason:</strong>
            <span style="color:var(--ink-600);">{{ $rejectionReason }}</span>
        </div>
    @endif

    @if($reportType === 'final')
        @php
            $sections = [
                'A' => ['label' => 'Achievements against objectives',           'grade' => $archive['gradeA'] ?? null, 'comment' => $archive['commentA'] ?? null],
                'B' => ['label' => 'Publications & IP',                        'grade' => $archive['gradeB'] ?? null, 'comment' => $archive['commentB'] ?? null],
                'C' => ['label' => 'Student & Young Researcher Involvement',   'grade' => $archive['gradeC'] ?? null, 'comment' => $archive['commentC'] ?? null],
                'D' => ['label' => 'Project Impact',                           'grade' => $archive['gradeD'] ?? null, 'comment' => $archive['commentD'] ?? null],
            ];
        @endphp

        <div class="ws-ro-summary">
            @foreach($sections as $s)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">{{ $s['label'] }}</span>
                    <span class="ws-ro-value">{{ $s['grade'] ?? '—' }}/5</span>
                </div>
                @if($s['comment'])
                    <div class="ws-ro-comment" style="padding:0 0 8px 0;">{{ $s['comment'] }}</div>
                @endif
            @endforeach

            <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--ink-100);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Total</span>
                <span class="ws-ro-value">{{ $archive['total'] ?? '—' }}</span>
            </div>
        </div>
    @else
        @php
            $ratings = [
                'achievements' => ['label' => 'Progress Toward Achieving Outcomes', 'rating' => $archive['achievementsRating'] ?? null, 'comments' => $archive['achievementsComments'] ?? null],
                'publications' => ['label' => 'Progress in Publications',           'rating' => $archive['publicationsRating'] ?? null, 'comments' => $archive['publicationsComments'] ?? null],
                'students'     => ['label' => 'Student Involvement & Capacity Building', 'rating' => $archive['studentsRating'] ?? null, 'comments' => $archive['studentsComments'] ?? null],
                'budget'       => ['label' => 'Budget Utilization',                 'rating' => $archive['budgetRating'] ?? null,       'comments' => $archive['budgetComments'] ?? null],
            ];
        @endphp

        <div class="ws-ro-summary">
            @foreach($ratings as $r)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">{{ $r['label'] }}</span>
                    <span class="ws-ro-value">{{ $r['rating'] ?? '—' }}/5</span>
                </div>
                @if($r['comments'])
                    <div class="ws-ro-comment" style="padding:0 0 8px 0;">{{ $r['comments'] }}</div>
                @endif
            @endforeach

            @if(($archive['ethical'] ?? null) !== null)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">Ethical Approvals</span>
                    <span class="ws-pill {{ $archive['ethical'] ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $archive['ethical'] ? 'Yes' : 'No' }}</span>
                </div>
            @endif

            @if($archive['analysis'] ?? null)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span class="ws-mini-label">Analysis</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $archive['analysis'] }}</div>
                </div>
            @endif
            @if($archive['comments'] ?? null)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span class="ws-mini-label">Comments</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $archive['comments'] }}</div>
                </div>
            @endif
            @if($archive['recommendation'] ?? null)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span class="ws-mini-label">Recommendation</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $archive['recommendation'] }}</div>
                </div>
            @endif
        </div>
    @endif

    <p style="font-size:11px;color:var(--ink-400);margin:8px 0 0;line-height:1.5;">
        <i class="fas fa-info-circle" style="margin-right:3px;"></i>
        Archived grading of the superseded version. Select the latest version from the version dropdown to grade the new submission.
    </p>
</div>
@endif
