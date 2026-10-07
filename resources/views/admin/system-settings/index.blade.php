@extends('layouts.app')

@section('title', 'System Settings - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-cog"></i> System Settings</h1>
        <p>Configure gauges, grading form, and AI assistant.</p>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- Tab Navigation (underline style) --}}
<div class="settings-tabs">
    @php
        $tabs = [
            'gauges'   => ['icon' => 'fa-gauge-high', 'label' => 'Gauges'],
            'grading'  => ['icon' => 'fa-clipboard-check', 'label' => 'Grading Form'],
            'scores'   => ['icon' => 'fa-star', 'label' => 'Scores'],
            'ai'       => ['icon' => 'fa-robot', 'label' => 'AI Assistant'],
        ];
    @endphp
    @foreach($tabs as $key => $t)
    <a href="{{ route('admin.system-settings', ['tab' => $key]) }}"
       class="settings-tab {{ $tab === $key ? 'active' : '' }}">
        <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
    </a>
    @endforeach
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• TAB: GAUGES â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
@if($tab === 'gauges')
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;align-items:start;">
    @foreach($gauges as $gauge)
    <div class="panel" id="gauge-{{ $gauge->id }}">
        <div class="panel-head">
            <h2 style="font-size:14px;margin:0;">
                @if($gauge->id === 1)<i class="fas fa-chart-line" style="margin-right:6px;"></i>
                @elseif($gauge->id === 2)<i class="fas fa-star" style="margin-right:6px;"></i>
                @else<i class="fas fa-user-check" style="margin-right:6px;"></i>
                @endif
                {{ $gauge->name }}
            </h2>
        </div>
        <div class="panel-body">
            <div id="chart_div{{ $gauge->id }}" class="gauge-chart" style="width:220px;height:150px;margin:0 auto 12px;"></div>
            <form method="POST" action="{{ route('gauge-settings.update', $gauge->id) }}" id="gaugeForm{{ $gauge->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $gauge->id }}">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;text-align:left;">
                    <div style="padding:8px 10px;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;">
                        <div style="font-size:10px;font-weight:600;color:#991b1b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#dc2626;font-size:8px;"></i> Red Zone
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="number" name="redfrom" value="{{ $gauge->redfrom }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                            <span style="font-size:11px;color:var(--ink-400);">to</span>
                            <input type="number" name="redto" value="{{ $gauge->redto }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                        </div>
                    </div>
                    <div style="padding:8px 10px;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;">
                        <div style="font-size:10px;font-weight:600;color:#92400e;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#f59e0b;font-size:8px;"></i> Yellow Zone
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <input type="number" name="yellowfrom" value="{{ $gauge->yellowfrom }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                            <span style="font-size:11px;color:var(--ink-400);">to</span>
                            <input type="number" name="yellowto" value="{{ $gauge->yellowto }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                        </div>
                    </div>
                    <div style="padding:8px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;grid-column:1/-1;">
                        <div style="font-size:10px;font-weight:600;color:#166534;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#22c55e;font-size:8px;"></i> Green Zone
                        </div>
                        <div style="display:flex;gap:6px;align-items:center;max-width:200px;">
                            <input type="number" name="greenfrom" value="{{ $gauge->greenfrom }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                            <span style="font-size:11px;color:var(--ink-400);">to</span>
                            <input type="number" name="greento" value="{{ $gauge->greento }}" min="0" max="10000" required class="form-control form-control-sm" style="font-size:12px;padding:4px 6px;text-align:center;">
                        </div>
                    </div>
                </div>
                <div style="margin-top:12px;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn-primary btn-sm"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• TAB: GRADING FORM â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
