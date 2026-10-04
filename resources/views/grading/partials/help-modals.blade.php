{{-- Help modals (Fluent brand maroon, custom WS-styled) --}}
<div class="ws-modal-overlay" id="helpCommitments" style="display:none;">
    <div class="ws-modal">
        <div class="ws-modal-head"><span><i class="fas fa-list-check"></i> Help — Commitments</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpCommitments')">&times;</button></div>
        <div class="ws-modal-body">Commitments reflect the research outputs pledged in the proposal, scored per category.</div>
    </div>
</div>

<div class="ws-modal-overlay" id="helpProgress" style="display:none;">
    <div class="ws-modal">
        <div class="ws-modal-head"><span><i class="fas fa-clipboard-check"></i> Help — Progress Report</span><button type="button" class="ws-modal-close" onclick="closeHelp('helpProgress')">&times;</button></div>
        <div class="ws-modal-body">
            <b>Grading guideline</b>
            <ul class="mb-0">
                <li>Evaluate the progress report on a scale of 1 to 5.</li>
                <li>1 indicates the highest dissatisfaction; 5 indicates the highest satisfaction.</li>
            </ul>
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
