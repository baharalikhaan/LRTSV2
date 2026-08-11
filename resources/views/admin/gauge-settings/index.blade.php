@extends('layouts.app')

@section('title', 'Gauge Settings - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-gauge-high"></i> Gauge Settings</h1>
        <p>Configure red/yellow/green threshold ranges for performance gauges.</p>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; align-items:start;">

    @foreach($gauges as $gauge)
    <div class="panel" id="gauge-{{ $gauge->id }}">
        <div class="panel-head">
            <h2 style="font-size:14px; margin:0;">
                @if($gauge->id === 1)
                    <i class="fas fa-chart-line" style="margin-right:6px;"></i>
                @elseif($gauge->id === 2)
                    <i class="fas fa-star" style="margin-right:6px;"></i>
                @else
                    <i class="fas fa-user-check" style="margin-right:6px;"></i>
                @endif
                {{ $gauge->name }}
            </h2>
        </div>
        <div class="panel-body" style="text-align:center;">
            {{-- Gauge Chart --}}
            <div id="chart_div{{ $gauge->id }}" style="width:100%; height:180px; margin-bottom:16px;"></div>

            {{-- Form --}}
            <form method="POST" action="{{ route('gauge-settings.update', $gauge->id) }}" id="gaugeForm{{ $gauge->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $gauge->id }}">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; text-align:left;">
                    {{-- Red Zone --}}
                    <div style="padding:8px 10px; background:#fef2f2; border:1px solid #fecaca; border-radius:4px;">
                        <div style="font-size:10px; font-weight:600; color:#991b1b; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#dc2626; font-size:8px;"></i> Red Zone
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <input type="number" name="redfrom" value="{{ $gauge->redfrom }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                            <span style="font-size:11px; color:var(--color-ink-400);">to</span>
                            <input type="number" name="redto" value="{{ $gauge->redto }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                        </div>
                    </div>

                    {{-- Yellow Zone --}}
                    <div style="padding:8px 10px; background:#fffbeb; border:1px solid #fde68a; border-radius:4px;">
                        <div style="font-size:10px; font-weight:600; color:#92400e; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#f59e0b; font-size:8px;"></i> Yellow Zone
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <input type="number" name="yellowfrom" value="{{ $gauge->yellowfrom }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                            <span style="font-size:11px; color:var(--color-ink-400);">to</span>
                            <input type="number" name="yellowto" value="{{ $gauge->yellowto }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                        </div>
                    </div>

                    {{-- Green Zone --}}
                    <div style="padding:8px 10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:4px; grid-column:1/-1;">
                        <div style="font-size:10px; font-weight:600; color:#166534; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px;">
                            <i class="fas fa-circle" style="color:#22c55e; font-size:8px;"></i> Green Zone
                        </div>
                        <div style="display:flex; gap:6px; align-items:center; max-width:200px;">
                            <input type="number" name="greenfrom" value="{{ $gauge->greenfrom }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                            <span style="font-size:11px; color:var(--color-ink-400);">to</span>
                            <input type="number" name="greento" value="{{ $gauge->greento }}" min="0" max="100" required
                                class="form-control form-control-sm" style="font-size:12px; padding:4px 6px; text-align:center;">
                        </div>
                    </div>
                </div>

                <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn-primary btn-sm" id="saveBtn{{ $gauge->id }}">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
</style>
@endpush

@push('scripts')
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
    var data = google.visualization.arrayToDataTable([
        ['Label', 'Value'],
        ['{{ $gauge->name }}', 0]
    ]);

    var options = {
        width: '100%', height: 180,
        redFrom: {{ $gauge->redfrom }}, redTo: {{ $gauge->redto }},
        yellowFrom: {{ $gauge->yellowfrom }}, yellowTo: {{ $gauge->yellowto }},
        greenFrom: {{ $gauge->greenfrom }}, greenTo: {{ $gauge->greento }},
        max: {{ $gauge->greento }},
        minorTicks: 5,
        majorTicks: ['0', '{{ $gauge->greento }}'],
    };

    var chart = new google.visualization.Gauge(document.getElementById('chart_div{{ $gauge->id }}'));
    chart.draw(data, options);
    window['gaugeChart{{ $gauge->id }}'] = chart;
    window['gaugeData{{ $gauge->id }}'] = data;
}
@endforeach

// Live preview on input change
document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('change', function() {
        var form = this.closest('form');
        var id = form.querySelector('input[name="id"]').value;
        var redFrom = parseInt(form.querySelector('[name="redfrom"]').value) || 0;
        var redTo = parseInt(form.querySelector('[name="redto"]').value) || 33;
        var yellowFrom = parseInt(form.querySelector('[name="yellowfrom"]').value) || 34;
        var yellowTo = parseInt(form.querySelector('[name="yellowto"]').value) || 66;
        var greenFrom = parseInt(form.querySelector('[name="greenfrom"]').value) || 67;
        var greenTo = parseInt(form.querySelector('[name="greento"]').value) || 100;

        var options = {
            width: '100%', height: 180,
            redFrom: redFrom, redTo: redTo,
            yellowFrom: yellowFrom, yellowTo: yellowTo,
            greenFrom: greenFrom, greenTo: greenTo,
            max: greenTo,
            minorTicks: 5,
            majorTicks: ['0', String(greenTo)],
        };

        if (window['gaugeChart' + id]) {
            window['gaugeChart' + id].draw(window['gaugeData' + id], options);
        }
    });
});
</script>
@endpush
