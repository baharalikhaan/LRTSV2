@extends('layouts.app')

@section('title', 'My Assignments - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tasks"></i> My Reviewer Assignments</h1>
</div>

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;">Total Assigned</p>
                        <h3 class="mb-0 font-weight-bold" style="color:var(--color-brand-500);">{{ $totalAssigned }}</h3>
                    </div>
                    <div style="font-size:28px;color:var(--color-brand-300);"><i class="fas fa-clipboard-list"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;">Claimed / Accepted</p>
                        <h3 class="mb-0 font-weight-bold" style="color:var(--color-success);">{{ $claimed }}</h3>
                    </div>
                    <div style="font-size:28px;color:var(--color-success);"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;">Graded</p>
                        <h3 class="mb-0 font-weight-bold" style="color:var(--color-info);">{{ $gradedCount }}</h3>
                    </div>
                    <div style="font-size:28px;color:var(--color-info);"><i class="fas fa-star"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted" style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;">Pending</p>
                        <h3 class="mb-0 font-weight-bold" style="color:var(--color-warning);">{{ $pending }}</h3>
                    </div>
                    <div style="font-size:28px;color:var(--color-warning);"><i class="fas fa-clock"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card { border-radius:8px; border:1px solid var(--color-ink-100); box-shadow:var(--fluent-depth-2); transition:box-shadow .2s; }
.stat-card:hover { box-shadow:var(--fluent-depth-8); }
.border-left-primary { border-left:4px solid var(--color-brand-500) !important; }
.border-left-success { border-left:4px solid var(--color-success) !important; }
.border-left-info { border-left:4px solid var(--color-info) !important; }
.border-left-warning { border-left:4px solid var(--color-warning) !important; }
</style>

<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Proposals Assigned for Review</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0" id="assignmentsTable">
            <thead>
                <tr>
                    <th>Project ID</th>
                    <th>Project Title</th>
                    <th>Cycle</th>
                    <th>LPI Name</th>
                    <th>LPI Email</th>
                    <th>Proposal Status</th>
                    <th>Status Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr>
                    <td><code>{{ $a->old_project_id }}</code></td>
                    <td>{{ $a->project_title }}</td>
                    <td>{{ $a->program_title }}</td>
                    <td>{{ $a->lpi_name }}</td>
                    <td>{{ $a->lpi_email }}</td>
                    <td>
                        @if($a->proposalstatus == 'accepted')
                            <span class="badge badge-success">Accepted</span>
                        @elseif($a->proposalstatus == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>{{ $a->statusdate ? date('d-m-Y', strtotime($a->statusdate)) : '—' }}</td>
                    <td>
                        @php
                            $proj = \App\Models\Project::find($a->project_id);
                            $progInactive = $proj && $proj->program && !$proj->programIsActive();
                            $hasClaimed = $a->proposalstatus == 'accepted';
                            $hasGraded = $proj && $proj->hasStatus(\App\Models\Project::STATUS_GRADED);
                            $hasProgressGrade = $proj && \App\Models\ProgressReportGrading::where('project_id', $proj->id)->where('user_id', auth()->id())->exists();
                            $hasFinalGrade = $proj && \App\Models\FinalReportGrading::where('project_id', $proj->id)->where('user_id', auth()->id())->exists();
                        @endphp
                        @if($progInactive)
                            <span class="text-muted" style="font-size:12px;"><i class="fas fa-lock" style="color:var(--color-danger);"></i> Inactive Research Call</span>
                        @elseif($hasGraded)
                            <span class="text-muted">Graded</span>
                        @elseif($hasClaimed)
                            <a href="{{ route('projects.grading', $a->project_id) }}" class="btn-sm btn-primary" style="font-size:11px;padding:4px 10px;text-decoration:none;">
                                <i class="fas fa-star" style="font-size:11px;"></i> Grade Project
                            </a>
                        @elseif(!$a->proposalstatus || $a->proposalstatus == 'pending')
                            <button type="button" class="btn-sm btn-primary" style="font-size:11px;padding:4px 10px;text-decoration:none;border:none;cursor:pointer;" onclick="openWorkflowModal({{ $a->project_id }}, 'accept-proposal')">
                                <i class="fas fa-check-circle" style="font-size:11px;"></i> Review
                            </button>
                        @else
                            <span class="text-muted">All Done</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#assignmentsTable').DataTable({ order: [[6, 'desc']] });
    });
</script>
@endpush
