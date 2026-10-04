@php
    $userId = auth()->id();
    $reviewerPivot = DB::table('projects_reviewers')
        ->where('project_id', $project->id)
        ->where('user_id', $userId)
        ->first();

    $progressRows = function ($g) {
        return [
            ['label' => 'Progress Toward Achieving Outcomes', 'rating' => $g->achievementsRating ?? null, 'word' => $g->achievementsRatingRef->rating ?? null, 'comments' => $g->achievementsComments ?? null],
            ['label' => 'Progress in Publications',           'rating' => $g->publicationsRating ?? null, 'word' => $g->publicationsRatingRef->rating ?? null, 'comments' => $g->publicationsComments ?? null],
            ['label' => 'Student Involvement & Capacity Building', 'rating' => $g->studentsRating ?? null, 'word' => $g->studentsRatingRef->rating ?? null, 'comments' => $g->studentsComments ?? null],
            ['label' => 'Budget Utilization',                 'rating' => $g->budgetRating ?? null,       'word' => $g->budgetRatingRef->rating ?? null,       'comments' => $g->budgetComments ?? null],
        ];
    };

    $hasAnyGrade = ($progressGrading && $progressGrading->publish !== 'pending' && $progressGrading->isAccepted !== null)
        || ($progress2Grading && $progress2Grading->publish !== 'pending' && $progress2Grading->isAccepted !== null)
        || ($finalGrading && $finalGrading->publish !== 'pending' && $finalGrading->isAccepted !== null);
@endphp

