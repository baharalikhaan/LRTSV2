@extends('layouts.app')

@section('title', 'LPI User Manual — RTS')

@section('content')
@include('about.manuals._styles')

<div class="page-head">
    <div>
        <h1><i class="fas fa-user-tie"></i> LPI User Manual</h1>
        <p>Lead Principal Investigator — end-to-end operational reference for the Research Tracking System.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('about.help', ['tab' => 'lpi']) }}" class="btn-secondary btn-sm"><i class="fas fa-circle-question"></i> Help Center</a>
    </div>
</div>

@include('about.manuals.lpi')
@endsection
