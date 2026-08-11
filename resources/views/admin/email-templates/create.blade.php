@extends('layouts.app')

@section('title', 'Create Email Template - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-file-lines"></i> Create Email Template</h1>
        <p>Define a reusable email template with placeholder tags.</p>
    </div>
</div>

@if($errors->any())
<div class="fluent-alert fluent-alert--error" style="margin-bottom:16px;">
    <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('email-templates.store') }}">
    @csrf
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; align-items:start;">

        <div>
            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-tag" style="margin-right:6px;"></i> Template Name</h2></div>
                <div class="panel-body">
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Progress Report Reminder">
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-heading" style="margin-right:6px;"></i> Subject</h2></div>
                <div class="panel-body">
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-control" placeholder="e.g. Progress Report Reminder">
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-align-left" style="margin-right:6px;"></i> Body</h2></div>
                <div class="panel-body">
                    <textarea name="body" required rows="14" class="form-control" placeholder="Dear *name*,&#10;&#10;This is a reminder that...">{{ old('body') }}</textarea>
                    <div style="font-size:11px; color:var(--color-ink-400); margin-top:4px;">Click a placeholder tag on the right to insert it at cursor position.</div>
                </div>
            </div>

            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-signature" style="margin-right:6px;"></i> Signature <span style="font-weight:400; font-size:11px; color:var(--color-ink-400);">(optional)</span></h2></div>
                <div class="panel-body">
                    <textarea name="signature" rows="3" class="form-control" placeholder="Best regards,&#10;RTS Admin Team">{{ old('signature') }}</textarea>
                </div>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <a href="{{ route('email-templates.index') }}" class="btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
                <button type="submit" class="btn-primary btn-sm"><i class="fas fa-save"></i> Create Template</button>
            </div>
        </div>

        <div>
            <div class="panel" style="margin-bottom:16px;">
                <div class="panel-head"><h2 style="font-size:14px;"><i class="fas fa-tag" style="margin-right:6px;"></i> Category</h2></div>
                <div class="panel-body">
                    <select name="category" class="form-control">
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="panel">
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
        </div>
    </div>
</form>

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
</script>
@endpush
@endsection
