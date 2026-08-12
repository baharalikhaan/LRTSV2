@php
    $actions = $actions ?? [];
@endphp

@if(count($actions) > 0)
    @foreach($actions as $act)
        @php
        $iconMap = [
            'progress'                  => 'fas fa-chart-line',
            'progress-v2'               => 'fas fa-sync-alt',
            'progress-ext-grade'        => 'fas fa-clipboard-check',
            'assign'                    => 'fas fa-user-tag',
            'unassign-reviewer'         => 'fas fa-user-minus',
            'accept-proposal'           => 'fas fa-check-circle',
            'review'                    => 'fas fa-clipboard-check',
            'accepted'                  => 'fas fa-check-circle',
            'graded'                    => 'fas fa-star',
            'report-card'               => 'fas fa-file-alt',
            'final-report'              => 'fas fa-paper-plane',
            'enable-extended-progress'  => 'fas fa-clock',
            'disable-extended-progress' => 'fas fa-ban',
            'request-extended-progress' => 'fas fa-clock',
            'approve-extended-progress' => 'fas fa-check-circle',
            'review-rejection'          => 'fas fa-balance-scale',
            'review-ext-rejection'      => 'fas fa-balance-scale',
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

        @if(in_array($act['action'], ['progress', 'progress-v2']))
            <a href="{{ route('progress.add', $project->id ?? '') }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}">
                <i class="{{ $actIcon }}"></i> {{ $actLabel }}
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
        @elseif(in_array($act['action'], ['progress-grade', 'progress-ext-grade', 'final-grade']))
            <a href="{{ route('projects.grading', $project->id ?? '') }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" style="text-decoration:none;">
                @if($act['action'] === 'progress-grade')
                    <i class="fas fa-star"></i>
                @else
                    <i class="fas fa-flag-checkered"></i>
                @endif
                {{ $actLabel }}
            </a>
        @elseif($act['action'] === 'final-report')
            <a href="{{ route('progress.final', $project->id ?? '') }}" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" style="text-decoration:none;">
                <i class="fas fa-paper-plane"></i> {{ $actLabel }}
            </a>
        @elseif($act['action'] === 'claim')
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'accept-proposal')">
                <i class="fas fa-check-circle"></i> {{ $actLabel }}
            </button>
        @elseif($act['action'] === 'approve-extended-progress')
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'approve-extended-progress')">
                <i class="fas fa-check-circle"></i> {{ $actLabel }}
            </button>
        @elseif($act['action'] === 'unassign-reviewer')
            <button type="button" class="btn-danger btn-sm" title="{{ $tooltip }}" onclick="confirmUnassignReviewer({{ $project->id ?? '' }})">
                <i class="fas fa-user-minus"></i> {{ $actLabel }}
            </button>
        @elseif($act['action'] === 'review-rejection')
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'review-rejection', 'lg', 'report_type=progress')">
                <i class="fas fa-balance-scale"></i> {{ $actLabel }}
            </button>
        @elseif($act['action'] === 'review-ext-rejection')
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, 'review-ext-rejection', 'lg', 'report_type=extended_progress')">
                <i class="fas fa-balance-scale"></i> {{ $actLabel }}
            </button>
        @elseif(in_array($act['action'], ['enable-extended-progress', 'disable-extended-progress']))
            <form method="POST" action="{{ route('workflow.toggle-extended', $project->id ?? '') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="enable" value="{{ $act['action'] === 'enable-extended-progress' ? '1' : '0' }}">
                <button type="submit" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}">
                    <i class="{{ $actIcon }}"></i> {{ $actLabel }}
                </button>
            </form>
        @else
            <button type="button" class="{{ $priClass }} btn-sm" title="{{ $tooltip }}" onclick="openWorkflowModal({{ $project->id ?? '' }}, '{{ $act['action'] }}')">
                <i class="{{ $actIcon }}"></i> {{ $actLabel }}
            </button>
        @endif
    @endforeach
@endif
