<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Report Card — {{ $project->old_project_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 11px; color: #333; line-height: 1.5; padding: 24px; background: #fff; }
        
        /* Print styles */
        @media print {
            body { padding: 0; font-size: 10px; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: avoid; }
            .section { page-break-inside: avoid; }
        }
        
        /* Header */
        .header { border-bottom: 1px solid #ccc; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; font-weight: 700; color: #333; margin: 0 0 4px; }
        .header .subtitle { font-size: 12px; color: #666; }
        .header .meta { display: flex; gap: 16px; margin-top: 8px; font-size: 10px; color: #666; }
        .header .meta span { display: inline-flex; align-items: center; gap: 4px; }
        
        /* Sections */
        .section { margin-bottom: 18px; }
        .section-title { font-size: 11px; font-weight: 700; color: #333; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Tables - hairline with rounded corners */
        table { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #ddd; border-radius: 6px; overflow: hidden; }
        th { text-align: left; padding: 5px 8px; font-weight: 600; font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; color: #555; border-bottom: 1px solid #ddd; background: transparent; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: transparent; }
        
        /* Badges */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8px; font-weight: 600; text-transform: uppercase; border: 1px solid; }
        .badge-success { border-color: #2e7d32; color: #2e7d32; background: transparent; }
        .badge-warning { border-color: #ef6c00; color: #ef6c00; background: transparent; }
        .badge-danger { border-color: #c62828; color: #c62828; background: transparent; }
        .badge-info { border-color: #1565c0; color: #1565c0; background: transparent; }
        .badge-neutral { border-color: #888; color: #888; background: transparent; }
        
        /* Icons */
        .icon { width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 3px; }
        .icon-check { color: #2e7d32; }
        .icon-cross { color: #c62828; }
        .icon-pending { color: #ef6c00; }
        
        /* Grid */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
        
        /* Info boxes - no shading */
        .info-box { border: 1px solid #ddd; border-radius: 6px; padding: 8px 10px; margin-bottom: 8px; }
        .info-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.3px; color: #888; font-weight: 600; }
        .info-box .value { font-size: 11px; color: #333; font-weight: 500; margin-top: 2px; }
        
        /* Grade display */
        .grade-box { text-align: center; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        .grade-box .grade { font-size: 22px; font-weight: 700; color: #333; }
        .grade-box .label { font-size: 9px; text-transform: uppercase; color: #888; margin-top: 2px; }
        
        /* Footer */
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 9px; color: #999; text-align: center; }
        
        /* Print button */
        .print-btn { position: fixed; top: 20px; right: 20px; background: #333; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; z-index: 1000; }
        .print-btn:hover { background: #555; }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report Card
    </button>

    {{-- Header --}}
    <div class="header">
        <h1>Project Report Card</h1>
        <div class="subtitle">{{ $project->title }}</div>
        <div class="meta">
            <span><strong>ID:</strong> {{ $project->old_project_id }}</span>
            <span><strong>Grant:</strong> {{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</span>
            <span><strong>Program:</strong> {{ $project->program->program_title ?? '—' }}</span>
            <span><strong>LPI:</strong> {{ $project->lpi->name ?? '—' }} ({{ $project->lpi->email ?? '—' }})</span>
            <span><strong>Date:</strong> {{ now()->format('M d, Y') }}</span>
        </div>
    </div>

    {{-- Core Information --}}
    <div class="section">
        <div class="section-title">Core Information</div>
        <div class="grid-3">
            <div class="info-box">
                <div class="label">Project Title</div>
                <div class="value">{{ $project->title }}</div>
            </div>
            <div class="info-box">
                <div class="label">Program</div>
                <div class="value">{{ $project->program->program_title ?? '—' }}</div>
            </div>
            <div class="info-box">
                <div class="label">Grant</div>
                <div class="value">{{ $project->grant->grant_name ?? $project->program->grant->grant_name ?? '—' }}</div>
            </div>
        </div>
        <div class="grid-3">
            <div class="info-box">
                <div class="label">Pillars</div>
                <div class="value">{{ $project->pillars->pluck('pillar_name')->implode(', ') ?: '—' }}</div>
            </div>
            <div class="info-box">
                <div class="label">Colleges</div>
                <div class="value">{{ $project->colleges->pluck('name')->implode(', ') ?: '—' }}</div>
            </div>
            <div class="info-box">
                <div class="label">Status</div>
                <div class="value">
                    @php $status = $project->latestStatus->status ?? 'Unknown'; @endphp
                    <span class="badge {{ in_array($status, ['Graded', 'Completed']) ? 'badge-success' : (in_array($status, ['rejected', 'progress_rejected']) ? 'badge-danger' : 'badge-info') }}">
                        {{ $status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Commitments --}}
    @if($commitment)
    <div class="section">
        <div class="section-title">Commitments</div>
        <div class="grid-3">
            <div class="info-box">
                <div class="label">Journal Articles (Q1-Q4)</div>
                <div class="value">{{ ($commitment->q1article ?? 0) + ($commitment->q2article ?? 0) + ($commitment->q3article ?? 0) + ($commitment->q4article ?? 0) }}</div>
            </div>
            <div class="info-box">
                <div class="label">Conference Papers</div>
                <div class="value">{{ $commitment->confArticle ?? 0 }}</div>
            </div>
            <div class="info-box">
                <div class="label">Books</div>
                <div class="value">{{ ($commitment->books ?? 0) + ($commitment->editBooks ?? 0) }}</div>
            </div>
            <div class="info-box">
                <div class="label">Book Chapters</div>
                <div class="value">{{ $commitment->chapters ?? 0 }}</div>
            </div>
            <div class="info-box">
                <div class="label">IP Disclosure</div>
                <div class="value">{{ $commitment->ip ?? 0 }}</div>
            </div>
            <div class="info-box">
                <div class="label">Patents</div>
                <div class="value">{{ ($commitment->filedPatent ?? 0) + ($commitment->grantedPatent ?? 0) }}</div>
            </div>
            <div class="info-box">
                <div class="label">Open Source SW</div>
                <div class="value">{{ $commitment->openSourceSW ?? 0 }}</div>
            </div>
            <div class="info-box">
                <div class="label">Startups</div>
                <div class="value">{{ $commitment->startUp ?? 0 }}</div>
            </div>
            <div class="info-box">
                <div class="label">Students</div>
                <div class="value">M: {{ $commitment->master ?? 0 }} | UG: {{ $commitment->UG ?? 0 }} | PhD: {{ $commitment->Phd ?? 0 }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Publications --}}
    @php
        $pubTypes = ['journal_q1','journal_q2','journal_q3','journal_q4','conference','book','edited_book','book_chapter'];
        $publicationOutcomes = $outcomes->filter(fn($o) => in_array($o->type, $pubTypes));
    @endphp
    @if($publicationOutcomes->count())
    <div class="section">
        <div class="section-title">Publications ({{ $publicationOutcomes->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Journal/Book</th>
                    <th>Year</th>
                    <th>DOI</th>
                    <th>Verified</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publicationOutcomes as $o)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="badge badge-info">{{ strtoupper(str_replace('_', ' ', $o->type)) }}</span></td>
                    <td>{{ $o->publication->publication_title ?? $o->identifier ?? '—' }}</td>
                    <td>{{ $o->publication->journal ?? '—' }}</td>
                    <td>{{ $o->publication->year ?? '—' }}</td>
                    <td>{{ $o->publication->doi ?? '—' }}</td>
                    <td>
                        @if($o->publication)
                            <span class="icon icon-check">✓</span>
                        @else
                            <span class="icon icon-pending">○</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Students --}}
    @if($students->count())
    <div class="section">
        <div class="section-title">Students ({{ $students->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Major</th>
                    <th>College</th>
                    <th>Days</th>
                    <th>Verified</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="badge badge-info">{{ $s->type }}</span></td>
                    <td>{{ $s->std_id ?? '—' }}</td>
                    <td>{{ $s->details->full_name ?? '—' }}</td>
                    <td>{{ $s->details->major ?? '—' }}</td>
                    <td>{{ $s->details->college ?? '—' }}</td>
                    <td>{{ $s->days ?? '—' }}</td>
                    <td>
                        @if($s->details)
                            <span class="icon icon-check">✓</span>
                        @else
                            <span class="icon icon-pending">○</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Researchers --}}
    @if($researchers->count())
    <div class="section">
        <div class="section-title">Researchers ({{ $researchers->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($researchers as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r->name ?? '—' }}</td>
                    <td>{{ $r->category ?? '—' }}</td>
                    <td>{{ $r->days ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Progress Grading --}}
    @if($progressGrading && $progressGrading->publish !== 'pending')
    <div class="section">
        <div class="section-title">Progress Report Grading</div>
        <div class="grid-2">
            <div>
                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="badge {{ $progressGrading->isAccepted == 1 ? 'badge-success' : 'badge-danger' }}">
                            {{ $progressGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Rating</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Achievements</td>
                            <td><strong>{{ $progressGrading->achievementsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progressGrading->achievementsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Publications</td>
                            <td><strong>{{ $progressGrading->publicationsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progressGrading->publicationsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Students</td>
                            <td><strong>{{ $progressGrading->studentsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progressGrading->studentsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Budget</td>
                            <td><strong>{{ $progressGrading->budgetRating ?? '—' }}/5</strong></td>
                            <td>{{ $progressGrading->budgetComments ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <div class="info-box">
                    <div class="label">Ethical Approval</div>
                    <div class="value">
                        <span class="badge {{ $progressGrading->ethical ? 'badge-success' : 'badge-neutral' }}">
                            {{ $progressGrading->ethical ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
                @if($progressGrading->analysis)
                <div class="info-box">
                    <div class="label">Analysis</div>
                    <div class="value">{{ $progressGrading->analysis }}</div>
                </div>
                @endif
                @if($progressGrading->comments)
                <div class="info-box">
                    <div class="label">Comments</div>
                    <div class="value">{{ $progressGrading->comments }}</div>
                </div>
                @endif
                @if($progressGrading->recommendation)
                <div class="info-box">
                    <div class="label">Recommendation</div>
                    <div class="value">{{ $progressGrading->recommendation }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Progress Report 2 Grading --}}
    @if(isset($progress2Grading) && $progress2Grading && $progress2Grading->publish !== 'pending')
    <div class="section">
        <div class="section-title">Progress Report 2 Grading</div>
        <div class="grid-2">
            <div>
                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="badge {{ $progress2Grading->isAccepted == 1 ? 'badge-success' : 'badge-danger' }}">
                            {{ $progress2Grading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Rating</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Achievements</td>
                            <td><strong>{{ $progress2Grading->achievementsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progress2Grading->achievementsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Publications</td>
                            <td><strong>{{ $progress2Grading->publicationsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progress2Grading->publicationsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Students</td>
                            <td><strong>{{ $progress2Grading->studentsRating ?? '—' }}/5</strong></td>
                            <td>{{ $progress2Grading->studentsComments ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>Budget</td>
                            <td><strong>{{ $progress2Grading->budgetRating ?? '—' }}/5</strong></td>
                            <td>{{ $progress2Grading->budgetComments ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <div class="info-box">
                    <div class="label">Ethical Approval</div>
                    <div class="value">
                        <span class="badge {{ $progress2Grading->ethical ? 'badge-success' : 'badge-neutral' }}">
                            {{ $progress2Grading->ethical ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
                @if($progress2Grading->analysis)
                <div class="info-box">
                    <div class="label">Analysis</div>
                    <div class="value">{{ $progress2Grading->analysis }}</div>
                </div>
                @endif
                @if($progress2Grading->comments)
                <div class="info-box">
                    <div class="label">Comments</div>
                    <div class="value">{{ $progress2Grading->comments }}</div>
                </div>
                @endif
                @if($progress2Grading->recommendation)
                <div class="info-box">
                    <div class="label">Recommendation</div>
                    <div class="value">{{ $progress2Grading->recommendation }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Final Grading --}}
    @if($finalGrading && $finalGrading->publish !== 'pending')
    <div class="section page-break">
        <div class="section-title">Final Report Grading</div>
        <div class="grid-2">
            <div>
                <div class="info-box">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="badge {{ $finalGrading->isAccepted == 1 ? 'badge-success' : 'badge-danger' }}">
                            {{ $finalGrading->isAccepted == 1 ? 'Accepted' : 'Rejected' }}
                        </span>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Grade</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>A. Achievements</td>
                            <td><strong>{{ $finalGrading->gradeA ?? '—' }}/5</strong></td>
                            <td>{{ $finalGrading->commentA ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>B. Publications</td>
                            <td><strong>{{ $finalGrading->gradeB ?? '—' }}/5</strong></td>
                            <td>{{ $finalGrading->commentB ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>C. Students</td>
                            <td><strong>{{ $finalGrading->gradeC ?? '—' }}/5</strong></td>
                            <td>{{ $finalGrading->commentC ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td>D. Impact</td>
                            <td><strong>{{ $finalGrading->gradeD ?? '—' }}/5</strong></td>
                            <td>{{ $finalGrading->commentD ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="text-align: center;">
                <div class="grade-box">
                    <div class="grade">{{ $finalGrading->total ?? '—' }}</div>
                    <div class="label">Total Score</div>
                </div>
                <div style="margin-top: 12px;">
                    <div class="info-box">
                        <div class="label">Score A (Achievements)</div>
                        <div class="value">{{ $finalGrading->scoreA ?? '—' }} / {{ $finalGrading->autoGradeA ?? '—' }}</div>
                    </div>
                    <div class="info-box">
                        <div class="label">Score B (Publications)</div>
                        <div class="value">{{ $finalGrading->scoreB ?? '—' }} / {{ $finalGrading->autoGradeB ?? '—' }}</div>
                    </div>
                    <div class="info-box">
                        <div class="label">Score C (Students)</div>
                        <div class="value">{{ $finalGrading->scoreC ?? '—' }} / {{ $finalGrading->autoGradeC ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ now()->format('M d, Y \a\t H:i') }} | Research Tracking System
    </div>
</body>
</html>
