<div class="modal-header" style="border-bottom:1px solid var(--color-ink-100);padding:16px 20px;">
    <h5 class="modal-title" style="font-weight:600;font-size:16px;">
        <i class="fas fa-check-circle" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Review Proposal
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 20px;">
    @php
        $reviewerPivot = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->first();
        $claimLabel = 'Claim';
        $proposalStatus = $reviewerPivot ? $reviewerPivot->proposalstatus ?? null : null;
        $statusDate = $reviewerPivot ? $reviewerPivot->statusdate ?? null : null;
    @endphp

    {{-- Project Info --}}
    <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:12px 14px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:600;color:var(--color-ink-800);">{{ $project->title }}</span>
            <span style="background:var(--color-gold-500);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;">{{ $claimLabel }}</span>
        </div>
        <div style="display:flex;gap:16px;font-size:11px;color:var(--color-ink-500);">
            <span><strong>ID:</strong> {{ $project->old_project_id ?? 'N/A' }}</span>
            <span><strong>Call:</strong> {{ $project->program->program_title ?? 'N/A' }}</span>
        </div>
    </div>

    @if($proposalStatus)
        <div style="border-radius:8px;padding:12px 14px;font-size:13px;display:flex;align-items:center;gap:10px;
            @if($proposalStatus === 'accepted') background:#e6f4ea;border:1px solid #a8e6b8;color:var(--color-success);
            @else background:#fef2f2;border:1px solid #fecaca;color:var(--color-danger); @endif">
            <i class="fas {{ $proposalStatus === 'accepted' ? 'fa-check-circle' : 'fa-times-circle' }}" style="font-size:16px;"></i>
            <div>
                <strong>You have already {{ $proposalStatus }}</strong>
                @if($statusDate) on <strong>{{ \Carbon\Carbon::parse($statusDate)->format('M d, Y') }}</strong>.@endif
            </div>
        </div>
    @else
        <p style="font-size:13px;color:var(--color-ink-500);margin:0 0 14px;">Select your decision below:</p>

        <form id="proposalDecisionForm">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="hidden" name="r_id" value="{{ $reviewerPivot->id ?? '' }}">
            <input type="hidden" name="accept" id="decisionValue" value="">

            {{-- Decision Options --}}
            <div style="display:flex;gap:12px;margin-bottom:14px;">
                {{-- Accept --}}
                <div id="acceptBtn" onclick="selectDecision('accepted')"
                     style="flex:1;cursor:pointer;padding:14px 12px;border:2px solid var(--color-ink-200);border-radius:8px;text-align:center;transition:all .15s;background:#fff;">
                    <i class="fas fa-check-circle" style="font-size:24px;color:var(--color-success);display:block;margin-bottom:6px;"></i>
                    <span style="font-size:13px;font-weight:600;color:var(--color-ink-700);">Accept</span>
                </div>

                {{-- Reject --}}
                <div id="rejectBtn" onclick="selectDecision('rejected')"
                     style="flex:1;cursor:pointer;padding:14px 12px;border:2px solid var(--color-ink-200);border-radius:8px;text-align:center;transition:all .15s;background:#fff;">
                    <i class="fas fa-times-circle" style="font-size:24px;color:var(--color-danger);display:block;margin-bottom:6px;"></i>
                    <span style="font-size:13px;font-weight:600;color:var(--color-ink-700);">Reject</span>
                </div>
            </div>
            <div id="decisionError" style="display:none;color:var(--color-danger);font-size:12px;margin-bottom:10px;"></div>

            {{-- Reason for rejection --}}
            <div id="rejectReasonWrap" style="display:none;margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:6px;color:var(--color-ink-700);">
                    Reason <span style="color:var(--color-ink-400);font-weight:400;">(optional)</span>
                </label>
                <textarea name="reject_reason" rows="2" maxlength="2000"
                    style="width:100%;font-size:13px;border:1px solid var(--color-ink-200);border-radius:6px;padding:8px 10px;color:var(--color-ink-700);resize:none;"
                    placeholder="Briefly explain why..."></textarea>
            </div>

            <div id="proposalError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;font-size:13px;color:var(--color-danger);margin-bottom:12px;">
                <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
                <span id="proposalErrorText"></span>
            </div>
        </form>
    @endif
</div>
@if(!$proposalStatus)
<div class="modal-footer" style="border-top:1px solid var(--color-ink-100);padding:12px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
    <button type="button" class="btn-primary btn-sm" id="submitDecisionBtn" onclick="submitProposalDecision()">
        <i class="fas fa-check"></i> Submit
    </button>
</div>
<script>
var _selectedDecision = null;

function selectDecision(value) {
    _selectedDecision = value;
    document.getElementById('decisionValue').value = value;
    document.getElementById('decisionError').style.display = 'none';

    var acceptBtn = document.getElementById('acceptBtn');
    var rejectBtn = document.getElementById('rejectBtn');

    // Reset both
    acceptBtn.style.borderColor = 'var(--color-ink-200)';
    acceptBtn.style.background = '#fff';
    rejectBtn.style.borderColor = 'var(--color-ink-200)';
    rejectBtn.style.background = '#fff';

    // Highlight selected
    if (value === 'accepted') {
        acceptBtn.style.borderColor = 'var(--color-success)';
        acceptBtn.style.background = '#e6f4ea';
    } else {
        rejectBtn.style.borderColor = 'var(--color-danger)';
        rejectBtn.style.background = '#fef2f2';
    }

    // Show/hide reject reason
    document.getElementById('rejectReasonWrap').style.display = value === 'rejected' ? 'block' : 'none';
}
</script>
@endif
