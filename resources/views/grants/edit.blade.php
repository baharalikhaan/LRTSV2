@extends('layouts.app')

@section('title', 'Edit Grant Type - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-trophy"></i> Edit Grant Type</h1>
        <p>Update the grant type details below.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('grant-types.show', $grant->id) }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Grant Type
        </a>
    </div>
</div>

@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <h2><i class="fas fa-edit"></i> Grant Type Details</h2>
    </div>
    <div class="panel-body">
        <form action="{{ route('grant-types.update', $grant->id) }}" method="POST" id="grantEditForm">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> Please fix the errors below.
                </div>
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="grant_code" class="form-label" style="font-size:12px;font-weight:600;">Grant Type Code <span class="text-danger">*</span></label>
                    <input type="text" name="grant_code" id="grant_code"
                           class="form-control @error('grant_code') is-invalid @enderror"
                           value="{{ old('grant_code', $grant->grant_code) }}"
                           placeholder="e.g. CG-2025-001" required>
                    @error('grant_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="grant_name" class="form-label" style="font-size:12px;font-weight:600;">Grant Type Name <span class="text-danger">*</span></label>
                    <input type="text" name="grant_name" id="grant_name"
                           class="form-control @error('grant_name') is-invalid @enderror"
                           value="{{ old('grant_name', $grant->grant_name) }}"
                           placeholder="e.g. Competitive Grant 2025" required>
                    @error('grant_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="category" class="form-label" style="font-size:12px;font-weight:600;">Category <span class="text-danger">*</span></label>
                    <select name="category" id="category"
                            class="form-select @error('category') is-invalid @enderror" required>
                        <option value="student" {{ old('category', $grant->category) === 'student' ? 'selected' : '' }}>Student</option>
                        <option value="regular" {{ old('category', $grant->category) === 'regular' ? 'selected' : '' }}>Regular</option>
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="funding_agency" class="form-label" style="font-size:12px;font-weight:600;">Funding Agency</label>
                    <input type="text" name="funding_agency" id="funding_agency"
                           class="form-control @error('funding_agency') is-invalid @enderror"
                           value="{{ old('funding_agency', $grant->funding_agency) }}"
                           placeholder="e.g. QNRF">
                    @error('funding_agency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_duration_years" class="form-label" style="font-size:12px;font-weight:600;">Max Duration (Years)</label>
                    <input type="number" name="max_duration_years" id="max_duration_years" min="1" max="10"
                           class="form-control @error('max_duration_years') is-invalid @enderror"
                           value="{{ old('max_duration_years', $grant->max_duration_years) }}"
                           placeholder="e.g. 3">
                    @error('max_duration_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $grant->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active" style="font-size:12px;font-weight:600;">Active</label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label" style="font-size:12px;font-weight:600;">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Optional description...">{{ old('description', $grant->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('grant-types.show', $grant->id) }}" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                    Cancel
                </a>
                <button type="submit" class="btn-primary" style="border:none;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection