@extends('layouts.app')

@section('title', 'Available Projects - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-project-diagram"></i> All Projects</h1>
        <p>Browse projects — register unregistered ones or view your registered projects.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" onchange="this.form.submit()">
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
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body p-0">
        @if($confProjects->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-check-circle" style="opacity:0.3;"></i>
                <h5>No Projects Found</h5>
                <p>No projects match your current filters.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="projectsTable">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Title</th>
                        <th>Research Call</th>
                        <th>Grant</th>
                        <th>Category</th>
                        <th>Budget (QAR)</th>
                        <th>Status</th>
                        <th>Next Step</th>
                        <th class="text-center" style="min-width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($confProjects as $cp)
                    @php
                        $isRegistered = $cp->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $cp->lpi_id;
                        $isOwned = $isRegistered && $cp->lpi_id === $user->id;
                        $flowStatus = $cp->currentWorkflowStatus();
                        $isClaimedByOther = $isRegistered && !$isOwned && $flowStatus === \App\Models\Project::STATUS_CLAIMED;
                        $activeRole = $user->activeRole();
                        $canRegister = $activeRole === 'LPI';
                        $programInactive = $cp->program && !$cp->programIsActive();
                        $availActions = $cp->availableActions($user);
                        $hasProgressExtended = $cp->hasStatus(\App\Models\Project::STATUS_PROGRESS_EXTENDED) ?? false;

                        // Determine "Next Step" info (what happens next & who is responsible)
                        $nextStepLabel = '';
                        $nextStepIcon = 'fa-arrow-right';
                        $nextStepColor = 'var(--color-ink-500)';
                        if ($programInactive) {
                            $nextStepLabel = 'Research call is inactive';
                            $nextStepIcon = 'fa-lock';
                            $nextStepColor = 'var(--color-danger)';
                        } else {
                            // Check if extended progress is enabled
                            $isExtended = $cp->is_extended ?? false;

                            switch ($flowStatus) {
                                case 'imported':
                                    $nextStepLabel = 'Register by LPI / Admin';
                                    $nextStepIcon = 'fa-user-plus';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'registered':
                                    $nextStepLabel = 'Assign Reviewer by Admin + Add Progress by LPI';
                                    $nextStepIcon = 'fa-user-tag';
                                    $nextStepColor = 'var(--color-gold-500)';
                                    break;
                                case 'Assigned':
                                case 'assigned':
                                    $nextStepLabel = 'Reviewer has to Accept/Reject proposal / Add Progress by LPI';
                                    $nextStepIcon = 'fa-check-circle';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'Claimed':
                                case 'claimed':
                                    $nextStepLabel = 'Review Progress by Reviewer';
                                    $nextStepIcon = 'fa-clipboard-check';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress':
                                case 'progress_add':
                                case 'progress_added':
                                    $hasReviewer = DB::table('projects_reviewers')->where('project_id', $cp->id)->exists();
                                    $nextStepLabel = $hasReviewer ? 'Review Progress by Reviewer' : 'Assign Reviewer by Admin';
                                    $nextStepIcon = $hasReviewer ? 'fa-clipboard-check' : 'fa-user-tag';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    // Debug: uncomment to see values
                                    // dd($flowStatus, $hasReviewer, $nextStepLabel);
                                    break;
                                case 'progress_reviewed':
                                    $nextStepLabel = $isExtended ? 'Add Extended Progress by LPI' : 'Add Final Report by LPI';
                                    $nextStepIcon = $isExtended ? 'fa-sync-alt' : 'fa-paper-plane';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_rejected':
                                    $nextStepLabel = 'Resubmit Progress by LPI';
                                    $nextStepIcon = 'fa-chart-line';
                                    $nextStepColor = 'var(--color-danger)';
                                    break;
                                case 'rejected':
                                case 'proposal_rejected':
                                    $nextStepLabel = 'Assign Reviewer by Admin + Add Progress by LPI';
                                    $nextStepIcon = 'fa-user-tag';
                                    $nextStepColor = 'var(--color-gold-500)';
                                    break;
                                case 'progress_extended':
                                    $nextStepLabel = 'Review Extended Progress by Reviewer';
                                    $nextStepIcon = 'fa-clipboard-check';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_ext_reviewed':
                                    $nextStepLabel = 'Add Final Report by LPI';
                                    $nextStepIcon = 'fa-paper-plane';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_ext_rejected':
                                    $nextStepLabel = 'Resubmit Extended Progress by LPI';
                                    $nextStepIcon = 'fa-sync-alt';
                                    $nextStepColor = 'var(--color-danger)';
                                    break;
                                case 'final_added':
                                    $nextStepLabel = 'Grade Final by Reviewer';
                                    $nextStepIcon = 'fa-flag-checkered';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case \App\Models\Project::STATUS_CLAIM1:
                                    $nextStepLabel = 'Reviewer-2 has to Accept/Reject Proposal';
                                    $nextStepIcon = 'fa-check-circle';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case \App\Models\Project::STATUS_CLAIM2:
                                    $nextStepLabel = 'Reviewer-1 has to Accept/Reject Proposal';
                                    $nextStepIcon = 'fa-check-circle';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case \App\Models\Project::STATUS_CLAIMED:
                                    $nextStepLabel = 'Reviewers have to grade the progress report';
                                    $nextStepIcon = 'fa-star';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case \App\Models\Project::STATUS_GRADE1:
                                    $nextStepLabel = 'Reviewer-2 has to grade the project';
                                    $nextStepIcon = 'fa-star';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case \App\Models\Project::STATUS_GRADE2:
                                    $nextStepLabel = 'Reviewer-1 has to grade the project';
                                    $nextStepIcon = 'fa-star';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'graded':
                                case 'report':
                                case \App\Models\Project::STATUS_GRADED:
                                    $nextStepLabel = 'All Done';
                                    $nextStepIcon = 'fa-check-double';
                                    $nextStepColor = 'var(--color-success)';
                                    break;
                                default:
                                    $nextStepLabel = '—';
                                    $nextStepIcon = 'fa-minus';
                                    $nextStepColor = 'var(--color-ink-300)';
                            }
                        }
                    @endphp
                    <tr>
                        <td><a href="{{ route('projects.show', $cp->id) }}"><code>{{ $cp->old_project_id }}</code></a></td>
                        <td><span style="font-weight:500;">{{ $cp->title }}</span></td>
                        <td>{{ $cp->program->program_title ?? '—' }}</td>
                        <td><span class="pill info" style="font-size:11px;">{{ $cp->program->grant->grant_code ?? 'N/A' }}</span></td>
                        <td><span class="pill info" style="font-size:11px;">{{ ucfirst($cp->program->grant->category ?? 'N/A') }}</span></td>
                        <td>{{ $cp->requested_budget_qar ? number_format($cp->requested_budget_qar, 2) : '—' }}</td>
                        <td>
                            @php
                                $flowStatus = $cp->currentWorkflowStatus();
                                switch($flowStatus) {
                                    case 'imported': $statusPillClass = 'warning'; break;
                                    case 'registered': $statusPillClass = 'accepted'; break;
                                    case 'assigned': $statusPillClass = 'review'; break;
                                    case 'claimed': $statusPillClass = 'accepted'; break;
                                    case 'progress':
                                    case 'progress_add':
                                    case 'progress_added':
                                    case 'progress_reviewed': $statusPillClass = 'info'; break;
                                    case 'progress_rejected':
                                    case 'rejected': $statusPillClass = 'danger'; break;
                                    case 'final_added': $statusPillClass = 'info'; break;
                                    case 'accepted': $statusPillClass = 'accepted'; break;
                                    case 'reviewed': $statusPillClass = 'info'; break;
                                    case 'graded': $statusPillClass = 'accepted'; break;
                                    case 'report': $statusPillClass = 'info'; break;
                                    default: $statusPillClass = 'ink'; break;
                                }
                                $statusLabel = $isClaimedByOther ? 'Claimed' : ($isOwned ? ucfirst($flowStatus) : ($flowStatus === 'imported' && !$isRegistered ? 'Unregistered' : ucfirst($flowStatus)));
                            @endphp
                            <span class="pill {{ $statusPillClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td style="white-space:nowrap;">
                            <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;color:{{ $nextStepColor }};font-weight:400;font-style:italic;">
                                <i class="fas {{ $nextStepIcon }}" style="font-size:11px;"></i>
                                {{ $nextStepLabel }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="display:flex;gap:4px;justify-content:center;align-items:center;flex-wrap:wrap;">
                                @if(!$isRegistered && $canRegister)
                                    @if($programInactive)
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-danger);"></i> Inactive Research Call</span>
                                    @else
                                        <a href="{{ route('projects.register-wizard', $cp->id) }}" class="btn-primary btn-sm" title="Register" style="display:inline-flex;align-items:center;justify-content:center;gap:5px;text-decoration:none;">
                                            <i class="fas fa-plus"></i> Register
                                        </a>
                                    @endif
                                @elseif(count($availActions) > 0)
                                    {{-- Workflow action buttons for LPIs, reviewers, and admins --}}
                                    @include('projects.partials.workflow-actions', ['project' => $cp, 'actions' => $availActions])
                                @elseif($user->isReviewer() && $flowStatus === 'Claimed' && $isRegistered && DB::table('projects_reviewers')->where('project_id', $cp->id)->where('user_id', $user->id)->where('proposalstatus', 'accepted')->exists())
                                    <a href="{{ route('projects.grading', $cp->id) }}" class="btn-primary btn-sm" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                                        <i class="fas fa-star" style="font-size:11px;"></i> Grade Project
                                    </a>
                                @elseif(!$isRegistered && !$canRegister)
                                    @if($programInactive)
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-danger);"></i> Inactive Research Call</span>
                                    @else
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-ink-400);"></i> No Access</span>
                                    @endif
                                @else
                                    @if($programInactive)
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-danger);"></i> Inactive Research Call</span>
                                    @elseif($user->isReviewer() && DB::table('projects_reviewers')->where('project_id', $cp->id)->where('user_id', $user->id)->exists())
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-ink-400);"></i> Unavailable</span>
                                    @else
                                        <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-ink-400);"></i> Unavailable</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($confProjects->count() > 0)
