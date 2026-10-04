@extends('layouts.app')

@section('title', 'Upload Reports - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-cloud-arrow-up"></i> Upload Reports</h1>
        <p>Upload progress, final, readiness, and ethical approval reports on behalf of LPIs.</p>
    </div>
</div>

@if(session('success'))
<div class="fluent-alert fluent-alert--success" style="margin-bottom:16px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <div class="panel-actions upload-toolbar" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div class="filter-group">
                <label style="font-size:11px;font-weight:600;">Cycle:</label>
                <select id="cycleFilter" style="min-width:180px;font-size:12px;padding:4px 8px;border:1px solid var(--ink-200);border-radius:6px;background:#fff;">
                    <option value="">All Cycles</option>
                    @foreach($cycleConfigs as $cycle)
                        <option value="{{ $cycle->id }}">{{ $cycle->title }} ({{ $cycle->year }})</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group search-group">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="tableSearch" placeholder="Search by project ID, title, LPI..." class="search-input">
            </div>

            <div class="filter-group missing-filters">
                <span class="filter-label">Missing reports:</span>
                <button type="button" class="chip" data-missing="proposal">Proposal</button>
                <button type="button" class="chip" data-missing="progress">Progress</button>
                <button type="button" class="chip" data-missing="final">Final</button>
                <button type="button" class="chip" data-missing="readiness">Readiness</button>
                <button type="button" class="chip" data-missing="ethical">Ethical</button>
            </div>

            <div class="upload-legend">
                <span class="pill success" style="font-size:9px;padding:2px 6px;"><i class="fas fa-check" style="font-size:8px;"></i> Uploaded</span>
                <span class="pill inactive" style="font-size:9px;padding:2px 6px;"><i class="fas fa-minus" style="font-size:8px;"></i> None</span>
            </div>
        </div>
    </div>
    <div class="panel-body p-0">
        <table class="fluent-table w-100" id="uploadTable" style="font-size:12.5px;">
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
.fluent-alert{padding:10px 14px;border-radius:6px;font-size:12.5px;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.fluent-alert--success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;}
.fluent-alert--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;}
#uploadTable td,#uploadTable th{padding:5px 6px !important;vertical-align:middle;font-size:12px;}
td.nowrap,th.nowrap{white-space:nowrap;}

