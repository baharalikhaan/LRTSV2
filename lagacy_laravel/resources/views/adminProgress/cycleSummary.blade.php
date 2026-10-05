<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Budget API Settings</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')

        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">
                                <div class="container">
                                    <div class="section">
                                        <b>Cycle Title:</b>
                                    </div>
                                    <div class="section">
                                        {{ $cycle }}
                                    </div>
                                </div>
                                <div class="heading">
                                    Cycle Info
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">

                            <div style=" margin: 40px;">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="thead-teal">
                                            <th colspan="1">Project Info</th>
                                            <th colspan="7">LPI</th>
                                            <th colspan="1">Admin</th>
                                            <th colspan="2">Reviewer</th>
                                        </tr>

                                        <tr class="thead-light-teal">
                                            <th style="width:270">Project ID</th>
                                            <th style="width:150">Registration</th>
                                            <th style="width:150">Outcomes</th>
                                            <th style="width:150">Students</th>
                                            <th style="width:150">Contribution</th>
                                            <th style="width:150">Progress Report</th>
                                            <th style="width:150">Final Report</th>
                                            <th style="width:150">Readiness Report</th>
                                            <th style="width:150">Reviewers assignment</th>
                                            <th style="width:150">Progress Grading</th>
                                            <th style="width:150">Final Grading</th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="scrollable-body">
                                    <table class="table table-bordered">
                                        <tbody>

                                            @php
                                                // Initialize counters
                                                $counts = [
                                                    'registration' => ['yes' => 0, 'no' => 0],
                                                    'progress_report' => ['yes' => 0, 'no' => 0],
                                                    'final_report' => ['yes' => 0, 'no' => 0],
                                                    'readiness_report' => ['yes' => 0, 'no' => 0],
                                                    'outcomes' => ['zero' => 0, 'not_zero' => 0],
                                                    'students' => ['zero' => 0, 'not_zero' => 0],
                                                    'contribution' => ['zero' => 0, 'not_zero' => 0],
                                                    'reviewers' => ['zero' => 0, 'not_zero' => 0],
                                                    'progress_grading' => ['zero' => 0, 'not_zero' => 0],
                                                    'final_grading' => ['zero' => 0, 'not_zero' => 0],
                                                ];
                                            @endphp



                                            @foreach ($summary as $record)
                                                <tr>
                                                    <td style="width:200"
                                                        class="{{ $record->old_project_id === 'No' || $record->old_project_id === 0 ? 'light-red' : '' }}">
                                                        {{ $record->old_project_id }}
                                                        </br>
                                                        <small style="color: teal; font-style: italic;">
                                                            {{ $record->email }} </small>
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->registration === 'No' || $record->registration === 0 ? 'light-red' : '' }}">
                                                        {{ $record->registration }}
                                                        @if ($record->registration === 'No')
                                                            @php $counts['registration']['no']++; @endphp
                                                        @else
                                                            @php $counts['registration']['yes']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->outcomes === 0 ? 'light-red' : '' }}">
                                                        {{ $record->outcomes }}
                                                        @if ($record->outcomes === 0)
                                                            @php $counts['outcomes']['zero']++; @endphp
                                                        @else
                                                            @php $counts['outcomes']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->students === 0 ? 'light-red' : '' }}">
                                                        {{ $record->students }}
                                                        @if ($record->students === 0)
                                                            @php $counts['students']['zero']++; @endphp
                                                        @else
                                                            @php $counts['students']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->contribution === 0 ? 'light-red' : '' }}">
                                                        {{ $record->contribution }}
                                                        @if ($record->contribution === 0)
                                                            @php $counts['contribution']['zero']++; @endphp
                                                        @else
                                                            @php $counts['contribution']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->progress_report === 'No' || $record->progress_report === 0 ? 'light-red' : '' }}">
                                                        {{ $record->progress_report }}
                                                        @if ($record->progress_report === 'No')
                                                            @php $counts['progress_report']['no']++; @endphp
                                                        @else
                                                            @php $counts['progress_report']['yes']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->final_report === 'No' || $record->final_report === 0 ? 'light-red' : '' }}">
                                                        {{ $record->final_report }}
                                                        @if ($record->final_report === 'No')
                                                            @php $counts['final_report']['no']++; @endphp
                                                        @else
                                                            @php $counts['final_report']['yes']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->readiness_report === 'No' || $record->readiness_report === 0 ? 'light-red' : '' }}">
                                                        {{ $record->readiness_report }}
                                                        @if ($record->readiness_report === 'No')
                                                            @php $counts['readiness_report']['no']++; @endphp
                                                        @else
                                                            @php $counts['readiness_report']['yes']++; @endphp
                                                        @endif
                                                    </td>

                                                    <td style="width:150"
                                                        class="{{ $record->reviewers === 0 ? 'light-red' : '' }}">
                                                        {{ $record->reviewers }}
                                                        @if ($record->reviewers === 0)
                                                            @php $counts['reviewers']['zero']++; @endphp
                                                        @else
                                                            @php $counts['reviewers']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:150"
                                                        class="{{ $record->progress_grading === 0 ? 'light-red' : '' }}">
                                                        {{ $record->progress_grading }}
                                                        @if ($record->progress_grading === 0)
                                                            @php $counts['progress_grading']['zero']++; @endphp
                                                        @else
                                                            @php $counts['progress_grading']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                    <td style="width:130"
                                                        class="{{ $record->final_grading === 0 ? 'light-red' : '' }}">
                                                        {{ $record->final_grading }}
                                                        @if ($record->final_grading === 0)
                                                            @php $counts['final_grading']['zero']++; @endphp
                                                        @else
                                                            @php $counts['final_grading']['not_zero']++; @endphp
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>

                                <table class="table table-bordered">
                                    <tbody>
                                        <tr class="thead-light-teal">
                                            <td style="width:310"><strong>Status</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['registration']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['registration']['no'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['outcomes']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['outcomes']['zero'] }}</strong>
                                            </td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['students']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['students']['zero'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['contribution']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['contribution']['zero'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['progress_report']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['progress_report']['no'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['final_report']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['final_report']['no'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['readiness_report']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['readiness_report']['no'] }}</strong></td>

                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['reviewers']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['reviewers']['zero'] }}</strong></td>
                                            <td style="width:150"><strong>Completed:
                                                    {{ $counts['progress_grading']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['progress_grading']['zero'] }}</strong></td>
                                            <td style="width:150"><strong>
                                                    Completed:
                                                    {{ $counts['final_grading']['not_zero'] }}</strong><br><strong>Pending:
                                                    {{ $counts['final_grading']['zero'] }}</strong></td>
                                        </tr>


                                        <tr class="thead-light-teal" style="text-align:left">
                                            <td rowspan="2" style="text-align:center;"><strong>Action</strong></td>


                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['registration']['no'] }}</strong> respective LPIs to
                                                register their projects
                                            </td>


                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['outcomes']['zero'] }}</strong> respective LPIs to enter
                                                outcomes
                                            </td>


                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['students']['zero'] }}</strong> respective LPIs to enter
                                                students involment
                                            </td>


                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['contribution']['zero'] }}</strong> respective LPIs to
                                                enter
                                                contributions
                                            </td>

                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['progress_report']['no'] }}</strong> respective LPIs to
                                                upload progress report
                                            </td>


                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['final_report']['no'] }}</strong> respective LPIs to
                                                upload final report

                                            </td>

                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['readiness_report']['no'] }}</strong> respective LPIs to
                                                upload readiness report

                                            </td>

                                            <td style="font-size:12">Kindly assign reviewers for all the <strong>
                                                    {{ $counts['reviewers']['zero'] }}</strong> pending projects </td>


                                            <td style="font-size:12">Send a reminder email to all respective reviewers
                                                of
                                                all pending <strong>
                                                    {{ $counts['progress_grading']['zero'] }}</strong> projects, to
                                                review
                                                the progress report

                                            </td>

                                            <td style="font-size:12">Send a reminder email to all respective reviewers
                                                of
                                                all pending <strong>
                                                    {{ $counts['final_grading']['zero'] }}</strong> projects, to review
                                                the
                                                final report

                                            </td>
                                        </tr>



                                        <tr class="thead-light-teal" style="text-align:left">
                                            <form action="{{ route('SummaryEmail') }}" method="POST">
                                                @csrf

                                                @php

                                                    $oldProjectIds = $summary->pluck('old_project_id')->toArray();
                                                @endphp
                                                <input type="hidden" name="cycle" value="{{ $cycleid }}">


                                                <td style="font-size:12">


                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="10">Send Email</button>
                                                    </div>
                                                </td>

                                                <td colspan="3" style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="11">Send Email</button>
                                                    </div>
                                                </td>


                                                {{-- <td style="font-size:12">
                                            <div class="center-block">
                                                <button class="btn btn-teal btn-sm">Send Email</button>
                                            </div>
                                        </td>


                                        <td style="font-size:12">
                                            <div class="center-block">
                                                <button class="btn btn-teal btn-sm">Send Email</button>
                                            </div>
                                        </td> --}}

                                                <td style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="12">Send Email</button>
                                                    </div>
                                                </td>


                                                <td style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="13">Send Email</button>
                                                    </div>
                                                </td>

                                                <td style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="14">Send Email</button>
                                                    </div>
                                                </td>

                                                <td style="font-size:12"> </td>
                                                <td style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="15">Send Email</button>
                                                    </div>
                                                </td>

                                                <td style="font-size:12">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="16">Send Email</button>
                                                    </div>
                                                </td>
                                            </form>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="heading">
                            Progress Details
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </body>

@endsection
