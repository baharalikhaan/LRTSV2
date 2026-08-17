@php
    $d = $draft ?? (isset($grading) ? $grading : null);
    $showAsSummary = $showAsSummary ?? false;
    $reportType = $report_type ?? 'progress';
    $isProgress2 = $reportType === 'progress2';
    $reportTitle = $isProgress2 ? 'Progress Report 2' : 'Progress Report';
@endphp

<div class="ws-card">
    <div class="ws-section-title">
        <span><i class="fas fa-clipboard-check"></i> {{ $reportTitle }}</span>
        @if(!$showAsSummary)
        <button type="button" class="ws-help" onclick="openHelp('helpProgress')">
            <i class="fas fa-question-circle"></i> Grading Help
        </button>
        @endif
    </div>

    @if(!$showAsSummary)
    <p style="color:var(--ink-500);font-size:13px;margin:0 0 16px;line-height:1.5;">
        Kindly evaluate the progress report based on the following criteria on a scale of 1 to 5, where 1 indicates the highest level of dissatisfaction and 5 indicates the highest level of satisfaction.
    </p>
    @endif

    {{-- ═══ READ-ONLY SUMMARY MODE (for final grading right-side tab) ═══ --}}
    @if($showAsSummary)
        @if($d)
            @if($d->publish !== 'pending' && $d->isAccepted !== null)
                {{-- Already graded — show status + ratings --}}
                <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:{{ $d->isAccepted == 1 ? '#e8f5ee' : '#fbeef1' }};border:1px solid {{ $d->isAccepted == 1 ? '#a8e6b8' : '#f5c6cb' }};border-radius:8px;margin-bottom:12px;">
                    @if($d->isAccepted == 1)
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span style="font-size:13px;font-weight:500;color:var(--success);">Accepted</span>
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        <span style="font-size:13px;font-weight:500;color:var(--danger);">Rejected</span>
                    @endif
                </div>

                @php
                    $ratings = [
                        'achievements' => ['label' => 'Progress Toward Achieving Outcomes', 'rating' => $d->achievementsRating ?? null, 'comments' => $d->achievementsComments ?? null],
                        'publications' => ['label' => 'Progress in Publications',           'rating' => $d->publicationsRating ?? null, 'comments' => $d->publicationsComments ?? null],
                        'students'     => ['label' => 'Student Involvement & Capacity Building', 'rating' => $d->studentsRating ?? null, 'comments' => $d->studentsComments ?? null],
                        'budget'       => ['label' => 'Budget Utilization',                 'rating' => $d->budgetRating ?? null,       'comments' => $d->budgetComments ?? null],
                    ];
                @endphp

                <div class="ws-ro-summary">
                    @foreach($ratings as $key => $r)
                        <div class="ws-ro-row">
                            <span class="ws-ro-label">{{ $r['label'] }}</span>
                            <span class="ws-ro-value">{{ $r['rating'] ?? '—' }}/5</span>
                        </div>
                        @if($r['comments'])
                            <div class="ws-ro-comment" style="padding:0 0 8px 0;">{{ $r['comments'] }}</div>
                        @endif
                    @endforeach

                    @if($d->ethical !== null)
                        <div class="ws-ro-row">
                            <span class="ws-ro-label">Ethical Approvals</span>
                            <span class="ws-pill {{ $d->ethical ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $d->ethical ? 'Yes' : 'No' }}</span>
                        </div>
                    @endif

                    @if($d->analysis)
                        <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                            <span class="ws-mini-label">Analysis</span>
                            <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->analysis }}</div>
                        </div>
                    @endif
                    @if($d->comments)
                        <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                            <span class="ws-mini-label">Comments</span>
                            <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->comments }}</div>
                        </div>
                    @endif
                    @if($d->recommendation)
                        <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                            <span class="ws-mini-label">Recommendation</span>
                            <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->recommendation }}</div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Grading exists but is still in draft/pending state --}}
                <div class="ws-ro-summary">
                    <p style="color:var(--ink-400);font-size:12px;margin:0;font-style:italic;">
                        <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                        Progress grading has been started but not yet submitted.
                    </p>
                </div>
            @endif
        @else
            {{-- No grading record exists yet --}}
            <div class="ws-ro-summary">
                <p style="color:var(--ink-400);font-size:12px;margin:0;font-style:italic;">
                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                    No progress grading has been submitted for this project yet.
                </p>
            </div>
        @endif

    {{-- ═══ ALREADY GRADED READ-ONLY (existing logic) ═══ --}}
    @elseif($d && $d->publish !== 'pending' && $d->isAccepted !== null)
        @if($project->hasStatus(\App\Models\Project::STATUS_GRADED))
            <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#e8f5ee;border:1px solid #a8e6b8;border-radius:8px;margin-bottom:12px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span style="font-size:13px;font-weight:500;color:var(--success);">Grade Already Submitted</span>
            </div>
        @endif
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;padding:8px 12px;background:{{ $d->isAccepted == 1 ? '#e8f5ee' : '#fbeef1' }};border:1px solid {{ $d->isAccepted == 1 ? '#a8e6b8' : '#f5c6cb' }};border-radius:8px;">
            <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Status</span>
            @if($d->isAccepted == 1)
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
            $ratings = [
                'achievements' => ['label' => 'Progress Toward Achieving Outcomes', 'rating' => $d->achievementsRating ?? null, 'comments' => $d->achievementsComments ?? null],
                'publications' => ['label' => 'Progress in Publications',           'rating' => $d->publicationsRating ?? null, 'comments' => $d->publicationsComments ?? null],
                'students'     => ['label' => 'Student Involvement & Capacity Building', 'rating' => $d->studentsRating ?? null, 'comments' => $d->studentsComments ?? null],
                'budget'       => ['label' => 'Budget Utilization',                 'rating' => $d->budgetRating ?? null,       'comments' => $d->budgetComments ?? null],
            ];
        @endphp

        <div class="ws-ro-summary">
            @foreach($ratings as $key => $r)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">{{ $r['label'] }}</span>
                    <span class="ws-ro-value">{{ $r['rating'] ?? '—' }}/5</span>
                </div>
                @if($r['comments'])
                    <div class="ws-ro-comment" style="padding:0 0 8px 0;">{{ $r['comments'] }}</div>
                @endif
            @endforeach

            @if($d->ethical !== null)
                <div class="ws-ro-row">
                    <span class="ws-ro-label">Ethical Approvals</span>
                    <span class="ws-pill {{ $d->ethical ? 'ws-pill-success' : 'ws-pill-ink' }}">{{ $d->ethical ? 'Yes' : 'No' }}</span>
                </div>
            @endif

            @if($d->analysis)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Analysis</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->analysis }}</div>
                </div>
            @endif
            @if($d->comments)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Comments</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->comments }}</div>
                </div>
            @endif
            @if($d->recommendation)
                <div style="padding:8px 0 0;border-top:1px solid var(--ink-100);">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--ink-400);font-weight:600;">Recommendation</span>
                    <div style="color:var(--ink-600);font-size:12px;margin-top:3px;">{{ $d->recommendation }}</div>
                </div>
            @endif
        </div>
    @else
        @php $projectId = $project->id ?? ($report->project_id ?? ''); @endphp>
        <form method="POST" action="/grading/{{ $projectId }}/save-progress-grade" data-progress-grade style="margin-top:4px;">
            @csrf
            <input type="hidden" name="report_type" value="{{ $reportType }}">

            {{-- Section 1: Progress Toward Achieving Outcomes --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:6px;">1. Progress Toward Achieving Outcomes:</label>

                <div style="margin-bottom:6px;font-size:11.5px;color:var(--ink-500);line-height:1.6;font-style:italic;padding-left:14px;">
                    <div>a. Degree of progress made towards realizing the proposed outcomes in the project.</div>
                    <div>b. Does the project demonstrate advancement towards producing a prototype, patent, or open-source software?</div>
                </div>

                <div style="margin-bottom:12px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="achievementsRating" value="{{ $i }}" {{ old('achievementsRating', $d->achievementsRating ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div style="position:relative;">
                    <textarea name="achievementsComments" rows="2" class="ws-input" style="font-size:12px;padding-right:50px;" maxlength="500" placeholder="progress report 1" oninput="updateCharCount(this)">{{ old('achievementsComments', $d->achievementsComments ?? '') }}</textarea>
                    <span class="ws-char-count">0/500</span>
                </div>
            </div>

            {{-- Section 2: Progress in Publications --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:10px;">2. Progress in Publications:</label>

                <div style="margin-bottom:12px;">
                    <label class="ws-field-label" style="font-size:11px;font-style:italic;color:var(--ink-500);padding-left:14px;">a. Progress in generating publications in high-ranked journals since the start of the project. <span style="color:var(--danger);">*</span></label>
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="publicationsRating" value="{{ $i }}" {{ old('publicationsRating', $d->publicationsRating ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div style="position:relative;">
                    <textarea name="publicationsComments" rows="2" class="ws-input" style="font-size:12px;padding-right:50px;" maxlength="500" placeholder="comments upto 500 characters (optional)" oninput="updateCharCount(this)">{{ old('publicationsComments', $d->publicationsComments ?? '') }}</textarea>
                    <span class="ws-char-count">0/500</span>
                </div>
            </div>

            {{-- Section 3: Engagement in Student Involvement and Capacity Building --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:10px;">3. Engagement in Student Involvement and Capacity Building:</label>

                <div style="margin-bottom:12px;">
                    <label class="ws-field-label" style="font-size:11px;font-style:italic;color:var(--ink-500);padding-left:14px;">a. Level of engagement of students and other project members in the ongoing project activities. <span style="color:var(--danger);">*</span></label>
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="studentsRating" value="{{ $i }}" {{ old('studentsRating', $d->studentsRating ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div style="position:relative;">
                    <textarea name="studentsComments" rows="2" class="ws-input" style="font-size:12px;padding-right:50px;" maxlength="500" placeholder="comments upto 500 characters (optional)" oninput="updateCharCount(this)">{{ old('studentsComments', $d->studentsComments ?? '') }}</textarea>
                    <span class="ws-char-count">0/500</span>
                </div>
            </div>

            {{-- Section 4: Ethical Approvals --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:10px;">4. Please verify if the necessary ethical approvals have been included with the progress report.</label>
                <div style="display:flex;align-items:center;gap:12px;justify-content:flex-end;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-600);" id="ethicalLabel">NO</span>
                    <label class="ws-toggle">
                        <input type="checkbox" name="ethical" value="1" {{ old('ethical', $d->ethical ?? '') == '1' ? 'checked' : '' }}>
                        <span class="ws-toggle-slider"></span>
                    </label>
                    <span style="font-size:13px;font-weight:600;color:var(--success);" id="ethicalYesLabel" style="opacity:.4;">YES</span>
                </div>
                <input type="hidden" name="ethical_hidden" value="1">
            </div>

            {{-- Section 5: Budget Utilization --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:6px;">5. Budget Utilization</label>

                <div style="margin-bottom:6px;font-size:11.5px;color:var(--ink-500);line-height:1.6;font-style:italic;padding-left:14px;">
                    <div>a. How adequate do you find the project's budget utilization?</div>
                    <div>b. The project budget should be structured to ensure that at least 60% is utilized within the first year.</div>
                </div>

                <div style="margin-bottom:12px;">
                    <div class="ws-grade-row">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="ws-grade-opt">
                                <input type="radio" name="budgetRating" value="{{ $i }}" {{ old('budgetRating', $d->budgetRating ?? '') == $i ? 'checked' : '' }} required>
                                <span>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                </div>

                <div style="position:relative;">
                    <textarea name="budgetComments" rows="2" class="ws-input" style="font-size:12px;padding-right:50px;" maxlength="500" placeholder="comments upto 500 characters (optional)" oninput="updateCharCount(this)">{{ old('budgetComments', $d->budgetComments ?? '') }}</textarea>
                    <span class="ws-char-count">0/500</span>
                </div>
            </div>

            {{-- Section 6: Recommendation for Continuation --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:10px;">6. Recommendation for Continuation:</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <label style="flex:1;min-width:180px;display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--success)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--success)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="accepted" {{ (old('publish', $d->publish ?? '') == 'accepted' || (old('publish', $d->publish ?? '') == 'pending' && old('isAccepted', $d->isAccepted ?? '') == '1')) ? 'checked' : '' }} style="accent-color:var(--success);width:18px;height:18px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:13px;">Accepted</div>
                            <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">The progress report demonstrates sufficient progress and potential for continuation of the project.</div>
                        </div>
                    </label>
                    <label style="flex:1;min-width:180px;display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px 14px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--danger)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--danger)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="rejected" {{ old('publish', $d->publish ?? '') == 'rejected' ? 'checked' : '' }} style="accent-color:var(--danger);width:18px;height:18px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:13px;">Reject</div>
                            <div style="font-size:12px;color:var(--ink-500);margin-top:2px;">The progress report does not meet expectations for continuation of the project at this stage.</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Rejection Reason (required when rejecting) --}}
            <div id="progressRejectionReason" style="margin-bottom:20px;display:none;">
                <label style="font-size:13px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:6px;">
                    Rejection Reason <span style="color:var(--danger);">*</span>
                </label>
                <textarea name="rejection_reason" id="progressRejectionReasonInput" rows="3" maxlength="2000" class="ws-input"
                    placeholder="Explain to the admin and LPI why this report is being rejected...">{{ old('rejection_reason', $d->rejection_reason ?? ($latestRejectionReason ?? '')) }}</textarea>
            </div>

            <div class="ws-form-actions" style="display:flex;justify-content:flex-end;gap:8px;padding-top:12px;border-top:1px solid var(--ink-100);">
                <button type="submit" name="save_action" value="draft" class="ws-btn ws-btn-outline" style="font-size:12px;padding:5px 12px;">
                    <i class="fas fa-save"></i> Save Report as Draft
                </button>
                <button type="submit" name="save_action" value="submit" class="ws-btn ws-btn-primary" style="font-size:12px;padding:5px 12px;">
                    <i class="fas fa-paper-plane"></i> Submit {{ $reportTitle }}
                </button>
            </div>
        </form>
    @endif
</div>
