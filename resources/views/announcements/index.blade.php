@extends('layouts.app')

@section('title', 'Announcements - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-bullhorn"></i> Announcements</h1>
        <p>Latest news and updates from the system administrators.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Type:</label>
                    <select name="type" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>General</option>
                        <option value="important" {{ request('type') == 'important' ? 'selected' : '' }}>Important</option>
                        <option value="deadline" {{ request('type') == 'deadline' ? 'selected' : '' }}>Deadline</option>
                        <option value="update" {{ request('type') == 'update' ? 'selected' : '' }}>Update</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Audience:</label>
                    <select name="audience" id="audienceFilter">
                        <option value="">All Audiences</option>
                        <option value="All" {{ request('audience') == 'All' ? 'selected' : '' }}>All</option>
                        <option value="LPI" {{ request('audience') == 'LPI' ? 'selected' : '' }}>LPI</option>
                        <option value="Reviewer" {{ request('audience') == 'Reviewer' ? 'selected' : '' }}>Reviewer</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            @auth
            @if(auth()->user()->isAdmin())
            <button type="button" class="btn-primary btn-sm" data-modal-create="announcementModal" style="flex-shrink:0;">
                <i class="fas fa-plus"></i> New Announcement
            </button>
            @endif
            @endauth
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="announcementsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Audience</th>
                    <th>Message</th>
                    <th>Posted By</th>
                    <th>Expires At</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $announcement)
                <tr>
                    <td><code>{{ $announcement->id }}</code></td>
                    <td>
                        <span style="font-weight:500;">{{ $announcement->title }}</span>
                    </td>
                    <td>
                        @php
                            $typeStyles = [
                                'general' => ['icon' => 'fa-info-circle', 'class' => 'info'],
                                'important' => ['icon' => 'fa-exclamation-triangle', 'class' => 'danger'],
                                'deadline' => ['icon' => 'fa-clock', 'class' => 'warning'],
                                'update' => ['icon' => 'fa-sync-alt', 'class' => 'secondary'],
                            ];
                            $type = $announcement->type ?? 'general';
                            $ts = $typeStyles[$type] ?? $typeStyles['general'];
                        @endphp
                        <span class="pill {{ $ts['class'] }}">
                            <i class="fas {{ $ts['icon'] }}" style="font-size:10px;"></i> {{ ucfirst($type) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $audienceStyles = [
                                'All' => ['class' => 'success'],
                                'LPI' => ['class' => 'info'],
                                'Reviewer' => ['class' => 'secondary'],
                            ];
                            $aud = $announcement->audience ?? 'All';
                            $as = $audienceStyles[$aud] ?? $audienceStyles['All'];
                        @endphp
                        <span class="pill {{ $as['class'] }}">
                            <i class="fas fa-users" style="font-size:10px;"></i> {{ $aud }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:13px;color:var(--color-ink-600);">
                            {{ Str::limit($announcement->message, 80) }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size:13px;">
                            @if($announcement->createdBy)
                                {{ $announcement->createdBy->name }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </span>
                        <br>
                        <small style="color:var(--color-ink-400);">{{ $announcement->created_at->format('d-m-Y H:i') }}</small>
                    </td>
                    <td>
                        @if($announcement->expires_at)
                            <span class="text-nowrap" style="font-size:13px;">
                                <i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);"></i>
                                {{ $announcement->expires_at->format('d-m-Y') }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($announcement->is_active && (!$announcement->expires_at || $announcement->expires_at->isFuture()))
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-times-circle" style="font-size:10px;"></i> Expired</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            @auth
                            @if(auth()->user()->isAdmin())
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="announcementModal"
                                data-field-id="{{ $announcement->id }}"
                                data-field-title="{{ $announcement->title }}"
                                data-field-message="{{ $announcement->message }}"
                                data-field-type="{{ $announcement->type ?? 'general' }}"
                                data-field-audience="{{ $announcement->audience ?? 'All' }}"
                                data-field-expires_at="{{ $announcement->expires_at ? $announcement->expires_at->format('Y-m-d') : '' }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete Announcement">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                            @else
                            <span class="text-muted" style="font-size:11px;">&mdash;</span>
                            @endif
                            @endauth
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state py-4">
                            <i class="fas fa-bullhorn"></i>
                            <h5>No Announcements Found</h5>
                            <p>Use the <strong>New Announcement</strong> button at the top right to create one.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Announcement Modal --}}
<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('announcements.store') }}" id="announcementModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="announcementModalLabel">
                        <i class="fas fa-bullhorn me-2"></i>
                        <span id="announcementModalTitleText">New Announcement</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="announcementModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="announcementModalRecordId" value="">

                    <div class="row gx-3">
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <label for="announcementModal_title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="announcementModal_title" class="form-control" required placeholder="e.g. New Submission Deadline">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="announcementModal_type" class="form-label">Type</label>
                                <select name="type" id="announcementModal_type" class="form-select">
                                    <option value="general">General</option>
                                    <option value="important">Important</option>
                                    <option value="deadline">Deadline</option>
                                    <option value="update">Update</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="announcementModal_message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" id="announcementModal_message" class="form-control" rows="4" required placeholder="Write the announcement content..."></textarea>
                    </div>

                    <div class="row gx-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="announcementModal_audience" class="form-label">Audience</label>
                                <select name="audience" id="announcementModal_audience" class="form-select">
                                    <option value="All">All</option>
                                    <option value="LPI">LPI</option>
                                    <option value="Reviewer">Reviewer</option>
                                </select>
                                <small class="form-text text-muted">Who should see this announcement?</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="announcementModal_expires_at" class="form-label">Expires At</label>
                                <input type="date" name="expires_at" id="announcementModal_expires_at" class="form-control">
                                <small class="form-text text-muted">Leave empty for no expiry.</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="announcementModal_is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="announcementModal_is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="announcementModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="announcementModalBtnText">Create Announcement</span>
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
    @if($announcements->count() > 0)
    var table = $('#announcementsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [3, 4, 5] },
            { searchable: false, targets: [4, 5] }
        ]
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Type filter (column index 2)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var typeFilter = $('#typeFilter').val();
        if (!typeFilter) return true;
        var typeCell = $(table.cell(dataIndex, 2).node()).text().trim().toLowerCase();
        return typeCell.indexOf(typeFilter) !== -1;
    });

    $('#typeFilter').on('change', function() {
        table.draw();
    });

    // Audience filter (column index 3)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var audienceFilter = $('#audienceFilter').val();
        if (!audienceFilter) return true;
        var audienceCell = $(table.cell(dataIndex, 3).node()).text().trim();
        return audienceCell === audienceFilter;
    });

    $('#audienceFilter').on('change', function() {
        table.draw();
    });

    // Status filter (column index 7)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var statusFilter = $('#statusFilter').val();
        if (!statusFilter) return true;
        var statusCell = $(table.cell(dataIndex, 7).node()).text().trim();
        if (statusFilter === 'active' && statusCell.indexOf('Active') !== -1) return true;
        if (statusFilter === 'expired' && statusCell.indexOf('Expired') !== -1) return true;
        return false;
    });

    $('#statusFilter').on('change', function() {
        table.draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // Open create modal
    $(document).on('click', '[data-modal-create="announcementModal"]', function() {
        $('#announcementModalForm')[0].reset();
        $('#announcementModalMethod').val('POST');
        $('#announcementModalRecordId').val('');
        $('#announcementModalTitleText').text('New Announcement');
        $('#announcementModalBtnText').text('Create Announcement');
        $('#announcementModalForm').attr('action', '{{ route('announcements.store') }}');
        $('#announcementModal_is_active').prop('checked', true);
        $('#announcementModal_audience').val('All');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#announcementModal').modal('show');
    });

    // Open edit modal
    $(document).on('click', '[data-modal-edit="announcementModal"]', function() {
        var data = $(this).data();
        $('#announcementModalForm')[0].reset();
        $('#announcementModalMethod').val('PUT');
        $('#announcementModalRecordId').val(data.fieldId);
        $('#announcementModalTitleText').text('Edit Announcement');
        $('#announcementModalBtnText').text('Update Announcement');
        $('#announcementModalForm').attr('action', '{{ route('announcements.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.fieldId));
        $('#announcementModal_title').val(data.fieldTitle || '');
        $('#announcementModal_message').val(data.fieldMessage || '');
        $('#announcementModal_type').val(data.fieldType || 'general');
        $('#announcementModal_audience').val(data.fieldAudience || 'All');
        $('#announcementModal_expires_at').val(data.fieldExpiresAt || '');
        $('#announcementModal_is_active').prop('checked', true);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#announcementModal').modal('show');
    });

    // AJAX submit
    $('#announcementModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#announcementModalSubmitBtn');
        var method = $('#announcementModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#announcementModal').modal('hide');
                showToast('success', resp.message || 'Announcement saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Announcement</span>');
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