<script>
    $(document).ready(function() {
        var table = $('#projectsTable').DataTable({
            dom: 'rt<"bottom"lip>',
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [7, 8] },
                { searchable: false, targets: [7, 8] }
            ]
        });

        // Connect custom search input to DataTables search
        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });

    // ─── Registration Wizard ────────────────────────────────────────────
    function openRegisterWizard(projectId) {
        // Remove any leftover modal from previous opens
        $('#registerWizardModal').remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');

        // Show loading state with proper modal structure (no .modal-body padding)
        const modal = $('<div class="modal fade" id="registerWizardModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">'
            + '<div class="modal-dialog modal-dialog-centered" role="document" style="max-width:820px;">'
            + '<div class="modal-content" style="border:none;border-radius:8px;overflow:hidden;">'
            + '<div class="text-center py-5">'
            + '<i class="fas fa-spinner fa-spin" style="font-size:32px;color:var(--color-brand-500);"></i>'
            + '<p class="mt-3">Loading registration wizard...</p>'
            + '</div></div></div></div>');

        $('body').append(modal);
        modal.modal('show');

        // Load wizard content via AJAX
        $.get('/wizard/load/' + projectId, function(res) {
            if (res.html) {
                modal.find('.modal-content').html(res.html);
                // If modal lost positioning during content swap, re-center
                modal.find('.modal-dialog').css('margin', '1.75rem auto');
            } else if (res.error) {
                modal.find('.modal-content').html('<div class="p-4 text-center"><div class="alert alert-danger mb-0">' + res.error + '</div></div>');
            }
        }).fail(function(xhr) {
            const err = xhr.responseJSON;
            modal.find('.modal-content').html(
                '<div class="p-4 text-center"><div class="alert alert-danger mb-0">'
                + (err?.error || 'Failed to load wizard. Project may already be registered.')
                + '</div></div>'
            );
        });
    }

    </script>
@endif
@endpush
