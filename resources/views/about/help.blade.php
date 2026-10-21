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
    $allTabs = [
        'general'  => ['icon' => 'fa-globe',       'label' => 'General'],
        'workflow' => ['icon' => 'fa-diagram-project', 'label' => 'System workflow'],
        'admin'    => ['icon' => 'fa-user-shield', 'label' => 'Administrator'],
        'lpi'      => ['icon' => 'fa-user-tie',    'label' => 'LPI'],
        'reviewer' => ['icon' => 'fa-user-check',  'label' => 'Reviewer'],
    ];

    // Role-based visibility: General and the System workflow overview are for
    // everyone; admins keep oversight of all role manuals, while an LPI sees
    // General + System workflow + LPI and a reviewer General + System
    // workflow + Reviewer.
    $u = auth()->user();
    if ($u && $u->isAdmin()) {
        $tabs = $allTabs;
    } else {
        $tabs = [
            'general'  => $allTabs['general'],
            'workflow' => $allTabs['workflow'],
        ];
        if ($u && $u->isLPI()) {
            $tabs['lpi'] = $allTabs['lpi'];
        }
        if ($u && $u->isReviewer()) {
            $tabs['reviewer'] = $allTabs['reviewer'];
        }
    }

    // A tab outside the allowed set (e.g. ?tab=admin opened by an LPI)
    // silently falls back to the role default.
    $tab = request('tab');
    if (!is_string($tab) || !array_key_exists($tab, $tabs)) {
        if ($u && $u->isAdmin()) {
            $tab = 'admin';
        } elseif ($u && $u->isReviewer() && isset($tabs['reviewer'])) {
            $tab = 'reviewer';
        } elseif ($u && $u->isLPI() && isset($tabs['lpi'])) {
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
