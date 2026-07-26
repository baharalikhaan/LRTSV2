@php
    $project ??= null;
    $outcomes ??= collect();
    $structuredOutcomeTypes ??= [];
    $submissions ??= collect();
    $deadlines ??= [];
    $projectStudents ??= collect();
    $projectResearchers ??= collect();

    // Cross-College & Research Awards from outcomes
    $crossCollegeOutcome = $outcomes->where('type', 'cross_college')->first();
    $crossCollegeValue = $crossCollegeOutcome ? 'Yes' : 'No';
    $crossCollegeDetails = $crossCollegeOutcome ? $crossCollegeOutcome->identifier : '';

    $researchAwardsOutcome = $outcomes->where('type', 'research_awards')->first();
    $researchAwardsValue = $researchAwardsOutcome ? 'Yes' : 'No';
    $researchAwardsDetails = $researchAwardsOutcome ? $researchAwardsOutcome->identifier : '';
    // Extract latest submissions by type for display in the upload cards
    $progressSub = $submissions->where('type', 'progress')->last();
    $readinessSub = $submissions->where('type', 'readiness')->last();
    $finalSub = $submissions->where('type', 'final')->last();

    // Pre-group outcomes by type for pre-filling the form
    $outcomeGroups = $outcomes->groupBy('type');
@endphp

@extends('layouts.app')

@section('title', 'Add Progress — ' . ($project->title ?? 'Project'))

@section('content')
<div class="page-head">
    <div>
        <h1><i class="fas fa-chart-line"></i> Add Progress Report</h1>
        <p>Submit a progress report for <strong>{{ $project->title ?? '' }}</strong>.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('projects.show', $project->id) }}" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Project
        </a>
    </div>
</div>

{{-- Research Call Inactive Banner --}}
@if($project && $project->program && !$project->programIsActive())
<div style="background:linear-gradient(135deg, #fbeef1 0%, #f3d2da 100%); border:1px solid var(--brand-200); border-radius:8px; padding:14px 18px; margin-bottom:22px; display:flex; align-items:center; gap:12px;">
    <div style="width:36px; height:36px; border-radius:50%; background:var(--brand-500); color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0;">
        <i class="fas fa-lock"></i>
    </div>
    <div>
        <strong style="color:var(--brand-800); font-size:14px;">Research Call Inactive</strong>
        <p style="margin:2px 0 0 0; color:var(--brand-700); font-size:13px;">
            The research call <strong>{{ $project->program->program_title }}</strong> is no longer active.
            Progress cannot be added to projects under this research call.
        </p>
    </div>
</div>
@endif

