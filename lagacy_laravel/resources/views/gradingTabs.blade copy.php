 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
 <!-- Load jQuery -->
 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


 <!-- Then load Bootstrap JavaScript files -->
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>






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
         width: 25%;
     }

     textarea {
         width: 100%;
         /* Set the textarea width to 100% of its parent container */
         box-sizing: border-box;
         /* Include padding and border in the width calculation */
     }

     .tablink:hover {
         background-color: #004c4c;
     }


     /* Add your styles here */
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
         /* Set fixed width */
         text-align: center;

     }
 </style>

 </style>

 <head>
     <meta name="viewport" content="width=device-width, initial-scale=1">
 </head>

 <body>

     <h2 style="color:teal;">Project Title: {{ $project->title }}</h2>

     <button class="tablink" onclick="openPage('Commitments', this,'teal')" id="defaultOpen">Project Proposal</button>
     <button class="tablink" onclick="openPage('ProgressReport', this, 'teal')">Progress Report</button>
     <button class="tablink" onclick="openPage('FinalReport', this, 'teal')">Final Report</button>
     <button class="tablink" onclick="openPage('Outcomes', this, 'teal')">QU Readiness Map</button>

     <br>
     <br>

     <div id="Commitments" class="tabcontent">

         @if (true)
         <div class="row">
             <div class="col-md-8" style="margin-top: 10;">
                 <div style="border: 2px solid teal; ">

                     <iframe
                         src="{{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                         id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                 </div>
             </div>
             <div class="col-md-4" style="margin-top: 10;">
                 <div style="border: 2px solid teal; padding-bottom:50">
                     <div style="margin: 20;">
                         <h3 class="text-center"><b>Commitments</b> </h3>
                         <i>
                             <p class="text-to-open-modal" data-toggle="modal" data-target="#helpreadiness"
                                 align="center"> Help related commitments </p>
                         </i>

                         <div align="center">
                             <form id="myForm">
                                 @csrf
                                 <table class="table table-striped table-sm">
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

                                         <td style="text-align:center; size:10px;display: none;" class="editable"
                                             data-column="project_id">{{ $commitments->project_id }}</td>

                                     <tr>

                                     <tr>
                                         <td>-</td>
                                         <td>Published articles in journals listed in Thomson Reuters Web of
                                             Science-SCI (Quartile in Category Q1)
                                         </td>
                                         <!-- <td style="text-align:center">{{ $commitments->q1article }}</td> -->
                                         <td style="text-align:center; size:10px;" class="editable"
                                             data-column="q1article">{{ $commitments->q1article }}</td>
                                         <td style="text-align:center">8</td>
                                     <tr>
                                         <td>-</td>
                                         <td>Published articles in journals listed in Thomson Reuters Web of
                                             Science-SCI (Quartile in Category Q2)
                                         </td>
                                         <td style="text-align:center" class="editable" data-column="q2article">
                                             {{ $commitments->q2article }}
                                         </td>
                                         <td style="text-align:center">6</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Published articles in journals listed in Thomson Reuters Web of
                                             Science-SCI (Quartile in Category Q3)</td>
                                         <td style="text-align:center" class="editable" data-column="q3article">
                                             {{ $commitments->q3article }}
                                         </td>
                                         <td style="text-align:center">4</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Published articles in journals listed in Thomson Reuters Web of
                                             Science-SCI (Quartile in Category Q4)</td>
                                         <td style="text-align:center" class="editable" data-column="q4article">
                                             {{ $commitments->q4article }}
                                         </td>
                                         <td style="text-align:center">3</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Public articles in indexed international conferences </td>
                                         <td style="text-align:center" class="editable" data-column="confArticle">
                                             {{ $commitments->confArticle }}
                                         </td>
                                         <td style="text-align:center">2</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Published Books</td>
                                         <td style="text-align:center" class="editable" data-column="books">
                                             {{ $commitments->books }}
                                         </td>
                                         <td style="text-align:center">8</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>
                                             Edited Books (collection)
                                         </td>
                                         <td style="text-align:center" class="editable" data-column="editBooks">
                                             {{ $commitments->editBooks }}
                                         </td>
                                         <td style="text-align:center">6</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>
                                             Book Chapters
                                         </td>
                                         <td style="text-align:center" class="editable" data-column="chapters">
                                             {{ $commitments->chapters }}
                                         </td>
                                         <td style="text-align:center">4</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Submitted IP Disclosure form submitted</td>
                                         <td style="text-align:center" class="editable" data-column="ip">
                                             {{ $commitments->ip }}
                                         </td>
                                         <td style="text-align:center">4</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Filed Provisional Patent</td>
                                         <td style="text-align:center" class="editable" data-column="filedPatent">
                                             {{ $commitments->filedPatent }}
                                         </td>
                                         <td style="text-align:center">7</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Open Source Software</td>
                                         <td style="text-align:center" class="editable" data-column="openSourceSW">
                                             {{ $commitments->openSourceSW }}
                                         </td>
                                         <td style="text-align:center">8</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Created a Start-Up
                                         <td style="text-align:center" class="editable" data-column="startUp">
                                             {{ $commitments->startUp }}
                                         </td>
                                         <td style="text-align:center">10</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>No. of UnderGrad Students Involved in Project
                                         <td style="text-align:center" class="editable" data-column="UG">
                                             {{ $commitments->UG }}
                                         </td>
                                         <td style="text-align:center">1</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>No. of Masters Students Involved in Project
                                         <td style="text-align:center" class="editable" data-column="master">
                                             {{ $commitments->master }}
                                         </td>
                                         <td style="text-align:center">2</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>No. of PhD Student Involved in Project
                                         <td style="text-align:center" class="editable" data-column="Phd">
                                             {{ $commitments->Phd }}
                                         </td>
                                         <td style="text-align:center">3</td>
                                     </tr>
                                     <tr>
                                         <td>-</td>
                                         <td>Cross College Participation
                                         <td style="text-align:center" class="editable"
                                             data-column="crossCollege">{{ $commitments->crossCollege }}</td>
                                         <td style="text-align:center">2</td>
                                     </tr>

                                 </table>
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
         @else
         <b class='alert'>Commitments for this project are not avaialable</b> <br>
         <br>
         @endif



     </div>


     <div id="ProgressReport" class="tabcontent">

         <div class="container-fluid">

             @if (true)
             <div class="row">
                 <div class="col-md-8" style="margin-top: 10;">
                     <div style="border: 2px solid teal; ">
                         @if ($progress_report)

                         <iframe src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                             id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>

                         {{-- <iframe src="{{ URL::to('/') }}/serveFile?file={{ $progress_report->path }}"
                         id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe> --}}
                         @else
                         <p>Progress Report not uploaded</p>
                         @endif
                     </div>
                 </div>

                 <div class="col-md-4" style="margin-top: 10;">
                     <div style="border: 2px solid teal; ">
                         <div style="margin: 20;">

                             <h3 class="text-center"><b>Progress Report</b> </h3>

                             <i>
                                 <p class="text-to-open-modal" data-toggle="modal" data-target="#helpprogress"
                                     align="center"> Help regarding filling the form </p>
                             </i>

                             <div align="center">
                                 @if (session('successprogressgrade_publish'))
                                 {!! session('successprogressgrade_publish') !!}
                                 @php
                                 session()->forget('successprogressgrade_publish');
                                 @endphp
                                 @endif

                                 @if (session('successprogressgrade_draft'))
                                 {!! session('successprogressgrade_draft') !!}
                                 @php
                                 session()->forget('successprogressgrade_draft');
                                 @endphp
                                 @endif

                             </div>

                             @if ($progressComments)
                             @include('components.progressGrades', [
                             'progressComments' => $progressComments,
                             ])
                             @elseif($progressDraft)
                             @include('components.progressDraft', [
                             'progressDraft' => $progressDraft,
                             ])
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

     <div id="FinalReport" class="tabcontent">

         @if (true)
         <div class="container-fluid">
             <div class="row">
                 <div class="col-md-8" style="margin-top: 10;">
                     <div style="border: 2px solid teal; ">
                         @if ($final_report)
                         {{-- <iframe src="{{ URL::to('/') }}/serveFile?file={{ $final_report->path }}"
                         id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe> --}}
                         <iframe src="{{ URL::to('/') }}/serveFile2?file=final_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                             id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                         @else
                         <p>Final Report not uploaded</p>
                         @endif
                     </div>
                 </div>

                 <div class="col-md-4" style="margin-top: 10;">
                     <div style="border: 2px solid teal;">
                         <div style="margin:20">
                             @foreach ($errors->all() as $error)
                             <div class="alert alert-danger">{{ $error }}</div>
                             @endforeach


                             @if (isset($finalGrades->gradeB))
                             @include('components.finalGrades', ['finalGrades' => $finalGrades])
                             @elseif($finalDraft)
                             <div>
                                 <h3 class="text-center"><b>Final Report</b> </h3>
                                 <i>
                                     <p class="text-to-open-modal" data-toggle="modal"
                                         data-target="#helpfinal" align="center"> Help regarding filling the
                                         form </p>
                                 </i>


                                 <form method="POST" action="{{ route('finalGrades') }}">
                                     @csrf
                                     <input type="text" name="p_id" value="{{ $p_id }}"
                                         hidden>

                                     <label class="col-form-label"><b>1. Achievements</b></label><br>
                                     <p>Degree of realization of the proposed outcomes in the project. Does the
                                         project produce a Prototype, Patent, Open Source Software, etc.? If a
                                         Prototype is achieved, state its TRL level (or SRL for society
                                         readiness)</p>

                                     <table class="table">
                                         <tr>

                                             <th>Select</th>
                                             <th>Detail</th>
                                             <th>Score</th>
                                         </tr>

                                         @foreach ($contributions as $contribution)
                                         <tr>
                                             <td>
                                                 <input type="checkbox" name="option1"
                                                     value="{{ $contribution['score'] }}" checked>
                                             </td>
                                             <td>{{ $contribution['detail'] }}</td>
                                             <td>{{ $contribution['score'] }}<br></td>
                                         </tr>
                                         @endforeach
                                     </table>
                                     <br>
                                     <label>a. Score</label>
                                     <input type="text" id="gradeA" name='gradeA' size="2"
                                         readonly>
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentA" name="commentA" rows="4" cols="40"></textarea><br><br>

                                     <label class="col-form-label"><b>2. Publications</b></label><br>
                                     <p>Number of Q1/Q2 publications in ranked journals. Number of Q1
                                         publications in highly ranked journals. Number and quality of Books,
                                         Chapters, etc</p>

                                     <table id="myTable" class="table">
                                         <tr>
                                             <th></th>
                                             <th>Identifier</th>
                                             <th>Type</th>
                                             <th>Publish date</th>
                                             <th>Link</th>
                                         </tr>
                                         @foreach ($outcomes as $outcome)
                                         <tr>
                                             <td>
                                                 <input type="checkbox" name="option2"
                                                     value="{{ $outcome['score'] }}" checked>
                                             </td>

                                             <td class="open-apimodal" data-toggle="modal"
                                                 data-mydata="{{ $outcome['identifier'] }}">
                                                 {{ $outcome['identifier'] }}
                                             </td>
                                             <td>{{ $outcome['type'] }}</td>
                                             <td> {{ \Carbon\Carbon::parse($outcome['publication_date'])->format('d-m-Y') }}
                                             </td>
                                             <td><a href={{ $outcome['url'] }}> link </a></td>

                                         </tr>
                                         @endforeach
                                     </table>
                                     <br>
                                     <label>a. Score</label>
                                     <input id="gradeB" type="text" name='gradeB' size="2"
                                         readonly>
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentB" name="commentB" rows="4" cols="40"></textarea><br><br>

                                     <b>3. Student and young researchers involements</b><br><br>
                                     <input type="radio" id="Yes" value="Yes" name="yesno2"
                                         onclick="show(); " checked>
                                     <label for="Yes">Yes</label><br>
                                     <input type="radio" id="No" value="No" name="yesno2"
                                         onclick="hide();">
                                     <label for="No">No</label><br>

                                     <div id="YR">
                                         <p>Level of engagement of graduate students in the activities of the
                                             project. Training of undergraduate students. Such as involvement of
                                             RAs and GAs in the project</p>
                                         <!-- <label class="col-form-label"><b>3. Young Researcher Supervision</b></label><br> -->
                                         <table class="table">
                                             <th></th>
                                             <th>Level</th>
                                             <th>Days</th>
                                             @foreach ($students as $student)
                                             <tr>
                                                 <td>
                                                     <input type="checkbox" name="option3"
                                                         value="{{ $student['score'] }}" checked>
                                                 </td>
                                                 <td>{{ $typeMappings[$student['type']] ?? $student['type'] }}
                                                 </td>
                                                 <td>{{ $student['days'] }}</td>
                                                 <td><br></td>
                                             </tr>
                                             @endforeach
                                         </table>
                                         <br>
                                         <label>a. Score</label>
                                         <input id="gradeD" type="text" name='gradeD' size="2"
                                             readonly>
                                         <br>
                                         <label>b. Comment</label><br>
                                         <textarea id="commentD" name="commentD" rows="4" cols="40"></textarea><br><br>
                                     </div>

                                     <label class="col-form-label"><b>4. Project Impact</b></label><br>
                                     <p>Has the project provided concise KPIs for the proposed outcomes? The
                                         value of the reported outcomes (e.g., KPIs) in comparison to what was
                                         suggested in the proposal on industry/society/government, etc. The
                                         potential to benefit society or advance desired economical (e.g.,
                                         technology transfer) and societal outcomes (e.g. capacity building of
                                         students and researchers, change in policy). The level of engagement
                                         with end-users. Extent to which end-users locally and internationally
                                         may realistically benefit from the outcomes. The relevance of the
                                         project to partners’ development with respect to industrial
                                         development, socio-economic, health and environmental aspects and the
                                         ability to address end-user needs, as well as the potential to create
                                         positive international scientific visibility for the partners (if any).
                                     </p>

                                     <label>a. Score</label>
                                     <input id="gradeC" type="text" name='gradeC' size="2">
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentC" name="commentC" rows="4" cols="40"></textarea>
                                     <br><br>

                                     <button type="submit" name="draft" value="draft"
                                         class="btn btn-secondary">
                                         Save As Draft
                                     </button>
                                     <button type="submit" class="btn btn-primary" name="publish"
                                         value="publish">
                                         {{ __('Submit') }}
                                     </button>

                                 </form>

                             </div>
                             @else
                             <div>

                                 <h3 class="text-center"><b>Final Report</b></h3>


                                 <i>
                                     <p class="text-to-open-modal" data-toggle="modal"
                                         data-target="#helpfinal" align="center"> Help regarding filling the
                                         form </p>
                                 </i>

                                 <form method="POST" action="{{ route('finalGrades') }}">
                                     @csrf
                                     <input type="text" name="p_id" value="{{ $p_id }}"
                                         hidden>

                                     <label class="col-form-label"><b>1. Achievements</b></label><br>
                                     <p>Degree of realization of the proposed outcomes in the project. Does the
                                         project produce a Prototype, Patent, Open Source Software, etc.? If a
                                         Prototype is achieved, state its TRL level (or SRL for society
                                         readiness)</p>

                                     <table class="table">
                                         @foreach ($contributions as $contribution)
                                         <tr>
                                             <td>
                                                 <input type="checkbox" name="option1"
                                                     value="{{ $contribution['score'] }}" checked>
                                             </td>
                                             <td>{{ $typeMappings[$contribution['type']] ?? $contribution['type'] }}
                                             </td>
                                             <td>{{ $contribution['detail'] }}<br></td>
                                         </tr>
                                         @endforeach
                                     </table>
                                     <br>
                                     <label>a. Score</label>
                                     <input type="text" id="gradeA" name='gradeA' size="2"
                                         readonly>
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentA" name="commentA" rows="4" cols="40"></textarea><br><br>

                                     <label class="col-form-label"><b>2. Publications</b></label><br>
                                     <p>Number of Q1/Q2 publications in ranked journals. Number of Q1
                                         publications in highly ranked journals. Number and quality of Books,
                                         Chapters, etc</p>

                                     <table id="myTable" class="table">
                                         <tr>
                                             <th></th>
                                             <th>Identifier</th>
                                             <th>Type</th>
                                             <th>Publish date</th>
                                             <th>Link</th>
                                         </tr>

                                         @foreach ($outcomes as $outcome)
                                         <tr>
                                             <td>
                                                 <input type="checkbox" name="option2"
                                                     value="{{ $outcome['score'] }}" checked>
                                             </td>

                                             <td class="open-apimodal" data-toggle="modal"
                                                 data-mydata="{{ $outcome['identifier'] }}">
                                                 {{ $outcome['identifier'] }}
                                             </td>
                                             <td>{{ $outcome['type'] }}</td>
                                             <td> {{ \Carbon\Carbon::parse($outcome['publication_date'])->format('d-m-Y') }}
                                             </td>
                                             <td><a href={{ $outcome['url'] }}> link </a></td>

                                         </tr>
                                         @endforeach
                                     </table>
                                     <br>
                                     <label>a. Score</label>
                                     <input id="gradeB" type="text" name='gradeB' size="2"
                                         readonly>
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentB" name="commentB" rows="4" cols="40"></textarea><br><br>

                                     <b>3. Student and young researchers involements</b><br><br>
                                     <input type="radio" id="Yes" value="Yes" name="yesno"
                                         onclick="show();" checked>
                                     <label for="Yes">Yes</label><br>
                                     <input type="radio" id="No" value="No" name="yesno"
                                         onclick="hide();">
                                     <label for="No">No</label><br>

                                     <div id="YR">
                                         <p>Level of engagement of graduate students in the activities of the
                                             project. Training of undergraduate students. Such as involvement of
                                             RAs and GAs in the project</p>
                                         <!-- <label class="col-form-label"><b>3. Young Researcher Supervision</b></label><br> -->
                                         <table class="table">
                                             <th></th>
                                             <th>Level</th>
                                             <th>Days</th>
                                             @foreach ($students as $student)
                                             <tr>
                                                 <td>
                                                     <input type="checkbox" name="option3"
                                                         value="{{ $student['score'] }}" checked>
                                                 </td>
                                                 <td>{{ $typeMappings[$student['type']] ?? $student['type'] }}
                                                 </td>
                                                 <td>{{ $student['days'] }}</td>
                                                 <td><br></td>
                                             </tr>
                                             @endforeach
                                         </table>
                                         <br>
                                         <label>a. Score</label>
                                         <input id="gradeD" type="text" name='gradeD' size="2"
                                             readonly><br>
                                         <label>b. Comment</label><br>
                                         <textarea id="commentD" name="commentD" rows="4" cols="40"></textarea><br><br>
                                     </div>

                                     <label class="col-form-label"><b>4. Project Impact</b></label><br>
                                     <p>Has the project provided concise KPIs for the proposed outcomes? The
                                         value of the reported outcomes (e.g., KPIs) in comparison to what was
                                         suggested in the proposal on industry/society/government, etc. The
                                         potential to benefit society or advance desired economical (e.g.,
                                         technology transfer) and societal outcomes (e.g. capacity building of
                                         students and researchers, change in policy). The level of engagement
                                         with end-users. Extent to which end-users locally and internationally
                                         may realistically benefit from the outcomes. The relevance of the
                                         project to partners’ development with respect to industrial
                                         development, socio-economic, health and environmental aspects and the
                                         ability to address end-user needs, as well as the potential to create
                                         positive international scientific visibility for the partners (if any).
                                     </p>

                                     <label>a. Score</label>
                                     <input id="gradeC" type="text" name='gradeC' value=0
                                         size="2">
                                     <br>
                                     <label>b. Comment</label><br>
                                     <textarea id="commentC" name="commentC" rows="4" cols="40"></textarea>
                                     <br><br>

                                     <button type="submit" name="draft" value="draft"
                                         class="btn btn-secondary">
                                         Save As Draft
                                     </button>
                                     <button type="submit" class="btn btn-primary" name="publish"
                                         value="publish">
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
         @else
         <b class='alert'>Final Report for this project is not avaialable</b> <br>
         <br>
         @endif

     </div>


     <div id="Outcomes" class="tabcontent" align="center">
         <!-- Ask doctor what should I put here -->
         @if ($verify_outcomes)
         @include('components.gradedOutcomes', ['verify_outcomes' => $verify_outcomes])
         @elseif(true)
         <div class="row">
             <div class="col-md-8" style="margin-top: 10;">
                 <div style="border: 2px solid teal; ">
                     @if ($readiness_report)
                     {{-- <iframe src="{{ URL::to('/') }}/serveFile?file={{ $readiness_report->path }}"
                     id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe> --}}
                     <iframe src="{{ URL::to('/') }}/serveFile2?file=readiness_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                         id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                     @else
                     <p>Readiness Report not uploaded</p>
                     @endif
                 </div>
             </div>


             <div class="col-md-4" style="margin-top: 10;">
                 <div style="border: 2px solid teal; ">
                     <div style="margin: 20;">

                         <h3 class="text-center"><b>QU Readiness Mapping</b> </h3>

                         <i>
                             <p class="text-to-open-modal" data-toggle="modal" data-target="#helpreadiness"
                                 align="center"> Help Regarding QU Readiness Mapping </p>
                         </i>

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

                         Contents related to QU Readiness Mapping goes here.
                     </div>

                 </div>
             </div>

         </div>
         @endif
     </div>




     <!-- Borderless Modal -->
     <div class="modal fade borderless-modal" id="apiModal" tabindex="-1" role="dialog"
         aria-labelledby="borderlessModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
             <div class="modal-content">
                 <!-- Modal Header -->
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">DOI Verification</h5>
                     <span aria-hidden="true">&times;</span>
                     </button>
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

                     <span aria-hidden="true">&times;</span>
                     </button>
                 </div>

                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     <h5>Help Regarding QU Readiness Mapping</h5>
                     <p>Help Regarding QU Readiness Mapping</p>
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

                     <span aria-hidden="true">&times;</span>
                     </button>
                 </div>

                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     <h5>Help Regarding Progress Report</h5>
                     <p>Help Regarding Progress Report</p>
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
                     <h5 class="modal-title text-white" id="modalTitle">Help - Commitment</h5>

                     <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     <h5>Help Regarding Commitments</h5>
                     <p>Help Regarding Commitments</p>
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

                     <span aria-hidden="true">&times;</span>
                     </button>
                 </div>
                 <!-- Modal Body -->
                 <div class="modal-body">
                     <!-- Your content goes here -->
                     <h5>Help</h5>

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


     <script>
         $(document).ready(function() {


             $('#editBtn').click(function() {
                 $('.editable').each(function() {
                     var value = $(this).text().trim();
                     $(this).data('originalValue', value); // Store original value
                     $(this).html('<input type="text" class="editField" value="' + value + '">');
                 });
                 $('#editBtn').hide();
                 $('#saveBtn').show();
                 $('#cancelBtn').show();
             });

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
                 url: "{{ route('updateCommitments') }}", // Change this to your actual save URL
                 data: data,
                 success: function(response) {
                     // Handle success response
                     console.log(response);
                     location.reload();
                     // Reload the page or do any other necessary action
                 },
                 error: function(xhr, status, error) {
                     // Handle error
                     console.error(xhr.responseText);
                 }
             });
         });
         ////////
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
         document.getElementById("defaultOpen").click();

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
 </body>
