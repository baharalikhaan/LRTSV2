@extends('layouts.app')

@section('title', $cycle->program_title . ' - RTS')

@section('content')
    @if(!$cycle->isActive())
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-3" role="alert" style="border-radius:6px;">
        <i class="fas fa-lock"></i>
        <span>This research call is <strong>inactive</strong> — its deadlines have passed. Projects under this research call cannot be manipulated.</span>
    </div>
    @endif

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-sync-alt"></i> {{ $cycle->program_title }}</h1>
    <div>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('cycles.edit', $cycle->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        @endif
            <a href="{{ route('cycles') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> All Research Calls</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Research Call Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Grant:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge badge-dark">{{ $cycle->grant->grant_code ?? 'N/A' }}</span>
                        {{ $cycle->grant->grant_name ?? '' }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Grant Code:</strong></div>
                    <div class="col-sm-8"><span class="badge badge-info">{{ $cycle->grant->grant_code ?? $cycle->grant->grant_title ?? 'N/A' }}</span></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Status:</strong></div>
                    <div class="col-sm-8">
                        @if($cycle->isActive())
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Prog. Report Deadline:</strong></div>
                    <div class="col-sm-8">{{ $cycle->prog_rpt_deadline ? $cycle->prog_rpt_deadline->format('d F Y H:i') : '—' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Final Report Deadline:</strong></div>
                    <div class="col-sm-8">{{ $cycle->final_rpt_deadline ? $cycle->final_rpt_deadline->format('d F Y H:i') : '—' }}</div>
                </div>
                @if($cycle->description)
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Description:</strong></div>
                    <div class="col-sm-8">{{ $cycle->description }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-simple"></i> Summary</h5>
            </div>
            <div class="card-body text-center">
                <h3 class="text-primary">{{ $cycle->projects->count() }}</h3>
                <p class="text-muted mb-0">Total Projects</p>
                <hr>
                <h3 class="text-success">{{ $cycle->projects->filter(fn($p) => $p->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $p->lpi_id)->count() }}</h3>
                <p class="text-muted mb-0">Registered Projects</p>
                <hr>
                <h3 class="text-warning">{{ $cycle->projects->filter(fn($p) => !$p->hasStatus(\App\Models\Project::STATUS_REGISTERED) || !$p->lpi_id)->count() }}</h3>
                <p class="text-muted mb-0">Unregistered Projects</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> Projects Imported from Conf-Tool</h5>
        @if(Auth::user()->isAdmin() && $cycle->isActive() && $cycle->projects->contains(function ($p) { return $p->reviewers->isEmpty(); }))
        <a href="{{ route('projects.assign-review', $cycle->id) }}" class="btn btn-sm btn-light">
            <i class="fas fa-user-check"></i> Assign Reviewers
        </a>
        @endif
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0" id="projectsTable">
            <thead>
                <tr>
                    <th>Old Project ID</th>
                    <th>Title</th>
                    <th>LPI Email</th>
                    <th>Grant Type</th>
                    <th>Status</th>
                    <th>Proposal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cycle->projects as $cp)
                <tr>
                    <td><code>{{ $cp->old_project_id }}</code></td>
                    <td>{{ \Illuminate\Support\Str::limit($cp->title, 60) }}</td>
                    <td>{{ $cp->lpi->email ?? 'N/A' }}</td>
                    <td><span class="badge badge-info">{{ $cp->program->grant->grant_code ?? $cp->program->grant->grant_title ?? 'N/A' }}</span></td>
                    <td>
                        @if($cp->hasStatus(\App\Models\Project::STATUS_REGISTERED) && $cp->lpi_id)
                            <span class="badge badge-success">Registered</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($cp->proposal_filename)
                            <span class="badge badge-dark" title="{{ $cp->proposal_filename }}">
                                <i class="fas fa-file-pdf"></i> Available
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No projects imported yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#projectsTable').DataTable({ order: [[4, 'asc'], [0, 'asc']] });
    });
</script>
@endpush
