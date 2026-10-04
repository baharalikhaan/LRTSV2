
@extends('layouts.app')

@section('title', $grant->grant_code . ' - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-trophy"></i> {{ $grant->grant_code }}: {{ $grant->grant_name }}</h1>
        <p>Grant type details and its research calls.</p>
    </div>
    <div class="page-actions">
        @if(Auth::user()->isAdmin())
        <a href="{{ route('grant-types.edit', $grant->id) }}" class="btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
        @endif
        <a href="{{ route('grant-types.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> All Grant Types
        </a>
    </div>
</div>

<div style="display:flex; gap:22px; align-items:flex-start;">

    <div class="panel" style="flex:1; min-width:0;">
        <div class="panel-head">
            <h2><i class="fas fa-info-circle"></i> Grant Type Details</h2>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table w-100">
                <tbody>
                    <tr>
                        <td style="width:180px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Code</td>
                        <td style="font-weight:600;"><code>{{ $grant->grant_code }}</code></td>
                    </tr>
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Name</td>
                        <td style="font-weight:500;">{{ $grant->grant_name }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Category</td>
                        <td><span class="pill {{ $grant->category == 'student' ? 'info' : 'primary' }}">{{ ucfirst($grant->category) }}</span></td>
                    </tr>
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Funding Agency</td>
                        <td>{{ $grant->funding_agency ?? 'â€”' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Max Duration</td>
                        <td>{{ $grant->max_duration_years ? $grant->max_duration_years . ' year(s)' : 'â€”' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Status</td>
                        <td>
                            @if($grant->is_active)
                                <span class="pill success"><i class="fas fa-check-circle" style="font-size:10px;"></i> Active</span>
                            @else
                                <span class="pill inactive"><i class="fas fa-minus-circle" style="font-size:10px;"></i> Inactive</span>
                            @endif
                        </td>
                    </tr>
                    @if($grant->description)
                    <tr>
                        <td style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink-400);">Description</td>
                        <td style="color:var(--ink-600);">{{ $grant->description }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel" style="width:360px; flex-shrink:0;">
        <div class="panel-head">
            <h2><i class="fas fa-sync-alt"></i> Research Calls</h2>
            <div class="panel-actions">
                <span style="font-size:12px; color:var(--color-ink-400);">{{ $grant->programs->count() }} total</span>
            </div>
        </div>
        <div class="panel-body p-0">
            <table class="fluent-table w-100">
                <tbody>
                    @forelse($grant->programs as $cycle)
                    <tr>
                        <td>
                            <a href="{{ route('programs.show', $cycle->id) }}" style="font-weight:500; color:var(--color-brand-500); text-decoration:none;">
                                {{ $cycle->program_title }}
                            </a>
                        </td>
                        <td class="text-right" style="white-space:nowrap;">
                            <span class="pill {{ $cycle->isActive() ? 'success' : 'inactive' }}" style="font-size:10px;">
                                {{ $cycle->isActive() ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2">
                            <div class="empty-state py-4">
                                <i class="fas fa-sync-alt"></i>
                                <h5>No Research Calls</h5>
                                <p>No research calls use this grant type yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