{{-- ========== TABBED FORM ========== --}}
<form id="mainProgressForm" action="{{ route('progress.save', $project->id) }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB HEADER (Fluent-style) --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="ws-tabs" role="tablist">
        <button type="button" class="ws-tab active" role="tab" data-tab="tab-outcomes" onclick="switchTab('tab-outcomes', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
            Project Outcomes
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-students" onclick="switchTab('tab-students', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Students & Personnel
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-submissions" onclick="switchTab('tab-submissions', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Report Submissions
        </button>
        <button type="button" class="ws-tab" role="tab" data-tab="tab-review" onclick="switchTab('tab-review', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Review & Submit
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: PROJECT OUTCOMES (merged Scholarly Articles + IP & Contributions) --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-outcomes" class="ws-tab-panel" role="tabpanel">

        {{-- BOTH SECTIONS IN A SINGLE ROW (two columns) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

            {{-- ── COLUMN 1: SCHOLARLY ARTICLES ── --}}
            <div>
                <h2 class="ws-section-title">Scholarly Articles</h2>

                @php
                    $scholarlyTypes = [
                        'journal_q1'    => 'Journal articles (Web of Science — Q1)',
                        'journal_q2'    => 'Journal articles (Web of Science — Q2)',
                        'journal_q3'    => 'Journal articles (Web of Science — Q3)',
                        'journal_q4'    => 'Journal articles (Web of Science — Q4)',
                        'conference'    => 'Indexed international conferences',
                        'book'          => 'Published Books',
                        'edited_book'   => 'Edited Books (collection)',
                        'book_chapter'  => 'Book Chapters',
                    ];
                    $contribTypeKeys = ['ip_disclosure','provisional_patent','patent_granted','open_source_sw','startup','cross_college','research_awards'];
                    $scholarlyOutcomes = $outcomes->filter(function($o) use ($contribTypeKeys) { return !in_array($o->type, $contribTypeKeys); });
                @endphp

                <div class="ws-card" style="padding:16px 18px;">
                    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <div style="flex:0 0 200px;max-width:100%;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Article Type</label>
                            <select id="scholarlyType" class="ws-input" style="width:100%;">
                                @foreach($scholarlyTypes as $tKey => $tLabel)
                                    <option value="{{ $tKey }}">{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:160px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">DOI / Identifier</label>
                            <input type="text" id="scholarlyIdentifier" class="ws-input" placeholder="Enter DOI / ISBN…" style="width:100%;">
                        </div>
                        <button type="button" class="ws-btn ws-btn-primary" onclick="addScholarlyRecord()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Article
                        </button>
                    </div>

                    <div id="scholarlyRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($scholarlyOutcomes as $so)
                        <div class="scholarly-record-row" data-id="{{ $so->id }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                            <input type="hidden" name="outcomes[{{ $so->type }}][existing][]" value="{{ $so->id }}">
                            <input type="hidden" name="outcomes[{{ $so->type }}][detail][]" value="{{ $so->identifier }}">
                            <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $scholarlyTypes[$so->type] ?? ucfirst(str_replace('_',' ',$so->type)) }}</span>
                            <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $so->identifier }}</span>
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteOutcomeRecord(this, '{{ $so->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        @endforeach
                        @if($scholarlyOutcomes->count() === 0)
                        <div id="scholarlyEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No scholarly articles added yet.</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── COLUMN 2: INTELLECTUAL PROPERTY ── --}}
            <div>
                <h2 class="ws-section-title">Intellectual Property</h2>

                @php
                    $ipTypes = [
                        'ip_disclosure'      => 'Intellectual Property Disclosure',
                        'provisional_patent' => 'Provisional Patent',
                        'patent_granted'     => 'Patents Granted',
                        'open_source_sw'     => 'Open Source Software',
                        'startup'            => 'Start-Up Created',
                    ];
                    $ipOutcomes = $outcomes->whereIn('type', array_keys($ipTypes));
                @endphp

                <div class="ws-card" style="padding:16px 18px;">
                    <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                        <div style="flex:0 0 200px;max-width:100%;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">IP Type</label>
                            <select id="ipType" class="ws-input" style="width:100%;">
                                @foreach($ipTypes as $tKey => $tLabel)
                                    <option value="{{ $tKey }}">{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:160px;">
                            <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Detail</label>
                            <input type="text" id="ipDetail" class="ws-input" placeholder="Enter detail…" style="width:100%;">
                        </div>
                        <button type="button" class="ws-btn ws-btn-primary" onclick="addIpRecord()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add IP
                        </button>
                    </div>

                    <div id="ipRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($ipOutcomes as $io)
                        <div class="ip-record-row" data-id="{{ $io->id }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                            <input type="hidden" name="outcomes[{{ $io->type }}][existing][]" value="{{ $io->id }}">
                            <input type="hidden" name="outcomes[{{ $io->type }}][detail][]" value="{{ $io->identifier }}">
                            <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $ipTypes[$io->type] ?? ucfirst(str_replace('_',' ',$io->type)) }}</span>
                            <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $io->identifier }}</span>
                            <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteOutcomeRecord(this, '{{ $io->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        @endforeach
                        @if($ipOutcomes->count() === 0)
                        <div id="ipEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No IP records added yet.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 3: STUDENTS & PERSONNEL INVOLVEMENT --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-students" class="ws-tab-panel" style="display:none;" role="tabpanel">

        {{-- ── STUDENTS FORM (50% width) + HIRED RESEARCHERS (50% width) ── --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

            {{-- ── STUDENTS FORM ── --}}
            <div class="ws-card" style="padding:16px 18px;">
                <h2 class="ws-section-title">Students</h2>
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:0 0 130px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Level</label>
                        <select id="studentLevel" class="ws-input" style="width:100%;">
                            <option value="UG">Undergraduate</option>
                            <option value="masters">Masters</option>
                            <option value="PhD">PhD</option>
                        </select>
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Student ID</label>
                        <input type="text" id="studentId" class="ws-input" placeholder="Enter student ID…" style="width:100%;">
                    </div>
                    <div style="flex:0 0 80px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Days</label>
                        <input type="number" id="studentDays" class="ws-input" placeholder="0" min="0" max="365" style="width:100%;">
                    </div>
                    <button type="button" class="ws-btn ws-btn-primary" onclick="addStudentRecord()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Student
                    </button>
                </div>

                <div id="studentRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                    @foreach($projectStudents as $s)
                    <div class="student-item-row" data-id="{{ $s->id }}" data-type="{{ $s->type }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                        <input type="hidden" name="students[{{ $s->id }}][existing]" value="{{ $s->id }}">
                        <input type="hidden" name="students[{{ $s->id }}][type]" value="{{ $s->type }}">
                        <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $s->type == 'UG' ? 'UG' : ($s->type == 'masters' ? 'Masters' : 'PhD') }}</span>
                        <span style="flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $s->std_id }}</span>
                        <span style="font-size:11px;color:var(--ink-500);white-space:nowrap;">{{ $s->days }} days</span>
                        <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteStudentRecord(this, '{{ $s->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                    @endforeach
                    @if($projectStudents->count() === 0)
                    <div id="studentEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No students added yet.</div>
                    @endif
                </div>
            </div>

            {{-- ── HIRED RESEARCHERS ── --}}
            <div class="ws-card" data-student-type="hired_researcher" style="padding:16px 18px;">
                <h2 class="ws-section-title">Hired Researchers</h2>
                <div style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap;">
                    <div style="flex:1;min-width:120px;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Name</label>
                        <input type="text" id="researcherName" class="ws-input" placeholder="Researcher name…" style="width:100%;">
                    </div>
                    <div style="flex:0 0 90px;max-width:100%;">
                        <label style="display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--ink-400);margin-bottom:5px;">Category</label>
                        <select id="researcherCategory" class="ws-input" style="width:100%;">
                            <option value="RA">RA</option>
                            <option value="GA">GA</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <button type="button" class="ws-btn ws-btn-primary" onclick="addResearcherRecord()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Researcher
                    </button>
                </div>

                <div id="researcherRecords" style="margin-top:16px;display:flex;flex-direction:column;gap:8px;">
                    @foreach($projectResearchers as $r)
                    <div class="researcher-item-row" data-id="{{ $r->id }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;">
                        <input type="hidden" name="researchers[{{ $r->id }}][existing]" value="{{ $r->id }}">
                        <input type="hidden" name="researchers[{{ $r->id }}][name]" value="{{ $r->name }}">
                        <input type="hidden" name="researchers[{{ $r->id }}][category]" value="{{ $r->category }}">
                        <span class="ws-pill ws-pill-brand" style="flex-shrink:0;">{{ $r->category }}</span>
                        <span style="flex:1;font-size:12px;color:var(--ink-700);word-break:break-all;">{{ $r->name }}</span>
                        <button type="button" class="ws-btn-icon ws-btn-icon-danger" onclick="deleteResearcherRecord(this, '{{ $r->id }}')" title="Delete" style="flex-shrink:0;width:26px;height:26px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                    @endforeach
                    @if($projectResearchers->count() === 0)
                    <div id="researcherEmpty" style="font-size:12px;color:var(--ink-400);text-align:center;padding:10px 0;">No researchers added yet.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── CROSS-COLLEGE PARTICIPATION & RESEARCH AWARDS (toggles) ── --}}
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-top:18px;">
            <div class="ws-card" style="padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;border-bottom:1px solid var(--ink-100);padding-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:var(--brand-600);line-height:1.3;">Cross-College Participation</span>
                    <label class="ws-toggle" style="flex-shrink:0;">
                        <input type="hidden" name="cross_college" value="No">
                        <input type="checkbox" name="cross_college" value="Yes" class="ws-toggle-checkbox" data-details-id="details-cross-college" onchange="saveToggle('cross_college', this.checked ? 'Yes' : 'No')" {{ $crossCollegeValue === 'Yes' ? 'checked' : '' }}>
                        <span class="ws-toggle-slider" style="width:58px;height:26px;">
                            <span class="ws-toggle-label ws-toggle-no" style="font-size:10px;">No</span>
                            <span class="ws-toggle-label ws-toggle-yes" style="font-size:10px;">Yes</span>
                        </span>
                    </label>
                </div>
                <div id="details-cross-college" class="ws-card-details" style="margin-top:0;padding-top:0;border-top:none;{{ $crossCollegeValue === 'Yes' ? '' : 'display:none;' }}">
                    <textarea name="cross_college_detail" class="ws-input ws-textarea" rows="2" placeholder="Describe cross-college participation…" style="font-size:12px;" onblur="saveToggle('cross_college', document.querySelector('input[name=\'cross_college\'][value=\'Yes\']').checked ? 'Yes' : 'No')">{{ $crossCollegeDetails }}</textarea>
                </div>
            </div>

            <div class="ws-card" style="padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;border-bottom:1px solid var(--ink-100);padding-bottom:8px;">
                    <span style="font-size:12px;font-weight:600;color:var(--brand-600);line-height:1.3;">Research Awards</span>
                    <label class="ws-toggle" style="flex-shrink:0;">
                        <input type="hidden" name="research_awards" value="No">
                        <input type="checkbox" name="research_awards" value="Yes" class="ws-toggle-checkbox" data-details-id="details-research-awards" onchange="saveToggle('research_awards', this.checked ? 'Yes' : 'No')" {{ $researchAwardsValue === 'Yes' ? 'checked' : '' }}>
                        <span class="ws-toggle-slider" style="width:58px;height:26px;">
                            <span class="ws-toggle-label ws-toggle-no" style="font-size:10px;">No</span>
                            <span class="ws-toggle-label ws-toggle-yes" style="font-size:10px;">Yes</span>
                        </span>
                    </label>
                </div>
                <div id="details-research-awards" class="ws-card-details" style="margin-top:0;padding-top:0;border-top:none;{{ $researchAwardsValue === 'Yes' ? '' : 'display:none;' }}">
                    <textarea name="research_awards_detail" class="ws-input ws-textarea" rows="2" placeholder="Describe research awards…" style="font-size:12px;" onblur="saveToggle('research_awards', document.querySelector('input[name=\'research_awards\'][value=\'Yes\']').checked ? 'Yes' : 'No')">{{ $researchAwardsDetails }}</textarea>
                </div>
            </div>
        </div>

        </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 4: REPORT SUBMISSIONS --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-submissions" class="ws-tab-panel" style="display:none;" role="tabpanel">
        <div class="ws-card">
            <div class="ws-card-header">
                <span class="ws-card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Report Submissions
                </span>
            </div>
            <div class="ws-card-body">
                <p style="font-size:13px;color:var(--ink-500);margin:0 0 16px;">
                    Upload progress reports as PDF files. Deadlines shown are from your program cycle.
                </p>

                @php
                    $reportTypes = [
                        'progress' => ['label' => 'Progress Report', 'color' => 'var(--brand-50)', 'icon' => 'var(--brand-600)'],
                        'final' => ['label' => 'Final Report', 'color' => 'var(--success)', 'icon' => '#fff'],
                        'readiness' => ['label' => 'Readiness Report', 'color' => 'var(--gold-400)', 'icon' => '#fff'],
                    ];
                @endphp

                <div id="ws-upload-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                    @foreach($reportTypes as $rType => $rInfo)
