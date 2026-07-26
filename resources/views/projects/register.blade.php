@extends('layouts.app')

@section('title', 'Register Project - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-plus-circle"></i> Register Project</h1>
    <a href="{{ route('projects.available') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Project Details from Conf-Tool</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Project ID:</strong></div>
                    <div class="col-sm-9"><code>{{ $confProject->old_project_id }}</code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Title:</strong></div>
                    <div class="col-sm-9">{{ $confProject->title }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Research Call:</strong></div>
                    <div class="col-sm-9">{{ $confProject->program->program_title }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Grant:</strong></div>
                    <div class="col-sm-9">{{ $confProject->program->grant->grant_code ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Category:</strong></div>
                    <div class="col-sm-9"><span class="badge badge-info">{{ $confProject->program->grant->grant_title ?? 'N/A' }}</span></div>
                </div>

                <hr>
                <form action="{{ route('projects.store-registration') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $confProject->id }}">

                    <div class="form-group">
                        <label>Project Objectives</label>
                        <textarea name="objectives" class="form-control @error('objectives') is-invalid @enderror" rows="3">{{ old('objectives') }}</textarea>
                        @error('objectives') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Methodology</label>
                        <textarea name="methodology" class="form-control @error('methodology') is-invalid @enderror" rows="3">{{ old('methodology') }}</textarea>
                        @error('methodology') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-control @error('expected_outcomes') is-invalid @enderror" rows="3">{{ old('expected_outcomes') }}</textarea>
                        @error('expected_outcomes') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Requested Budget (QAR)</label>
                        <input type="number" name="requested_budget_qar" class="form-control @error('requested_budget_qar') is-invalid @enderror" value="{{ old('requested_budget_qar') }}" step="0.01" min="0">
                        @error('requested_budget_qar') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Pillars</label>
                        <div class="row">
                            @forelse($pillars as $pillar)
                            <div class="col-md-6">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="pillars[]" value="{{ $pillar->id }}" id="pillar{{ $pillar->id }}"
                                        {{ in_array($pillar->id, old('pillars', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="pillar{{ $pillar->id }}">{{ $pillar->name }}</label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><p class="text-muted">No pillars defined.</p></div>
                            @endforelse
                        </div>
                        @error('pillars') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Colleges/Institutes</label>
                        <div class="row">
                            @forelse($colleges as $college)
                            <div class="col-md-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="colleges[]" value="{{ $college->id }}" id="college{{ $college->id }}"
                                        {{ in_array($college->id, old('colleges', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="college{{ $college->id }}">{{ $college->name }}</label>
                                </div>
                            </div>
                            @empty
                            <div class="col-12"><p class="text-muted">No colleges defined.</p></div>
                            @endforelse
                        </div>
                        @error('colleges') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Additional Stakeholder Emails</label>
                        <input type="text" name="stakeholder_emails" class="form-control" value="{{ old('stakeholder_emails') }}" placeholder="email1@qu.edu.qa, email2@qu.edu.qa">
                        <small class="form-text text-muted">Comma-separated email addresses of other stakeholders.</small>
                    </div>

                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Register Project</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
