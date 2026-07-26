@extends('layouts.app')
@section('title', 'Reviewer Grading')
@section('content')

<style>
    :root {
        --color-brand-50: #fbeef1;
        --color-brand-100: #f3d2da;
        --color-brand-200: #e6a5b6;
        --color-brand-300: #d3738f;
        --color-brand-400: #b8496b;
        --color-brand-500: #8d1b3d;
        --color-brand-600: #7a1636;
        --color-brand-700: #63102b;
        --color-brand-800: #4c0c21;
        --color-brand-900: #350818;
        --color-sand-50: #faf7f0;
        --color-sand-100: #f2ead6;
        --color-sand-200: #e4d3ac;
        --color-sand-300: #d3b57c;
        --color-sand-400: #c39c58;
        --color-sand-500: #ab8140;
        --color-sand-600: #8c6733;
        --color-sand-700: #6d4f29;
        --color-sand-800: #503a1e;
        --color-sand-900: #362715;
        --color-gold-400: #e3b04b;
        --color-gold-500: #cf9a2f;
        --color-gold-600: #a97b22;
        --color-ink-50: #f7f7f8;
        --color-ink-100: #eeedf0;
        --color-ink-200: #d8d6dc;
        --color-ink-300: #b4b0ba;
        --color-ink-400: #8b8592;
        --color-ink-500: #675f6e;
        --color-ink-600: #4c4553;
        --color-ink-700: #38333e;
        --color-ink-800: #241f2a;
        --color-ink-900: #16131a;
        --color-success: #1f8a5f;
        --color-warning: #cf9a2f;
        --color-danger: #b3261e;
        --color-info: #2563a8;
        --fluent-depth-2: 0 1px 2px rgba(22,19,26,.07), 0 0px 1px rgba(22,19,26,.06);
        --fluent-depth-4: 0 2px 4px rgba(22,19,26,.09), 0 0px 2px rgba(22,19,26,.07);
        --fluent-depth-8: 0 4px 8px rgba(22,19,26,.12), 0 0px 2px rgba(22,19,26,.08);
        --fluent-depth-16: 0 8px 16px rgba(22,19,26,.16), 0 0px 2px rgba(22,19,26,.10);
    }

    * {
        font-family: 'Inter', 'Segoe UI Variable', 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
    }

    /* ===== Reviewer Info Bar ===== */
    .reviewer-info-bar {
        border: 1px solid var(--color-brand-500);
        border-radius: 8px;
        background: var(--color-sand-50);
        padding: 18px;
        position: relative;
        box-shadow: var(--fluent-depth-2);
    }
    .reviewer-info-bar .heading-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: var(--color-brand-500);
        color: #fff;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .reviewer-stat-item {
        text-align: center;
        border-right: 1px solid var(--color-ink-200);
        padding: 6px 18px;
    }
    .reviewer-stat-item:last-child { border-right: none; }
    .reviewer-stat-item .stat-value { font-weight: 700; font-size: 18px; color: var(--color-brand-500); }
    .reviewer-stat-item .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-ink-400); }

    .btn-detail {
        background: var(--color-brand-500);
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: var(--fluent-depth-2);
        transition: all 0.15s ease;
    }
    .btn-detail:hover {
        background: var(--color-brand-600);
        color: #fff;
        box-shadow: var(--fluent-depth-4);
    }

    .main-content-row {
        display: flex;
        gap: 20px;
        margin-top: 18px;
    }
    .left-panel { flex: 4; }
    .right-panel { flex: 1; min-width: 240px; }

    /* ===== Research Call Selector ===== */
    .program-selector-card {
        border: 1px solid var(--color-brand-500);
        border-radius: 8px;
        position: relative;
        padding: 20px;
        background: #fff;
        box-shadow: var(--fluent-depth-2);
    }
    .program-selector-card .heading-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: var(--color-brand-500);
        color: #fff;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .program-selector-card select {
        border-radius: 6px;
        border: 1px solid var(--color-ink-200);
        font-size: 13px;
        padding: 7px 12px;
    }
    .program-selector-card select:focus {
        border-color: var(--color-brand-500);
        box-shadow: 0 0 0 2px rgba(141,27,61,.15);
    }

    /* ===== Projects Section ===== */
    .projects-section {
        border: 1px solid var(--color-brand-500);
        border-radius: 8px;
        position: relative;
        padding: 20px;
        background: #fff;
        box-shadow: var(--fluent-depth-2);
        margin-top: 18px;
    }
    .projects-section .heading-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: var(--color-brand-500);
        color: #fff;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    /* ===== Rating Form ===== */
    .rating-form-card {
        border: 1px solid var(--color-brand-500);
        border-radius: 8px;
        background: var(--color-sand-50);
        position: relative;
        padding: 22px 16px 16px;
        box-shadow: var(--fluent-depth-2);
        height: fit-content;
        position: sticky;
        top: 80px;
    }
    .rating-form-card .heading-label {
        position: absolute;
        top: -12px;
        left: 20px;
        background: var(--color-brand-500);
        color: #fff;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .rating-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        gap: 8px;
    }
    .rating-row label {
        font-size: 12px;
        font-weight: 600;
        color: var(--color-ink-600);
        white-space: nowrap;
        min-width: 110px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .star-rating {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 1px;
    }
    .star-rating input { display: none; }
    .star-rating label {
        cursor: pointer;
        font-size: 16px;
        color: var(--color-ink-200);
        transition: color 0.1s ease;
        min-width: auto;
        text-transform: none;
        letter-spacing: 0;
        line-height: 1;
        padding: 0 1px;
    }
    .star-rating label::before { content: "\2605"; }
    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: var(--color-brand-500);
    }

    .rating-value {
        font-size: 12px;
        font-weight: 700;
        color: var(--color-brand-500);
        min-width: 20px;
        text-align: center;
    }

    .rating-msg {
        font-size: 12px;
        color: var(--color-ink-400);
        display: block;
        margin-top: 4px;
    }
    .rating-msg.success { color: var(--color-success); }

    .btn-save-rating {
        background: var(--color-brand-500);
        color: #fff;
        border: 1px solid var(--color-brand-600);
        border-radius: 6px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        box-shadow: var(--fluent-depth-2);
        width: 100%;
        transition: all 0.15s ease;
    }
    .btn-save-rating:hover {
        background: var(--color-brand-600);
        box-shadow: var(--fluent-depth-4);
        color: #fff;
    }
    .btn-save-rating:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== Project Table ===== */
    .project-table-card {
        border: 1px solid var(--color-ink-100);
        border-radius: 8px;
        background: #fff;
        padding: 14px;
        box-shadow: var(--fluent-depth-2);
        margin-bottom: 14px;
        transition: box-shadow 0.15s ease;
    }
    .project-table-card:hover {
        box-shadow: var(--fluent-depth-8);
        border-color: var(--color-ink-200);
    }
    .project-table-card .table {
        margin-bottom: 0;
        font-size: 12.5px;
    }
    .project-table-card .table th {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--color-ink-400);
        font-weight: 600;
        background: transparent;
        border-bottom: 2px solid var(--color-ink-100);
    }
    .project-id-cell {
        font-weight: 700;
        color: var(--color-brand-500);
        font-size: 14px;
        text-align: center;
        vertical-align: middle;
        width: 80px;
        background: var(--color-sand-50);
    }
    .grading-cell {
        background: var(--color-sand-50);
        text-align: center;
        vertical-align: middle;
        font-size: 12px;
    }
    .grading-cell .grade {
        font-weight: 700;
        color: var(--color-brand-500);
    }
    .grading-cell .comment {
        font-size: 11px;
        color: var(--color-ink-400);
        font-style: italic;
    }
    .not-reviewed-badge {
        background: var(--color-warning);
        color: #fff;
        border-radius: 6px;
        padding: 2px 10px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: var(--color-ink-400);
    }
    .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; color: var(--color-ink-200); }
    .empty-state .empty-text { font-size: 14px; font-weight: 500; }
    .empty-state .empty-sub { font-size: 12px; color: var(--color-ink-300); }

    @media (max-width: 992px) {
        .main-content-row { flex-direction: column; }
        .right-panel { flex: none; width: 100%; }
        .rating-form-card { position: static; }
    }
