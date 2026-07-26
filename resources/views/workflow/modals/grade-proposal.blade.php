@php
    // Get the current user's reviewer role for this project
    $gradeReviewerPivot = DB::table('projects_reviewers')
        ->where('project_id', $project->id)
        ->where('user_id', auth()->id())
        ->first();
    $gradeLabel = 'Grade';
@endphp
<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-star" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Grade Project
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    <div style="margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        <span style="background:var(--color-brand-500);color:#fff;font-size:11px;font-weight:600;padding:3px 9px;border-radius:999px;">
            {{ $gradeLabel }}
        </span>
        <span style="font-size:12px;color:var(--color-ink-400);">
            You are grading as <strong>{{ ($gradeReviewerPivot ? $gradeReviewerPivot->role : 'Reviewer') }}</strong>
        </span>
    </div>
    <p style="color:var(--color-ink-600);font-size:13px;margin-bottom:16px;">
        You are about to grade <strong>{{ $project->title }}</strong>. You have already accepted the proposal — now provide your assessment.
    </p>

    <div class="d-flex align-items-start gap-3" style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;">
        <div style="flex-shrink:0;width:36px;height:36px;border-radius:6px;background:rgba(141,27,61,.1);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-file-alt" style="color:var(--color-brand-500);font-size:16px;"></i>
        </div>
        <div style="flex:1;">
            <div style="font-size:13px;font-weight:600;">{{ $project->title }}</div>
            <div style="font-size:12px;color:var(--color-ink-400);margin-top:2px;">
            ID: <code>{{ $project->old_project_id ?? 'N/A' }}</code> &middot; {{ $project->program->program_title ?? 'N/A' }}
            </div>
        </div>
    </div>

    <form id="gradeForm" action="{{ route('grading.saveGrade', $project->id) }}" method="POST">
        @csrf
        <div style="margin-bottom:14px;">
            <label style="font-size:13px;font-weight:500;color:var(--color-ink-700);display:block;margin-bottom:4px;">
                Reviewer Grade (0-100) <span style="color:var(--color-danger);">*</span>
            </label>
            <input type="number" name="reviewer_grade" min="0" max="100" step="0.1" required
                   style="width:100%;padding:8px 10px;border:1px solid var(--color-ink-200);border-radius:6px;font-size:13px;"
                   placeholder="Enter a grade between 0 and 100">
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:13px;font-weight:500;color:var(--color-ink-700);display:block;margin-bottom:4px;">
                Outcome Grade (0-100)
            </label>
            <input type="number" name="outcome_grade" min="0" max="100" step="0.1"
                   style="width:100%;padding:8px 10px;border:1px solid var(--color-ink-200);border-radius:6px;font-size:13px;"
                   placeholder="Enter an outcome grade (optional)">
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:13px;font-weight:500;color:var(--color-ink-700);display:block;margin-bottom:4px;">
                Comments
            </label>
            <textarea name="comments" rows="3"
                      style="width:100%;padding:8px 10px;border:1px solid var(--color-ink-200);border-radius:6px;font-size:13px;resize:vertical;"
                      placeholder="Optional comments about this project..."></textarea>
        </div>
    </form>

    <div class="text-end" style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid var(--color-ink-100);">
        <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn-primary btn-sm" onclick="submitGradeForm()">
            <i class="fas fa-save"></i> Submit Grade
        </button>
    </div>
</div>

<script>
    function submitGradeForm() {
        const form = document.getElementById('gradeForm');
        const grade = form.querySelector('[name="reviewer_grade"]').value.trim();

        if (!grade || parseFloat(grade) < 0 || parseFloat(grade) > 100) {
            alert('Please enter a valid grade between 0 and 100.');
            return;
        }

        // Submit via AJAX
        $.ajax({
            url: form.action,
            method: 'POST',
            data: $(form).serialize(),
            success: function(res) {
                if (res.success) {
                    // Close modal and reload
                    $('#workflowModal').modal('hide');
                    $('.modal-backdrop').remove();
                    location.reload();
                } else {
                    alert(res.message || 'Failed to save grade.');
                }
            },
            error: function(xhr) {
                const err = xhr.responseJSON;
                alert(err?.message || err?.error || 'Failed to save grade. Please try again.');
            }
        });
    }
</script>
