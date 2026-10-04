@extends('layouts.app')

@section('title', 'Show/Hide Research Calls - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-eye-slash"></i> Show/Hide Research Calls</h1>
        <p>Toggle whether each active research call is visible to LPIs.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" onchange="this.form.submit()">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ ($cycleId ?? '') == $cycle->id ? 'selected' : '' }}>{{ $cycle->title }} ({{ $cycle->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Visibility:</label>
                    <select name="visibility" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="shown" {{ ($visibility ?? '') == 'shown' ? 'selected' : '' }}>Shown</option>
                        <option value="hidden" {{ ($visibility ?? '') == 'hidden' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>
        </div>
    </div>

    <div class="panel-body p-0">
        @if($programs->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-eye-slash" style="opacity:0.3;"></i>
                <h5>No Research Calls Found</h5>
                <p>No active research calls match your current filters.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="callsTable">
                <thead>
                    <tr>
                        <th style="min-width:220px;">Research Call</th>
                        <th>Grant</th>
                        <th>Category</th>
                        <th>Cycle</th>
                        <th class="text-center">Projects</th>
                        <th>Final Report Deadline</th>
                        <th class="text-center" style="min-width:210px;">Visibility</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programs as $program)
                    <tr>
                        <td>
                            <a href="{{ route('programs.show', $program->id) }}" style="font-weight:500; color:var(--color-ink-800); text-decoration:none;">
                                {{ $program->program_title }}
                            </a>
                        </td>
                        <td>
                            <span class="pill info" style="font-size:11px;">{{ $program->grant->grant_code ?? 'N/A' }}</span>
                            <div style="font-size:11px; color:var(--color-ink-500); margin-top:2px;">{{ $program->grant->grant_name ?? '' }}</div>
                        </td>
                        <td><span class="pill info" style="font-size:11px;">{{ ucfirst($program->grant->category ?? 'N/A') }}</span></td>
                        <td>{{ $program->cycleConfig->year ?? '—' }}</td>
                        <td class="text-center">
                            <span class="pill {{ $program->projects_count ? 'info' : 'inactive' }}" style="font-size:11px;">
                                {{ $program->projects_count }}
                            </span>
                        </td>
                        <td>
                            @if($program->final_rpt_deadline)
                                {{ $program->final_rpt_deadline->format('M d, Y') }}
                            @else
                                <span style="color:var(--color-ink-400);">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <form action="{{ route('programs.toggle-visibility', $program->id) }}" method="POST" style="display:inline-flex;align-items:center;gap:8px;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $program->is_visible ? 'btn-primary' : 'btn-secondary' }}"
                                        style="white-space:nowrap;">
                                    <i class="fas {{ $program->is_visible ? 'fa-eye' : 'fa-eye-slash' }}" style="font-size:11px;"></i>
                                    {{ $program->is_visible ? 'Shown — Click to Hide' : 'Hidden — Click to Show' }}
                                </button>
                                @if($program->is_visible)
                                    <span class="pill accepted" style="font-size:10px;">Shown</span>
                                @else
                                    <span class="pill inactive" style="font-size:10px;">Hidden</span>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($programs->count() > 0)
<script>
    $(document).ready(function() {
        var table = $('#callsTable').DataTable({
            dom: 'rt<"bottom"lip>',
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] },
                { searchable: false, targets: [6] }
            ]
        });

        $('#tableSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    });
</script>
@endif
@endpush
