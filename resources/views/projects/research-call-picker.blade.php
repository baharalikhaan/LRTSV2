@extends('layouts.app')

@section('title', $meta['title'] . ' – Choose a Research Call – RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="{{ $meta['icon'] }}"></i> {{ $meta['title'] }}</h1>
        <p>{{ $meta['description'] }}</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions">
            <form method="GET" class="filter-bar" id="researchCallFilter">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" onchange="this.form.submit()">
                        <option value="">All Cycles</option>
                        @foreach($cycleConfigs as $cycle)
                            <option value="{{ $cycle->id }}" {{ (string) $cycleId === (string) $cycle->id ? 'selected' : '' }}>
                                {{ $cycle->title }} ({{ $cycle->year }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="researchCallSearch" placeholder="Search table..." class="search-input">
                </div>
            </form>

            <a href="{{ route($meta['route']) }}" class="btn-secondary btn-sm" title="Open the page without a research-call filter" style="margin-left:12px;">
                <i class="fas fa-layer-group"></i> Show all projects
            </a>
        </div>
    </div>

    <div class="panel-body p-0">
        @if($researchCalls->isEmpty())
            <div class="empty-state py-5">
                <i class="{{ $meta['icon'] }}" style="opacity:0.3;"></i>
                <h5>No Research Calls</h5>
                <p>No research call matches the selected cycle.</p>
            </div>
        @else
            @php
                // Deadline cell: plain text (no badges) with a short
                // "days left / passed" note so the state is readable.
                $deadlineCell = function ($value) {
                    if (!$value) {
                        return '<span style="color:var(--ink-300,#b3aeb9);">—</span>';
                    }

                    $due = \Illuminate\Support\Carbon::parse($value);
                    $days = now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);

                    $colour = 'var(--ink-600,#4b4654)';
                    if ($days < 0) {
                        $colour = '#a93434';
                    } elseif ($days <= 14) {
                        $colour = '#8a6d1f';
                    }

                    $note = $days < 0
                        ? 'passed'
                        : $days . ' day' . ($days === 1 ? '' : 's') . ' left';

                    return '<span style="color:' . $colour . ';white-space:nowrap;">'
                        . $due->format('d M Y')
                        . ' <span style="font-size:11px;opacity:.75;">&middot; ' . $note . '</span></span>';
                };

                // Header links toggle the direction of the current sort and keep
                // the cycle filter.
                $sortLink = function (string $key) use ($sort, $direction, $cycleId) {
                    $next = ($sort === $key && $direction === 'asc') ? 'desc' : 'asc';

                    return route('projects.research-calls', array_filter([
                        'context'  => request()->route('context'),
                        'cycle_id' => $cycleId,
                        'sort'     => $key,
                        'dir'      => $next,
                    ]));
                };
                $sortArrow = function (string $key) use ($sort, $direction) {
                    if ($sort !== $key) {
                        return '<i class="fas fa-sort" style="opacity:.35;font-size:10px;"></i>';
                    }

                    return $direction === 'asc'
                        ? '<i class="fas fa-sort-up" style="font-size:10px;"></i>'
                        : '<i class="fas fa-sort-down" style="font-size:10px;"></i>';
                };
            @endphp

            <table class="fluent-table w-100" id="researchCallsTable">
                <thead>
                    <tr>
                        <th>
                            <a href="{{ $sortLink('title') }}" style="color:inherit;text-decoration:none;">Research Call {!! $sortArrow('title') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('grant') }}" style="color:inherit;text-decoration:none;">Grant {!! $sortArrow('grant') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('cycle') }}" style="color:inherit;text-decoration:none;">Cycle {!! $sortArrow('cycle') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('projects') }}" style="color:inherit;text-decoration:none;">Projects {!! $sortArrow('projects') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('pr1') }}" style="color:inherit;text-decoration:none;">Progress 1 deadline {!! $sortArrow('pr1') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('pr2') }}" style="color:inherit;text-decoration:none;">Progress 2 deadline {!! $sortArrow('pr2') !!}</a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('final') }}" style="color:inherit;text-decoration:none;">Final report deadline {!! $sortArrow('final') !!}</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($researchCalls as $researchCall)
                        @php
                            $targetUrl = route($meta['route'], ['program_id' => $researchCall->id]);
                        @endphp
                        <tr class="research-call-row" style="cursor:pointer;" onclick="window.location='{{ $targetUrl }}'">
                            <td>
                                <a href="{{ $targetUrl }}"
                                   title="{{ $researchCall->program_title }}"
                                   onclick="event.stopPropagation()"
                                   style="color:var(--brand-600,#7a1636);font-weight:400;text-decoration:underline;">
                                    {{ $researchCall->program_title }}
                                </a>
                            </td>
                            <td>{{ $researchCall->grant->grant_code ?? 'N/A' }}</td>
                            <td>{{ $researchCall->cycle->title ?? '—' }}</td>
                            <td>{{ $researchCall->projects_count }}</td>
                            <td>{!! $deadlineCell($researchCall->prog_rpt_deadline) !!}</td>
                            <td>{!! $deadlineCell($researchCall->prog_rpt2_deadline) !!}</td>
                            <td>{!! $deadlineCell($researchCall->final_rpt_deadline) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($researchCalls->isNotEmpty())
<script>
    $(document).ready(function () {
        // Same DataTable theme as the other project tables (toolbar with the
        // export buttons, search, "show N entries", info and pagination).
        // Ordering stays server-side: the column headers are sort links.
        var table = $('#researchCallsTable').DataTable({
            order: [],
            ordering: false,
            pageLength: 25
        });

        // The page's search box drives the DataTable search.
        $('#researchCallSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>
@endif
@endpush
