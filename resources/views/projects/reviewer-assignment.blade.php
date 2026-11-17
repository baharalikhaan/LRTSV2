@extends('layouts.app')

@section('title', 'Reviewer Assignment - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-user-check"></i> Reviewer Assignment</h1>
        <p>Assign reviewers to projects and manage existing assignments.</p>
    </div>
</div>

@include('projects.partials.research-call-context', ['contextKey' => 'reviewer-assignment', 'listedCount' => $confProjects->count()])

{{-- Bulk-assignment progress modal: static backdrop (a stray click cannot close
     it), Cancel stops the remaining assignments, and the page reloads at the
     end so the list reflects the new assignments. --}}
@if($confProjects->count() > 0)
<div class="modal fade" id="bulkProgressModal" tabindex="-1" role="dialog" aria-hidden="true"
     data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:700px;">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.18);">
            <div class="modal-header" style="border-bottom:1px solid var(--ink-100);padding:16px 20px;">
                <h5 class="modal-title" id="bulkProgressTitle" style="font-size:15px;font-weight:700;color:var(--ink-800,#2f2b35);margin:0;">
                    Assigning reviewers…
                </h5>
            </div>
            <div class="modal-body" style="padding:16px 20px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:12.5px;color:var(--ink-600,#4b4654);">
                    <span id="bulkProgressStatus">Starting…</span>
                    <span id="bulkProgressCount">0 / 0</span>
                </div>
                <div style="height:10px;margin-top:8px;background:var(--ink-100,#eceef2);border-radius:5px;overflow:hidden;">
                    <div id="bulkProgressBar" style="height:100%;width:0%;background:var(--brand-500,#8d1b3d);transition:width .25s ease;"></div>
                </div>
                <ul id="bulkProgressLog" style="margin:12px 0 0;padding-left:18px;font-size:11.5px;line-height:1.55;color:var(--ink-600,#4b4654);max-height:170px;overflow:auto;"></ul>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--ink-100);padding:12px 20px;">
                <button type="button" class="btn-secondary btn-sm" id="bulkCancelBtn">
                    <i class="fas fa-xmark"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" onchange="onAssignCycleChange(this)">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ ($cycleId ?? '') == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }} ({{ $cycle->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Research Call:</label>
                    <select name="program_id" onchange="this.form.submit()">
                        <option value="">All Research Calls</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ ($programId ?? '') == $program->id ? 'selected' : '' }}>{{ $program->program_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="unregistered" {{ ($status ?? '') == 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                        <option value="registered" {{ ($status ?? '') == 'registered' ? 'selected' : '' }}>Registered</option>
                        <option value="claimed" {{ ($status ?? '') == 'claimed' ? 'selected' : '' }}>Claimed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Assignment:</label>
                    <select name="assignment" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="unassigned" {{ ($assignment ?? '') == 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                        <option value="assigned" {{ ($assignment ?? '') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            <script>
                // Changing the cycle resets the research-call selection; the
                // call list is narrowed to the chosen cycle server-side.
                function onAssignCycleChange(sel) {
                    var form = sel.form;
                    var prog = form.querySelector('[name="program_id"]');
                    if (prog) prog.value = '';
                    form.submit();
                }
            </script>
        </div>
    </div>

    <form action="{{ route('projects.bulk-assign') }}" method="POST" id="bulkAssignForm">
        @csrf
        @if($confProjects->count() > 0)
        @php
            $assignedCount = $confProjects->filter(fn($p) => $p->reviewers->isNotEmpty())->count();
            $unassignedCount = $confProjects->filter(fn($p) => $p->reviewers->isEmpty())->count();
        @endphp
        <div class="panel-head" style="border-bottom:1px solid var(--ink-100);background:var(--sand-50);gap:16px;">
            <span style="flex:1;min-width:0;font-size:12.5px;line-height:1.5;color:var(--ink-500);">
                <i class="fas fa-info-circle"></i> Select a reviewer per project — for an already-assigned project the selected reviewer is
                <strong>added alongside</strong> the existing one (each reviews independently). Then click
                <strong>Bulk Assign</strong>. Projects with no reviewer selected will be skipped; every newly assigned reviewer receives
                an assignment email.
                <span style="display:inline-flex;gap:6px;margin-left:10px;vertical-align:middle;">
                    <span class="pill warning" style="font-size:11px;"><span id="needReviewerCount">{{ $actionCount }}</span> need reviewer</span>
                    <span class="pill review" style="font-size:11px;">{{ $assignedCount }} Assigned</span>
                    <span class="pill warning" style="font-size:11px;">{{ $unassignedCount }} Unassigned</span>
                </span>
            </span>
            <button type="submit" class="btn btn-primary btn-sm" id="bulkAssignBtn" style="flex-shrink:0;">
                <i class="fas fa-user-check"></i> Bulk Assign Reviewers
            </button>
        </div>
        @endif
        <div class="panel-body p-0" style="overflow-x:auto;">
            @if($confProjects->isEmpty())
                <div class="empty-state py-5">
                    <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                    <h5>No Projects Found</h5>
                    <p>No projects match your current filters.</p>
                </div>
            @else
                <table class="fluent-table w-100" id="projectsTable">
                    <colgroup>
                        <col style="width:175px;">
                        <col style="width:170px;">
                        <col>
                        <col style="width:80px;">
                        <col style="width:110px;">
                        <col style="width:150px;">
                        <col style="width:175px;">
                        <col style="width:180px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Project ID</th>
                            <th>LPI</th>
                            <th>Project title</th>
                            <th>Grant</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Assigned reviewer</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($confProjects as $index => $cp)
                        @php
                            $flowStatus = $cp->currentWorkflowStatus();
                            $isRegistered = $cp->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $cp->lpi_id;
                            $statusLabel = (!$isRegistered && $flowStatus === 'imported') ? 'Unregistered' : ucfirst(str_replace('_', ' ', $flowStatus ?? '—'));
                            switch ($flowStatus) {
                                case 'imported': $statusPillClass = 'warning'; break;
                                case 'registered': $statusPillClass = 'accepted'; break;
                                case 'Assigned':
                                case 'assigned': $statusPillClass = 'review'; break;
                                case 'Claimed':
                                case 'claimed': $statusPillClass = 'accepted'; break;
                                case 'progress_added':
                                case 'progress_reviewed': $statusPillClass = 'info'; break;
                                case 'progress_rejected':
                                case 'final_rejected':
                                case 'rejected': $statusPillClass = 'danger'; break;
                                case 'final_added': $statusPillClass = 'info'; break;
                                case 'Graded':
                                case 'graded': $statusPillClass = 'accepted'; break;
                                default: $statusPillClass = 'ink'; break;
                            }
                            $assignedReviewers = $cp->reviewers;
                            $hasReviewer = $assignedReviewers->isNotEmpty();
                            $needsAction = (bool) ($needsReviewerAction[$cp->id] ?? false);
                        @endphp
                        <tr class="{{ $needsAction ? 'needs-reviewer' : '' }}" data-project-id="{{ $cp->id }}">
                            <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><a href="{{ route('projects.show', $cp->id) }}"><code>{{ $cp->old_project_id }}</code></a></td>
                            <td>
                                @if($cp->lpi)
                                    <div style="font-weight:500;">{{ $cp->lpi->name }}</div>
                                    <div title="{{ $cp->lpi->email }}" style="font-size:11px;color:var(--color-ink-400);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cp->lpi->email }}</div>
                                @else
                                    <span style="font-size:12px;color:var(--ink-400);font-style:italic;">—</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $cp->title }}" style="font-weight:500;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $cp->title }}</span>
                            </td>
                            <td><span class="pill info" style="font-size:11px;white-space:nowrap;">{{ $cp->program->grant->grant_code ?? 'N/A' }}</span></td>
                            <td><span class="pill info" style="font-size:11px;white-space:nowrap;">{{ ucfirst($cp->program->grant->category ?? 'N/A') }}</span></td>
                            <td>
                                <span class="pill {{ $statusPillClass }}">{{ $statusLabel }}</span>
                                @if($needsAction)
                                    <span class="pill warning needs-reviewer-badge" style="font-size:10px;margin-left:4px;white-space:nowrap;" title="This project still needs a reviewer assigned">
                                        Needs reviewer
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="assigned-list" style="display:flex;flex-direction:column;gap:4px;align-items:flex-start;">
                                    @foreach($assignedReviewers as $reviewer)
                                        <span class="pill review" style="font-size:11px;display:inline-flex;align-items:center;gap:6px;max-width:100%;">
                                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                {{ $reviewer->name }}@if($reviewer->pivot->claimed_at) <i class="fas fa-check" title="Claimed" style="font-size:9px;"></i> @endif
                                            </span>
                                            {{-- Un-assign this reviewer straight from his name --}}
                                            <a href="#"
                                               onclick="event.preventDefault(); event.stopPropagation(); confirmUnassignReviewer({{ $cp->id }}, {{ $reviewer->id }});"
                                               title="Un-assign {{ $reviewer->name }}"
                                               style="font-size:10px;font-weight:600;color:#a93434;text-decoration:underline;white-space:nowrap;">
                                                Un-assign
                                            </a>
                                        </span>
                                    @endforeach
                                    @unless($hasReviewer)
                                        <span class="assigned-empty" style="font-size:12px;color:var(--ink-400);font-style:italic;">Unassigned</span>
                                    @endunless
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;flex-direction:column;gap:6px;align-items:stretch;">
                                    <select name="assignments[{{ $index }}][reviewers][]" class="reviewer-select">
                                        <option value="">{{ $hasReviewer ? '— Add reviewer —' : '— Select Reviewer —' }}</option>
                                        @foreach($reviewerGroups as $pillarName => $pillarReviewers)
                                            <optgroup label="{{ $pillarName }}">
                                                @foreach($pillarReviewers as $reviewer)
                                                    @if(!$assignedReviewers->pluck('id')->contains($reviewer->id))
                                                        <option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="assignments[{{ $index }}][project_id]" value="{{ $cp->id }}">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    /* Fixed layout so the column widths declared in the DataTable config are
       honoured exactly (the title column would otherwise be squeezed by the
       wide LPI / action cells). */
    #projectsTable {
        table-layout: fixed;
        /* Keep the columns at their declared widths on smaller screens and let
           the panel scroll instead of squeezing the title column. */
        min-width: 1180px;
    }
    #projectsTable th,
    #projectsTable td {
        overflow-wrap: anywhere;
    }

    /* Rows of a research call that still need reviewer attention (the page
       lists the whole call when a research call is selected). */
    #projectsTable tbody tr.needs-reviewer > td:first-child {
        box-shadow: inset 3px 0 0 var(--brand-500, #8d1b3d);
    }
    #projectsTable tbody tr.needs-reviewer {
        background: rgba(141, 27, 61, .03);
    }

    .reviewer-select {
        width: 100%;
        min-width: 160px;
        max-width: 100%;
        padding: 5px 26px 5px 10px;
        font-size: 12px;
        font-weight: 500;
        color: var(--ink-700, #38333e);
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: var(--fluent-radius-md, 6px);
        background: #fff;
        cursor: pointer;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='%23675f6e'%3E%3Cpath d='M1 1l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }
    .reviewer-select:hover { border-color: var(--brand-300, #d3738f); }
    .reviewer-select:focus {
        border-color: var(--brand-500, #8d1b3d);
        box-shadow: 0 0 0 2px rgba(141,27,61,.15);
    }
    .reviewer-select optgroup {
        font-size: 11px;
        font-weight: 700;
        color: var(--brand-600, #7a1636);
        background: var(--sand-50, #faf7f0);
    }
    .reviewer-select optgroup option {
        font-size: 12px;
        font-weight: 500;
        color: var(--ink-700, #38333e);
        background: #fff;
    }
</style>
@endpush

@push('scripts')
@if(session('success'))
<script>
    // Flash message as a bottom-right toast (same as the rest of the app),
    // instead of an inline alert in the page.
    $(document).ready(function () {
        showToast('success', @json(session('success')));
    });
</script>
@endif
@if(session('error'))
<script>
    $(document).ready(function () {
        showToast('error', @json(session('error')));
    });
</script>
@endif
@if($confProjects->count() > 0)
<script>
    $(document).ready(function() {
        var table = $('#projectsTable').DataTable({
            dom: 'Brt<"bottom"lip>',
            order: [],
            autoWidth: false,
            pageLength: 25,
            columns: [
                { width: '175px' }, // Project ID
                { width: '170px' }, // LPI
                { width: 'auto' },  // Project title — takes the remaining space
                { width: '80px' },  // Grant
                { width: '110px' }, // Category
                { width: '150px' }, // Status
                { width: '175px' }, // Assigned reviewer (+ un-assign link)
                { width: '180px' }  // Action (reviewer dropdown)
            ],
            columnDefs: [
                { orderable: false, searchable: false, targets: [6, 7] }
            ]
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        /* ── Bulk assignment inside a progress modal ──────────────────────────
           One request per project keeps the bar and the log moving; the modal
           has a static backdrop (a stray click cannot dismiss it) and offers a
           Cancel button. When the run finishes (or is cancelled) the page is
           reloaded so the table shows the new assignments. */
        var $form    = $('#bulkAssignForm');
        var $btn     = $('#bulkAssignBtn');
        var $modal   = $('#bulkProgressModal');
        var $bar     = $('#bulkProgressBar');
        var $count   = $('#bulkProgressCount');
        var $title   = $('#bulkProgressTitle');
        var $status  = $('#bulkProgressStatus');
        var $log     = $('#bulkProgressLog');
        var csrf     = $form.find('input[name="_token"]').val();
        var endpoint = $form.attr('action');
        var cancelled = false;
        var running = false;

        function logLine(text, colour) {
            $log.append($('<li></li>').css('color', colour || '').text(text));
            $log.scrollTop($log[0].scrollHeight);
        }

        // Toast after the reload (the summary is passed through sessionStorage).
        try {
            var pending = window.sessionStorage.getItem('bulkAssignSummary');
            if (pending) {
                window.sessionStorage.removeItem('bulkAssignSummary');
                var parsed = JSON.parse(pending);
                showToast(parsed.type || 'success', parsed.message);
            }
        } catch (err) { /* sessionStorage unavailable – ignore */ }

        $('#bulkCancelBtn').on('click', function () {
            cancelled = true;
            $(this).prop('disabled', true);
            $title.text('Cancelling…');
            $status.text('Finishing the current project…');
        });

        $form.on('submit', function (e) {
            var rows = [];

            $form.find('.reviewer-select').each(function () {
                var reviewerId = $(this).val();
                if (!reviewerId) { return; }

                var $row = $(this).closest('tr');
                rows.push({
                    projectId: $row.data('project-id'),
                    code: $.trim($row.find('td:first code').text()),
                    reviewerId: reviewerId
                });
            });

            if (rows.length === 0) {
                e.preventDefault();
                showToast('warning', 'Select at least one reviewer first.');
                return;
            }

            if (running) { e.preventDefault(); return; }

            e.preventDefault();
            running = true;
            cancelled = false;

            var total = rows.length, done = 0, assigned = 0, skipped = 0, failed = 0;

            $bar.css('width', '0%');
            $count.text('0 / ' + total);
            $title.text('Assigning reviewers…');
            $status.text('Starting…');
            $log.empty();
            $btn.prop('disabled', true);
            $('#bulkCancelBtn').prop('disabled', false).show();

            $modal.modal({ backdrop: 'static', keyboard: false }).modal('show');

            function finish() {
                var wasCancelled = cancelled;
                var summary = assigned + ' reviewer' + (assigned === 1 ? '' : 's') + ' assigned';
                if (skipped > 0) { summary += ', ' + skipped + ' skipped'; }
                if (failed > 0) { summary += ', ' + failed + ' failed'; }
                if (wasCancelled) { summary += ' (cancelled after ' + done + ' of ' + total + ')'; }

                $title.text(wasCancelled ? 'Bulk assignment cancelled' : 'Bulk assignment finished');
                $status.text(summary);
                $bar.css('width', '100%');

                try {
                    window.sessionStorage.setItem('bulkAssignSummary', JSON.stringify({
                        type: failed > 0 ? 'warning' : (assigned > 0 ? 'success' : 'warning'),
                        message: summary + '.'
                    }));
                } catch (err) { /* ignore */ }

                // Give the user a moment to read the final state, then reload so
                // the table reflects what was written.
                setTimeout(function () { window.location.reload(); }, wasCancelled ? 700 : 1000);
            }

            function step(index) {
                if (cancelled || index >= total) { finish(); return; }

                var row = rows[index];
                $status.text('Assigning ' + row.code + '…');

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        assignments: [{ project_id: row.projectId, reviewers: [row.reviewerId] }]
                    })
                })
                .then(function (response) {
                    return response.json().then(function (body) { return { ok: response.ok, body: body }; });
                })
                .then(function (result) {
                    if (!result.ok || !result.body.success) {
                        throw new Error((result.body && (result.body.message || result.body.error)) || 'Request failed');
                    }

                    var list = (result.body.assigned || {})[row.projectId] || [];
                    if (list.length === 0) {
                        skipped++;
                        logLine('⚠ ' + row.code + ' — no reviewer added (already assigned or previously rejected)', '#8a6d1f');
                    } else {
                        list.forEach(function (reviewer) {
                            logLine('✓ ' + row.code + ' — ' + (reviewer.name || 'reviewer assigned'), '#1f8a5f');
                        });
                        assigned += list.length;
                    }
                })
                .catch(function (error) {
                    failed++;
                    logLine('✗ ' + row.code + ' — ' + error.message, '#b3261e');
                })
                .then(function () {
                    done++;
                    $bar.css('width', Math.round((done / total) * 100) + '%');
                    $count.text(done + ' / ' + total);
                    step(index + 1);
                });
            }

            step(0);
        });
    });
</script>
@endif
@endpush
