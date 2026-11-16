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
        <div class="panel-actions" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:space-between;">
            <form method="GET" class="filter-bar" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Cycle:</label>
                    <select name="cycle_id" class="search-input" style="min-width:190px;" onchange="this.form.submit();">
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
                    <input type="text" id="researchCallSearch" placeholder="Search research calls…" class="search-input">
                </div>
            </form>

            <a href="{{ route($meta['route']) }}" class="btn-secondary btn-sm" title="Open the page without a research-call filter">
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
            <table class="fluent-table w-100" id="researchCallsTable" style="table-layout:fixed;">
                <colgroup>
                    <col style="width:20%;">
                    <col style="width:20%;">
                    <col style="width:20%;">
                    <col style="width:20%;">
                    <col style="width:20%;">
                </colgroup>
                <thead>
                    <tr>
                        @php
                            // Header links toggle the direction of the current sort and
                            // keep the cycle filter.
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
                        <th style="white-space:nowrap;">
                            <a href="{{ $sortLink('title') }}" style="color:inherit;text-decoration:none;">Research Call {!! $sortArrow('title') !!}</a>
                        </th>
                        <th style="white-space:nowrap;">
                            <a href="{{ $sortLink('grant') }}" style="color:inherit;text-decoration:none;">Grant {!! $sortArrow('grant') !!}</a>
                        </th>
                        <th style="white-space:nowrap;">
                            <a href="{{ $sortLink('cycle') }}" style="color:inherit;text-decoration:none;">Cycle {!! $sortArrow('cycle') !!}</a>
                        </th>
                        <th style="white-space:nowrap;">
                            <a href="{{ $sortLink('projects') }}" style="color:inherit;text-decoration:none;">Projects {!! $sortArrow('projects') !!}</a>
                        </th>
                        <th style="white-space:nowrap;">
                            <a href="{{ $sortLink('assigned') }}" style="color:inherit;text-decoration:none;">Assigned {!! $sortArrow('assigned') !!}</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($researchCalls as $researchCall)
                        @php
                            $targetUrl = route($meta['route'], ['program_id' => $researchCall->id]);
                        @endphp
                        <tr class="research-call-row" onclick="window.location='{{ $targetUrl }}'">
                            <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <a href="{{ $targetUrl }}"
                                   title="{{ $researchCall->program_title }}"
                                   onclick="event.stopPropagation()"
                                   style="font-weight:600;color:var(--brand-600,#7a1636);text-decoration:none;white-space:nowrap;">
                                    {{ $researchCall->program_title }}
                                </a>
                            </td>
                            <td style="white-space:nowrap;">
                                <span class="pill info" style="font-size:11px;white-space:nowrap;">
                                    {{ $researchCall->grant->grant_code ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <span style="font-size:12px;color:var(--ink-500,#5d6677);white-space:nowrap;">
                                    {{ $researchCall->cycle->title ?? '—' }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                <span class="pill review" style="font-size:11px;white-space:nowrap;">
                                    {{ $researchCall->projects_count }} project{{ $researchCall->projects_count == 1 ? '' : 's' }}
                                </span>
                            </td>
                            <td style="white-space:nowrap;">
                                @if($researchCall->assigned_projects_count > 0)
                                    <span class="pill accepted" style="font-size:11px;white-space:nowrap;">
                                        {{ $researchCall->assigned_projects_count }} assigned
                                    </span>
                                @else
                                    <span style="font-size:12px;color:var(--ink-400,#8b8794);font-style:italic;white-space:nowrap;">None</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Compact rows: the research-call name is the link (no action button), so
       each row is a single tight line. The whole row is clickable. */
    #researchCallsTable td {
        padding-top: 4px;
        padding-bottom: 4px;
        vertical-align: middle;
    }
    #researchCallsTable tbody tr.research-call-row {
        cursor: pointer;
    }
    #researchCallsTable tbody tr.research-call-row:hover {
        background: var(--sand-50, #faf7f0);
    }
    /* Equal 20% columns: centre everything but the research-call name so the
       spare room is spread evenly instead of trailing in one column. */
    #researchCallsTable th:not(:first-child),
    #researchCallsTable td:not(:first-child) {
        text-align: center;
    }
</style>
@endpush

@push('scripts')
@if($researchCalls->isNotEmpty())
<script>
    // Lightweight client-side filter – the list is a handful of research calls,
    // so a full DataTable would only add noise here.
    document.getElementById('researchCallSearch').addEventListener('keyup', function () {
        var term = this.value.toLowerCase();

        document.querySelectorAll('#researchCallsTable tbody tr').forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
        });
    });
</script>
@endif
@endpush
