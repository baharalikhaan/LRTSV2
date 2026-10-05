<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">


    <style>
        .heading {
            position: absolute;
            top: -15;
            left: 35;
            background-color: teal;
            color: white;
            padding: 6px;
            border-radius: 15px 15px 15px 15px;
        }

        .heading2 {
            position: absolute;
            top: -15;
            left: 35;
            background-color: #623C21;
            color: white;
            padding: 6px;
            border-radius: 15px 15px 15px 15px;
        }

        .footer {
            position: absolute;
            bottom: 1;
            right: 55;
            font-size: 11px;
            font-style: italic;
            color: #623C21;

        }


        .btn-teal {
            color: #fff;
            background-color: #008080;
            float: right;
            border-color: #008080;

        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            border-color: #005959;
        }

        .container {
            display: flex;
            margin: 0;
        }

        .section {
            text-align: left;
            border-right: 1px solid #ccc;
            padding-left: 20px;
            padding-right: 20px;
        }

        .section4 {
            text-align: left;
            border-left: 1px solid #ccc;
            padding-left: 20px;
            padding-right: 20px;
        }

        .section3 {
            text-align: left;
            border-right: 1px solid #ccc;
            padding: 30px;

        }


        .section2 {
            text-align: left;
            padding: 20px;
        }

        .section:last-child {
            border-right: none;
        }
    </style>

</head>

