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

 <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

 <style>
     .label-teal {
         background-color: #c23e3e;
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

     .label-teal2 {
         background-color: #0c420c;
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

     .bg3 {
         background-color: #f0f0f0;
         background-image: url("{{ asset('images/infographs-pattern.png') }}");

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




 <div class="row" style=" margin:5; ">
     <div class="col-md-12" style="padding-top: 10;">
         <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
             class="bg3">
             <div style=" margin: 5px;">
                 <div class="container">

                     <div class="section" style="margin:25">



                         <h2 style="color:teal;"> <a href="{{ route('project', ['c_id' => $project->cycle]) }} "><i
                                     class="fa fa-arrow-left" title="Back to List of projects"></i></a>
                         </h2>


                     </div>

                     <div class="section2">
                         <b> Grant Type: </b> <span class="label-teal"> Student Grant</span><br>
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
         <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
             class="bg3">
             <div style=" margin: 5px;">


                <button id="Commitments1" class="tablink" onclick="openPage('Commitments', this,'teal')"
                    style=" border-radius: 30px 0px 0px 0px; ">Project Proposal</button>

                <button id="ProgressReport1" class="tablink" onclick="openPage('ProgressReport', this, 'teal')"
                    style="">Progress Report</button>
                <button id="ProgressReport21" class="tablink" onclick="openPage('ProgressReport2', this, 'teal')"
                    style="">Progress Report 2</button>
                 <button style=" border-radius: 0px 30px 0px 0px; " id="FinalReport1" class="tablink"
                     onclick="openPage('FinalReport', this, 'teal')">Final
                     Report</button>


                  <div style="clear: both;"></div>

                 <div id="Commitments" class="tabcontent">

                     {{-- @if (true) --}}
                     <div class="row">
                         <div class="col-md-12" style="margin-top: 10;">
                             <div style="border: 2px solid teal; ">


                                 @php
                                     use Illuminate\Support\Facades\Storage;

                                     $filePath =
                                         'uploads/lpi_project_proposals/' .
                                         $project->cycle_title .
                                         '/' .
                                       str_replace('/', '', $project->old_project_id)   .
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
                                         id="pdfViewer2" style="height:1200; width:100%; padding:10;"></iframe>
                                 @endif


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
                         $filePathPr =
                             'uploads/progress_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
                         $fileExistsPr = Storage::exists($filePathPr);
                     @endphp

                     <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-8" style="margin-top: 10px;">
                                 <div style="border: 2px solid teal;">
                                     @if ($fileExistsPr)
                                         <iframe
                                             src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}.pdf"
                                             id="pdfViewerPr" style="height:1200; width:100%; padding:10;"></iframe>
                                     @else
                                         <p style="padding: 10px;">Progress Report not available</p>
                                     @endif
                                 </div>
                             </div>
                             <div class="col-md-4" style="margin-top: 10;">
                                 <div style="border: 2px solid teal;">
                                     <div style="margin:20">

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
                     </div>
                 </div>

                 <div id="ProgressReport2" class="tabcontent">

                     @php
                         $filePathPr2 = 'uploads/progress_reports/' . $project->cycle_title . '/' . $project->old_project_id . '_2.pdf';
                         $fileExistsPr2 = Storage::exists($filePathPr2);
                     @endphp

                     <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-8" style="margin-top: 10px;">
                                 <div style="border: 2px solid teal;">
                                     @if ($fileExistsPr2)
                                         <iframe
                                             src="{{ URL::to('/') }}/serveFile2?file=progress_reports/{{ $project->cycle_title }}/{{ $project->old_project_id }}_2.pdf"
                                             id="pdfViewerPr2" style="height:1200; width:100%; padding:10;"></iframe>
                                     @else
                                         <p style="padding: 10px;">Progress Report 2 not available</p>
                                     @endif
                                 </div>
                             </div>
                             <div class="col-md-4" style="margin-top: 10;">
                                 <div style="border: 2px solid teal;">
                                     <div style="margin:20">

                                         @if ($progressComments2)
                                             @include('components.progressGrades', [
                                                 'progressComments' => $progressComments2,
                                             ])
                                         @elseif($progressDraft)
                                             @include('components.progressDraft', [
                                                 'progressDraft' => $progressDraft,
                                             ])
                                         @else
                                             @include('components.progressGradingForm2')
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
                             'uploads/final_reports/' . $project->cycle_title . '/' . $project->old_project_id . '.pdf';
                         $fileExists = Storage::exists($filePath);
                     @endphp


                     <div class="container-fluid">
                         <div class="row">




                             <div class="col-md-8" style="margin-top: 10px;">
                                 <div style="border: 2px solid teal;">

                                     @if (!empty($fileNames))
                                         <!-- Dropdown to Select a File -->
                                         <div style="display: flex; align-items: center; gap: 10px; border:0px">
                                             <span><strong>Ethical Approvals:</strong></span>
                                             <select id="fileSelector" class="form-control" style="width: 300px;">
                                                 <option value="">Select a file</option>
                                                 @foreach ($fileNames as $file)
                                                     <option value="{{ $file }}">{{ $file }}
                                                     </option>
                                                 @endforeach
                                             </select>
                                         </div>

                                         <!-- PDF Viewer -->
                                         <iframe id="pdfViewer" src=""
                                             style="height: 1200px; width: 100%; padding: 10px;"></iframe>
                                     @else
                                         <p style="padding: 10px;">Final Report not available</p>
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
                                         @if ($next)
                                             <button
                                                 style="background-color: teal; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 4px; float: right; cursor: pointer;"
                                                 onclick="window.location.href='{{ url('grading/' . $next->id) }}'">Next
                                                 Project</button>
                                         @endif

                                         <br> <br>
                                         <h3 class="text-center"><b>Final Report</b>
                                             <span class="text-to-open-modal" data-toggle="modal"
                                                 data-target="#helpfinal" align="center"><i
                                                     class="fa fa-question-circle" style="color:teal"></i></span>
                                         </h3>

                                         <br>



                                         <div>
                                             <form method="POST" action="{{ route('finalGrades') }}">
                                                 @csrf
                                                 <input type="text" name="p_id" value="{{ $p_id }}"
                                                     hidden>
                                                 <input type="text" name="gradeA" value="0" hidden>
                                                 <input type="text" name="gradeB" value="0" hidden>
                                                 <input type="text" name="gradeC" value="0" hidden>


                                                 {{-- Publications --}}
                                                 <label class="col-form-label"><b>1.
                                                         Publications</b></label><br>

                                                 <p style="color: teal; font-style: italic;"> Publication
                                                     information submitted by the LPI is as follows:</p>

                                                 <p>{{ $project->publications ?? 'N/A' }}</p>


                                                 <label class="col-form-label"><b>2.
                                                         Budget / Spendings</b></label><br>
                                                 <p style="color: teal; font-style: italic;"> Budget utilisation
                                                     justification by the LPI</p>
                                                 <input type="hidden" id="gradeA" value="0"></input>
                                                 <table class="table" style="font-size: 11px; width: 100%;">

                                                     <tr>
                                                         <th>Total Budget (QAR)
                                                         </th>
                                                         <td>{{ $project->requested_budget_qar }}<br></td>
                                                     </tr>
                                                     <tr>
                                                         <th>Total Spendings (QAR)
                                                         </th>
                                                         <td>{{ $project->spending }}<br></td>
                                                     </tr>
                                                     <tr>
                                                         <th>Spending Details
                                                         </th>
                                                         <td>{{ $project->spending_detail }}<br></td>
                                                     </tr>

                                                     <tr>
                                                         <th>Budget Utilization</th>
                                                         <td>
                                                             @php
                                                                 $budget = floatval($project->requested_budget_qar);
                                                                 $spending = floatval($project->spending);
                                                                 $percentage =
                                                                     $budget > 0 ? ($spending / $budget) * 100 : 0;
                                                                 $formattedPercentage = number_format($percentage, 2);
                                                             @endphp

                                                             @if ($percentage == 100)
                                                                 <span
                                                                     style="color: green;">{{ $formattedPercentage }}%</span>
                                                             @elseif($percentage < 100)
                                                                 <span style="color: red;">{{ $formattedPercentage }}%
                                                                     - Under Utilized</span>
                                                             @else
                                                                 <span style="color: red;">{{ $formattedPercentage }}%
                                                                     - Exceeding Budget Limit</span>
                                                             @endif
                                                         </td>
                                                     </tr>

                                                 </table>




                                                 <label class="col-form-label"><b>3. Student Engagement
                                                     </b></label><br>
                                                 <p style="color: teal; font-style: italic;">a. List of Students
                                                 </p>

                                                 <div id="YR">
                                                     <table class="table" style="font-size: 11px; width: 100%;">
                                                         <thead>
                                                             <tr>
                                                                 <th>ID</th>
                                                                 <th>Name</th>
                                                                 <th>Status</th>
                                                                 <th>Program</th>
                                                                 <th>Major</th>
                                                                 <th>Nationality</th>
                                                             </tr>
                                                         </thead>
                                                         <tbody>
                                                             @foreach ($students as $student)
                                                                 <tr>
                                                                     <td>{{ $student->student_id }}</td>
                                                                     <td>{{ $student->first_name . ' ' . $student->last_name }}
                                                                     </td>
                                                                     <td>{{ $typeMappings[$student->student_status] ?? $student->student_status }}
                                                                     </td>
                                                                     <td>{{ $student->std_program }}</td>

                                                                     <td>{{ $student->major }}</td>
                                                                     <td>{{ $student->nationality = 'qatri' ? 'Qatari' : 'Non-Qatari' }}
                                                                     </td>
                                                                     <td><br></td>
                                                                 </tr>
                                                             @endforeach
                                                         </tbody>
                                                     </table>
                                                     <br>
                                                 </div>

                                                 <p style="color: teal; font-style: italic;">b. Student engagement
                                                     description by LPI</p>

                                                 <div id="YR">
                                                     <p>{{ $project->student_engagement }}</p>
                                                     <br>
                                                 </div>

                                                 <label class="col-form-label"><b>4. Accept / Reject Project
                                                     </b></label><br>
                                                 <br>

                                                 <div class="vc-toggle-container" style="align:center;">
                                                     <label class="vc-switch">
                                                         <input type="checkbox" class="vc-switch-input"
                                                             id="isAccepted" name="isAccepted"
                                                             {{ $finalGrades != null ? ($finalGrades->isaccepted == 1 ? 'checked' : '') : '' }} />
                                                         <span data-on="Accepted" data-off="Rejected"
                                                             class="vc-switch-label"></span>
                                                         <span class="vc-handle"></span>
                                                     </label>
                                                 </div>
                                                 <br>

                                                 {{-- <button type="submit" name="draft" value="draft"
                                                         class="btn btn-secondary">
                                                         Save As Draft
                                                     </button> --}}
                                                 <button type="submit" class="btn btn-primary " name="publish"
                                                     value="publish">
                                                     {{ __('Submit') }}
                                                 </button>
                                             </form>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
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
        // Adjust remaining tab buttons to fill the row
        $('.tablink').css('width', '33.33%');
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("Commitments1").click();
    });

        //  var aa = document.getElementById("ProgressReport1");
        //  openPage('FinalReport', aa, 'teal');


        //  @if (session('tab') == 'ProgressReport')
        //      tabcontent = document.getElementsByClassName("tabcontent");

        //      openPage('ProgressReport', aa, 'teal');
        //  @endif


        //  var aa = document.getElementById("FinalReport1");
        //  @if (session('tab') == 'FinalReport')
        //      tabcontent = document.getElementsByClassName("tabcontent");

        //      openPage('FinalReport', aa, 'teal');
        //  @endif


         $('#achievementsRating').val(
             '{{ $progressComments != null ? $progressComments->achievementsRating : '' }}');
         $('#publicationsRating').val(
             '{{ $progressComments != null ? $progressComments->publicationsRating : '' }}');
         $('#studentsRating').val('{{ $progressComments != null ? $progressComments->studentsRating : '' }}');




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





         $('#editBtn').click(function() {
             $('.editable').each(function() {
                 var value = $(this).text().trim();
                 $(this).data('originalValue', value); // Store original value
                 $(this).html('<input type="text" class="editField" value="' + value + '">');
                 console.log(value);
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

         // Define the fields to show (in order) with labels
         const fieldLabels = {
             'student_id': 'Student ID',
             'first_name': 'First Name',
             'last_name': 'Last Name',
             'email': 'Email',
             'college': 'College',
             'major': 'Major',
             'minor': 'Minor',
             'nationality': 'Nationality',
             'student_status': 'Status',
             'std_program': 'Program',
             'std_level': 'Level',
             'admission_term': 'Admission Term',
             'reg_in_course': 'Registered in Course'
         };

         var studentId = $(this).data('mydata');
         const students = @json($students);
         const result = students.find(std => std.student_id === studentId);
         const responseDiv = document.getElementById('responseBody');
         responseDiv.innerHTML = '';

         if (result) {
             let table =
                 '<table border="1" cellpadding="8" cellspacing="0" style="width:100%;max-width:600px;">';

             for (const key in fieldLabels) {
                 let value = result[key] ?? '';

                 if (key === 'nationality') {
                     if (value === 'qatri') result[key] = 'Qatari';
                     else if (value === 'nonqatri') result[key] = 'Non Qatari';
                 }

                 table += `
                    <tr>
                        <td style="font-weight:bold;">${fieldLabels[key]}</td>
                        <td>${result[key] ?? ''}</td>
                    </tr>
                `;
             }

             table += '</table>';
             responseDiv.innerHTML = table;
         } else {
             responseDiv.innerHTML = '<p style="color:red;">Student not found.</p>';
         }


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

         //  const checkboxes1 = document.querySelectorAll('input[name="option1"]');
         //  const totalValueField1 = document.getElementById('gradeA');
         //  let sum1 = 0;

         //  checkboxes1.forEach((checkbox) => {
         //      if (checkbox.checked) {
         //          sum1 += parseInt(checkbox.value);
         //      }
         //  });
         //  var divA = <?php echo json_encode($divA); ?>;
         //  totalValueField1.value = Math.round((sum1 / divA) * 5, 2);
     }
     // Attach event listeners for List 1
     const checkboxes1 = document.querySelectorAll('input[name="option1"]');
     checkboxes1.forEach((checkbox) => {
         checkbox.addEventListener('change', calculategradeA);
     });
     calculategradeA();

     function calculategradeB() {
         //  const checkboxes2 = document.querySelectorAll('input[name="option2"]');
         //  const totalValueField2 = document.getElementById('gradeB');
         //  let sum1 = 0;

         //  checkboxes2.forEach((checkbox) => {
         //      if (checkbox.checked) {
         //          sum1 += parseInt(checkbox.value);
         //      }
         //  });
         //  var divB = <?php echo json_encode($divB); ?>;
         //  totalValueField2.value = Math.round((sum1 / divB) * 5, 2);
     }
     const checkboxes2 = document.querySelectorAll('input[name="option2"]');
     checkboxes2.forEach((checkbox) => {
         checkbox.addEventListener('change', calculategradeB);
     });

     calculategradeB();

     function calculategradeD() {
         //  const checkboxes3 = document.querySelectorAll('input[name="option3"]');
         //  const totalValueField3 = document.getElementById('gradeD');
         //  let sum1 = 0;

         //  checkboxes3.forEach((checkbox) => {
         //      if (checkbox.checked) {
         //          sum1 += parseInt(checkbox.value);
         //      }
         //  });
         //  var divC = <?php echo json_encode($divC); ?>;
         //  totalValueField3.value = Math.round((sum1 / divC) * 5, 2);
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
                 new bootstrap.Tab(tabLink).show();
             }
         }
     });
 </script>



 <script>
     $(".btn-group-toggle").twbsToggleButtons();
 </script>


 <script>
     document.addEventListener('DOMContentLoaded', function() {
         const fileSelector = document.getElementById('fileSelector');
         const pdfViewer = document.getElementById('pdfViewer');

         if (fileSelector && fileSelector.options.length > 0) {
             // Select first non-empty option (if first is placeholder)
             let firstValidIndex = Array.from(fileSelector.options).findIndex(option => option.value);
             if (firstValidIndex !== -1) {
                 fileSelector.selectedIndex = firstValidIndex;

                 // Manually update viewer (instead of dispatching change)
                 const selectedFile = fileSelector.value;
                 if (selectedFile) {
                     const baseUrl =
                         "{{ url('/') }}/serveFile2?file=ethical_approvals/{{ $project->cycle_title }}/{{ $project->old_project_id }}/";

                     pdfViewer.src = baseUrl + selectedFile;
                 }
             }
         }

         // Normal change handler for user interaction
         fileSelector.addEventListener('change', function() {

             const fileSelector = document.getElementById('fileSelector');
             const selectedFile = fileSelector.value;

             if (selectedFile) {
                 const baseUrl =
                     "{{ url('/') }}/serveFile2?file=ethical_approvals/{{ $project->cycle_title }}/{{ $project->old_project_id }}/";
                 console.log(baseUrl + selectedFile);
                 pdfViewer.src = baseUrl + selectedFile;
             }
         });
     });
 </script>
 </body>
