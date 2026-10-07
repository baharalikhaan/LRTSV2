@extends('layouts.app')

@section('title', 'Reviewer User Manual — RTS')

@section('content')
@include('about.manuals._styles')

<div class="page-head">
    <div>
        <h1><i class="fas fa-user-check"></i> Reviewer User Manual</h1>
        <p>Reviewer — end-to-end reference for proposal decisions, report grading and verification.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('about.help', ['tab' => 'reviewer']) }}" class="btn-secondary btn-sm"><i class="fas fa-circle-question"></i> Help Center</a>
    </div>
</div>

@include('about.manuals.reviewer')
@endsection
