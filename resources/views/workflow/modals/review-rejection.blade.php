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
    $rejType = $latestRejection->metadata['rejection_type'] ?? 'report';
    $rejTypeLabels = [
        'report'  => $reportLabel . ' Rejection',
        'ethical' => 'Missing/Invalid Ethical Approval',
        'other'   => 'Other',
    ];
    $rejTypeLabel = $rejTypeLabels[$rejType] ?? $rejTypeLabels['report'];
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
            <span style="margin-left:auto;background:#fff;border:1px solid #fecaca;color:var(--color-danger);font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;">{{ $rejTypeLabel }}</span>
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

    {{-- Sending feedback: the request also delivers the e-mail, which can take a
         few seconds on a real SMTP host, so the dialog must look alive. --}}
    <div id="reviewRejectionProgress" style="display:none;margin-top:14px;">
        <div style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--color-brand-600);">
            <i class="fas fa-circle-notch fa-spin"></i>
            <span id="reviewRejectionProgressText">Sending the review and the e-mail to the LPI…</span>
        </div>
        <div style="position:relative;margin-top:8px;height:6px;border-radius:999px;background:var(--color-sand-50);border:1px solid var(--color-ink-100);overflow:hidden;">
            <div style="position:absolute;top:0;bottom:0;width:35%;border-radius:999px;background:var(--color-brand-500);animation:reviewRejectionBar 1.2s ease-in-out infinite;"></div>
        </div>
        <div style="margin-top:6px;font-size:11px;color:var(--color-ink-400);line-height:1.5;">
            This can take a few seconds while the notification e-mail is sent.
            Please keep this window open — it reloads automatically when the review is saved.
        </div>
    </div>
</div>

<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" id="reviewRejectionCancelBtn" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-primary btn-sm" id="reviewRejectionSendBtn" onclick="submitRejectionReview()" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-paper-plane"></i> Send to LPI
    </button>
</div>

<style>
    @keyframes reviewRejectionBar {
        0%   { left: -35%; }
        100% { left: 100%; }
    }
</style>

<script>
function submitRejectionReview() {
    var form        = document.getElementById('reviewRejectionForm');
    var btn         = document.getElementById('reviewRejectionSendBtn');
    var cancelBtn   = document.getElementById('reviewRejectionCancelBtn');
    var progress    = document.getElementById('reviewRejectionProgress');
    var progressTxt = document.getElementById('reviewRejectionProgressText');
    var errorDiv    = document.getElementById('reviewRejectionError');
    var errorText   = document.getElementById('reviewRejectionErrorText');

    var formData = new FormData(form);
    var message  = formData.get('message');

    errorDiv.style.display = 'none';

    if (!message || !message.trim()) {
        errorText.textContent = 'Please enter a message for the LPI.';
        errorDiv.style.display = 'block';
        return;
    }

    function resetButton() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send to LPI';
        if (cancelBtn) {
            cancelBtn.disabled = false;
        }
        progress.style.display = 'none';
    }

    // Lock the dialog and show the progress bar until the request finishes.
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending…';
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    progressTxt.textContent = 'Sending the review and the e-mail to the LPI…';
    progress.style.display = 'block';

    fetch(appUrl('/workflow/review-rejection/{{ $project->id }}'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(function(response) {
        return response.json().then(function(body) {
            return { status: response.status, body: body || {} };
        });
    })
    .then(function(res) {
        var data = res.body;

        if (res.status >= 200 && res.status < 300 && data.success) {
            progressTxt.textContent = 'Sent — reloading the page…';
            location.reload();
            return;
        }

        var firstError = data.errors ? Object.values(data.errors)[0] : null;
        resetButton();
        errorText.textContent = data.error
            || (Array.isArray(firstError) ? firstError[0] : firstError)
            || 'Failed to process request.';
        errorDiv.style.display = 'block';
    })
    .catch(function() {
        resetButton();
        errorText.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    });
}
</script>
