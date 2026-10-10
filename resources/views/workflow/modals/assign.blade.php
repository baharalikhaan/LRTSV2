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

            // Reviewers who previously rejected this proposal — excluded from the
            // dropdown so the admin doesn't re-assign the same reviewer.
            $previousRejectors = $project->previousRejectors();
            $rejectorIds = $previousRejectors->pluck('user_id')->toArray();

            // Reviewers grouped by their research pillar (each reviewer appears
            // once — under their first pillar, or "Unassigned" when they have
            // none), mirroring the Reviewer Assignment page.
            $reviewers = \App\Models\User::with('pillars')
                ->whereIn('type', ['Reviewer', 'LPI+Reviewer', 'Admin+LPI+Reviewer'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $reviewerGroups = [];
            foreach ($reviewers as $reviewer) {
                $pillarNames = $reviewer->getRelation('pillars')->pluck('pillar')->filter()->values()->all();
                $group = !empty($pillarNames) ? $pillarNames[0] : 'Unassigned';
                $reviewerGroups[$group][] = $reviewer;
            }
            ksort($reviewerGroups);
        @endphp

        @if($previousRejectors->isNotEmpty())
            <div style="padding:8px 12px;border-radius:6px;background:#fef2f2;border:1px solid #fecaca;font-size:12px;color:#b91c1c;margin-bottom:12px;">
                <i class="fas fa-ban" style="margin-right:4px;"></i>
                <strong>Previously rejected by:</strong>
                {{ $previousRejectors->map(function ($r) { return (optional($r->user)->name ?? 'Unknown') . ' (' . $r->created_at->format('M d, Y') . ')'; })->implode(', ') }}
                — excluded from the list below.
            </div>
        @endif

        {{-- Single Reviewer (custom themed dropdown) --}}
        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:600;color:var(--color-ink-700);display:block;margin-bottom:5px;">
                Reviewer
            </label>

            @php
                $currentReviewerLabel = $currentReviewer
                    ? $currentReviewer->name . ' (' . $currentReviewer->email . ')'
                    : '— Select Reviewer —';
            @endphp

            <div class="rv-dropdown" id="reviewerDropdown">
                <button type="button" class="rv-dd-toggle" onclick="toggleReviewerDd(event)">
                    <span id="reviewerDdLabel">{{ $currentReviewerLabel }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                {{-- Kept as the form value; submitAssignment() reads #reviewer_1.value --}}
                <input type="hidden" name="reviewer_ids[]" id="reviewer_1" value="{{ $currentReviewerId }}">

                <div class="rv-dd-menu" id="reviewerDdMenu" style="display:none;">
                    @foreach($reviewerGroups as $pillarName => $groupReviewers)
                        <div class="rv-dd-group">{{ $pillarName }}</div>
                        @foreach($groupReviewers as $reviewer)
                            @if(in_array($reviewer->id, $rejectorIds))
                                <div class="rv-dd-item rv-dd-item--disabled">
                                    {{ $reviewer->name }} ({{ $reviewer->email }}) — previously rejected
                                </div>
                            @else
                                <div class="rv-dd-item {{ $reviewer->id == $currentReviewerId ? 'is-selected' : '' }}"
                                     data-id="{{ $reviewer->id }}"
                                     data-label="{{ $reviewer->name }} ({{ $reviewer->email }})"
                                     onclick="pickReviewerDd(this)">
                                    {{ $reviewer->name }} ({{ $reviewer->email }})
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <style>
            .rv-dropdown { position: relative; }
            .rv-dd-toggle {
                width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 8px;
                padding: 8px 10px; border: 1px solid var(--ink-200, #d8d6dc); border-radius: 6px;
                background: #fff; font-size: 13px; color: var(--ink-800, #241f2a); cursor: pointer;
                font-family: inherit; text-align: left; transition: border-color .15s, box-shadow .15s;
            }
            .rv-dd-toggle:hover { border-color: var(--brand-300, #d3738f); }
            .rv-dd-toggle:focus { outline: none; border-color: var(--brand-400, #b8496b); box-shadow: 0 0 0 2px var(--brand-100, #f3d2da); }
            .rv-dd-toggle i { color: var(--ink-400); font-size: 11px; }

            .rv-dd-menu {
                position: fixed; z-index: 2000; margin-top: 0;
                max-height: 320px; overflow-y: auto; background: #fff;
                border: 1px solid var(--ink-200, #d8d6dc); border-radius: 8px;
                box-shadow: 0 8px 24px rgba(0,0,0,.15);
            }
            /* Pillar headers — maroon so they stand out from reviewer entries */
            .rv-dd-group {
                position: sticky; top: 0; z-index: 1;
                padding: 7px 12px; font-size: 10.5px; font-weight: 700;
                text-transform: uppercase; letter-spacing: .05em;
                color: var(--brand-600, #7a1636);
                background: var(--brand-50, #fbeef1);
                border-top: 1px solid var(--brand-100, #f3d2da);
            }
            .rv-dd-group:first-child { border-top: none; }

            .rv-dd-item { padding: 8px 14px; font-size: 13px; color: var(--ink-700, #38333e); cursor: pointer; }
            .rv-dd-item:hover { background: var(--brand-500, #8d1b3d); color: #fff; }
            .rv-dd-item.is-selected { background: var(--brand-50, #fbeef1); color: var(--brand-600, #7a1636); font-weight: 600; }
            .rv-dd-item.is-selected:hover { background: var(--brand-500, #8d1b3d); color: #fff; }
            .rv-dd-item--disabled { color: var(--ink-400, #8b8592); cursor: not-allowed; }
            .rv-dd-item--disabled:hover { background: transparent; color: var(--ink-400, #8b8592); }
        </style>

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

