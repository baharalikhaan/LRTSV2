<div class="modal-header" style="border:none;padding:20px 24px 0;">
    <h5 class="modal-title" style="font-weight:600;font-size:18px;">
        <i class="fas fa-check-circle" style="color:var(--color-brand-500);margin-right:8px;"></i>
        Accept / Reject Proposal
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body" style="padding:16px 24px 20px;">
    @php
        $reviewerPivot = DB::table('projects_reviewers')
            ->where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->first();
        $claimLabel = 'Claim';
        $proposalStatus = $reviewerPivot ? $reviewerPivot->proposalstatus ?? null : null;
        $statusDate = $reviewerPivot ? $reviewerPivot->statusdate ?? null : null;
    @endphp

    {{-- Project Info Card --}}
    <div style="background:var(--color-sand-50);border:1px solid var(--color-ink-100);border-radius:8px;padding:14px 16px;margin-bottom:16px;display:flex;flex-direction:column;gap:6px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:13px;font-weight:600;color:var(--color-ink-800);">{{ $project->title }}</span>
            <span style="background:var(--color-gold-500);color:#fff;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;white-space:nowrap;">{{ $claimLabel }}</span>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:12px;color:var(--color-ink-500);">
            <span><strong style="color:var(--color-ink-700);">Project ID:</strong> <code style="font-size:11px;background:var(--color-ink-50);padding:1px 5px;border-radius:3px;">{{ $project->old_project_id ?? 'N/A' }}</code></span>
            <span><strong style="color:var(--color-ink-700);">Research Call:</strong> {{ $project->program->program_title ?? 'N/A' }}</span>
            <span><strong style="color:var(--color-ink-700);">Role:</strong> {{ $reviewerPivot ? $reviewerPivot->role : 'Reviewer' }}</span>
        </div>
    </div>

    @if($proposalStatus)
        <div style="border-radius:8px;padding:14px 16px;font-size:13px;display:flex;align-items:center;gap:10px;
            @if($proposalStatus === 'accepted') background:#e6f4ea;border:1px solid #a8e6b8;color:var(--color-success);
            @else background:#fef2f2;border:1px solid #fecaca;color:var(--color-danger); @endif">
            <i class="fas {{ $proposalStatus === 'accepted' ? 'fa-check-circle' : 'fa-times-circle' }}" style="font-size:16px;"></i>
            <div>
                <strong>You have already {{ $proposalStatus }}</strong> this proposal
                @if($statusDate) on <strong>{{ \Carbon\Carbon::parse($statusDate)->format('M d, Y') }}</strong>.@endif
            </div>
        </div>
    @else
        <p style="font-size:13px;color:var(--color-ink-500);margin:0 0 16px;">Review the project proposal and submit your decision below.</p>

        <form id="proposalDecisionForm">
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="hidden" name="r_id" value="{{ $reviewerPivot->id ?? '' }}">

            {{-- Decision --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:8px;color:var(--color-ink-700);">
                    Decision <span style="color:var(--color-danger);">*</span>
                </label>
                <div style="display:flex;gap:12px;">
                    <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 14px;border:2px solid var(--color-ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onclick="document.getElementById('acceptYes').checked=true;"
                           onmouseover="this.style.borderColor='var(--color-success)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--color-success)':'var(--color-ink-200)'">
                        <input type="radio" name="accept" id="acceptYes" value="accepted" required
                               onchange="highlightDecision()" style="accent-color:var(--color-success);width:16px;height:16px;">
                        <i class="fas fa-check-circle" style="color:var(--color-success);font-size:14px;"></i>
                        <span style="font-size:13px;font-weight:600;color:var(--color-ink-700);">Accept</span>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 14px;border:2px solid var(--color-ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onclick="document.getElementById('acceptNo').checked=true;"
                           onmouseover="this.style.borderColor='var(--color-danger)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--color-danger)':'var(--color-ink-200)'">
                        <input type="radio" name="accept" id="acceptNo" value="rejected" required
                               onchange="highlightDecision()" style="accent-color:var(--color-danger);width:16px;height:16px;">
                        <i class="fas fa-times-circle" style="color:var(--color-danger);font-size:14px;"></i>
                        <span style="font-size:13px;font-weight:600;color:var(--color-ink-700);">Reject</span>
                    </label>
                </div>
                <div id="decisionError" style="display:none;color:var(--color-danger);font-size:12px;margin-top:4px;"></div>
            </div>

            <div id="proposalError" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;font-size:13px;color:var(--color-danger);margin-bottom:12px;">
                <i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>
                <span id="proposalErrorText"></span>
            </div>
        </form>
    @endif
</div>
@if(!$proposalStatus)
<div class="modal-footer" style="border:none;padding:0 24px 20px;display:flex;justify-content:flex-end;gap:8px;">
    <button type="button" class="btn-secondary btn-sm" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancel
    </button>
    <button type="button" class="btn-primary btn-sm" id="submitDecisionBtn" onclick="submitProposalDecision()" style="display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-check"></i> Submit Decision
    </button>
</div>
@push('scripts')
<script>
function highlightDecision() {
    document.querySelectorAll('input[name="accept"]').forEach(function(r) {
        var label = r.closest('label');
        if (r.checked) {
            var color = r.value === 'accepted' ? 'var(--color-success)' : 'var(--color-danger)';
            label.style.borderColor = color;
            label.style.background = r.value === 'accepted' ? '#e6f4ea' : '#fef2f2';
        } else {
            label.style.borderColor = 'var(--color-ink-200)';
            label.style.background = '#fff';
        }
    });
}
</script>
@endpush
@endif