<body class="body">

    <head>
        <meta charset="UTF-8">
        <title>Register Project</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')

        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 5px;">
                                <div class="container">
                                    <div class="section" style="padding-top:25">
                                        <b> {{ $user->name }} </b><br>
                                        {{ $user->email }}
                                    </div>
                                    <div class="section2">
                                        <b> Project ID: </b>
                                        <span>{{ $project->old_project_id }}</span><br>
                                        <b> Project Title: </b>
                                        <span>{{ $project->title }}</span>
                                    </div>
                                    {{--
                            <div class="section4" style="padding-top:35">
                                <form action="{{ route('uploadedOutcomes') }}">
                                    <input type="text" name='p_id' value="{{ $p_id }}" hidden>
                                    @if ($outcomes)
                                        <button id="btn" class="btn btn-teal btn-sm">
                                            Progress Summary
                                        </button>
                                    @endif
                                </form>
                            </div> --}}

                                </div>
                                <div class="heading">
                                    Upload Progress
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
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 20px;">
                                <div style=" margin: 20px;">
                                    <div class="container">
                                        <div class="section">
                                            @if ($project->upload_outcomes == 'active')
                                                <p>Kindly fill out the detailed "Outcome-Form" of your project:</p>
                                                <input type="text" name='report_type' value="final" hidden>
                                                <form action="{{ route('projectOutcomes') }}">
                                                    <input type="text" name='p_id' value="{{ $p_id }}" hidden>
                                                    <button id="btn" class="btn btn-teal btn-sm">
                                                        {{ __('Open Form') }}
                                                    </button>
                                                </form>
                                                @if (session('successoutcome'))
                                                    {!! session('successoutcome') !!}
                                                    @php
                                                        session()->forget('successoutcome');
                                                    @endphp
                                                @endif
                                            @endif
                                        </div>
                                        @if ($project->upload_outcomes == 'active')
                                            <div class="section">

                                                <form action="{{ route('uploadedOutcomes') }}">
                                                    <input type="text" name='p_id' value="{{ $p_id }}" hidden>
                                                    <p>List of already filed outcomes</p>
                                                    <button id="btn" class="btn btn-teal btn-sm">
                                                        Progress Summary
                                                    </button>
                                                </form>
                                            @else
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="heading">
                                Outcomes of Project
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
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 20px;">
                                <div class="container">
                                    <div class="section3">
                                        <a href="{{ url('downloads/Progess Report Template (IG) -En.docx') }}"
                                            style="  color: teal;">Download Template File</a>
                                    </div>
                                    <div class="section3">
                                        <p><i class="fas fa-calendar"></i> <span class="ml-2">Deadline: @if ($cycle->prog_rpt_deadline)
                                                    {{ $cycle->prog_rpt_deadline }}
                                                @endif
                                            </span>
                                        </p>
                                        @if ($cycle->extended_prog_rpt_deadline != '')
                                            <p><i class="fas fa-calendar"></i> <span class="ml-2">Extended Deadline:

                                                    {{ $cycle->extended_prog_rpt_deadline }}

                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="section" style="margin-top:20">


                                        @if (auth()->user()->type == 'LPI')
                                            @if ($progress_report)
                                                <a target="_blank"
                                                    href="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $cycle->cycle_title }}/{{ $project->old_project_id }}.pdf">
                                                    <i class="fas fa-file-pdf" style="color:green"></i> <span class="ml-2"
                                                        style="color:green">{{ $project->old_project_id . '.pdf' }}
                                                    </span> </a>
                                            @endif

                                            @if ($cycle->extended_prog_rpt_deadline == null or $cycle->extended_prog_rpt_deadline == '')
                                                @if ($cycle->prog_rpt_deadline >= date('Y-m-d'))
                                                    <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="text" name='report_type' value="progress" hidden>
                                                        <input type="text" name='title' value="{{ $project->title }}"
                                                            hidden><br>

                                                        <input type="file" name="file">





                                                        <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                            {{ __('Upload') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                        style="color:red">Progress Report Deadline Passed</span>
                                                @endif
                                            @elseif ($cycle->extended_prog_rpt_deadline >= date('Y-m-d'))
                                                <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="text" name='report_type' value="progress" hidden>
                                                    <input type="text" name='title' value="{{ $project->title }}"
                                                        hidden><br>
                                                    <input type="file" name="file">
                                                    <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                        {{ __('Upload') }}
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                    style="color:red">Extended Progress Report Deadline Passed</span>
                                            @endif

                                            <hr>
                                            <!-- Progress Report Form Buttons -->
                                            <div style="margin-top:10px;">
                                                <button type="button" class="btn btn-sm btn-teal" style="float:none; margin-right:10px;" onclick="openProgressReportModal()">
                                                    <i class="fas fa-edit"></i> Update Progress Report
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" style="float:none;" onclick="viewProgressReport()">
                                                    <i class="fas fa-eye"></i> View Progress Report
                                                </button>
                                            </div>

                                        @endif

                                        @if ($errors->any() and session('mmtype') == 'progress')
                                            <div class="alert alert-danger">
                                                @foreach ($errors->all() as $error)
                                                    {{ $error }}
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (session('successprogress'))
                                            {!! session('successprogress') !!}
                                            @php
                                                session()->forget('successprogress');
                                            @endphp
                                        @endif

                                    </div>
                                </div>
                                <div class="heading">
                                    Progress Report
                                </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @if ($project->has_progress_report2)
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 20px;">
                                <div class="container">
                                    <div class="section3">
                                        <a href="{{ url('downloads/Progess Report Template (IG) -En.docx') }}"
                                            style="  color: teal;">Download Template File</a>
                                    </div>
                                    <div class="section3">
                                        <p><i class="fas fa-calendar"></i> <span class="ml-2">Deadline: @if ($cycle->prog2_rpt_deadline)
                                                    {{ $cycle->prog2_rpt_deadline }}
                                                @endif
                                            </span>
                                        </p>
                                        @if ($cycle->extended_prog2_rpt_deadline != '')
                                            <p><i class="fas fa-calendar"></i> <span class="ml-2">Extended Deadline:

                                                    {{ $cycle->extended_prog2_rpt_deadline }}

                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="section" style="margin-top:20">


                                        @if (auth()->user()->type == 'LPI')
                                            @if ($progress_report2)
                                                <a target="_blank"
                                                    href="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $cycle->cycle_title }}/{{ $project->old_project_id }}_2.pdf">
                                                    <i class="fas fa-file-pdf" style="color:green"></i> <span class="ml-2"
                                                        style="color:green">{{ $project->old_project_id . '_2.pdf' }}
                                                    </span> </a>
                                            @endif

                                            @if ($cycle->extended_prog2_rpt_deadline == null or $cycle->extended_prog2_rpt_deadline == '')
                                                @if ($cycle->prog2_rpt_deadline >= date('Y-m-d'))
                                                    <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="text" name='report_type' value="progress2" hidden>
                                                        <input type="text" name='title' value="{{ $project->title }}"
                                                            hidden><br>
                                                        <input type="file" name="file">
                                                        <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                            {{ __('Upload') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                        style="color:red">Progress Report 2 Deadline Passed</span>
                                                @endif
                                            @elseif ($cycle->extended_prog2_rpt_deadline >= date('Y-m-d'))
                                                <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="text" name='report_type' value="progress2" hidden>
                                                    <input type="text" name='title' value="{{ $project->title }}"
                                                        hidden><br>
                                                    <input type="file" name="file">
                                                    <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                        {{ __('Upload') }}
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                    style="color:red">Extended Progress Report 2 Deadline Passed</span>
                                            @endif

                                        @endif

                                        @if ($errors->any() and session('mmtype') == 'progress2')
                                            <div class="alert alert-danger">
                                                @foreach ($errors->all() as $error)
                                                    {{ $error }}
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (session('successreport2'))
                                            {!! session('successreport2') !!}
                                            @php
                                                session()->forget('successreport2');
                                            @endphp
                                        @endif

                                    </div>
                                </div>
                                <div class="heading">
                                    Progress Report 2
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div
                            style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 20px;">
                                <div class="container">
                                    <div class="section3">
                                        <a href="{{ url('downloads/Final Report Template (IG) -En.docx') }}"
                                            style="  color: teal;">Download Template File</a>
                                    </div>


                                    <div class="section3">
                                        <p><i class="fas fa-calendar"></i> <span class="ml-2">Deadline: @if ($cycle->final_rpt_deadline)
                                                    {{ $cycle->final_rpt_deadline }}
                                                @endif
                                            </span>
                                        </p>
                                        @if ($cycle->extended_final_rpt_deadline != '')
                                            <p><i class="fas fa-calendar"></i> <span class="ml-2">Extended Deadline:

                                                    {{ $cycle->extended_final_rpt_deadline }}

                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="section" style="margin-top:20">


                                        @if (auth()->user()->type == 'LPI')
                                            @if ($progress_report)
                                                <a target="_blank"
                                                    href="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $cycle->cycle_title }}/{{ $project->old_project_id }}.pdf">
                                                    <i class="fas fa-file-pdf" style="color:green"></i> <span class="ml-2"
                                                        style="color:green">{{ $project->old_project_id . '.pdf' }}
                                                    </span> </a>
                                            @endif

                                            @if ($cycle->extended_prog_rpt_deadline == null or $cycle->extended_prog_rpt_deadline == '')
                                                @if ($cycle->prog_rpt_deadline >= date('Y-m-d'))
                                                    <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="text" name='report_type' value="progress" hidden>
                                                        <input type="text" name='title' value="{{ $project->title }}"
                                                            hidden><br>

                                                        <input type="file" name="file">





                                                        <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                            {{ __('Upload') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                        style="color:red">Progress Report Deadline Passed</span>
                                                @endif
                                            @elseif ($cycle->extended_prog_rpt_deadline >= date('Y-m-d'))
                                                <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="text" name='report_type' value="progress" hidden>
                                                    <input type="text" name='title' value="{{ $project->title }}"
                                                        hidden><br>
                                                    <input type="file" name="file">
                                                    <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                        {{ __('Upload') }}
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                    style="color:red">Extended Progress Report Deadline Passed</span>
                                            @endif

                                        @endif

                                        @if ($errors->any() and session('mmtype') == 'final')
                                            <div class="alert alert-danger">
                                                @foreach ($errors->all() as $error)
                                                    {{ $error }}
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (session('successreportfinal'))
                                            {!! session('successreportfinal') !!}
                                            @php
                                                session()->forget('successreportfinal');
                                            @endphp
                                        @endif

                                    </div>
                                </div>
                                <div class="heading">
                                    Final Report
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
                        <div
                            style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 20px;">
                                <div class="container">
                                    <div class="section3">

                                        <a href="{{ asset('downloads/QU_Readiness Mapping Template_ORS-En.docx') }}"
                                            style="  color: teal;">Download Template File</a>
                                    </div>

                                    <div class="section3">
                                        <p><i class="fas fa-calendar"></i> <span class="ml-2">Deadline: @if ($cycle->final_rpt_deadline)
                                                    {{ $cycle->final_rpt_deadline }}
                                                @endif
                                            </span>
                                        </p>
                                        @if ($cycle->extended_final_rpt_deadline != '')
                                            <p><i class="fas fa-calendar"></i> <span class="ml-2">Extended Deadline:
                                                    {{ $cycle->extended_final_rpt_deadline }}
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <div class="section" style="margin-top:20">

                                        @if (auth()->user()->type == 'LPI')
                                            @if ($readiness_report)
                                                <a target="_blank"
                                                    href="{{ URL::to('/') }}/serveFile2?file=readiness_reports/{{ $cycle->cycle_title }}/{{ $project->old_project_id }}.pdf">
                                                    <i class="fas fa-file-pdf" style="color:green"></i> <span
                                                        class="ml-2"
                                                        style="color:green">{{ $project->old_project_id . '.pdf' }}
                                                    </span> </a>
                                            @endif

                                            @if ($cycle->extended_final_rpt_deadline == null or $cycle->extended_final_rpt_deadline == '')
                                                @if ($cycle->final_rpt_deadline >= date('Y-m-d'))
                                                    <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="text" name='report_type' value="readiness"
                                                            hidden>
                                                        <input type="text" name='title'
                                                            value="{{ $project->title }}" hidden><br>
                                                        <input type="file" name="file">
                                                        <button type="submit" class="btn btn-sm btn-teal"
                                                            id="btn">
                                                            {{ __('Upload') }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                        style="color:red">QU Readiness Report Deadline Passed</span>
                                                @endif
                                            @elseif ($cycle->extended_final_rpt_deadline >= date('Y-m-d'))
                                                <form action="{{ route('reportUpload', ['p_id' => $p_id]) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="text" name='report_type' value="readiness" hidden>
                                                    <input type="text" name='title' value="{{ $project->title }}"
                                                        hidden><br>
                                                    <input type="file" name="file">
                                                    <button type="submit" class="btn btn-sm btn-teal" id="btn">
                                                        {{ __('Upload') }}
                                                    </button>
                                                </form>
                                            @else
                                                <i class="fas fa-times" style="color:red"></i> <span class="ml-2"
                                                    style="color:red">Extended QU Readiness Report Deadline Passed</span>
                                            @endif
                                        @endif

                                        @if ($errors->any() and session('mmtype') == 'readiness')
                                            <div class="alert alert-danger">
                                                @foreach ($errors->all() as $error)
                                                    {{ $error }}
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (session('successreportreadiness'))
                                            {!! session('successreportreadiness') !!}
                                            @php
                                                session()->forget('successreportreadiness');
                                            @endphp
                                        @endif

                                    </div>
                                </div>
                            </div>
                            <div class="heading">
                                QU Readiness Report
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </div>

        </div>

<style>
    .modal-xxl {
        max-width: 95% !important;
        width: 95% !important;
    }
    @media (min-width: 1200px) {
        .modal-xxl {
            max-width: 1400px !important;
        }
    }
</style>

<!-- Progress Report Modal -->
<div class="modal fade" id="progressReportModal" tabindex="-1" role="dialog" aria-labelledby="progressReportModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: teal; color: white; border-bottom: none;">
                <h5 class="modal-title" id="progressReportModalLabel">Update Progress Report</h5>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <iframe id="progressReportFrame" src="" style="width:100%; height:75vh; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- View Progress Report Modal -->
<div class="modal fade" id="viewProgressReportModal" tabindex="-1" role="dialog" aria-labelledby="viewProgressReportModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: teal; color: white; border-bottom: none;">
                <h5 class="modal-title" id="viewProgressReportModalLabel">Progress Report Document</h5>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                <iframe id="viewProgressReportFrame" src="" style="width:100%; height:75vh; border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    function openProgressReportModal() {
        var p_id = '{{ $p_id }}';
        var url = '{{ url("/") }}' + '/progressReport/edit/' + p_id;
        document.getElementById('progressReportFrame').src = url;
        $('#progressReportModal').modal('show');
    }

    function viewProgressReport() {
        var p_id = '{{ $p_id }}';
        var url = '{{ route("progressReport.preview", ["project_id" => $p_id]) }}';
        document.getElementById('viewProgressReportFrame').src = url;
        $('#viewProgressReportModal').modal('show');
    }
</script>

</body>@endsection
