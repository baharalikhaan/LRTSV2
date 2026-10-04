<div class="panel announcements-panel">
    <div class="panel-head">
        <h2><i class="fas fa-bullhorn"></i> Announcements</h2>
        <div class="panel-actions">
            <a href="{{ route('announcements.index') }}" class="btn-secondary btn-sm">View All</a>
        </div>
    </div>
    <div class="panel-body announcements-body">
        @if(count($announcements ?? []) > 0)
            @foreach($announcements as $announcement)
            <div class="announcement-item">
                <div class="announcement-title-row">
                    <i class="fas fa-bullhorn" style="color:var(--gold-500); font-size:12px;"></i>
                    <span class="announcement-title">{{ $announcement->title }}</span>
                    <span class="announcement-date">{{ $announcement->created_at ? $announcement->created_at->format('d M Y') : '' }}</span>
                </div>
                @if($announcement->message)
                <p class="announcement-body">{{ \Illuminate\Support\Str::limit($announcement->message, 140) }}</p>
                @endif
            </div>
            @endforeach
        @else
            <div class="empty-state py-4">
                <i class="fas fa-inbox"></i>
                <p class="mb-0">No announcements</p>
            </div>
        @endif
    </div>
</div>
