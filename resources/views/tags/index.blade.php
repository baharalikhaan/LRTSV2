@extends('layouts.app')

@section('title', 'Tags - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-tags"></i> Tags</h1>
        <p>Create tags to categorize projects and users.</p>
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
            <button type="button" class="btn-primary btn-sm" data-modal-create="tagModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Tag
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="tagsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tag Name</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tags as $tag)
                <tr>
                    <td><code>{{ $tag->id }}</code></td>
                    <td>
                        <span class="pill primary" style="font-size:13px;padding:4px 12px;">
                            <i class="fas fa-tag" style="font-size:11px;"></i> {{ $tag->tag }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                data-modal-edit="tagModal"
                                data-field-id="{{ $tag->id }}"
                                data-field-tag="{{ $tag->tag }}">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </button>
                            <form action="{{ route('tags.destroy', $tag->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);" title="Delete Tag">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">
                        <div class="empty-state py-4">
                            <i class="fas fa-tags"></i>
                            <h5>No Tags Found</h5>
                            <p>Create tags to categorize projects and users.</p>
                            <button type="button" class="btn-primary mt-2" data-modal-create="tagModal">
                                <i class="fas fa-plus"></i> New Tag
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tag Modal --}}
@include('partials.modal-form', [
    'modalId' => 'tagModal',
    'title' => 'Tag',
    'icon' => 'tag',
    'fields' => [
        ['name' => 'tag', 'label' => 'Tag Name', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. Computational Biology'],
    ],
    'storeRoute' => 'tags.store',
    'updateRoute' => 'tags.update',
    'size' => 'sm',
])
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($tags->count() > 0)
    var table = $('#tagsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [2] },
            { searchable: false, targets: [2] }
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

    $('#tagModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#tagModalSubmitBtn');
        var method = $('#tagModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#tagModal').modal('hide');
                showToast('success', resp.message || 'Tag saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Tag</span>');
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