.upload-toolbar{align-items:center;}
.filter-label{font-size:12px;font-weight:600;color:var(--ink-500,#6f6a78);white-space:nowrap;}
.search-group{position:relative;}
.search-icon{color:var(--ink-400,#8b8592);font-size:12px;margin-right:-2px;}
.search-group .search-input{width:220px;}
.chip{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border:1px solid var(--ink-200,#eceef2);border-radius:999px;background:#fff;font-size:12px;color:var(--ink-600,#575263);cursor:pointer;transition:all .12s;white-space:nowrap;}
.chip:hover{border-color:var(--brand-300,#e8a4b8);color:var(--brand-600,#8d1b3d);}
.chip.active{background:var(--brand-50,#fbeef1);border-color:var(--brand-400,#d14d77);color:var(--brand-600,#8d1b3d);font-weight:600;}
.upload-legend{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--ink-400,#8b8592);margin-left:auto;flex-shrink:0;}


.admin-upload-label{cursor:pointer;margin:0;}
.admin-upload-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);}
.admin-upload-btn{display:inline-flex;align-items:center;gap:3px;white-space:nowrap;transition:all .15s;}
.admin-upload-label:hover .admin-upload-btn{background:var(--brand-50,#fbeef1);border-color:var(--brand-200,#e8a4b8);}
.admin-upload-label.has-file .admin-upload-btn{background:#d1fae5;border-color:#86efac;color:#166534;}
.admin-upload-submit:disabled{opacity:.5;cursor:not-allowed;}

@keyframes uploadPulse{
    0%{opacity:1;}
    50%{opacity:.5;}
    100%{opacity:1;}
}
.admin-upload-form.uploading .admin-upload-btn{animation:uploadPulse 1s infinite;}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#uploadTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin-upload.ajax-list") }}',
            data: function(d) {
                d.missing = $('.chip.active').map(function() { return $(this).data('missing'); }).get();
                d.cycle_id = $('#cycleFilter').val();
            }
        },
        dom: 'rt<"bottom"lip>',
        columns: [
            { data: 'old_project_id', name: 'old_project_id', title: 'ID', width: '100px', className: 'nowrap' },
            { data: 'title', name: 'title', title: 'Title', orderable: false, searchable: true },
            { data: 'lpi', name: 'lpi', title: 'LPI', orderable: false, searchable: false, width: '120px', className: 'nowrap' },

            { data: 'proposal', name: 'proposal', title: 'Proposal', orderable: false, searchable: false, className: 'text-center', width: '70px' },
            { data: 'progress', name: 'progress', title: 'PR1', orderable: false, searchable: false, className: 'text-center', width: '55px' },
            { data: 'progress2', name: 'progress2', title: 'PR2', orderable: false, searchable: false, className: 'text-center', width: '55px' },
            { data: 'final', name: 'final', title: 'Final', orderable: false, searchable: false, className: 'text-center', width: '55px' },
            { data: 'readiness', name: 'readiness', title: 'Readiness', orderable: false, searchable: false, className: 'text-center', width: '70px' },
            { data: 'ethical', name: 'ethical', title: 'Ethical', orderable: false, searchable: false, className: 'text-center', width: '70px' },
            { data: 'action', name: 'action', title: 'Upload', orderable: false, searchable: false, className: 'text-center', width: '180px' },
        ],
        order: [[0, 'desc']],
        pageLength: 15,
        drawCallback: function() {
            initUploadForms();
        }
    });

    $('#tableSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#cycleFilter').on('change', function() {
        table.draw();
    });

    $(document).on('click', '.chip', function() {
        $(this).toggleClass('active');
        table.draw();
    });
});

function initUploadForms() {
    // File input change — show filename
    $('.admin-upload-input').off('change').on('change', function() {
        var label = $(this).closest('.admin-upload-label');
        if (this.files && this.files.length > 0) {
            label.addClass('has-file');
            var btn = label.find('.admin-upload-btn');
            var name = this.files[0].name;
            if (name.length > 12) name = name.substring(0, 10) + '...';
            btn.html('<i class="fas fa-file-pdf" style="font-size:10px;"></i> ' + name);
        } else {
            label.removeClass('has-file');
            var type = $(this).data('type');
            var labels = {proposal: 'Proposal', progress: 'Progress', progress2: 'Progress 2', final: 'Final', readiness: 'Readiness', ethical: 'Ethical Approval'};
            $(this).closest('.admin-upload-label').find('.admin-upload-btn').html('<i class="fas fa-file-pdf" style="font-size:10px;"></i> ' + labels[type]);
        }
    });

    // Form submit — AJAX upload
    $('.admin-upload-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var projectId = form.data('project-id');
        var hasFile = false;

        // Check if any file is selected
        form.find('.admin-upload-input').each(function() {
            if (this.files && this.files.length > 0) {
                hasFile = true;
            }
        });

        if (!hasFile) {
            return;
        }

        var submitBtn = form.find('.admin-upload-submit');
        var formData = new FormData(form[0]);

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="font-size:10px;"></i>');
        form.addClass('uploading');

        $.ajax({
            url: '{{ route("admin-upload.upload") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                showToast('success', resp.message || 'Uploaded!');
                // Reset form
                form.find('.admin-upload-input').val('');
                form.find('.admin-upload-label').removeClass('has-file');
                form.find('.admin-upload-input').each(function() {
                    var type = $(this).data('type');
                    var labels = {proposal: 'Proposal', progress: 'Progress', progress2: 'Progress 2', final: 'Final', readiness: 'Readiness', ethical: 'Ethical Approval'};
                    $(this).closest('.admin-upload-label').find('.admin-upload-btn').html('<i class="fas fa-file-pdf" style="font-size:10px;"></i> ' + labels[type]);
                });
                submitBtn.prop('disabled', false).html('<i class="fas fa-upload" style="font-size:10px;"></i>');
                form.removeClass('uploading');
                // Reload table to update status badges
                $('#uploadTable').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                var msg = 'Upload failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showToast('error', msg);
                submitBtn.prop('disabled', false).html('<i class="fas fa-upload" style="font-size:10px;"></i>');
                form.removeClass('uploading');
            }
        });
    });
}
</script>
@endpush
