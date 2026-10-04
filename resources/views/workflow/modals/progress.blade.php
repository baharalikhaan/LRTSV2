<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-chart-line" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Add Progress Report
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    <p style="color:var(--color-ink-600);font-size:13px;margin-bottom:16px;">
        Submit a progress report for <strong>{{ $project->title }}</strong>.
    </p>

    <div class="alert alert-info" style="font-size:13px;border-radius:6px;">
        <i class="fas fa-info-circle" style="margin-right:6px;"></i>
        Progress report submission will be implemented in a future update.
    </div>

    <div class="text-end mt-3" style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid var(--color-ink-100);">
        <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn-primary btn-sm" onclick="recordStatus({{ $project->id }}, 'progress')">
            <i class="fas fa-check"></i> Mark Progress Added
        </button>
    </div>
</div>
