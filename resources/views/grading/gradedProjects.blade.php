@extends('layouts.app')

@section('title', 'Graded Projects - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-check-double"></i> Graded Projects</h1>
        <p>Review and manage graded project evaluations.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-list"></i> Graded Projects</h2>
        <div class="panel-actions">
            @if($cycles->count() > 1)
            <form method="GET" class="d-flex align-items-center gap-2" id="cycleFilter">
                <label style="font-size:12px;color:var(--color-ink-500);margin:0 6px 0 0;white-space:nowrap;">Filter:</label>
                <select name="cycle_id" class="form-select" style="width:auto;padding:4px 28px 4px 10px;font-size:12px;" onchange="this.form.submit()">
                    <option value="">All Cycles</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ $cycleId == $cycle->id ? 'selected' : '' }}>{{ $cycle->program_title }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
    </div>
    <div class="panel-body p-0">
        @if($gradedProjects->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-check-double" style="opacity:0.3;"></i>
                <h5>No Graded Projects</h5>
                <p>No projects have been graded yet in the selected cycle.</p>
            </div>
        @else
            <table class="fluent-table w-100" id="gradedTable">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Title</th>
                        <th>PI Name</th>
                        <th>Research Call</th>
                        <th>Status</th>
                        <th class="text-center">Total Score</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradedProjects as $project)
                    <tr>
                        <td><code>{{ $project->old_project_id }}</code></td>
                        <td><span style="font-weight:500;">{{ Str::limit($project->title, 50) }}</span></td>
                        <td>{{ $project->lpi->name ?? $project->author ?? '—' }}</td>
                        <td>{{ $project->program->program_title ?? 'N/A' }}</td>
                        <td>
                            @php
                                $statusValue = $project->college_decision ?? $project->final_rsd_decision ?? 'pending';
                                $statusLower = strtolower($statusValue);
                                $statusClass = match(true) {
                                    $statusLower === 'accepted' || $statusLower === 'approved' => 'success',
                                    $statusLower === 'rejected' || $statusLower === 'declined' => 'danger',
                                    default => 'review',
                                };
                            @endphp
                            <span class="pill {{ $statusClass }}">
                                <i class="fas fa-{{ $statusClass === 'success' ? 'check-circle' : ($statusClass === 'danger' ? 'times-circle' : 'clock') }}" style="font-size:10px;"></i>
                                {{ $statusValue }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="pill info" style="font-weight:700;">{{ $project->total_score ?? '—' }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('projects.grading', $project->id) }}" class="row-action" title="View Grade" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </a>
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
@if($gradedProjects->count() > 0)
<script>
    $(document).ready(function() {
        $('#gradedTable').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] },
                { searchable: false, targets: [6] }
            ]
        });
    });
</script>
@endif
@endpush