<style>
.vg-modal{background:#fff;border-radius:8px;overflow:hidden;box-shadow:var(--fluent-depth-16)}
.vg-head{display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--brand-500,#8d1b3d);color:#fff;padding:12px 18px;font-size:14px;font-weight:500}
.vg-head span{display:inline-flex;align-items:center;gap:8px;min-width:0}
.vg-head .vg-title{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.vg-close{background:transparent;border:none;color:#fff;font-size:22px;line-height:1;cursor:pointer;opacity:.85}
.vg-close:hover{opacity:1}
.vg-body{padding:14px 18px;font-size:13px;color:var(--ink-600,#4c4553);max-height:70vh;overflow-y:auto}
.vg-sub{display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--brand-50,#fbeef1);border:1px solid var(--brand-200,#e8c9d2);border-radius:6px;padding:7px 12px;margin:0 0 10px;font-size:12.5px;font-weight:500;color:var(--brand-700,#6d122e)}
.vg-sub i{color:var(--brand-500,#8d1b3d)}
.vg-sub .vg-date{font-size:10.5px;font-weight:400;color:var(--ink-400,#8b8592);white-space:nowrap}
.vg-summary{background:var(--sand-50,#faf7f0);border:1px solid var(--ink-100,#eeedf0);border-radius:6px;padding:4px 14px;font-size:12.5px}
.vg-row{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:7px 0;border-bottom:1px solid var(--ink-100,#eeedf0)}
.vg-row:last-child{border-bottom:none}
.vg-label{font-weight:500;color:var(--ink-700,#38333e);font-size:12px}
.vg-value{font-weight:500;color:var(--brand-600,#7a1636);font-size:12.5px;white-space:nowrap;flex-shrink:0}
.vg-comment{color:var(--ink-500,#675f6e);font-size:11.5px;margin-top:3px;white-space:pre-wrap}
.vg-pill{display:inline-flex;align-items:center;gap:4px;border-radius:999px;padding:2px 9px;font-size:11px;font-weight:500;margin:2px 0 6px}
.vg-pill-ok{background:#e8f5ee;color:var(--success,#1f8a5f);border:1px solid #a8e6b8}
.vg-pill-no{background:#fbeef1;color:var(--danger,#b3261e);border:1px solid #f5c6cb}
.vg-empty{text-align:center;padding:28px 12px;color:var(--ink-400,#8b8592);background:var(--sand-50,#faf7f0);border:1px solid var(--ink-100,#eeedf0);border-radius:6px;font-size:12.5px}
.vg-empty i{font-size:26px;color:var(--ink-200,#d8d6dc);display:block;margin-bottom:6px}
.vg-footer{display:flex;justify-content:flex-end;padding:12px 18px;border-top:1px solid var(--ink-100,#eeedf0)}
</style>

<div class="vg-modal">
    <div class="vg-head">
        <span>
            <i class="fas fa-star" style="color:var(--color-gold-500,#f0b429);"></i>
            <span class="vg-title">My Grades &mdash; {{ \Illuminate\Support\Str::limit($project->project_title, 40) }}</span>
        </span>
        <button type="button" class="vg-close" data-dismiss="modal" aria-label="Close">&times;</button>
    </div>

    <div class="vg-body">
        {{-- Project context --}}
        <div style="padding:8px 12px;background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:6px;margin-bottom:12px;">
            <div style="font-size:12.5px;font-weight:500;color:var(--ink-800,#241f2a);">
                {{ $project->project_title }}
            </div>
            <div style="font-size:11.5px;color:var(--ink-400,#8b8592);margin-top:2px;">
                ID: <code>{{ $project->old_project_id ?? $project->id }}</code>
                &middot; {{ $project->program->program_title ?? 'N/A' }}
                @if($project->program && $project->program->grant)
                    &middot; {{ $project->program->grant->category ?? $project->program->grant->grant_code ?? 'N/A' }}
                @endif
                @if($reviewerPivot && $reviewerPivot->role)
                    &middot; Graded as <strong>{{ $reviewerPivot->role }}</strong>
                @endif
            </div>
        </div>

        @if(!$hasAnyGrade)
            <div class="vg-empty">
                <i class="fas fa-hourglass-half"></i>
                You have not submitted any grades for this project yet.
            </div>
        @else

        {{-- ═══ Progress Report ═══ --}}
        @if($progressGrading && $progressGrading->publish !== 'pending' && $progressGrading->isAccepted !== null)
        <div class="vg-sub">
            <span><i class="fas fa-chart-line"></i> Progress Report</span>
            @if($progressGrading->created_at)
            <span class="vg-date"><i class="far fa-clock"></i> {{ $progressGrading->created_at->format('d M Y') }}</span>
            @endif
        </div>
        <div class="vg-summary" style="margin-bottom:14px;">
            <span class="vg-pill {{ $progressGrading->isAccepted == 1 ? 'vg-pill-ok' : 'vg-pill-no' }}">
                <i class="fas {{ $progressGrading->isAccepted == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                {{ $progressGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
            </span>
            @foreach($progressRows($progressGrading) as $r)
            <div class="vg-row">
                <span class="vg-label">{{ $r['label'] }}</span>
                <span class="vg-value">{{ $r['word'] ? $r['word'] . ' (' . $r['rating'] . '/5)' : ($r['rating'] !== null ? $r['rating'] . '/5' : '—') }}</span>
            </div>
            @if($r['comments'])
            <div class="vg-comment" style="padding-bottom:6px;">{{ $r['comments'] }}</div>
            @endif
            @endforeach
            @if($progressGrading->ethical !== null)
            <div class="vg-row">
                <span class="vg-label">Ethical Approvals</span>
                <span class="vg-value" style="color:{{ $progressGrading->ethical ? 'var(--success,#1f8a5f)' : 'var(--danger,#b3261e)' }};">{{ $progressGrading->ethical ? 'Yes' : 'No' }}</span>
            </div>
            @endif
            @if($progressGrading->analysis)
            <div class="vg-comment" style="padding-top:6px;border-top:1px solid var(--ink-100,#eeedf0);">
                <span class="vg-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;display:block;">Analysis</span>
                {{ $progressGrading->analysis }}
            </div>
            @endif
            @if($progressGrading->recommendation)
            <div class="vg-comment" style="padding-top:6px;border-top:1px solid var(--ink-100,#eeedf0);">
                <span class="vg-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;display:block;">Recommendation</span>
                {{ $progressGrading->recommendation }}
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ Progress Report 2 ═══ --}}
        @if($progress2Grading && $progress2Grading->publish !== 'pending' && $progress2Grading->isAccepted !== null)
        <div class="vg-sub">
            <span><i class="fas fa-chart-line"></i> Progress Report 2</span>
            @if($progress2Grading->created_at)
            <span class="vg-date"><i class="far fa-clock"></i> {{ $progress2Grading->created_at->format('d M Y') }}</span>
            @endif
        </div>
        <div class="vg-summary" style="margin-bottom:14px;">
            <span class="vg-pill {{ $progress2Grading->isAccepted == 1 ? 'vg-pill-ok' : 'vg-pill-no' }}">
                <i class="fas {{ $progress2Grading->isAccepted == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                {{ $progress2Grading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
            </span>
            @foreach($progressRows($progress2Grading) as $r)
            <div class="vg-row">
                <span class="vg-label">{{ $r['label'] }}</span>
                <span class="vg-value">{{ $r['word'] ? $r['word'] . ' (' . $r['rating'] . '/5)' : ($r['rating'] !== null ? $r['rating'] . '/5' : '—') }}</span>
            </div>
            @if($r['comments'])
            <div class="vg-comment" style="padding-bottom:6px;">{{ $r['comments'] }}</div>
            @endif
            @endforeach
            @if($progress2Grading->ethical !== null)
            <div class="vg-row">
                <span class="vg-label">Ethical Approvals</span>
                <span class="vg-value" style="color:{{ $progress2Grading->ethical ? 'var(--success,#1f8a5f)' : 'var(--danger,#b3261e)' }};">{{ $progress2Grading->ethical ? 'Yes' : 'No' }}</span>
            </div>
            @endif
            @if($progress2Grading->analysis)
            <div class="vg-comment" style="padding-top:6px;border-top:1px solid var(--ink-100,#eeedf0);">
                <span class="vg-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;display:block;">Analysis</span>
                {{ $progress2Grading->analysis }}
            </div>
            @endif
            @if($progress2Grading->recommendation)
            <div class="vg-comment" style="padding-top:6px;border-top:1px solid var(--ink-100,#eeedf0);">
                <span class="vg-label" style="font-size:10.5px;text-transform:uppercase;letter-spacing:.04em;display:block;">Recommendation</span>
                {{ $progress2Grading->recommendation }}
            </div>
            @endif
        </div>
        @endif

        {{-- ═══ Final Report ═══ --}}
        @if($finalGrading && $finalGrading->publish !== 'pending' && $finalGrading->isAccepted !== null)
        @php
            $finalSections = [
                'A' => ['label' => 'Achievements against objectives', 'grade' => $finalGrading->gradeA ?? null, 'comment' => $finalGrading->commentA ?? null],
                'B' => ['label' => 'Publications & IP',              'grade' => $finalGrading->gradeB ?? null, 'comment' => $finalGrading->commentB ?? null],
                'C' => ['label' => 'Student & Young Researcher Involvement', 'grade' => $finalGrading->gradeC ?? null, 'comment' => $finalGrading->commentC ?? null],
                'D' => ['label' => 'Project Impact',                 'grade' => $finalGrading->gradeD ?? null, 'comment' => $finalGrading->commentD ?? null],
            ];
        @endphp
        <div class="vg-sub" style="background:#e8f5ee;border-color:#a8e6b8;color:#2e7d32;">
            <span><i class="fas fa-flag-checkered" style="color:#2e7d32;"></i> Final Report</span>
            @if($finalGrading->created_at)
            <span class="vg-date"><i class="far fa-clock"></i> {{ $finalGrading->created_at->format('d M Y') }}</span>
            @endif
        </div>
        <div class="vg-summary">
            <span class="vg-pill {{ $finalGrading->isAccepted == 1 ? 'vg-pill-ok' : 'vg-pill-no' }}">
                <i class="fas {{ $finalGrading->isAccepted == 1 ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                {{ $finalGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
            </span>
            @foreach($finalSections as $key => $s)
            <div class="vg-row">
                <span class="vg-label">{{ $key }}. {{ $s['label'] }}</span>
                <span class="vg-value">{{ $s['grade'] !== null ? $s['grade'] . '/5' : '—' }}</span>
            </div>
            @if($s['comment'])
            <div class="vg-comment" style="padding-bottom:6px;">{{ $s['comment'] }}</div>
            @endif
            @endforeach
            <div class="vg-row">
                <span class="vg-label">Total Score</span>
                <span class="vg-value" style="font-size:15px;">{{ $finalGrading->total ?? '—' }}</span>
            </div>
        </div>
        @endif

        @endif
    </div>

    <div class="vg-footer">
        <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</div>