@if($tab === 'grading')
<div class="panel">
    <div class="panel-head">
        <h2 style="font-size:14px;margin:0;"><i class="fas fa-clipboard-check" style="margin-right:6px;"></i> Grading Form Settings</h2>
    </div>
    <div class="panel-body" style="padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
            <div style="flex:1;">
                <div style="font-size:14px;font-weight:600;color:var(--ink-800);margin-bottom:6px;">Show Auto-Grade in Final Report</div>
                <div style="font-size:13px;color:var(--ink-500);line-height:1.6;">
                    When enabled, reviewers see outcome checkboxes and auto-calculated scores alongside manual grade selection.
                    When disabled, only the manual grade radio buttons are shown (simpler grading experience).
                </div>
            </div>
            <form id="autoGradeToggleForm" method="POST" action="{{ route('gauge-settings.auto-grade-visibility') }}">
                @csrf
                <input type="hidden" name="auto_grade_visibility" id="autoGradeVisibilityInput" value="{{ $autoGradeVisibility }}">
                <label style="position:relative;display:inline-block;width:52px;height:28px;flex-shrink:0;cursor:pointer;" onclick="toggleAutoGrade()">
                    <span id="autoGradeToggleTrack" style="position:absolute;inset:0;background:{{ $autoGradeVisibility === '1' ? 'var(--brand-500)' : 'var(--ink-300)' }};border-radius:14px;transition:.3s;"></span>
                    <span id="autoGradeToggleKnob" style="position:absolute;height:22px;width:22px;left:{{ $autoGradeVisibility === '1' ? '26px' : '3px' }};bottom:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                </label>
            </form>
        </div>
    </div>
