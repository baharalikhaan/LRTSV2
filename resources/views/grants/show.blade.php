@extends('layouts.app')

@section('title', $grant->grant_code . ' - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-trophy"></i> {{ $grant->grant_code }}: {{ $grant->grant_name }}</h1>
    <div>
        <a href="{{ route('grant-types.edit', $grant->id) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('grant-types.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> All Grant Types</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4"><strong>Code:</strong></div>
                    <div class="col-sm-8"><span class="badge badge-dark">{{ $grant->grant_code }}</span></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Name:</strong></div>
                    <div class="col-sm-8">{{ $grant->grant_name }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Category:</strong></div>
                    <div class="col-sm-8"><span class="badge badge-{{ $grant->category == 'student' ? 'info' : 'dark' }}">{{ $grant->category }}</span></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Funding Agency:</strong></div>
                    <div class="col-sm-8">{{ $grant->funding_agency ?? '—' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Max Duration:</strong></div>
                    <div class="col-sm-8">{{ $grant->max_duration_years ? $grant->max_duration_years . ' year(s)' : '—' }}</div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Status:</strong></div>
                    <div class="col-sm-8">
                        <span class="badge badge-{{ $grant->is_active ? 'success' : 'secondary' }}">
                            {{ $grant->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                @if($grant->description)
                <hr>
                <div class="row">
                    <div class="col-sm-4"><strong>Description:</strong></div>
                    <div class="col-sm-8">{{ $grant->description }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-sync-alt"></i> Research Calls ({{ $grant->programs->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($grant->programs as $cycle)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('programs.show', $cycle->id) }}">{{ $cycle->program_title }}</a>
                            <span class="badge badge-{{ $cycle->isActive() ? 'success' : 'secondary' }} badge-pill">
                                {{ $cycle->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No research calls yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
