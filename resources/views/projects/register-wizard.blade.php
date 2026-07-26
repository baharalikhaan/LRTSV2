{{-- ============================================================
        WIZARD REGISTRATION MODAL — register-wizard.blade.php
        Injected into Bootstrap .modal-content via AJAX.
        Original structure: read-only tab1, pillars+college tab2,
        publications/IP/students tab3, review tab4.
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

<style>
    .wizard-modal { display:flex; flex-direction:column; max-height:82vh; font-family:'Inter','Segoe UI Variable','Segoe UI',ui-sans-serif,system-ui,sans-serif; }
    .wizard-steps { display:flex; align-items:center; justify-content:center; padding:18px 20px 14px; background:#fff; border-bottom:1px solid var(--ink-100); flex-shrink:0; }
    .wizard-step { display:flex; align-items:center; gap:7px; cursor:default; }
    .step-circle { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:var(--ink-100); color:var(--ink-500); font-size:12px; font-weight:700; transition:all .2s; flex-shrink:0; }
    .wizard-step.active .step-circle { background:var(--brand-500); color:#fff; box-shadow:0 0 0 3px var(--brand-100); }
    .wizard-step.completed .step-circle { background:var(--success); color:#fff; }
    .step-label { font-size:12.5px; font-weight:500; color:var(--ink-400); white-space:nowrap; }
    .wizard-step.active .step-label, .wizard-step.completed .step-label { color:var(--brand-500); }
    .wizard-connector { width:40px; height:2px; background:var(--ink-200); margin:0 6px; flex-shrink:0; }
    .wizard-body { flex:1; overflow-y:auto; padding:20px 24px; }
    .wizard-panel { display:none; }
    .wizard-panel.active { display:block; }
    .wizard-form-step { margin-bottom:10px; }
    .form-group { display:flex; flex-direction:column; gap:4px; margin-bottom:12px; }
    .form-group label { font-size:12.5px; font-weight:600; color:var(--ink-600); }
    .wizard-input { width:100%; padding:8px 10px; border:1px solid var(--ink-200); border-radius:6px; font-size:13px; font-family:'Inter','Segoe UI',system-ui,sans-serif; color:var(--ink-800); background:#fff; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; }
    .wizard-input:focus { outline:none; border-color:var(--brand-400); box-shadow:0 0 0 3px var(--brand-100); }
    .wizard-input[readonly] { background:var(--sand-50); color:var(--ink-500); cursor:default; }
    .pillar-checkboxes { display:flex; flex-wrap:wrap; gap:8px; margin-top:4px; }
    .pillar-checkboxes label { display:inline-flex; align-items:center; gap:5px; font-size:12.5px; color:var(--ink-600); cursor:pointer; padding:4px 10px; border:1px solid var(--ink-200); border-radius:6px; transition:all .15s; }
    .pillar-checkboxes label:hover { border-color:var(--brand-300); background:var(--brand-50); }
    .pillar-checkboxes input:checked + span { color:var(--brand-500); font-weight:600; }
    .pillar-checkboxes input:checked ~ label { border-color:var(--brand-400); background:var(--brand-50); }
    .commitment-section { margin-bottom:20px; }
    .commitment-section:last-child { margin-bottom:0; }
    .commitment-section h4 { font-size:13px; font-weight:700; color:var(--ink-700); margin:0 0 8px; padding-bottom:4px; border-bottom:1px solid var(--ink-100); text-transform:uppercase; letter-spacing:.04em; }
    .commitment-grid { display:flex; flex-wrap:wrap; gap:8px; }
    .commitment-item { display:flex; flex-direction:column; min-width:100px; }
    .commitment-item label { font-size:11px; font-weight:500; color:var(--ink-500); margin-bottom:2px; }
    .commitment-item input { padding:5px 7px; font-size:12px; border:1px solid var(--ink-200); border-radius:4px; max-width:95px; }
    .commitment-item input:focus { outline:none; border-color:var(--brand-400); box-shadow:0 0 0 2px var(--brand-100); }
    .wizard-footer { display:flex; align-items:center; gap:10px; padding:14px 24px; border-top:1px solid var(--ink-100); background:#fff; flex-shrink:0; }
    .wizard-btn { padding:8px 16px; border-radius:6px; font-size:13px; font-weight:600; border:1px solid transparent; cursor:pointer; transition:all .15s; font-family:inherit; }
    .wizard-btn-primary { background:var(--brand-500); color:#fff; border-color:var(--brand-600); }
    .wizard-btn-primary:hover { background:var(--brand-600); }
    .wizard-btn-secondary { background:#fff; color:var(--ink-600); border-color:var(--ink-200); }
    .wizard-btn-secondary:hover { background:var(--ink-50); }
    .wizard-btn-success { background:var(--success); color:#fff; }
    .wizard-btn-success:hover { opacity:.9; }
    .wizard-footer-left { margin-right:auto; }
    .wizard-review-area { background:var(--sand-50); border:1px solid var(--sand-100); border-radius:6px; padding:14px 16px; font-size:13px; color:var(--ink-700); max-height:320px; overflow-y:auto; }
    .wizard-review-area dt { font-weight:600; color:var(--ink-800); margin-top:8px; font-size:12px; }
    .wizard-review-area dt:first-child { margin-top:0; }
    .wizard-review-area dd { margin:2px 0 0 8px; color:var(--ink-600); }
    @media (max-width:600px) {
        .wizard-body { padding:14px 16px; }
        .wizard-connector { width:16px; }
    }
</style>

<form id="wizardForm" method="POST" action="{{ route('wizard.save-all') }}">
    @csrf
    <input type="hidden" name="project_id" value="{{ $confProject->id ?? 0 }}">

    <div class="wizard-modal">

        {{-- ─── Progress / step indicators ─── --}}
        <div class="wizard-steps">
            @foreach($steps as $i => $step)
                <div class="wizard-step {{ $i === 0 ? 'active' : '' }}"
                     data-step="{{ $i + 1 }}"
                     data-label="{{ $step['label'] }}">
                    <span class="step-circle">{{ $i + 1 }}</span>
                    <span class="step-label">{{ $step['label'] }}</span>
                </div>
                @if($i < count($steps) - 1)
                    <div class="wizard-connector"></div>
                @endif
            @endforeach
        </div>

        <div class="wizard-body">

            {{-- ─────────────── TAB 1: Basic Info (read-only) ─────────────── --}}
            <div class="wizard-panel active" data-panel="1">
                <div class="wizard-form-step">
                    <div class="form-group">
                        <label>Project ID</label>
                        <input type="text" class="wizard-input" readonly
                               value="{{ $confProject->old_project_id ?? '—' }}">
                    </div>
                    <div class="form-group">
                        <label>Project Title</label>
                        <input type="text" class="wizard-input" readonly
                               value="{{ $confProject->title ?? '—' }}">
                    </div>
                    <div class="form-group">
                        <label>Principal Investigator (LPI) Name</label>
                        <input type="text" class="wizard-input" readonly
                               value="{{ $confProject->author ?? '—' }}">
                    </div>
                    <div class="form-group">
                        <label>LPI Email</label>
                        <input type="text" class="wizard-input" readonly
                               value="{{ $confProject->email ?? '—' }}">
                    </div>
                    {{-- Hidden fields carried along for save --}}
                    <input type="hidden" name="project_title_en" value="{{ $confProject->title ?? '' }}">
                    <input type="hidden" name="pi_name" value="{{ $confProject->author ?? auth()->user()->name ?? '' }}">
                    <input type="hidden" name="pi_email" value="{{ $confProject->email ?? auth()->user()->email ?? '' }}">
                </div>
            </div>

            {{-- ─────────────── TAB 2: Pillar & College ─────────────── --}}
            <div class="wizard-panel" data-panel="2">
                <div class="wizard-form-step">
                    <div class="form-group">
                        <label>Pillars <span class="text-muted">(select all that apply)</span></label>
                        <div class="pillar-checkboxes">
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
                    <div class="form-group" style="margin-top:16px;">
                        <label for="wizardCollege">College</label>
                        <select id="wizardCollege" name="college_id" class="wizard-input">
                            <option value="">— Select College —</option>
                            @foreach($colleges as $c)
                                @php
                                    $colSelected = (old('college_id') !== null && old('college_id') == $c->id) || in_array($c->id, $projectCollegeIds);
                                @endphp
                                <option value="{{ $c->id }}" {{ $colSelected ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ─────────────── TAB 3: Commitments (Publications, IP, Students) ─────────────── --}}
            <div class="wizard-panel" data-panel="3">
                <div class="wizard-form-step">

                    {{-- Publications --}}
                    <div class="commitment-section">
                        <h4>Publications</h4>
                        <div class="commitment-grid">
                            <div class="commitment-item"><label>Q1 Articles</label><input type="number" name="pub_q1" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Q2 Articles</label><input type="number" name="pub_q2" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Q3 Articles</label><input type="number" name="pub_q3" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Q4 Articles</label><input type="number" name="pub_q4" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Conference</label><input type="number" name="pub_conf" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Books</label><input type="number" name="pub_books" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Edited Books</label><input type="number" name="pub_edit_books" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Chapters</label><input type="number" name="pub_chapters" min="0" step="1" value="0"></div>
                        </div>
                    </div>

                    {{-- Intellectual Property --}}
                    <div class="commitment-section">
                        <h4>Intellectual Property & Innovation</h4>
                        <div class="commitment-grid">
                            <div class="commitment-item"><label>IP</label><input type="number" name="ip_count" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Filed Patents</label><input type="number" name="ip_patents" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Open-Source SW</label><input type="number" name="ip_opensource" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Start-up</label><input type="checkbox" name="ip_startup" value="1" style="width:16px;height:16px;margin-top:8px;"></div>
                            <div class="commitment-item"><label>Ethical</label><input type="checkbox" name="ip_ethical" value="1" style="width:16px;height:16px;margin-top:8px;"></div>
                        </div>
                    </div>

                    {{-- Students --}}
                    <div class="commitment-section">
                        <h4>Students & Training</h4>
                        <div class="commitment-grid">
                            <div class="commitment-item"><label>Master Students</label><input type="number" name="stu_master" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Undergraduate</label><input type="number" name="stu_ug" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>PhD Students</label><input type="number" name="stu_phd" min="0" step="1" value="0"></div>
                            <div class="commitment-item"><label>Cross-College</label><input type="checkbox" name="stu_cross" value="1" style="width:16px;height:16px;margin-top:8px;"></div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ─────────────── TAB 4: Review & Submit ─────────────── --}}
            <div class="wizard-panel" data-panel="4">
                <div class="wizard-form-step">
                    <p class="text-muted" style="font-size:13px; margin-bottom:14px;">
                        Please review the information before submitting.
                    </p>
                    <div id="wizard-review-area" class="wizard-review-area">
                        {{-- Populated dynamically via JS --}}
                    </div>
                    <div class="form-group" style="margin-top:14px;">
                        <label class="checkbox-label" style="display:flex; align-items:flex-start; gap:8px; font-size:13px; color:var(--ink-600);">
                            <input type="checkbox" id="agreeCheck" required>
                            <span>I confirm that the information provided is accurate and I agree to the terms and conditions.</span>
                        </label>
                    </div>
                </div>
            </div>

        </div>{{-- /.wizard-body --}}

        {{-- ─── Footer: navigation buttons ─── --}}
        <div class="wizard-footer">
            <button type="button" id="prevBtn" class="wizard-btn wizard-btn-secondary" style="display:none;">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
            <button type="button" id="nextBtn" class="wizard-btn wizard-btn-primary">
                Next <i class="fas fa-chevron-right"></i>
            </button>
            <button type="submit" id="submitBtn" class="wizard-btn wizard-btn-success" style="display:none;">
                <i class="fas fa-check"></i> Submit Registration
            </button>
            <button type="button" id="cancelBtn" class="wizard-btn wizard-btn-ghost">
                Cancel
            </button>
        </div>
    </div>
</form>

<script>
(function() {
    'use strict';

    const prevBtn     = document.getElementById('prevBtn');
    const nextBtn     = document.getElementById('nextBtn');
    const submitBtn   = document.getElementById('submitBtn');
    const cancelBtn   = document.getElementById('cancelBtn');
    const panels      = document.querySelectorAll('.wizard-panel');
    const steps       = document.querySelectorAll('.wizard-step');
    const agreeCheck  = document.getElementById('agreeCheck');
    const wizardForm  = document.getElementById('wizardForm');

    let currentStep = 1;
    const totalSteps = panels.length;

    function closeWizard() {
        const bsModal = bootstrap.Modal.getInstance(document.getElementById('registerWizardModal'));
        if (bsModal) bsModal.hide();
        else {
            const el = document.getElementById('registerWizardModal');
            if (el) {
                const jqEl = $(el);
                if (jqEl.modal) jqEl.modal('hide');
                else el.remove();
            }
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.classList.remove('modal-open');
        }
    }

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        panels.forEach(p => p.classList.remove('active'));
        const activePanel = document.querySelector(`.wizard-panel[data-panel="${step}"]`);
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

        const body = document.querySelector('.wizard-body');
        if (body) body.scrollTop = 0;
    }

    if (nextBtn) nextBtn.addEventListener('click', function() {
        const currentPanel = document.querySelector(`.wizard-panel[data-panel="${currentStep}"]`);
        if (currentPanel) {
            const required = currentPanel.querySelectorAll('[required]');
            for (const el of required) {
                if (!el.value || el.value.trim() === '') {
                    el.focus();
                    el.style.borderColor = 'var(--danger)';
                    setTimeout(() => el.style.borderColor = '', 2000);
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

    if (cancelBtn) cancelBtn.addEventListener('click', closeWizard);

    function buildReview() {
        const area = document.getElementById('wizard-review-area');
        if (!area) return;

        // Tab 1 data (hidden fields)
        const projTitle = document.querySelector('input[name="project_title_en"]')?.value || '—';
        const piName    = document.querySelector('input[name="pi_name"]')?.value || '—';
        const piEmail   = document.querySelector('input[name="pi_email"]')?.value || '—';

        // Tab 2 data
        const pillarLabels = [];
        document.querySelectorAll('input[name="pillars[]"]:checked').forEach(function(cb) {
            const label = cb.closest('label')?.textContent?.trim() || cb.value;
            pillarLabels.push(esc(label));
        });
        const collegeSel = document.getElementById('wizardCollege');
        const collegeLabel = collegeSel ? collegeSel.options[collegeSel.selectedIndex]?.text || '—' : '—';

        // Tab 3 data
        function getVal(name) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return null;
            if (el.type === 'checkbox') return el.checked ? 'Yes' : '';
            return parseInt(el.value) || 0;
        }

        // Publications summary
        const pubFields = [
            ['pub_q1', 'Q1 Articles'], ['pub_q2', 'Q2 Articles'], ['pub_q3', 'Q3 Articles'],
            ['pub_q4', 'Q4 Articles'], ['pub_conf', 'Conference'], ['pub_books', 'Books'],
            ['pub_edit_books', 'Ed. Books'], ['pub_chapters', 'Chapters']
        ];
        let pubSummary = '';
        pubFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v > 0) pubSummary += `<span style="display:inline-block;margin-right:6px;"><strong>${p[1]}:</strong> ${v}</span>`;
        });
        if (!pubSummary) pubSummary = '—';

        const ipFields = [
            ['ip_count', 'IP'], ['ip_patents', 'Patents'], ['ip_opensource', 'Open-Source'],
            ['ip_startup', 'Start-up'], ['ip_ethical', 'Ethical']
        ];
        let ipSummary = '';
        ipFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v !== '' && v !== 0) ipSummary += `<span style="display:inline-block;margin-right:6px;"><strong>${p[1]}:</strong> ${v}</span>`;
        });
        if (!ipSummary) ipSummary = '—';

        const stuFields = [
            ['stu_master', 'Master'], ['stu_ug', 'UG'], ['stu_phd', 'PhD'],
            ['stu_cross', 'Cross-College']
        ];
        let stuSummary = '';
        stuFields.forEach(function(p) {
            const v = getVal(p[0]);
            if (v !== null && v !== '' && v !== 0) stuSummary += `<span style="display:inline-block;margin-right:6px;"><strong>${p[1]}:</strong> ${v}</span>`;
        });
        if (!stuSummary) stuSummary = '—';

        let html = '<dl>';
        html += `<dt>Project</dt><dd>${esc(projTitle)}</dd>`;
        html += `<dt>PI</dt><dd>${esc(piName)} — ${esc(piEmail)}</dd>`;
        html += `<dt>College</dt><dd>${esc(collegeLabel)}</dd>`;
        html += `<dt>Pillars</dt><dd>${pillarLabels.length ? pillarLabels.join(', ') : '—'}</dd>`;
        html += `<dt>Publications</dt><dd style="line-height:1.6;">${pubSummary}</dd>`;
        html += `<dt>IP & Innovation</dt><dd style="line-height:1.6;">${ipSummary}</dd>`;
        html += `<dt>Students</dt><dd style="line-height:1.6;">${stuSummary}</dd>`;
        html += '</dl>';
        area.innerHTML = html;
    }

    function esc(s) {
        if (!s) return s;
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // ── AJAX form submission ──
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
                        const body = document.querySelector('.wizard-body');
                        if (body) {
                            body.innerHTML = '<div class="text-center py-5"><i class="fas fa-check-circle" style="font-size:48px;color:var(--success);"></i><p class="mt-3" style="font-size:16px;font-weight:600;">' + (res.message || 'Project registered successfully.') + '</p></div>';
                        }
                        const footer = document.querySelector('.wizard-footer');
                        if (footer) footer.style.display = 'none';
                        setTimeout(function() {
                            closeWizard();
                            if (res.redirect) window.location.href = res.redirect;
                        }, 2000);
                    } else if (res.error) {
                        const body = document.querySelector('.wizard-body');
                        if (body) {
                            body.innerHTML = '<div class="text-center py-5"><div class="alert alert-danger mx-4">' + res.error + '</div></div>';
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Registration';
                        }
                    }
                },
                error: function(xhr) {
                    let msg = 'An error occurred. Please try again.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) msg = xhr.responseJSON.error;
                        else if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            msg = Object.values(errors).flat().join('<br>');
                        }
                    }
                    const body = document.querySelector('.wizard-body');
                    if (body) {
                        body.innerHTML = '<div class="text-center py-5"><div class="alert alert-danger mx-4">' + msg + '</div></div>';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit Registration';
                    }
                }
            });
        });
    }

})();
</script>