@php
    $isEdit = false;
    $rLatest = null;
    if ($rType === 'progress') $rLatest = $progressSub ?? null;
    elseif ($rType === 'final') $rLatest = $finalSub ?? null;
    elseif ($rType === 'readiness') $rLatest = $readinessSub ?? null;
    $dl = $deadlines[$rType] ?? [];
    $effectiveDeadline = isset($dl['extended']) ? $dl['extended'] : ($dl['original'] ?? null);
    $deadlinePassed = $effectiveDeadline ? now()->greaterThan($effectiveDeadline) : false;
@endphp
                    <div class="ws-upload-card {{ $deadlinePassed ? 'ws-upload-card--deadline-passed' : '' }}" data-type="{{ $rType }}" data-deadline-passed="{{ $deadlinePassed ? '1' : '0' }}">
                        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;">
                            <div class="ws-upload-card-icon" style="background:{{ $rInfo['color'] }};color:{{ $rInfo['icon'] }};">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <div style="flex:1;">
                                <strong style="font-size:13px;">{{ $rInfo['label'] }}</strong>
                                @if($effectiveDeadline)
                                    <div style="font-size:11px;color:{{ $deadlinePassed ? 'var(--danger)' : 'var(--ink-400)' }};display:flex;align-items:center;gap:6px;">
                                        @if($deadlinePassed)
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        @endif
                                        Deadline: {{ $effectiveDeadline->format('M d, Y') }}
                                        @if($deadlinePassed)
                                            <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--danger);">Passed</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Current file (single, replaceable) --}}
                        <div class="ws-current-file" data-type="{{ $rType }}" style="border:1px solid var(--ink-100);border-radius:6px;padding:6px 8px;margin-bottom:8px;min-height:30px;background:var(--sand-50);">
                            @if($rLatest)
                                <div class="ws-file-row" data-id="{{ $rLatest->id }}" style="display:flex;align-items:center;gap:8px;padding:4px 0;">
                                    <a href="{{ route('serveFile2', ['type' => $rType, 'id' => $project->id]) }}" target="_blank" style="color:var(--brand-500);font-size:12px;font-weight:500;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                                        {{ $rLatest->stored_filename }}
                                    </a>
                                    <span style="font-size:10px;color:var(--ink-400);">{{ $rLatest->created_at->format('M d, Y H:i') }}</span>
                                    <button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="{{ $rLatest->id }}" style="width:20px;height:20px;margin-left:auto;" title="Delete file">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            @else
                                <p style="font-size:12px;color:var(--ink-400);margin:8px 0;text-align:center;">No file uploaded yet.</p>
                            @endif
                        </div>

                        @if($deadlinePassed)
                            <div style="text-align:center;padding:10px 0;border-top:1px solid var(--ink-100);margin-top:4px;">
                                <span style="font-size:11px;color:var(--danger);font-weight:500;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    Deadline passed — upload closed
                                </span>
                            </div>
                        @else
                            <input type="file" class="ws-file-input" accept=".pdf">
                            <button type="button" class="ws-btn ws-btn-outline ws-btn-sm ws-upload-btn" style="cursor:pointer;width:100%;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                {{ $rLatest ? 'Re-Upload' : 'Upload' }}
                            </button>
                            <div class="ws-upload-status" style="margin-top:6px;font-size:11px;color:var(--ink-400);"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Submission notes -- REMOVED --}}

        {{-- Tab 4 Save Button -- REMOVED (moved to Review & Submit tab) --}}
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB 5: REVIEW & SUBMIT --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-review" class="ws-tab-panel" style="display:none;" role="tabpanel">
        <div style="display:flex;justify-content:center;align-items:center;min-height:50vh;">
            <div class="ws-card" style="text-align:center;padding:48px 40px;max-width:480px;width:100%;">
                <div style="width:56px;height:56px;border-radius:12px;background:var(--brand-50);color:var(--brand-500);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <h2 style="font-size:20px;font-weight:600;color:var(--ink-800);margin:0 0 8px;">Ready to Submit?</h2>
                <p style="font-size:14px;color:var(--ink-500);margin:0 0 24px;line-height:1.5;">
                    Please review the previous tabs (Outcomes, Students & Personnel, and Report Submissions) before submitting your final progress report.
                </p>
                <div style="display:flex;flex-direction:column;gap:12px;align-items:center;">
                    <button type="submit" class="ws-btn ws-btn-primary" id="submitFullForm" style="min-width:240px;padding:12px 24px;font-size:14px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        Submit Full Progress Report
                    </button>
                    <span style="font-size:12px;color:var(--ink-400);">All data will be saved and the report will be finalized.</span>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('scripts')
