*<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Student Grant Summary</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Student Grant Summary')
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
                                    <div class="section">
                                        <span class="badge badge-success" style="font-size: 14px;">Student Grant</span>
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

                                <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                    <thead>
                                        <tr class="thead-teal">
                                            <th style="width:270px;" colspan="1">Project Info</th>
                                            <th style="width:910px;" colspan="7">LPI Student Project Form</th>
                                        </tr>

                                        <tr class="thead-light-teal">
                                            <th style="width:270px;">Project ID</th>
                                            <th style="width:130px;">Form Saved</th>
                                            <th style="width:130px;">Qatari Students</th>
                                            <th style="width:130px;">Non-Qatari Students</th>
                                            <th style="width:130px;">Engagement</th>
                                            <th style="width:130px;">Publications</th>
                                            <th style="width:130px;">Ethical Approval</th>
                                            <th style="width:130px;">Spending (QAR)</th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="scrollable-body">
                                    <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                        <tbody>

                                            @php
                                                // Initialize counters
                                                $counts = [
                                                    'registration' => ['yes' => 0, 'no' => 0],
                                                    'form_saved' => ['yes' => 0, 'no' => 0],
                                                    'budget' => ['filled' => 0, 'empty' => 0],
                                                    'spending' => ['filled' => 0, 'empty' => 0],
                                                    'students' => ['zero' => 0, 'not_zero' => 0],
                                                    'engagement' => ['yes' => 0, 'no' => 0],
                                                    'publications' => ['yes' => 0, 'no' => 0],
                                                ];
                                                $totalQatari = 0;
                                                $totalNonQatari = 0;
                                            @endphp

                                            @foreach ($summary as $record)
                                                <tr>
                                                    <td style="width:270px;"
                                                        class="{{ $record->old_project_id === 'No' || $record->old_project_id === 0 ? 'light-red' : '' }}">
                                                        {{ $record->old_project_id }}
                                                        </br>
                                                        <small style="color: teal; font-style: italic;">
                                                            {{ $record->email }} </small>
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ $record->student_project_draft !== 'save' ? 'light-red' : '' }}">
                                                        {{ $record->student_project_draft === 'save' ? 'Yes' : 'No' }}
                                                        @if ($record->student_project_draft === 'save')
                                                            @php $counts['form_saved']['yes']++; @endphp
                                                        @else
                                                            @php $counts['form_saved']['no']++; @endphp
                                                        @endif
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ ($record->qatari_count ?? 0) == 0 && ($record->non_qatari_count ?? 0) == 0 ? 'light-red' : '' }}">
                                                        {{ $record->qatari_count ?? 0 }}
                                                        @php $totalQatari += $record->qatari_count ?? 0; @endphp
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ ($record->qatari_count ?? 0) == 0 && ($record->non_qatari_count ?? 0) == 0 ? 'light-red' : '' }}">
                                                        {{ $record->non_qatari_count ?? 0 }}
                                                        @php $totalNonQatari += $record->non_qatari_count ?? 0; @endphp
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ is_null($record->student_engagement) || $record->student_engagement === '' ? 'light-red' : '' }}">
                                                        {{ !is_null($record->student_engagement) && $record->student_engagement !== '' ? 'Yes' : 'No' }}
                                                        @if (!is_null($record->student_engagement) && $record->student_engagement !== '')
                                                            @php $counts['engagement']['yes']++; @endphp
                                                        @else
                                                            @php $counts['engagement']['no']++; @endphp
                                                        @endif
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ is_null($record->publications) || $record->publications === '' ? 'light-red' : '' }}">
                                                        {{ !is_null($record->publications) && $record->publications !== '' ? 'Yes' : 'No' }}
                                                        @if (!is_null($record->publications) && $record->publications !== '')
                                                            @php $counts['publications']['yes']++; @endphp
                                                        @else
                                                            @php $counts['publications']['no']++; @endphp
                                                        @endif
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ is_null($record->student_project_draft) || $record->student_project_draft !== 'save' ? 'light-red' : '' }}">
                                                        @php
                                                            $hasEthicalApproval = false;
                                                            if ($record->project_id && $record->student_project_draft === 'save') {
                                                                $directory = 'uploads/ethical_approvals/' . $cycle . '/' . $record->old_project_id . '/';
                                                                $hasEthicalApproval = \Illuminate\Support\Facades\Storage::exists($directory) && count(\Illuminate\Support\Facades\Storage::files($directory)) > 0;
                                                            }
                                                        @endphp
                                                        {{ $hasEthicalApproval ? 'Yes' : 'N/A' }}
                                                    </td>

                                                    <td style="width:130px; text-align: center;"
                                                        class="{{ is_null($record->spending) || $record->spending == 0 || is_null($record->requested_budget_qar) || $record->requested_budget_qar === '' ? 'light-red' : '' }}">
                                                        @php
                                                            $budgetVal = floatval(str_replace(',', '', $record->requested_budget_qar ?? '0'));
                                                            $spendingVal = floatval($record->spending ?? 0);
                                                            $utilization = $budgetVal > 0 ? ($spendingVal / $budgetVal) * 100 : 0;
                                                            $formattedUtil = number_format($utilization, 2);
                                                        @endphp

                                                        @if ($budgetVal > 0 && $spendingVal > 0)
                                                            @if ($utilization == 100)
                                                                <span style="color: green;">{{ $formattedUtil }}%</span>
                                                            @elseif($utilization < 100)
                                                                <span style="color: red;">{{ $formattedUtil }}%
                                                                    - Under Utilized</span>
                                                            @else
                                                                <span style="color: red;">{{ $formattedUtil }}%
                                                                    - Exceeding Budget</span>
                                                            @endif
                                                        @elseif ($spendingVal == 0 && $record->student_project_draft === 'save')
                                                            <span style="color: orange;">0% - No Spending</span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>

                                <table class="table table-bordered" style="table-layout: fixed; width: 100%;">
                                    <tbody>
                                        <tr class="thead-light-teal">
                                            <td style="width:270px;"><strong>Status</strong></td>
                                            <td style="width:130px; text-align: center;"><strong>Completed:
                                                    {{ $counts['form_saved']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['form_saved']['no'] }}</strong></td>
                                            <td style="width:130px; text-align: center;"><strong>Qatari Students = {{ $totalQatari }}</strong></td>
                                            <td style="width:130px; text-align: center;"><strong>Non-Qatari Students = {{ $totalNonQatari }}</strong></td>
                                            <td style="width:130px; text-align: center;"><strong>Completed:
                                                    {{ $counts['engagement']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['engagement']['no'] }}</strong></td>
                                            <td style="width:130px; text-align: center;"><strong>Completed:
                                                    {{ $counts['publications']['yes'] }}</strong><br><strong>Pending:
                                                    {{ $counts['publications']['no'] }}</strong></td>
                                            <td style="width:130px; text-align: center;"></td>
                                            <td style="width:130px; text-align: center;"></td>
                                        </tr>

                                        <tr class="thead-light-teal" style="text-align:left">
                                            <td rowspan="2" style="text-align:center;"><strong>Action</strong></td>

                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['form_saved']['no'] }}</strong> respective LPIs to
                                                fill student project form</td>

                                            <td style="font-size:12" colspan="2">Send a reminder email to LPIs to update student records</td>

                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['engagement']['no'] }}</strong> respective LPIs to
                                                enter engagement description</td>

                                            <td style="font-size:12">Send a reminder email to all <strong>
                                                    {{ $counts['publications']['no'] }}</strong> respective LPIs to
                                                enter publication details</td>

                                            <td style="font-size:12" colspan="2"> </td>
                                        </tr>

                                        <tr class="thead-light-teal" style="text-align:left">
                                            <form action="{{ route('SummaryEmail') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="cycle" value="{{ $cycleid }}">

                                                <td style="font-size:12">
                                                    @if ($counts['form_saved']['no'] > 0)
                                                        <div class="center-block">
                                                            <button class="btn btn-teal btn-sm" type="submit"
                                                                name="submit_id" value="20">Send Email</button>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td style="font-size:12" colspan="2">
                                                    <div class="center-block">
                                                        <button class="btn btn-teal btn-sm" type="submit"
                                                            name="submit_id" value="22">Send Email</button>
                                                    </div>
                                                </td>

                                                <td style="font-size:12">
                                                    @if ($counts['engagement']['no'] > 0)
                                                        <div class="center-block">
                                                            <button class="btn btn-teal btn-sm" type="submit"
                                                                name="submit_id" value="23">Send Email</button>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td style="font-size:12">
                                                    @if ($counts['publications']['no'] > 0)
                                                        <div class="center-block">
                                                            <button class="btn btn-teal btn-sm" type="submit"
                                                                name="submit_id" value="24">Send Email</button>
                                                        </div>
                                                    @endif
                                                </td>

                                                <td style="font-size:12" colspan="2"> </td>
                                            </form>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="heading">
                            Progress Details - Student Grant
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </body>

@endsection
