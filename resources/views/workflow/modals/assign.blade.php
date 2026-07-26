<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-user-tag" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Assign Reviewer
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    <p style="color:var(--color-ink-600);font-size:13px;margin-bottom:16px;">
        Select a reviewer to assign to <strong>{{ $project->title }}</strong>.
    </p>

    <form id="assignReviewerForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">

        @php
            // Get currently assigned reviewer with their pivot data
            $assignedReviewers = $project->reviewers()->get();
            $currentReviewer = $assignedReviewers->first();
            $currentReviewerId = $currentReviewer ? $currentReviewer->id : null;

            $reviewers = \App\Models\User::whereIn('type', ['Reviewer', 'LPI+Reviewer', 'Admin+LPI+Reviewer'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        @endphp

        {{-- Single Reviewer --}}
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:600;color:var(--color-ink-700);display:block;margin-bottom:5px;">
                Reviewer
            </label>
            <select name="reviewer_ids[]" id="reviewer_1" class="reviewer-select" style="width:100%;padding:8px 10px;border:1px solid var(--color-ink-200);border-radius:6px;font-size:13px;color:var(--color-ink-800);background:#fff;appearance:auto;">
                <option value="">— Select Reviewer —</option>
                @foreach($reviewers as $reviewer)
                    <option value="{{ $reviewer->id }}" {{ $reviewer->id == $currentReviewerId ? 'selected' : '' }}>
                        {{ $reviewer->name }} ({{ $reviewer->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Already-assigned info --}}
        @if($currentReviewer)
            <div style="padding:8px 12px;border-radius:6px;background:var(--color-sand-50);border:1px solid var(--color-sand-200);font-size:12px;color:var(--color-sand-700);margin-bottom:10px;">
                <i class="fas fa-info-circle"></i>
                This project already has a reviewer assigned. Selecting a new reviewer will replace the existing assignment.
            </div>
        @endif

        <div id="assignError" style="display:none;margin-top:10px;padding:8px 12px;border-radius:6px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:12px;"></div>
    </form>
</div>
<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-primary btn-sm" id="saveAssignBtn" onclick="submitAssignment()" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-check"></i> Assign Reviewer
    </button>
</div>