<style>
/* ═══════════════════════════════════════════════════════════════════════════
   Fluent Design System — Workspace UI
   ═══════════════════════════════════════════════════════════════════════════ */

/* ─── Tab Navigation (Fluent-style) ─── */
.ws-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 1px solid var(--ink-100);
    padding: 0;
    margin-bottom: 24px;
    background: var(--sand-50);
    border-radius: 8px 8px 0 0;
    padding: 4px 4px 0 4px;
}
.ws-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-500);
    background: transparent;
    border: 1px solid transparent;
    border-bottom: none;
    border-radius: 6px 6px 0 0;
    cursor: pointer;
    transition: color .15s, background .15s, border-color .15s;
    font-family: inherit;
    position: relative;
    top: 1px;
    white-space: nowrap;
}
.ws-tab:hover {
    color: var(--brand-500);
    background: rgba(141,27,61,.06);
}
.ws-tab.active {
    color: var(--brand-600);
    background: #fff;
    border-color: var(--ink-100);
    font-weight: 600;
    box-shadow: 0 -1px 3px rgba(22,19,26,.04);
}
.ws-tab svg { flex-shrink: 0; }
.ws-tab-panel { animation: tabFadeIn .15s ease-out; }
@keyframes tabFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Card ─── */
.ws-card {
    background: #fff;
    border: 1px solid var(--ink-100);
    border-radius: 8px;
    padding: 16px 18px;
    box-shadow: var(--fluent-depth-2);
    transition: box-shadow .15s, border-color .15s;
}
.ws-card:hover { box-shadow: var(--fluent-depth-4); border-color: var(--ink-200); }
.ws-section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--brand-700);
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ws-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--ink-100);
}
.ws-card-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-800);
    display: flex;
    align-items: center;
}
.ws-card-body { padding: 0; }
.ws-card-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ws-card-row-label {
    font-size: 13px;
    font-weight: 500;
    color: var(--ink-700);
    flex: 0 0 55%;
}
.ws-card-row-input { flex: 0 0 auto; }
.ws-card-details {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--ink-50);
}

/* ─── Toggle Switch ─── */
.ws-toggle {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.ws-toggle-checkbox { display: none; }
.ws-toggle-slider {
    position: relative;
    width: 68px;
    height: 30px;
    background: var(--ink-200);
    border-radius: 15px;
    transition: background .2s;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
}
.ws-toggle-checkbox:checked + .ws-toggle-slider {
    background: var(--brand-500);
}
.ws-toggle-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    transition: color .2s;
}
.ws-toggle-no { color: #fff; }
.ws-toggle-yes { color: rgba(255,255,255,.6); }
.ws-toggle-checkbox:checked + .ws-toggle-slider .ws-toggle-no { color: rgba(255,255,255,.6); }
.ws-toggle-checkbox:checked + .ws-toggle-slider .ws-toggle-yes { color: #fff; }

/* ─── Inputs ─── */
.ws-input {
    font-family: inherit;
    font-size: 13px;
    padding: 7px 10px;
    border: 1px solid var(--ink-200);
    border-radius: 6px;
    background: #fff;
    color: var(--ink-800);
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    width: 100%;
}
.ws-input:focus {
    border-color: var(--brand-500);
    box-shadow: 0 0 0 2px rgba(141,27,61,.15);
}
.ws-input-sm { padding: 6px 9px; font-size: 12.5px; }
.ws-input-narrow { max-width: 90px; }
.ws-input-date { max-width: 150px; }
.ws-input-suffix {
    font-size: 11px;
    color: var(--ink-400);
    white-space: nowrap;
}
.ws-textarea {
    resize: vertical;
    min-height: 52px;
}
.ws-file-input { display: none; }

/* ─── Buttons ─── */
.ws-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: background .15s, box-shadow .15s, border-color .15s;
    white-space: nowrap;
}
.ws-btn-primary {
    background: var(--brand-500);
    color: #fff;
    border-color: var(--brand-600);
    box-shadow: var(--fluent-depth-2);
}
.ws-btn-primary:hover {
    background: var(--brand-600);
    box-shadow: var(--fluent-depth-4);
}
.ws-btn-outline {
    background: transparent;
    color: var(--ink-600);
    border-color: var(--ink-200);
}
.ws-btn-outline:hover {
    background: var(--ink-50);
    border-color: var(--ink-300);
}
.ws-btn-sm { padding: 5px 12px; font-size: 12px; }
.ws-btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid var(--ink-100);
    border-radius: 6px;
    background: transparent;
    color: var(--ink-500);
    cursor: pointer;
    transition: background .15s, color .15s, border-color .15s;
    flex-shrink: 0;
}
.ws-btn-icon:hover {
    background: var(--brand-50);
    color: var(--brand-500);
    border-color: var(--brand-200);
}
.ws-btn-icon-danger:hover {
    background: #fbeef1;
    color: var(--danger);
    border-color: #f3d2da;
}

