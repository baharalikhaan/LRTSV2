@extends('layouts.app')

@section('title', 'Team Management - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-users-gear"></i> Team Management</h1>
        <p>Manage team members displayed on the "Our Team" page.</p>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search by name, role, email..." class="search-input">
                </div>
            </form>
            <button type="button" class="btn-primary btn-sm" data-modal-create="teamModal" style="flex-shrink:0;">
                <i class="fas fa-plus"></i> Add Member
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="teamsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th class="text-center" style="min-width:120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teamMembers as $m)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @php
                                $initials = collect(explode(' ', $m->name))->map(function($w) { return mb_substr($w, 0, 1); })->take(2)->implode('');
                                $colors = ['brand','info','gold','success'];
                                $c = $colors[($m->id - 1) % count($colors)];
                                $bgMap = ['brand'=>'#fdf2f4','info'=>'#eff6ff','gold'=>'#fffbeb','success'=>'#ecfdf5'];
                                $fgMap = ['brand'=>'var(--color-brand-600)','info'=>'#1d4ed8','gold'=>'#b45309','success'=>'#047857'];
                            @endphp
                            <div style="width:34px;height:34px;border-radius:6px;background:{{ $bgMap[$c] }};color:{{ $fgMap[$c] }};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;">
                                {{ $initials }}
                            </div>
                            <div>
                                <div style="font-weight:500;">{{ $m->name }}</div>
                                @if($m->introduction)
                                    <div style="font-size:11px;color:var(--color-ink-400);margin-top:1px;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($m->introduction, 60) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($m->role)
                            <span class="pill info">{{ $m->role }}</span>
                        @else
                            <span style="font-size:12px;color:var(--color-ink-400);">&mdash;</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;">{{ $m->email ?? '—' }}</td>
                    <td style="font-size:12.5px;">{{ $m->phone ?? '—' }}</td>
                    <td style="font-size:12.5px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $m->address ?? '—' }}</td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="teamModal"
                                data-field-id="{{ $m->id }}"
                                data-field-name="{{ $m->name }}"
                                data-field-role="{{ $m->role }}"
                                data-field-introduction="{{ $m->introduction }}"
                                data-field-email="{{ $m->email }}"
                                data-field-phone="{{ $m->phone }}"
                                data-field-address="{{ $m->address }}"
                                data-field-path="{{ $m->path }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            <form action="{{ route('teams.destroy', $m->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state py-4">
                            <i class="fas fa-users"></i>
                            <h5>No Team Members</h5>
                            <p>Click <strong>Add Member</strong> to add your first team member.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Team Modal --}}
<div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('teams.store') }}" id="teamModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="teamModalLabel">
                        <i class="fas fa-user-plus me-2"></i>
                        <span id="teamModalTitleText">Add Team Member</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="teamModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="teamModalRecordId" value="">

                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="teamModal_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="teamModal_name" class="form-control" required placeholder="e.g. Dr. Ahmed Ali">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="teamModal_role" class="form-label">Role / Title</label>
                                <input type="text" name="role" id="teamModal_role" class="form-control" placeholder="e.g. System Administrator">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="teamModal_introduction" class="form-label">Introduction</label>
                        <textarea name="introduction" id="teamModal_introduction" class="form-control" rows="3" placeholder="Brief bio or description..."></textarea>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="teamModal_email" class="form-label">Email</label>
                                <input type="email" name="email" id="teamModal_email" class="form-control" placeholder="e.g. ahmed@qu.edu.qa">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="teamModal_phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="teamModal_phone" class="form-control" placeholder="e.g. +974 4400 0000">
                            </div>
                        </div>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label for="teamModal_address" class="form-label">Address / Office</label>
                                <input type="text" name="address" id="teamModal_address" class="form-control" placeholder="e.g. Building C, Room 301">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="teamModal_path" class="form-label">Photo URL</label>
                                <input type="text" name="path" id="teamModal_path" class="form-control" placeholder="Optional image path">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="teamModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="teamModalSubmitText">Save Member</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if($teamMembers->count() > 0)
    var table = $('#teamsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [5] }],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // Reset modal on close
    $('#teamModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('#teamModalMethod').val('POST');
        $(this).find('#teamModalRecordId').val('');
        $(this).find('#teamModalTitleText').text('Add Team Member');
        $(this).find('#teamModalSubmitText').text('Save Member');
        $(this).find('form').attr('action', '{{ route("teams.store") }}');
    });

    // Create mode
    $(document).on('click', '[data-modal-create="teamModal"]', function() {
        var modal = $('#teamModal');
        modal.find('form')[0].reset();
        modal.find('#teamModalMethod').val('POST');
        modal.find('#teamModalRecordId').val('');
        modal.find('#teamModalTitleText').text('Add Team Member');
        modal.find('#teamModalSubmitText').text('Save Member');
        modal.find('form').attr('action', '{{ route("teams.store") }}');
        modal.modal('show');
    });

    // Edit mode
    $(document).on('click', '[data-modal-edit="teamModal"]', function() {
        var btn = $(this);
        var modal = $('#teamModal');
        modal.find('form')[0].reset();
        modal.find('#teamModalMethod').val('PUT');
        modal.find('#teamModalRecordId').val(btn.data('field-id'));
        modal.find('#teamModal_name').val(btn.data('field-name'));
        modal.find('#teamModal_role').val(btn.data('field-role'));
        modal.find('#teamModal_introduction').val(btn.data('field-introduction'));
        modal.find('#teamModal_email').val(btn.data('field-email'));
        modal.find('#teamModal_phone').val(btn.data('field-phone'));
        modal.find('#teamModal_address').val(btn.data('field-address'));
        modal.find('#teamModal_path').val(btn.data('field-path'));
        modal.find('#teamModalTitleText').text('Edit Team Member');
        modal.find('#teamModalSubmitText').text('Update Member');
        modal.find('form').attr('action', '/teams/' + btn.data('field-id'));
        modal.modal('show');
    });

    // AJAX form submit
    $('#teamModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#teamModalSubmitBtn');
        var url = form.attr('action');
        var method = $('#teamModalMethod').val();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize() + (method === 'PUT' ? '&_method=PUT' : ''),
            dataType: 'json',
            success: function(resp) {
                $('#teamModal').modal('hide');
                showToast('success', resp.message || 'Saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span id="teamModalSubmitText">Save</span>');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                    });
                } else {
                    showToast('error', 'An error occurred. Please try again.');
                }
            }
        });
    });
});
</script>
@endpush
