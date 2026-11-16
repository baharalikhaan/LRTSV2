@php
    // Shows which research call the current list is filtered by, how many
    // projects the call holds, and how many the page is actually listing.
    // Pass ['contextKey' => 'reviewer-assignment', 'listedCount' => $n] when
    // including this partial (listedCount is optional).
    $contextKey = $contextKey ?? null;
    $listedCount = $listedCount ?? null;
    $programId = $programId ?? request('program_id');
    $contextRoute = $contextKey
        ? (\App\Http\Controllers\ProjectController::RESEARCH_CALL_CONTEXTS[$contextKey]['route'] ?? null)
        : null;
    $selectedProgram = ($contextRoute && $programId)
        ? \App\Models\Program::with('grant', 'cycle')->find($programId)
        : null;
    $totalProjects = $selectedProgram ? $selectedProgram->projects()->count() : 0;
@endphp

@if($selectedProgram)
<div class="alert" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:var(--sand-50,#faf7f0);border:1px solid var(--ink-100,#eceef2);border-radius:8px;font-size:13px;color:var(--ink-600,#4b4654);">
    <i class="fas fa-filter" style="color:var(--brand-500,#8d1b3d);"></i>
    <span>Research call:</span>
    <strong style="color:var(--ink-800,#2f2b35);">{{ $selectedProgram->program_title }}</strong>
    @if($selectedProgram->grant)
        <span class="pill info" style="font-size:11px;">{{ $selectedProgram->grant->grant_code }}</span>
    @endif
    @if($selectedProgram->cycle)
        <span style="font-size:12px;color:var(--ink-500,#5d6677);">{{ $selectedProgram->cycle->title }}</span>
    @endif

    {{-- How many projects the research call holds, and how many are listed. --}}
    <span class="pill review" style="font-size:11px;white-space:nowrap;">
        @if($listedCount === null)
            {{ $totalProjects }} project{{ $totalProjects === 1 ? '' : 's' }} in this research call
        @else
            Showing {{ $listedCount }} of {{ $totalProjects }} project{{ $totalProjects === 1 ? '' : 's' }}
        @endif
    </span>

    <span style="flex:1;"></span>
    <a href="{{ route('projects.research-calls', $contextKey) }}" class="btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Change research call
    </a>
    <a href="{{ route($contextRoute) }}" class="btn-secondary btn-sm" title="Remove the research-call filter">
        <i class="fas fa-layer-group"></i> Show all
    </a>
</div>
@endif
