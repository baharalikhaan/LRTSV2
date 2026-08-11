@extends('layouts.app')

@section('title', 'Edit Email Template - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-file-lines"></i> Edit Email Template</h1>
        <p>{{ $template->name }}</p>
    </div>
</div>

@if($errors->any())
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('email-templates.update', $template->id) }}">
    @csrf @method('PUT')
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; align-items:start;">

        <div>
            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-tag" style="margin-right:6px;"></i> Template Name</h2></div>
                <div class="panel-body">
                    <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="form-control" placeholder="e.g. Progress Report Reminder">
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-heading" style="margin-right:6px;"></i> Subject</h2></div>
                <div class="panel-body">
                    <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="form-control" placeholder="e.g. Progress Report Reminder">
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-align-left" style="margin-right:6px;"></i> Body</h2></div>
                <div class="panel-body">
                    <textarea name="body" required rows="14" class="form-control" placeholder="Dear *name*,...">{{ old('body', $template->body) }}</textarea>
                    <div style="font-size:11px; color:var(--color-ink-400); margin-top:4px;">Click a placeholder tag on the right to insert it at cursor position.</div>
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-signature" style="margin-right:6px;"></i> Signature <span style="font-weight:400; font-size:11px; color:var(--color-ink-400);">(optional)</span></h2></div>
                <div class="panel-body">
                    <textarea name="signature" rows="3" class="form-control" placeholder="Best regards,...">{{ old('signature', $template->signature) }}</textarea>
                </div>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <a href="{{ route('email-templates.index') }}" class="btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>

        <div>
            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-tag" style="margin-right:6px;"></i> Category</h2></div>
                <div class="panel-body">
                    <select name="category" class="form-control">
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $template->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-code" style="margin-right:6px;"></i> Placeholders</h2></div>
                <div class="panel-body">
                    <p style="font-size:11.5px; color:var(--color-ink-500); margin:0 0 10px;">Click to insert into the body field:</p>
                    @foreach($placeholders as $tag => $desc)
                    <button type="button" class="placeholder-btn" onclick="insertTag('{{ $tag }}')">
                        <code>{{ $tag }}</code>
                        <span>{{ $desc }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-eye" style="margin-right:6px;"></i> Preview</h2></div>
                <div class="panel-body">
                    <button type="button" class="btn-secondary btn-sm" style="width:100%; justify-content:center;" onclick="previewTemplate({{ $template->id }})">
                        <i class="fas fa-eye"></i> Preview with sample data
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

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

@push('styles')
<style>
.placeholder-btn{display:flex;align-items:center;gap:8px;width:100%;padding:6px 10px;background:var(--sand-50,#faf7f0);border:1px solid var(--ink-100,#eceef2);border-radius:4px;cursor:pointer;font-family:inherit;transition:all .15s;margin-bottom:4px;}.placeholder-btn:hover{background:var(--brand-50,#fbeef1);border-color:var(--brand-200,#e8a4b8);}.placeholder-btn code{font-size:11.5px;font-weight:600;color:var(--brand-600,#8d1b3d);background:none;padding:0;}.placeholder-btn span{font-size:11px;color:var(--ink-500);}
</style>
@endpush

@push('scripts')
<script>
function insertTag(tag) {
    var ta = document.querySelector('textarea[name="body"]');
    var pos = ta.selectionStart;
    var before = ta.value.substring(0, pos);
    var after = ta.value.substring(pos);
    ta.value = before + tag + after;
    ta.focus();
    ta.selectionStart = ta.selectionEnd = pos + tag.length;
}
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
@endsection