/* ─── Badges & Pills ─── */
.ws-badge {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    letter-spacing: .02em;
}
.ws-badge-ink {
    background: var(--ink-50);
    color: var(--ink-500);
    border: 1px solid var(--ink-100);
}
.ws-badge-brand {
    background: var(--brand-50);
    color: var(--brand-600);
    border: 1px solid var(--brand-200);
}
.ws-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    border: 1px solid transparent;
}
.ws-pill-brand {
    background: var(--brand-50);
    color: var(--brand-600);
    border-color: var(--brand-200);
}
.ws-pill-success {
    background: #e6f4ea;
    color: var(--success);
    border-color: #a8e6b8;
}
.ws-pill-warning {
    background: #fef7e0;
    color: var(--warning);
    border-color: #fce8b2;
}
.ws-pill-ink {
    background: var(--ink-50);
    color: var(--ink-500);
    border-color: var(--ink-100);
}

/* ─── Table ─── */
.ws-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.ws-table th {
    text-align: left;
    padding: 9px 12px;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ink-400);
    border-bottom: 1px solid var(--ink-100);
    background: var(--sand-50);
}
.ws-table td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--ink-100);
    color: var(--ink-700);
}
.ws-table tbody tr:hover { background: var(--ink-50); }

/* ─── Outcome / Student dynamic rows ─── */
.outcome-item-row,
.student-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 6px 8px;
    background: var(--ink-50);
    border-radius: 6px;
    border: 1px solid var(--ink-100);
}
.outcome-input-group,
.student-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
}
.outcome-input-group .ws-input { flex: 1; }

