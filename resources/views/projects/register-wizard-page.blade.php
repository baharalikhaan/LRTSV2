{{-- ============================================================
    WIZARD REGISTRATION — FULL PAGE (register-wizard-page.blade.php)
    70/30 split: PDF preview (left) + compact wizard (right)
============================================================ --}}
@php
    $confProject ??= null;
    $pillars ??= collect();
    $colleges ??= collect();
    $projectPillarIds ??= [];
    $projectCollegeIds ??= [];
    $steps ??= [
        ['label' => 'Basic Info', 'icon' => 'fas fa-info-circle'],
        ['label' => 'Pillar & College', 'icon' => 'fas fa-university'],
        ['label' => 'Commitments', 'icon' => 'fas fa-file-signature'],
        ['label' => 'Review & Submit', 'icon' => 'fas fa-check-circle'],
    ];
@endphp

@extends('layouts.app')

@section('title', 'Register Project — Wizard')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* ── Page reset ── */
    html, body { height: 100%; margin: 0; }
    .app-content { padding: 0 !important; height: 100vh; overflow: hidden; }

    /* ── Split layout ── */
    .wizard-split {
        display: flex;
        height: 100vh;
        width: 100%;
        background: var(--ink-50, #f7f7f8);
    }

    /* ── Left: PDF preview (70%) ── */
    .wizard-pdf-pane {
        flex: 7;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-right: 1px solid var(--ink-200, #d8d6dc);
        overflow: hidden;
    }

    .wizard-pdf-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 16px;
        background: var(--sand-50, #faf7f0);
        border-bottom: 1px solid var(--ink-100, #eeedf0);
        flex-shrink: 0;
    }

    .wizard-pdf-header h3 {
        margin: 0;
        font-size: 12px;
        font-weight: 600;
        color: var(--ink-600, #4c4553);
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .wizard-pdf-header .pdf-actions {
        display: flex;
        gap: 6px;
    }

    .wizard-pdf-header .pdf-actions a,
    .wizard-pdf-header .pdf-actions button {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        font-size: 11.5px;
        font-weight: 500;
        border-radius: 4px;
        text-decoration: none;
        transition: all .15s;
        font-family: inherit;
        line-height: 1;
        cursor: pointer;
    }

    .wizard-pdf-header .pdf-actions a {
        color: var(--ink-600, #4c4553);
        background: #fff;
        border: 1px solid var(--ink-200, #d8d6dc);
    }

    .wizard-pdf-header .pdf-actions a:hover {
        background: var(--ink-100, #eeedf0);
        border-color: var(--ink-300, #b4b0ba);
    }

    .wizard-pdf-header .pdf-actions .btn-upload {
        color: #fff;
        background: var(--brand-500, #8d1b3d);
        border: 1px solid var(--brand-600, #7a1636);
        box-shadow: var(--fluent-depth-2, 0 1px 2px rgba(22,19,26,.07), 0 0px 1px rgba(22,19,26,.06));
    }

    .wizard-pdf-header .pdf-actions .btn-upload:hover {
        background: var(--brand-600, #7a1636);
        box-shadow: var(--fluent-depth-4, 0 2px 4px rgba(22,19,26,.09), 0 0px 2px rgba(22,19,26,.07));
    }

    .wizard-pdf-header .pdf-actions .btn-upload:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .wizard-pdf-body {
        flex: 1;
        position: relative;
        background: #f0efed;
    }

    .wizard-pdf-body iframe,
    .wizard-pdf-body embed,
    .wizard-pdf-body object {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
    }

    /* ── Right: wizard form (30%) ── */
    .wizard-form-pane {
        flex: 3;
        display: flex;
        flex-direction: column;
        background: #fff;
        overflow: hidden;
        min-width: 0;
    }

    .wizard-form-pane .wizard-form-header {
        padding: 12px 14px 10px;
        border-bottom: 1px solid var(--ink-100, #eeedf0);
        flex-shrink: 0;
    }

    .wizard-form-pane .wizard-form-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--brand-500, #8d1b3d);
    }

    .wizard-form-pane .wizard-form-header p {
        margin: 2px 0 0;
        font-size: 11px;
        color: var(--ink-400, #8b8592);
    }

    /* ── Compact step tabs ── */
    .wizard-compact-steps {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 12px 18px 10px;
        background: #fff;
        border-bottom: 1px solid var(--ink-100, #eeedf0);
        flex-shrink: 0;
        gap: 2px;
    }

    .wizard-compact-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        cursor: default;
        min-width: 0;
        flex: 1;
    }

    .wizard-compact-step .sc-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--ink-100, #eeedf0);
        color: var(--ink-500, #675f6e);
        font-size: 10px;
        font-weight: 700;
        transition: all .2s;
        flex-shrink: 0;
    }

    .wizard-compact-step.active .sc-circle {
        background: var(--brand-500, #8d1b3d);
        color: #fff;
        box-shadow: 0 0 0 2px var(--brand-100, #f3d2da);
    }

    .wizard-compact-step.completed .sc-circle {
        background: var(--success, #1f8a5f);
        color: #fff;
    }

    .wizard-compact-step .sc-label {
        font-size: 9px;
        font-weight: 600;
        color: var(--ink-400, #8b8592);
        white-space: nowrap;
        text-align: center;
        letter-spacing: .02em;
    }

    .wizard-compact-step.active .sc-label,
    .wizard-compact-step.completed .sc-label {
        color: var(--brand-500, #8d1b3d);
    }

    .wizard-compact-connector {
        width: auto;
        flex: 1;
        height: 2px;
        margin: 11px 3px 0;
        background: var(--ink-200, #d8d6dc);
        flex-shrink: 0;
    }

    /* ── Form body (scrollable) ── */
    .wizard-form-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 16px 18px 14px;
    }

    .wizard-compact-panel {
        display: none !important;
    }

    .wizard-compact-panel.active {
        display: block !important;
    }

    /* ── Compact form controls ── */
    .cmp-form-group {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-bottom: 10px;
    }

    .cmp-form-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--ink-600, #4c4553);
    }

    .cmp-input {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 4px;
        font-size: 12px;
        font-family: 'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
        color: var(--ink-800, #241f2a);
        background: #fff;
        transition: border-color .15s, box-shadow .15s;
        box-sizing: border-box;
    }

    .cmp-input:focus {
        outline: none;
        border-color: var(--brand-400, #b8496b);
        box-shadow: 0 0 0 2px var(--brand-100, #f3d2da);
    }

    .cmp-input[readonly] {
        background: var(--sand-50, #faf7f0);
        color: var(--ink-500, #675f6e);
        cursor: default;
    }

    .cmp-input.error {
        border-color: var(--danger, #b3261e);
        box-shadow: 0 0 0 2px rgba(179,38,30,.12);
    }

    /* ── Pillar chips ── */
    .cmp-pillar-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 2px;
    }

    .cmp-pillar-list label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: var(--ink-600, #4c4553);
        cursor: pointer;
        padding: 3px 8px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 4px;
        transition: all .15s;
    }

    .cmp-pillar-list label:hover {
        border-color: var(--brand-300, #d3738f);
        background: var(--brand-50, #fbeef1);
    }

    .cmp-pillar-list input:checked + span {
        color: var(--brand-500, #8d1b3d);
        font-weight: 600;
    }

    .cmp-pillar-list input:checked ~ label {
        border-color: var(--brand-400, #b8496b);
        background: var(--brand-50, #fbeef1);
    }

    /* ── Commitment grid ── */
    .cmp-commit-group {
        margin-bottom: 12px;
    }

    .cmp-commit-group:last-child { margin-bottom: 0; }

    .cmp-commit-group h4 {
        font-size: 11px;
        font-weight: 700;
        color: var(--ink-700, #38333e);
        margin: 0 0 5px;
        padding-bottom: 2px;
        border-bottom: 1px solid var(--ink-100, #eeedf0);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .cmp-commit-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .cmp-commit-item {
        display: flex;
        flex-direction: column;
        min-width: 72px;
    }

    .cmp-commit-item label {
        font-size: 10px;
        font-weight: 500;
        color: var(--ink-500, #675f6e);
        margin-bottom: 1px;
    }

    .cmp-commit-item input[type="number"] {
        padding: 3px 5px;
        font-size: 11px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 3px;
        max-width: 64px;
    }

    .cmp-commit-item input[type="number"]:focus {
        outline: none;
        border-color: var(--brand-400, #b8496b);
        box-shadow: 0 0 0 2px var(--brand-100, #f3d2da);
    }

    .cmp-commit-item input[type="checkbox"] {
        width: 14px;
        height: 14px;
        margin-top: 4px;
    }

    /* ── Select ── */
    .cmp-select {
        padding: 6px 8px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 4px;
        font-size: 12px;
        font-family: inherit;
        color: var(--ink-800, #241f2a);
        background: #fff;
        transition: border-color .15s;
        box-sizing: border-box;
        width: 100%;
    }

    .cmp-select:focus {
        outline: none;
        border-color: var(--brand-400, #b8496b);
        box-shadow: 0 0 0 2px var(--brand-100, #f3d2da);
    }

    /* ── Review area (table layout) ── */
    .cmp-review-area {
        background: var(--sand-50, #faf7f0);
        border: 1px solid var(--sand-100, #f2ead6);
        border-radius: 4px;
        padding: 8px 10px;
        font-size: 11px;
        color: var(--ink-700, #38333e);
        max-height: 240px;
        overflow-y: auto;
    }

    .cmp-review-area table {
        width: 100%;
        border-collapse: collapse;
    }

    .cmp-review-area table td {
        padding: 3px 4px;
        vertical-align: top;
        line-height: 1.5;
    }

    .cmp-review-area table td:first-child {
        font-weight: 600;
        color: var(--ink-800, #241f2a);
        white-space: nowrap;
        width: 40%;
        padding-right: 6px;
    }

    .cmp-review-area table td:last-child {
        color: var(--ink-600, #4c4553);
    }

    .cmp-review-area table tr:not(:last-child) td {
        border-bottom: 1px solid var(--sand-200, #e4d3ac);
    }

    .cmp-checkbox-label {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        font-size: 11px;
        color: var(--ink-600, #4c4553);
        margin-top: 10px;
    }

    .cmp-checkbox-label input[type="checkbox"] {
        width: 14px;
        height: 14px;
        margin-top: 1px;
        flex-shrink: 0;
    }

    /* ── Footer ── */
    .wizard-form-footer {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-top: 1px solid var(--ink-100, #eeedf0);
        background: #fff;
        flex-shrink: 0;
    }

    .wizard-form-footer .wz-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 11.5px;
        font-weight: 600;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all .15s;
        font-family: inherit;
        line-height: 1;
    }

    .wizard-form-footer .wz-btn i,
    .wizard-form-footer .wz-btn svg {
        font-size: 12px;
        display: inline-block;
    }

    .wz-btn-primary {
        background: var(--brand-500, #8d1b3d);
        color: #fff;
        border-color: var(--brand-600, #7a1636);
    }

    .wz-btn-primary:hover { background: var(--brand-600, #7a1636); }

    .wz-btn-secondary {
        background: #fff;
        color: var(--ink-600, #4c4553);
        border-color: var(--ink-200, #d8d6dc);
    }

    .wz-btn-secondary:hover { background: var(--ink-50, #f7f7f8); }

    .wz-btn-success {
        background: var(--success, #1f8a5f);
        color: #fff;
    }

    .wz-btn-success:hover { opacity: .9; }

    .wz-btn-ghost {
        background: transparent;
        color: var(--ink-400, #8b8592);
        border-color: transparent;
    }

    .wz-btn-ghost:hover {
        background: var(--ink-50, #f7f7f8);
        color: var(--ink-600, #4c4553);
    }

    .wizard-form-footer .wz-mleft {
        margin-right: auto;
    }

    .wizard-form-btn-group {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-left: auto;
    }

    /* ── Success state ── */
    .wizard-compact-success {
        text-align: center;
        padding: 40px 14px;
    }

    .wizard-compact-success i {
        font-size: 36px;
        color: var(--success, #1f8a5f);
        margin-bottom: 10px;
    }

    .wizard-compact-success p {
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-700, #38333e);
    }

    /* ── Responsive ── */
    @media (max-width: 820px) {
        .wizard-split { flex-direction: column; }
        .wizard-pdf-pane { flex: none; height: 45vh; border-right: none; border-bottom: 1px solid var(--ink-200, #d8d6dc); }
        .wizard-form-pane { flex: 1; }
    }
</style>
@endpush

@section('content')
<div class="wizard-split">

    {{-- ────── LEFT PANE: PDF Preview (70%) ────── --}}
    <div class="wizard-pdf-pane">
        <div class="wizard-pdf-header">
            <h3><i class="far fa-file-pdf" style="margin-right:5px;color:var(--danger,#b3261e);"></i> Proposal Document</h3>
            <div class="pdf-actions">
                @if($proposalUrl)
                    <a href="{{ $proposalUrl }}" target="_blank"><i class="fas fa-external-link-alt"></i> Open</a>
                    <a href="{{ $proposalUrl }}" download><i class="fas fa-download"></i> Download</a>
                @endif
            </div>
        </div>
        <div class="wizard-pdf-body">
            @if($proposalUrl)
                <iframe src="{{ $proposalUrl }}#toolbar=1&navpanes=0" title="Proposal PDF"></iframe>
            @else
                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--ink-400,#8b8592);font-size:13px;flex-direction:column;gap:8px;">
                    <i class="far fa-file-pdf" style="font-size:36px;opacity:.4;"></i>
                    <span>No proposal document attached to this project.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ────── RIGHT PANE: Compact Wizard Form (30%) ────── --}}
    <div class="wizard-form-pane">

        {{-- Header --}}
        <div class="wizard-form-header">
            <h2><i class="fas fa-magic" style="margin-right:6px;color:var(--gold-500,#cf9a2f);"></i> Project Registration</h2>
            <p>Project ID: {{ $confProject->old_project_id ?? '—' }}</p>
        </div>

        {{-- Step indicators --}}
        <div class="wizard-compact-steps">
            @foreach($steps as $i => $step)
                <div class="wizard-compact-step {{ $i === 0 ? 'active' : '' }}"
                     data-step="{{ $i + 1 }}"
                     data-label="{{ $step['label'] }}">
                    <span class="sc-circle">{{ $i + 1 }}</span>
                    <span class="sc-label">{{ $step['label'] }}</span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="wizard-compact-connector"></div>
                @endif
            @endforeach
        </div>

        <form id="wizardCompactForm" method="POST" action="{{ route('wizard.save-all') }}">
            @csrf
            <input type="hidden" name="project_id" value="{{ $confProject->id ?? 0 }}">

            <div class="wizard-form-scroll">

                {{-- TAB 1: Basic Info --}}
                <div class="wizard-compact-panel active" data-panel="1">
                    <div class="cmp-form-group">
                        <label>Project Title</label>
                        <input type="text" class="cmp-input" readonly
                               value="{{ $confProject->title ?? '—' }}">
                    </div>
                    <div class="cmp-form-group">
                        <label>PI Name</label>
                        <input type="text" class="cmp-input" readonly
                               value="{{ $confProject->author ?? '—' }}">
                    </div>
                    <div class="cmp-form-group">
                        <label>PI Email</label>
                        <input type="text" class="cmp-input" readonly
                               value="{{ $confProject->email ?? '—' }}">
                    </div>
                    <input type="hidden" name="project_title_en" value="{{ $confProject->title ?? '' }}">
                    <input type="hidden" name="pi_name" value="{{ $confProject->author ?? auth()->user()->name ?? '' }}">
                    <input type="hidden" name="pi_email" value="{{ $confProject->email ?? auth()->user()->email ?? '' }}">
                </div>

                {{-- TAB 2: Pillar & College --}}
                <div class="wizard-compact-panel" data-panel="2">
                    <div class="cmp-form-group">
                        <label>Pillars</label>
                        <div class="cmp-pillar-list">
                            @foreach($pillars as $p)
                                @php
                                    $isChecked = (old('pillars') && in_array($p->id, old('pillars', []))) || in_array($p->id, $projectPillarIds);
                                @endphp
                                <label>
                                    <input type="checkbox" name="pillars[]" value="{{ $p->id }}"
                                           {{ $isChecked ? 'checked' : '' }}>
                                    <span>{{ $p->pillar }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="cmp-form-group" style="margin-top:10px;">
                        <label for="cmpCollege">College</label>
                        <select id="cmpCollege" name="college_id" class="cmp-select">
                            <option value="">— Select —</option>
                            @foreach($colleges as $c)
                                @php
                                    $colSelected = (old('college_id') !== null && old('college_id') == $c->id) || in_array($c->id, $projectCollegeIds);
                                @endphp
                                <option value="{{ $c->id }}"{{ $colSelected ? ' selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- TAB 3: Commitments --}}
                <div class="wizard-compact-panel" data-panel="3">
                    <div class="cmp-commit-group">
                        <h4>Publications</h4>
                        <div class="cmp-commit-grid">
                            <div class="cmp-commit-item"><label>Q1 Articles</label><input type="number" name="pub_q1" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Q2 Articles</label><input type="number" name="pub_q2" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Q3 Articles</label><input type="number" name="pub_q3" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Q4 Articles</label><input type="number" name="pub_q4" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Conferences</label><input type="number" name="pub_conf" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Books</label><input type="number" name="pub_books" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Edited Books</label><input type="number" name="pub_edit_books" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Chapters</label><input type="number" name="pub_chapters" min="0" step="1" value="0"></div>
                        </div>
                    </div>
                    <div class="cmp-commit-group">
                        <h4>Intellectual Property</h4>
                        <div class="cmp-commit-grid">
                            <div class="cmp-commit-item"><label>Intellectual Property</label><input type="number" name="ip_count" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Filed Patents</label><input type="number" name="ip_patents" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Open-Source SW</label><input type="number" name="ip_opensource" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Start-up</label><input type="checkbox" name="ip_startup" value="1" style="width:14px;height:14px;margin-top:4px;"></div>
                            <div class="cmp-commit-item"><label>Ethical</label><input type="checkbox" name="ip_ethical" value="1" style="width:14px;height:14px;margin-top:4px;"></div>
                        </div>
                    </div>
                    <div class="cmp-commit-group">
                        <h4>Students & Training</h4>
                        <div class="cmp-commit-grid">
                            <div class="cmp-commit-item"><label>Master Students</label><input type="number" name="stu_master" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Undergraduate</label><input type="number" name="stu_ug" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>PhD Students</label><input type="number" name="stu_phd" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Cross-College</label><input type="checkbox" name="stu_cross" value="1" style="width:14px;height:14px;margin-top:4px;"></div>
                        </div>
                    </div>
                </div>

                {{-- TAB 4: Review & Submit --}}
                <div class="wizard-compact-panel" data-panel="4">
                    <p style="font-size:11px;color:var(--ink-400,#8b8592);margin-bottom:10px;">
                        Please review before submitting.
                    </p>
                    <div id="cmpReviewArea" class="cmp-review-area">
                        {{-- Populated dynamically --}}
                    </div>
                    <label class="cmp-checkbox-label">
                        <input type="checkbox" id="cmpAgreeCheck" required>
                        <span>I confirm the information is accurate.</span>
                    </label>
                </div>

            </div>{{-- /.wizard-form-scroll --}}

            {{-- Footer --}}
            <div class="wizard-form-footer">
                <a href="{{ route('projects.available') }}" class="wz-btn wz-btn-ghost" style="text-decoration:none;">
                    Cancel
                </a>
                <div class="wizard-form-btn-group">
                    <button type="button" id="cmpPrevBtn" class="wz-btn wz-btn-secondary" style="display:none;">
                        <i class="fas fa-chevron-left"></i> Back
                    </button>
                    <button type="button" id="cmpNextBtn" class="wz-btn wz-btn-primary">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <button type="submit" id="cmpSubmitBtn" class="wz-btn wz-btn-success" style="display:none;">
                        <i class="fas fa-check"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>{{-- /.wizard-form-pane --}}
</div>{{-- /.wizard-split --}}
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const prevBtn     = document.getElementById('cmpPrevBtn');
    const nextBtn     = document.getElementById('cmpNextBtn');
    const submitBtn   = document.getElementById('cmpSubmitBtn');
    const panels      = document.querySelectorAll('.wizard-compact-panel');
    const steps       = document.querySelectorAll('.wizard-compact-step');
    const agreeCheck  = document.getElementById('cmpAgreeCheck');
    const wizardForm  = document.getElementById('wizardCompactForm');

    let currentStep = 1;
    const totalSteps = panels.length;

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        panels.forEach(p => p.classList.remove('active'));
        const activePanel = document.querySelector(`.wizard-compact-panel[data-panel="${step}"]`);
        if (activePanel) activePanel.classList.add('active');

        steps.forEach((s, i) => {
            const idx = i + 1;
            s.classList.remove('active', 'completed');
            if (idx === step) s.classList.add('active');
            else if (idx < step) s.classList.add('completed');
        });

        if (prevBtn) prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
        if (nextBtn) nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
        if (submitBtn) submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';

        const scroll = document.querySelector('.wizard-form-scroll');
        if (scroll) scroll.scrollTop = 0;
    }

    if (nextBtn) nextBtn.addEventListener('click', function() {
        const currentPanel = document.querySelector(`.wizard-compact-panel[data-panel="${currentStep}"]`);
        if (currentPanel) {
            const required = currentPanel.querySelectorAll('[required]');
            for (const el of required) {
                if (!el.value || el.value.trim() === '') {
                    el.focus();
                    el.classList.add('error');
                    setTimeout(() => el.classList.remove('error'), 2000);
                    return;
                }
            }
        }
        if (currentStep + 1 === totalSteps) buildReview();
        goToStep(currentStep + 1);
    });

    if (prevBtn) prevBtn.addEventListener('click', function() {
        goToStep(currentStep - 1);
    });

    function buildReview() {
        const area = document.getElementById('cmpReviewArea');
        if (!area) return;

        const projTitle = document.querySelector('input[name="project_title_en"]')?.value || '—';
        const piName    = document.querySelector('input[name="pi_name"]')?.value || '—';
        const piEmail   = document.querySelector('input[name="pi_email"]')?.value || '—';

        const pillarLabels = [];
        document.querySelectorAll('input[name="pillars[]"]:checked').forEach(function(cb) {
            const label = cb.closest('label')?.textContent?.trim() || cb.value;
            pillarLabels.push(esc(label));
        });

        const collegeSel = document.getElementById('cmpCollege');
        const collegeLabel = collegeSel ? collegeSel.options[collegeSel.selectedIndex]?.text || '—' : '—';

        function getVal(name) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return null;
            if (el.type === 'checkbox') return el.checked ? 'Yes' : '';
            return parseInt(el.value) || 0;
        }

        const pubFields = [
            ['pub_q1', 'Q1 Articles'], ['pub_q2', 'Q2 Articles'], ['pub_q3', 'Q3 Articles'],
            ['pub_q4', 'Q4 Articles'], ['pub_conf', 'Conferences'], ['pub_books', 'Books'],
            ['pub_edit_books', 'Edited Books'], ['pub_chapters', 'Chapters']
        ];
        let pubList = [];
        pubFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v > 0) pubList.push(p[1] + ': ' + v);
        });
        const pubSummary = pubList.length ? pubList.join(', ') : '—';

        const ipFields = [
            ['ip_count', 'Intellectual Property'], ['ip_patents', 'Filed Patents'], ['ip_opensource', 'Open-Source SW'],
            ['ip_startup', 'Start-up'], ['ip_ethical', 'Ethical']
        ];
        let ipList = [];
        ipFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v !== '' && v !== 0) ipList.push(p[1] + ': ' + v);
        });
        const ipSummary = ipList.length ? ipList.join(', ') : '—';

        const stuFields = [
            ['stu_master', 'Master Students'], ['stu_ug', 'Undergraduate'], ['stu_phd', 'PhD Students'],
            ['stu_cross', 'Cross-College']
        ];
        let stuList = [];
        stuFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v !== '' && v !== 0) stuList.push(p[1] + ': ' + v);
        });
        const stuSummary = stuList.length ? stuList.join(', ') : '—';

        const rows = [
            ['Project', projTitle],
            ['PI', piName + ' — ' + piEmail],
            ['College', collegeLabel],
            ['Pillars', pillarLabels.length ? pillarLabels.join(', ') : '—'],
            ['Publications', pubSummary],
            ['Intellectual Property', ipSummary],
            ['Students', stuSummary]
        ];

        let html = '<table>';
        rows.forEach(function(r) {
            html += '<tr><td>' + esc(r[0]) + '</td><td>' + r[1] + '</td></tr>';
        });
        html += '</table>';
        area.innerHTML = html;
    }

    function esc(s) {
        if (!s) return s;
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // ── AJAX submission ──
    if (wizardForm) {
        wizardForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';
            }

            const formData = new FormData(wizardForm);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            $.ajax({
                url: wizardForm.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        document.querySelectorAll('.wizard-compact-step').forEach(function(s) {
                            s.classList.remove('active');
                            s.classList.add('completed');
                        });
                        const scroll = document.querySelector('.wizard-form-scroll');
                        if (scroll) {
                            scroll.innerHTML = '<div class="wizard-compact-success"><i class="fas fa-check-circle"></i><p>' + (res.message || 'Registered successfully.') + '</p></div>';
                        }
                        const footer = document.querySelector('.wizard-form-footer');
                        if (footer) footer.style.display = 'none';
                        setTimeout(function() {
                            if (res.redirect) window.location.href = res.redirect;
                            else window.location.href = '{{ route("projects.available") }}';
                        }, 2000);
                    } else if (res.error) {
                        const scroll = document.querySelector('.wizard-form-scroll');
                        if (scroll) {
                            scroll.innerHTML = '<div class="wizard-compact-success" style="color:var(--danger,#b3261e);"><i class="fas fa-exclamation-circle" style="color:var(--danger,#b3261e);"></i><p>' + res.error + '</p></div>';
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit';
                        }
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                        else if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                    }
                    const scroll = document.querySelector('.wizard-form-scroll');
                    if (scroll) {
                        scroll.innerHTML = '<div class="wizard-compact-success" style="color:var(--danger,#b3261e);"><i class="fas fa-exclamation-circle" style="color:var(--danger,#b3261e);"></i><p>' + msg + '</p></div>';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit';
                    }
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        goToStep(1);
    });

})();
</script>
@endpush
