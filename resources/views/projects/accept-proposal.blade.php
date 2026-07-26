@extends('layouts.app')

@section('title', 'Review Proposal - RTS')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-check-circle"></i> Review Proposal</h1>
    <a href="{{ route('projects.my-assignments') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Assignments</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> {{ $assignment->project_title }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Project ID:</strong></div>
                    <div class="col-sm-8"><code>{{ $assignment->old_project_id }}</code></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Cycle:</strong></div>
                    <div class="col-sm-8">{{ $assignment->program_title }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Current Status:</strong></div>
                    <div class="col-sm-8">
                        @if($assignment->proposalstatus == 'accepted')
                            <span class="badge badge-success">Accepted</span>
                        @elseif($assignment->proposalstatus == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </div>
                </div>

                @if(!$assignment->proposalstatus || $assignment->proposalstatus == 'pending')
                <hr>
                <form action="{{ route('projects.accept-proposal-post') }}" method="POST">
                    @csrf
                    <input type="hidden" name="r_id" value="{{ $assignment->id }}">

                    <div class="form-group">
                        <label>Decision <span class="text-danger">*</span></label>
                        <div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input" name="accept" id="acceptYes" value="accepted" required>
                                <label class="custom-control-label text-success" for="acceptYes"><i class="fas fa-check"></i> Accept</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input" name="accept" id="acceptNo" value="rejected">
                                <label class="custom-control-label text-danger" for="acceptNo"><i class="fas fa-times"></i> Reject</label>
                            </div>
                        </div>
                        @error('accept') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Decision</button>
                </form>
                @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> You have already submitted your decision on this proposal.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
