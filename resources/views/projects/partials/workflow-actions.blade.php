@php
    $actions = $actions ?? [];
@endphp

@if(count($actions) > 0)
    @foreach($actions as $act)
        @php
            $iconMap = [
                'progress'        => 'fas fa-chart-line',
                'assign'          => 'fas fa-user-tag',
                'accept-proposal' => 'fas fa-check-circle',
                'review'          => 'fas fa-clipboard-check',
                'accepted'        => 'fas fa-check-circle',
                'graded'          => 'fas fa-star',
                'report-card'     => 'fas fa-file-alt',
            ];
            $actIcon = $iconMap[$act['action']] ?? 'fas fa-arrow-right';
            $actLabel = $act['label'] ?? ucfirst($act['action']);

            $priClass = ($act['type'] ?? 'primary') === 'secondary' ? 'btn-secondary' : 'btn-primary';

            // Build tooltip — use friendly description for claim/grading actions
            if ($act['action'] === 'claim') {
                $tooltip = 'Accept/Reject Proposal';
            } elseif ($act['action'] === 'progress-grade') {
                $tooltip = 'Grade the Progress Report';
            } elseif ($act['action'] === 'final-grade') {
                $tooltip = 'Grade the Final Report';
            } else {
                $tooltip = $actLabel;
            }
            // Append step info only for non-claim/grade actions if needed
            if (!empty($act['step']) && !in_array($act['action'], ['claim', 'progress-grade', 'final-grade'])) {
                $tooltip .= ' (' . $act['step'] . ')';
            }

            // If action has a direct URL, render as an <a> tag
            $hasUrl = !empty($act['url']);
        @endphp

        @if($act['action'] === 'progress')
            <a href="{{ route('progress.add', $project->id ?? '') }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}">
                <i class="{{ $actIcon }}"></i> Add Progress
            </a>
        @elseif($hasUrl)
            <a href="{{ $act['url'] }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}">
                <i class="{{ $actIcon }}"></i> {{ $actLabel }}
            </a>
        @elseif(in_array($act['action'], ['accepted', 'graded']))
            <form method="POST" action="{{ route('workflow.record-redirect') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id ?? '' }}">
                <input type="hidden" name="action" value="{{ $act['action'] }}">
                <input type="hidden" name="redirect" value="{{ url()->current() }}">
                <button type="submit" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}">
                    <i class="{{ $actIcon }}"></i> {{ $actLabel }}
                </button>
            </form>
        @elseif($act['action'] === 'report-card' && !auth()->user()->isReviewer())
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'report-card', 'lg')">
                <i class="{{ $actIcon }}"></i> {{ $actLabel }}
            </button>
        @elseif(in_array($act['action'], ['progress-grade', 'final-grade']))
            <a href="{{ route('projects.grading', $project->id ?? '') }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" style="text-decoration:none;">
                @if($act['action'] === 'progress-grade')
                    <i class="fas fa-star"></i>
                @else
                    <i class="fas fa-flag-checkered"></i>
                @endif
                {{ $actLabel }}
            </a>
        @elseif($act['action'] === 'claim')
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'accept-proposal')">
                <i class="fas fa-check-circle"></i> {{ $actLabel }}
            </button>
        @else
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, '{{ $act['action'] }}')">
                <i class="{{ $actIcon }}"></i> {{ $actLabel }}
            </button>
        @endif
    @endforeach
@endif
