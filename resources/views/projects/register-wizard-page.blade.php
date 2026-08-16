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
        padding: 14px 16px;
        background: var(--ink-50, #f7f7f8);
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
        gap: 4px;
        margin-bottom: 12px;
    }

    .cmp-form-group label {
        font-size: 11px;
        font-weight: 600;
        color: var(--ink-600, #4c4553);
        letter-spacing: .02em;
    }

    /* ── Section card ── */
    .cmp-section {
        background: #fff;
        border: 1px solid var(--ink-100, #eeedf0);
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .cmp-section-header {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
        padding-bottom: 6px;
        border-bottom: 1px solid var(--ink-100, #eeedf0);
    }

    .cmp-section-header i {
        color: var(--brand-500, #8d1b3d);
        font-size: 12px;
    }

    .cmp-section-header h4 {
        margin: 0;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--ink-700, #38333e);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .cmp-section-desc {
        font-size: 10.5px;
        color: var(--ink-400, #8b8592);
        margin: 0 0 8px;
        line-height: 1.4;
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
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }

    .cmp-pillar-list label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--ink-600, #4c4553);
        cursor: pointer;
        padding: 6px 10px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 4px;
        transition: all .15s;
        background: #fff;
    }

    .cmp-pillar-list label:hover {
        border-color: var(--brand-300, #d3738f);
        background: var(--brand-50, #fbeef1);
    }

    .cmp-pillar-list input {
        width: 14px;
        height: 14px;
    }

    .cmp-pillar-list input:checked + span {
        color: var(--brand-500, #8d1b3d);
        font-weight: 600;
    }

    .cmp-pillar-list label:has(input:checked) {
        border-color: var(--brand-400, #b8496b);
        background: var(--brand-50, #fbeef1);
    }

    /* ── Commitment grid ── */
    .cmp-commit-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .cmp-commit-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .cmp-commit-item label {
        font-size: 10.5px;
        font-weight: 500;
        color: var(--ink-600, #4c4553);
    }

    .cmp-commit-item input[type="number"] {
        padding: 5px 8px;
        font-size: 12px;
        border: 1px solid var(--ink-200, #d8d6dc);
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    .cmp-commit-item input[type="number"]:focus {
        outline: none;
        border-color: var(--brand-400, #b8496b);
        box-shadow: 0 0 0 2px var(--brand-100, #f3d2da);
    }

    .cmp-commit-item input[type="checkbox"] {
        width: 14px;
        height: 14px;
        margin-top: 2px;
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

    /* ── Review area ── */
    .cmp-review-area {
        background: #fff;
        border: 1px solid var(--ink-100, #eeedf0);
        border-radius: 4px;
        padding: 0;
        font-size: 11px;
        color: var(--ink-700, #38333e);
    }

    .cmp-review-section {
        padding: 8px 10px;
    }

    .cmp-review-section:not(:last-child) {
        border-bottom: 1px solid var(--ink-100, #eeedf0);
    }

    .cmp-review-section-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 10.5px;
        font-weight: 700;
        color: var(--brand-500, #8d1b3d);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 6px;
    }

    .cmp-review-section-title i {
        font-size: 10px;
    }

    .cmp-review-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cmp-review-table td {
        padding: 3px 6px;
        font-size: 10.5px;
        border: none;
    }

    .cmp-review-table td:nth-child(odd) {
        font-weight: 600;
        color: var(--ink-600, #4c4553);
        white-space: nowrap;
        width: 25%;
    }

    .cmp-review-table td:nth-child(even) {
        color: var(--ink-800, #241f2a);
    }

    .cmp-review-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        gap: 8px;
    }

    .cmp-review-row:not(:last-child) {
        border-bottom: 1px dashed var(--ink-100, #eeedf0);
    }

    .cmp-review-label {
        font-weight: 600;
        color: var(--ink-600, #4c4553);
        white-space: nowrap;
    }

    .cmp-review-value {
        color: var(--ink-800, #241f2a);
    }

    .cmp-review-total {
        margin-top: 4px;
        padding-top: 4px;
        border-top: 1px solid var(--ink-200, #d8d6dc);
        font-size: 10.5px;
        color: var(--ink-700, #38333e);
        text-align: right;
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
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-file-alt"></i>
                            <h4>Project Details</h4>
                        </div>
                        <p class="cmp-section-desc">Information imported from the Excel file.</p>
                        <div class="cmp-form-group">
                            <label>Project Title</label>
                            <input type="text" class="cmp-input" readonly
                                   value="{{ $confProject->title ?? '—' }}">
                        </div>
                    </div>
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-user"></i>
                            <h4>Principal Investigator</h4>
                        </div>
                        <div class="cmp-form-group">
                            <label>PI Name</label>
                            <input type="text" class="cmp-input" readonly
                                   value="{{ $confProject->lpi->name ?? $confProject->pi_name ?? $confProject->author ?? '—' }}">
                        </div>
                        <div class="cmp-form-group">
                            <label>PI Email</label>
                            <input type="text" class="cmp-input" readonly
                                   value="{{ $confProject->lpi->email ?? $confProject->pi_email ?? $confProject->email ?? '—' }}">
                        </div>
                    </div>
                    <input type="hidden" name="project_title_en" value="{{ $confProject->title ?? '' }}">
                    <input type="hidden" name="pi_name" value="{{ $confProject->lpi->name ?? $confProject->pi_name ?? $confProject->author ?? auth()->user()->name ?? '' }}">
                    <input type="hidden" name="pi_email" value="{{ $confProject->lpi->email ?? $confProject->pi_email ?? $confProject->email ?? auth()->user()->email ?? '' }}">
                </div>

                {{-- TAB 2: Pillar & College --}}
                <div class="wizard-compact-panel" data-panel="2">
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-university"></i>
                            <h4>Research Pillars</h4>
                        </div>
                        <p class="cmp-section-desc">Select all pillars that apply to this project.</p>
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
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-building"></i>
                            <h4>College Affiliation</h4>
                        </div>
                        <div class="cmp-form-group">
                            <label for="cmpCollege">Select College</label>
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
                </div>

                {{-- TAB 3: Commitments --}}
                <div class="wizard-compact-panel" data-panel="3">
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-book-open"></i>
                            <h4>Publications</h4>
                        </div>
                        <p class="cmp-section-desc">Expected publication outputs during the project.</p>
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
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-lightbulb"></i>
                            <h4>Intellectual Property</h4>
                        </div>
                        <p class="cmp-section-desc">IP and innovation outputs.</p>
                        <div class="cmp-commit-grid">
                            <div class="cmp-commit-item"><label>IP Disclosures</label><input type="number" name="ip_count" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Filed Patents</label><input type="number" name="ip_patents" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Open-Source SW</label><input type="number" name="ip_opensource" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Start-up</label><input type="checkbox" name="ip_startup" value="1" style="width:14px;height:14px;margin-top:4px;"></div>
                        </div>
                    </div>
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-user-graduate"></i>
                            <h4>Students & Training</h4>
                        </div>
                        <p class="cmp-section-desc">Student involvement and training commitments.</p>
                        <div class="cmp-commit-grid">
                            <div class="cmp-commit-item"><label>Master Students</label><input type="number" name="stu_master" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Undergraduate</label><input type="number" name="stu_ug" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>PhD Students</label><input type="number" name="stu_phd" min="0" step="1" value="0"></div>
                            <div class="cmp-commit-item"><label>Cross-College</label><input type="checkbox" name="stu_cross" value="1" style="width:14px;height:14px;margin-top:4px;"></div>
                        </div>
                    </div>
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-check-double"></i>
                            <h4>Ethical Approval</h4>
                        </div>
                        <p class="cmp-section-desc">Does this project require ethical approval?</p>
                        <label class="cmp-checkbox-label" style="margin:0;padding:8px 10px;background:var(--ink-50,#f7f7f8);border-radius:4px;border:1px solid var(--ink-100,#eeedf0);">
                            <input type="checkbox" name="ip_ethical" value="1" style="width:16px;height:16px;">
                            <span>This project requires ethical approval</span>
                        </label>
                    </div>
                </div>

                {{-- TAB 4: Review & Submit --}}
                <div class="wizard-compact-panel" data-panel="4">
                    <div class="cmp-section">
                        <div class="cmp-section-header">
                            <i class="fas fa-clipboard-check"></i>
                            <h4>Review Summary</h4>
                        </div>
                        <p class="cmp-section-desc">Please verify all information before submitting.</p>
                        <div id="cmpReviewArea" class="cmp-review-area">
                            {{-- Populated dynamically --}}
                        </div>
                    </div>
                    <div class="cmp-section" style="background:var(--brand-50,#fbeef1);border-color:var(--brand-200,#e8a4b8);">
                        <label class="cmp-checkbox-label" style="margin:0;">
                            <input type="checkbox" id="cmpAgreeCheck" required>
                            <span>I confirm that the information provided is accurate and complete.</span>
                        </label>
                    </div>
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
            if (el.type === 'checkbox') return el.checked;
            return parseInt(el.value) || 0;
        }

        function buildRow(label, value) {
            return '<div class="cmp-review-row"><span class="cmp-review-label">' + esc(label) + '</span><span class="cmp-review-value">' + esc(value) + '</span></div>';
        }

        function buildTable4(items) {
            let html = '<table class="cmp-review-table"><tr>';
            for (let i = 0; i < items.length; i++) {
                html += '<td>' + esc(items[i][0]) + '</td><td>' + esc(items[i][1]) + '</td>';
                if ((i + 1) % 2 === 0 && i + 1 < items.length) html += '</tr><tr>';
            }
            if (items.length % 2 !== 0) html += '<td></td><td></td>';
            html += '</tr></table>';
            return html;
        }

        function buildSection(title, icon, content) {
            let html = '<div class="cmp-review-section">';
            html += '<div class="cmp-review-section-title"><i class="fas ' + icon + '"></i> ' + esc(title) + '</div>';
            html += content;
            html += '</div>';
            return html;
        }

        let html = '';

        // ── Project Info ──
        let projInfoContent = '';
        projInfoContent += buildRow('Project Title', projTitle);
        projInfoContent += buildRow('PI Name', piName);
        projInfoContent += buildRow('PI Email', piEmail);
        projInfoContent += buildRow('College', collegeLabel);
        projInfoContent += buildRow('Pillars', pillarLabels.length ? pillarLabels.join(', ') : '—');
        html += buildSection('Project Info', 'fa-file-alt', projInfoContent);

        // ── Publications ──
        const pubFields = [
            ['pub_q1', 'Q1 Articles'], ['pub_q2', 'Q2 Articles'], ['pub_q3', 'Q3 Articles'],
            ['pub_q4', 'Q4 Articles'], ['pub_conf', 'Conferences'], ['pub_books', 'Books'],
            ['pub_edit_books', 'Edited Books'], ['pub_chapters', 'Chapters']
        ];
        let pubItems = [];
        let totalPub = 0;
        pubFields.forEach(function(p) {
            const v = getVal(p[0]);
            totalPub += v;
            pubItems.push([p[1], v]);
        });
        let pubContent = buildTable4(pubItems);
        pubContent += '<div class="cmp-review-total"><strong>Total Publications:</strong> ' + totalPub + '</div>';
        html += buildSection('Publications', 'fa-book-open', pubContent);

        // ── Intellectual Property ──
        const ipFields = [
            ['ip_count', 'IP Disclosures'], ['ip_patents', 'Filed Patents'], ['ip_opensource', 'Open-Source SW'],
            ['ip_startup', 'Start-up']
        ];
        let ipItems = [];
        let totalIp = 0;
        ipFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (p[0] === 'ip_startup') {
                ipItems.push([p[1], v ? 'Yes' : 'No']);
            } else {
                totalIp += v;
                ipItems.push([p[1], v]);
            }
        });
        let ipContent = buildTable4(ipItems);
        ipContent += '<div class="cmp-review-total"><strong>Total IP:</strong> ' + totalIp + '</div>';
        html += buildSection('Intellectual Property', 'fa-lightbulb', ipContent);

        // ── Students & Training ──
        const stuFields = [
            ['stu_master', 'Master Students'], ['stu_ug', 'Undergraduate'], ['stu_phd', 'PhD Students'],
            ['stu_cross', 'Cross-College']
        ];
        let stuItems = [];
        let totalStu = 0;
        stuFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (p[0] === 'stu_cross') {
                stuItems.push([p[1], v ? 'Yes' : 'No']);
            } else {
                totalStu += v;
                stuItems.push([p[1], v]);
            }
        });
        let stuContent = buildTable4(stuItems);
        stuContent += '<div class="cmp-review-total"><strong>Total Students:</strong> ' + totalStu + '</div>';
        html += buildSection('Students & Training', 'fa-user-graduate', stuContent);

        // ── Ethical Approval ──
        let ethicalContent = buildTable4([
            ['Requires Ethical Approval', getVal('ip_ethical') ? 'Yes' : 'No']
        ]);
        html += buildSection('Ethical Approval', 'fa-check-double', ethicalContent);

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
