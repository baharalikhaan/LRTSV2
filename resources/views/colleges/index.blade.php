@extends('layouts.app')

@section('title', 'Colleges - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-university"></i> Colleges / Institutes</h1>
        <p>Manage colleges and institutes for project categorization.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <form method="GET" class="filter-bar" id="filterForm" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            <button type="button" class="btn-primary btn-sm" data-modal-create="collegeModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New College
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="collegesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>College Name</th>
                    <th>Departments</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colleges as $college)
                <tr>
                    <td><code>{{ $college->id }}</code></td>
                    <td><code>{{ $college->code }}</code></td>
                    <td>
                        <div style="font-weight:500;">{{ $college->name }}</div>
                    </td>
                    <td>
                        @if($college->departments->count())
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($college->departments as $dept)
                                    <span class="pill info" style="font-size:11px;padding:2px 9px;">
                                        <i class="fas fa-folder" style="font-size:9px;"></i> {{ $dept->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:var(--color-ink-400);">&mdash;</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-primary" style="font-size:11px;padding:4px 10px;"
                                title="Edit College"
                                data-modal-edit="collegeModal"
                                data-field-id="{{ $college->id }}"
                                data-field-code="{{ $college->code }}"
                                data-field-name="{{ $college->name }}"
                                data-field-departments='@json($college->departments->pluck("name"))'>
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            <form action="{{ route('colleges.destroy', $college->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete College">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state py-4">
                            <i class="fas fa-university"></i>
                            <h5>No Colleges Found</h5>
                            <p>Create colleges to categorize projects and users.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- College Modal (custom — includes addable departments list) --}}
<div class="modal fade" id="collegeModal" tabindex="-1" aria-labelledby="collegeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('colleges.store') }}" id="collegeModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="collegeModalLabel">
                        <i class="fas fa-university me-2"></i>
                        <span id="collegeModalTitleText">New College</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="collegeModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="collegeModalRecordId" value="">

                    <div class="form-group mb-3">
                        <label for="collegeModal_code" class="form-label">College Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="collegeModal_code" class="form-control" required placeholder="e.g. ENG">
                    </div>

                    <div class="form-group mb-3">
                        <label for="collegeModal_name" class="form-label">College Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="collegeModal_name" class="form-control" required placeholder="e.g. College of Engineering">
                    </div>

                    {{-- Departments — addable list --}}
                    <div class="form-group mb-1">
                        <label class="form-label">Departments</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" id="collegeModalDeptInput" class="form-control" placeholder="e.g. Computer Science" autocomplete="off">
                            <button type="button" id="collegeModalDeptAdd" class="btn-primary btn-sm" style="white-space:nowrap;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <small style="color:var(--color-ink-400);font-size:11px;">Press Enter or click Add. Departments are saved with the college.</small>
                        <div id="collegeModalDeptList" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;"></div>
                        <div id="collegeModalDepartmentsInput"></div>{{-- hidden departments[] inputs --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="collegeModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="collegeModalBtnText">Create College</span>
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
    @if($colleges->count() > 0)
    var table = $('#collegesTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [4] },
            { searchable: false, targets: [4] }
        ],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // ─── Departments addable list ────────────────────────────────────────
    var deptNames = [];

    function esc(s) {
        return $('<div>').text(s == null ? '' : String(s)).html();
    }

    function renderDepts() {
        var html = '';
        if (deptNames.length === 0) {
            html = '<span style="font-size:12px;color:var(--color-ink-400);">No departments added yet.</span>';
        } else {
            $.each(deptNames, function(i, n) {
                html += '<span class="pill info" style="font-size:12px;">' + esc(n) +
                        ' <a href="#" data-dept-remove="' + i + '" style="margin-left:5px;color:inherit;font-weight:700;text-decoration:none;" title="Remove">&times;</a></span>';
            });
        }
        $('#collegeModalDeptList').html(html);

        // Sync hidden inputs so form.serialize() picks them up
        var $c = $('#collegeModalDepartmentsInput').empty();
        $.each(deptNames, function(i, n) {
            $c.append($('<input>', { type: 'hidden', name: 'departments[]', value: n }));
        });
    }

    function addDept() {
        var v = $('#collegeModalDeptInput').val().trim();
        if (!v) return;
        var exists = deptNames.some(function(n) { return n.toLowerCase() === v.toLowerCase(); });
        if (exists) {
            showToast('warning', 'This department is already in the list.');
            return;
        }
        deptNames.push(v);
        $('#collegeModalDeptInput').val('').focus();
        renderDepts();
    }

    $('#collegeModalDeptAdd').on('click', addDept);
    $('#collegeModalDeptInput').on('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); addDept(); }
    });
    $(document).on('click', '[data-dept-remove]', function(e) {
        e.preventDefault();
        deptNames.splice(+$(this).data('dept-remove'), 1);
        renderDepts();
    });

    // ─── CREATE ──────────────────────────────────────────────────────────
    $(document).on('click', '[data-modal-create="collegeModal"]', function() {
        $('#collegeModalForm')[0].reset();
        $('#collegeModalMethod').val('POST');
        $('#collegeModalRecordId').val('');
        $('#collegeModalTitleText').text('New College');
        $('#collegeModalBtnText').text('Create College');
        $('#collegeModalForm').attr('action', '{{ route('colleges.store') }}');
        deptNames = [];
        renderDepts();
        $('#collegeModal .is-invalid').removeClass('is-invalid');
        $('#collegeModal .invalid-feedback').remove();
        $('#collegeModal').modal('show');
    });

    // ─── EDIT ────────────────────────────────────────────────────────────
    $(document).on('click', '[data-modal-edit="collegeModal"]', function() {
        var data = {};
        $.each(this.attributes, function(i, attr) {
            if (attr.name.startsWith('data-field-')) {
                data[attr.name.replace('data-field-', '')] = attr.value;
            }
        });

        $('#collegeModalForm')[0].reset();
        $('#collegeModalMethod').val('PUT');
        $('#collegeModalRecordId').val(data.id || '');
        $('#collegeModalTitleText').text('Edit College');
        $('#collegeModalBtnText').text('Update College');
        $('#collegeModalForm').attr('action', '{{ route('colleges.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.id));

        $('#collegeModal_code').val(data.code || '');
        $('#collegeModal_name').val(data.name || '');

        // data-field-departments is a JSON array of names
        var depts = $(this).data('fieldDepartments');
        deptNames = Array.isArray(depts) ? depts : [];
        renderDepts();

        $('#collegeModal .is-invalid').removeClass('is-invalid');
        $('#collegeModal .invalid-feedback').remove();
        $('#collegeModal').modal('show');
    });

    // ─── SUBMIT (AJAX) ───────────────────────────────────────────────────
    $('#collegeModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#collegeModalSubmitBtn');
        var method = $('#collegeModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#collegeModal').modal('hide');
                showToast('success', resp.message || 'College saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' College</span>');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('#collegeModal .is-invalid').removeClass('is-invalid');
                    $('#collegeModal .invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"], [name="' + field.replace('.*', '') + '[]"]').first();
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                        }
                    });
                } else {
                    showToast('error', 'An error occurred. Please try again.');
                }
            }
        });
    });

    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        if (!confirm('Are you sure you want to delete this college?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
