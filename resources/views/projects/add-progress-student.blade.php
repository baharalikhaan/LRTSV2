@extends('layouts.app')

@section('title', 'Student Progress — RTS')

@php
    $isLocked = $isLocked ?? false;
@endphp

@section('content')
<div class="page-head">
    <div>
        <h1 style="font-size:17px;"><i class="fas fa-user-graduate"></i> Student Project — Progress Update</h1>
        <p style="font-size:12.5px;"><code style="font-size:11.5px;">{{ $project->old_project_id }}</code> · {{ Str::limit($project->title, 70) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.show', $project->id) }}" class="btn-secondary btn-sm">
            <i class="fas fa-arrow-left" style="font-size:11px;"></i> Back to Project
        </a>
    </div>
</div>

@if($isLocked)
<div style="display:flex;gap:8px;align-items:center;background:var(--ink-50,#f5f4f2);border:1px solid var(--ink-100,#eceef2);border-radius:10px;padding:9px 14px;margin-bottom:12px;font-size:13px;">
    <i class="fas fa-lock" style="color:var(--brand-500,#6c4cf1);font-size:13px;"></i>
    <div><strong>Form submitted — locked.</strong>&nbsp;It can no longer be edited. Contact the administrator if a correction is needed.</div>
</div>
@endif

<div class="panel">
    <div class="panel-head">
        <h2 style="font-size:14px;"><i class="fas fa-clipboard-list"></i> Progress Details</h2>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <span class="pill primary" style="font-size:10.5px;padding:3px 9px;">{{ $project->program?->grant?->grant_code ?? '—' }}</span>
            @if($isLocked)
                <span class="pill inactive" style="font-size:10.5px;padding:3px 9px;"><i class="fas fa-lock" style="font-size:9px;"></i> Locked</span>
            @else
                <span class="pill info" style="font-size:10.5px;padding:3px 9px;">Editable</span>
            @endif
        </div>
    </div>
    <div class="panel-body" style="padding:16px 18px;">
        <div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px 20px;">
            {{-- ─────────── LEFT COLUMN ─────────── --}}
            <div>

                {{-- Students + nationality --}}
                <div style="font-size:10.5px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px;">
                    <i class="fas fa-user-graduate" style="color:var(--brand-500);margin-right:4px;"></i> Students &amp; Nationality
                </div>
                @forelse($projectStudents as $s)
                @php
                    $nat = $s->nationality;
                    $natTitle = $nat && str_starts_with(strtolower($nat), 'non') ? 'Non-Qatari' : ($nat ? 'Qatari' : null);
                    $levelMap = ['UG' => 'Undergraduate', 'masters' => 'Master', 'PhD' => 'PhD'];
                    $level = $s->details ? ($s->details->std_level ?? $s->details->std_program ?? null)
                        : ($s->type ? ($levelMap[$s->type] ?? $s->type) : null);
                @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:6px 10px;border:1px solid var(--ink-100,#eceef2);border-radius:8px;margin-bottom:6px;font-size:12.5px;">
                    <code style="font-size:11px;">{{ $s->std_id ?: $s->student_id ?: '—' }}</code>
                    <span style="font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $s->student_name ?: (($s->details->first_name ?? '') . ' ' . ($s->details->last_name ?? '')) }}</span>
                    @if($level)<span class="pill inactive" style="font-size:9.5px;padding:1px 6px;">{{ $level }}</span>@endif
                    <span class="student-nat-cell" data-student-row="{{ $s->id }}" style="display:inline-flex;gap:8px;align-items:center;white-space:nowrap;">
                        <label style="display:inline-flex;align-items:center;gap:3px;font-size:12px;cursor:pointer;">
                            <input type="radio" name="students[{{ $s->id }}][category]" class="student-nationality"
                                   value="Qatari" {{ $natTitle === 'Qatari' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}
                                   style="width:auto;"> Qatari
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:3px;font-size:12px;cursor:pointer;">
                            <input type="radio" name="students[{{ $s->id }}][category]" class="student-nationality"
                                   value="Non-Qatari" {{ $natTitle === 'Non-Qatari' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}
                                   style="width:auto;"> Non-Qatari
                        </label>
                    </span>
                </div>
                @empty
                <div style="color:var(--color-ink-400);font-size:12.5px;padding:8px 0;">No students linked yet — linked from registration / Conf-Tool sheet.</div>
                @endforelse

                {{-- Budget / Spending --}}
                <div style="font-size:10.5px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin:14px 0 7px;">
                    <i class="fas fa-coins" style="color:var(--brand-500);margin-right:4px;"></i> Budget / Spending
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label" style="font-size:11.5px;">Allocated (QAR)</label>
                        <input type="text" class="form-control" readonly value="{{ number_format((float) ($project->requested_budget_qar ?? 0), 2) }}"
                               style="background:var(--sand-50,#faf8f5);cursor:not-allowed;font-size:12.5px;padding:6px 10px;">
                    </div>
                    <div>
                        <label for="spendingInput" class="form-label" style="font-size:11.5px;">Spent (QAR) {{ $isLocked ? '' : '*' }}</label>
                        <input type="number" step="0.01" min="0" id="spendingInput" name="spending" class="form-control"
                               value="{{ old('spending', $project->spending) }}" {{ $isLocked ? 'readonly' : '' }}
                               placeholder="e.g. 17500" style="font-size:12.5px;padding:6px 10px;">
                    </div>
                </div>
                <div id="spendingMeterBar" style="height:6px;border-radius:5px;background:var(--ink-100,#eceef2);overflow:hidden;margin-top:8px;">
                    <div id="spendingMeterFill" style="height:100%;width:0%;background:#16a34a;transition:width .2s;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:3px;">
                    <span id="spendingMeterLabel" style="font-size:11px;font-weight:600;color:var(--ink-500);">&nbsp;</span>
                </div>
                <textarea name="spending_details" id="spendingDetails" rows="2" class="form-control" placeholder="Spending details / remarks (optional)"
                          style="font-size:12.5px;padding:7px 10px;margin-top:7px;" {{ $isLocked ? 'readonly' : '' }}>{{ old('spending_details', $project->spending_detail) }}</textarea>

                {{-- Publications --}}
                <div style="font-size:10.5px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin:14px 0 7px;">
                    <i class="fas fa-file-invoice" style="color:var(--brand-500);margin-right:4px;"></i> Publications
                </div>
                <textarea id="publicationsInput" name="publications" rows="3" class="form-control" placeholder="One output per line — e.g. AlRababaa et al., Walkability and livability, Journal of Transport Geography, 2025"
                          style="font-size:12.5px;padding:7px 10px;" {{ $isLocked ? 'readonly' : '' }}>{{ old('publications', $project->publications) }}</textarea>
            </div>

            {{-- ─────────── RIGHT COLUMN ─────────── --}}
            <div>

                {{-- Engagement --}}
                <div style="font-size:10.5px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px;">
                    <i class="fas fa-pen-nib" style="color:var(--brand-500);margin-right:4px;"></i> Student Engagement {{ $isLocked ? '' : '*' }}
                </div>
                <textarea id="engagementInput" name="student_engagement" rows="7" class="form-control" placeholder="Describe how the student was engaged: their role, tasks, supervision..."
                          style="font-size:12.5px;padding:8px 10px;" {{ $isLocked ? 'readonly' : '' }}>{{ old('student_engagement', $project->student_engagement) }}</textarea>

                {{-- Ethical approvals --}}
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:10.5px;font-weight:700;color:var(--ink-500);text-transform:uppercase;letter-spacing:.05em;margin:14px 0 7px;">
                    <span><i class="fas fa-file-shield" style="color:var(--brand-500);margin-right:4px;"></i> Ethical Approval(s)</span>
                    @if(!$isLocked)
                    <span style="display:inline-flex;gap:6px;align-items:center;text-transform:none;letter-spacing:0;">
                        <input type="file" id="ethicalFileInput" accept="application/pdf" style="font-size:11px;max-width:190px;">
                        <button type="button" id="ethicalUploadBtn" class="btn-primary btn-sm" style="font-size:11px;padding:3px 9px;"><i class="fas fa-cloud-arrow-up" style="font-size:10px;"></i> Upload</button>
                    </span>
                    @endif
                </div>
                <div id="ethicalDocsList" style="display:flex;flex-direction:column;gap:5px;">
                    @forelse($ethicalDocs as $doc)
                    <div class="ethical-doc-row" data-submission-id="{{ $doc->id }}" style="display:flex;align-items:center;gap:8px;border:1px solid var(--ink-100,#eceef2);border-radius:8px;padding:5px 10px;font-size:12px;">
                        <i class="far fa-file-pdf" style="color:var(--color-danger,#dc3545);"></i>
                        <a href="{{ route('serveFile2', ['type' => 'ethical', 'id' => $project->id, 'submission_id' => $doc->id]) }}"
                           target="_blank" style="color:var(--brand-500);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $doc->stored_filename }}</a>
                        <span style="color:var(--color-ink-400);font-size:10.5px;white-space:nowrap;">{{ $doc->created_at?->format('d M') ?? '' }}</span>
                        @if(!$isLocked)
                        <button type="button" class="ethical-delete" data-id="{{ $doc->id }}" style="background:none;border:none;cursor:pointer;color:var(--color-danger,#dc3545);font-size:11px;"><i class="fas fa-trash"></i></button>
                        @endif
                    </div>
                    @empty
                    <div style="color:var(--color-ink-400);font-size:12.5px;padding:4px 0;">No ethical approval document uploaded.</div>
                    @endforelse
                </div>

                {{-- Deadline hint --}}
                @if(!empty($deadlines['final_rpt_deadline']))
                <div style="font-size:11px;color:var(--color-ink-400);margin-top:12px;">
                    <i class="far fa-clock" style="margin-right:3px;"></i> Final report deadline: <strong>{{ \Carbon\Carbon::parse($deadlines['final_rpt_deadline'])->format('d M Y') }}</strong>
                </div>
                @endif
            </div>
        </div>

        {{-- ─────────── ACTION BAR (inline, one row) ─────────── --}}
        @if(!$isLocked)
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;border-top:1px solid var(--ink-100,#eceef2);margin-top:16px;padding-top:12px;">
            <button type="button" id="saveDraftBtn" class="btn-secondary btn-sm"><i class="fas fa-download" style="font-size:11px;"></i> Save as Draft</button>
            <button type="button" id="saveSubmitBtn" class="btn-primary btn-sm"><i class="fas fa-paper-plane" style="font-size:11px;"></i> Save &amp; Submit (locks form)</button>
            <button type="button" id="restorePointBtn" class="btn-secondary btn-sm" title="Roll the form back to the last-saved (DB) state" style="margin-left:auto;opacity:.85;">
                <i class="fas fa-clock-rotate-left" style="font-size:11px;"></i> Restore
            </button>
            <span id="restoreFlash" style="font-size:11px;color:var(--color-ink-400);"></span>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var ACTION = '{{ route('progress.save-student', $project->id) }}';
    var UPLOAD_URL = '{{ route('progress.upload-submission', $project->id) }}';
    var DELETE_URL = '{{ route('progress.delete-submission', $project->id) }}';

    // ────────────────────────────────────────────────────────────────────
    // RESTORE POINT — snapshot every editable field at load; "Restore"
    // reverts the LIVE form to the stored server values.
    // ────────────────────────────────────────────────────────────────────
    var restorePoint = [];

    function snapshot() {
        restorePoint = [];
        $('#spendingInput, #spendingDetails, #publicationsInput, #engagementInput').each(function() {
            restorePoint.push({el: this, value: $(this).val()});
        });
        $('.student-nationality').each(function() {
            restorePoint.push({radio: this, value: $(this).prop('checked')});
        });
    }
    snapshot();

    $('#restorePointBtn').on('click', function() {
        restorePoint.forEach(function(item) {
            if (item.el || $(item.el).length) {
                $(item.el).val(item.value);
            } else {
                $(item.radio).prop('checked', item.value);
            }
        });
        updateMeter();
        $('#restoreFlash').text('Form restored to last-saved values at ' + new Date().toLocaleTimeString());
        setTimeout(function() { $('#restoreFlash').text(''); }, 3500);
    });

    // ────────────────────────────────────────────────────────────────────
    // Spending meter
    // ────────────────────────────────────────────────────────────────────
    var BUDGET = {{ (float) ($project->requested_budget_qar ?? 0) }};

    function updateMeter() {
        var spending = parseFloat($('#spendingInput').val() || 0);
        var pct = BUDGET > 0 ? (spending / BUDGET) * 100 : (spending > 0 ? 101 : 0);
        var clamped = Math.max(0, Math.min(100, pct));
        var color = '#16a34a';
        if (pct > 100) color = '#dc3545';
        else if (pct > 90) color = '#f59e0b';
        $('#spendingMeterFill').css({width: clamped + '%', background: color});
        var label = BUDGET > 0
            ? pct.toFixed(1) + '% of the allocated budget used' + (pct > 100 ? ' — over budget!' : '')
            : (spending > 0 ? 'No budget figure on record' : '');
        $('#spendingMeterLabel').text(label);
    }
    $('#spendingInput').on('input', updateMeter);
    updateMeter();

    // ────────────────────────────────────────────────────────────────────
    // Save (draft / submit)
    // ────────────────────────────────────────────────────────────────────
    function collectStudents() {
        var out = [];
        $('.student-nat-cell').each(function() {
            out.push({ id: $(this).data('student-row'), category: $(this).find('.student-nationality:checked').val() || '' });
        });
        return out;
    }

    function saveForm(action) {
        var btn = action === 'submit' ? $('#saveSubmitBtn') : $('#saveDraftBtn');
        var original = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        $.ajax({
            url: ACTION, method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: action,
                students: collectStudents(),
                publications: $('#publicationsInput').val(),
                spending: $('#spendingInput').val(),
                spending_details: $('#spendingDetails').val(),
                student_engagement: $('#engagementInput').val()
            },
            success: function(resp) {
                if (resp && resp.success) {
                    showToast(resp.locked ? 'success' : 'info', resp.message);
                    if (resp.locked) { setTimeout(function() { location.reload(); }, 900); }
                    else { snapshot(); btn.prop('disabled', false).html(original); }
                } else {
                    showToast('error', (resp && resp.error) || 'Could not save.');
                    btn.prop('disabled', false).html(original);
                }
            },
            error: function(xhr) {
                var msg = 'Server error. Please try again.';
                if (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
                    msg = xhr.responseJSON.error || xhr.responseJSON.message;
                } else if (xhr.status === 422) {
                    try { msg = Object.values(xhr.responseJSON.errors)[0][0]; } catch (e) {}
                }
                showToast('error', msg);
                btn.prop('disabled', false).html(original);
            }
        });
    }
    $('#saveDraftBtn').on('click', function() { saveForm('draft'); });
    $('#saveSubmitBtn').on('click', function() {
        if (!confirm('Submitting permanently locks this form. Continue?')) return;
        saveForm('submit');
    });

    // ────────────────────────────────────────────────────────────────────
    // Ethical approval upload / delete
    // ────────────────────────────────────────────────────────────────────
    $('#ethicalUploadBtn').on('click', function() {
        var fileInput = document.getElementById('ethicalFileInput');
        if (!fileInput.files || !fileInput.files.length) {
            showToast('warning', 'Choose a PDF first.');
            return;
        }
        var fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('type', 'ethical');
        fd.append('file', fileInput.files[0]);
        $('#ethicalUploadBtn').prop('disabled', true);
        $.ajax({
            url: UPLOAD_URL, method: 'POST', data: fd, processData: false, contentType: false,
            success: function(resp) {
                $('#ethicalUploadBtn').prop('disabled', false);
                fileInput.value = '';
                var holder = $('#ethicalDocsList');
                var emptyMsg = holder.find('div:only-child').filter(function() { return $(this).find('.ethical-doc-row').length === 0; });
                if (holder.children().length && !holder.find('.ethical-doc-row').length) { holder.empty(); }
                holder.append(
                    '<div class="ethical-doc-row" data-submission-id="' + resp.submission.id + '" style="display:flex;align-items:center;gap:8px;border:1px solid var(--ink-100,#eceef2);border-radius:8px;padding:5px 10px;font-size:12px;">' +
                    '<i class="far fa-file-pdf" style="color:var(--color-danger,#dc3545);"></i>' +
                    '<a href="' + resp.submission.download_url + '" target="_blank" style="color:var(--brand-500);flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + $('<div>').text(resp.submission.stored_filename).html() + '</a>' +
                    '<span style="color:var(--color-ink-400);font-size:10.5px;">just now</span>' +
                    '<button type="button" class="ethical-delete" data-id="' + resp.submission.id + '" style="background:none;border:none;cursor:pointer;color:var(--color-danger,#dc3545);font-size:11px;"><i class="fas fa-trash"></i></button>' +
                    '</div>'
                );
                showToast('success', resp.message || 'Ethical approval uploaded.');
            },
            error: function(xhr) {
                $('#ethicalUploadBtn').prop('disabled', false);
                var msg = 'Upload failed.';
                try { msg = (xhr.responseJSON.error || xhr.responseJSON.message); } catch (e) {}
                showToast('error', msg);
            }
        });
    });
    $(document).on('click', '.ethical-delete', function() {
        var row = $(this).closest('.ethical-doc-row');
        var id = $(this).data('id');
        if (!confirm('Delete this ethical approval document?')) return;
        $.ajax({
            url: DELETE_URL, method: 'POST',
            data: { _token: '{{ csrf_token() }}', submission_id: id },
            success: function(resp) {
                if (resp.success) {
                    row.remove();
                    showToast('success', resp.message || 'Deleted.');
                } else {
                    showToast('error', resp.error || 'Could not delete.');
                }
            },
            error: function(xhr) {
                var msg = 'Delete failed.';
                try { msg = (xhr.responseJSON.error || xhr.responseJSON.message); } catch (e) {}
                showToast('error', msg);
            }
        });
    });
});
</script>
@endpush
