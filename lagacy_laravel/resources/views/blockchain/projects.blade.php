<!-- Include Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

@foreach ($projects as $project)
    <div class="card mb-4">
        <div class="card-header">
            <p class="card-title"><strong>Project Title: </strong>{{ $project->project_title }} (ID: {{ $project->project_id }})</p>
            <p class="card-text"><strong>LPI:</strong> {{ $project->lpi }} ({{ $project->lpi_email }})</p>
            <p class="card-text"><strong>Cycle:</strong> {{ $project->cycle_title }}</p>
            <p class="card-text"><strong>Pillar:</strong> {{ $project->pillar }}</p>


            <p><strong>Tags: </strong>
           {{ implode(', ', $project->tags->toArray()) }}</p>
        </div>
        <div class="card-body">

            <p><strong>Outcomes</strong></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Score</th>
                            <th>Publication Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->outcomes as $outcome)
                            <tr>
                                <td>{{ $outcome->outcome_type }}</td>
                                <td>{{ $outcome->outcome_score }}</td>
                                <td>
                                    @if ($outcome->publication_details)
                                        <ul>
                                            @foreach ($outcome->publication_details as $publication)
                                                <li>
                                                    Title: {{ $publication->title }} <br>
                                                    Date: {{ $publication->publication_date }} <br>
                                                    Venue: {{ $publication->venue }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        No publication details available.
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            <h3>Students</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>ID</th>
                            <th>Days</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->students as $student)
                            <tr>
                                <td>{{ $student->student_type }}</td>
                                <td>{{ $student->student_id }}</td>
                                <td>{{ $student->student_days }}</td>
                                <td>{{ $student->student_score }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h3>Final Reports</h3>
            <div class="table-responsive">

                <table class="table table-striped">
                    <tbody>
                        @foreach ($project->final_report as $final_report)
                            <tr>
                                <td>
                                    {{ $final_report->user_id }}
                                </td>
                                <td>
                                    <lu>
                                        <li>Grade A: {{ $final_report->final_report_gradeA }} Commnts:
                                            {{ $final_report->final_report_CommentA }} </li>
                                        <li>Grade B: {{ $final_report->final_report_gradeB }} Commnts:
                                            {{ $final_report->final_report_CommentB }} </li>
                                        <li>Grade C: {{ $final_report->final_report_gradeC }} Commnts:
                                            {{ $final_report->final_report_CommentC }} </li>
                                        <li>Grade D: {{ $final_report->final_report_gradeD }} Commnts:
                                            {{ $final_report->final_report_CommentD }} </li>
                                    </lu>
                                </td>
                            </tr>
                        @endforeach
                </table>


            </div>

            <h3>Progress Reports</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Achievement Rating</th>
                            <th>Publications Rating</th>
                            <th>Student Rating</th>
                            <th>Achievement Comments</th>
                            <th>Publications Comments</th>
                            <th>Student Comments</th>

                            <th>Grading Date</th>
                            <th>Status</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($project->progress_report as $progress_report)
                            <tr>
                                <td>{{ $progress_report->user_id }}</td>
                                <td>{{ $progress_report->progress_report_achievement_rating }}</td>
                                <td>{{ $progress_report->progress_report_publications_rating }}</td>
                                <td>{{ $progress_report->progress_report_students_rating }}</td>

                                <td>{{ $progress_report->progress_report_achievement_comments }}</td>
                                <td>{{ $progress_report->progress_report_publications_comments }}</td>
                                <td>{{ $progress_report->progress_report_students_comments }}</td>

                                <td>{{ $progress_report->progress_report_grading_date }}</td>
                                <td>{{ $progress_report->progress_report_status }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endforeach
