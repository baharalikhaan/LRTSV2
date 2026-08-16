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
                        <th style="min-width:180px;">Project ID</th>
                        <th>Title</th>
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


                        // Determine "Next Step" info (what happens next & who is responsible)
                        $nextStepLabel = '';
                        $nextStepIcon = 'fa-arrow-right';
                        $nextStepColor = 'var(--color-ink-500)';
                        if ($programInactive) {
                            $nextStepLabel = 'Research call is inactive';
                            $nextStepIcon = 'fa-lock';
                            $nextStepColor = 'var(--color-danger)';
                        } else {
                            switch ($flowStatus) {
                                case 'imported':
                                    $nextStepLabel = 'Register by LPI';
                                    $nextStepIcon = 'fa-user-plus';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'registered':
                                    $nextStepLabel = 'Assign Reviewer';
                                    $nextStepIcon = 'fa-user-tag';
                                    $nextStepColor = 'var(--color-gold-500)';
                                    break;
                                case 'Assigned':
                                case 'assigned':
                                    $nextStepLabel = 'Accept/Reject by Reviewer';
                                    $nextStepIcon = 'fa-check-circle';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'Claimed':
                                case 'claimed':
                                    $nextStepLabel = 'Grade by Reviewer';
                                    $nextStepIcon = 'fa-clipboard-check';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_added':
                                    $nextStepLabel = 'Review by Reviewer';
                                    $nextStepIcon = 'fa-clipboard-check';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_reviewed':
                                    $nextStepLabel = 'Add Final Report by LPI';
                                    $nextStepIcon = 'fa-paper-plane';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'progress_rejected':
                                    $nextStepLabel = 'Admin Review Pending';
                                    $nextStepIcon = 'fa-balance-scale';
                                    $nextStepColor = 'var(--color-danger)';
                                    break;
                                case 'final_added':
                                    $nextStepLabel = 'Grade by Reviewer';
                                    $nextStepIcon = 'fa-flag-checkered';
                                    $nextStepColor = 'var(--color-brand-500)';
                                    break;
                                case 'final_rejected':
                                    $nextStepLabel = 'Admin Review Pending';
                                    $nextStepIcon = 'fa-balance-scale';
                                    $nextStepColor = 'var(--color-danger)';
                                    break;
                                case 'graded':
                                case 'Graded':
                                    $nextStepLabel = 'Completed';
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
                        <td><span class="pill info" style="font-size:11px;">{{ $cp->program->grant->grant_code ?? 'N/A' }}</span></td>
                        <td><span class="pill info" style="font-size:11px;">{{ ucfirst($cp->program->grant->category ?? 'N/A') }}</span></td>
                        <td>
                            @php
                                $budget = \App\Models\ProjectBudget::where('project_id', $cp->id)->first();
                            @endphp
                            @if($budget && $budget->budget_amount > 0)
                                <div style="font-size:12px;">
                                    <span style="font-weight:600;color:var(--color-ink-700);">{{ number_format($budget->actual_exp_amount, 0) }}</span>
                                    <span style="color:var(--color-ink-400);">/</span>
                                    <span style="color:var(--color-ink-500);">{{ number_format($budget->budget_amount, 0) }}</span>
                                    <span style="color:var(--color-ink-400);font-size:10px;">QAR</span>
                                </div>
                                @php
                                    $pct = $budget->utilizationPercent();
                                    $color = $pct < 50 ? '#f59e0b' : ($pct <= 90 ? '#10b981' : '#3b82f6');
                                @endphp
                                <div style="margin-top:3px;height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;">
                                    <div style="width:{{ min($pct, 100) }}%;height:100%;background:{{ $color }};border-radius:2px;"></div>
                                </div>
                                <div style="font-size:10px;color:var(--color-ink-400);margin-top:2px;">{{ $pct }}% utilized</div>
                            @elseif($cp->requested_budget_qar)
                                <span style="font-size:12px;color:var(--color-ink-500);">{{ number_format($cp->requested_budget_qar, 0) }} QAR</span>
                                <div style="font-size:10px;color:var(--color-ink-400);">Requested</div>
                            @else
                                <span style="color:var(--color-ink-400);">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $flowStatus = $cp->currentWorkflowStatus();
                                switch($flowStatus) {
                                    case 'imported': $statusPillClass = 'warning'; break;
                                    case 'registered': $statusPillClass = 'accepted'; break;
                                    case 'assigned':
                                    case 'Assigned': $statusPillClass = 'review'; break;
                                    case 'claimed':
                                    case 'Claimed': $statusPillClass = 'accepted'; break;
                                    case 'progress':
                                    case 'progress_add':
                                    case 'progress_added':
                                    case 'progress_reviewed': $statusPillClass = 'info'; break;
                                    case 'progress_rejected':
                                    case 'final_rejected':
                                    case 'rejected': $statusPillClass = 'danger'; break;
                                    case 'progress_rejection_reviewed':
                                    case 'final_rejection_reviewed': $statusPillClass = 'warning'; break;
                                    case 'final_added': $statusPillClass = 'info'; break;
                                    case 'accepted': $statusPillClass = 'accepted'; break;
                                    case 'reviewed': $statusPillClass = 'info'; break;
                                    case 'graded':
                                    case 'Graded': $statusPillClass = 'accepted'; break;
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
                            @if(!$isRegistered && !$canRegister)
                                <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock"></i> No Access</span>
                            @else
                                <div class="dropdown" style="position:relative;display:inline-block;">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleProjectMenu(this)" style="font-size:11px;">Actions ▾</button>
                                    <div class="action-menu" style="display:none;position:fixed;z-index:10000;background:#fff;border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,.15);min-width:180px;padding:4px 0;">
                                        @if(!$isRegistered && $canRegister)
                                        <a class="dropdown-item" href="{{ route('projects.register-wizard', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                            <i class="fas fa-plus" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Register
                                        </a>
                                        @else
                                        <a class="dropdown-item" href="{{ route('projects.show', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                            <i class="fas fa-eye" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> View Details
                                        </a>
                                        @if(!$cp->proposal_filename && $user->isAdmin())
                                        <a class="dropdown-item" href="#" onclick="closeProjectMenus();openSingleUploadModal({{ $cp->id }}, '{{ addslashes($cp->old_project_id) }}')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                            <i class="fas fa-file-pdf" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Upload Proposal
                                        </a>
                                        @elseif($cp->proposal_filename && $user->isAdmin())
                                        <a class="dropdown-item" href="#" onclick="closeProjectMenus();openSingleUploadModal({{ $cp->id }}, '{{ addslashes($cp->old_project_id) }}')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                            <i class="fas fa-sync-alt" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Replace Proposal
                                        </a>
                                        @endif
                                        @if(count($availActions) > 0)
                                        <div style="border-top:1px solid #eee;margin:4px 0;"></div>
                                        @php $updateProgressShown = false; @endphp
                                        @foreach($availActions as $act)
                                            @if(in_array($act['action'], ['progress', 'final-report']) && !$updateProgressShown)
                                                @php $updateProgressShown = true; @endphp
                                                <a class="dropdown-item" href="{{ route('progress.update', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-chart-line" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> Update Progress
                                                </a>
                                            @elseif($act['action'] === 'progress-grade' || $act['action'] === 'final-grade')
                                                <a class="dropdown-item" href="{{ route('projects.grading', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-star" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'open-grading')
                                                <a class="dropdown-item" href="{{ route('projects.grading', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-clipboard-check" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'report-card')
                                                <a class="dropdown-item" href="{{ route('projects.report-card', $cp->id) }}" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-file-alt" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'claim')
                                                <a class="dropdown-item" href="#" onclick="closeProjectMenus();openWorkflowModal({{ $cp->id }}, 'accept-proposal')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-handshake" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'assign')
                                                <a class="dropdown-item" href="#" onclick="closeProjectMenus();openWorkflowModal({{ $cp->id }}, 'assign')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-user-tag" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'unassign-reviewer')
                                                <a class="dropdown-item" href="#" onclick="closeProjectMenus();confirmUnassignReviewer({{ $cp->id }})" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#dc3545;">
                                                    <i class="fas fa-user-minus" style="width:16px;text-align:center;font-size:11px;"></i> {{ $act['label'] }}
                                                </a>

                                            @elseif($act['action'] === 'review-progress-rejection')
                                                <a class="dropdown-item" href="#" onclick="closeProjectMenus();openWorkflowModal({{ $cp->id }}, 'review-rejection', 'lg', 'report_type=progress')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-balance-scale" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>
                                            @elseif($act['action'] === 'review-final-rejection')
                                                <a class="dropdown-item" href="#" onclick="closeProjectMenus();openWorkflowModal({{ $cp->id }}, 'review-rejection', 'lg', 'report_type=final')" style="padding:6px 12px;font-size:12px;display:flex;align-items:center;gap:8px;text-decoration:none;color:#333;">
                                                    <i class="fas fa-balance-scale" style="width:16px;text-align:center;font-size:11px;color:#6c757d;"></i> {{ $act['label'] }}
                                                </a>

                                            @endif
                                        @endforeach
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

