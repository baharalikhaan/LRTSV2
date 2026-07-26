@extends('layouts.app')

@section('title', 'Pillars - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-columns"></i> Pillars</h1>
        <p>Define evaluation pillars for grading criteria.</p>
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
            <button type="button" class="btn-primary btn-sm" data-modal-create="pillarModal" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Pillar
            </button>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="pillarsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pillar</th>
                    <th>Sub-Pillars</th>
                    <th class="text-center" style="min-width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pillars as $pillar)
                <tr>
                    <td><code>{{ $pillar->id }}</code></td>
                    <td>
                        <span style="font-weight:500;">{{ $pillar->pillar }}</span>
                        @if($pillar->description)
                            <br><small style="color:var(--color-ink-400);">{{ Str::limit($pillar->description, 80) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($pillar->subpillar)
                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                @foreach(explode("\n", $pillar->subpillar) as $sp)
                                    @php $sp = trim($sp); @endphp
                                    @if($sp)
                                        <span style="display:inline-block; font-size:11px; font-weight:400; font-style:italic; color:#000; background:#fbeef1; padding:2px 8px; border-radius:10px; border:1px solid #f3d2da;">{{ $sp }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center" style="min-width:130px;">
                        <div class="action-group">
                            <button type="button"
                                class="row-action"
                                title="Edit Pillar"
                                data-bs-toggle="tooltip"
                                data-modal-edit="pillarModal"
                                data-field-id="{{ $pillar->id }}"
                                data-field-pillar="{{ $pillar->pillar }}"
                                data-field-subpillar="{{ $pillar->subpillar }}"
                                data-field-description="{{ $pillar->description }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('pillars.destroy', $pillar->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="row-action" title="Delete Pillar" data-bs-toggle="tooltip" style="color:var(--color-danger);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state py-4">
                            <i class="fas fa-columns"></i>
                            <h5>No Pillars Found</h5>
                            <p>Define evaluation pillars for grading criteria.</p>
                            <button type="button" class="btn-primary mt-2" data-modal-create="pillarModal">
                                <i class="fas fa-plus"></i> New Pillar
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pillar Modal --}}
<div class="modal fade" id="pillarModal" tabindex="-1" aria-labelledby="pillarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('pillars.store') }}" id="pillarModalForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="pillarModalLabel">
                        <i class="fas fa-columns me-2"></i>
                        <span id="pillarModalTitleText">New Pillar</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="pillarModalMethod" value="POST">
                    <input type="hidden" name="record_id" id="pillarModalRecordId" value="">
                    <input type="hidden" name="subpillar" id="pillarModal_subpillar" value="">

                    {{-- Pillar Name --}}
                    <div class="form-group mb-3">
                        <label for="pillarModal_pillar" class="form-label">Pillar Name <span class="text-danger">*</span></label>
                        <input type="text" name="pillar" id="pillarModal_pillar" class="form-control" required placeholder="e.g. Research Quality">
                    </div>

                    {{-- Sub-Pillars tag input --}}
                    <div class="form-group mb-3">
                        <label class="form-label">Sub-Pillars</label>
                        <div id="subpillarTagsContainer" class="d-flex flex-wrap gap-1 mb-2"></div>
                        <div class="input-group input-group-sm" style="margin-top:6px;">
                            <input type="text" id="subpillarInput" class="form-control" placeholder="Type sub-pillar and press Enter or Add" style="font-size:12px;">
                            <button type="button" id="addSubpillarBtn" class="btn btn-primary" style="font-size:12px; padding:2px 10px; border-radius:0 6px 6px 0;">Add</button>
                        </div>
                        <small class="form-text text-muted" style="margin-top:4px; display:block;">Add each sub-pillar individually. Click ✕ on a chip to remove it.</small>
                    </div>

                    {{-- Description --}}
                    <div class="form-group mb-3">
                        <label for="pillarModal_description" class="form-label">Description</label>
                        <textarea name="description" id="pillarModal_description" class="form-control" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="pillarModalSubmitBtn">
                        <i class="fas fa-save"></i> <span id="pillarModalBtnText">Create Pillar</span>
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
    @if($pillars->count() > 0)
    var table = $('#pillarsTable').DataTable({
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

    $('#pillarModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#pillarModalSubmitBtn');
        var method = $('#pillarModalMethod').val();
        var url = form.attr('action');

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: url,
            method: method === 'PUT' ? 'PUT' : 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                $('#pillarModal').modal('hide');
                showToast('success', resp.message || 'Pillar saved successfully!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> <span>' + (method === 'PUT' ? 'Update' : 'Create') + ' Pillar</span>');
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

// ---- Sub-pillar tag input helper ----
var subpillarTags = [];

function renderSubpillarTags() {
    var container = $('#subpillarTagsContainer');
    container.empty();
    subpillarTags.forEach(function(tag, index) {
        var chip = $(
            '<span class="d-inline-flex align-items-center" style="font-size:11px; font-weight:400; font-style:italic; color:#000; background:#fbeef1; padding:3px 8px; border-radius:10px; border:1px solid #f3d2da; gap:4px;">' +
                $('<span>').text(tag).html() +
                '<button type="button" style="background:none; border:none; cursor:pointer; font-size:13px; line-height:1; color:#8b8592; padding:0 0 0 2px;" data-index="' + index + '" title="Remove">&#10005;</button>' +
            '</span>'
        );
        container.append(chip);
    });
    syncSubpillarField();
}

function syncSubpillarField() {
    $('#pillarModal_subpillar').val(subpillarTags.join('\n'));
}

// Add sub-pillar
$('#addSubpillarBtn').on('click', function() {
    addSubpillarTag();
});

$('#subpillarInput').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addSubpillarTag();
    }
});

function addSubpillarTag() {
    var input = $('#subpillarInput');
    var val = input.val().trim();
    if (val) {
        subpillarTags.push(val);
        renderSubpillarTags();
        input.val('').focus();
    }
}

// Remove sub-pillar via event delegation (match the ✕ button)
$(document).on('click', '#subpillarTagsContainer button[data-index]', function() {
    var index = $(this).data('index');
    subpillarTags.splice(index, 1);
    renderSubpillarTags();
});

// On edit, load existing subpillars into tags
$(document).on('click', '[data-modal-edit="pillarModal"]', function() {
    var data = $(this).data();
    $('[data-field-subpillar]').each(function() {});
    var btn = $(this);
    var id = btn.data('field-id');
    var pillar = btn.data('field-pillar');
    var subpillar = btn.data('field-subpillar');
    var description = btn.data('field-description');

    $('#pillarModalForm')[0].reset();
    $('#pillarModal_subpillar').val('');
    $('#pillarModalMethod').val('PUT');
    $('#pillarModalRecordId').val(id);
    $('#pillarModalTitleText').text('Edit Pillar');
    $('#pillarModalBtnText').text('Update Pillar');
    $('#pillarModalForm').attr('action', '{{ route("pillars.update", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id));
    $('#pillarModal_pillar').val(pillar);
    $('#pillarModal_description').val(description || '');

    // Load subpillars
    subpillarTags = [];
    if (subpillar) {
        var lines = subpillar.split('\n');
        lines.forEach(function(line) {
            line = line.trim();
            if (line) subpillarTags.push(line);
        });
    }
    renderSubpillarTags();

    // Remove validation feedback
    $('#pillarModal .is-invalid').removeClass('is-invalid');
    $('#pillarModal .invalid-feedback').remove();
    $('#pillarModal').modal('show');
});

// On create, reset tags
$(document).on('click', '[data-modal-create="pillarModal"]', function() {
    $('#pillarModalForm')[0].reset();
    $('#pillarModal_subpillar').val('');
    $('#pillarModalMethod').val('POST');
    $('#pillarModalRecordId').val('');
    $('#pillarModalTitleText').text('New Pillar');
    $('#pillarModalBtnText').text('Create Pillar');
    $('#pillarModalForm').attr('action', '{{ route("pillars.store") }}');
    subpillarTags = [];
    renderSubpillarTags();

    $('#pillarModal .is-invalid').removeClass('is-invalid');
    $('#pillarModal .invalid-feedback').remove();
    $('#pillarModal').modal('show');
});
</script>
@endpush
