@php
    $rejectionStatus = $reportType === 'final'
        ? \App\Models\Project::STATUS_FINAL_REJECTED
        : ($reportType === 'progress2'
            ? \App\Models\Project::STATUS_PROGRESS2_REJECTED
            : \App\Models\Project::STATUS_PROGRESS_REJECTED);
    $latestRejection = $project->statusHistories()
        ->where('status', $rejectionStatus)
        ->latest()->first();
    $reportLabel = $reportType === 'final' ? 'Final Report'
        : ($reportType === 'progress2' ? 'Progress Report 2' : 'Progress Report');
@endphp

<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-balance-scale" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Review {{ $reportLabel }} Rejection
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="modal-body" style="padding:16px 24px 20px;">
    {{-- Project Info --}}
    <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;font-weight:600;color:var(--color-ink-800);">{{ $project->title }}</span>
            <span style="background:var(--color-danger);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;">{{ $reportLabel }} Rejected</span>
        </div>
        <div style="font-size:12px;color:var(--color-ink-500);margin-top:6px;">
            <strong>Project ID:</strong> {{ $project->old_project_id ?? 'N/A' }} &middot; <strong>LPI:</strong> {{ $project->lpi->name ?? 'N/A' }}
        </div>
    </div>

    {{-- Rejection Reason --}}
    @if($latestRejection)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
            <i class="fas fa-exclamation-triangle" style="color:var(--color-danger);font-size:14px;"></i>
            <strong style="font-size:13px;color:var(--color-danger);">Reviewer's Rejection Reason</strong>
        </div>
        <p style="font-size:13px;color:var(--color-ink-600);margin:0;">
            {{ $latestRejection->metadata['comment'] ?? $latestRejection->metadata['reason'] ?? 'No reason provided' }}
        </p>
        @if(isset($latestRejection->user))
        <p style="font-size:11px;color:var(--color-ink-400);margin:8px 0 0;">
            Rejected by {{ $latestRejection->user->name }} on {{ $latestRejection->created_at->format('M d, Y H:i') }}
        </p>
        @endif
    </div>
    @endif

    {{-- Message to LPI --}}
    <form id="reviewRejectionForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="report_type" value="{{ $reportType }}">
        <input type="hidden" name="action" value="send_to_lpi">

        <label style="font-size:12px;font-weight:600;display:block;margin-bottom:6px;color:var(--color-ink-700);">
            Message to LPI <span style="color:var(--color-danger);">*</span>
        </label>
        <textarea name="message" rows="3" maxlength="2000" required
            style="width:100%;font-size:13px;border:1px solid var(--color-ink-200);border-radius:6px;padding:8px 10px;color:var(--color-ink-700);"
            placeholder="Explain what needs to be revised in the {{ strtolower($reportLabel) }}..."></textarea>
    </form>

    <div id="reviewRejectionError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;font-size:13px;color:var(--color-danger);margin-top:12px;">
        <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
        <span id="reviewRejectionErrorText"></span>
    </div>
</div>

<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-primary btn-sm" onclick="submitRejectionReview()" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-paper-plane"></i> Send to LPI
    </button>
</div>

<script>
function submitRejectionReview() {
    var form = document.getElementById('reviewRejectionForm');
    var formData = new FormData(form);

    var message = formData.get('message');
    if (!message || !message.trim()) {
        var errorDiv = document.getElementById('reviewRejectionError');
        var errorText = document.getElementById('reviewRejectionErrorText');
        errorText.textContent = 'Please enter a message for the LPI.';
        errorDiv.style.display = 'block';
        return;
    }

    var errorDiv = document.getElementById('reviewRejectionError');
    errorDiv.style.display = 'none';

    fetch('/workflow/review-rejection/{{ $project->id }}', {
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
            document.getElementById('reviewRejectionErrorText').textContent = data.error || 'Failed to process request.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(function(error) {
        document.getElementById('reviewRejectionErrorText').textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    });
}
</script>