</div>
@endif

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• TAB: AI ASSISTANT â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
@if($tab === 'ai')
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2 style="font-size:14px;margin:0;"><i class="fas fa-cog" style="margin-right:6px;"></i> General Settings</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.ai-settings.save') }}">
            @csrf
            {{-- ── Assistant on/off switch ── --}}
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--ink-50,#f5f4f2);border:1px solid var(--ink-100,#eceef2);border-radius:8px;margin-bottom:16px;">
                <label class="form-check form-switch" style="margin:0;display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="assistant_enabled" value="0">
                    <input type="checkbox" name="assistant_enabled" value="1" class="form-check-input"
                           {{ ($settings['assistant_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                           style="width:36px;height:20px;margin-left:0;cursor:pointer;">
                    <span style="font-size:12.5px;font-weight:600;color:var(--ink-700);">Enable AI Assistant</span>
                </label>
                <span style="font-size:11.5px;color:var(--ink-400);">
                    When OFF, the Gemini chat widget is removed from the Help Center and <code>/ai/chat</code> answers are disabled for everyone.
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--ink-600);display:block;margin-bottom:4px;">
                        API Key
                        <a href="https://aistudio.google.com/apikey" target="_blank" style="font-size:11px;font-weight:400;color:var(--brand-600);margin-left:6px;text-decoration:none;">
                            <i class="fas fa-external-link-alt" style="font-size:10px;"></i> Get key
                        </a>
                    </label>
                    <input type="password" name="api_key" value="{{ $settings['api_key'] }}" required class="form-control" placeholder="AIza..." style="font-size:12px;" autocomplete="off">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--ink-600);display:block;margin-bottom:4px;">Model</label>
                    <select name="model" class="form-control" style="font-size:12px;">
                        @foreach([
                            'gemini-2.0-flash'         => 'Gemini 2.0 Flash (free)',
                            'gemini-2.5-flash'         => 'Gemini 2.5 Flash (free)',
                            'gemini-2.5-pro'           => 'Gemini 2.5 Pro',
                            'gemini-3-flash'           => 'Gemini 3 Flash',
                            'gemini-3.1-flash-lite'    => 'Gemini 3.1 Flash Lite',
                            'gemini-1.5-flash'         => 'Gemini 1.5 Flash',
                            'gemini-1.5-pro'           => 'Gemini 1.5 Pro',
                        ] as $value => $label)
                            <option value="{{ $value }}" {{ $settings['model'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:var(--ink-600);display:block;margin-bottom:4px;">Mode</label>
                    <select name="mode" class="form-control" style="font-size:12px;">
                        <option value="static" {{ $settings['mode'] === 'static' ? 'selected' : '' }}>Static (prompt only, no DB)</option>
                        <option value="dynamic" {{ $settings['mode'] === 'dynamic' ? 'selected' : '' }}>Dynamic (live database access)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary btn-sm"><i class="fas fa-save"></i> Save Settings</button>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div style="display:flex;justify-content:space-between;align-items:center;width:100%;">
            <h2 style="font-size:14px;margin:0;"><i class="fas fa-file-alt" style="margin-right:6px;"></i> System Prompt</h2>
            <form method="POST" action="{{ route('admin.ai-settings.reset') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-secondary btn-sm" onclick="return confirm('Reset prompt to default?')">
                    <i class="fas fa-undo"></i> Restore Default
                </button>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.ai-settings.prompt.save') }}">
            @csrf
            <div style="margin-bottom:12px;">
                <textarea name="prompt" required class="form-control" rows="18" style="font-size:12px;font-family:monospace;line-height:1.6;">{{ $settings['prompt'] }}</textarea>
            </div>
            <button type="submit" class="btn-primary btn-sm"><i class="fas fa-save"></i> Save Prompt</button>
        </form>
    </div>
</div>
@endif

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• TAB: SCORES â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
@if($tab === 'scores')
<div class="panel">
    <div class="panel-body" style="padding:10px 16px;">
        <input type="text" id="scoresTableSearch" placeholder="Search scores..." class="search-input" style="width:100%;max-width:300px;font-size:12px;padding:6px 10px;border:1px solid var(--ink-200);border-radius:6px;">
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100 compact" id="scoresTable" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Label</th>
                    <th>Value</th>
                    <th class="text-center" style="min-width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scores as $score)
                <tr>
                    <td>
                        <span style="font-weight:500;font-size:12.5px;">{{ $score->name }}</span>
                        @if($score->description)
                            <br><small style="color:var(--ink-400);font-size:11px;">{{ $score->description }}</small>
                        @endif
                    </td>
                    <td>
                        @if($score->label)
                            <span class="pill info" style="font-size:11px;padding:1px 6px;">{{ $score->label }}</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">â€”</span>
                        @endif
                    </td>
                    <td style="font-weight:500;font-size:12.5px;">{{ number_format($score->value, 2) }}</td>
                    <td class="text-center">
                        <button type="button"
                            class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                            data-modal-edit="scoreModal"
                            data-field-id="{{ $score->id }}"
                            data-field-name="{{ $score->name }}"
                            data-field-label="{{ $score->label }}"
                            data-field-value="{{ $score->value }}">
                            <i class="fas fa-edit" style="font-size:11px;"></i> Edit
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
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px;padding:10px 14px;background:var(--sand-50);border-radius:8px;border:1px solid var(--ink-100);">
                        <div style="width:38px;height:38px;border-radius:6px;background:var(--brand-100);color:var(--brand-600);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-star" style="font-size:15px;"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:14px;color:var(--ink-800);" id="scoreModal_name_display"></div>
                            <div style="font-size:12px;color:var(--ink-400);" id="scoreModal_label_display"></div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="scoreModal_value" class="form-label" style="font-size:13px;">Score Value <span class="text-danger">*</span></label>
                        <input type="number" name="value" id="scoreModal_value" class="form-control form-control-lg" step="0.01" min="0" max="999.99" required placeholder="e.g. 5.00" style="font-size:24px;font-weight:700;text-align:center;padding:12px;border-radius:8px;">
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

@push('scripts')
<style>
#scoresTable td, #scoresTable th { padding: 6px 10px !important; }
</style>
<script>
$(document).ready(function() {
    @if($scores->count() > 0)
    var scoresTbl = $('#scoresTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[2, 'desc']],
        pageLength: 25,
        drawCallback: function() { $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip(); }
    });
    $('#scoresTableSearch').on('keyup', function() { scoresTbl.search(this.value).draw(); });
    @endif

    $(document).on('click', '[data-modal-edit="scoreModal"]', function() {
        var btn = $(this);
        $('#scoreModalForm')[0].reset();
        $('#scoreModalRecordId').val(btn.data('field-id'));
        $('#scoreModal_name_display').text(btn.data('field-name'));
        $('#scoreModal_label_display').text(btn.data('field-label') ? 'Label: ' + btn.data('field-label') : '');
        $('#scoreModal_value').val(btn.data('field-value'));
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#scoreModal').modal('show');
    });

    $('#scoreModalForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this), btn = $('#scoreModalSubmitBtn');
        var id = $('#scoreModalRecordId').val();
        var url = '{{ route("scores.update", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: url, method: 'PUT', data: form.serialize(), dataType: 'json',
            success: function(resp) {
                $('#scoreModal').modal('hide');
                showToast('success', resp.message || 'Score updated!');
                setTimeout(function() { location.reload(); }, 800);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Score');
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $('.is-invalid').removeClass('is-invalid'); $('.invalid-feedback').remove();
                    $.each(errors, function(field, msgs) {
                        var input = form.find('[name="' + field + '"]');
                        input.addClass('is-invalid');
                        input.after('<div class="invalid-feedback">' + msgs[0] + '</div>');
                    });
                } else { showToast('error', 'An error occurred.'); }
            }
        });
    });
});
</script>
@endpush
@endif

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
.gauge-chart text{display:none !important;}

