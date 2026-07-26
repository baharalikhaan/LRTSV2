@extends('layouts.app')

@section('title', 'Show/Hide Research Calls - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-arrows-rotate"></i> Show/Hide Research Calls</h1>
        <p>Manage visibility of research calls. Toggle the <strong>is_visible</strong> flag to show/hide calls.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <form method="GET" class="filter-bar" id="filterForm" style="flex:1;">
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search research calls..." class="search-input">
                </div>
            </form>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100 compact" id="researchCallsTable" style="font-size:12.5px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Research Call Title</th>
                    <th>Grant</th>
                    <th>Cycle</th>
                    <th>Final Deadline</th>
                    <th>Ext. Deadline</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="min-width:80px;">Visibility</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr>
                    <td><code>{{ $program->id }}</code></td>
                    <td>
                        <a href="{{ route('programs.show', $program->id) }}" style="font-weight:500;text-decoration:none;color:var(--color-brand-500);">
                            {{ $program->program_title }}
                        </a>
                    </td>
                    <td>
                        @if($program->grant)
                            <span class="pill primary" style="font-size:11px;">{{ $program->grant->grant_code }}</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($program->cycleConfig)
                            <span class="pill secondary" style="font-size:11px;">{{ $program->cycleConfig->title }}</span>
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;">
                        @if($program->final_rpt_deadline)
                            <i class="far fa-calendar-alt me-1" style="color:var(--color-ink-400);font-size:11px;"></i>
                            {{ $program->final_rpt_deadline->format('d-m-Y') }}
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;">
                        @if($program->extended_final_rpt_deadline)
                            <i class="far fa-calendar-alt me-1" style="color:var(--color-gold-400);font-size:11px;"></i>
                            {{ $program->extended_final_rpt_deadline->format('d-m-Y') }}
                        @else
                            <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($program->isActive())
                            <span class="pill success" style="font-size:11px;"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                        @else
                            <span class="pill inactive" style="font-size:11px;"><i class="fas fa-lock" style="font-size:10px;"></i> Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <form action="{{ route('research-calls.toggle', $program->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn-sm {{ $program->is_visible ? 'btn-secondary' : 'btn-primary' }}"
                                style="font-size:11px;padding:4px 10px;white-space:nowrap;"
                                title="{{ $program->is_visible ? 'Hide this call' : 'Show this call' }}"
                                data-bs-toggle="tooltip">
                                <i class="fas {{ $program->is_visible ? 'fa-eye-slash' : 'fa-eye' }}" style="font-size:11px;"></i>
                                {{ $program->is_visible ? 'Hide' : 'Show' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state py-4">
                            <i class="fas fa-arrows-rotate"></i>
                            <h5>No Research Calls</h5>
                            <p>There are currently no research calls in the system.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($programs->count() > 0)
    var table = $('#researchCallsTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[0, 'desc']],
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
});
</script>
@endpush
