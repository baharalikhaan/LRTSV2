<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-clipboard-check" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Review Project
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    <p style="color:var(--color-ink-600);font-size:13px;margin-bottom:16px;">
        You are about to mark <strong>{{ $project->title }}</strong> as reviewed.
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

    <p style="font-size:13px;color:var(--color-ink-600);">
        <i class="fas fa-info-circle" style="color:var(--color-info);margin-right:6px;"></i>
        Confirm that you have reviewed this project. This will update the project status to "Reviewed".
    </p>

    <div class="text-end" style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid var(--color-ink-100);">
        <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn-primary btn-sm" onclick="recordStatus({{ $project->id }}, 'review')">
            <i class="fas fa-check"></i> Confirm Reviewed
        </button>
    </div>
</div>
