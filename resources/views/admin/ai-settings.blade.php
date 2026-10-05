@extends('layouts.app')

@section('title', 'AI Assistant Settings - RTS')

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-robot"></i> AI Assistant Settings</h1>
        <p>Configure Gemini API, model, and prompt.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px;">{{ session('error') }}</div>
@endif

{{-- General Settings --}}
<div class="panel" style="margin-bottom:22px;">
    <div class="panel-head">
        <h2><i class="fas fa-cog"></i> General Settings</h2>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.ai-settings.save') }}">
            @csrf
            {{-- ── Assistant on/off switch ── --}}
        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--ink-50,#f5f4f2);border:1px solid var(--ink-100,#eceef2);border-radius:8px;margin-bottom:16px;">
            <label class="form-check form-switch" style="margin:0;display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="hidden" name="assistant_enabled" value="0">
                <input type="checkbox" name="assistant_enabled" value="1" class="form-check-input"
                       {{ $settings['assistant_enabled'] === '1' ? 'checked' : '' }}
                       style="width:36px;height:20px;margin-left:0;cursor:pointer;">
                <span style="font-size:12.5px;font-weight:600;color:var(--ink-700);">Enable AI Assistant</span>
            </label>
            <span style="font-size:11.5px;color:var(--ink-400);">
                When OFF, the Gemini chat widget is removed from the Help Center and <code>/ai/chat</code> answers are disabled for everyone.
            </span>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="font-size:12px; font-weight:600; color:var(--ink-600); display:block; margin-bottom:4px;">API Key</label>
                    <input type="text" name="api_key" value="{{ $settings['api_key'] }}" required
                           class="form-control" placeholder="AIza..." style="font-size:12px;">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:var(--ink-600); display:block; margin-bottom:4px;">Model</label>
                    <select name="model" class="form-control" style="font-size:12px;">
                        @foreach([
                            'gemini-2.0-flash'         => 'Gemini 2.0 Flash (recommended free)',
                            'gemini-2.5-flash'         => 'Gemini 2.5 Flash',
                            'gemini-2.5-pro'           => 'Gemini 2.5 Pro',
                            'gemini-1.5-pro'           => 'Gemini 1.5 Pro',
                            'gemini-1.5-flash'         => 'Gemini 1.5 Flash',
                            'gemini-3-flash'           => 'Gemini 3 Flash',
                            'gemini-3.1-flash-lite'    => 'Gemini 3.1 Flash Lite',
                            'gemini-3.5-flash'         => 'Gemini 3.5 Flash',
                            'gemini-3.5-flash-lite'    => 'Gemini 3.5 Flash Lite',
                            'gemini-3.6-flash'         => 'Gemini 3.6 Flash',
                            'gemini-3.7-flash'         => 'Gemini 3.7 Flash',
                            'gemini-3.8-flash'         => 'Gemini 3.8 Flash (latest)',
                        ] as $value => $label)
                            <option value="{{ $value }}" {{ $settings['model'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:var(--ink-600); display:block; margin-bottom:4px;">Mode</label>
                    <select name="mode" class="form-control" style="font-size:12px;">
                        <option value="static" {{ $settings['mode'] === 'static' ? 'selected' : '' }}>Static (prompt only, no DB)</option>
                        <option value="dynamic" {{ $settings['mode'] === 'dynamic' ? 'selected' : '' }}>Dynamic (live database access)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Settings</button>
        </form>
    </div>
</div>

{{-- Prompt --}}
<div class="panel">
    <div class="panel-head">
        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
            <h2><i class="fas fa-file-alt"></i> System Prompt</h2>
            <form method="POST" action="{{ route('admin.ai-settings.reset') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Reset prompt to default?')">
                    <i class="fas fa-undo"></i> Restore Default
                </button>
            </form>
        </div>
    </div>
    <div class="panel-body">
        <form method="POST" action="{{ route('admin.ai-settings.prompt.save') }}">
            @csrf
            <div style="margin-bottom:12px;">
                <textarea name="prompt" required class="form-control" rows="18" style="font-size:12px; font-family:monospace; line-height:1.6;">{{ $settings['prompt'] }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Prompt</button>
        </form>
    </div>
</div>
@endsection
