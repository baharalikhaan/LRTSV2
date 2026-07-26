@extends('layouts.app')
@section('title', 'Reviewer Detail')
@section('content')

<style>
    :root {
        --color-brand-50: #fbeef1;
        --color-brand-100: #f3d2da;
        --color-brand-200: #e6a5b6;
        --color-brand-300: #d3738f;
        --color-brand-400: #b8496b;
        --color-brand-500: #8d1b3d;
        --color-brand-600: #7a1636;
        --color-brand-700: #63102b;
        --color-brand-800: #4c0c21;
        --color-brand-900: #350818;
        --color-sand-50: #faf7f0;
        --color-sand-100: #f2ead6;
        --color-sand-200: #e4d3ac;
        --color-sand-300: #d3b57c;
        --color-sand-400: #c39c58;
        --color-sand-500: #ab8140;
        --color-sand-600: #8c6733;
        --color-sand-700: #6d4f29;
        --color-sand-800: #503a1e;
        --color-sand-900: #362715;
        --color-gold-400: #e3b04b;
        --color-gold-500: #cf9a2f;
        --color-gold-600: #a97b22;
        --color-ink-50: #f7f7f8;
        --color-ink-100: #eeedf0;
        --color-ink-200: #d8d6dc;
        --color-ink-300: #b4b0ba;
        --color-ink-400: #8b8592;
        --color-ink-500: #675f6e;
        --color-ink-600: #4c4553;
        --color-ink-700: #38333e;
        --color-ink-800: #241f2a;
        --color-ink-900: #16131a;
        --color-success: #1f8a5f;
        --color-warning: #cf9a2f;
        --color-danger: #b3261e;
        --color-info: #2563a8;
        --fluent-depth-2: 0 1px 2px rgba(22,19,26,.07), 0 0px 1px rgba(22,19,26,.06);
        --fluent-depth-4: 0 2px 4px rgba(22,19,26,.09), 0 0px 2px rgba(22,19,26,.07);
        --fluent-depth-8: 0 4px 8px rgba(22,19,26,.12), 0 0px 2px rgba(22,19,26,.08);
        --fluent-depth-16: 0 8px 16px rgba(22,19,26,.16), 0 0px 2px rgba(22,19,26,.10);
    }

    * {
        font-family: 'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
    }

    .detail-header {
        background: #fff;
        border: 1px solid var(--color-ink-100);
        border-radius: 8px;
        padding: 20px 24px;
        box-shadow: var(--fluent-depth-2);
        display: flex;
        align-items: center;
        gap: 18px;
        margin-bottom: 20px;
    }
    .detail-avatar {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        background: var(--color-brand-500);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        flex-shrink: 0;
    }
    .detail-info h4 {
        font-weight: 600;
        font-size: 16px;
        margin: 0;
        color: var(--color-ink-800);
    }
    .detail-info .sub {
        font-size: 12px;
        color: var(--color-ink-400);
        margin-top: 2px;
    }
    .detail-info .sub span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .detail-info .sub i {
        font-size: 10px;
        color: var(--color-brand-400);
    }

    .rating-table {
        font-size: 13px;
        border-collapse: collapse;
        width: 100%;
    }
    .rating-table th {
        background: var(--color-sand-50);
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--color-ink-500);
        padding: 10px 12px;
        border-bottom: 2px solid var(--color-brand-500);
        text-align: left;
    }
    .rating-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--color-ink-100);
        color: var(--color-ink-700);
    }
    .rating-table tr:hover td {
        background: var(--color-sand-50);
    }
    .rating-table .program-title {
        font-weight: 500;
        color: var(--color-brand-500);
    }
    .avg-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--color-brand-100);
        color: var(--color-brand-700);
        font-weight: 700;
        font-size: 12px;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .avg-badge i {
        font-size: 10px;
    }
    .star-indicator {
        color: var(--color-gold-400);
    }

    .rating-cell {
        text-align: center;
        font-weight: 500;
    }