/* ─── Upload Card ─── */
.ws-upload-card {
    border: 1px solid var(--ink-100);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #fff;
    transition: box-shadow .15s;
}
.ws-upload-card:hover { box-shadow: var(--fluent-depth-4); }
.ws-upload-card--deadline-passed {
    background: var(--ink-50);
    opacity: 0.85;
    border-color: var(--ink-200);
    pointer-events: none;
}
.ws-upload-card--deadline-passed .ws-upload-card-icon {
    filter: grayscale(0.6);
    opacity: 0.7;
}
.ws-upload-card--deadline-passed .ws-current-file {
    pointer-events: auto;
}
.ws-upload-card--deadline-passed .ws-current-file a {
    pointer-events: auto;
}
.ws-upload-card--deadline-passed .ws-delete-file {
    pointer-events: auto;
}
.ws-upload-card--deadline-passed:hover {
    box-shadow: var(--fluent-depth-2);
    border-color: var(--ink-200);
}
.ws-upload-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
    .ws-upload-card-file {
        padding: 6px 8px;
        background: var(--sand-50);
        border-radius: 6px;
        font-size: 12px;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ═══════════════════════════════════════════════════════════════════════
    // TAB SWITCHING
    // ═══════════════════════════════════════════════════════════════════════
    window.switchTab = function(tabId, btn) {
        document.querySelectorAll('.ws-tab-panel').forEach(function(p) {
            p.style.display = 'none';
        });
        document.querySelectorAll('.ws-tab').forEach(function(t) {
            t.classList.remove('active');
        });
        btn.classList.add('active');
        var panel = document.getElementById(tabId);
        if (panel) panel.style.display = 'block';
    };

    // Ensure first tab is active on load
    var firstTab = document.querySelector('.ws-tab.active');
    if (firstTab) firstTab.click();

    // ═══════════════════════════════════════════════════════════════════════
    // TOGGLE: Show/hide detail textareas on Yes/No toggle
    // ═══════════════════════════════════════════════════════════════════════
    document.querySelectorAll('.ws-toggle-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var detailsId = this.getAttribute('data-details-id');
            if (detailsId) {
                var details = document.getElementById(detailsId);
                if (details) {
                    details.style.display = this.checked ? 'block' : 'none';
                }
            }
        });
    });

    // ═══════════════════════════════════════════════════════════════════════
    // SCHOLARLY ARTICLES — Add a record from the type dropdown + DOI input
    // ═══════════════════════════════════════════════════════════════════════
    var scholarlyTypeLabels = {
        'journal_q1':    'Journal articles (Web of Science — Q1)',
        'journal_q2':    'Journal articles (Web of Science — Q2)',
        'journal_q3':    'Journal articles (Web of Science — Q3)',
        'journal_q4':    'Journal articles (Web of Science — Q4)',
        'conference':    'Indexed international conferences',
        'book':          'Published Books',
        'edited_book':   'Edited Books (collection)',
        'book_chapter':  'Book Chapters'
    };

    window.addScholarlyRecord = function() {
        var typeSel = document.getElementById('scholarlyType');
        var idInput = document.getElementById('scholarlyIdentifier');
        if (!typeSel || !idInput) return;
        var type = typeSel.value;
        var identifier = idInput.value.trim();
        if (!identifier) {
            showToast('Please enter a DOI / identifier.', 'error');
            idInput.focus();
            return;
        }
        var list = document.getElementById('scholarlyRecords');
        if (!list) return;

        // Optimistically add the row, then save to DB
        var empty = document.getElementById('scholarlyEmpty');
        if (empty) empty.remove();

        var row = buildOutcomeRow('scholarly-record-row', type, identifier, scholarlyTypeLabels[type] || type);
        list.appendChild(row);
        idInput.value = '';
        idInput.focus();

        saveSingleOutcome(type, identifier, row);
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 3: STUDENT ITEMS — Add / Remove (record-by-record save)
    // ═══════════════════════════════════════════════════════════════════════
    var studentLevelLabels = { 'UG': 'UG', 'masters': 'Masters', 'PhD': 'PhD' };

    window.addStudentRecord = function() {
        var levelSel = document.getElementById('studentLevel');
        var idInput = document.getElementById('studentId');
        var daysInput = document.getElementById('studentDays');
        if (!levelSel || !idInput) return;
        var type = levelSel.value;
        var stdId = idInput.value.trim();
        var days = daysInput ? (parseInt(daysInput.value) || 0) : 0;
        if (!stdId) {
            showToast('Please enter a student ID.', 'error');
            idInput.focus();
            return;
        }
        var list = document.getElementById('studentRecords');
        if (!list) return;

        var empty = document.getElementById('studentEmpty');
        if (empty) empty.remove();

        // Build the record row
        var row = document.createElement('div');
        row.className = 'student-item-row';
        row.setAttribute('data-type', type);
        row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        var pill = document.createElement('span');
        pill.className = 'ws-pill ws-pill-brand';
        pill.style.flexShrink = '0';
        pill.textContent = studentLevelLabels[type] || type;
        row.appendChild(pill);

        var text = document.createElement('span');
        text.style.cssText = 'flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;';
        text.textContent = stdId;
        row.appendChild(text);

        var daysSpan = document.createElement('span');
        daysSpan.style.cssText = 'font-size:11px;color:var(--ink-500);white-space:nowrap;';
        daysSpan.textContent = days + ' days';
        row.appendChild(daysSpan);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:26px;height:26px;';
        delBtn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.onclick = function() { row.remove(); };
        row.appendChild(delBtn);

        list.appendChild(row);

        // Reset inputs
        idInput.value = '';
        if (daysInput) daysInput.value = '';
        idInput.focus();

        // Save to DB
        saveSingleStudent(type, stdId, days, row);
    };

    window.addResearcherRecord = function() {
        var nameInput = document.getElementById('researcherName');
        var catSel = document.getElementById('researcherCategory');
        if (!nameInput) return;
        var name = nameInput.value.trim();
        var category = catSel ? catSel.value : 'RA';
        if (!name) {
            showToast('Please enter a researcher name.', 'error');
            nameInput.focus();
            return;
        }
        var list = document.getElementById('researcherRecords');
        if (!list) return;

        var empty = document.getElementById('researcherEmpty');
        if (empty) empty.remove();

        var row = document.createElement('div');
        row.className = 'researcher-item-row';
        row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        var pill = document.createElement('span');
        pill.className = 'ws-pill ws-pill-brand';
        pill.style.flexShrink = '0';
        pill.textContent = category;
        row.appendChild(pill);

        var text = document.createElement('span');
        text.style.cssText = 'flex:1;font-size:12px;color:var(--ink-700);word-break:break-all;';
        text.textContent = name;
        row.appendChild(text);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:26px;height:26px;';
        delBtn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.onclick = function() { row.remove(); };
        row.appendChild(delBtn);

        list.appendChild(row);

        nameInput.value = '';
        nameInput.focus();

        saveSingleResearcher(name, category, row);
    };

    // ═══════════════════════════════════════════════════════════════════════
    // INTELLECTUAL PROPERTY — Add a record from the type dropdown + detail input
    // ═══════════════════════════════════════════════════════════════════════
    var ipTypeLabels = {
        'ip_disclosure':      'Intellectual Property Disclosure',
        'provisional_patent': 'Provisional Patent',
        'patent_granted':     'Patents Granted',
        'open_source_sw':     'Open Source Software',
        'startup':            'Start-Up Created'
    };

    window.addIpRecord = function() {
        var typeSel = document.getElementById('ipType');
        var detailInput = document.getElementById('ipDetail');
        if (!typeSel || !detailInput) return;
        var type = typeSel.value;
        var detail = detailInput.value.trim();
        if (!detail) {
            showToast('Please enter IP detail.', 'error');
            detailInput.focus();
            return;
        }
        var list = document.getElementById('ipRecords');
        if (!list) return;

        var empty = document.getElementById('ipEmpty');
        if (empty) empty.remove();

        var row = buildOutcomeRow('ip-record-row', type, detail, ipTypeLabels[type] || type);
        list.appendChild(row);
        detailInput.value = '';
        detailInput.focus();

        saveSingleOutcome(type, detail, row);
    };

    // Build a record row (used by both scholarly + IP add functions)
    function buildOutcomeRow(rowClass, type, detail, label) {
        var row = document.createElement('div');
        row.className = rowClass;
        row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--ink-50);border:1px solid var(--ink-100);border-radius:6px;';

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'outcomes[' + type + '][detail][]';
        hidden.value = detail;
        row.appendChild(hidden);

        var pill = document.createElement('span');
        pill.className = 'ws-pill ws-pill-brand';
        pill.style.flexShrink = '0';
        pill.textContent = label;
        row.appendChild(pill);

        var text = document.createElement('span');
        text.style.cssText = 'flex:1;font-family:monospace;font-size:12px;color:var(--ink-700);word-break:break-all;';
        text.textContent = detail;
        row.appendChild(text);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'ws-btn-icon ws-btn-icon-danger';
        delBtn.title = 'Delete';
        delBtn.style.cssText = 'flex-shrink:0;width:26px;height:26px;';
        delBtn.innerHTML = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        delBtn.addEventListener('click', function() { row.remove(); });
        row.appendChild(delBtn);

        return row;
    }

    // Save a single outcome record to the DB via AJAX
    function saveSingleOutcome(type, detail, row) {
        fetch('{{ route("progress.save-single-outcome", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, detail: detail })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                // Tag the row with the DB id so delete can target it
                row.setAttribute('data-id', data.id);
                // Replace the local delete handler with one that hits the DB
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) {
                    delBtn.onclick = function() { deleteOutcomeRecord(delBtn, data.id); };
                }
                showToast('Record saved.', 'success');
            } else {
                showToast(data.error || 'Failed to save record.', 'error');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('Network error: ' + err.message, 'error');
            row.remove();
        });
    }

    // Delete an outcome record (both pre-existing and just-saved) from the DB
    window.deleteOutcomeRecord = function(btn, id) {
        if (!id) { btn.closest('.scholarly-record-row, .ip-record-row')?.remove(); return; }
        if (!confirm('Delete this record?')) return;
        var row = btn.closest('.scholarly-record-row, .ip-record-row');
        fetch('{{ route("progress.delete-outcome", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (row) row.remove();
                showToast('Record deleted.', 'success');
            } else {
                showToast(data.error || 'Failed to delete.', 'error');
            }
        })
        .catch(function(err) {
            showToast('Network error: ' + err.message, 'error');
        });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // SAVE OUTCOMES (AJAX) — kept for backward compatibility (bulk save)
    // ═══════════════════════════════════════════════════════════════════════
    window.saveOutcomes = function() {
        var outcomes = [];
        function collectFromList(selector) {
            document.querySelectorAll(selector).forEach(function(row) {
                var detailInput = row.querySelector('input[type="hidden"][name$="[detail][]"]');
                if (!detailInput) return;
                var detail = detailInput.value.trim();
                if (!detail) return;
                var m = detailInput.name.match(/outcomes\[([^\]]+)\]\[detail\]\[\]/);
                var type = m ? m[1] : null;
                if (!type) return;
                outcomes.push({ type: type, detail: detail });
            });
        }
        collectFromList('#scholarlyRecords .scholarly-record-row');
        collectFromList('#ipRecords .ip-record-row');

        if (outcomes.length === 0) {
            showToast('No records to save.', 'error');
            return;
        }

        fetch('{{ route("progress.save-outcomes", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ outcomes: outcomes })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Outcomes saved successfully.', 'success');
                setTimeout(function() { location.reload(); }, 1200);
            } else {
                showToast(data.error || 'Failed to save outcomes.', 'error');
            }
        })
        .catch(function(err) {
            showToast('Network error: ' + err.message, 'error');
        });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 3: SAVE SINGLE STUDENT / RESEARCHER / TOGGLE (AJAX, record-by-record)
    // ═══════════════════════════════════════════════════════════════════════
    function saveSingleStudent(type, stdId, days, row) {
        fetch('{{ route("progress.save-single-student", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, std_id: stdId, days: days })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                row.setAttribute('data-id', data.id);
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) delBtn.onclick = function() { deleteStudentRecord(delBtn, data.id); };
                showToast('Student saved.', 'success');
            } else {
                showToast(data.error || 'Failed to save student.', 'error');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('Network error: ' + err.message, 'error');
            row.remove();
        });
    }

    function saveSingleResearcher(name, category, row) {
        fetch('{{ route("progress.save-single-researcher", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, category: category })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                row.setAttribute('data-id', data.id);
                var delBtn = row.querySelector('.ws-btn-icon-danger');
                if (delBtn) delBtn.onclick = function() { deleteResearcherRecord(delBtn, data.id); };
                showToast('Researcher saved.', 'success');
            } else {
                showToast(data.error || 'Failed to save researcher.', 'error');
                row.remove();
            }
        })
        .catch(function(err) {
            showToast('Network error: ' + err.message, 'error');
            row.remove();
        });
    }

    window.deleteStudentRecord = function(btn, id) {
        if (!id) { btn.closest('.student-item-row')?.remove(); return; }
        if (!confirm('Delete this student?')) return;
        var row = btn.closest('.student-item-row');
        fetch('{{ route("progress.delete-student", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { if (row) row.remove(); showToast('Student deleted.', 'success'); }
            else showToast(data.error || 'Failed to delete.', 'error');
        })
        .catch(function(err) { showToast('Network error: ' + err.message, 'error'); });
    };

    window.deleteResearcherRecord = function(btn, id) {
        if (!id) { btn.closest('.researcher-item-row')?.remove(); return; }
        if (!confirm('Delete this researcher?')) return;
        var row = btn.closest('.researcher-item-row');
        fetch('{{ route("progress.delete-researcher", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) { if (row) row.remove(); showToast('Researcher deleted.', 'success'); }
            else showToast(data.error || 'Failed to delete.', 'error');
        })
        .catch(function(err) { showToast('Network error: ' + err.message, 'error'); });
    };

    window.saveToggle = function(type, value) {
        var detail = '';
        if (value === 'Yes') {
            var ta = document.querySelector('textarea[name="' + type + '_detail"]');
            if (ta) detail = ta.value;
        }
        fetch('{{ route("progress.save-toggle", $project->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type, value: value, detail: detail })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) showToast('Saved.', 'success');
            else showToast(data.error || 'Failed to save.', 'error');
        })
        .catch(function(err) { showToast('Network error: ' + err.message, 'error'); });
    };

    // ═══════════════════════════════════════════════════════════════════════
    // TOAST NOTIFICATION
    // ═══════════════════════════════════════════════════════════════════════
    function showToast(message, type) {
        var existing = document.querySelector('.ws-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'ws-toast ws-toast-' + (type || 'info');
        toast.innerHTML = message;
        document.body.appendChild(toast);

        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 4: REPORT SUBMISSION — AJAX upload on file selection (single file, replace)
    // ═══════════════════════════════════════════════════════════════════════
    var projectId = {{ $project->id }};
    var csrfToken = document.querySelector('input[name="_token"]')?.value || '';

    // Build the "current file" row HTML for a freshly uploaded submission
    function buildFileRow(type, sub) {
        var downloadUrl = '{{ route("serveFile2", ["type" => "PLACEHOLDER", "id" => $project->id]) }}'.replace('PLACEHOLDER', type);
        var row = document.createElement('div');
        row.className = 'ws-file-row';
        row.setAttribute('data-id', sub.id);
        row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:4px 0;';
        row.innerHTML =
            '<a href="' + downloadUrl + '" target="_blank" style="color:var(--brand-500);font-size:12px;font-weight:500;">' +
            '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>' +
            sub.stored_filename + '</a>' +
            '<span style="font-size:10px;color:var(--ink-400);">' + sub.created_at + '</span>' +
            '<button type="button" class="ws-btn-icon ws-btn-icon-danger ws-delete-file" data-id="' + sub.id + '" style="width:20px;height:20px;margin-left:auto;" title="Delete file">' +
            '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>';
        return row;
    }

    // Attach delete handlers for the current file
    function attachDeleteHandlers() {
        document.querySelectorAll('.ws-delete-file').forEach(function(btn) {
            btn.removeEventListener('click', handleDeleteFile);
            btn.addEventListener('click', handleDeleteFile);
        });
    }

    function handleDeleteFile(e) {
        var btn = e.currentTarget;
        var submissionId = btn.getAttribute('data-id');
        var card = btn.closest('.ws-upload-card');
        var type = card ? card.getAttribute('data-type') : null;

        if (!confirm('Delete this file? This action cannot be undone.')) {
            return;
        }

        var formData = new FormData();
        formData.append('submission_id', submissionId);
        formData.append('_token', csrfToken);

        fetch('{{ route("progress.delete-submission", $project->id) }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r) {
            if (!r.ok) return r.json().then(function(err) { throw new Error(err.error || 'Delete failed'); });
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                var fileBox = card ? card.querySelector('.ws-current-file') : null;
                if (fileBox) {
                    fileBox.innerHTML = '<p style="font-size:12px;color:var(--ink-400);margin:8px 0;text-align:center;">No file uploaded yet.</p>';
                }
                if (card) {
                    var uploadBtn = card.querySelector('.ws-upload-btn');
                    if (uploadBtn) {
                        uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Upload';
                    }
                }
                showToast(data.message || 'File deleted successfully.', 'success');
            } else {
                throw new Error(data.error || 'Delete failed');
            }
        })
        .catch(function(err) {
            showToast(err.message, 'error');
        });
    }

    // Upload handler
    var uploadGrid = document.getElementById('ws-upload-grid');
    if (uploadGrid) {
        uploadGrid.querySelectorAll('.ws-upload-card').forEach(function(card) {
            var fileInput = card.querySelector('.ws-file-input');
            var uploadBtn = card.querySelector('.ws-upload-btn');
            var statusDiv = card.querySelector('.ws-upload-status');
            var fileBox = card.querySelector('.ws-current-file');
            var type = card.getAttribute('data-type');

            if (uploadBtn && fileInput) {
                uploadBtn.addEventListener('click', function(e) {
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    var file = this.files[0];
                    if (!file) return;

                    if (file.type !== 'application/pdf') {
                        showToast('Only PDF files are allowed.', 'error');
                        this.value = '';
                        return;
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        showToast('File must be under 10MB.', 'error');
                        this.value = '';
                        return;
                    }

                    // If a file already exists, confirm replacement
                    var existingRow = fileBox ? fileBox.querySelector('.ws-file-row') : null;
                    if (existingRow) {
                        if (!confirm('A file already exists for this report. Re-uploading will replace it. Continue?')) {
                            this.value = '';
                            return;
                        }
                    }

                    if (uploadBtn) {
                        uploadBtn.disabled = true;
                        uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:4px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Uploading…';
                    }
                    if (statusDiv) { statusDiv.textContent = 'Uploading…'; statusDiv.style.color = 'var(--ink-400)'; }

                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('type', type);
                    formData.append('_token', csrfToken);

                    fetch('{{ route("progress.upload-submission", $project->id) }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: formData
                    })
                    .then(function(r) {
                        if (!r.ok) return r.json().then(function(e) { throw new Error(e.error || 'Upload failed'); });
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // Replace the current-file box with the new file row
                            if (fileBox) {
                                fileBox.innerHTML = '';
                                fileBox.appendChild(buildFileRow(type, data.submission));
                            }
                            if (statusDiv) { statusDiv.textContent = 'Uploaded successfully!'; statusDiv.style.color = 'var(--success)'; }
                            if (uploadBtn) {
                                uploadBtn.disabled = false;
                                uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> Re-Upload';
                            }
                            attachDeleteHandlers();
                            showToast(data.message || 'Report uploaded successfully.', 'success');
                        } else {
                            throw new Error(data.error || 'Upload failed');
                        }
                    })
                    .catch(function(err) {
                        showToast(err.message, 'error');
                        if (statusDiv) { statusDiv.textContent = 'Upload failed. Please try again.'; statusDiv.style.color = 'var(--danger)'; }
                        if (uploadBtn) {
                            uploadBtn.disabled = false;
                            uploadBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> ' + (existingRow ? 'Re-Upload' : 'Upload');
                        }
                    });
                });
            }
        });
    }

    // Initialize delete handlers on page load
    attachDeleteHandlers();

    // ═══════════════════════════════════════════════════════════════════════
    // TAB 5: FULL FORM SUBMIT — Prevent double submission
    // ═══════════════════════════════════════════════════════════════════════
    var mainForm = document.getElementById('mainProgressForm');
    var submitBtn = document.getElementById('submitFullForm');
    if (mainForm && submitBtn) {
        mainForm.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-spin" style="margin-right:8px;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="32"/></svg> Submitting…';
        });
    }
});
</script>
<style>
/* Toast notification */
.ws-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    box-shadow: var(--fluent-depth-8);
    transition: opacity .3s, transform .3s;
    animation: toastSlideIn .3s ease-out;
}
@keyframes toastSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.ws-toast-success { background: var(--success); }
.ws-toast-error { background: var(--danger); }
.ws-toast-info { background: var(--info); }
.animate-spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endpush
