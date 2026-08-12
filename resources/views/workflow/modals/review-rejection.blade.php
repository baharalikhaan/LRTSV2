<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-balance-scale" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Review {{ $reportType === 'extended_progress' ? 'Extended ' : '' }}Progress Rejection
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    @php
        $rejectionStatus = $reportType === 'extended_progress'
            ? \App\Models\Project::STATUS_PROGRESS_EXT_REJECTED
            : \App\Models\Project::STATUS_PROGRESS_REJECTED;
        $latestRejection = $project->statusHistories()
            ->where('status', $rejectionStatus)
            ->latest()->first();
    @endphp

    {{-- Project Info Card --}}
    <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;font-weight:600;color:var(--color-ink-800);">{{ $project->title }}</span>
            <span style="background:var(--color-danger);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;white-space:nowrap;">{{ $reportType === 'extended_progress' ? 'Extended Progress' : 'Progress' }} Rejected</span>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:12px;color:var(--color-ink-500);">
            <span><strong style="color:var(--color-ink-700);">Project ID:</strong> <code style="font-size:11px;background:var(--color-ink-50);padding:1px 5px;border-radius:3px;">{{ $project->old_project_id ?? 'N/A' }}</code></span>
            <span><strong style="color:var(--color-ink-700);">LPI:</strong> {{ $project->lpi->name ?? 'N/A' }}</span>
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

    <p style="font-size:13px;color:var(--color-ink-600);margin:0 0 16px;">
        Review the rejection and decide the next action. You can either send the report back to the LPI for resubmission, or override the rejection and ask the reviewer to grade the existing report.
    </p>

    <form id="reviewRejectionForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <input type="hidden" name="report_type" value="{{ $reportType }}">

        {{-- Decision --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:8px;color:var(--color-ink-700);">
                Your Decision <span style="color:var(--color-danger);">*</span>
            </label>
            <div style="display:flex;gap:12px;">
                <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 14px;border:2px solid var(--color-ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                       onclick="document.getElementById('actionSendToLpi').checked=true;highlightDecision();"
                       onmouseover="this.style.borderColor='var(--color-warning)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--color-warning)':'var(--color-ink-200)'">
                    <input type="radio" name="action" id="actionSendToLpi" value="send_to_lpi" required
                           onchange="highlightDecision()" style="accent-color:var(--color-warning);width:16px;height:16px;">
                    <i class="fas fa-paper-plane" style="color:var(--color-warning);font-size:14px;"></i>
                    <span style="font-size:12px;font-weight:600;color:var(--color-ink-700);">Send to LPI</span>
                </label>
                <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 14px;border:2px solid var(--color-ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                       onclick="document.getElementById('actionOverride').checked=true;highlightDecision();"
                       onmouseover="this.style.borderColor='var(--color-success)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--color-success)':'var(--color-ink-200)'">
                    <input type="radio" name="action" id="actionOverride" value="override" required
                           onchange="highlightDecision()" style="accent-color:var(--color-success);width:16px;height:16px;">
                    <i class="fas fa-balance-scale" style="color:var(--color-success);font-size:14px;"></i>
                    <span style="font-size:12px;font-weight:600;color:var(--color-ink-700);">Override</span>
                </label>
            </div>
            <div id="decisionDescription" style="margin-top:8px;font-size:11px;color:var(--color-ink-400);display:none;"></div>
        </div>

        {{-- Message --}}
        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:6px;color:var(--color-ink-700);">
                Message <span style="color:var(--color-danger);">*</span>
            </label>
            <textarea name="message" rows="3" maxlength="2000" required
                style="width:100%;font-size:13px;border:1px solid var(--color-ink-200);border-radius:6px;padding:8px 10px;color:var(--color-ink-700);"
                placeholder="Enter your decision message..."></textarea>
        </div>
    </form>

    <div id="reviewRejectionError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;font-size:13px;color:var(--color-danger);margin-bottom:12px;">
        <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
        <span id="reviewRejectionErrorText"></span>
    </div>
</div>
<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-warning btn-sm" onclick="submitRejectionReview('send_to_lpi')" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-paper-plane"></i> Send to LPI
    </button>
    <button type="button" class="btn-primary btn-sm" onclick="submitRejectionReview('override')" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-balance-scale"></i> Override Rejection
    </button>
</div>

@push('scripts')
<script>
function highlightDecision() {
    var sendToLpi = document.getElementById('actionSendToLpi');
    var override = document.getElementById('actionOverride');
    var desc = document.getElementById('decisionDescription');

    var labels = document.querySelectorAll('input[name="action"]');
    labels.forEach(function(r) {
        var label = r.closest('label');
        if (r.checked) {
            var color = r.value === 'send_to_lpi' ? 'var(--color-warning)' : 'var(--color-success)';
            label.style.borderColor = color;
            label.style.background = r.value === 'send_to_lpi' ? '#fff8e1' : '#e6f4ea';
        } else {
            label.style.borderColor = 'var(--color-ink-200)';
            label.style.background = '#fff';
        }
    });

    if (sendToLpi && sendToLpi.checked) {
        desc.style.display = 'block';
        desc.textContent = 'The LPI will be notified to resubmit the report.';
    } else if (override && override.checked) {
        desc.style.display = 'block';
        desc.textContent = 'The reviewer will be notified to grade the existing report.';
    } else {
        desc.style.display = 'none';
    }
}

function submitRejectionReview(action) {
    var form = document.getElementById('reviewRejectionForm');
    var formData = new FormData(form);
    formData.append('action', action);

    var message = formData.get('message');
    if (!message || !message.trim()) {
        var errorDiv = document.getElementById('reviewRejectionError');
        var errorText = document.getElementById('reviewRejectionErrorText');
        errorText.textContent = 'Please enter a message.';
        errorDiv.style.display = 'block';
        return;
    }

    var errorDiv = document.getElementById('reviewRejectionError');
    var errorText = document.getElementById('reviewRejectionErrorText');
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
