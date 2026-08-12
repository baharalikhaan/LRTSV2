<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-clock" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Review Extended Progress Request
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    @php
        $requestHistory = $project->statusHistories()
            ->where('status', \App\Models\Project::STATUS_EXT_PROGRESS_REQUESTED)
            ->latest()->first();
    @endphp

    {{-- Project Info Card --}}
    <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;font-weight:600;color:var(--color-ink-800);">{{ $project->title }}</span>
            <span style="background:var(--color-gold-500);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;white-space:nowrap;">Extended Progress</span>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:12px;color:var(--color-ink-500);">
            <span><strong style="color:var(--color-ink-700);">Project ID:</strong> <code style="font-size:11px;background:var(--color-ink-50);padding:1px 5px;border-radius:3px;">{{ $project->old_project_id ?? 'N/A' }}</code></span>
            <span><strong style="color:var(--color-ink-700);">LPI:</strong> {{ $project->lpi->name ?? 'N/A' }}</span>
            <span><strong style="color:var(--color-ink-700);">Request Date:</strong> {{ $requestHistory ? $requestHistory->created_at->format('M d, Y H:i') : 'N/A' }}</span>
        </div>
    </div>

    <p style="font-size:13px;color:var(--color-ink-600);margin:0 0 16px;">
        The LPI has requested to upload an extended progress report. Review the request and approve or reject it.
    </p>

    <form id="approveExtendedForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">

        {{-- Message to LPI --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:6px;color:var(--color-ink-700);">
                Message to LPI <span style="color:var(--color-ink-400);font-weight:400;">(optional)</span>
            </label>
            <textarea name="message" rows="3" maxlength="2000"
                style="width:100%;font-size:13px;border:1px solid var(--color-ink-200);border-radius:6px;padding:8px 10px;color:var(--color-ink-700);"
                placeholder="Add any notes for the LPI..."></textarea>
        </div>
    </form>

    <div id="approveExtendedError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;font-size:13px;color:var(--color-danger);margin-bottom:12px;">
        <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
        <span id="approveExtendedErrorText"></span>
    </div>
</div>
<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-danger btn-sm" onclick="submitApproval('rejected')" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-times-circle"></i> Reject
    </button>
    <button type="button" class="btn-success btn-sm" onclick="submitApproval('approved')" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-check-circle"></i> Approve
    </button>
</div>

@push('scripts')
<script>
function submitApproval(status) {
    var form = document.getElementById('approveExtendedForm');
    var formData = new FormData(form);
    formData.append('approve', status);

    var errorDiv = document.getElementById('approveExtendedError');
    var errorText = document.getElementById('approveExtendedErrorText');
    errorDiv.style.display = 'none';

    fetch('/workflow/approve-extended/{{ $project->id }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            errorText.textContent = data.error || 'Failed to process request.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(function(error) {
        errorText.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    });
}
</script>
@endpush
