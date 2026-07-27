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
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colleges as $college)
                <tr>
                    <td><code>{{ $college->id }}</code></td>
                    <td><code>{{ $college->code }}</code></td>
                    <td>
                        <span class="pill primary" style="font-size:13px;padding:4px 12px;">
                            <i class="fas fa-university" style="font-size:11px;"></i> {{ $college->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="collegeModal"
                                data-field-id="{{ $college->id }}"
                                data-field-code="{{ $college->code }}"
                                data-field-name="{{ $college->name }}">
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
                    <td colspan="4">
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

{{-- College Modal --}}
@include('partials.modal-form', [
    'modalId' => 'collegeModal',
    'title' => 'College',
    'icon' => 'university',
    'fields' => [
        ['name' => 'code', 'label' => 'College Code', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. ENG'],
        ['name' => 'name', 'label' => 'College Name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. College of Engineering'],
    ],
    'storeRoute' => 'colleges.store',
    'updateRoute' => 'colleges.update',
    'size' => 'sm',
])
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($colleges->count() > 0)
    var table = $('#collegesTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [3] },
            { searchable: false, targets: [3] }
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
