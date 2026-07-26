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

    <p style="color:var(--ink-500);font-size:12px;margin:0 0 14px;line-height:1.5;">
        Kindly evaluate the final report based on the following criteria on a scale of 1 to 5, where 1 indicates the highest level of dissatisfaction and 5 indicates the highest level of satisfaction.
    </p>

    @php
        $contributions = $project->contributions()->get();
        $outcomes = $project->outcomes()->get();
        $students = $project->students()->get();
    @endphp

    @if($fg && $fg->publish !== 'pending')
        {{-- Already graded — show submitted values --}}
        <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px 14px;font-size:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span class="ws-mini-label">Status</span>
                @if($fg->isAccepted == 1)
                    <span style="display:inline-flex;align-items:center;gap:4px;color:var(--success);font-weight:600;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        Accepted
                    </span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:4px;color:var(--danger);font-weight:600;">
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
            @foreach($sections as $key => $s)
                <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--ink-100);">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-weight:600;color:var(--ink-700);font-size:12px;">{{ $s['label'] }}</span>
                        <strong style="color:var(--brand-600);font-size:15px;">{{ $s['grade'] ?? '—' }}/5</strong>
                    </div>
                    @if($s['comment'])
                        <div style="margin-top:4px;color:var(--ink-500);font-size:11px;font-style:italic;">{{ $s['comment'] }}</div>
                    @endif
                </div>
            @endforeach

            <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--ink-100);display:flex;justify-content:space-between;align-items:center;">
                <span class="ws-mini-label">Total</span>
                <strong style="color:var(--brand-600);font-size:16px;">{{ $fg->total ?? '—' }}</strong>
            </div>
        </div>
    @else
        @php $projectId = $project->id ?? ($report->project_id ?? ''); @endphp
        <form method="POST" action="/grading/{{ $projectId }}/save-final-grade" data-final-grade style="margin-top:4px;">
            @csrf
            <input type="hidden" name="report_type" value="final">

            {{-- ===== Section A: Achievements against objectives ===== --}}
            <div style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-trophy" style="color:var(--brand-500);margin-right:4px;"></i> A. Achievements against objectives</span>
                    <span class="ws-mini-label">{{ $contributions->count() }} record(s)</span>
                </div>

                {{-- Data table --}}
                @if($contributions->count())
                    <div style="overflow-x:auto;margin-bottom:10px;">
                        <table class="ws-table" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th style="font-size:10px;">#</th>
                                    <th style="font-size:10px;">Type</th>
                                    <th style="font-size:10px;">Detail</th>
                                    <th style="font-size:10px;">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contributions as $c)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $c->type ?? '—' }}</td>
                                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $c->detail ?? '—' }}</td>
                                    <td style="text-align:center;">{{ $c->score ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No contributions recorded.</p>
                @endif

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
                </div>
                <textarea name="commentA" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentA', $fg->commentA ?? '') }}</textarea>
            </div>

            {{-- ===== Section B: Publications & IP ===== --}}
            <div style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-book-open" style="color:var(--brand-500);margin-right:4px;"></i> B. Publications & IP</span>
                    <span class="ws-mini-label">{{ $outcomes->count() }} record(s)</span>
                </div>

                {{-- Data table --}}
                @if($outcomes->count())
                    <div style="overflow-x:auto;margin-bottom:10px;">
                        <table class="ws-table" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th style="font-size:10px;">#</th>
                                    <th style="font-size:10px;">Type</th>
                                    <th style="font-size:10px;">Identifier</th>
                                    <th style="font-size:10px;">Online Date</th>
                                    <th style="font-size:10px;">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outcomes as $o)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $o->type ?? '—' }}</td>
                                    <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $o->identifier ?? '—' }}</td>
                                    <td>{{ $o->online_date ? $o->online_date->format('Y-m-d') : '—' }}</td>
                                    <td style="text-align:center;">{{ $o->score ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No publications recorded.</p>
                @endif

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
                </div>
                <textarea name="commentB" rows="2" class="ws-input" style="font-size:11px;" placeholder="comments upto 500 characters (optional)">{{ old('commentB', $fg->commentB ?? '') }}</textarea>
            </div>

            {{-- ===== Section C: Student & Young Researcher Involvement ===== --}}
            <div style="margin-bottom:20px;background:var(--sand-50);border:1px solid var(--ink-100);border-radius:8px;padding:14px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <span style="font-size:13px;font-weight:600;color:var(--ink-800);"><i class="fas fa-user-graduate" style="color:var(--brand-500);margin-right:4px;"></i> C. Student & Young Researcher Involvement</span>
                    <span class="ws-mini-label">{{ $students->count() }} record(s)</span>
                </div>

                {{-- Data table --}}
                @if($students->count())
                    <div style="overflow-x:auto;margin-bottom:10px;">
                        <table class="ws-table" style="font-size:11px;">
                            <thead>
                                <tr>
                                    <th style="font-size:10px;">#</th>
                                    <th style="font-size:10px;">Type</th>
                                    <th style="font-size:10px;">Student ID</th>
                                    <th style="font-size:10px;">Days</th>
                                    <th style="font-size:10px;">Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $s)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $s->type ?? '—' }}</td>
                                    <td>{{ $s->std_id ?? '—' }}</td>
                                    <td style="text-align:center;">{{ $s->days ?? '—' }}</td>
                                    <td style="text-align:center;">{{ $s->score ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="color:var(--ink-400);font-size:11px;margin:0 0 10px;">No students recorded.</p>
                @endif

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

            {{-- Recommendation for Continuation --}}
            <div style="margin-bottom:16px;">
                <label style="font-size:12.5px;font-weight:600;color:var(--ink-800);display:block;margin-bottom:5px;">Recommendation for Continuation:</label>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <label style="flex:1;min-width:160px;display:flex;align-items:flex-start;gap:8px;cursor:pointer;padding:10px 12px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--success)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--success)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="accepted" {{ (old('publish', $fg->publish ?? '') == 'accepted' || (old('publish', $fg->publish ?? '') == 'pending' && old('isAccepted', $fg->isAccepted ?? '') == '1')) ? 'checked' : '' }} required style="accent-color:var(--success);width:16px;height:16px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:12px;">Accepted</div>
                            <div style="font-size:11px;color:var(--ink-500);margin-top:2px;">The final report demonstrates sufficient progress and potential for continuation of the project.</div>
                        </div>
                    </label>
                    <label style="flex:1;min-width:160px;display:flex;align-items:flex-start;gap:8px;cursor:pointer;padding:10px 12px;border:2px solid var(--ink-200);border-radius:8px;transition:all .15s;background:#fff;"
                           onmouseover="this.style.borderColor='var(--danger)'" onmouseout="this.style.borderColor=this.querySelector('input').checked?'var(--danger)':'var(--ink-200)'">
                        <input type="radio" name="publish" value="rejected" {{ (old('publish', $fg->publish ?? '') == 'rejected' || (old('publish', $fg->publish ?? '') == 'pending' && old('isAccepted', $fg->isAccepted ?? '') == '0')) ? 'checked' : '' }} required style="accent-color:var(--danger);width:16px;height:16px;margin-top:1px;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;color:var(--ink-800);font-size:12px;">Reject</div>
                            <div style="font-size:11px;color:var(--ink-500);margin-top:2px;">The final report does not meet expectations for continuation of the project at this stage.</div>
                        </div>
                    </label>
                </div>
            </div>

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
