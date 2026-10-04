{{--
====================================================================
  Modal Form Partial – Reusable for Create/Edit operations
  Usage:
    @include('partials.modal-form', [
        'modalId' => 'pillarModal',
        'title' => 'Pillar',
        'fields' => [
            ['name' => 'pillar_name', 'label' => 'Pillar Name', 'type' => 'text', 'required' => true],
            ['name' => 'max_score', 'label' => 'Max Score', 'type' => 'number', 'required' => true],
        ],
        'storeRoute' => 'pillars.store',
        'updateRoute' => 'pillars.update',  // with {pillar} param
        'size' => 'md', // sm, md, lg, xl
    ])
====================================================================
--}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-{{ $size ?? 'lg' }} modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route($storeRoute) }}" id="{{ $modalId }}Form" enctype="{{ isset($multipart) && $multipart ? 'multipart/form-data' : 'application/x-www-form-urlencoded' }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        <i class="fas fa-{{ $icon ?? 'plus-circle' }} me-2"></i>
                        <span id="{{ $modalId }}TitleText">New {{ $title }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Hidden ID field for edit mode --}}
                    <input type="hidden" name="_method" id="{{ $modalId }}Method" value="POST">
                    <input type="hidden" name="record_id" id="{{ $modalId }}RecordId" value="">

                    @foreach($fields as $field)
                        @php
                            $fid = $modalId . '_' . $field['name'];
                            $required = $field['required'] ?? false;
                            $fieldType = $field['type'] ?? 'text';
                        @endphp
                        <div class="form-group mb-3">
                            <label for="{{ $fid }}" class="form-label">
                                {{ $field['label'] }}
                                @if($required) <span class="text-danger">*</span> @endif
                            </label>

                            @if($fieldType === 'select')
                                <select name="{{ $field['name'] }}" id="{{ $fid }}"
                                    class="form-select @error($field['name']) is-invalid @enderror"
                                    {{ $required ? 'required' : '' }}>
                                    <option value="">-- {{ $field['placeholder'] ?? 'Select ' . $field['label'] }} --</option>
                                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                                        <option value="{{ $optValue }}">{{ $optLabel }}</option>
                                    @endforeach
                                </select>

                            @elseif($fieldType === 'textarea')
                                <textarea name="{{ $field['name'] }}" id="{{ $fid }}"
                                    class="form-control @error($field['name']) is-invalid @enderror"
                                    rows="{{ $field['rows'] ?? 3 }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    {{ $required ? 'required' : '' }}></textarea>

                            @elseif($fieldType === 'checkbox')
                                <div class="form-check form-switch">
                                    <input type="hidden" name="{{ $field['name'] }}" value="0">
                                    <input type="checkbox" name="{{ $field['name'] }}" id="{{ $fid }}"
                                        class="form-check-input @error($field['name']) is-invalid @enderror"
                                        value="1" {{ isset($field['checked']) && $field['checked'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $fid }}">{{ $field['label'] }}</label>
                                </div>

                            @elseif($fieldType === 'file')
                                <input type="file" name="{{ $field['name'] }}" id="{{ $fid }}"
                                    class="form-control @error($field['name']) is-invalid @enderror"
                                    accept="{{ $field['accept'] ?? '*' }}"
                                    {{ $required ? 'required' : '' }}>
                                @if(isset($field['help']))
                                    <small class="form-text text-muted">{{ $field['help'] }}</small>
                                @endif

                            @elseif($fieldType === 'datetime-local')
                                <input type="datetime-local" name="{{ $field['name'] }}" id="{{ $fid }}"
                                    class="form-control @error($field['name']) is-invalid @enderror"
                                    value="{{ $field['default'] ?? '' }}"
                                    {{ $required ? 'required' : '' }}>

                            @else
                                <input type="{{ $fieldType }}" name="{{ $field['name'] }}" id="{{ $fid }}"
                                    class="form-control @error($field['name']) is-invalid @enderror"
                                    value="{{ $field['default'] ?? '' }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    step="{{ $field['step'] ?? 'any' }}"
                                    {{ $field['min'] ?? '' ? 'min=' . $field['min'] : '' }}
                                    {{ $field['max'] ?? '' ? 'max=' . $field['max'] : '' }}
                                    {{ $required ? 'required' : '' }}>
                            @endif

                            @if(isset($field['help']))
                                <small class="form-text text-muted">{{ $field['help'] }}</small>
                            @endif
                        </div>
                    @endforeach

                    {{-- Extra content slot --}}
                    @if(isset($extraContent))
                        {{ $extraContent }}
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary" id="{{ $modalId }}SubmitBtn">
                        <i class="fas fa-save"></i> <span id="{{ $modalId }}BtnText">Create {{ $title }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // ---- Open modal for CREATE ----
    $(document).on('click', '[data-modal-create="{{ $modalId }}"]', function() {
        var modalId = '{{ $modalId }}';
        $('#' + modalId + 'Form')[0].reset();
        $('#' + modalId + 'Method').val('POST');
        $('#' + modalId + 'RecordId').val('');
        $('#' + modalId + 'TitleText').text('New {{ $title }}');
        $('#' + modalId + 'BtnText').text('Create {{ $title }}');
        $('#' + modalId + 'Form').attr('action', '{{ route($storeRoute) }}');
        // Remove validation feedback
        $('#' + modalId + ' .is-invalid').removeClass('is-invalid');
        $('#' + modalId + ' .invalid-feedback').remove();
        $('#' + modalId).modal('show');
    });

    // ---- Open modal for EDIT ----
    $(document).on('click', '[data-modal-edit="{{ $modalId }}"]', function() {
        var modalId = '{{ $modalId }}';
        var data = $(this).data('record');
        if (!data) {
            // Try reading from data attributes
            data = {};
            $.each(this.attributes, function(i, attr) {
                if (attr.name.startsWith('data-field-')) {
                    var key = attr.name.replace('data-field-', '');
                    data[key] = attr.value;
                }
            });
        }
        $('#' + modalId + 'Form')[0].reset();
        $('#' + modalId + 'Method').val('PUT');
        $('#' + modalId + 'RecordId').val(data.id || '');
        $('#' + modalId + 'TitleText').text('Edit {{ $title }}');
        $('#' + modalId + 'BtnText').text('Update {{ $title }}');
        $('#' + modalId + 'Form').attr('action', '{{ route($updateRoute, 'PLACEHOLDER') }}'.replace('PLACEHOLDER', data.id));

        // Populate fields
        @foreach($fields as $field)
            @php
                $fieldName = $field['name'];
                $fieldType = $field['type'] ?? 'text';
                $fid = $modalId . '_' . $fieldName;
            @endphp
            @if($fieldType !== 'file')
                var val = data.{{ $fieldName }} || '';
                @if($fieldType === 'checkbox')
                    $('#' + '{{ $fid }}').prop('checked', val == 1 || val === true || val === '1');
                @elseif($fieldType === 'select')
                    $('#' + '{{ $fid }}').val(val);
                @else
                    $('#' + '{{ $fid }}').val(val);
                @endif
            @endif
        @endforeach

        // Remove validation feedback
        $('#' + modalId + ' .is-invalid').removeClass('is-invalid');
        $('#' + modalId + ' .invalid-feedback').remove();
        $('#' + modalId).modal('show');
    });
});
</script>
@endpush
