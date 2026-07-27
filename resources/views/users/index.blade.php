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
                        <option value="PI">PI</option>
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
                    <th>Department</th>
                    <th>College</th>
                    <th>Active</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
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
                    <td>{{ $user->department ?? '—' }}</td>
                    <td>{{ $user->college ?? '—' }}</td>
                    <td>
                        @if($user->is_active)
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-times-circle" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="userModal"
                                data-field-id="{{ $user->id }}"
                                data-field-name="{{ $user->name }}"
                                data-field-email="{{ $user->email }}"
                                data-field-type="{{ $user->type }}"
                                data-field-qu_id="{{ $user->qu_id }}"
                                data-field-nationality_id="{{ $user->nationality_id }}"
                                data-field-phone="{{ $user->phone }}"
                                data-field-department="{{ $user->department }}"
                                data-field-college="{{ $user->college }}"
                                data-field-faculty="{{ $user->faculty ? '1' : '0' }}"
                                data-field-is_active="{{ $user->is_active ? '1' : '0' }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete User">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
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
        <div class="modal-content">
            <form method="POST" action="{{ route('users.store') }}" id="userModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        <span id="userModalTitleText">New User</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="userModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="userModalRecordId" value="">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="userModal_name" class="form-control" required placeholder="e.g. John Doe">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="userModal_email" class="form-control" required placeholder="e.g. johndoe@qu.edu.qa">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_type" class="form-label">User Type <span class="text-danger">*</span></label>
                                <select name="type" id="userModal_type" class="form-select" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="LPI">LPI</option>
                                    <option value="Reviewer">Reviewer</option>
                                    <option value="LPI+Reviewer">LPI+Reviewer</option>
                                    <option value="PI">PI</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_qu_id" class="form-label">QU ID</label>
                                <input type="text" name="qu_id" id="userModal_qu_id" class="form-control" placeholder="e.g. 2018XXXXX">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_password" class="form-label">
                                    Password
                                    <span class="text-danger" id="userModalPwdRequired">*</span>
                                    <small style="color:var(--color-ink-400);display:none;" id="userModalPwdHelp">(leave blank to keep current)</small>
                                </label>
                                <input type="password" name="password" id="userModal_password" class="form-control" minlength="8" placeholder="Min. 8 characters">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="userModal_password_confirmation" class="form-control" placeholder="Repeat password">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_nationality_id" class="form-label">Nationality</label>
                                <select name="nationality_id" id="userModal_nationality_id" class="form-select">
                                    <option value="">-- Select Nationality --</option>
                                    @foreach($nationalities as $nat)
                                        <option value="{{ $nat->id }}">{{ $nat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="userModal_phone" class="form-control" placeholder="e.g. +974 1234 5678">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_department" class="form-label">Department</label>
                                <input type="text" name="department" id="userModal_department" class="form-control" placeholder="e.g. Computer Science">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="userModal_college" class="form-label">College</label>
                                <input type="text" name="college" id="userModal_college" class="form-control" placeholder="e.g. College of Engineering">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label d-block">Roles & Status</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="faculty" value="0">
                                    <input type="checkbox" name="faculty" id="userModal_faculty" class="form-check-input" value="1" checked>
                                    <label class="form-check-label" for="userModal_faculty">Faculty Member</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" id="userModal_is_active" class="form-check-input" value="1" checked>
                                    <label class="form-check-label" for="userModal_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="userModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="userModalBtnText">Create User</span>
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

    // CREATE modal
    $(document).on('click', '[data-modal-create="userModal"]', function() {
        $('#userModalForm')[0].reset();
        $('#userModalMethod').val('POST');
        $('#userModalRecordId').val('');
        $('#userModalTitleText').text('New User');
        $('#userModalBtnText').text('Create User');
        $('#userModalForm').attr('action', '{{ route('users.store') }}');
        $('#userModalPwdRequired').show();
        $('#userModalPwdHelp').hide();
        $('#userModal_password').prop('required', true);
        $('#userModal_password_confirmation').prop('required', true);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#userModal').modal('show');
    });

    // EDIT modal
    $(document).on('click', '[data-modal-edit="userModal"]', function() {
        var data = $(this).data();
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
        $('#userModal_nationality_id').val(data.fieldNationalityId || '');
        $('#userModal_phone').val(data.fieldPhone || '');
        $('#userModal_department').val(data.fieldDepartment || '');
        $('#userModal_college').val(data.fieldCollege || '');
        $('#userModal_faculty').prop('checked', data.fieldFaculty == '1');
        $('#userModal_is_active').prop('checked', data.fieldIsActive == '1');
        $('#userModal_password').prop('required', false).val('');
        $('#userModal_password_confirmation').prop('required', false).val('');
        $('#userModalPwdRequired').hide();
        $('#userModalPwdHelp').show();

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
