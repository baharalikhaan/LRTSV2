@extends('layouts.app')

@section('title', 'Users - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-users"></i> Users</h1>
        <p>Manage system users, roles, and permissions.</p>
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
                <div class="filter-group">
                    <label>Role:</label>
                    <select id="roleFilter" class="search-input" style="min-width:140px;">
                        <option value="">All Roles</option>
                        <option value="LPI">LPI</option>
                        <option value="LPI+Reviewer">LPI+Reviewer</option>
                        <option value="Admin">Admin</option>
                        <option value="Reviewer">Reviewer</option>
                    </select>
                </div>
            </form>
            <button type="button" class="btn-primary btn-sm" data-modal-create="userModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New User
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Pillar</th>
                    <th>College</th>
                    <th>Department</th>
                    <th>Active</th>
                    <th style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td><code>{{ $user->id }}</code></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:var(--color-brand-50);color:var(--color-brand-600);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:500;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-{{ $user->type }}">{{ $user->type }}</span></td>
                    <td>
                        @php
                            $userPillars = $user->getRelation('pillars');
                        @endphp
                        @if($userPillars->isNotEmpty())
                            @foreach($userPillars as $pillar)
                                <span class="pill info" style="font-size:11px;">{{ $pillar->pillar }}</span>
                            @endforeach
                        @else
                            <span style="color:var(--ink-400);">—</span>
                        @endif
                    </td>
                    <td>{{ $user->college ?? '—' }}</td>
                    <td>{{ $user->department ?? '—' }}</td>
                    <td>
                        @if($user->is_active)
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-times-circle" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="userModal"
                                data-field-id="{{ $user->id }}"
                                data-field-name="{{ $user->name }}"
                                data-field-email="{{ $user->email }}"
                                data-field-type="{{ $user->type }}"
                                data-field-qu_id="{{ $user->qu_id }}"
                                data-field-department="{{ $user->department }}"
                                data-field-college="{{ $user->college }}"
                                data-field-faculty="{{ $user->faculty ? '1' : '0' }}"
                                data-field-is_active="{{ $user->is_active ? '1' : '0' }}"
                                data-field-pillar_ids="{{ $user->getRelation('pillars')->pluck('id')->toJson() }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            @if(!$user->isAdmin() && $user->lpi_projects_count === 0 && $user->reviewer_projects_count === 0)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete User">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-4">
                            <i class="fas fa-users"></i>
                            <h5>No Users Found</h5>
                            <p>Get started by creating a new user.</p>
                            <button type="button" class="btn-primary mt-2" data-modal-create="userModal">
                                <i class="fas fa-plus"></i> New User
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- User Modal --}}
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <form method="POST" action="{{ route('users.store') }}" id="userModalForm">
                @csrf
                <div class="modal-header" style="border-bottom:1px solid #e2e8f0;padding:20px 28px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--color-brand-50,#fdf2f4),var(--color-brand-100,#fce7eb));display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-user-plus" style="color:var(--color-brand-600,#8d1b3d);font-size:16px;"></i>
                        </div>
                        <h5 style="margin:0;font-weight:700;font-size:17px;color:#1e1b4b;">
                            <span id="userModalTitleText">New User</span>
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:24px 28px;">
                    <input type="hidden" name="_method" id="userModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="userModalRecordId" value="">

                    {{-- Identity --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--color-ink-400,#64748b);margin-bottom:10px;">Identity</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="userModal_name" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="userModal_name" class="form-control" style="border-radius:8px;font-size:13px;" required placeholder="e.g. John Doe">
                            </div>
                            <div class="col-md-6">
                                <label for="userModal_email" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="userModal_email" class="form-control" style="border-radius:8px;font-size:13px;" required placeholder="e.g. johndoe@qu.edu.qa">
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-6">
                                <label for="userModal_qu_id" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">QU ID</label>
                                <input type="email" name="qu_id" id="userModal_qu_id" class="form-control" style="border-radius:8px;font-size:13px;" placeholder="e.g. jdoe@qu.edu.qa">
                                <small style="color:var(--color-ink-400);font-size:11px;">The university email address.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="userModal_type" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">User Type <span class="text-danger">*</span></label>
                                <select name="type" id="userModal_type" class="form-select" style="border-radius:8px;font-size:13px;" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="LPI">LPI</option>
                                    <option value="Reviewer">Reviewer</option>
                                    <option value="LPI+Reviewer">LPI+Reviewer</option>
                                    </select>
                            </div>
                        </div>
                    </div>

                    <div style="border-top:1px solid #f1f5f9;margin:4px 0 20px;"></div>

                    {{-- Affiliation --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--color-ink-400,#64748b);margin-bottom:10px;">Affiliation</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="userModal_college" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">College</label>
                                <select name="college" id="userModal_college" class="form-select" style="border-radius:8px;font-size:13px;">
                                    <option value="">-- Select College --</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->name }}">{{ $college->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="userModal_department" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Department</label>
                                <select name="department" id="userModal_department" class="form-select" style="border-radius:8px;font-size:13px;" disabled>
                                    <option value="">-- Select College first --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="border-top:1px solid #f1f5f9;margin:4px 0 20px;"></div>

                    {{-- Status & Pillars --}}
                    <div style="margin-bottom:16px;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--color-ink-400,#64748b);margin-bottom:10px;">Status & Pillars</div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label d-block" style="font-size:12px;font-weight:600;color:#475569;">Roles & Status</label>
                                <div class="form-check form-switch" style="margin-bottom:6px;">
                                    <input type="hidden" name="faculty" value="0">
                                    <input type="checkbox" name="faculty" id="userModal_faculty" class="form-check-input" value="1" checked>
                                    <label class="form-check-label" for="userModal_faculty" style="font-size:13px;">Faculty Member</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="userModal_is_active" class="form-check-input" value="1" checked>
                                    <label class="form-check-label" for="userModal_is_active" style="font-size:13px;">Active</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="userModal_pillar_ids" class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Research Pillars</label>
                                <select name="pillar_ids[]" id="userModal_pillar_ids" class="form-select" multiple style="min-height:80px;border-radius:8px;font-size:12px;">
                                    @foreach($pillars as $pillar)
                                        <option value="{{ $pillar->id }}">{{ $pillar->pillar }}</option>
                                    @endforeach
                                </select>
                                <small style="color:var(--color-ink-400);font-size:11px;">Hold Ctrl/Cmd to select multiple.</small>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer" style="border-top:1px solid #e2e8f0;padding:16px 28px;justify-content:flex-start;gap:10px;">
                    <button type="button" class="btn-secondary btn-sm" data-bs-dismiss="modal" style="padding:10px 20px;border-radius:8px;font-weight:600;font-size:13px;">
                        <i class="fas fa-times" style="margin-right:4px;"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary btn-sm" id="userModalSubmitBtn" style="padding:10px 24px;border-radius:8px;font-weight:600;font-size:13px;">
                        <i class="fas fa-save" style="margin-right:4px;"></i> <span id="userModalBtnText">Create User</span>
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
    @if($users->count() > 0)
    var table = $('#usersTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [7] },
            { searchable: false, targets: [7] }
        ],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#roleFilter').on('change', function() {
        var val = this.value;
        table.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // ─── Dependent College → Department dropdowns ────────────────────────
    var departmentsData = @json($departments->map(function($d) {
        return ['id' => $d->id, 'name' => $d->name, 'college_name' => optional($d->college)->name];
    }));

    function populateDepartments(collegeName, selectedDept) {
        var $dept = $('#userModal_department');
        $dept.empty();
        if (!collegeName) {
            $dept.append('<option value="">-- Select College first --</option>').prop('disabled', true);
            return;
        }
        var list = departmentsData.filter(function(d) { return d.college_name === collegeName; });
        if (list.length === 0) {
            $dept.append('<option value="">No departments for this college</option>').prop('disabled', true);
            return;
        }
        $dept.append('<option value="">-- Select Department --</option>');
        $.each(list, function(i, d) {
            $dept.append($('<option>', { value: d.name, text: d.name }));
        });
        // Keep the stored value visible even if it is no longer in the table.
        if (selectedDept && list.filter(function(d) { return d.name === selectedDept; }).length === 0) {
            $dept.append($('<option>', { value: selectedDept, text: selectedDept + ' (current)' }));
        }
        if (selectedDept) {
            $dept.val(selectedDept);
        }
        $dept.prop('disabled', false);
    }

    $('#userModal_college').on('change', function() {
        populateDepartments(this.value, null);
    });

    // CREATE modal
    $(document).on('click', '[data-modal-create="userModal"]', function() {
        $('#userModalForm')[0].reset();
        $('#userModalMethod').val('POST');
        $('#userModalRecordId').val('');
        $('#userModalTitleText').text('New User');
        $('#userModalBtnText').text('Create User');
        $('#userModalForm').attr('action', '{{ route('users.store') }}');
        $('#userModal_name, #userModal_email, #userModal_qu_id').prop('readonly', false);
        populateDepartments('', null);
        $('#userModal_pillar_ids').val([]);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#userModal').modal('show');
    });

    // EDIT modal
    $(document).on('click', '[data-modal-edit="userModal"]', function() {
        var el = $(this);
        var data = {
            fieldId: el.attr('data-field-id'),
            fieldName: el.attr('data-field-name'),
            fieldEmail: el.attr('data-field-email'),
            fieldType: el.attr('data-field-type'),
            fieldQuId: el.attr('data-field-qu_id'),
            fieldDepartment: el.attr('data-field-department'),
            fieldCollege: el.attr('data-field-college'),
            fieldFaculty: el.attr('data-field-faculty'),
            fieldIsActive: el.attr('data-field-is_active'),
            fieldPillarIds: el.attr('data-field-pillar_ids'),
        };
        $('#userModalForm')[0].reset();
        $('#userModalMethod').val('PUT');
        $('#userModalRecordId').val(data.fieldId || '');
        $('#userModalTitleText').text('Edit User');
        $('#userModalBtnText').text('Update User');
        $('#userModalForm').attr('action', '{{ route('users.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.fieldId));

        $('#userModal_name').val(data.fieldName || '');
        $('#userModal_email').val(data.fieldEmail || '');
        $('#userModal_type').val(data.fieldType || '');
        $('#userModal_qu_id').val(data.fieldQuId || '');
        $('#userModal_name, #userModal_email, #userModal_qu_id').prop('readonly', true);

        // College → dependent department preselection
        var collegeName = data.fieldCollege || '';
        if (collegeName && $('#userModal_college option[value="' + collegeName + '"]').length === 0) {
            $('#userModal_college').append($('<option>', { value: collegeName, text: collegeName + ' (current)' }));
        }
        $('#userModal_college').val(collegeName);
        populateDepartments(collegeName, data.fieldDepartment || '');

        $('#userModal_faculty').prop('checked', data.fieldFaculty == '1');
        $('#userModal_is_active').prop('checked', data.fieldIsActive == '1');

        // Pre-select pillars
        var pillarIds = [];
        try { pillarIds = JSON.parse(data.fieldPillarIds || '[]'); } catch(e) { pillarIds = []; }
        $('#userModal_pillar_ids').val(pillarIds);

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#userModal').modal('show');
    });

    // AJAX submit
    $('#userModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#userModalSubmitBtn');
        var method = $('#userModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#userModal').modal('hide');
                showToast('success', resp.message || 'User saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' User</span>');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"], [name="' + field + '[]"]');
                        if (input.length) {
                            input.addClass('is-invalid');
                            input.parent().append('<div class="invalid-feedback">' + msgs[0] + '</div>');
                        } else {
                            var firstArr = form.find('[name="' + field.replace('[]', '') + '[]"]');
                            if (firstArr.length) {
                                firstArr.addClass('is-invalid');
                                firstArr.parent().append('<div class="invalid-feedback">' + msgs[0] + '</div>');
                            }
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
        if (!confirm('Are you sure you want to delete this user?')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
