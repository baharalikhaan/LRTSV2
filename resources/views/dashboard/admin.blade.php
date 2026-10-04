@extends('layouts.app')

@section('title', 'Admin Dashboard - RTS')

@section('content')
<div class="dash-layout">
    <div class="dash-main">

        {{-- Primary KPI cards --}}
        <div class="stat-grid">
            <a class="stat-card" href="{{ route('programs.index') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge maroon"><i class="fas fa-arrows-rotate"></i></div>
                </div>
                <div class="stat-value">{{ $totalPrograms ?? 0 }}</div>
                <div class="stat-label">Total Cycles</div>
                <div class="stat-subs">
                    <span class="stat-sub active">{{ $activeProgramsCount ?? 0 }} active</span>
                    <span class="stat-sub">{{ $inactiveProgramsCount ?? 0 }} inactive</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.available') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge info"><i class="fas fa-project-diagram"></i></div>
                </div>
                <div class="stat-value">{{ $totalProjects ?? 0 }}</div>
                <div class="stat-label">Total Projects</div>
                <div class="stat-subs">
                    <span class="stat-sub active">{{ $registeredProjects ?? 0 }} registered</span>
                    <span class="stat-sub">{{ $unregisteredProjects ?? 0 }} unregistered</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('admin-upload.index') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge gold"><i class="fas fa-file-upload"></i></div>
                </div>
                <div class="stat-value">{{ $reportsSubmitted ?? 0 }}</div>
                <div class="stat-label">Reports Submitted</div>
                <div class="stat-subs">
                    <span class="stat-sub">PR1: {{ $programStats->sum('submitted_pr1') }}</span>
                    <span class="stat-sub">PR2: {{ $programStats->sum('submitted_pr2') }}</span>
                    <span class="stat-sub">Readiness: {{ $programStats->sum('submitted_readiness') }}</span>
                    <span class="stat-sub">Final: {{ $programStats->sum('submitted_final') }}</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.reviewer-assignment') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge info"><i class="fas fa-user-check"></i></div>
                </div>
                <div class="stat-value">{{ $assignedProjects ?? 0 }}</div>
                <div class="stat-label">Assigned Reviewers</div>
                <div class="stat-subs">
                    <span class="stat-sub">{{ $unassignedProjects ?? 0 }} unassigned</span>
                </div>
            </a>

            <a class="stat-card" href="{{ route('projects.pending-reviews') }}">
                <div class="stat-top">
                    <div class="stat-icon-badge success"><i class="fas fa-check-double"></i></div>
                </div>
                <div class="stat-value">{{ $reportsReviewed ?? 0 }}</div>
                <div class="stat-label">Reports Reviewed</div>
                <div class="stat-subs">
                    <span class="stat-sub">PR1: {{ $programStats->sum('reviewed_pr1') }}</span>
                    <span class="stat-sub">PR2: {{ $programStats->sum('reviewed_pr2') }}</span>
                    <span class="stat-sub">Final: {{ $programStats->sum('reviewed_final') }}</span>
                </div>
            </a>
        </div>

        {{-- Research Call Summary --}}
        <div style="margin-top:18px;">
            <div class="panel">
                <div class="panel-head">
                    <h2><i class="fas fa-table-cells"></i> Research Call Summary</h2>
                </div>
                <div class="panel-body p-0" style="overflow-x:auto;">
                    <table class="fluent-table w-100" style="font-size:11px;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align:bottom; min-width:180px;">Research Call</th>
                                <th class="text-center" colspan="3" style="vertical-align:bottom; border-left:2px solid var(--ink-200); background:var(--ink-50,#f4f4f5);">Total Projects</th>
                                <th class="text-center" colspan="4" style="border-left:2px solid var(--ink-200); background:#fef3c7;">PR1</th>
                                <th class="text-center" colspan="4" style="border-left:2px solid var(--ink-200); background:#dbeafe;">PR2</th>
                                <th class="text-center" colspan="4" style="border-left:2px solid var(--ink-200); background:#d1fae5;">Final</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="border-left:2px solid var(--ink-200); font-size:10px; padding:4px 6px;">Total</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Reg.</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Non-Reg.</th>
                                <th class="text-center" style="border-left:2px solid var(--ink-200); font-size:10px; padding:4px 6px;">Submitted</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Pending</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Reviewed</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Rev. Pending</th>
                                <th class="text-center" style="border-left:2px solid var(--ink-200); font-size:10px; padding:4px 6px;">Submitted</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Pending</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Reviewed</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Rev. Pending</th>
                                <th class="text-center" style="border-left:2px solid var(--ink-200); font-size:10px; padding:4px 6px;">Submitted</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Pending</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Reviewed</th>
                                <th class="text-center" style="font-size:10px; padding:4px 6px;">Rev. Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($programStats as $stat)
                            <tr>
                                <td style="font-weight:500; font-size:11px; {{ $stat['is_active'] ? 'color:#7f1d1d;' : '' }}">{{ $stat['name'] }}</td>
                                {{-- Total Projects --}}
                                <td class="text-center" style="border-left:2px solid var(--ink-200);"><span class="pill info" style="font-size:10px; padding:1px 6px;">{{ $stat['total_projects'] }}</span></td>
                                <td class="text-center">
                                    @if($stat['registered'] > 0)
                                        <span class="pill" style="background:#d1fae5;color:#065f46;font-size:10px;padding:1px 5px;">{{ $stat['registered'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['non_registered'] > 0)
                                        <span class="pill warning" style="font-size:10px; padding:1px 5px;">{{ $stat['non_registered'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                {{-- PR1 --}}
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">
                                    <span style="font-size:10px;">{{ $stat['submitted_pr1'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($stat['pending_pr1'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['pending_pr1'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['reviewed_pr1'] > 0)
                                        <span class="pill success" style="font-size:10px; padding:1px 5px;">{{ $stat['reviewed_pr1'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['review_pending_pr1'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['review_pending_pr1'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                {{-- PR2 --}}
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">
                                    <span style="font-size:10px;">{{ $stat['submitted_pr2'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($stat['pending_pr2'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['pending_pr2'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['reviewed_pr2'] > 0)
                                        <span class="pill success" style="font-size:10px; padding:1px 5px;">{{ $stat['reviewed_pr2'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['review_pending_pr2'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['review_pending_pr2'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                {{-- Final --}}
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">
                                    <span style="font-size:10px;">{{ $stat['submitted_final'] }}</span>
                                </td>
                                <td class="text-center">
                                    @if($stat['pending_final'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['pending_final'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['reviewed_final'] > 0)
                                        <span class="pill success" style="font-size:10px; padding:1px 5px;">{{ $stat['reviewed_final'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($stat['review_pending_final'] > 0)
                                        <span class="pill gold" style="font-size:10px; padding:1px 5px;">{{ $stat['review_pending_final'] }}</span>
                                    @else
                                        <span style="color:var(--ink-300);">0</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="16"><div class="empty-state py-4"><i class="fas fa-inbox"></i><p class="mb-0">No research calls found</p></div></td></tr>
                            @endforelse
                        </tbody>
                        @if($programStats->count() > 0)
                        <tfoot>
                            <tr style="font-weight:700; background:var(--sand-50); font-size:11px;">
                                <td>Total</td>
                                <td class="text-center" style="border-left:2px solid var(--ink-200);"><span class="pill info" style="font-size:10px; padding:1px 6px;">{{ $programStats->sum('total_projects') }}</span></td>
                                <td class="text-center">{{ $programStats->sum('registered') }}</td>
                                <td class="text-center">{{ $programStats->sum('non_registered') }}</td>
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">{{ $programStats->sum('submitted_pr1') }}</td>
                                <td class="text-center">{{ $programStats->sum('pending_pr1') }}</td>
                                <td class="text-center">{{ $programStats->sum('reviewed_pr1') }}</td>
                                <td class="text-center">{{ $programStats->sum('review_pending_pr1') }}</td>
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">{{ $programStats->sum('submitted_pr2') }}</td>
                                <td class="text-center">{{ $programStats->sum('pending_pr2') }}</td>
                                <td class="text-center">{{ $programStats->sum('reviewed_pr2') }}</td>
                                <td class="text-center">{{ $programStats->sum('review_pending_pr2') }}</td>
                                <td class="text-center" style="border-left:2px solid var(--ink-200);">{{ $programStats->sum('submitted_final') }}</td>
                                <td class="text-center">{{ $programStats->sum('pending_final') }}</td>
                                <td class="text-center">{{ $programStats->sum('reviewed_final') }}</td>
                                <td class="text-center">{{ $programStats->sum('review_pending_final') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
.dash-layout{display:block; width:100%;}
.dash-main{min-width:0; width:100%;}
a.stat-card{text-decoration:none; color:inherit; transition:transform .12s, box-shadow .12s;}
a.stat-card:hover{transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.08);}

.dash-main .stat-grid{display:grid !important; grid-template-columns:repeat(5,1fr) !important; gap:14px; width:100%; margin-bottom:22px;}

@media (max-width: 1100px){
    .dash-main .stat-grid{grid-template-columns:repeat(3,1fr) !important;}
}
@media (max-width: 700px){
    .dash-main .stat-grid{grid-template-columns:repeat(2,1fr) !important;}
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function(){
    // Dashboard initialized
});
</script>
@endpush
