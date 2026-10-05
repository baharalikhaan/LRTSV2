 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

 <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">


 <script src="{{ asset('js/jquery.barrating.js') }}"></script>

 <link rel="stylesheet" href="{{ asset('css/vc-toggle-switch.css') }}">
 <link rel="stylesheet" href="{{ asset('css/bars-pill.css') }}">
 <link rel="stylesheet" href="{{ asset('css/bars-horizontal.css') }}">
 <link rel="stylesheet" href="{{ asset('css/themes/bars-movie.css') }}">



 <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/4.3.1/flatly/bootstrap.min.css">
 <script src="{{ asset('css/jquery.twbs-toggle-buttons.min.js') }}"></script>

 <style>
     .label-teal {
         background-color: #008080;
         /* Teal color */
         color: white;
         /* Text color */
         padding: 0.05em 0.4em;
         /* Reduced padding for a more oval shape */
         border-radius: 30px;
         /* Oval shape */
         display: inline-block;
         /* Ensure proper spacing */
     }

     .table-wrapper {
         overflow-x: auto;
         -webkit-overflow-scrolling: touch;
     }

     .table-wrapper table {
         min-width: 600px;
         /* Adjust based on your needs */
     }
 </style>

 <style>
     * {
         box-sizing: border-box
     }

     body,
     html {
         height: 100%;
         margin: 0;
         font-family: Arial;
     }


     input[type="radio"]:checked {
         background-color: teal;
         border-color: teal;
     }

     /* Customize the color for the unchecked radio button */
     input[type="radio"] {
         background-color: white;
         border-color: teal;
     }

     /* Style to hide the default radio button appearance */
     input[type="radio"] {
         -webkit-appearance: none;
         -moz-appearance: none;
         appearance: none;
         border-radius: 50%;
         width: 16px;
         height: 16px;
         border: 2px solid teal;
         outline: none;
         display: inline-block;
         vertical-align: middle;
         cursor: pointer;
     }


     /* Style tab links */
     .tablink {
         background-color: #66b2b2;
         color: beige;
         float: left;
         border: none;
         outline: none;
         cursor: pointer;
         padding: 14px 16px;
         font-size: 17px;
         font-weight: bold;
          width: 20%;
      }

      textarea {
         width: 100%;
         box-sizing: border-box;
     }

     .tablink:hover {
         background-color: #004c4c;
     }

     .borderless-modal .modal-dialog {
         margin: 10;
         position: absolute;
         top: 10%;
         left: 30%;
         transform: translate(-10%, -10%);
         max-width: 700px;
         width: 100%;
         background-color: "teal";
     }

     .borderless-modal .modal-content {
         border: none;
         border-radius: 10;
     }



     .editField {
         width: 50px;
         text-align: center;

     }

     .open-apimodal {
         cursor: pointer;
     }

     .open-apimodal2 {
         cursor: pointer;
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

     .w3-button {
         color: beige;
     }

     #bg i:hover {
         background: #ccc;
         color: #f58216;
     }

     .text-to-open-modal {
         cursor: pointer;
     }


     .btn-toggle {
         width: 45px;
         /* Adjusted width */
         height: 28px;
         /* Adjusted height */
         font-size: 11;
         margin: 0 2px;
         color: teal;
         background-color: #bef5e8;
         /* Light background color */
         border: 2px solid transparent;
         box-shadow: none;
         /* Remove shadow */
     }

     .btn-toggle.active {
         background-color: teal;
         /* Teal color for active button */
         color: white;
         border: 2px solid teal;
         box-shadow: none;
         /* Remove shadow */
         /* Border to match the active button */
     }

     .btn-group-toggle .btn {
         border-radius: 0;
         box-shadow: none;
         /* Remove shadow */
         /* Square corners */
     }
 </style>


 <head>
     <meta name="viewport" content="width=device-width, initial-scale=1">

     <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


 </head>

 <body>

     {{-- <h4 style="color:teal;">Project Title:</h4>

     <h5> {{ $project->title }}</h5> --}}


     <div class="row" style=" margin:5; ">
         <div class="col-md-12" style="padding-top: 10;">
             <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                 <div style=" margin: 5px;">
                     <div class="container">

                         <div class="section" style="margin:25">


                             <h2 style="color:teal;"> <a href="{{ route('project', ['c_id' => $project->cycle]) }} "
                                     {{-- class="w3-bar-item w3-button" --}}>
                                     <i class="fa fa-arrow-left" title="Back to List of projects"></i></a>
                             </h2>

                         </div>

                         <div class="section2">
                             <b> Grant Type: </b> <span class="label-teal"> Regular Grant</span><br>
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


     <div class="row" style=" margin:5; ">
         <div class="col-md-12" style="padding-top: 10;">
             <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                 <div style=" margin: 5px;">

                     <button id="Commitments1" class="tablink" onclick="openPage('Commitments', this,'teal')"
                         style=" border-radius: 30px 0px 0px 0px; ">Project Proposal</button>
                     <button id="ProgressReport1" class="tablink"
                         onclick="openPage('ProgressReport', this, 'teal')">Progress Report</button>
                     <button id="ProgressReport21" class="tablink"
                         onclick="openPage('ProgressReport2', this, 'teal')">Progress Report 2</button>
                     <button id="FinalReport1" class="tablink" onclick="openPage('FinalReport', this, 'teal')">Final
                         Report</button>
                     <button id="Outcomes1" class="tablink" onclick="openPage('Outcomes', this, 'teal')"
                         style=" border-radius: 0px 30px 0px 0px; ">QU Readiness Map</button>

                     <br>
                     <br>

                     <div id="Commitments" class="tabcontent">

                         {{-- @if (true) --}}
                         <div class="row">
                             <div class="col-md-8" style="margin-top: 10;">
                                 <div style="border: 2px solid teal; ">


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
                                         <p>
                                             Proposal not available
                                         </p>
                                     @else
                                         <iframe
                                             src="{{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ $project->cycle_title }}/{{ str_replace('/', '', $project->old_project_id) }}.pdf"
                                             id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                                     @endif
                                 </div>
                             </div>
                             <div class="col-md-4" style="margin-top: 10;">
                                 <div style="border: 2px solid teal; padding-bottom:50">
                                     <div style="margin: 20;">
                                         <h3 class="text-center"><b>Commitments</b>

                                             <span class="text-to-open-modal" data-toggle="modal"
                                                 data-target="#helpcommitments" align="center"><i
                                                     class="fa fa-question-circle" style="color:teal"></i></span>
                                         </h3>

                                         <br>
                                         <table class="table table-striped">
                                             <thead>
                                                 <tr>
                                                     <th>Proposal Date:</th>
                                                     <th colspan="2">

                                                         {{ $proposal_report ? date('d-m-Y', strtotime($proposal_report->created_at)) : '---' }}
                                                     </th>
                                                 </tr>
                                             </thead>
                                         </table>


                                         <div align="center">
                                             <form id="myForm">
                                                 @csrf
                                                 @if ($commitments)
                                                     <table class="table table-striped table-sm" style="font-size:12">
                                                         <colgroup>
                                                             <col span="1" style="width: 5%;text-align:center">
                                                             <col span="1" style="width: 70%;text-align:center">
                                                             <col span="1" style="width: 10%; text-align:center">
                                                         </colgroup>
                                                         <td></td>
                                                         <th style="color:teal;text-align:center">Commitments</th>
                                                         <th style="color:teal;text-align:center">No.</th>
                                                         <th style="color:teal;text-align:center">Score.</th>
                                                         <tr>

                                                             <td style="text-align:center; size:10px;display: none;"
                                                                 class="editable" data-column="project_id">
                                                                 {{ $commitments->project_id }}</td>
                                                         <tr>

                                                         <tr>
                                                             <td>-</td>
                                                             <td>Published articles in journals listed in Thomson
                                                                 Reuters Web of
                                                                 Science-SCI (Quartile in Category Q1)
                                                             </td>
                                                             <!-- <td style="text-align:center">{{ $commitments->q1article }}</td> -->
                                                             <td style="text-align:center; size:10px;"
                                                                 class="editable" data-column="q1article">
                                                                 {{ $commitments->q1article }}</td>
                                                             <td style="text-align:center">8</td>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Published articles in journals listed in Thomson
                                                                 Reuters Web of
                                                                 Science-SCI (Quartile in Category Q2)
                                                             </td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="q2article">
                                                                 {{ $commitments->q2article }}</td>
                                                             <td style="text-align:center">6</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Published articles in journals listed in Thomson
                                                                 Reuters Web of
                                                                 Science-SCI (Quartile in Category Q3)</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="q3article">
                                                                 {{ $commitments->q3article }}</td>
                                                             <td style="text-align:center">4</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Published articles in journals listed in Thomson
                                                                 Reuters Web of
                                                                 Science-SCI (Quartile in Category Q4)</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="q4article">
                                                                 {{ $commitments->q4article }}</td>
                                                             <td style="text-align:center">3</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Public articles in indexed international
                                                                 conferences </td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="confArticle">
                                                                 {{ $commitments->confArticle }}</td>
                                                             <td style="text-align:center">2</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Published Books</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="books">
                                                                 {{ $commitments->books }}</td>
                                                             <td style="text-align:center">8</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>
                                                                 Edited Books (collection)
                                                             </td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="editBooks">
                                                                 {{ $commitments->editBooks }}</td>
                                                             <td style="text-align:center">6</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>
                                                                 Book Chapters
                                                             </td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="chapters">
                                                                 {{ $commitments->chapters }}</td>
                                                             <td style="text-align:center">4</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Submitted IP Disclosure form submitted</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="ip">
                                                                 {{ $commitments->ip }}</td>
                                                             <td style="text-align:center">4</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Filed Provisional Patent</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="filedPatent">
                                                                 {{ $commitments->filedPatent }}</td>
                                                             <td style="text-align:center">7</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Open Source Software</td>
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="openSourceSW">
                                                                 {{ $commitments->openSourceSW }}</td>
                                                             <td style="text-align:center">8</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Created a Start-Up
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="startUp">
                                                                 {{ $commitments->startUp }}</td>
                                                             <td style="text-align:center">10</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>No. of UnderGrad Students Involved in Project
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="UG">
                                                                 {{ $commitments->UG }}</td>
                                                             <td style="text-align:center">1</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>No. of Masters Students Involved in Project
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="master">
                                                                 {{ $commitments->master }}</td>
                                                             <td style="text-align:center">2</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>No. of PhD Student Involved in Project
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="Phd">
                                                                 {{ $commitments->Phd }}</td>
                                                             <td style="text-align:center">3</td>
                                                         </tr>
                                                         <tr>
                                                             <td>-</td>
                                                             <td>Cross College Participation
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="crossCollege">
                                                                 {{ $commitments->crossCollege }}</td>
                                                             <td style="text-align:center">2</td>
                                                         </tr>

                                                         <tr>
                                                             <td>-</td>
                                                             <td>Does this project requires ethical approval?
                                                             <td style="text-align:center" class="editable"
                                                                 data-column="ethical">
                                                                 {{ $commitments->ethical == 0 ? 'No' : 'Yes' }}
                                                             </td>
                                                             <td style="text-align:center"></td>
                                                         </tr>


                                                     </table>
                                                 @endif
                                             </form>
                                             <div style="float:right;">
                                                 <button id="editBtn"
                                                     style="background-color:  teal;  border-color: teal; color: white;"
                                                     class="btn btn-sm">Edit</button>
                                                 <button id="saveBtn"
                                                     style="display:none; background-color:  teal;  border-color: teal; color: white;"
                                                     class="btn btn-sm">Save</button>
                                                 <button id="cancelBtn"
                                                     style="display:none; background-color:  teal;  border-color: teal; color: white;"
                                                     class="btn btn-sm">Cancel</button>

                                             </div>


                                         </div>

                                     </div>

                                 </div>
                             </div>

                         </div>
                         {{-- @else
                             <b class='alert'>Commitments for this project are not avaialable</b> <br>
                             <br>
                         @endif --}}



                     </div>


                     <div id="ProgressReport" class="tabcontent">

                         @php
                             $filePath =
                                 'uploads/progress_reports/' .
                                 $project->cycle_title .
                                 '/' .
                                 $project->old_project_id .
                                 '.pdf';
                             $fileExists = Storage::exists($filePath);
                         @endphp

                         <div class="container-fluid">
                             @if (true)
                                 <div class="row">
                                     <div class="col-md-8" style="margin-top: 10;">
                                         <div style="border: 2px solid teal; ">
                                             @if ($progress_report)

                                                 @if (!$fileExists)
                                                     <p> Progress Report not available</p>
                                                 @else
                                                     <iframe
                                                         src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                                                         id="pdfViewer"
                                                         style="height:1200; width:100%; padding:10;"></iframe>
                                                 @endif
                                             @else
                                                 <p> Progress Report not available</p>
                                             @endif
                                         </div>
                                     </div>

                                     <div class="col-md-4" style="margin-top: 10;">
                                         <div style="border: 2px solid teal; ">
                                             <div style="margin: 20;">

                                                 <div align="center">
                                                     @if (session('successprogressgrade'))
                                                         {!! session('successprogressgrade') !!}
                                                         @php
                                                             session()->forget('successprogressgrade');
                                                         @endphp
                                                     @endif
                                                 </div>
                                                 <br>

                                                 <h3 class="text-center"><b>Progress Report</b>
                                                     <span class="text-to-open-modal" data-toggle="modal"
                                                         data-target="#helpprogress" align="center"><i
                                                             class="fa fa-question-circle"
                                                             style="color:teal"></i></span>
                                                 </h3>
                                                 <br>
                                                 @if (isset($progressComments))
                                                     @if ($progressComments->publish == 'pending')
                                                         @include('components.progressGradingForm', [
                                                             'progressComments' => $progressComments,
                                                         ])
                                                     @else
                                                         @include('components.progressGrades', [
                                                             'progressComments' => $progressComments,
                                                         ])
                                                     @endif
                                                 @else
                                                     @include('components.progressGradingForm')
                                                 @endif

                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             @else
                                 <b class='alert'>Progress Report for this project is not avaialable</b> <br>
                                 <br>
                             @endif
                         </div>

                     </div>

                     <div id="ProgressReport2" class="tabcontent">

                         @php
                             $filePath2 =
                                 'uploads/progress_reports/' .
                                 $project->cycle_title .
                                 '/' .
                                 $project->old_project_id .
                                 '_pr2.pdf';
                             $fileExists2 = Storage::exists($filePath2);
                         @endphp

                         <div class="container-fluid">
                             <div class="row">
                                 <div class="col-md-8" style="margin-top: 10;">
                                     <div style="border: 2px solid teal; ">
                                         @if ($progress_report2)
                                             @if (!$fileExists2)
                                                 <p> Progress Report 2 not available</p>
                                             @else
                                                 <iframe
                                                     src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}_pr2.pdf"
                                                     id="pdfViewer"
                                                     style="height:1200; width:100%; padding:10;"></iframe>
                                             @endif
                                         @else
                                             <p> Progress Report 2 not available</p>
                                         @endif
                                     </div>
                                 </div>

                                 <div class="col-md-4" style="margin-top: 10;">
                                     <div style="border: 2px solid teal; ">
                                         <div style="margin: 20;">

                                             <div align="center">
                                                 @if (session('successprogressgrade2'))
                                                     {!! session('successprogressgrade2') !!}
                                                     @php
                                                         session()->forget('successprogressgrade2');
                                                     @endphp
                                                 @endif
                                             </div>
                                             <br>

                                             <h3 class="text-center"><b>Progress Report 2</b>
                                                 <span class="text-to-open-modal" data-toggle="modal"
                                                     data-target="#helpprogress" align="center"><i
                                                         class="fa fa-question-circle"
                                                         style="color:teal"></i></span>
                                             </h3>
                                             <br>
                                              @if (isset($progressComments2))
                                                  @if ($progressComments2->publish == 'pending')
                                                      @include('components.progressGradingForm', [
                                                          'progressComments' => $progressComments2,
                                                          'reportType' => 'Progress2',
                                                      ])
                                                  @else
                                                      @include('components.progressGrades', [
                                                          'progressComments' => $progressComments2,
                                                      ])
                                                  @endif
                                              @else
                                                  @include('components.progressGradingForm', [
                                                      'reportType' => 'Progress2',
                                                  ])
                                              @endif

                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                     </div>

                     <div id="FinalReport" class="tabcontent">

                         @php

                             $filePath =
                                 'uploads/final_reports/' .
                                 $project->cycle_title .
                                 '/' .
                                 $project->old_project_id .
                                 '.pdf';
                             $fileExists = Storage::exists($filePath);
                         @endphp


                         <div class="container-fluid">
                             <div class="row">
                                 <div class="col-md-8" style="margin-top: 10;">
                                     <div style="border: 2px solid teal; ">
                                         @if ($final_report)
                                             @if (!$fileExists)
                                                 <p>Final Report not available</p>
                                             @else
                                                 <iframe
                                                     src="{{ URL::to('/') }}/serveFile2?file=final_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                                                     id="pdfViewer"
                                                     style="height:1200; width:100%; padding:10;"></iframe>
                                             @endif
                                         @else
                                             <p>Final Report not available</p>
                                         @endif
                                     </div>
                                 </div>

                                 <div class="col-md-4" style="margin-top: 10;">
                                     <div style="border: 2px solid teal;">
                                         <div style="margin:20">
                                             @foreach ($errors->all() as $error)
                                                 <div class="alert alert-danger">{{ $error }}</div>
                                             @endforeach

                                             <div align="center">
                                                 @if (session('successfinalgrade'))
                                                     {!! session('successfinalgrade') !!}
                                                     @php
                                                         session()->forget('successfinalgrade');
                                                     @endphp
                                                 @endif
                                             </div>
                                             <br>

                                             <h3 class="text-center"><b>Final Report</b>
                                                 <span class="text-to-open-modal" data-toggle="modal"
                                                     data-target="#helpfinal" align="center"><i
                                                         class="fa fa-question-circle" style="color:teal"></i></span>
                                             </h3>

                                             <br>


                                             @if (isset($finalGrades->gradeB))
                                                 @if ($finalGrades->publish == 'accepted')
                                                     @include('components.finalGrades', [
                                                         'finalGrades' => $finalGrades,
                                                     ])
                                                 @else
                                                     <div>

                                                         <form method="POST" action="{{ route('finalGrades') }}">
                                                             @csrf
                                                             <input type="text" name="p_id"
                                                                 value="{{ $p_id }}" hidden>

                                                             <label class="col-form-label"><b>1.
                                                                     Achievements</b></label><br>
                                                             <p>Degree of realization of the proposed outcomes in
                                                                 the
                                                                 project. Does the
                                                                 project produce a Prototype, Patent, Open Source
                                                                 Software, etc.? If a
                                                                 Prototype is achieved, state its TRL level (or SRL
                                                                 for
                                                                 society
                                                                 readiness)</p>

                                                             <table class="table">
                                                                 @foreach ($contributions as $contribution)
                                                                     <tr>
                                                                         <td>
                                                                             <input type="checkbox" name="option1"
                                                                                 value="{{ $contribution['score'] }}"
                                                                                 checked>
                                                                         </td>
                                                                         <td>{{ $typeMappings[$contribution['type']] ?? $contribution['type'] }}
                                                                         </td>
                                                                         <td>{{ $contribution['detail'] }}<br></td>
                                                                     </tr>
                                                                 @endforeach
                                                             </table>

                                                             <label>a. Score</label>


                                                             <input type="text" id="gradeA" name='gradeA'
                                                                 size="2">

                                                             <br>
                                                             <label>b. Comment</label><br>
                                                             <textarea id="commentA" name="commentA" rows="4" cols="40">{{ $finalGrades->commentA }}</textarea><br><br>

                                                             <label class="col-form-label"><b>2.
                                                                     Publications</b></label><br>

                                                             <p>Number of Q1/Q2 publications in ranked journals.
                                                                 Number
                                                                 of Q1
                                                                 publications in highly ranked journals. Number and
                                                                 quality of Books,
                                                                 Chapters, etc</p>

                                                             <p style="color:teal; font-size:12px; ">
                                                                 (Kindly visit each
                                                                 research artifact and ensure that the LPI has
                                                                 acknowledged the QU
                                                                 funding in the artifact. If not acknowledged, then
                                                                 uncheck the artifact
                                                                 from the list.)</p>
                                                             <div class="table-wrapper">
                                                                 <div class="table-responsive">
                                                                     <table id="myTable"
                                                                         class="table table-bordered table-striped table-hover">
                                                                         <thead>
                                                                             <tr>
                                                                                 <th></th>
                                                                                 <th style="width:50px;">Identifier
                                                                                 </th>
                                                                                 <th>Type</th>
                                                                                 <th>Publish date</th>
                                                                                 <th>Link</th>
                                                                             </tr>
                                                                         </thead>
                                                                         <tbody>
                                                                             @foreach ($outcomes as $outcome)
                                                                                 <tr>
                                                                                     <td>
                                                                                         <input type="checkbox"
                                                                                             name="option2"
                                                                                             value="{{ $outcome['score'] }}"
                                                                                             checked>
                                                                                     </td>
                                                                                     <td class="open-apimodal"
                                                                                         data-toggle="modal"
                                                                                         data-mydata="{{ $outcome['identifier'] }}">
                                                                                         {{ $outcome['identifier'] }}
                                                                                     </td>
                                                                                     <td>{{ $outcome['type'] }}</td>
                                                                                     <td>{{ \Carbon\Carbon::parse($outcome['publication_date'])->format('d-m-Y') }}
                                                                                     </td>
                                                                                     <td>
                                                                                         <a href="{{ $outcome['url'] }}"
                                                                                             target="_blank">link</a>
                                                                                     </td>
                                                                                 </tr>
                                                                             @endforeach
                                                                         </tbody>
                                                                     </table>
                                                                 </div>
                                                             </div>
                                                             <br>
                                                             <label>a. Score</label>
                                                             <input type="text" id="gradeB" name='gradeB'
                                                                 size="2">


                                                             <br>
                                                             <label>b. Comment</label><br>
                                                             <textarea id="commentB" name="commentB" rows="4" cols="40">{{ $finalGrades->commentB }}</textarea><br><br>

                                                             <b>3. Student and young researchers
                                                                 involements</b><br><br>
                                                             <input type="radio" id="Yes" value="Yes"
                                                                 name="yesno" onclick="show();" checked>
                                                             <label for="Yes">Yes</label><br>
                                                             <input type="radio" id="No" value="No"
                                                                 name="yesno" onclick="hide();">
                                                             <label for="No">No</label><br>

                                                             <div id="YR">
                                                                 <p>Level of engagement of graduate students in the
                                                                     activities of the
                                                                     project. Training of undergraduate students.
                                                                     Such
                                                                     as involvement of
                                                                     RAs and GAs in the project</p>
                                                                 <!-- <label class="col-form-label"><b>3. Young Researcher Supervision</b></label><br> -->
                                                                 <table class="table">
                                                                     <th></th>
                                                                     <th>Student ID</th>
                                                                     <th>Level</th>
                                                                     <th>Days</th>
                                                                     @foreach ($students as $student)
                                                                         <tr>
                                                                             <td>
                                                                                 <input type="checkbox" name="option3"
                                                                                     value="{{ $student['score'] }}"
                                                                                     checked>
                                                                             </td>

                                                                             <td class="open-apimodal2"
                                                                                 data-toggle="modal"
                                                                                 data-mydata="{{ $student['std_id'] }}">
                                                                                 {{ $student['std_id'] }}</td>


                                                                             <td>{{ $typeMappings[$student['type']] ?? $student['type'] }}
                                                                             </td>
                                                                             <td>{{ $student['days'] }}</td>
                                                                             <td><br></td>
                                                                         </tr>
                                                                     @endforeach
                                                                 </table>
                                                                 <br>
                                                                 <label>a. Score</label>
                                                                 <input type="text" id="gradeD" name='gradeD'
                                                                     size="2">

                                                                 <br>
                                                                 <label>b. Comment</label><br>
                                                                 <textarea id="commentD" name="commentD" rows="4" cols="40">{{ $finalGrades->commentD }}</textarea><br><br>
                                                             </div>

                                                             <label class="col-form-label"><b>4. Project
                                                                     Impact</b></label><br>
                                                             <p>Has the project provided concise KPIs for the
                                                                 proposed
                                                                 outcomes? The
                                                                 value of the reported outcomes (e.g., KPIs) in
                                                                 comparison to what was
                                                                 suggested in the proposal on
                                                                 industry/society/government, etc. The
                                                                 potential to benefit society or advance desired
                                                                 economical (e.g.,
                                                                 technology transfer) and societal outcomes (e.g.
                                                                 capacity building of
                                                                 students and researchers, change in policy). The
                                                                 level
                                                                 of engagement
                                                                 with end-users. Extent to which end-users locally
                                                                 and
                                                                 internationally
                                                                 may realistically benefit from the outcomes. The
                                                                 relevance of the
                                                                 project to partners’ development with respect to
                                                                 industrial
                                                                 development, socio-economic, health and
                                                                 environmental
                                                                 aspects and the
                                                                 ability to address end-user needs, as well as the
                                                                 potential to create
                                                                 positive international scientific visibility for
                                                                 the
                                                                 partners (if any).
                                                             </p>

                                                             <label>a. Score</label>
                                                             <input id="gradeC" type="text" name='gradeC'
                                                                 value=0 size="2">

                                                             <span style="color:teal; font-size:12">Kindly grade
                                                                 this project impact from 0 to 4</span>
                                                             <br>
                                                             <label>b. Comment</label><br>
                                                             <textarea id="commentC" name="commentC" rows="4" cols="40">{{ $finalGrades->commentC }}</textarea>
                                                             <br><br>



                                                             <button type="submit" name="draft" value="draft"
                                                                 class="btn btn-secondary">
                                                                 Save As Draft
                                                             </button>
                                                             <button type="submit" class="btn btn-primary"
                                                                 name="publish" value="publish">
                                                                 {{ __('Submit') }}
                                                             </button>

                                                         </form>
                                                     </div>
                                                 @endif
                                             @else
                                                 <div>
                                                     <form method="POST" action="{{ route('finalGrades') }}">
                                                         @csrf
                                                         <input type="text" name="p_id"
                                                             value="{{ $p_id }}" hidden>

                                                         <label class="col-form-label"><b>1.
                                                                 Achievements</b></label><br>
                                                         <p>Degree of realization of the proposed outcomes in the
                                                             project. Does the
                                                             project produce a Prototype, Patent, Open Source
                                                             Software, etc.? If a
                                                             Prototype is achieved, state its TRL level (or SRL for
                                                             society
                                                             readiness)</p>

                                                         <table class="table">
                                                             @foreach ($contributions as $contribution)
                                                                 <tr>
                                                                     <td>
                                                                         <input type="checkbox" name="option1"
                                                                             value="{{ $contribution['score'] }}"
                                                                             checked>
                                                                     </td>
                                                                     <td>{{ $typeMappings[$contribution['type']] ?? $contribution['type'] }}
                                                                     </td>
                                                                     <td>{{ $contribution['detail'] }}<br></td>
                                                                 </tr>
                                                             @endforeach
                                                         </table>

                                                         <label>a. Score</label>
                                                         <input type="text" id="gradeA" name='gradeA'
                                                             size="2">
                                                         <br>
                                                         <label>b. Comment</label><br>
                                                         <textarea id="commentA" name="commentA" rows="4" cols="40"></textarea><br><br>

                                                         <label class="col-form-label"><b>2.
                                                                 Publications</b></label><br>

                                                         <p>Number of Q1/Q2 publications in ranked journals. Number
                                                             of Q1
                                                             publications in highly ranked journals. Number and
                                                             quality of Books,
                                                             Chapters, etc</p>

                                                         <p style="color:teal; font-size:12px; ">
                                                             (Kindly visit each
                                                             research artifact and ensure that the LPI has
                                                             acknowledged the QU
                                                             funding in the artifact. If not acknowledged, then
                                                             uncheck the artifact
                                                             from the list.)</p>
                                                         <div class="table-wrapper">
                                                             <div class="table-responsive">
                                                                 <table id="myTable"
                                                                     class="table table-bordered table-striped table-hover">
                                                                     <thead>
                                                                         <tr>
                                                                             <th></th>
                                                                             <th style="width:20px;">Identifier</th>
                                                                             <th>Type</th>
                                                                             <th>Publish date</th>
                                                                             <th>Link</th>
                                                                         </tr>
                                                                     </thead>
                                                                     <tbody>
                                                                         @foreach ($outcomes as $outcome)
                                                                             <tr>
                                                                                 <td>
                                                                                     <input type="checkbox"
                                                                                         name="option2"
                                                                                         value="{{ $outcome['score'] }}"
                                                                                         checked>
                                                                                 </td>
                                                                                 <td class="open-apimodal"
                                                                                     data-toggle="modal"
                                                                                     data-mydata="{{ $outcome['identifier'] }}">
                                                                                     {{ $outcome['identifier'] }}
                                                                                 </td>
                                                                                 <td>{{ $outcome['type'] }}</td>
                                                                                 <td>{{ \Carbon\Carbon::parse($outcome['publication_date'])->format('d-m-Y') }}
                                                                                 </td>
                                                                                 <td>
                                                                                     <a href="{{ $outcome['url'] }}"
                                                                                         target="_blank">link</a>
                                                                                 </td>
                                                                             </tr>
                                                                         @endforeach
                                                                     </tbody>
                                                                 </table>
                                                             </div>
                                                         </div>

                                                         <br>
                                                         <label>a. Score</label>
                                                         <input type="text" id="gradeB" name='gradeB'
                                                             size="2">

                                                         <br>
                                                         <label>b. Comment</label><br>
                                                         <textarea id="commentB" name="commentB" rows="4" cols="40"></textarea><br><br>

                                                         <b>3. Student and young researchers involements</b><br><br>
                                                         <input type="radio" id="Yes" value="Yes"
                                                             name="yesno" onclick="show();" checked>
                                                         <label for="Yes">Yes</label><br>
                                                         <input type="radio" id="No" value="No"
                                                             name="yesno" onclick="hide();">
                                                         <label for="No">No</label><br>

                                                         <div id="YR">
                                                             <p>Level of engagement of graduate students in the
                                                                 activities of the
                                                                 project. Training of undergraduate students. Such
                                                                 as involvement of
                                                                 RAs and GAs in the project</p>
                                                             <!-- <label class="col-form-label"><b>3. Young Researcher Supervision</b></label><br> -->
                                                             <table class="table">
                                                                 <th></th>
                                                                 <th>Student ID</th>
                                                                 <th>Level</th>
                                                                 <th>Days</th>
                                                                 @foreach ($students as $student)
                                                                     <tr>
                                                                         <td>
                                                                             <input type="checkbox" name="option3"
                                                                                 value="{{ $student['score'] }}"
                                                                                 checked>
                                                                         </td>

                                                                         <td class="open-apimodal2"
                                                                             data-toggle="modal"
                                                                             data-mydata="{{ $student['std_id'] }}">
                                                                             {{ $student['std_id'] }}</td>


                                                                         <td>{{ $typeMappings[$student['type']] ?? $student['type'] }}
                                                                         </td>
                                                                         <td>{{ $student['days'] }}</td>
                                                                         <td><br></td>
                                                                     </tr>
                                                                 @endforeach
                                                             </table>
                                                             <br>
                                                             <label>a. Score</label>
                                                             {{-- <input type="text" id="gradeD" name='gradeD'
                                                                 size="2" style="border: none; outline: none; background: none; color:teal; font-size:16; float:right; font-weight: bold;"> --}}
                                                             <input type="text" id="gradeD" name='gradeD'
                                                                 size="2">

                                                             <br>
                                                             <label>b. Comment</label><br>
                                                             <textarea id="commentD" name="commentD" rows="4" cols="40"></textarea><br><br>
                                                         </div>

                                                         <label class="col-form-label"><b>4. Project
                                                                 Impact</b></label><br>
                                                         <p>Has the project provided concise KPIs for the proposed
                                                             outcomes? The
                                                             value of the reported outcomes (e.g., KPIs) in
                                                             comparison to what was
                                                             suggested in the proposal on
                                                             industry/society/government, etc. The
                                                             potential to benefit society or advance desired
                                                             economical (e.g.,
                                                             technology transfer) and societal outcomes (e.g.
                                                             capacity building of
                                                             students and researchers, change in policy). The level
                                                             of engagement
                                                             with end-users. Extent to which end-users locally and
                                                             internationally
                                                             may realistically benefit from the outcomes. The
                                                             relevance of the
                                                             project to partners’ development with respect to
                                                             industrial
                                                             development, socio-economic, health and environmental
                                                             aspects and the
                                                             ability to address end-user needs, as well as the
                                                             potential to create
                                                             positive international scientific visibility for the
                                                             partners (if any).
                                                         </p>

                                                         <label>a. Score</label>
                                                         <input type="text" id="gradeC" name='gradeC'
                                                             size="2">
                                                         <br>
                                                         <label>b. Comment</label><br>
                                                         <textarea id="commentC" name="commentC" rows="4" cols="40"></textarea>
                                                         <br><br>



                                                         <button type="submit" name="draft" value="draft"
                                                             class="btn btn-secondary">
                                                             Save As Draft
                                                         </button>
                                                         <button type="submit" class="btn btn-primary"
                                                             name="publish" value="publish">
                                                             {{ __('Submit') }}
                                                         </button>

                                                     </form>
                                                 </div>
                                             @endif
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>


                     <div id="Outcomes" class="tabcontent" align="center">
                         <!-- Ask doctor what should I put here -->
                         @if ($verify_outcomes)
                             @include('components.gradedOutcomes', ['verify_outcomes' => $verify_outcomes])
                         @elseif(true)
                             <div class="row">
                                 <div class="col-md-8" style="margin-top: 10;">
                                     <div style="border: 2px solid teal; ">

                                         @php

                                             $filePath =
                                                 'uploads/readiness_reports/' .
                                                 $project->cycle_title .
                                                 '/' .
                                                 $project->old_project_id .
                                                 '.pdf';
                                             $fileExists = Storage::exists($filePath);
                                         @endphp

                                         @if ($readiness_report)

                                             @if (!$fileExists)
                                                 <p>Readiness Report not available</p>
                                             @else
                                                 <iframe
                                                     src="{{ URL::to('/') }}/serveFile2?file=readiness_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                                                     id="pdfViewer"
                                                     style="height:1200; width:100%; padding:10;"></iframe>
                                             @endif
                                         @else
                                             <p>Readiness Report not available</p>
                                         @endif
                                     </div>
                                 </div>


                                 <div class="col-md-4" style="margin-top: 10;">
                                     <div style="border: 2px solid teal; ">
                                         <div style="margin: 20;">

                                             <h3 class="text-center"><b>QU Readiness Mapping</b>

                                                 <span class="text-to-open-modal" data-toggle="modal"
                                                     data-target="#helpreadiness" align="center"><i
                                                         class="fa fa-question-circle" style="color:teal"></i></span>
                                             </h3>

                                             <br>

                                             <div align="center">
                                                 @if (session('successreadinessmapping_publish'))
                                                     {!! session('successreadinessmapping_publish') !!}
                                                     @php
                                                         session()->forget('successreadinessmapping_publish');
                                                     @endphp
                                                 @endif

                                                 @if (session('successreadinessmapping_draft'))
                                                     {!! session('successreadinessmapping_draft') !!}
                                                     @php
                                                         session()->forget('successreadinessmapping_draft');
                                                     @endphp
                                                 @endif

                                             </div>



                                             <table class="table table-striped">

                                                 <thead>

                                                     <tr>
                                                         <th>Readiness Report Date:</th>
                                                         <th colspan="2">
                                                             {{ $readiness ? date('d-m-Y', strtotime($readiness->created_at)) : '---' }}
                                                         </th>

                                                     </tr>
                                                 </thead>
                                             </table>
                                             {{-- Contents related to QU Readiness Mapping goes here. --}}
                                         </div>

                                     </div>
                                 </div>

                             </div>
                         @endif
                     </div>
                 </div>
             </div>
         </div>
     </div>





     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="studentApiModal" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Student Verification</h5>
                 </div>

                 <div class="modal-body">

                     <table id="responseTable" class="table table-bordered table-sm">
                         <tbody id="responseBody"></tbody>
                     </table>

                 </div>
             </div>
         </div>
     </div>


     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="apiModal" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">DOI Verification</h5>
                 </div>

                 <div class="modal-body">

                 </div>
             </div>
         </div>
     </div>


     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="helpreadiness" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Help - QU Readiness Mapping</h5>



                 </div>

                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     {{-- <h5>Help Regarding QU Readiness Mapping</h5>
                     <p>Help Regarding QU Readiness Mapping</p> --}}
                 </div>
             </div>
         </div>
     </div>



     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="helpprogress" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Help - Progress Report</h5>


                 </div>

                 <!-- Modal Body -->
                 <div class="modal-body">


                     <b>A. Grading guideline</b>
                     <ul>
                         <li>Kindly evaluate the progress report based on the following criteria on a scale of 1 to 5
                         </li>
                         <li>1 indicates the highest level of dissatisfaction and 5 indicates the highest level of
                             satisfaction</li>

                     </ul>

                 </div>
             </div>
         </div>
     </div>


     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="helpcommitments" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Help - Commitments</h5>

                 </div>
                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     {{-- <h5>Help Regarding Commitments</h5>
                     <p>Help Regarding Commitments</p> --}}
                 </div>
             </div>
         </div>
     </div>

     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="helpfinal" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Help - Final Report</h5>

                 </div>
                 <!-- Modal Body -->
                 <div class="modal-body">


                     <b>A. Achievements</b>
                     <ul>
                         <li>Degree of realization of the proposed outcomes in the project</li>
                         <li>Does the project produced a Prototype, Patent, Open Source Software, etc.?</li>
                         <li>If a Prototype is achieved, state its TRL level (or SRL for society readiness)</li>
                     </ul>
                     <b>B. Publication</b>
                     <ul>
                         <li>Number of Q1/Q2 publications in ranked journals</li>
                         <li>Number of Q1 publications in highly ranked journals</li>
                         <li>Number and quality of Books, Chapters, etc</li>
                     </ul>


                     <b>C. Did the project commited in Students and Young Researchers Supervision?</b>
                     <ul>
                         <li>Level of engagement of graduate students in the activities of the proejct</li>
                         <li>Training of undergraduate students</li>
                         <li>Involvement of RAs and GAs in the project</li>
                     </ul>

                     <b>D. Project Impact</b>
                     <ul>
                         <li>Has the project provided concise KPIs for the proposed outcomes?</li>
                         <li>The value of the reported outcomes (e.g., KPIs) in comparison to what was suggested in the
                             proposal on industry/society/government, etc.</li>
                         <li>The potential to benefit society or advance desired economical (e.g., Patents, technology
                             transfer) and societal outcomes (e.g. capacity building of students and researchers, change
                             in policy)</li>
                         <li>The level of engagement with end-users. Extent to which end-users locally and
                             internationally may realistically benefit from the outcomes..</li>
                         <li>The relevance of the project to partners’ development with respect to industrial
                             development, socio-economic, health and environmental aspects and the ability to address
                             end-user needs, as well as the potential to create positive international scientific
                             visibility for the partners (if any).</li>
                     </ul>
                 </div>
             </div>
         </div>
     </div>

     {{-- {{dd($progressComments)}} --}}
     <script>
         $(document).ready(function() {

             // Hide Progress Report 2 button if project is not extended
             var hasPR2 = {{ $project->has_progress_report2 ? 'true' : 'false' }};
             if (!hasPR2) {
                 $('#ProgressReport21').hide();
                 $('.tablink').css('width', '25%');
             }

             var aa = document.getElementById("ProgressReport1");
             @if (session('tab') == 'ProgressReport')
                 tabcontent = document.getElementsByClassName("tabcontent");

                 openPage('ProgressReport', aa, 'teal');
             @endif


             var aa = document.getElementById("FinalReport1");
             @if (session('tab') == 'FinalReport')
                 tabcontent = document.getElementsByClassName("tabcontent");

                 openPage('FinalReport', aa, 'teal');
             @endif


             $('#achievementsRating').val(
                 '{{ $progressComments != null ? $progressComments->achievementsRating : '' }}');
             $('#publicationsRating').val(
                 '{{ $progressComments != null ? $progressComments->publicationsRating : '' }}');
             $('#studentsRating').val('{{ $progressComments != null ? $progressComments->studentsRating : '' }}');



             $('#budgetRating').val('{{ $progressComments != null ? $progressComments->budgetRating : '' }}');



             $('#budgetRating').barrating('show', {
                 theme: 'bars-pill',
                 initialRating: '{{ $progressComments != null ? $progressComments->budgetRating : '' }}',
                 showValues: true,

                 showSelectedRating: true,
                 allowEmpty: false,
                 emptyValue: '-- no rating selected --',
                 onSelect: function(value, text) {

                 }
             });

             $('#achievementsRating').barrating('show', {
                 theme: 'bars-pill',
                 initialRating: '{{ $progressComments != null ? $progressComments->achievementsRating : '' }}',
                 showValues: true,

                 showSelectedRating: true,
                 allowEmpty: false,
                 emptyValue: '-- no rating selected --',
                 onSelect: function(value, text) {

                 }
             });

             $('#publicationsRating').barrating('show', {
                 theme: 'bars-pill',
                 initialRating: '{{ $progressComments != null ? $progressComments->publicationsRating : '' }}',
                 showValues: true,

                 showSelectedRating: true,
                 allowEmpty: false,
                 emptyValue: '-- no rating selected --',
                 onSelect: function(value, text) {

                 }
             });


             $('#studentsRating').barrating('show', {
                 theme: 'bars-pill',
                 initialRating: '{{ $progressComments != null ? $progressComments->studentsRating : '' }}',
                 showValues: true,
                 showSelectedRating: true,
                 allowEmpty: false,
                 emptyValue: '-- no rating selected --',
                 onSelect: function(value, text) {

                 },

             });


             $('#ethicalApproval').barrating('show', {
                 theme: 'bars-pill',
                 initialRating: '{{ $progressComments != null ? $progressComments->studentsRating : '' }}',
                 showValues: true,
                 showSelectedRating: true,
                 allowEmpty: false,
                 emptyValue: '-- no rating selected --',
                 onSelect: function(value, text) {

                 },

             });

             // Initialize barRating for Progress Report 2 selects
             $('#ProgressReport2').find('select[id="achievementsRating"], select[id="publicationsRating"], select[id="studentsRating"], select[id="budgetRating"]').each(function() {
                 var $select = $(this);
                 var initialVal = $select.val() || '';
                 $select.barrating('show', {
                     theme: 'bars-pill',
                     initialRating: initialVal,
                     showValues: true,
                     showSelectedRating: true,
                     allowEmpty: false,
                     emptyValue: '-- no rating selected --',
                 });
             });

             $('#editBtn').click(function() {
                 $('.editable').each(function() {
                     var value = $(this).text().trim();
                     $(this).data('originalValue', value); // Store original value

                     // Check data-column attribute
                     if ($(this).data('column') === 'ethical') {
                         var selectHtml = `
                <select class="editField">
                    <option value="Yes" ${value === 'Yes' ? 'selected' : ''}>Yes</option>
                    <option value="No" ${value === 'No' ? 'selected' : ''}>No</option>
                </select>
            `;
                         $(this).html(selectHtml);
                     } else {
                         $(this).html('<input type="text" class="editField" value="' + value + '">');
                     }
                 });

                 $('#editBtn').hide();
                 $('#saveBtn').show();
                 $('#cancelBtn').show();
             });


             //  $('#editBtn').click(function() {
             //      $('.editable').each(function() {
             //          var value = $(this).text().trim();
             //          $(this).data('originalValue', value); // Store original value
             //          $(this).html('<input type="text" class="editField" value="' + value + '">');
             //          console.log(value);
             //      });
             //      $('#editBtn').hide();
             //      $('#saveBtn').show();
             //      $('#cancelBtn').show();
             //  });

             $('#cancelBtn').click(function() {
                 $('.editable').each(function() {
                     var originalValue = $(this).data('originalValue');
                     $(this).text(originalValue); // Revert back to original value
                 });
                 $('.editField').remove(); // Remove text boxes
                 $('#editBtn').show();
                 $('#saveBtn').hide();
                 $('#cancelBtn').hide();
             });


         });

         $('#saveBtn').click(function() {
             var data = {};
             $('.editField').each(function() {
                 var column = $(this).parent().data('column');
                 var value = $(this).val().trim();
                 data[column] = value;
             });
             // var data = $('#myForm').serialize();
             data['_token'] = "{{ csrf_token() }}";

             $.ajax({
                 type: 'POST',
                 url: "{{ route('updateCommitments') }}",
                 data: data,
                 success: function(response) {
                     console.log(response);
                     location.reload();
                 },
                 error: function(xhr, status, error) {
                     console.error(xhr.responseText);
                 }
             });
         });


         $('.open-apimodal2').on('click', function() {

             $('#responseBody').html('');

             $('responseBody').append("<h5 align='center'>Please wait...</h5>")

             $('#studentApiModal').modal('show');

             var studentid = $(this).data('mydata');

             /////////////////////////
             //  var arrayData = {

             //  var jsonData = JSON.stringify(arrayData);
             //  var items = arrayData['items'];

             //  var tableBody = $('#responseBody');

             //  $.each(items, function(index, item) {
             //      $.each(item, function(key, value) {
             //          var row = $('<tr>');
             //          row.append($('<th>').text(key));
             //          row.append($('<td>').text(value));
             //          tableBody.append(row);
             //      });
             //  });

             $.ajax({
                 //  url: 'http://quapxweb1.qu.edu.qa/sisapx/qusis/student_info/std',
                 url: "{{ route('StudentAPI') }}?id=" + studentid,

                 type: 'GET',

                 success: function(response) {
                     console.log(response);
                     var items = response.items;
                     var tableBody = $('#responseBody');
                     var count = 0;
                     $.each(items, function(index, item) {
                         count++;

                         tableBody.append(
                             '<tr style="color:teal; align:center;" ><th colspan="2" align:"center"><h3>' +
                             item['std_program'] + '</h3></th></tr>');
                         $.each(item, function(key, value) {
                             var row = $('<tr>');

                             var key2 = key.replace(/_/g,
                                 ' ').replace(/\b\w/g,
                                 function(char) {
                                     return char.toUpperCase();
                                 });

                             row.append($('<th>').text(key2));
                             row.append($('<td>').text(value));
                             tableBody.append(row);
                         });
                     });
                 },
                 error: function(xhr, status, error) {
                     console.error("Error fetching data:", error);
                     $('#studentApiModal .modal-body').html(
                         '<p>No information found against the provided Student ID</p>');
                 }
             });
         });



         $('.open-apimodal').on('click', function() {

             $('#apiModal .modal-body').append("<h5 align='center'>Please wait...</h5>")
             $('#apiModal').modal('show');

             var doi = $(this).data('mydata');
             var url = "https://api.crossref.org/works/" + doi;

             // Reset the modal content before making a new API request
             //    $('#apiModal .modal-body').empty();

             fetch(url)
                 .then(response => response.json())
                 .then(res => {
                     // Handle the API response here
                     console.log(res, res.message);

                     var title = res.message.title[0];
                     var doi = res.message.DOI;
                     var journal = res.message['container-title'][0];
                     var pubDate = res.message.created['date-time'];
                     var publisher = res.message.publisher;
                     var type = res.message.type;
                     var authors = res.message.author;

                     // Append content to the modal body
                     $('#apiModal .modal-body').empty();


                     $('#apiModal .modal-body').append(
                         `<table class="table table-striped" id="infoTable"> <tbody> `);
                     $('#apiModal .modal-body').append('<tr><th>Title</th><td>' + title + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>DOI</th><td>' + doi + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>Journal</th><td>' + journal + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>Publish Date</th><td>' + pubDate + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>Publisher</th><td>' + publisher + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>Type</th><td>' + type + '</td></tr>');
                     $('#apiModal .modal-body').append('<tr><th>Authors</th><td><ul>');
                     $.each(authors, function(i, author) {
                         $('#apiModal .modal-body').append('<li>' + author.given + ' ' + author.family +
                             '</li>');
                     });
                     $('#apiModal .modal-body').append('</ul></td></tr>');
                     $('#apiModal .modal-body').append(`</tbody> </table> `);

                     // Show the modal


                 })
                 .catch(error => {
                     console.error("Error fetching data:", error);
                     $('#apiModal .modal-body').html('<p>No information found against the provided DOI</p>');
                     $('#apiModal').modal('show');
                 });
         });


         // Clear modal content when modal is hidden
         $('#apiModal').on('hidden.bs.modal', function() {
             $('#apiModal .modal-body').html('');
         });


         function openPage(pageName, elmnt, color) {
             console.log(elmnt);
             var i, tabcontent, tablinks;
             tabcontent = document.getElementsByClassName("tabcontent");
             for (i = 0; i < tabcontent.length; i++) {
                 tabcontent[i].style.display = "none";
             }
             tablinks = document.getElementsByClassName("tablink");
             for (i = 0; i < tablinks.length; i++) {
                 tablinks[i].style.backgroundColor = "";
             }
             document.getElementById(pageName).style.display = "block";
             elmnt.style.backgroundColor = color;
         }

         // Get the element with id="defaultOpen" and click on it
         document.getElementById("Commitments1").click();

         function hide() {
             document.getElementById('YR').style.display = 'none';
             document.getElementById("gradeD").value = '-1';
             calculategradeD();
         }

         function showVerify(id) {
             tablinks = document.getElementsByClassName("largeModalLabel");
             tablinks.text(id);
             $('#modalVerify').modal('show');
         }


         function show() {

             document.getElementById('YR').style.display = 'block';
         }

         function calculategradeA() {

             const checkboxes1 = document.querySelectorAll('input[name="option1"]');
             const totalValueField1 = document.getElementById('gradeA');
             let sum1 = 0;

             checkboxes1.forEach((checkbox) => {
                 if (checkbox.checked) {
                     sum1 += parseInt(checkbox.value);
                 }
             });
             var divA = <?php echo json_encode($divA); ?>;
             totalValueField1.value = Math.round((sum1 / divA) * 5, 2);
         }
         // Attach event listeners for List 1
         const checkboxes1 = document.querySelectorAll('input[name="option1"]');
         checkboxes1.forEach((checkbox) => {
             checkbox.addEventListener('change', calculategradeA);
         });
         calculategradeA();

         function calculategradeB() {
             const checkboxes2 = document.querySelectorAll('input[name="option2"]');
             const totalValueField2 = document.getElementById('gradeB');
             let sum1 = 0;

             checkboxes2.forEach((checkbox) => {
                 if (checkbox.checked) {
                     sum1 += parseInt(checkbox.value);
                 }
             });
             var divB = <?php echo json_encode($divB); ?>;
             totalValueField2.value = Math.round((sum1 / divB) * 5, 2);
         }
         const checkboxes2 = document.querySelectorAll('input[name="option2"]');
         checkboxes2.forEach((checkbox) => {
             checkbox.addEventListener('change', calculategradeB);
         });

         calculategradeB();

         function calculategradeD() {
             const checkboxes3 = document.querySelectorAll('input[name="option3"]');
             const totalValueField3 = document.getElementById('gradeD');
             let sum1 = 0;

             checkboxes3.forEach((checkbox) => {
                 if (checkbox.checked) {
                     sum1 += parseInt(checkbox.value);
                 }
             });
             var divC = <?php echo json_encode($divC); ?>;
             totalValueField3.value = Math.round((sum1 / divC) * 5, 2);
         }
         const checkboxes3 = document.querySelectorAll('input[name="option3"]');
         checkboxes3.forEach((checkbox) => {
             checkbox.addEventListener('change', calculategradeD);
         });

         calculategradeD();
     </script>


     <script>
         document.addEventListener('DOMContentLoaded', function() {
             // Check if there is a tab parameter in the URL
             const urlParams = new URLSearchParams(window.location.search);

             const tab = urlParams.get('tab');
             console.log(tab);
             if (tab) {
                 const tabLink = document.querySelector(`#${tab}-tab`);
                 if (tabLink) {
                     // Activate the tab
                     new bootstrap.Tab(tabLink).show();
                 }
             }
         });
     </script>



     <script>
         $(".btn-group-toggle").twbsToggleButtons();
     </script>


 </body>
