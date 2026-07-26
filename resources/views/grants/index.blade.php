@extends('layouts.app')

@section('title', 'Grant Types - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-trophy"></i> Grant Types</h1>
        <p>Manage research grant types and funding opportunities.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <form method="GET" class="filter-bar" id="filterForm" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Category:</label>
                    <select name="category" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="student" {{ request('category') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="regular" {{ request('category') == 'regular' ? 'selected' : '' }}>Regular</option>
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
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
            @if(Auth::user()->isAdmin())
            <button type="button" class="btn-primary btn-sm" data-modal-create="grantModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Grant Type
            </button>
            @endif
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="grantsTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Grant Type Name</th>
                    <th>Category</th>
                    <th>Funding Agency</th>
                    <th>Max Years</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grants as $grant)
                <tr>
                    <td><code>{{ $grant->grant_code }}</code></td>
                    <td>
                        <div style="font-weight:500;">{{ $grant->grant_name }}</div>
                        @if($grant->description)
                            <div style="font-size:11px; color: var(--color-ink-400); font-style: italic; margin-top: 2px; line-height:1.3;">{{ Str::limit($grant->description, 80) }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="pill {{ $grant->category == 'student' ? 'info' : 'primary' }}">
                            {{ ucfirst($grant->category) }}
                        </span>
                    </td>
                    <td>{{ $grant->funding_agency ?? '—' }}</td>
                    <td>{{ $grant->max_duration_years ?? '—' }}</td>
                    <td>
                        @if($grant->is_active ?? true)
                            <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive"><i class="fas fa-minus-circle" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="action-group">
                            <a href="{{ route('grant-types.show', $grant->id) }}" class="row-action" title="View Grant Type" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(Auth::user()->isAdmin())
                            <button type="button"
                                class="row-action"
                                title="Edit Grant Type"
                                data-bs-toggle="tooltip"
                                data-modal-edit="grantModal"
                                data-field-id="{{ $grant->id }}"
                                data-field-grant_code="{{ $grant->grant_code }}"
                                data-field-grant_name="{{ $grant->grant_name }}"
                                data-field-category="{{ $grant->category }}"
                                data-field-funding_agency="{{ $grant->funding_agency }}"
                                data-field-max_duration_years="{{ $grant->max_duration_years }}"
                                data-field-description="{{ $grant->description }}"
                                data-field-is_active="{{ $grant->is_active ? '1' : '0' }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state py-4">
                            <i class="fas fa-trophy"></i>
                            <h5>No Grant Types Found</h5>
                            <p>Get started by creating a new grant type.</p>
                            @if(Auth::user()->isAdmin())
                            <button type="button" class="btn-primary mt-2" data-modal-create="grantModal">
                                <i class="fas fa-plus"></i> New Grant Type
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

{{-- Grant Type Modal --}}
@include('partials.modal-form', [
    'modalId' => 'grantModal',
    'title' => 'Grant Type',
    'icon' => 'trophy',
    'fields' => [
        ['name' => 'grant_code', 'label' => 'Grant Type Code', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. CG-2025-001'],
        ['name' => 'grant_name', 'label' => 'Grant Type Name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Competitive Grant 2025'],
        ['name' => 'category', 'label' => 'Category', 'type' => 'select', 'required' => true, 'options' => ['student' => 'Student', 'regular' => 'Regular']],
        ['name' => 'funding_agency', 'label' => 'Funding Agency', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. QNRF'],
        ['name' => 'max_duration_years', 'label' => 'Max Duration (Years)', 'type' => 'number', 'required' => false, 'placeholder' => 'e.g. 3', 'min' => 1, 'max' => 10],
        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Optional description...', 'rows' => 3],
        ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'checked' => true],
    ],
    'storeRoute' => 'grant-types.store',
    'updateRoute' => 'grant-types.update',
    'size' => 'lg',
])
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($grants->count() > 0)
    var table = $('#grantsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] },
            { searchable: false, targets: [6] }
        ]
    });

    // Connect custom search input to DataTables search
    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Category filter — custom DataTable filter on column index 2 (Category)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var categoryFilter = $('#categoryFilter').val();
        if (!categoryFilter) return true;

        var categoryCell = $(table.cell(dataIndex, 2).node()).text().trim().toLowerCase();
        return categoryCell.indexOf(categoryFilter) !== -1;
    });

    // Status filter — custom DataTable filter on column index 5 (Status)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var statusFilter = $('#statusFilter').val();
        if (!statusFilter) return true;

        var statusCell = $(table.cell(dataIndex, 5).node()).text().trim();
        if (statusFilter === 'active' && statusCell.indexOf('Active') !== -1) return true;
        if (statusFilter === 'inactive' && statusCell.indexOf('Inactive') !== -1) return true;
        return false;
    });

    // Bind filter dropdowns
    $('#categoryFilter, #statusFilter').on('change', function() {
        table.draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    // Grant modal: AJAX submit
    $('#grantModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#grantModalSubmitBtn');
        var method = $('#grantModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#grantModal').modal('hide');
                showToast('success', resp.message || 'Grant Type saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Grant Type</span>');
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

function showToast(type, message) {
    var bg = type === 'success' ? '#1f8a5f' : '#b3261e';
    var toast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999"><div class="toast align-items-center text-white border-0" style="background:' + bg + '" role="alert"><div class="d-flex"><div class="toast-body"><i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>');
    $('body').append(toast);
    var toastEl = new bootstrap.Toast(toast.find('.toast')[0], { delay: 4000 });
    toastEl.show();
    setTimeout(function() { toast.remove(); }, 4500);
}
</script>
@endpush
