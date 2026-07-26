@extends('layouts.app')

@section('title', 'Grade Project - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-gavel"></i> Grade Project #{{ $project->id }}</h1>
    <a href="{{ route('gradedProjects') }}" class="btn btn-sm btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <!-- Project Info -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle"></i> Project Details</div>
            <div class="card-body">
                <h5>{{ $project->title }}</h5>
                <dl class="row">
                    <dt class="col-sm-3">LPI:</dt>
                    <dd class="col-sm-9">{{ $project->lpi->name ?? '—' }}</dd>
                    <dt class="col-sm-3">Research Call:</dt>
                    <dd class="col-sm-9">{{ $project->program->program_title ?? '—' }}</dd>
                    <dt class="col-sm-3">College Decision:</dt>
                    <dd class="col-sm-9">{{ $project->college_decision ?? '—' }}</dd>
                    <dt class="col-sm-3">Grant:</dt>
                    <dd class="col-sm-9">{{ $project->program->grant->grant_code ?? '—' }}</dd>
                    <dt class="col-sm-3">Budget:</dt>
                    <dd class="col-sm-9">{{ $project->requested_budget_qar ? number_format($project->requested_budget_qar, 2) . ' QAR' : '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Current Grade -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-star"></i> Current Grade</div>
            <div class="card-body text-center">
                <p class="text-muted mb-0">Not yet graded</p>
            </div>
        </div>

        @if(Auth::user()->isAdmin() && $finalDraft)
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-check-circle"></i> Final Decision</div>
            <div class="card-body text-center">
                <h3 class="text-{{ $finalDraft->remarks == 'Accepted' ? 'success' : 'danger' }}">
                    {{ $finalDraft->remarks }}
                </h3>
                <p>Final Grade: {{ $finalDraft->grade }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Grade Form -->
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-edit"></i> Submit Grade</div>
            <div class="card-body">
                <form action="{{ route('grading.saveGrade', $project->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Reviewer Grade (0-100)</label>
                        <input type="number" name="reviewer_grade" class="form-control" min="0" max="100" step="0.1"
                            value="{{ old('reviewer_grade', '') }}">
                    </div>
                    <div class="form-group">
                        <label>Outcome Grade (0-100)</label>
                        <input type="number" name="outcome_grade" class="form-control" min="0" max="100" step="0.1"
                            value="{{ old('outcome_grade', '') }}">
                    </div>
                    <div class="form-group">
                        <label>Comments</label>
                        <textarea name="comments" class="form-control" rows="4">{{ old('comments', '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Grade
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Final Decision (Admin only) -->
    @if(Auth::user()->isAdmin())
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-gavel"></i> Final Decision (Admin)</div>
            <div class="card-body">
                <form action="{{ route('grading.submitFinalGrade', $project->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Final Grade (0-100)</label>
                        <input type="number" name="grade" class="form-control" min="0" max="100" step="0.1" required
                            value="{{ old('grade', $finalDraft->grade ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label>Decision</label>
                        <select name="final_decision" class="form-control" required>
                            <option value="Accepted" {{ old('final_decision', $finalDraft->remarks ?? '') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="Rejected" {{ old('final_decision', $finalDraft->remarks ?? '') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="Pending" {{ old('final_decision', $finalDraft->remarks ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Revisions" {{ old('final_decision', $finalDraft->remarks ?? '') == 'Revisions' ? 'selected' : '' }}>Revisions Required</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Submit Final Decision
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
