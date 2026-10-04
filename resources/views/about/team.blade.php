@extends('layouts.app')

@section('title', 'Our Team — RTS')

@section('content')
<div class="team-page">

    {{-- Page header --}}
    <div class="panel" style="margin-bottom:22px;">
        <div class="panel-body" style="padding:28px 28px; text-align:center;">
            <i class="fas fa-users" style="font-size:34px; color:var(--color-brand-500); margin-bottom:10px;"></i>
            <h1 style="font-size:24px; font-weight:700; color:var(--color-ink-900); margin:0 0 4px;">Our Team</h1>
            <p style="font-size:13.5px; color:var(--color-ink-500); margin:0; max-width:520px; margin-inline:auto;">
                The dedicated team behind the Research Tracking System — committed to serving Qatar University's research community.
            </p>
        </div>
    </div>

    {{-- Team Members Grid --}}
    <div class="team-grid">
        @foreach($teamMembers as $m)
            @php
                $initials = collect(explode(' ', $m->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $colorMap = ['brand', 'info', 'gold', 'success'];
                $cIdx = ($m->id - 1) % count($colorMap);
                $colorKey = $colorMap[$cIdx];
                $bgColors = [
                    'brand'   => ['bg' => '#fdf2f4', 'fg' => 'var(--color-brand-600)', 'tile' => 'var(--color-brand-100)'],
                    'info'    => ['bg' => '#eff6ff', 'fg' => '#1d4ed8',              'tile' => '#dbeafe'],
                    'gold'    => ['bg' => '#fffbeb', 'fg' => '#b45309',              'tile' => '#fef3c7'],
                    'success' => ['bg' => '#ecfdf5', 'fg' => '#047857',              'tile' => '#d1fae5'],
                ];
                $c = $bgColors[$colorKey];
            @endphp
            <div class="panel team-card" style="border:1px solid var(--color-ink-100);">
                <div class="panel-body" style="padding:22px 20px; display:flex; flex-direction:column; align-items:center; text-align:center; background:{{ $c['bg'] }}; border-radius:6px;">
                    <div style="width:56px; height:56px; border-radius:8px; background:{{ $c['tile'] }}; color:{{ $c['fg'] }}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:17px; margin-bottom:14px; box-shadow:var(--fluent-depth-2); flex-shrink:0;">
                        {{ $initials }}
                    </div>

                    {{-- Name --}}
                    <h3 style="font-size:15px; font-weight:600; color:var(--color-ink-800); margin:0 0 3px;">{{ $m->name }}</h3>

                    {{-- Role --}}
                    <div style="font-size:12px; font-weight:500; color:var(--color-brand-500); margin-bottom:2px;">{{ $m->role }}</div>

                    {{-- Introduction --}}
                    <div style="font-size:11px; color:var(--color-ink-400); margin-bottom:10px; line-height:1.3;">{{ $m->introduction }}</div>

                    {{-- Email link --}}
                    <a href="mailto:{{ $m->email }}" style="font-size:12px; color:var(--color-brand-500); text-decoration:none; display:inline-flex; align-items:center; gap:6px; margin-bottom:6px;">
                        <i class="fas fa-envelope" style="font-size:11px;"></i>
                        {{ $m->email }}
                    </a>

                    {{-- Phone --}}
                    @if($m->phone)
                        <div style="font-size:11.5px; color:var(--color-ink-500); display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-phone" style="font-size:10px; color:var(--color-ink-400);"></i>
                            {{ $m->phone }}
                        </div>
                    @endif

                    {{-- Address --}}
                    @if($m->address)
                        <div style="font-size:11px; color:var(--color-ink-400); margin-top:4px;">
                            <i class="fas fa-map-marker-alt" style="font-size:10px; margin-right:4px;"></i>
                            {{ trim($m->address) }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

@push('styles')
<style>
.team-page .team-grid {
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:18px;
    margin-bottom:24px;
}
.team-card {
    transition: box-shadow .2s ease, transform .15s ease;
}
.team-card:hover {
    box-shadow: var(--fluent-depth-8);
    transform: translateY(-2px);
}

@media (max-width:1100px) {
    .team-page .team-grid { grid-template-columns:repeat(3, 1fr); }
}
@media (max-width:820px) {
    .team-page .team-grid { grid-template-columns:repeat(2, 1fr); }
}
@media (max-width:480px) {
    .team-page .team-grid { grid-template-columns:1fr; }
}
</style>
@endpush
