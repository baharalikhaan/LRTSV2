<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-size: 13px; }
        .form-container { max-width: 1200px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-container h4 { margin-top: 25px; margin-bottom: 15px; font-weight: bold; font-size: 16px; border-bottom: 2px solid teal; padding-bottom: 8px; color: teal; }
        .form-container h5 { margin-top: 18px; font-weight: bold; font-size: 14px; color: #555; }
        .form-container label { font-weight: 600; font-size: 13px; }
        .btn-teal { background: teal; color: #fff; border: none; }
        .btn-teal:hover { background: #005959; color: #fff; }
        .btn-success { background: #28a745; color: #fff; border: none; }
        .btn-success:hover { background: #218838; color: #fff; }
        .delete-row { color: red; cursor: pointer; }
        .delete-row:hover { color: darkred; }
        .add-row { color: teal; cursor: pointer; font-weight: 600; }
        .add-row:hover { color: #005959; }
        .table-form th { background: #e8f5f5; font-size: 12px; text-align: center; }
        .table-form td { vertical-align: middle; }
        .table-form input, .table-form select, .table-form textarea { font-size: 12px; }
        .loading-spinner { display: none; }
        .header-info { background: #e8f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .header-info strong { color: teal; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="header-info">
            <h4 style="margin-top:0; border-bottom:none;">Progress Report Form</h4>
            <p><strong>Project:</strong> {{ $project->title ?? 'N/A' }} ({{ $project->old_project_id ?? 'N/A' }})</p>
            <p><strong>LPI:</strong> {{ $user->name ?? 'N/A' }}</p>
        </div>

        <form id="progressReportForm" method="POST">
            @csrf

            <!-- Basic Info Row -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Grant ID</label>
                    <input type="text" name="grant_id" class="form-control form-control-sm" value="{{ $report->grant_id ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>LPI Name</label>
                    <input type="text" name="lpi_name" class="form-control form-control-sm" value="{{ $report->lpi_name ?? $user->name }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Collaborator Institute</label>
                    <input type="text" name="collaborator_institute" class="form-control form-control-sm" value="{{ $report->collaborator_institute ?? '' }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Collaborator LPI Name</label>
                    <input type="text" name="collaborator_lpi_name" class="form-control form-control-sm" value="{{ $report->collaborator_lpi_name ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Report Period From</label>
                    <input type="date" name="report_period_from" class="form-control form-control-sm" value="{{ $report->report_period_from ?? '' }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Report Period To</label>
                    <input type="date" name="report_period_to" class="form-control form-control-sm" value="{{ $report->report_period_to ?? '' }}">
                </div>
            </div>

            <h4>GRANT INFORMATION</h4>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Grant ID</label>
                    <input type="text" name="grant_id_info" class="form-control form-control-sm" value="{{ $report->grant_id ?? '' }}" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Funding Duration</label>
                    <input type="text" name="funding_duration" class="form-control form-control-sm" value="{{ $report->funding_duration ?? '' }}" placeholder="e.g. 3 years">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Current Year (1st, 2nd or 3rd)</label>
                    <select name="current_year" class="form-control form-control-sm">
                        <option value="">Select...</option>
                        <option value="1st" {{ ($report->current_year ?? '') == '1st' ? 'selected' : '' }}>1st Year</option>
                        <option value="2nd" {{ ($report->current_year ?? '') == '2nd' ? 'selected' : '' }}>2nd Year</option>
                        <option value="3rd" {{ ($report->current_year ?? '') == '3rd' ? 'selected' : '' }}>3rd Year</option>
                    </select>
                </div>
            </div>

            <h5>Budget (QAR)</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-form table-sm">
                    <thead>
                        <tr>
                            <th rowspan="2">Category</th>
                            <th colspan="2">Year 1</th>
                            <th colspan="2">Year 2</th>
                            <th colspan="2">Year 3</th>
                        </tr>
                        <tr>
                            <th>QU</th>
                            <th>Collaborator</th>
                            <th>QU</th>
                            <th>Collaborator</th>
                            <th>QU</th>
                            <th>Collaborator</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Awarded Budget</strong></td>
                            <td><input type="number" step="0.01" name="year1_qu_awarded" class="form-control form-control-sm" value="{{ $report->year1_qu_awarded ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year1_collab_awarded" class="form-control form-control-sm" value="{{ $report->year1_collab_awarded ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year2_qu_awarded" class="form-control form-control-sm" value="{{ $report->year2_qu_awarded ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year2_collab_awarded" class="form-control form-control-sm" value="{{ $report->year2_collab_awarded ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year3_qu_awarded" class="form-control form-control-sm" value="{{ $report->year3_qu_awarded ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year3_collab_awarded" class="form-control form-control-sm" value="{{ $report->year3_collab_awarded ?? 0 }}"></td>
                        </tr>
                        <tr>
                            <td><strong>Actual Expenses</strong></td>
                            <td><input type="number" step="0.01" name="year1_qu_actual" class="form-control form-control-sm" value="{{ $report->year1_qu_actual ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year1_collab_actual" class="form-control form-control-sm" value="{{ $report->year1_collab_actual ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year2_qu_actual" class="form-control form-control-sm" value="{{ $report->year2_qu_actual ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year2_collab_actual" class="form-control form-control-sm" value="{{ $report->year2_collab_actual ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year3_qu_actual" class="form-control form-control-sm" value="{{ $report->year3_qu_actual ?? 0 }}"></td>
                            <td><input type="number" step="0.01" name="year3_collab_actual" class="form-control form-control-sm" value="{{ $report->year3_collab_actual ?? 0 }}"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h4>SCOPE, PURPOSE AND PROGRESS</h4>
            <h5>Specific Aims</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-form table-sm" id="specificAimsTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Aim</th>
                            <th style="width:120px;">Status</th>
                            <th style="width:120px;">On "date"</th>
                            <th>Comments</th>
                            <th style="width:40px;"><i class="fas fa-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $aims = json_decode($report->specific_aims ?? '[]', true); @endphp
                        @forelse($aims as $i => $aim)
                        <tr>
                            <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                            <td><input type="text" name="specific_aims[{{ $i }}][aim]" class="form-control form-control-sm" value="{{ $aim['aim'] ?? '' }}"></td>
                            <td>
                                <select name="specific_aims[{{ $i }}][status]" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    <option value="not_started" {{ ($aim['status'] ?? '') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                                    <option value="in_progress" {{ ($aim['status'] ?? '') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ ($aim['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </td>
                            <td><input type="date" name="specific_aims[{{ $i }}][date]" class="form-control form-control-sm" value="{{ $aim['date'] ?? '' }}"></td>
                            <td><textarea name="specific_aims[{{ $i }}][comments]" class="form-control form-control-sm" rows="1">{{ $aim['comments'] ?? '' }}</textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @empty
                        <tr>
                            <td class="row-num" style="text-align:center;">1</td>
                            <td><input type="text" name="specific_aims[0][aim]" class="form-control form-control-sm"></td>
                            <td>
                                <select name="specific_aims[0][status]" class="form-control form-control-sm">
                                    <option value="">Select</option>
                                    <option value="not_started">Not Started</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </td>
                            <td><input type="date" name="specific_aims[0][date]" class="form-control form-control-sm"></td>
                            <td><textarea name="specific_aims[0][comments]" class="form-control form-control-sm" rows="1"></textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <span class="add-row" onclick="addSpecificAim()"><i class="fas fa-plus-circle"></i> Add Aim</span>
            </div>

            <h4>RESULTS ACHIEVED</h4>
            <textarea name="results_achieved" class="form-control form-control-sm" rows="4">{{ $report->results_achieved ?? '' }}</textarea>

            <h4>REMAINING RESEARCH QUESTIONS</h4>
            <textarea name="remaining_questions" class="form-control form-control-sm" rows="4">{{ $report->remaining_questions ?? '' }}</textarea>

            <h4>GRANT OUTPUTS AND PROGRESS AGAINST GRANT PROPOSAL COMMITMENTS</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-form table-sm" id="outcomesTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Committed Outcomes in the Grant Proposal</th>
                            <th style="width:150px;">Number of Committed Outcomes</th>
                            <th style="width:150px;">Number of Achieved Outcomes</th>
                            <th style="width:40px;"><i class="fas fa-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $outcomes = json_decode($report->committed_outcomes ?? '[]', true); @endphp
                        @forelse($outcomes as $i => $oc)
                        <tr>
                            <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                            <td><input type="text" name="committed_outcomes[{{ $i }}][outcome]" class="form-control form-control-sm" value="{{ $oc['outcome'] ?? '' }}"></td>
                            <td><input type="number" name="committed_outcomes[{{ $i }}][committed]" class="form-control form-control-sm" value="{{ $oc['committed'] ?? '' }}"></td>
                            <td><input type="number" name="committed_outcomes[{{ $i }}][achieved]" class="form-control form-control-sm" value="{{ $oc['achieved'] ?? '' }}"></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @empty
                        <tr>
                            <td class="row-num" style="text-align:center;">1</td>
                            <td><input type="text" name="committed_outcomes[0][outcome]" class="form-control form-control-sm"></td>
                            <td><input type="number" name="committed_outcomes[0][committed]" class="form-control form-control-sm"></td>
                            <td><input type="number" name="committed_outcomes[0][achieved]" class="form-control form-control-sm"></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <span class="add-row" onclick="addOutcome()"><i class="fas fa-plus-circle"></i> Add Outcome</span>
            </div>

            <h5>Publications</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-form table-sm" id="publicationsTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Full Text</th>
                            <th style="width:40px;"><i class="fas fa-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $pubs = json_decode($report->publications_list ?? '[]', true); @endphp
                        @forelse($pubs as $i => $pub)
                        <tr>
                            <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                            <td><textarea name="publications_list[{{ $i }}][text]" class="form-control form-control-sm" rows="2">{{ $pub['text'] ?? '' }}</textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @empty
                        <tr>
                            <td class="row-num" style="text-align:center;">1</td>
                            <td><textarea name="publications_list[0][text]" class="form-control form-control-sm" rows="2"></textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <span class="add-row" onclick="addPublication()"><i class="fas fa-plus-circle"></i> Add Publication</span>
            </div>

            <h5>Capacity Building</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>RAs recruited:</strong></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-form table-sm" id="raTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Name</th>
                                    <th>Details</th>
                                    <th style="width:40px;"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $ras = json_decode($report->capacity_building_ras ?? '[]', true); @endphp
                                @forelse($ras as $i => $ra)
                                <tr>
                                    <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                                    <td><input type="text" name="capacity_building_ras[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $ra['name'] ?? '' }}"></td>
                                    <td><input type="text" name="capacity_building_ras[{{ $i }}][details]" class="form-control form-control-sm" value="{{ $ra['details'] ?? '' }}"></td>
                                    <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="row-num" style="text-align:center;">1</td>
                                    <td><input type="text" name="capacity_building_ras[0][name]" class="form-control form-control-sm"></td>
                                    <td><input type="text" name="capacity_building_ras[0][details]" class="form-control form-control-sm"></td>
                                    <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <span class="add-row" onclick="addRA()"><i class="fas fa-plus-circle"></i> Add RA</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <p><strong>Students involved:</strong></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-form table-sm" id="studentsTable">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Name</th>
                                    <th>Details</th>
                                    <th style="width:40px;"><i class="fas fa-trash"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $students = json_decode($report->capacity_building_students ?? '[]', true); @endphp
                                @forelse($students as $i => $s)
                                <tr>
                                    <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                                    <td><input type="text" name="capacity_building_students[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $s['name'] ?? '' }}"></td>
                                    <td><input type="text" name="capacity_building_students[{{ $i }}][details]" class="form-control form-control-sm" value="{{ $s['details'] ?? '' }}"></td>
                                    <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="row-num" style="text-align:center;">1</td>
                                    <td><input type="text" name="capacity_building_students[0][name]" class="form-control form-control-sm"></td>
                                    <td><input type="text" name="capacity_building_students[0][details]" class="form-control form-control-sm"></td>
                                    <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <span class="add-row" onclick="addStudent()"><i class="fas fa-plus-circle"></i> Add Student</span>
                    </div>
                </div>
            </div>

            <h4>ACTION PLAN FOR THE NEXT SIX MONTHS</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-form table-sm" id="actionPlanTable">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>List of Aims</th>
                            <th>Action Plan</th>
                            <th style="width:40px;"><i class="fas fa-trash"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $plans = json_decode($report->action_plan ?? '[]', true); @endphp
                        @forelse($plans as $i => $ap)
                        <tr>
                            <td class="row-num" style="text-align:center;">{{ $i + 1 }}</td>
                            <td><textarea name="action_plan[{{ $i }}][aim]" class="form-control form-control-sm" rows="2">{{ $ap['aim'] ?? '' }}</textarea></td>
                            <td><textarea name="action_plan[{{ $i }}][plan]" class="form-control form-control-sm" rows="2">{{ $ap['plan'] ?? '' }}</textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @empty
                        <tr>
                            <td class="row-num" style="text-align:center;">1</td>
                            <td><textarea name="action_plan[0][aim]" class="form-control form-control-sm" rows="2"></textarea></td>
                            <td><textarea name="action_plan[0][plan]" class="form-control form-control-sm" rows="2"></textarea></td>
                            <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <span class="add-row" onclick="addActionPlan()"><i class="fas fa-plus-circle"></i> Add Action Plan</span>
            </div>

            <h4>ETHICAL AND REGULATORY REQUIREMENTS</h4>
            <textarea name="ethical_requirements" class="form-control form-control-sm" rows="3">{{ $report->ethical_requirements ?? '' }}</textarea>

            <h4>POTENTIAL DIFFICULTIES (IF ANY)</h4>
            <textarea name="potential_difficulties" class="form-control form-control-sm" rows="3">{{ $report->potential_difficulties ?? '' }}</textarea>

            <h4>CONTRIBUTION OF COLLABORATOR (IF APPLICABLE)</h4>
            <textarea name="collaborator_contribution" class="form-control form-control-sm" rows="3">{{ $report->collaborator_contribution ?? '' }}</textarea>

            <h4>APPENDIX</h4>
            <textarea name="appendix" class="form-control form-control-sm" rows="3">{{ $report->appendix ?? '' }}</textarea>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-teal btn-lg" id="saveBtn" style="min-width:220px;">
                    <span id="saveBtnText"><i class="fas fa-save"></i> Save Progress Report</span>
                    <span id="saveBtnLoading" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    <span id="saveBtnDone" style="display:none; color:#fff;"><i class="fas fa-check-circle"></i> Saved Successfully</span>
                </button>
                <button type="button" class="btn btn-secondary btn-lg ms-2" id="cancelBtn" onclick="resetForm()">Cancel</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        /**
         * Reset the form back to its original saved state
         */
        function resetForm() {
            // Re-enable save button and reset its appearance
            $('#saveBtn').prop('disabled', false);
            $('#saveBtnText').show();
            $('#saveBtnLoading').hide();
            $('#saveBtnDone').hide();
            $('#saveBtn').removeClass('btn-success').addClass('btn-teal');

            // Reload the iframe content (re-fetch from server to get saved state)
            if (window.parent) {
                var p_id = '{{ $project->id }}';
                var url = '{{ url("/") }}/progressReport/edit/' + p_id;
                window.location.href = url;
            }
        }

        // Initialize indices
        function reindexRows(tableId) {
            const table = document.getElementById(tableId);
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                const numCell = row.querySelector('.row-num');
                if (numCell) numCell.textContent = index + 1;

                const inputs = row.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const parts = name.match(/^([^\[]+)\[(\d+)\](\[.*\])?$/);
                        if (parts) {
                            input.setAttribute('name', parts[1] + '[' + index + ']' + (parts[3] || ''));
                        }
                    }
                });
            });
        }

        function removeRow(element) {
            const row = element.closest('tr');
            const table = row.closest('table');
            row.remove();
            reindexRows(table.id);
        }

        function addRow(tableId, templateFn) {
            const table = document.getElementById(tableId);
            const tbody = table.querySelector('tbody');
            const rowCount = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = templateFn(rowCount);
            tbody.appendChild(tr);
            reindexRows(tableId);
        }

        function addSpecificAim() {
            addRow('specificAimsTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><input type="text" name="specific_aims[${i}][aim]" class="form-control form-control-sm"></td>
                <td>
                    <select name="specific_aims[${i}][status]" class="form-control form-control-sm">
                        <option value="">Select</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </td>
                <td><input type="date" name="specific_aims[${i}][date]" class="form-control form-control-sm"></td>
                <td><textarea name="specific_aims[${i}][comments]" class="form-control form-control-sm" rows="1"></textarea></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        function addOutcome() {
            addRow('outcomesTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><input type="text" name="committed_outcomes[${i}][outcome]" class="form-control form-control-sm"></td>
                <td><input type="number" name="committed_outcomes[${i}][committed]" class="form-control form-control-sm"></td>
                <td><input type="number" name="committed_outcomes[${i}][achieved]" class="form-control form-control-sm"></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        function addPublication() {
            addRow('publicationsTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><textarea name="publications_list[${i}][text]" class="form-control form-control-sm" rows="2"></textarea></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        function addRA() {
            addRow('raTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><input type="text" name="capacity_building_ras[${i}][name]" class="form-control form-control-sm"></td>
                <td><input type="text" name="capacity_building_ras[${i}][details]" class="form-control form-control-sm"></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        function addStudent() {
            addRow('studentsTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><input type="text" name="capacity_building_students[${i}][name]" class="form-control form-control-sm"></td>
                <td><input type="text" name="capacity_building_students[${i}][details]" class="form-control form-control-sm"></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        function addActionPlan() {
            addRow('actionPlanTable', (i) => `
                <td class="row-num" style="text-align:center;">${i + 1}</td>
                <td><textarea name="action_plan[${i}][aim]" class="form-control form-control-sm" rows="2"></textarea></td>
                <td><textarea name="action_plan[${i}][plan]" class="form-control form-control-sm" rows="2"></textarea></td>
                <td style="text-align:center;"><span class="delete-row" onclick="removeRow(this)"><i class="fas fa-times"></i></span></td>
            `);
        }

        // Form submission via AJAX
        $('#progressReportForm').on('submit', function(e) {
            e.preventDefault();
            $('#saveBtn').prop('disabled', true);
            $('#saveBtnText').hide();
            $('#saveBtnLoading').show();
            $('#saveBtnDone').hide();
            $('#saveBtn').removeClass('btn-success').addClass('btn-teal');

            var formData = $(this).serialize();
            var projectId = '{{ $project->id }}';

            $.ajax({
                url: '{{ url("/") }}/progressReport/save/' + projectId,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Show completed status on the button
                        $('#saveBtnLoading').hide();
                        $('#saveBtnDone').show();
                        $('#saveBtn').removeClass('btn-teal').addClass('btn-success');

                        // Wait 1.2 seconds so user sees the success message, then close & reload
                        setTimeout(function() {
                            $('#progressReportModal').modal('hide');
                            $('.modal-backdrop').remove();
                            window.location.reload();
                        }, 1200);
                    }
                },
                error: function(xhr) {
                    alert('Error saving progress report. Please try again.');
                    $('#saveBtn').prop('disabled', false);
                    $('#saveBtnText').show();
                    $('#saveBtnLoading').hide();
                    $('#saveBtnDone').hide();
                    $('#saveBtn').removeClass('btn-success').addClass('btn-teal');
                }
            });
        });
    </script>
</body>
</html>
