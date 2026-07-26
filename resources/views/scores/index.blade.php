@extends('layouts.app')

@section('title', 'Scores - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-star"></i> Scores</h1>
        <p>Score values used in grading.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search scores..." class="search-input">
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100 compact" id="scoresTable" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Label</th>
                    <th>Value</th>
                    <th class="text-center" style="min-width: 60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scores as $score)
                <tr>
                    <td>
                        <span style="font-weight:500;font-size:12.5px;">{{ $score->name }}</span>
                        @if($score->description)
                            <br><small style="color:var(--color-ink-400);font-size:11px;">{{ $score->description }}</small>
                        @endif
                    </td>
                    <td>
                        @if($score->label)
                            <span class="pill info" style="font-size:11px;padding:1px 6px;">{{ $score->label }}</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-weight:500;font-size:12.5px;">{{ number_format($score->value, 2) }}</td>
                    <td class="text-center">
                        <button type="button"
                            class="row-action"
                            title="Edit Score"
                            data-bs-toggle="tooltip"
                            data-modal-edit="scoreModal"
                            data-field-id="{{ $score->id }}"
                            data-field-name="{{ $score->name }}"
                            data-field-label="{{ $score->label }}"
                            data-field-value="{{ $score->value }}">
                            <i class="fas fa-edit" style="font-size:13px;"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state py-4">
                            <i class="fas fa-star"></i>
                            <h5>No Scores Found</h5>
                            <p>No score values have been defined yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Edit Score Modal --}}
<div class="modal fade" id="scoreModal" tabindex="-1" aria-labelledby="scoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="" id="scoreModalForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="scoreModalLabel">
                        <i class="fas fa-star me-2"></i>
                        <span id="scoreModalTitleText">Edit Score</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="record_id" id="scoreModalRecordId" value="">

                    {{-- Name (read-only display) --}}
                    <div class="form-group mb-3">
                        <label class="form-label">Name</label>
                        <p class="form-control-plaintext" id="scoreModal_name_display" style="font-weight:500;padding-top:6px;"></p>
                    </div>

                    {{-- Label --}}
                    <div class="form-group mb-3">
                        <label for="scoreModal_label" class="form-label">Label</label>
                        <input type="text" name="label" id="scoreModal_label" class="form-control" placeholder="e.g. A" maxlength="20">
                    </div>

                    {{-- Value --}}
                    <div class="form-group mb-3">
                        <label for="scoreModal_value" class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" name="value" id="scoreModal_value" class="form-control" step="0.01" min="0" max="999.99" required placeholder="e.g. 5.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="scoreModalSubmitBtn">
                        <i class="fas fa-save"></i> Update Score
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
#scoresTable td, #scoresTable th {
    padding: 6px 10px !important;
}
</style>
<script>
$(document).ready(function() {
    @if($scores->count() > 0)
    var table = $('#scoresTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[2, 'desc']],
        pageLength: 25,
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();

    $('#scoreModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#scoreModalSubmitBtn');
        var id = $('#scoreModalRecordId').val();
        var url = '{{ route("scores.update", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: 'PUT',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#scoreModal').modal('hide');
                showToast('success', resp.message || 'Score updated successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Score');
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

// On edit, populate modal
$(document).on('click', '[data-modal-edit="scoreModal"]', function() {
    var btn = $(this);
    var id = btn.data('field-id');
    var name = btn.data('field-name');
    var label = btn.data('field-label');
    var value = btn.data('field-value');

    $('#scoreModalForm')[0].reset();
    $('#scoreModalRecordId').val(id);
    $('#scoreModal_name_display').text(name);
    $('#scoreModal_label').val(label || '');
    $('#scoreModal_value').val(value);

    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    $('#scoreModal').modal('show');
});
</script>
@endpush
