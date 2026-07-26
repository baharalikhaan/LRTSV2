@extends('layouts.app')

@section('title', 'Assign Reviewers - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-user-check"></i> Assign Reviewers</h1>
    <div>
        <span class="badge badge-dark">{{ $cycle->grant->grant_code ?? 'N/A' }}</span>
        <span class="badge badge-info">{{ $cycle->program_title }}</span>
        <a href="{{ route('programs.show', $cycle->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Research Call</a>
    </div>
</div>

@if($projects->isEmpty())
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> All projects in this cycle already have reviewers assigned.
</div>
@else
<form action="{{ route('projects.bulk-assign') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-users"></i> {{ $projects->count() }} Unassigned Projects</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th>Project</th>
                        <th>Grant Type</th>
                        <th>Reviewers (select one or more)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $index => $project)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $project->title }}</strong><br>
                            <small class="text-muted">ID: <code>{{ $project->old_project_id }}</code></small>
                        </td>
                        <td><span class="badge badge-info">{{ $project->program->grant->grant_title ?? 'N/A' }}</span></td>
                        <td>
                            @foreach($reviewers as $reviewer)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input"
                                    name="assignments[{{ $index }}][reviewers][]"
                                    value="{{ $reviewer->id }}"
                                    id="r{{ $project->id }}_{{ $reviewer->id }}">
                                <label class="custom-control-label" for="r{{ $project->id }}_{{ $reviewer->id }}">
                                    {{ $reviewer->name }}
                                    <small class="text-muted">({{ $reviewer->email }})</small>
                                    @if($reviewer->pillar_names)
                                        <span class="badge badge-secondary">{{ $reviewer->pillar_names }}</span>
                                    @endif
                                </label>
                            </div>
                            @endforeach
                            <input type="hidden" name="assignments[{{ $index }}][project_id]" value="{{ $project->id }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Assign Selected Reviewers</button>
        </div>
    </div>
</form>
@endif
@endsection
