@extends('layouts.app')

@section('title', 'Help Center — RTS')

@section('content')
@include('about.manuals._styles')

<div class="page-head">
    <div>
        <h1><i class="fas fa-circle-question"></i> Help Center</h1>
        <p>Role-based manuals and guides for using the Research Tracking System.</p>
    </div>
</div>

@php
    $tabs = [
        'general'  => ['icon' => 'fa-globe',       'label' => 'General'],
        'admin'    => ['icon' => 'fa-user-shield', 'label' => 'Administrator'],
        'lpi'      => ['icon' => 'fa-user-tie',    'label' => 'LPI'],
        'reviewer' => ['icon' => 'fa-user-check',  'label' => 'Reviewer'],
    ];

    $tab = request('tab');
    if (!is_string($tab) || !array_key_exists($tab, $tabs)) {
        $u = auth()->user();
        if ($u && $u->isAdmin()) {
            $tab = 'admin';
        } elseif ($u && $u->isReviewer()) {
            $tab = 'reviewer';
        } elseif ($u && $u->isLPI()) {
            $tab = 'lpi';
        } else {
            $tab = 'general';
        }
    }
@endphp

{{-- Tab navigation (underline style, same as System Settings) --}}
<div class="help-tabs">
    @foreach($tabs as $key => $t)
    <a href="{{ route('about.help', ['tab' => $key]) }}" class="help-tab {{ $tab === $key ? 'active' : '' }}">
        <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
    </a>
    @endforeach
</div>

{{-- Active manual --}}
@include('about.manuals.' . $tab)
@endsection

@include('about.manuals._ai-assistant')