/* ── Settings tabs (underline style) ── */
.settings-tabs{display:flex;gap:4px;margin-bottom:24px;border-bottom:1px solid var(--ink-200,#d8d6dc);overflow-x:auto;}
.settings-tab{position:relative;display:inline-flex;align-items:center;gap:8px;padding:11px 18px;font-size:13px;font-weight:600;color:var(--ink-500);text-decoration:none;white-space:nowrap;border-bottom:2.5px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;}
.settings-tab i{font-size:12.5px;color:var(--ink-400);transition:color .15s;}
.settings-tab:hover{color:var(--ink-800);}
.settings-tab:hover i{color:var(--ink-600);}
.settings-tab.active{color:var(--brand-600);border-bottom-color:var(--brand-500);}
.settings-tab.active i{color:var(--brand-500);}
@media (max-width:600px){.settings-tab{padding:10px 12px;font-size:12px;}}
</style>
@endpush

@push('scripts')
@if($tab === 'gauges')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', {'packages':['gauge']});
google.charts.setOnLoadCallback(function() {
    @foreach($gauges as $gauge)
    drawGauge{{ $gauge->id }}();
    @endforeach
});

@foreach($gauges as $gauge)
function drawGauge{{ $gauge->id }}() {
    var data = google.visualization.arrayToDataTable([['Label', 'Value'], ['', 0]]);
    var options = {
        width:220, height:150,
        redFrom:{{ $gauge->redfrom }}, redTo:{{ $gauge->redto }},
        yellowFrom:{{ $gauge->yellowfrom }}, yellowTo:{{ $gauge->yellowto }},
        greenFrom:{{ $gauge->greenfrom }}, greenTo:{{ $gauge->greento }},
        max:{{ $gauge->greento }}, minorTicks:5, majorTicks:null,
    };
    var chart = new google.visualization.Gauge(document.getElementById('chart_div{{ $gauge->id }}'));
    chart.draw(data, options);
    window['gaugeChart{{ $gauge->id }}'] = chart;
    window['gaugeData{{ $gauge->id }}'] = data;
}
@endforeach

document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('change', function() {
        var form = this.closest('form');
        var id = form.querySelector('input[name="id"]').value;
        var options = {
            width:220, height:150,
            redFrom: parseInt(form.querySelector('[name="redfrom"]').value)||0,
            redTo: parseInt(form.querySelector('[name="redto"]').value)||33,
            yellowFrom: parseInt(form.querySelector('[name="yellowfrom"]').value)||34,
            yellowTo: parseInt(form.querySelector('[name="yellowto"]').value)||66,
            greenFrom: parseInt(form.querySelector('[name="greenfrom"]').value)||67,
            greenTo: parseInt(form.querySelector('[name="greento"]').value)||100,
            max: parseInt(form.querySelector('[name="greento"]').value)||100,
            minorTicks:5, majorTicks:null,
        };
        if (window['gaugeChart'+id]) window['gaugeChart'+id].draw(window['gaugeData'+id], options);
    });
});
</script>
@endif

@if($tab === 'grading')
<script>
function toggleAutoGrade() {
    var input = document.getElementById('autoGradeVisibilityInput');
    var track = document.getElementById('autoGradeToggleTrack');
    var knob = document.getElementById('autoGradeToggleKnob');
    var newVal = input.value === '1' ? '0' : '1';
    input.value = newVal;
    track.style.background = newVal === '1' ? 'var(--brand-500)' : 'var(--ink-300)';
    knob.style.left = newVal === '1' ? '26px' : '3px';
    document.getElementById('autoGradeToggleForm').submit();
}
</script>
@endif
@endpush