</style>

<div class="row">
    <div class="col-md-12">
        {{-- Reviewer Information Bar --}}
        <div class="reviewer-info-bar">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex" style="flex: 1;">
                    <div class="reviewer-stat-item">
                        <div class="stat-value">{{ $user->name }}</div>
                        <div class="stat-label">{{ $user->email }}</div>
                    </div>
                    <div class="reviewer-stat-item">
                        <div class="stat-value">{{ $totalProjects }}</div>
                        <div class="stat-label">Total Projects</div>
                    </div>
                    <div class="reviewer-stat-item">
                        <div class="stat-value" id="totalPrograms">{{ count($programs) }}</div>
                        <div class="stat-label">Research Calls</div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('reviewer-grading.detail', ['u_id' => $user->id]) }}" class="btn-detail">
                        <i class="fas fa-chart-line"></i> View Details
                    </a>
                </div>
            </div>
            <div class="heading-label">Reviewer Information</div>
        </div>

        {{-- Main Content: 80/20 Split --}}
        <div class="main-content-row">

            {{-- LEFT PANEL (80%) --}}
            <div class="left-panel">

                {{-- Research Call Selector --}}
                <div class="program-selector-card">
                    <div class="row align-items-center">
                        <div class="col-md-6" style="border-right:1px solid var(--color-ink-200); padding-right:24px;">
                            <div style="font-size:12px; font-weight:600; color:var(--color-ink-500); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:6px;">
                            <i class="fas fa-layer-group"></i> Select Research Call
                            </div>
                            <select class="form-select" id="programDropdown" style="width:100%;">
                                <option value="">— Select a Research Call —</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->program_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" style="padding-left:20px;">
                            <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                                <div style="font-size:12px; color:var(--color-ink-500);">
                                    <strong style="color:var(--color-brand-500);">Selected:</strong>
                                    <span id="programTitle">—</span>
                                </div>
                                <div style="font-size:12px; color:var(--color-ink-500);">
                                    <strong style="color:var(--color-brand-500);">Projects:</strong>
                                    <span id="programProjectCount">0</span>
                                </div>
                                <div style="font-size:12px; color:var(--color-ink-500);">
                                    <strong style="color:var(--color-brand-500);">Reviewed:</strong>
                                    <span id="statReviewed">0</span>
                                </div>
                                <div style="font-size:12px; color:var(--color-ink-500);">
                                    <strong style="color:var(--color-brand-500);">Pending:</strong>
                                    <span id="statPending">0</span>
                                </div>
                                <div style="font-size:12px; color:var(--color-ink-500);">
                                    <strong style="color:var(--color-brand-500);">Avg:</strong>
                                    <span id="statAvgRating">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="heading-label">Research Call Selection</div>
                </div>

                {{-- Projects List --}}
                <div class="projects-section">
                    <div id="projectsContainer">
                        @include('admin.partials.reviewer-projects-empty')
                    </div>
                    <div class="heading-label">Projects in Research Call</div>
                </div>
            </div>

            {{-- RIGHT PANEL (20%) — Rating Form --}}
            <div class="right-panel">
                <div class="rating-form-card">
                    <form method="POST" action="{{ route('reviewer-grading.save-ratings') }}" id="ratingForm">
                        @csrf
                        <input type="hidden" name="reviewer" value="{{ $user->id }}">
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                        <input type="hidden" id="program_id" name="program_id" value="">

                        <div class="rating-row">
                            <label>Conflict</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="star-rating" data-for="conflict">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="conflict-{{ $i }}" name="conflict" value="{{ $i }}">
                                        <label for="conflict-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rating-value" id="conflict-val">0</span>
                            </div>
                        </div>

                        <div class="rating-row">
                            <label>Responsiveness</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="star-rating" data-for="responsiveness">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="responsiveness-{{ $i }}" name="responsiveness" value="{{ $i }}">
                                        <label for="responsiveness-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rating-value" id="responsiveness-val">0</span>
                            </div>
                        </div>

                        <div class="rating-row">
                            <label>Comprehensiveness</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="star-rating" data-for="comprehensiveness">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="comprehensiveness-{{ $i }}" name="comprehensiveness" value="{{ $i }}">
                                        <label for="comprehensiveness-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rating-value" id="comprehensiveness-val">0</span>
                            </div>
                        </div>

                        <div class="rating-row">
                            <label>No. of Reviews</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="star-rating" data-for="no_reviewers">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="no_reviewers-{{ $i }}" name="no_reviewers" value="{{ $i }}">
                                        <label for="no_reviewers-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rating-value" id="no_reviewers-val">0</span>
                            </div>
                        </div>

                        <div class="rating-row">
                            <label>Behaviour</label>
                            <div class="d-flex align-items-center gap-1">
                                <div class="star-rating" data-for="behaviour">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="behaviour-{{ $i }}" name="behaviour" value="{{ $i }}">
                                        <label for="behaviour-{{ $i }}"></label>
                                    @endfor
                                </div>
                                <span class="rating-value" id="behaviour-val">0</span>
                            </div>
                        </div>

                        <hr style="border-color: var(--color-ink-200); margin: 12px 0;">

                        <span class="rating-msg" id="ratingStatus"></span>
                        <span class="rating-msg success" id="ratingAvgDisplay"></span>
                        <span class="rating-msg" id="flashMsg">
                            @if (session('successrating'))
                                {!! session('successrating') !!}
                                @php session()->forget('successrating'); @endphp
                            @endif
                        </span>

                        <button type="submit" class="btn-save-rating mt-2" id="saveRatingBtn" disabled>
                            <i class="fas fa-save"></i> Save Ratings
                        </button>
                    </form>
                    <div class="heading-label">Rate Reviewer <span id="avgRatingLabel" style="font-weight:400;font-size:10px;opacity:0.8;"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function updateRatingDisplay(field) {
        var val = $('input[name="' + field + '"]:checked').val() || 0;
        $('#' + field + '-val').text(val);
    }

    function updateAvgLabel() {
        var sum = 0, count = 0;
        ['conflict', 'responsiveness', 'comprehensiveness', 'no_reviewers', 'behaviour'].forEach(function(f) {
            var val = parseInt($('input[name="' + f + '"]:checked').val() || 0);
            sum += val;
            if (val > 0) count++;
        });
        var avg = count > 0 ? (sum / 5).toFixed(1) : '0.0';
        $('#avgRatingLabel').text('— Avg: ' + avg + ' / 5');
        return avg;
    }

    $('.star-rating input[type="radio"]').on('change', function() {
        var name = $(this).attr('name');
        updateRatingDisplay(name);
        updateAvgLabel();
    });

    ['conflict', 'responsiveness', 'comprehensiveness', 'no_reviewers', 'behaviour'].forEach(function(f) {
        updateRatingDisplay(f);
    });
    updateAvgLabel();

    // ─── Research Call dropdown change ───
    $('#programDropdown').change(function() {
        var programId = $(this).val();
        $('#program_id').val(programId);
        var userId = '{{ $user->id }}';

        if (!programId) {
            $('#projectsContainer').html(`
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-folder-open"></i></div>
                    <div class="empty-text">Select a Research Call</div>
                    <div class="empty-sub">Choose a research call above to see the reviewer's assigned projects</div>
                </div>
            `);
            $('#programTitle').text('—');
            $('#programProjectCount').text('0');
            $('#saveRatingBtn').prop('disabled', true);
            return;
        }

        $('#programTitle').text($('#programDropdown option:selected').text());

        $.ajax({
            url: '{{ route('ajaxListreviewerGrading') }}',
            type: 'GET',
            data: {
                program_id: programId,
                user_id: userId
            },
            success: function(response) {
                var projects = response.projects || [];
                var ratings = response.ratings;

                $('#programProjectCount').text(projects.length);

                var reviewedCount = 0;
                var pendingCount = 0;
                $.each(projects, function(idx, proj) {
                    if (proj.gradeA !== null) {
                        reviewedCount++;
                    } else {
                        pendingCount++;
                    }
                });
                $('#statReviewed').text(reviewedCount);
                $('#statPending').text(pendingCount);

                if (projects.length === 0) {
                    $('#projectsContainer').html(`
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                            <div class="empty-text">No Projects Found</div>
                            <div class="empty-sub">This reviewer has no projects assigned in the selected program</div>
                        </div>
                    `);
                } else {
                    var html = '';
                    $.each(projects, function(idx, proj) {
                        var isGraded = proj.gradeA !== null;
                        var hasProgress = proj.progressGrade !== null && proj.progressGrade !== undefined;

                        html += '<div class="project-table-card">';

                        if (!isGraded) {
                            var deadline = new Date(proj.deadline);
                            deadline.setTime(deadline.getTime() + (2 * 7 * 24 * 60 * 60 * 1000));
                            var newDeadline = deadline.toISOString().slice(0,19).replace('T',' ');

                            html += '<table class="table table-bordered table-sm" style="margin:0;">';
                            html += '<tr>';
                            html += '<th class="project-id-cell" rowspan="3" style="width:80px;">' + (proj.old_project_id || proj.id) + '</th>';
                            html += '<th style="width:180px;font-size:11px;">Review Status</th>';
                            html += '<td><span class="not-reviewed-badge">⏳ Not Yet Reviewed</span></td>';
                            html += '</tr>';
                            html += '<tr>';
                            html += '<th>Proposal Status</th>';
                            html += '<td>' + (proj.proposalstatus || 'Accepted') + '</td>';
                            html += '</tr>';
                            html += '<tr>';
                            html += '<th>Feedback Deadline</th>';
                            html += '<td>' + newDeadline + '</td>';
                            html += '</tr>';
                            html += '</table>';
                        } else {
                            // ─── Graded project: show the actual data properly ───
                            html += '<table class="table table-bordered table-sm" style="margin:0;">';

                            // Header row
                            html += '<thead>';
                            html += '<tr>';
                            html += '<th class="project-id-cell" rowspan="2" style="vertical-align:middle;width:70px;">' + (proj.old_project_id || proj.id) + '</th>';
                            html += '<th style="font-size:11px;background:var(--color-sand-50);">Achievements</th>';
                            html += '<th style="font-size:11px;background:var(--color-sand-50);">Publications</th>';
                            html += '<th style="font-size:11px;background:var(--color-sand-50);">Student Involvement</th>';
                            html += '<th style="font-size:11px;background:var(--color-sand-50);">Project Impact / Budget</th>';
                            html += '</tr>';
                            html += '</thead>';

                            html += '<tbody>';

                            // Final Report row
                            html += '<tr>';
                            html += '<th style="font-size:10px;text-align:center;vertical-align:middle;background:var(--color-brand-500);color:#fff;width:90px;">Final Report</th>';
                            html += '<td class="grading-cell"><span class="grade">' + (proj.gradeA ?? '-') + '</span><br><span class="comment">' + (proj.commentA ?? '') + '</span></td>';
                            html += '<td class="grading-cell"><span class="grade">' + (proj.gradeB ?? '-') + '</span><br><span class="comment">' + (proj.commentB ?? '') + '</span></td>';
                            html += '<td class="grading-cell"><span class="grade">' + (proj.gradeD ?? '-') + '</span><br><span class="comment">' + (proj.commentD ?? '') + '</span></td>';
                            html += '<td class="grading-cell"><span class="grade">' + (proj.gradeC ?? '-') + '</span><br><span class="comment">' + (proj.commentC ?? '') + '</span></td>';
                            html += '</tr>';

                            // Progress Report row (if exists)
                            if (hasProgress) {
                                html += '<tr>';
                                html += '<th style="font-size:10px;text-align:center;vertical-align:middle;background:var(--color-sand-500);color:#fff;">Progress Report</th>';
                                html += '<td class="grading-cell"><span class="grade">' + (proj.achievementsRating ?? '-') + '</span><br><span class="comment">' + (proj.achievementsComments ?? '-') + '</span></td>';
                                html += '<td class="grading-cell"><span class="grade">' + (proj.publicationsRating ?? '-') + '</span><br><span class="comment">' + (proj.publicationsComments ?? '-') + '</span></td>';
                                html += '<td class="grading-cell"><span class="grade">' + (proj.studentsRating ?? '-') + '</span><br><span class="comment">' + (proj.studentsComments ?? '-') + '</span></td>';
                                html += '<td class="grading-cell"><span class="grade">' + (proj.budgetRating ?? '-') + '</span><br><span class="comment">' + (proj.budgetComments ?? '-') + '</span></td>';
                                html += '</tr>';
                            }

                            html += '</tbody>';
                            html += '</table>';
                        }

                        html += '</div>';
                    });
                    $('#projectsContainer').html(html);
                }

                // Handle ratings
                if (ratings) {
                    setRatingValues(ratings);
                    $('#ratingStatus').text('✅ Rating already set for this research call').css('color', 'var(--color-brand-500)');
                    var avg = ((parseInt(ratings.conflict || 0) + parseInt(ratings.responsiveness || 0) + parseInt(ratings.comprehensiveness || 0) + parseInt(ratings.no_reviewers || 0) + parseInt(ratings.behaviour || 0)) / 5).toFixed(1);
                    $('#ratingAvgDisplay').html('Average Rating: <strong>' + avg + '</strong> / 5');
                    $('#statAvgRating').text(avg);
                    updateAvgLabel();
                    $('#flashMsg').html('');
                    $('#saveRatingBtn').prop('disabled', false);
                } else {
                    resetRatingValues();
                    $('#ratingStatus').text('');
                    $('#ratingAvgDisplay').text('');
                    $('#statAvgRating').text('—');
                    $('#avgRatingLabel').text('');
                    $('#flashMsg').html('');
                    $('#saveRatingBtn').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                $('#projectsContainer').html(`
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-exclamation-triangle" style="color:var(--color-danger);"></i></div>
                        <div class="empty-text">Error loading projects</div>
                        <div class="empty-sub">Please try again</div>
                    </div>
                `);
            }
        });
    });

    function setRatingValues(ratings) {
        var fields = ['conflict', 'responsiveness', 'comprehensiveness', 'no_reviewers', 'behaviour'];
        $.each(fields, function(i, f) {
            var val = ratings[f] || 0;
            $('input[name="' + f + '"][value="' + val + '"]').prop('checked', true);
            $('#' + f + '-val').text(val);
        });
    }

    function resetRatingValues() {
        var fields = ['conflict', 'responsiveness', 'comprehensiveness', 'no_reviewers', 'behaviour'];
        $.each(fields, function(i, f) {
            $('input[name="' + f + '"]').prop('checked', false);
            $('#' + f + '-val').text('0');
        });
    }

    $('#ratingForm').on('submit', function(e) {
        var progId = $('#program_id').val();
        if (!progId) {
            e.preventDefault();
            alert('Please select a research call first.');
            return;
        }
    });
});
</script>
@endpush