{{-- Single PDF Upload Modal --}}
<div class="modal fade" id="singleUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-header" style="border-bottom:1px solid #e2e8f0;padding:16px 24px;">
                <h5 style="margin:0;font-weight:700;font-size:16px;color:#1e1b4b;">
                    <i class="fas fa-file-pdf" style="color:#dc2626;margin-right:8px;"></i>
                    Upload Proposal PDF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px 24px;">
                <p style="font-size:13px;color:#64748b;margin:0 0 12px;">
                    Upload proposal for project: <strong id="singleUploadProjectId"></strong>
                </p>
                <div id="singleDropZone" style="border:2px dashed #d1d5db;border-radius:10px;padding:30px 20px;text-align:center;cursor:pointer;transition:all .2s;background:#fafafa;"
                    onclick="document.getElementById('singlePdfInput').click();"
                    ondragover="event.preventDefault();this.style.borderColor='#6366f1'"
                    ondragleave="this.style.borderColor='#d1d5db'"
                    ondrop="event.preventDefault();handleSingleFileDrop(event);">
                    <i class="fas fa-cloud-upload-alt" style="font-size:28px;color:#9ca3b8;margin-bottom:6px;display:block;"></i>
                    <p style="margin:0;font-size:12px;font-weight:600;color:#374151;">Click or drag PDF here</p>
                    <input type="file" id="singlePdfInput" accept=".pdf" style="display:none;" onchange="handleSingleFileSelect(this.files)">
                </div>
                <div id="singleFilePreview" style="margin-top:12px;display:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;align-items:center;gap:8px;font-size:12px;">
                    <i class="fas fa-file-pdf" style="color:#dc2626;font-size:14px;"></i>
                    <span id="singleFileName" style="flex:1;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                    <span id="singleFileSize" style="color:#9ca3b8;font-size:10px;"></span>
                    <button type="button" onclick="removeSingleFile()" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;"><i class="fas fa-times"></i></button>
                </div>
                <div id="singleUploadProgress" style="display:none;margin-top:12px;">
                    <div style="height:4px;background:#e5e7eb;border-radius:2px;overflow:hidden;">
                        <div id="singleProgressBar" style="height:100%;background:linear-gradient(135deg,#6366f1,#8b5cf6);width:0%;transition:width .3s;border-radius:2px;"></div>
                    </div>
                </div>
                <div id="singleUploadResult" style="display:none;margin-top:12px;"></div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:12px 24px;display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-primary btn-sm" id="singleUploadBtn" onclick="submitSingleUpload()" disabled>
                    <i class="fas fa-upload" style="margin-right:4px;"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

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

    function toggleProjectMenu(btn) {
        var menu = btn.nextElementSibling;
        var wasOpen = menu.style.display === 'block';
        closeProjectMenus();
        if (!wasOpen) {
            var rect = btn.getBoundingClientRect();
            menu.style.left = (rect.right - 180) + 'px';
            menu.style.top = (rect.bottom + 2) + 'px';
            menu.style.display = 'block';
        }
    }

    function closeProjectMenus() {
        document.querySelectorAll('.action-menu').forEach(function(m) { m.style.display = 'none'; });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            closeProjectMenus();
        }
    });

    var singleFile = null;
    var singleProjectId = null;

    function openSingleUploadModal(projectId, projectTitle) {
        singleFile = null;
        singleProjectId = projectId;
        $('#singleUploadProjectId').text(projectTitle);
        $('#singlePdfInput').val('');
        $('#singleFilePreview').hide();
        $('#singleUploadProgress').hide();
        $('#singleUploadResult').hide();
        $('#singleUploadBtn').prop('disabled', true);
        var modal = new bootstrap.Modal(document.getElementById('singleUploadModal'));
        modal.show();
    }

    function handleSingleFileDrop(event) {
        event.preventDefault();
        handleSingleFileSelect(event.dataTransfer.files);
    }

    function handleSingleFileSelect(files) {
        if (files.length === 0) return;
        var file = files[0];
        if (file.type !== 'application/pdf') {
            showToast('error', 'Please select a PDF file.');
            return;
        }
        singleFile = file;
        $('#singleFileName').text(file.name);
        $('#singleFileSize').text((file.size / 1024).toFixed(1) + ' KB');
        $('#singleFilePreview').css('display', 'flex').show();
        $('#singleUploadBtn').prop('disabled', false);
    }

    function removeSingleFile() {
        singleFile = null;
        $('#singlePdfInput').val('');
        $('#singleFilePreview').hide();
        $('#singleUploadBtn').prop('disabled', true);
    }

    function submitSingleUpload() {
        if (!singleFile || !singleProjectId) return;
        var btn = $('#singleUploadBtn');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
        $('#singleUploadProgress').show();
        $('#singleUploadResult').hide();
        var formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('pdf', singleFile);
        $.ajax({
            url: '/programs/' + singleProjectId + '/upload-proposal',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var pct = Math.round((e.loaded / e.total) * 100);
                        $('#singleProgressBar').css('width', pct + '%');
                    }
                });
                return xhr;
            },
            success: function(resp) {
                $('#singleProgressBar').css('width', '100%');
                $('#singleUploadResult').html('<div style="background:#d1fae5;border:1px solid #a8e6b8;border-radius:8px;padding:10px 14px;font-size:13px;color:#065f46;"><i class="fas fa-check-circle" style="margin-right:6px;"></i>Proposal uploaded successfully.</div>').show();
                btn.html('<i class="fas fa-check"></i> Done');
                setTimeout(function() { location.reload(); }, 1500);
            },
            error: function(xhr) {
                var msg = 'Upload failed.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var firstKey = Object.keys(errors)[0];
                        if (firstKey && errors[firstKey]) {
                            msg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey];
                        }
                    } else if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                }
                $('#singleUploadResult').html('<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;color:#991b1b;"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>' + msg + '</div>').show();
                btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Upload');
            }
        });
    }

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
