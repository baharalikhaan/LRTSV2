@extends('layouts.app')

@section('title', 'Email Templates - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-file-lines"></i> Email Templates</h1>
        <p>Manage reusable email templates with placeholder tags.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:space-between;">
            <form method="GET" class="filter-bar" id="filterForm" style="margin-bottom:0;">
                <div class="filter-group">
                    <label>Category:</label>
                    <select name="category" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="general">General</option>
                        <option value="reminder">Reminder</option>
                        <option value="notification">Notification</option>
                        <option value="welcome">Welcome</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search:</label>
                    <input type="text" id="tableSearch" placeholder="Search templates..." class="search-input">
                </div>
            </form>
            <a href="{{ route('email-templates.create') }}" class="btn-primary btn-sm" style="white-space:nowrap;">
                <i class="fas fa-plus"></i> New Template
            </a>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="templatesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Updated</th>
                    <th class="text-center" style="min-width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $t->name }}</div>
                        @if($t->signature)
                            <div style="font-size:11px; color:var(--color-ink-400); margin-top:2px;">
                                <i class="fas fa-signature" style="font-size:10px;"></i> Has signature
                            </div>
                        @endif
                    </td>
                    <td style="max-width:280px;">
                        <span style="font-size:12.5px; color:var(--color-ink-600);">{{ Str::limit($t->subject, 60) }}</span>
                    </td>
                    <td>
                        @php
                        $catPills = ['general'=>'info','reminder'=>'warning','notification'=>'primary','welcome'=>'success'];
                        $pillClass = $catPills[$t->category] ?? 'info';
                        @endphp
                        <span class="pill {{ $pillClass }}">{{ ucfirst($t->category) }}</span>
                    </td>
                    <td style="font-size:12px; color:var(--color-ink-400);">
                        {{ $t->updated_at ? $t->updated_at->diffForHumans() : '—' }}
                    </td>
                    <td class="text-center">
                        <div class="btn-action-group" style="white-space:nowrap;">
                            <button type="button"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                title="Preview"
                                data-bs-toggle="tooltip"
                                onclick="previewTemplate({{ $t->id }})">
                                <i class="fas fa-eye" style="font-size:11px;"></i> Preview
                            </button>
                            <a href="{{ route('email-templates.edit', $t->id) }}"
                                class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;"
                                title="Edit"
                                data-bs-toggle="tooltip">
                                <i class="fas fa-edit" style="font-size:11px;"></i> Edit
                            </a>
                            <form action="{{ route('email-templates.destroy', $t->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn-sm btn-secondary" style="font-size:11px;padding:4px 10px;color:var(--color-danger);"
                                    title="Delete"
                                    data-bs-toggle="tooltip">
                                    <i class="fas fa-trash" style="font-size:11px;"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state py-4">
                            <i class="fas fa-file-lines"></i>
                            <h5>No Templates Found</h5>
                            <p>No email templates have been created yet.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Placeholders Reference --}}
<div class="panel" style="margin-top:20px;">
    <div class="panel-head">
        <h2 style="font-size:14px; font-weight:600; color:var(--color-ink-800); margin:0;">
            <i class="fas fa-code" style="margin-right:6px;"></i> Available Placeholders
        </h2>
    </div>
    <div class="panel-body">
        <p style="font-size:12.5px; color:var(--color-ink-500); margin:0 0 12px;">
            Use these tags in your subject or body. They will be replaced with actual values when the email is sent.
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            @foreach($placeholders as $tag => $desc)
            <div style="display:flex; align-items:center; gap:8px; padding:6px 10px; background:var(--sand-50, #faf7f0); border-radius:4px; border:1px solid var(--ink-100, #eceef2);">
                <code style="font-size:12px; font-weight:600; color:var(--brand-600, #8d1b3d); background:none; padding:0;">{{ $tag }}</code>
                <span style="font-size:11px; color:var(--ink-500);">{{ $desc }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i> Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px; font-weight:600; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:.04em;">Subject</label>
                    <div id="previewSubject" style="font-size:14px; font-weight:600; color:var(--color-ink-800); padding:8px 12px; background:var(--sand-50, #faf7f0); border-radius:4px; margin-top:4px;"></div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px; font-weight:600; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:.04em;">Body</label>
                    <div id="previewBody" style="font-size:13px; color:var(--color-ink-700); padding:12px; background:#fff; border:1px solid var(--ink-100, #eceef2); border-radius:4px; margin-top:4px; line-height:1.7;"></div>
                </div>
                <div id="previewSigWrap" style="display:none;">
                    <label style="font-size:11px; font-weight:600; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:.04em;">Signature</label>
                    <div id="previewSig" style="font-size:12px; color:var(--color-ink-500); padding:8px 12px; border-top:1px solid var(--ink-100, #eceef2); margin-top:8px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    @if($templates->count() > 0)
    var table = $('#templatesTable').DataTable({
        dom: 'rt<"bottom"lip>',
        order: [[2, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [4] }],
        drawCallback: function() {
            $('[data-bs-toggle="tooltip"]').tooltip('dispose').tooltip();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#categoryFilter').on('change', function() {
        var val = $(this).val();
        table.column(2).search(val).draw();
    });
    @endif

    $('[data-bs-toggle="tooltip"]').tooltip();
});

function previewTemplate(id) {
    fetch('/email-templates/' + id + '/preview')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            document.getElementById('previewSubject').textContent = d.subject;
            document.getElementById('previewBody').innerHTML = d.body.replace(/\n/g, '<br>');
            if (d.signature) {
                document.getElementById('previewSigWrap').style.display = 'block';
                document.getElementById('previewSig').innerHTML = d.signature.replace(/\n/g, '<br>');
            } else {
                document.getElementById('previewSigWrap').style.display = 'none';
            }
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });
}
</script>
@endpush
