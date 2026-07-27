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
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <div class="filter-group" style="margin-bottom:0;">
                    <label>Grant:</label>
                    <select id="filterGrant" style="font-size:13px; padding:4px 8px; border:1px solid var(--color-ink-200); border-radius:6px;">
                        <option value="">All Grants</option>
                        @foreach($grants as $grant)
                            <option value="{{ $grant->grant_code }}">{{ $grant->grant_code }} - {{ $grant->grant_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="margin-bottom:0;">
                    <label>Research Call:</label>
                    <select id="filterCycle" style="font-size:13px; padding:4px 8px; border:1px solid var(--color-ink-200); border-radius:6px;">
                        <option value="">All Research Calls</option>
                        @foreach($filterCycles as $fc)
                            <option value="{{ $fc->program_title }}">{{ $fc->program_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="margin-bottom:0;">
                    <label>Search:</label>
                    <input type="text" id="cyclesTableSearch" placeholder="Search research calls..." style="font-size:13px; padding:4px 8px; border:1px solid var(--color-ink-200); border-radius:6px;">
                </div>
            </div>
            @if(Auth::user()->isAdmin() || Auth::user()->isLPI())
            <button type="button" class="btn-primary btn-sm" data-modal-create="cycleModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Research Call
            </button>
            @endif
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="cyclesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Research Call Title</th>
                    <th>Grant</th>
                    <th>Cycle</th>
                    <th>Prog. Report Deadline</th>
                    <th>Final Report Deadline</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cycles as $cycle)
                <tr>
                    <td><code>{{ $cycle->id }}</code></td>
                    <td><span style="font-weight:500;">{{ $cycle->program_title }}</span></td>
                    <td><span class="pill primary">{{ $cycle->grant->grant_code ?? 'N/A' }}</span></td>
                    <td>
                        <span class="text-muted">&mdash;</span>
                    </td>
                    <td>
                        @if($cycle->prog_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $cycle->prog_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($cycle->final_rpt_deadline)
                            <span class="text-nowrap"><i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i> {{ $cycle->final_rpt_deadline->format('d-m-Y') }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        @if($cycle->isActive())
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill review"><i class="fas fa-lock" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <a href="{{ route('cycles.show', $cycle->id) }}" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;">
                                <i class="fas fa-eye" style="font-size:11px;"></i> View
                            </a>
                            @if(Auth::user()->isAdmin())
                            <button type="button"
                                class="btn-sm btn-primary" style="font-size:11px;padding:4px 10px;"
                                title="Edit Research Call"
                                data-bs-toggle="tooltip"
                                data-modal-edit="cycleModal"
                                data-field-id="{{ $cycle->id }}"
                                data-field-program_title="{{ $cycle->program_title }}"
                                data-field-grant_id="{{ $cycle->grant_id }}"
                                data-field-prog_rpt_deadline="{{ $cycle->prog_rpt_deadline ? $cycle->prog_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-extended_prog_rpt_deadline="{{ $cycle->extended_prog_rpt_deadline ? $cycle->extended_prog_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-final_rpt_deadline="{{ $cycle->final_rpt_deadline ? $cycle->final_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-extended_final_rpt_deadline="{{ $cycle->extended_final_rpt_deadline ? $cycle->extended_final_rpt_deadline->format('Y-m-d') : '' }}"
                                data-field-description="{{ $cycle->description }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state py-4">
                            <i class="fas fa-sync-alt"></i>
                            <h5>No Research Calls Found</h5>
                            <p>Get started by creating a new research call.</p>
                            @if(Auth::user()->isAdmin() || Auth::user()->isLPI())
                            <button type="button" class="btn-primary mt-2" data-modal-create="cycleModal">
                                <i class="fas fa-plus"></i> New Research Call
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Research Call Modal --}}
<div class="modal fade" id="cycleModal" tabindex="-1" aria-labelledby="cycleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('cycles.store') }}" id="cycleModalForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cycleModalLabel">
                        <i class="fas fa-sync-alt me-2"></i>
                        <span id="cycleModalTitleText">New Research Call</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="cycleModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="cycleModalRecordId" value="">

                    {{-- Alert for upload info --}}
                    <div class="fluent-alert info d-none" id="cycleModalFileNotice">
                        <i class="fas fa-info-circle alert-icon"></i>
                        <span>Excel file is <strong>required</strong> on create. Proposals ZIP is optional.</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_grant_id" class="form-label">Grant <span class="text-danger">*</span></label>
                                <select name="grant_id" id="cycleModal_grant_id" class="form-select" required>
                                    <option value="">-- Select Grant --</option>
                                    @foreach($grants as $grant)
                                        <option value="{{ $grant->id }}">{{ $grant->grant_code }} - {{ $grant->grant_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_program_title" class="form-label">Research Call Title <span class="text-danger">*</span></label>
                                <input type="text" name="program_title" id="cycleModal_program_title" class="form-control" required placeholder="e.g. Spring 2025 Research Call">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_prog_rpt_deadline" class="form-label">Progress Report Deadline</label>
                                <input type="date" name="prog_rpt_deadline" id="cycleModal_prog_rpt_deadline" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_extended_prog_rpt_deadline" class="form-label">Extended Prog. Report Deadline</label>
                                <input type="date" name="extended_prog_rpt_deadline" id="cycleModal_extended_prog_rpt_deadline" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_final_rpt_deadline" class="form-label">Final Report Deadline</label>
                                <input type="date" name="final_rpt_deadline" id="cycleModal_final_rpt_deadline" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_extended_final_rpt_deadline" class="form-label">Extended Final Report Deadline</label>
                                <input type="date" name="extended_final_rpt_deadline" id="cycleModal_extended_final_rpt_deadline" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3" id="cycleModal_file_upload_group">
                                <label for="cycleModal_excel" class="form-label">
                                    Excel File (.xlsx, .xls, .csv) <span class="text-danger" id="cycleModalExcelRequired">*</span>
                                </label>
                                <input type="file" name="excel" id="cycleModal_excel" class="form-control" accept=".xlsx,.xls,.csv">
                                <small class="form-text" style="color:var(--color-ink-400);">Upload the ConfTool export Excel file.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_proposals_zip" class="form-label">Proposals ZIP (optional)</label>
                                <input type="file" name="proposals_zip" id="cycleModal_proposals_zip" class="form-control" accept=".zip">
                                <small class="form-text" style="color:var(--color-ink-400);">Upload a ZIP file containing proposal documents.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="cycleModal_description" class="form-label">Description</label>
                                <textarea name="description" id="cycleModal_description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="cycleModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="cycleModalBtnText">Create Research Call</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($cycles->count() > 0)
    var table = $('#cyclesTable').DataTable({
        dom: '<"row"<"col-sm-12"tr>>' +
             '<"row align-items-center"<"col-sm-5"i><"col-sm-7"p>>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [7] },
            { searchable: false, targets: [7] }
        ]
    });

    // Custom search input
    $('#cyclesTableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Grant filter (column 2)
    $('#filterGrant').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    // Cycle/Research Call filter (column 1)
    $('#filterCycle').on('change', function() {
        table.column(1).search(this.value).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // Open create modal
    $(document).on('click', '[data-modal-create="cycleModal"]', function() {
        $('#cycleModalForm')[0].reset();
        $('#cycleModalMethod').val('POST');
        $('#cycleModalRecordId').val('');
        $('#cycleModalTitleText').text('New Research Call');
        $('#cycleModalBtnText').text('Create Research Call');
        $('#cycleModalForm').attr('action', '{{ route('cycles.store') }}');
        $('#cycleModal_file_upload_group').show();
        $('#cycleModalFileNotice').removeClass('d-none');
        $('#cycleModalExcelRequired').show();
        $('#cycleModal_excel').prop('required', true);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#cycleModal').modal('show');
    });

    // Open edit modal
    $(document).on('click', '[data-modal-edit="cycleModal"]', function() {
        var data = $(this).data();
        $('#cycleModalForm')[0].reset();
        $('#cycleModalMethod').val('PUT');
        $('#cycleModalRecordId').val(data.fieldId || '');
        $('#cycleModalTitleText').text('Edit Research Call');
        $('#cycleModalBtnText').text('Update Research Call');
        $('#cycleModalForm').attr('action', '{{ route('cycles.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.fieldId));
        $('#cycleModal_file_upload_group').hide();
        $('#cycleModalFileNotice').addClass('d-none');
        $('#cycleModal_excel').prop('required', false);
        $('#cycleModal_grant_id').val(data.fieldGrantId || '');
        $('#cycleModal_program_title').val(data.fieldProgramTitle || '');
        $('#cycleModal_prog_rpt_deadline').val(data.fieldProgRptDeadline || '');
        $('#cycleModal_extended_prog_rpt_deadline').val(data.fieldExtendedProgRptDeadline || '');
        $('#cycleModal_final_rpt_deadline').val(data.fieldFinalRptDeadline || '');
        $('#cycleModal_extended_final_rpt_deadline').val(data.fieldExtendedFinalRptDeadline || '');
        $('#cycleModal_description').val(data.fieldDescription || '');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#cycleModal').modal('show');
    });

    // AJAX submit
    $('#cycleModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#cycleModalSubmitBtn');
        var method = $('#cycleModalMethod').val();
        var url = form.attr('action');

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
                $('#cycleModal').modal('hide');
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

</script>
@endpush
