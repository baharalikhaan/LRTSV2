@php
    $fg = $finalGrades ?? (isset($grading) ? $grading : null);
@endphp

<div class="ws-card">
    <div class="ws-section-title">
        <span><i class="fas fa-star"></i> Final Report Grade</span>
        <button type="button" class="ws-help" onclick="openHelp('helpFinal')">
            <i class="fas fa-question-circle"></i> Grading Help
        </button>
    </div>

    @if(!$fg || $fg->publish === 'pending')
    <p style="color:var(--ink-500);font-size:12px;margin:0 0 14px;line-height:1.5;">
        Kindly evaluate the final report based on the following criteria on a scale of 1 to 5, where 1 indicates the highest level of dissatisfaction and 5 indicates the highest level of satisfaction.
    </p>
    @endif

    @php
        $contributions = $project->contributions()->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
    @endphp

    @if($fg && $fg->publish !== 'pending')
        {{-- Already graded — show submitted values + grade status --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding:8px 12px;background:{{ $fg->isAccepted == 1 ? '#e8f5ee' : '#fbeef1' }};border:1px solid {{ $fg->isAccepted == 1 ? '#a8e6b8' : '#f5c6cb' }};border-radius:8px;">
            <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Status</span>
            @if($fg->isAccepted == 1)
                <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:500;font-size:13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Accepted
                </span>
            @else
                <span style="display:inline-flex;align-items:center;gap:4px;color:var(--danger);font-weight:500;font-size:13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Rejected
                </span>
            @endif
        </div>

        @php
            $sections = [
                'A' => ['label' => 'Achievements against objectives', 'grade' => $fg->gradeA ?? null, 'comment' => $fg->commentA ?? null],
                'B' => ['label' => 'Publications & IP', 'grade' => $fg->gradeB ?? null, 'comment' => $fg->commentB ?? null],
                'C' => ['label' => 'Student & Young Researcher Involvement', 'grade' => $fg->gradeC ?? null, 'comment' => $fg->commentC ?? null],
                'D' => ['label' => 'Project Impact', 'grade' => $fg->gradeD ?? null, 'comment' => $fg->commentD ?? null],
            ];
        @endphp

        <div class="ws-ro-summary">
            @foreach($sections as $key => $s)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">{{ $s['label'] }}</span>
                    <span class="ws-ro-value">{{ $s['grade'] ?? '—' }}/5</span>
                </div>
                @if($s['comment'])
                    <div class="ws-ro-comment" style="padding:0 0 8px 0;">{{ $s['comment'] }}</div>
                @endif
            @endforeach

            <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--ink-100);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Total</span>
                <span class="ws-ro-value">{{ $fg->total ?? '—' }}</span>
            </div>
        </div>
    @else
        @php $projectId = $project->id ?? ($report->project_id ?? ''); @endphp
        <form method="POST" action="/grading/{{ $projectId }}/save-final-grade" data-final-grade style="margin-top:4px;">
            @csrf
            <input type="hidden" name="report_type" value="final">

            {{-- ===== Section A: Achievements against objectives ===== --}}
            @php
                $pubBookTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
                $achievementOutcomes = $outcomes->reject(fn($o) => in_array($o->type, $pubBookTypes));
                $totalAchievementPoints = 0;
                if ($commitments) {
                    $totalAchievementPoints = ($commitments->ip ?? 0) * ($scoreMap['ip_disclosure'] ?? 0)
                        + ($commitments->filedPatent ?? 0) * ($scoreMap['provisional_patent'] ?? 0)
                        + ($commitments->grantedPatent ?? 0) * ($scoreMap['granted_patent'] ?? 0)
                        + ($commitments->openSourceSW ?? 0) * ($scoreMap['open_source_sw'] ?? 0)
                        + ($commitments->startUp ?? 0) * ($scoreMap['startup'] ?? 0);
                }
            @endphp
            <div class="ws-achievement-section" style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-trophy" style="color:var(--brand-500);margin-right:4px;"></i> A. Achievements against objectives</span>
                    <span class="ws-mini-label">{{ $achievementOutcomes->count() }} item(s)</span>
                </div>

                {{-- Non-publication outcomes with checkboxes --}}
                @if($achievementOutcomes->count())
                    <div style="margin-bottom:12px;">
                        @foreach($achievementOutcomes as $o)
                        @php $isChecked = ($o->verifcation_by_reviewer ?? 'verified') === 'verified'; @endphp
                        <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid var(--ink-100);border-radius:6px;margin-bottom:4px;background:#fff;cursor:pointer;transition:all .15s;" class="ws-check-item" onmouseover="this.style.borderColor='var(--brand-300)'" onmouseout="this.style.borderColor='var(--ink-100)'">
                            <input type="checkbox" name="achievement_outcomes[]" value="{{ $o->id }}" data-type="{{ $o->type }}" data-points="{{ $scoreMap[$o->type] ?? 0 }}" {{ $isChecked ? 'checked' : '' }} style="accent-color:var(--brand-500);width:15px;height:15px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:500;color:var(--ink-700);">{{ $o->identifier ?? $o->type }}</div>
                                <div style="font-size:10px;color:var(--ink-400);">{{ $o->type }} @if($o->online_date) · {{ $o->online_date->format('Y-m-d') }} @endif</div>
                            </div>
                            <span class="ws-pill ws-pill-brand" style="font-size:10px;">{{ $scoreMap[$o->type] ?? 0 }} pts</span>
                        </label>
                        @endforeach
                    </div>
                @endif

                @if(!$achievementOutcomes->count())
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No achievement items recorded.</p>
                @endif

                {{-- Score & Grade summary --}}
                <div class="ws-auto-grade-box" data-value="{{ $autoGradeA ?? 0 }}" data-base-expected="{{ $expectedSumA ?? 0 }}" style="display:none;"></div>
                <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Score (Selected)</div>
                        <div style="font-size:18px;font-weight:700;color:var(--ink-800);" class="ws-achievement-score">0</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of {{ $totalAchievementPoints }} expected</div>
                    </div>
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Auto-Grade</div>
                        <div style="font-size:18px;font-weight:700;color:var(--brand-600);" class="ws-achievement-grade">{{ $autoGradeA ?? 0 }}</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of 5</div>
                    </div>
                </div>

                {{-- Rating control --}}
                <div style="margin-bottom:8px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="gradeA" value="{{ $i }}" {{ old('gradeA', $fg->gradeA ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    @if(isset($autoGradeA) && $autoGradeA !== null)
                    <div class="ws-auto-hint" style="display:none;"></div>
                    @endif
                </div>
                <textarea name="commentA" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentA', $fg->commentA ?? '') }}</textarea>
            </div>

            {{-- ===== Section B: Publications & IP ===== --}}
            @php
                $pubTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
                $publicationOutcomes = $outcomes->filter(fn($o) => in_array($o->type, $pubTypes));
                $totalPubPoints = 0;
                if ($commitments) {
                    $totalPubPoints = ($commitments->q1article ?? 0) * ($scoreMap['journal_q1'] ?? 0)
                        + ($commitments->q2article ?? 0) * ($scoreMap['journal_q2'] ?? 0)
                        + ($commitments->q3article ?? 0) * ($scoreMap['journal_q3'] ?? 0)
                        + ($commitments->q4article ?? 0) * ($scoreMap['journal_q4'] ?? 0)
                        + ($commitments->confArticle ?? 0) * ($scoreMap['conference'] ?? 0)
                        + ($commitments->books ?? 0) * ($scoreMap['book'] ?? 0)
                        + ($commitments->editBooks ?? 0) * ($scoreMap['edited_book'] ?? 0)
                        + ($commitments->chapters ?? 0) * ($scoreMap['book_chapter'] ?? 0);
                }
            @endphp
            <div class="ws-publication-section" style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-book-open" style="color:var(--brand-500);margin-right:4px;"></i> B. Publications & IP</span>
                    <span class="ws-mini-label">{{ $publicationOutcomes->count() }} item(s)</span>
                </div>

                @if($publicationOutcomes->count())
                    <div style="margin-bottom:12px;">
                        @foreach($publicationOutcomes as $o)
                        @php $isChecked = ($o->verifcation_by_reviewer ?? 'verified') === 'verified'; @endphp
                        <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid var(--ink-100);border-radius:6px;margin-bottom:4px;background:#fff;cursor:pointer;transition:all .15s;" class="ws-check-item" onmouseover="this.style.borderColor='var(--brand-300)'" onmouseout="this.style.borderColor='var(--ink-100)'">
                            <input type="checkbox" name="publication_outcomes[]" value="{{ $o->id }}" data-type="{{ $o->type }}" data-points="{{ $scoreMap[$o->type] ?? 0 }}" {{ $isChecked ? 'checked' : '' }} style="accent-color:var(--brand-500);width:15px;height:15px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:500;color:var(--ink-700);">{{ $o->identifier ?? $o->type }}</div>
                                <div style="font-size:10px;color:var(--ink-400);">{{ $o->type }} @if($o->online_date) · {{ $o->online_date->format('Y-m-d') }} @endif</div>
                            </div>
                            @if($o->publication)
                            <button type="button" onclick="event.preventDefault();event.stopPropagation();openDetailModal('pub-modal-{{ $o->id }}')" style="background:var(--brand-50);border:1px solid var(--brand-200);border-radius:4px;padding:2px 6px;cursor:pointer;font-size:10px;color:var(--brand-600);" title="View Details">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Auto-Verified: Publication details available"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            @endif
                            <span class="ws-pill ws-pill-brand" style="font-size:10px;">{{ $scoreMap[$o->type] ?? 0 }} pts</span>
                        </label>
                        @endforeach
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No publications recorded.</p>
                @endif

                {{-- Publication Detail Modals --}}
                @foreach($publicationOutcomes as $o)
                    @if($o->publication)
                    <div class="ws-modal-overlay" id="pub-modal-{{ $o->id }}" style="display:none;">
                        <div class="ws-modal" style="max-width:480px;">
                            <div class="ws-modal-head">
                                <span><i class="fas fa-book-open"></i> Publication Details</span>
                                <button type="button" class="ws-modal-close" onclick="closeDetailModal('pub-modal-{{ $o->id }}')">&times;</button>
                            </div>
                            <div class="ws-modal-body">
                                <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                                    <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);width:100px;">Title</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->publication_title ?? '—' }}</td></tr>
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Authors</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->authors ?? '—' }}</td></tr>
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Journal</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->journal ?? '—' }}</td></tr>
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Year</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->year ?? '—' }}</td></tr>
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">DOI</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->doi ?? '—' }}</td></tr>
                                        <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">URL</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->url ?? '—' }}</td></tr>
                                        <tr><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Status</td><td style="padding:8px 0;color:var(--ink-800);">{{ $o->publication->status ?? '—' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach

                {{-- Score & Grade summary --}}
                <div class="ws-auto-grade-box" data-value="{{ $autoGradeB ?? 0 }}" data-base-expected="{{ $totalPubPoints }}" style="display:none;"></div>
                <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Score (Selected)</div>
                        <div style="font-size:18px;font-weight:700;color:var(--ink-800);" class="ws-pub-score">0</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of {{ $totalPubPoints }} expected</div>
                    </div>
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Auto-Grade</div>
                        <div style="font-size:18px;font-weight:700;color:var(--brand-600);" class="ws-pub-grade">{{ $autoGradeB ?? 0 }}</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of 5</div>
                    </div>
                </div>

                {{-- Rating control --}}
                <div style="margin-bottom:8px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="gradeB" value="{{ $i }}" {{ old('gradeB', $fg->gradeB ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    @if(isset($autoGradeB) && $autoGradeB !== null)
                    <div class="ws-auto-hint" style="display:none;"></div>
                    @endif
                </div>
                <textarea name="commentB" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentB', $fg->commentB ?? '') }}</textarea>
            </div>

            {{-- ===== Section C: Student & Young Researcher Involvement ===== --}}
            @php
                $totalStudentPoints = 0;
                if ($commitments) {
                    $totalStudentPoints = ($commitments->master ?? 0) * ($scoreMap['masters'] ?? 0)
                        + ($commitments->UG ?? 0) * ($scoreMap['ug'] ?? 0)
                        + ($commitments->Phd ?? 0) * ($scoreMap['phd'] ?? 0);
                }
            @endphp
            <div class="ws-student-section" style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-user-graduate" style="color:var(--brand-500);margin-right:4px;"></i> C. Student & Young Researcher Involvement</span>
                    <span class="ws-mini-label">{{ $students->count() + $researchers->count() }} item(s)</span>
                </div>

                @if($students->count())
                    <div style="margin-bottom:12px;">
                        <div style="font-size:11px;font-weight:600;color:var(--ink-600);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;">Students</div>
                        @foreach($students as $s)
                        @php $isChecked = ($s->verifcation_by_reviewer ?? 'verified') === 'verified'; @endphp
                        <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid var(--ink-100);border-radius:6px;margin-bottom:4px;background:#fff;cursor:pointer;transition:all .15s;" class="ws-check-item" onmouseover="this.style.borderColor='var(--brand-300)'" onmouseout="this.style.borderColor='var(--ink-100)'">
                            <input type="checkbox" name="student_items[]" value="{{ $s->id }}" data-type="{{ $s->type }}" data-points="{{ $scoreMap[strtolower($s->type)] ?? 0 }}" {{ $isChecked ? 'checked' : '' }} style="accent-color:var(--brand-500);width:15px;height:15px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:500;color:var(--ink-700);">{{ $s->std_id ?? '—' }}</div>
                                <div style="font-size:10px;color:var(--ink-400);">{{ $s->type }} @if($s->days) · {{ $s->days }} days @endif</div>
                            </div>
                            @if($s->details)
                            <button type="button" onclick="event.preventDefault();event.stopPropagation();openDetailModal('student-modal-{{ $s->id }}')" style="background:var(--brand-50);border:1px solid var(--brand-200);border-radius:4px;padding:2px 6px;cursor:pointer;font-size:10px;color:var(--brand-600);" title="View Details">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" title="Auto-Verified: Student details available"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            @endif
                            <span class="ws-pill ws-pill-brand" style="font-size:10px;">{{ $scoreMap[strtolower($s->type)] ?? 0 }} pts</span>
                        </label>
                        @endforeach
                    </div>

                    {{-- Student Detail Modals --}}
                    @foreach($students as $s)
                        @if($s->details)
                        <div class="ws-modal-overlay" id="student-modal-{{ $s->id }}" style="display:none;">
                            <div class="ws-modal" style="max-width:480px;">
                                <div class="ws-modal-head">
                                    <span><i class="fas fa-user-graduate"></i> Student Details</span>
                                    <button type="button" class="ws-modal-close" onclick="closeDetailModal('student-modal-{{ $s->id }}')">&times;</button>
                                </div>
                                <div class="ws-modal-body">
                                    <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                                        <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);width:120px;">Student ID</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->student_id ?? $s->std_id ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Name</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->full_name ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Status</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->student_status ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Major</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->major ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Minor</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->minor ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">College</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->college ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Program</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->std_program ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Level</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->std_level ?? '—' }}</td></tr>
                                            <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Admission Term</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->admission_term ?? '—' }}</td></tr>
                                            <tr><td style="padding:8px 0;font-weight:600;color:var(--ink-600);">Registration</td><td style="padding:8px 0;color:var(--ink-800);">{{ $s->details->reg_in_course ?? '—' }}</td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif

                @if($researchers->count())
                    <div style="margin-bottom:12px;">
                        <div style="font-size:11px;font-weight:600;color:var(--ink-600);margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em;">Researchers</div>
                        @foreach($researchers as $r)
                        @php $isChecked = ($r->verifcation_by_reviewer ?? 'verified') === 'verified'; @endphp
                        <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border:1px solid var(--ink-100);border-radius:6px;margin-bottom:4px;background:#fff;cursor:pointer;transition:all .15s;" class="ws-check-item" onmouseover="this.style.borderColor='var(--brand-300)'" onmouseout="this.style.borderColor='var(--ink-100)'">
                            <input type="checkbox" name="researcher_items[]" value="{{ $r->id }}" data-type="researcher" data-points="{{ $scoreMap['researcher'] ?? 0 }}" {{ $isChecked ? 'checked' : '' }} style="accent-color:var(--brand-500);width:15px;height:15px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:500;color:var(--ink-700);">{{ $r->name ?? '—' }}</div>
                                <div style="font-size:10px;color:var(--ink-400);">{{ $r->category ?? 'Researcher' }} @if($r->days) · {{ $r->days }} days @endif</div>
                            </div>
                            <span class="ws-pill ws-pill-brand" style="font-size:10px;">{{ $scoreMap['researcher'] ?? 0 }} pts</span>
                        </label>
                        @endforeach
                    </div>
                @endif

                @if(!$students->count() && !$researchers->count())
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No students or researchers recorded.</p>
                @endif

                {{-- Score & Grade summary --}}
                <div class="ws-auto-grade-box" data-value="{{ $autoGradeC ?? 0 }}" data-base-expected="{{ $totalStudentPoints }}" style="display:none;"></div>
                <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Score (Selected)</div>
                        <div style="font-size:18px;font-weight:700;color:var(--ink-800);" class="ws-student-score">0</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of {{ $totalStudentPoints }} expected</div>
                    </div>
                    <div style="flex:1;min-width:140px;background:#fff;border:1px solid var(--ink-100);border-radius:6px;padding:10px 12px;">
                        <div style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;margin-bottom:4px;">Auto-Grade</div>
                        <div style="font-size:18px;font-weight:700;color:var(--brand-600);" class="ws-student-grade">{{ $autoGradeC ?? 0 }}</div>
                        <div style="font-size:10px;color:var(--ink-400);margin-top:2px;">out of 5</div>
                    </div>
                </div>

                {{-- Rating control --}}
                <div style="margin-bottom:8px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="gradeC" value="{{ $i }}" {{ old('gradeC', $fg->gradeC ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    @if(isset($autoGradeC) && $autoGradeC !== null)
                    <div class="ws-auto-hint" style="display:none;"></div>
                    @endif
                </div>
                <textarea name="commentC" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentC', $fg->commentC ?? '') }}</textarea>
            </div>

            {{-- ===== Section D: Project Impact ===== --}}
            <div style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-chart-line" style="color:var(--brand-500);margin-right:4px;"></i> D. Project Impact</span>
                </div>

                <p style="color:var(--ink-500);font-size:11px;margin:0 0 10px;line-height:1.5;font-style:italic;">
                    Evaluate the overall impact of the project outcomes, including potential for commercialization, societal benefit, and contribution to knowledge.
                </p>

                {{-- Rating control --}}
                <div style="margin-bottom:8px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="gradeD" value="{{ $i }}" {{ old('gradeD', $fg->gradeD ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
                <textarea name="commentD" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentD', $fg->commentD ?? '') }}</textarea>
            </div>

            {{-- Section: Recommendation --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:10px;">Recommendation:</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <label style="flex:1;min-width:180px;display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--success)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--success)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="accepted" {{ (old('publish', $fg->publish ?? '') == 'accepted' || old('publish', $fg->publish ?? '') == '') ? 'checked' : '' }} style="accent-color:var(--success);width:18px;height:18px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:13px;">Accept</div>
                            <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">The final report meets expectations and the project is complete.</div>
                        </div>
                    </label>
                    <label style="flex:1;min-width:180px;display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--danger)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--danger)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="rejected" {{ old('publish', $fg->publish ?? '') == 'rejected' ? 'checked' : '' }} style="accent-color:var(--danger);width:18px;height:18px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:13px;">Reject</div>
                            <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">The final report does not meet expectations and requires admin review.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Rejection Reason (required when rejecting) --}}
            <div id="finalRejectionReason" style="margin-bottom:20px;display:none;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:6px;">
                    Rejection Reason <span style="color:var(--danger);">*</span>
                </label>
                <textarea name="rejection_reason" id="finalRejectionReasonInput" rows="3" maxlength="2000" class="ws-input"
                    placeholder="Explain to the admin and LPI why this report is being rejected...">{{ old('rejection_reason', $fg->rejection_reason ?? ($latestRejectionReason ?? '')) }}</textarea>
            </div>

            {{-- Hidden fields for score data --}}
            <input type="hidden" name="scoreA" class="ws-score-a" value="0">
            <input type="hidden" name="autoGradeA" class="ws-auto-grade-a" value="{{ $autoGradeA ?? 0 }}">
            <input type="hidden" name="scoreB" class="ws-score-b" value="0">
            <input type="hidden" name="autoGradeB" class="ws-auto-grade-b" value="{{ $autoGradeB ?? 0 }}">
            <input type="hidden" name="scoreC" class="ws-score-c" value="0">
            <input type="hidden" name="autoGradeC" class="ws-auto-grade-c" value="{{ $autoGradeC ?? 0 }}">

            <div class="ws-form-actions" style="display:flex;justify-content:flex-end;gap:8px;padding-top:10px;border-top:1px solid var(--ink-100);">
                <button type="submit" name="save_action" value="draft" class="ws-btn ws-btn-outline" style="font-size:12px;padding:5px 12px;">
                    <i class="fas fa-save"></i> Save Report as Draft
                </button>
                <button type="submit" name="save_action" value="submit" class="ws-btn ws-btn-primary" style="font-size:12px;padding:5px 12px;">
                    <i class="fas fa-paper-plane"></i> Submit Final Report
                </button>
            </div>
        </form>
    @endif
</div>
