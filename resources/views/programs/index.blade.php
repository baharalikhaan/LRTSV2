@extends('layouts.app')

@section('title', 'Research Calls - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-sync-alt"></i> Research Calls</h1>
        <p>Manage research calls, deadlines, and submissions.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Grant:</label>
                    <select name="grant" onchange="this.form.submit();" id="grantFilter">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->id }}" {{ (request('grant') == $grant->id) ? 'selected' : '' }}>{{ $grant->grant_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle" onchange="this.form.submit();" id="cycleFilter">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cc)
                            <option value="{{ $cc->id }}" {{ (request('cycle') == $cc->id) ? 'selected' : '' }}>{{ $cc->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Visibility:</label>
                    <select name="visibility" onchange="this.form.submit();" id="visibilityFilter">
                        <option value="">All</option>
                        <option value="visible" {{ request('visibility') == 'visible' ? 'selected' : '' }}>Visible</option>
                        <option value="hidden" {{ request('visibility') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            @auth
            @if(auth()->user()->isAdmin())
            <button type="button" class="btn-primary btn-sm" data-modal-create="programModal" style="flex-shrink:0;">
                <i class="fas fa-plus"></i> New Research Call
            </button>
            @endif
            @endauth
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="programsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Research Call Title</th>
                    <th>Grant</th>
                    <th>Cycle</th>
                    <th>Prog. Report Deadline</th>
                    <th>Ext. Prog. Report Deadline</th>
                    <th>Final Report Deadline</th>
                    <th>Ext. Final Report Deadline</th>
                    <th class="text-center">Status
                        <i class="fas fa-question-circle" style="color:var(--color-ink-300);font-size:11px;margin-left:4px;cursor:help;"
                            data-bs-toggle="tooltip" data-bs-html="true"
                            title="When the deadlines are not passed, the status is <strong>Active</strong>.<br>When all deadlines have passed, the status becomes <strong>Inactive</strong>."></i>
                    </th>
                    <th class="text-center" style="min-width:80px;">Visibility
                        <i class="fas fa-question-circle" style="color:var(--color-ink-300);font-size:11px;margin-left:4px;cursor:help;"
                            data-bs-toggle="tooltip" data-bs-html="true"
                            title="When the admin marks it <strong>Visible</strong>, it is visible to reviewers.<br>Otherwise it will be <strong>Hidden</strong> from reviewers."></i>
                    </th>
                    <th class="text-center" style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr class="{{ $program->is_visible ? '' : 'hidden-row' }}">
                    <td><code>{{ $program->id }}</code></td>
                    <td><span style="font-weight:500;">{{ $program->program_title }}</span></td>
                    <td>
                        @if($program->grant)
                            <span class="pill primary">{{ $program->grant->grant_code }}</span>
                        @else
                            <span class="pill primary">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($program->cycleConfig)
                            <span class="pill secondary">{{ $program->cycleConfig->title }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($program->prog_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->prog_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($program->extended_prog_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->extended_prog_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($program->final_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->final_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($program->extended_final_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $program->extended_final_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($program->isActive())
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-lock" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @auth
                        @if(auth()->user()->isAdmin())
                        <button type="button" class="toggle-visibility-btn btn-sm {{ $program->is_visible ? 'btn-secondary' : 'btn-primary' }}"
                            style="font-size:11px;padding:4px 10px;white-space:nowrap;"
                            title="{{ $program->is_visible ? 'Hide this call' : 'Show this call' }}"
                            data-bs-toggle="tooltip"
                            data-program-id="{{ $program->id }}">
                            <i class="fas {{ $program->is_visible ? 'fa-eye-slash' : 'fa-eye' }}" style="font-size:11px;"></i>
                            {{ $program->is_visible ? 'Hide' : 'Show' }}
                        </button>
                        @else
                        <span class="pill {{ $program->is_visible ? 'success' : 'inactive' }}" style="font-size:10px;">
                            {{ $program->is_visible ? 'Visible' : 'Hidden' }}
                        </span>
                        @endif
                        @endauth
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <a href="{{ route('programs.show', $program->id) }}" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;">
                                <i class="fas fa-eye" style="font-size:11px;"></i> Details
                            </a>
                            @auth
                            @if(auth()->user()->isAdmin())
                            <button type="button"
                                class="btn-sm btn-primary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="programModal"
                                data-field-id="{{ $program->id }}"
                                data-field-program_title="{{ $program->program_title }}"
                                data-field-grant_id="{{ $program->grant_id }}"
                                data-field-cycle_id="{{ $program->cycle_id }}"
                                data-field-prog_rpt_deadline="{{ $program->prog_rpt_deadline ? $program->prog_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-extended_prog_rpt_deadline="{{ $program->extended_prog_rpt_deadline ? $program->extended_prog_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-prog_rpt2_deadline="{{ $program->prog_rpt2_deadline ? $program->prog_rpt2_deadline->format('Y-m-d') : '' }}"
                                data-field-extended_prog_rpt2_deadline="{{ $program->extended_prog_rpt2_deadline ? $program->extended_prog_rpt2_deadline->format('Y-m-d') : '' }}"
                                data-field-final_rpt_deadline="{{ $program->final_rpt_deadline ? $program->final_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-extended_final_rpt_deadline="{{ $program->extended_final_rpt_deadline ? $program->extended_final_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-description="{{ $program->description }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11">
                        <div class="empty-state py-4">
                            <i class="fas fa-sync-alt"></i>
                            <h5>No Research Calls Found</h5>
                            <p>Use the <strong>New Research Call</strong> button at the top right to create one.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Research Call Modal --}}
<div class="modal fade" id="programModal" tabindex="-1" aria-labelledby="programModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:1200px;">
        <div class="modal-content">
            <form method="POST" action="{{ route('programs.store') }}" id="programModalForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="programModalLabel">
                        <i class="fas fa-sync-alt me-2"></i>
                        <span id="programModalTitleText">New Research Call</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="programModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="programModalRecordId" value="">

                    {{-- Upload notice (compact) --}}
                    <div class="fluent-alert info d-none py-1 mb-2 text-center" id="programModalFileNotice" style="font-size:12px;">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <span>Excel file <strong>required</strong> on create. Proposals ZIP optional.</span>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_grant_id" class="form-label" style="font-size:12px;">Grant <span class="text-danger">*</span></label>
                                <select name="grant_id" id="programModal_grant_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Grant --</option>
                                    @foreach($grants as $grant)
                                        <option value="{{ $grant->id }}" data-grant-code="{{ $grant->grant_code }}">{{ $grant->grant_code }} - {{ $grant->grant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_cycle_id" class="form-label" style="font-size:12px;">Cycle</label>
                                <select name="cycle_id" id="programModal_cycle_id" class="form-select form-select-sm">
                                    <option value="">-- None --</option>
                                    @foreach($cycleConfigs as $cc)
                                        <option value="{{ $cc->id }}" data-cycle-title="{{ $cc->title }}">{{ $cc->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_program_title" class="form-label" style="font-size:12px;">Research Call Title <span class="text-danger">*</span></label>
                                <input type="text" name="program_title" id="programModal_program_title" class="form-control form-control-sm" required placeholder="Auto-generated from Grant + Cycle" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2" id="programModal_file_upload_group">
                                <label for="programModal_excel" class="form-label" style="font-size:12px;">
                                    Excel File (.xlsx, .xls, .csv) <span class="text-danger" id="programModalExcelRequired">*</span>
                                </label>
                                <input type="file" name="excel" id="programModal_excel" class="form-control form-control-sm" accept=".xlsx,.xls,.csv">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_prog_rpt_deadline" class="form-label" style="font-size:12px;">Progress Report Deadline</label>
                                <input type="date" name="prog_rpt_deadline" id="programModal_prog_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_extended_prog_rpt_deadline" class="form-label" style="font-size:12px;">Extended Prog. Report Deadline</label>
                                <input type="date" name="extended_prog_rpt_deadline" id="programModal_extended_prog_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_prog_rpt2_deadline" class="form-label" style="font-size:12px;">Prog. Report 2 Deadline</label>
                                <input type="date" name="prog_rpt2_deadline" id="programModal_prog_rpt2_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_extended_prog_rpt2_deadline" class="form-label" style="font-size:12px;">Extended Prog. Report 2 Deadline</label>
                                <input type="date" name="extended_prog_rpt2_deadline" id="programModal_extended_prog_rpt2_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_final_rpt_deadline" class="form-label" style="font-size:12px;">Final Report Deadline</label>
                                <input type="date" name="final_rpt_deadline" id="programModal_final_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_extended_final_rpt_deadline" class="form-label" style="font-size:12px;">Extended Final Report Deadline</label>
                                <input type="date" name="extended_final_rpt_deadline" id="programModal_extended_final_rpt_deadline" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_proposals_zip" class="form-label" style="font-size:12px;">Proposals ZIP (optional)</label>
                                <input type="file" name="proposals_zip" id="programModal_proposals_zip" class="form-control form-control-sm" accept=".zip">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label for="programModal_description" class="form-label" style="font-size:12px;">Description</label>
                                <textarea name="description" id="programModal_description" class="form-control form-control-sm" rows="2" placeholder="Optional description..."></textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <p class="mb-0" style="font-size:11px;color:var(--color-ink-400);">
                                <i class="fas fa-info-circle"></i> Status is computed from deadlines — inactive when deadlines have passed.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="programModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="programModalBtnText">Create Research Call</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tooltip .tooltip-inner {
    background: #fff;
    color: var(--color-ink-700);
    border: 1px solid var(--color-ink-200);
    font-size: 11.5px;
    font-weight: 400;
    padding: 8px 12px;
    border-radius: 6px;
    box-shadow: var(--fluent-depth-8);
    max-width: 260px;
}
.tooltip.bs-tooltip-top .tooltip-arrow::before { border-top-color: var(--color-ink-200); }
.tooltip.bs-tooltip-bottom .tooltip-arrow::before { border-bottom-color: var(--color-ink-200); }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if($programs->count() > 0)
    var table = $('#programsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [9, 10] },
            { searchable: false, targets: [9, 10] }
        ]
    });

    // Connect custom search input to DataTables search
    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Status filter — custom DataTable filter on column index 8 (Status)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var statusFilter = $('#statusFilter').val();
        if (!statusFilter) return true; // no filter

        var statusCell = $(table.cell(dataIndex, 8).node()).text().trim();
        if (statusFilter === 'active' && statusCell.indexOf('Active') !== -1) return true;
        if (statusFilter === 'inactive' && statusCell.indexOf('Inactive') !== -1) return true;
        return false;
    });

    // Bind status filter dropdown
    var statusSelect = $('#statusFilter');
    statusSelect.on('change', function() {
        table.draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip({html: true});

    // Auto-generate program_title from Grant code + Cycle title
    function autoGenerateProgramTitle() {
        var grantSelect = $('#programModal_grant_id');
        var cycleSelect = $('#programModal_cycle_id');
        var titleInput = $('#programModal_program_title');

        var grantCode = grantSelect.find('option:selected').data('grant-code') || '';
        var cycleTitle = cycleSelect.find('option:selected').data('cycle-title') || '';

        if (grantCode && cycleTitle) {
            titleInput.val(grantCode + ' - ' + cycleTitle);
        } else if (grantCode) {
            titleInput.val(grantCode);
        } else if (cycleTitle) {
            titleInput.val(cycleTitle);
        } else {
            titleInput.val('');
        }
    }

    $('#programModal_grant_id').on('change', autoGenerateProgramTitle);
    $('#programModal_cycle_id').on('change', autoGenerateProgramTitle);

    // Open create modal
    $(document).on('click', '[data-modal-create="programModal"]', function() {
        $('#programModalForm')[0].reset();
        $('#programModalMethod').val('POST');
        $('#programModalRecordId').val('');
        $('#programModalTitleText').text('New Research Call');
        $('#programModalBtnText').text('Create Research Call');
        $('#programModalForm').attr('action', '{{ route('programs.store') }}');
        $('#programModal_file_upload_group').show();
        $('#programModalFileNotice').removeClass('d-none');
        $('#programModalExcelRequired').show();
        $('#programModal_excel').prop('required', true);
        $('#programModal_program_title').prop('readonly', true).val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#programModal').modal('show');
        autoGenerateProgramTitle();
    });

    // Open edit modal
    $(document).on('click', '[data-modal-edit="programModal"]', function() {
        var data = $(this).data();
        $('#programModalForm')[0].reset();
        $('#programModalMethod').val('PUT');
        $('#programModalRecordId').val(data.fieldId || '');
        $('#programModalTitleText').text('Edit Research Call');
        $('#programModalBtnText').text('Update Research Call');
        $('#programModalForm').attr('action', '{{ route('programs.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.fieldId));
        $('#programModal_file_upload_group').hide();
        $('#programModalFileNotice').addClass('d-none');
        $('#programModal_excel').prop('required', false);
        $('#programModal_grant_id').val(data.fieldGrantId || '');
        $('#programModal_cycle_id').val(data.fieldCycleId || '');
        $('#programModal_program_title').val(data.fieldProgramTitle || '');
        $('#programModal_prog_rpt_deadline').val(data.fieldProgRptDeadline || '');
        $('#programModal_extended_prog_rpt_deadline').val(data.fieldExtendedProgRptDeadline || '');
        $('#programModal_prog_rpt2_deadline').val(data.fieldProgRpt2Deadline || '');
        $('#programModal_extended_prog_rpt2_deadline').val(data.fieldExtendedProgRpt2Deadline || '');
        $('#programModal_final_rpt_deadline').val(data.fieldFinalRptDeadline || '');
        $('#programModal_extended_final_rpt_deadline').val(data.fieldExtendedFinalRptDeadline || '');
        $('#programModal_description').val(data.fieldDescription || '');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#programModal').modal('show');
    });

    // AJAX submit
    $('#programModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#programModalSubmitBtn');
        var method = $('#programModalMethod').val();
        var url = form.attr('action');

        // Ensure program_title is auto-populated before submit
        autoGenerateProgramTitle();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        var formData = new FormData(form[0]);

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(resp) {
                $('#programModal').modal('hide');
                showToast('success', resp.message || 'Research call saved successfully!');
                setTimeout(function() { location.reload(); }, 1200);
            },
            error: function(xhr) {
        btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Research Call</span>');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                        }
                    });
                } else {
                    showToast('error', xhr.responseJSON?.message || 'An error occurred. Please try again.');
                }
            }
        });
    });
});

// Toggle visibility via AJAX — updates button and row style in-place
$(document).on('click', '.toggle-visibility-btn', function() {
    var btn = $(this);
    var programId = btn.data('program-id');
    var row = btn.closest('tr');
    btn.prop('disabled', true);

    $.ajax({
        url: '{{ route('programs.toggle', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', programId),
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        dataType: 'json',
        success: function(resp) {
            if (resp.is_visible) {
                row.removeClass('hidden-row');
                btn.removeClass('btn-primary').addClass('btn-secondary');
                btn.attr('title', 'Hide this call');
                btn.html('<i class="fas fa-eye-slash" style="font-size:11px;"></i> Hide');
            } else {
                row.addClass('hidden-row');
                btn.removeClass('btn-secondary').addClass('btn-primary');
                btn.attr('title', 'Show this call');
                btn.html('<i class="fas fa-eye" style="font-size:11px;"></i> Show');
            }
            showToast('success', resp.message);
            btn.prop('disabled', false);
        },
        error: function(xhr) {
            showToast('error', xhr.responseJSON?.message || 'Failed to toggle visibility.');
            btn.prop('disabled', false);
        }
    });
});

</script>
@endpush
