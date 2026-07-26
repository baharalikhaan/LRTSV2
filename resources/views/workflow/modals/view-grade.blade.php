@php
    // Get the current user's grading data for this project
    $myGrade = \App\Models\ProgressReportGrading::where('project_id', $project->id)
        ->where('user_id', auth()->id())
        ->first();
    if (!$myGrade) {
        $myGrade = \App\Models\FinalReportGrading::where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->first();
    }
@endphp
<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-star" style="color:var(--color-gold-500);margin-right:8px;"></i>
        My Grade &mdash; {{ \Illuminate\Support\Str::limit($project->project_title, 45) }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    {{-- Project context card --}}
    <div class="d-flex align-items-start gap-3" style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;">
        <div style="flex-shrink:0;width:36px;height:36px;border-radius:6px;background:rgba(141,27,61,.1);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-file-alt" style="color:var(--color-brand-500);font-size:16px;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;">{{ $project->project_title }}</div>
            <div style="font-size:12px;color:var(--color-ink-400);margin-top:2px;">
                ID: <code>{{ $project->old_project_id ?? $project->id }}</code>
                &middot; {{ $project->program->program_title ?? 'N/A' }}
                @if($project->program && $project->program->grant)
                    &middot; {{ $project->program->grant->category ?? $project->program->grant->grant_code ?? 'N/A' }}
                @endif
            </div>
        </div>
    </div>

    @if($myGrade)
        {{-- Grade display card --}}
        <div style="background:#fff;border:1px solid var(--color-ink-100);border-radius:8px;padding:20px;margin-bottom:16px;text-align:center;box-shadow:var(--fluent-depth-2);">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--color-ink-400);margin-bottom:8px;">
                Your Assessment
            </div>
            <div style="display:flex;justify-content:center;gap:32px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:36px;font-weight:700;color:var(--color-brand-500);line-height:1;">
                        {{ $myGrade->achievementsRating ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:var(--color-ink-400);margin-top:4px;">Reviewer Grade</div>
                </div>
                @if($myGrade->publicationsRating ?? $myGrade->total ?? null)
                <div>
                    <div style="font-size:36px;font-weight:700;color:var(--color-gold-500);line-height:1;">
                        {{ $myGrade->total ?? $myGrade->publicationsRating ?? '—' }}
                    </div>
                    <div style="font-size:11px;color:var(--color-ink-400);margin-top:4px;">Total Score</div>
                </div>
                @endif
            </div>
            @if($myGrade->created_at ?? false)
            <div style="font-size:11px;color:var(--color-ink-400);margin-top:12px;padding-top:12px;border-top:1px solid var(--color-ink-100);">
                <i class="far fa-clock"></i> Submitted {{ $myGrade->created_at->format('d M Y, h:i A') }}
            </div>
            @endif
        </div>

        {{-- Comments --}}
        @if($myGrade->commentA ?? $myGrade->achievementsComments ?? $myGrade->comments ?? false)
        @php
            $commentText = $myGrade->commentA ?? $myGrade->achievementsComments ?? $myGrade->comments ?? '';
        @endphp
        <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--color-ink-400);margin-bottom:6px;">
                <i class="fas fa-comment"></i> Your Comments
            </div>
            <p style="font-size:13px;color:var(--color-ink-700);margin:0;white-space:pre-wrap;">{{ $commentText }}</p>
        </div>
        @endif

        {{-- Reviewer role badge --}}
        @php
            $reviewerPivot = DB::table('projects_reviewers')
                ->where('project_id', $project->id)
                ->where('user_id', auth()->id())
                ->first();
        @endphp
        @if($reviewerPivot && $reviewerPivot->role)
        <div style="display:flex;align-items:center;gap:8px;padding:8px 0 0;">
            <span style="background:var(--color-brand-500);color:#fff;font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px;">
                {{ $reviewerPivot->role }}
            </span>
            <span style="font-size:12px;color:var(--color-ink-400);">Graded as <strong>{{ $reviewerPivot->role }}</strong></span>
        </div>
        @endif
    @else
        {{-- No grade yet --}}
        <div class="text-center py-4" style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;margin-bottom:16px;">
            <i class="fas fa-hourglass-half" style="font-size:32px;color:var(--color-ink-300);"></i>
            <p style="color:var(--color-ink-500);margin:8px 0 0;font-size:13px;">
                You have not submitted a grade for this project yet.
            </p>
            @if(in_array(optional($project->latestStatus)->status, ['Claimed', 'Graded']))
            <a href="{{ route('projects.show', $project->id) }}" class="btn-primary btn-sm" style="margin-top:12px;">
                <i class="fas fa-gavel"></i> Grade Now
            </a>
            @endif
        </div>
    @endif

    <div class="text-end" style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid var(--color-ink-100);">
        <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</div>
</write_to_file>