</style>

<div class="row">
    <div class="col-md-12">

        {{-- Back link --}}
        <a href="{{ route('reviewer-grading.index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--color-brand-500);text-decoration:none;margin-bottom:12px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Reviewers
        </a>

        {{-- Header card --}}
        <div class="detail-header">
            <div class="detail-avatar">
                {{ collect(explode(' ', $user->name))->map(function($w) { return substr($w, 0, 1); })->take(2)->implode('') }}
            </div>
            <div class="detail-info">
                <h4>{{ $user->name }}</h4>
                <div class="sub">
                    <span><i class="fa-solid fa-envelope"></i> {{ $user->email }}</span>
                    &middot;
                    <span><i class="fa-solid fa-tag"></i> {{ $user->type }}</span>
                    &middot;
                    <span><i class="fa-solid fa-diagram-project"></i> {{ $totalProjects }} projects reviewed</span>
                </div>
            </div>
        </div>

        {{-- Rating History (per Research Call) --}}
        <div style="background:#fff;border:1px solid var(--color-ink-100);border-radius:8px;box-shadow:var(--fluent-depth-2);overflow:hidden;">
            @if(count($ratings) === 0)
                <div style="text-align:center;padding:40px 16px;color:var(--color-ink-400);">
                    <i class="fa-solid fa-star" style="font-size:40px;margin-bottom:10px;opacity:.3;"></i>
                    <p style="font-size:13px;margin:0;">No ratings recorded yet for this reviewer.</p>
                </div>
            @else
                <div style="padding:14px 16px;border-bottom:1px solid var(--color-ink-100);">
                    <strong style="font-size:14px;color:var(--color-ink-800);">Rating History — Per Research Call</strong>
                </div>
                <div style="overflow-x:auto;">
                    <table class="rating-table">
                        <thead>
                            <tr>
                                <th style="min-width:160px;">Research Call</th>
                                <th style="text-align:center;">Conflict</th>
                                <th style="text-align:center;">Responsiveness</th>
                                <th style="text-align:center;">Comprehensiveness</th>
                                <th style="text-align:center;">Reviews</th>
                                <th style="text-align:center;">Behaviour</th>
                                <th style="text-align:center;">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ratings as $rating)
                                @php
                                    $avg = ($rating->conflict + $rating->responsiveness + $rating->comprehensiveness + $rating->no_reviewers + $rating->behaviour) / 5;
                                @endphp
                                <tr>
                                    <td class="program-title">{{ $rating->program->program_title ?? 'N/A' }}</td>
                                    <td class="rating-cell">{{ $rating->conflict }}</td>
                                    <td class="rating-cell">{{ $rating->responsiveness }}</td>
                                    <td class="rating-cell">{{ $rating->comprehensiveness }}</td>
                                    <td class="rating-cell">{{ $rating->no_reviewers }}</td>
                                    <td class="rating-cell">{{ $rating->behaviour }}</td>
                                    <td class="rating-cell">
                                        <span class="avg-badge">
                                            <i class="fa-solid fa-star star-indicator"></i>
                                            {{ number_format($avg, 1) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Overall Average --}}
                @php
                    $overallAvg = $ratings->avg(function($r) {
                        return ($r->conflict + $r->responsiveness + $r->comprehensiveness + $r->no_reviewers + $r->behaviour) / 5;
                    });
                @endphp
                <div style="padding:12px 16px;border-top:1px solid var(--color-ink-100);display:flex;align-items:center;gap:10px;background:var(--color-sand-50);">
                            <strong style="font-size:13px;color:var(--color-ink-700);">Overall Average (all research calls):</strong>
                    <span class="avg-badge" style="font-size:14px;">
                        <i class="fa-solid fa-star star-indicator"></i>
                        {{ number_format($overallAvg, 1) }}
                    </span>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
