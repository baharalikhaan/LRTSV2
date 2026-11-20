{{-- Help modals (Fluent brand maroon, custom WS-styled) --}}
<div class="ws-modal-overlay" id="helpCommitments" style="display:none;">
    <div class="ws-modal">
        <div class="ws-modal-head"><span><i class="fas fa-list-check"></i> Help — Commitments</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpCommitments')">&times;</button></div>
        <div class="ws-modal-body">Commitments reflect the research outputs pledged in the proposal, scored per category.</div>
    </div>
</div>

<div class="ws-modal-overlay" id="helpProgress" style="display:none;">
    <div class="ws-modal" style="max-width:1180px;">
        <div class="ws-modal-head"><span><i class="fas fa-clipboard-check"></i> Help — Progress Report Grading</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpProgress')">&times;</button></div>
        <div class="ws-modal-body" style="max-height:80vh;overflow-y:auto;">
            <b>Grading guideline</b>
            <ul class="mb-0">
                <li>Evaluate the progress report on a scale of 1 to 5.</li>
                <li>1 indicates the highest dissatisfaction; 5 indicates the highest satisfaction.</li>
            </ul>

            <p style="margin:14px 0 0;font-size:12.5px;color:var(--ink-600);line-height:1.5;">
                Use the descriptors below to choose the rating for each category. Read across the row: the
                column you pick should be the one that best describes the project's current standing.
            </p>

            @php
                // 1–5 descriptors per graded category of the progress report.
                $progressRubric = [
                    'Progress toward committed outcomes' => [
                        '&lt;40% of interim targets achieved; key milestones not started or clearly off track, with little or no credible explanation.',
                        '40–59% of interim targets achieved; important milestones not initiated or substantially delayed, with weak or partial justification.',
                        '60–79% of interim targets achieved; several milestones delayed or incomplete, but with a plausible and documented catch-up plan.',
                        '80–99% of interim targets achieved; minor delays or gaps, but overall progress clearly on track and well justified.',
                        '≥100% of interim targets for key outputs (publications, prototypes, IP, software, creative/professional works) achieved or exceeded; trajectory clearly supports final commitments.',
                    ],
                    'Publications / scholarly or creative outputs' => [
                        'No admissible outputs (e.g., pre-award or missing LPI authorship) or serious issues in claimed outputs (incorrect dates, misattribution).',
                        'Few admissible outputs; heavy reliance on low-reputation or non-peer-reviewed venues without strong disciplinary justification; clearly below committed volume.',
                        'Outputs mainly in mid/lower-tier but acceptable venues; admissible; quantity somewhat below plan.',
                        'Mix of tiers with some high-tier or strong field-equivalent outputs; all admissible; volume and venue choices largely consistent with the plan.',
                        'Outputs fully aligned with commitments; majority in top-tier or field-equivalent venues; all admissible (≥3 months post-award, LPI involved).',
                    ],
                    'Student engagement & capacity building' => [
                        '&lt;30% engaged or engagement nominal only, with no demonstrable capacity building.',
                        '30–49% engaged and/or roles largely routine or administrative; limited evidence of skills development.',
                        '50–69% engaged; some roles limited or mainly assisting; capacity building present but modest.',
                        '70–89% engaged; roles mostly meaningful; documented skill development through training, research tasks, or creative practice.',
                        '≥90% of planned students/trainees engaged with clear roles and strong evidence of capacity building (theses, co-authorship, substantive research, or creative tasks).',
                    ],
                    'Budget utilization & project management' => [
                        '&lt;25% utilized without substantial justification, or clear indications of inappropriate or ineffective use of funds.',
                        '25–39% utilized or evident misallocation against the approved plan; justifications are incomplete or weak.',
                        '40–49% or &gt;90% utilized; noticeable misalignment or timing issues, but explanations and corrective steps documented.',
                        '50–59% or 80–90% utilized; mostly aligned with plan; minor timing or category variances with reasonable explanations.',
                        '60–80% of the relevant-period budget utilized; spending clearly aligned with work plan and deliverables; variances minor and well justified.',
                    ],
                    'Ethics, governance & compliance' => [
                        'Required ethics/governance approval is clearly missing or inadequate for ongoing work; this must cap the overall decision at Reject or Conditional.',
                        'Required approval likely needed but not yet obtained or significantly delayed; substantial uncertainty about compliance.',
                        'Some approvals are pending for secondary or low-risk components, or for delays in documentation; a credible plan and timeline to resolve them.',
                        'Required approvals obtained; minor documentation gaps that do not affect participant/subject safety or compliance.',
                        'All required ethics/governance approvals are in place, valid, and documented; reporting deadlines met; no compliance concerns.',
                    ],
                ];
            @endphp

            <div style="overflow-x:auto;border:1px solid var(--ink-100);border-radius:6px;margin-top:12px;">
                <table style="width:100%;min-width:1080px;border-collapse:collapse;font-size:11px;line-height:1.45;">
                    <thead>
                        <tr style="background:var(--brand-100);">
                            <th style="text-align:left;padding:9px 10px;width:154px;font-size:11px;font-weight:600;color:var(--brand-700);">Category</th>
                            @foreach([1, 2, 3, 4, 5] as $rating)
                                <th style="text-align:center;padding:9px 8px;width:18%;font-size:11px;font-weight:700;color:var(--brand-700);border-left:1px solid var(--brand-300);">{{ $rating }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($progressRubric as $category => $descriptors)
                            <tr style="border-top:1px solid var(--ink-100);">
                                <td style="padding:9px 10px;vertical-align:top;font-weight:600;color:var(--ink-800);background:var(--sand-50);">{{ $category }}</td>
                                @foreach($descriptors as $descriptor)
                                    <td style="padding:9px 10px;vertical-align:top;color:var(--ink-600);border-left:1px solid var(--ink-100);">{!! $descriptor !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p style="margin:12px 0 0;font-size:11px;color:var(--ink-400);font-style:italic;">
                Tip: the rating you select for a category is what the reviewer sees in the report card — support it in the comments
                column by naming the descriptor you applied and the evidence behind it.
            </p>
        </div>
    </div>
</div>

<div class="ws-modal-overlay" id="helpFinal" style="display:none;">
    <div class="ws-modal" style="max-width:560px;">
        <div class="ws-modal-head"><span><i class="fas fa-star"></i> Help — Final Report Grading</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpFinal')">&times;</button></div>
        <div class="ws-modal-body" style="max-height:70vh;overflow-y:auto;">
            <p style="margin:0 0 12px;">Each section is auto-calculated based on committed targets and verified outcomes. You may override the auto-grade by selecting a different rating.</p>

            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;margin-bottom:12px;">
                <b style="color:var(--ink-800);">A. Achievements against objectives</b>
                <p style="margin:6px 0 0;font-size:12px;color:var(--ink-600);line-height:1.5;">
                    Calculates how well the project achieved its committed IP, patents, software, and startup outcomes.
                </p>
                <div style="background:#fff;border:1px solid var(--ink-100);border-radius:4px;padding:8px 10px;margin-top:8px;font-size:11px;font-family:monospace;color:var(--ink-700);">
                    expectedSum = Σ (committed × points per type)<br>
                    actualSum = Σ (selected item points)<br>
                    grade = min((actualSum / expectedSum) × 5, 5)
                </div>
                <table style="width:100%;font-size:11px;margin-top:8px;border-collapse:collapse;">
                    @foreach(['ip_disclosure' => 'IP Disclosure', 'provisional_patent' => 'Provisional Patent', 'granted_patent' => 'Granted Patent', 'open_source_sw' => 'Open Source SW', 'startup' => 'Startup', 'cross_college' => 'Cross-College'] as $key => $label)
                    <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:3px 0;">{{ $label }}</td><td style="text-align:right;font-weight:600;">{{ $scoreMap[$key] ?? 0 }} pts</td></tr>
                    @endforeach
                </table>
            </div>

            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;margin-bottom:12px;">
                <b style="color:var(--ink-800);">B. Publications & IP</b>
                <p style="margin:6px 0 0;font-size:12px;color:var(--ink-600);line-height:1.5;">
                    Calculates the publication output against committed targets.
                </p>
                <div style="background:#fff;border:1px solid var(--ink-100);border-radius:4px;padding:8px 10px;margin-top:8px;font-size:11px;font-family:monospace;color:var(--ink-700);">
                    expectedSum = Σ (committed × points per type)<br>
                    actualSum = Σ (selected item points)<br>
                    grade = min((actualSum / expectedSum) × 5, 5)
                </div>
                <table style="width:100%;font-size:11px;margin-top:8px;border-collapse:collapse;">
                    @foreach(['journal_q1' => 'Q1 Journal Article', 'journal_q2' => 'Q2 Journal Article', 'journal_q3' => 'Q3 Journal Article', 'journal_q4' => 'Q4 Journal Article', 'conference' => 'Conference Paper', 'book' => 'Book', 'edited_book' => 'Edited Book', 'book_chapter' => 'Book Chapter'] as $key => $label)
                    <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:3px 0;">{{ $label }}</td><td style="text-align:right;font-weight:600;">{{ $scoreMap[$key] ?? 0 }} pts</td></tr>
                    @endforeach
                </table>
            </div>

            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;margin-bottom:12px;">
                <b style="color:var(--ink-800);">C. Student & Young Researcher Involvement</b>
                <p style="margin:6px 0 0;font-size:12px;color:var(--ink-600);line-height:1.5;">
                    Calculates student and researcher engagement against committed targets.
                </p>
                <div style="background:#fff;border:1px solid var(--ink-100);border-radius:4px;padding:8px 10px;margin-top:8px;font-size:11px;font-family:monospace;color:var(--ink-700);">
                    expectedSum = Σ (committed students × points per type)<br>
                    actualSum = Σ (selected students + researchers) points<br>
                    grade = min((actualSum / expectedSum) × 5, 5)
                </div>
                <table style="width:100%;font-size:11px;margin-top:8px;border-collapse:collapse;">
                    <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:3px 0;">PhD Student</td><td style="text-align:right;font-weight:600;">{{ $scoreMap['phd'] ?? 0 }} pts</td></tr>
                    <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:3px 0;">Masters Student</td><td style="text-align:right;font-weight:600;">{{ $scoreMap['masters'] ?? 0 }} pts</td></tr>
                    <tr style="border-bottom:1px solid var(--ink-100);"><td style="padding:3px 0;">Undergraduate Student</td><td style="text-align:right;font-weight:600;">{{ $scoreMap['ug'] ?? 0 }} pts</td></tr>
                    <tr><td style="padding:3px 0;">Researcher</td><td style="text-align:right;font-weight:600;">{{ $scoreMap['researcher'] ?? 0 }} pts</td></tr>
                </table>
            </div>

            <div style="background:var(--sand-50);border:1px solid var(--ink-100);border-radius:6px;padding:12px;">
                <b style="color:var(--ink-800);">D. Project Impact</b>
                <p style="margin:6px 0 0;font-size:12px;color:var(--ink-600);line-height:1.5;">
                    Manual rating only — no auto-calculation. Evaluate the overall impact of the project outcomes, including potential for commercialization, societal benefit, and contribution to knowledge.
                </p>
            </div>

            <p style="margin:12px 0 0;font-size:11px;color:var(--ink-400);font-style:italic;">
                Tip: Uncheck items in sections A-C to exclude them from the auto-grade calculation. Points are sourced from the scores configuration table.
            </p>
        </div>
    </div>
</div>

<div class="ws-modal-overlay" id="helpReadiness" style="display:none;">
    <div class="ws-modal">
        <div class="ws-modal-head"><span><i class="fas fa-map"></i> Help — QU Readiness Mapping</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpReadiness')">&times;</button></div>
        <div class="ws-modal-body">QU Readiness Mapping captures the project's readiness across the assessed dimensions.</div>
    </div>
</div>
</invoke>
