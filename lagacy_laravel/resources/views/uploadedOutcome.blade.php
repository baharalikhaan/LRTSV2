<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS - Budget balances</title>

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

        .btn-teal {
            color: #fff;
            background-color: #008080;
            /* Teal color */
            border-color: #008080;
            /* Teal color */
        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            /* Darker Teal color on hover */
            border-color: #005959;
            /* Darker Teal color on hover */
        }


        .tabs {

            margin: 20 auto;





        }

        #tab-button {
            display: table;
            table-layout: fixed;
            width: 100%;
            margin: 0;
            padding: 0;
            list-style: none;

        }

        #tab-button li {
            display: table-cell;
            width: 20%;
            cursor: pointer;
        }

        #tab-button li a {
            display: block;
            padding: .5em;
            background: teal;
            border: 2px solid teal;
            text-align: center;
            color: white;

            text-decoration: none;
            font-size: 17px;

        }

        #tab-button li:not(:first-child) a {
            border-left: none;
        }

        #tab-button li a:hover a {

            background: #fff;
        }

        #tab-button .is-active a {
            border-bottom-color: transparent;
            border: 2px solid #004c4c;
            background: #004c4c;

        }

        .tab-contents {
            padding: .5em 2em 1em;
            border: 2px solid teal;
            border-top: 1px solid teal;
            /* border-radius: 0 0 20px 20px; */
        }



        .tab-button-outer {
            display: none;
        }

        .tab-contents {
            margin-top: 20px;
        }

        @media screen and (min-width: 768px) {
            .tab-button-outer {
                position: relative;
                z-index: 2;
                display: block;
            }

            .tab-select-outer {
                display: none;
            }

            .tab-contents {
                position: relative;
                top: -1px;
                margin-top: 0;
            }
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
        <div class="row"  >
            <div class="col-md-12"  >
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                    <div style=" margin: 15px;">
                        <div class="container">
                            <div class="section2">
                                <b> Project ID: </b>
                                <span>{{ $project->old_project_id }}</span><br>
                                <b> Project Title: </b>
                                <span>{{ $project->title }}</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab-button-outer">
                <ul id="tab-button">
                    <li><a href="#tab00" style=" border-radius: 20px 0px 0 0;">Proposal</a></li>
                    <li><a href="#tab01">Commitments</a></li>
                    <li><a href="#tab02">Outcomes</a></li>
                    <li><a href="#tab03">Contributions</a></li>
                    <li><a href="#tab04">Students Involments</a></li>

                    <li><a href="#tab05">Progress Report</a></li>
                    <li><a href="#tab06">Final Report</a></li>
                    <li><a href="#tab07" style=" border-radius: 0px 20px 0 0;">Readiness Report</a></li>


                </ul>
            </div>


            <div id="tab00" class="tab-contents">

                @php
                    use Illuminate\Support\Facades\Storage;

                    $filePath =
                        'uploads/lpi_project_proposals/' .
                        $project->cycle_title .
                        '/' .
                         str_replace('/', '', $project->old_project_id) .
                        '.pdf';
                    $fileExists = Storage::exists($filePath);
                @endphp

                @if (!$fileExists)
                    <div class="alert alert-warning">
                        The requested file does not exist. contact support.
                    </div>
                @else
                    <iframe
                        src="{{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ $project->cycle_title }}/{{  str_replace('/', '', $project->old_project_id)  }}.pdf"
                        id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                @endif


            </div>



            <div id="tab01" class="tab-contents">

                @if ($commitments)
                    <table class="table table-bordered" style="margin-top:20px;">
                        <thead>
                            <tr>
                                <th>Commitments</th>
                                <th>No.</th>
                                <th>Score.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Published articles in journals listed in Thomson Reuters Web of
                                    Science-SCI
                                    (Quartile in Category Q1)</td>
                                <td>{{ $commitments->q1article }}</td>
                                <td>8</td>
                            </tr>
                            <tr>
                                <td>Published articles in journals listed in Thomson Reuters Web of
                                    Science-SCI
                                    (Quartile in Category Q2)</td>
                                <td>{{ $commitments->q2article }}</td>
                                <td>6</td>
                            </tr>
                            <tr>
                                <td>Published articles in journals listed in Thomson Reuters Web of
                                    Science-SCI
                                    (Quartile in Category Q3)</td>
                                <td>{{ $commitments->q3article }}</td>
                                <td>4</td>
                            </tr>
                            <tr>
                                <td>Published articles in journals listed in Thomson Reuters Web of
                                    Science-SCI
                                    (Quartile in Category Q4)</td>
                                <td>{{ $commitments->q4article }}</td>
                                <td>3</td>
                            </tr>
                            <tr>
                                <td>Public articles in indexed international conferences</td>
                                <td>{{ $commitments->confArticle }}</td>
                                <td>2</td>
                            </tr>
                            <tr>

                                <td>Published Books</td>
                                <td>{{ $commitments->books }}</td>
                                <td>8</td>
                            </tr>
                            <tr>

                                <td>Edited Books (collection)</td>
                                <td>{{ $commitments->editBooks }}</td>
                                <td>6</td>
                            </tr>
                            <tr>

                                <td>Book Chapters</td>
                                <td>{{ $commitments->chapters }}</td>
                                <td>4</td>
                            </tr>
                            <tr>

                                <td>Submitted IP Disclosure form submitted</td>
                                <td>{{ $commitments->ip }}</td>
                                <td>4</td>
                            </tr>
                            <tr>

                                <td>Filed Provisional Patent</td>
                                <td>{{ $commitments->filedPatent }}</td>
                                <td>7</td>
                            </tr>
                            <tr>

                                <td>Open Source Software</td>
                                <td>{{ $commitments->openSourceSW }}</td>
                                <td>8</td>
                            </tr>
                            <tr>

                                <td>Created a Start-Up</td>
                                <td>{{ $commitments->startUp }}</td>
                                <td>10</td>
                            </tr>
                            <tr>

                                <td>No. of UnderGrad Students Involved in Project</td>
                                <td>{{ $commitments->UG }}</td>
                                <td>1</td>
                            </tr>
                            <tr>

                                <td>No. of Masters Students Involved in Project</td>
                                <td>{{ $commitments->master }}</td>
                                <td>2</td>
                            </tr>
                            <tr>

                                <td>No. of PhD Student Involved in Project</td>
                                <td>{{ $commitments->Phd }}</td>
                                <td>3</td>
                            </tr>
                            <tr>

                                <td>Cross College Participation</td>
                                <td>{{ $commitments->crossCollege }}</td>
                                <td>2</td>
                            </tr>

                            <tr>

                                <td>Does this project requires ethical approval?</td>
                                <td>{{ $commitments->ethical = 0 ? 'No' : 'Yes' }}</td>
                                <td></td>
                            </tr>

                        </tbody>
                    </table>
                @else
                    <b class='alert'>Commitments for this project are not avaialable</b> <br>
                    <br>
                @endif

            </div>
            <div id="tab02" class="tab-contents">

                @if ($outcomes)
                    <table class="table table-bordered" style="margin-top:20px;">
                        <thead style="text-align:center;color:teal;font-weight:bold;">
                            <tr>
                                <th scope="col">Identifier</th>
                                <th scope="col">Title</th>
                                <th scope="col">Date</th>
                                <th scope="col">Venue</th>
                                <th scope="col">Type</th>
                                <th scope="col">Score</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody style="text-align:left">
                            {{-- {{dd($outcomes)}} --}}
                            @foreach ($outcomes as $outcome)
                                <tr>
                                    <td>{{ $outcome->identifier }}</td>
                                    <td><a href="{{ $outcome->url }}">{{ $outcome->title }}</a></td>
                                    <td>{{ $outcome->publication_date }}</td>
                                    <td>{{ $outcome->venue }}</td>
                                    <td>{{ $outcome->type }}</td>
                                    <td style="text-align:center">{{ $outcome->score }}</td>
                                    <td>
                                        <form id="deleteForm_{{ $outcome->id }}"
                                            action="{{ route('uploadedOutcomesDelete', ['p_id' => $outcome->id]) }}"
                                            method="POST" style="display: inline;">
                                            @csrf <!-- Add CSRF token for Laravel -->
                                            @method('POST') <!-- Specify the method as POST -->
                                            <button type="submit" class="btn btn-link btn-sm text-danger"
                                                onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                        </form>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div id="tab03" class="tab-contents">


                @if ($contribution)
                    <table class="table table-bordered" style="margin-top:20px;">

                        <thead class="text-center" style="color:teal;font-weight:bold;">
                            <tr>

                                <th scope="col">Type</th>
                                <th scope="col">Details</th>
                                <th scope="col">Score</th>
                                <th scope="col">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contribution as $item)
                                <tr>

                                    <td>{{ $typeMappings[$item->type] ?? $item->type }}</td>
                                    <td>{{ $item->detail }}</td>
                                    <td class="text-center">{{ $item->score }}</td>


                                    <td>
                                        <form id="deleteForm_{{ $item->id }}"
                                            action="{{ route('uploadedOutcomesDeleteContribution', ['id' => $item->id]) }}"
                                            method="POST" style="display: inline;">
                                            @csrf <!-- Add CSRF token for Laravel -->
                                            @method('POST') <!-- Specify the method as POST -->
                                            <button type="submit" class="btn btn-link btn-sm text-danger"
                                                onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                        </form>
                                    </td>



                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div id="tab04" class="tab-contents">

                @if ($students)
                    <table class="table table-bordered" style="margin-top:20px;">

                        <thead class="text-center" style="color:teal;font-weight:bold;">
                            <tr>

                                <th scope="col">Student ID</th>
                                <th scope="col">Level</th>
                                <th scope="col">Days of Working</th>
                                <th scope="col">Score</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr>

                                    <td>{{ $student->std_id }}</td>
                                    <td>{{ $typeMappings[$student->type] ?? $student->type }}</td>
                                    <td class="text-center">{{ $student->days }}</td>
                                    <td class="text-center">{{ $student->score }}</td>

                                    <td>
                                        <form id="deleteForm_{{ $student->id }}"
                                            action="{{ route('uploadedOutcomesDeleteStudent', ['id' => $student->id]) }}"
                                            method="POST" style="display: inline;">
                                            @csrf <!-- Add CSRF token for Laravel -->
                                            @method('POST') <!-- Specify the method as POST -->
                                            <button type="submit" class="btn btn-link btn-sm text-danger"
                                                onclick="return confirm('Are you sure you want to delete this item?')">Delete</button>
                                        </form>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div id="tab05" class="tab-contents">
                <style>
                    .sub-tab-button-outer {
                        display: block;
                        margin-bottom: 10px;
                    }
                    .sub-tab-button-outer ul {
                        display: table;
                        table-layout: fixed;
                        width: 100%;
                        margin: 0;
                        padding: 0;
                        list-style: none;
                    }
                    .sub-tab-button-outer ul li {
                        display: table-cell;
                        width: 50%;
                        cursor: pointer;
                    }
                    .sub-tab-button-outer ul li a {
                        display: block;
                        padding: .5em;
                        background: teal;
                        border: 2px solid teal;
                        text-align: center;
                        color: white;
                        text-decoration: none;
                        font-size: 15px;
                    }
                    .sub-tab-button-outer ul li:not(:first-child) a {
                        border-left: none;
                    }
                    .sub-tab-button-outer .is-active a {
                        border-bottom-color: transparent;
                        border: 2px solid #004c4c;
                        background: #004c4c;
                    }
                    .sub-tab-contents {
                        padding: .5em 2em 1em;
                        border: 2px solid teal;
                        border-top: 1px solid teal;
                    }
                </style>

                <div class="sub-tab-button-outer">
                    <ul id="sub-tab-button">
                        <li class="is-active"><a href="#subtab00">Progress Report 1</a></li>
                        @if ($project->has_progress_report2)
                        <li><a href="#subtab01">Progress Report 2</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Progress Report 1 --}}
                <div id="subtab00" class="sub-tab-contents">
                    @php
                        $filePath1 = 'uploads/progress_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
                        $fileExists1 = Storage::exists($filePath1);
                    @endphp

                    @if (!$fileExists1)
                        <div class="alert alert-warning">
                            The Progress Report 1 file has not been uploaded yet.
                        </div>
                    @else
                        <iframe
                            src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                            id="pdfViewer1" style="height:1200; width:100%; padding:10;"></iframe>
                    @endif
                </div>

                {{-- Progress Report 2 --}}
                <div id="subtab01" class="sub-tab-contents" style="display:none;">
                    @php
                        $filePath2 = 'uploads/progress_reports/' . $project->cycle_title . '/' . $project->old_project_id . '_2.pdf';
                        $fileExists2 = Storage::exists($filePath2);
                    @endphp

                    @if (!$fileExists2)
                        <div class="alert alert-warning">
                            The Progress Report 2 file has not been uploaded yet.
                        </div>
                    @else
                        <iframe
                            src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}_2.pdf"
                            id="pdfViewer2" style="height:1200; width:100%; padding:10;"></iframe>
                    @endif
                </div>
            </div>


            <div id="tab06" class="tab-contents">
                {{-- @if ($final_report) --}}

                @php

                    $filePath =
                        'uploads/final_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
                    $fileExists = Storage::exists($filePath);
                @endphp

                @if (!$fileExists)
                    <div class="alert alert-warning">
                        The requested file does not exist. contact support.
                    </div>
                @else
                    <iframe
                        src="{{ URL::to('/') }}/serveFile2?file=final_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                        id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                @endif


                {{-- <iframe
                    src="{{ URL::to('/') }}/serveFile2?file=final_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                    id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe> --}}
                {{-- @else
                <div class="alert alert-warning">
                    The requested file does not exist. contact support.
                </div>
            @endif --}}

            </div>


            <div id="tab07" class="tab-contents">

                @php
                    $filePath =
                        'uploads/readiness_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
                    $fileExists = Storage::exists($filePath);
                @endphp

                @if (!$fileExists)
                    <div class="alert alert-warning">
                        The requested file does not exist. contact support.
                    </div>
                @else
                    <iframe
                        src="{{ URL::to('/') }}/serveFile2?file=readiness_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                        id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                @endif
                {{--
            @if ($progress_report)
                <iframe
                    src="{{ URL::to('/') }}/serveFile2?file=readiness_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                    id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
            @else
                <p>Readiness Report not uploaded</p>
            @endif --}}

            </div>
        </div>


        <script>
            $(function() {
                var $tabButtonItem = $('#tab-button li'),
                    $tabSelect = $('#tab-select'),
                    $tabContents = $('.tab-contents'),
                    activeClass = 'is-active';

                $tabButtonItem.first().addClass(activeClass);
                $tabContents.not(':first').hide();

                $tabButtonItem.find('a').on('click', function(e) {
                    var target = $(this).attr('href');

                    $tabButtonItem.removeClass(activeClass);
                    $(this).parent().addClass(activeClass);
                    $tabSelect.val(target);
                    $tabContents.hide();
                    $(target).show();
                    e.preventDefault();
                });

                $tabSelect.on('change', function() {
                    var target = $(this).val(),
                        targetSelectNum = $(this).prop('selectedIndex');

                    $tabButtonItem.removeClass(activeClass);
                    $tabButtonItem.eq(targetSelectNum).addClass(activeClass);
                    $tabContents.hide();
                    $(target).show();
                });

                // Sub-tab functionality for Progress Report
                var $subTabButtonItem = $('#sub-tab-button li'),
                    $subTabContents = $('.sub-tab-contents');

                $subTabButtonItem.find('a').on('click', function(e) {
                    var target = $(this).attr('href');

                    $subTabButtonItem.removeClass('is-active');
                    $(this).parent().addClass('is-active');
                    $subTabContents.hide();
                    $(target).show();
                    e.preventDefault();
                });
            });
        </script>
    </body>

@endsection
