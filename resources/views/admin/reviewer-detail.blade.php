@extends('layouts.app')
@section('title', 'Reviewer Detail - RTS')
@section('content')

<style>
.rd-header{background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:var(--fluent-radius-md,6px);padding:18px 22px;box-shadow:var(--fluent-depth-2);display:flex;align-items:center;gap:16px;margin-bottom:18px}
.rd-avatar{width:48px;height:48px;border-radius:var(--fluent-radius-md,6px);background:var(--brand-500,#8d1b3d);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0}
.rd-info h4{font-weight:600;font-size:15px;margin:0;color:var(--ink-800,#241f2a)}
.rd-info .rd-sub{font-size:11px;color:var(--ink-400,#8b8592);margin-top:2px;display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.rd-info .rd-sub i{font-size:10px;color:var(--brand-400,#b8496b)}
.rd-card{background:#fff;border:1px solid var(--ink-100,#eeedf0);border-radius:var(--fluent-radius-md,6px);box-shadow:var(--fluent-depth-2);overflow:hidden}
.rd-card-head{padding:12px 16px;border-bottom:1px solid var(--ink-100,#eeedf0);font-size:13px;font-weight:600;color:var(--ink-800,#241f2a)}
.rd-empty{text-align:center;padding:40px 16px;color:var(--ink-400,#8b8592)}
.rd-empty i{font-size:36px;margin-bottom:10px;opacity:.3;display:block}
.rd-tbl{width:100%;border-collapse:collapse;font-size:12px}
.rd-tbl th{background:var(--sand-50,#faf7f0);font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:var(--ink-500,#675f6e);padding:10px 12px;border-bottom:2px solid var(--brand-500,#8d1b3d);text-align:left}
.rd-tbl td{padding:10px 12px;border-bottom:1px solid var(--ink-100,#eeedf0);color:var(--ink-700,#38333e)}
.rd-tbl tr:hover td{background:var(--sand-50,#faf7f0)}
.rd-tbl .rd-prog{font-weight:500;color:var(--brand-500,#8d1b3d)}
.rd-tbl .rd-cell{text-align:center;font-weight:500}
.rd-avg{display:inline-flex;align-items:center;gap:4px;background:var(--brand-100,#f3d2da);color:var(--brand-700,#63102b);font-weight:700;font-size:12px;padding:2px 8px;border-radius:4px}
.rd-avg i{font-size:10px;color:var(--gold-400,#e3b04b)}
.rd-footer-bar{padding:12px 16px;border-top:1px solid var(--ink-100,#eeedf0);display:flex;align-items:center;gap:10px;background:var(--sand-50,#faf7f0)}
.rd-back{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--brand-500,#8d1b3d);text-decoration:none;margin-bottom:12px}
.rd-back:hover{text-decoration:underline}
</style>

<a href="{{ route('reviewer-grading.index') }}" class="rd-back"><i class="fa-solid fa-arrow-left"></i> Back to Reviewers</a>

<div class="rd-header">
    <div class="rd-avatar">{{ collect(explode(' ', $user->name))->map(fn($w)=>substr($w,0,1))->take(2)->implode('') }}</div>
    <div class="rd-info">
        <h4>{{ $user->name }}</h4>
        <div class="rd-sub">
            <span><i class="fa-solid fa-envelope"></i> {{ $user->email }}</span>
            <span><i class="fa-solid fa-tag"></i> {{ $user->type }}</span>
            <span><i class="fa-solid fa-diagram-project"></i> {{ $totalProjects }} projects reviewed</span>
        </div>
    </div>
</div>

<div class="rd-card">
    @if(count($ratings) === 0)
        <div class="rd-empty">
            <i class="fa-solid fa-star"></i>
            <p style="font-size:13px;margin:0;">No ratings recorded yet for this reviewer.</p>
        </div>
    @else
        <div class="rd-card-head">Rating History — Per Research Call</div>
        <div style="overflow-x:auto;">
            <table class="rd-tbl">
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
                            <td class="rd-prog">{{ $rating->program->program_title ?? 'N/A' }}</td>
                            <td class="rd-cell">{{ $rating->conflict }}</td>
                            <td class="rd-cell">{{ $rating->responsiveness }}</td>
                            <td class="rd-cell">{{ $rating->comprehensiveness }}</td>
                            <td class="rd-cell">{{ $rating->no_reviewers }}</td>
                            <td class="rd-cell">{{ $rating->behaviour }}</td>
                            <td class="rd-cell">
                                <span class="rd-avg">
                                    <i class="fa-solid fa-star"></i>
                                    {{ number_format($avg, 1) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $overallAvg = $ratings->avg(function($r) {
                return ($r->conflict + $r->responsiveness + $r->comprehensiveness + $r->no_reviewers + $r->behaviour) / 5;
            });
        @endphp
        <div class="rd-footer-bar">
            <strong style="font-size:12px;color:var(--ink-700,#38333e);">Overall Average (all research calls):</strong>
            <span class="rd-avg" style="font-size:13px;">
                <i class="fa-solid fa-star"></i>
                {{ number_format($overallAvg, 1) }}
            </span>
        </div>
    @endif
</div>

@endsection
