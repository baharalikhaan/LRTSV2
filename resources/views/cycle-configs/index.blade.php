@extends('layouts.app')

@section('title', 'Cycles - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-calendar-alt"></i> Cycles</h1>
        <p>Manage academic cycles — years and titles.</p>
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
            <button type="button" class="btn-primary btn-sm" data-modal-create="cycleConfigModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Cycle
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="cycleConfigsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Year</th>
                    <th>Title</th>
                    <th class="text-center"># Research Calls</th>
                    <th class="text-center" style="min-width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cycleConfigs as $cc)
                <tr>
                    <td><code>{{ $cc->id }}</code></td>
                    <td><span class="fw-medium">{{ $cc->year }}</span></td>
                    <td>
                        <span style="font-weight:500;">{{ $cc->title }}</span>
                    </td>
                    <td class="text-center">
                        <span class="pill pill-info">{{ $cc->programs_count }}</span>
                    </td>
                    <td class="text-center">
                        <div class="action-group">
                            <button type="button"
                                class="row-action"
                                title="Edit Cycle"
                                data-bs-toggle="tooltip"
                                data-modal-edit="cycleConfigModal"
                                data-field-id="{{ $cc->id }}"
                                data-field-year="{{ $cc->year }}"
                                data-field-title="{{ $cc->title }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('cycle-configs.destroy', $cc->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="row-action" title="Delete Cycle" data-bs-toggle="tooltip" style="color:var(--color-danger);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state py-4">
                            <i class="fas fa-calendar-alt"></i>
                            <h5>No Cycles Found</h5>
                            <p>Add cycles to define academic years.</p>
                            <button type="button" class="btn-primary mt-2" data-modal-create="cycleConfigModal">
                                <i class="fas fa-plus"></i> New Cycle
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Cycle Config Modal --}}
@include('partials.modal-form', [
    'modalId' => 'cycleConfigModal',
    'title' => 'Cycle',
    'icon' => 'calendar-alt',
    'fields' => [
        ['name' => 'year', 'label' => 'Year', 'type' => 'number', 'required' => true, 'placeholder' => 'e.g. 2026', 'min' => 2000, 'max' => 2099],
        ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Academic Year 2026-2027'],
    ],
    'storeRoute' => 'cycle-configs.store',
    'updateRoute' => 'cycle-configs.update',
    'size' => 'sm',
])
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($cycleConfigs->count() > 0)
    var table = $('#cycleConfigsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] },
            { searchable: false, targets: [5] }
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

    $('#cycleConfigModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#cycleConfigModalSubmitBtn');
        var method = $('#cycleConfigModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#cycleConfigModal').modal('hide');
                showToast('success', resp.message || 'Cycle saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Cycle</span>');
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
    var toastEl = new bootstrap.Toast(toast.find('.toast')[0], { delay: 3000 });
    toastEl.show();
    setTimeout(function() { toast.remove(); }, 3500);
}
</script>
@endpush
