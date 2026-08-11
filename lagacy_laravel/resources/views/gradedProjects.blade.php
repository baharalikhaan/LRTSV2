 <head>

     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Assigned Reviewers</title>


 </head>

 @extends('layouts.app')
 @section('title', 'Home Page')
 @section('content')

     <body class="body">


         <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6" class="bg3">
             <div style=" margin: 40px;">

                 @if (session('successcycle'))
                     {!! session('successcycle') !!}
                     @php
                         session()->forget('successcycle');
                     @endphp
                 @endif


                 <table id="usertable" class="table table-striped">
                     <thead>
                         <tr>


                             <th colspan="2"></th>

                             <th colspan="2" class="progress-report">Progress Report Grading</th>

                             <th colspan="2" class="final-report">Final Report Grading</th>
                             <th></th>
                         </tr>

                         <tr>
                             <th>Project Id</th>
                             <th>Project Title</th>
                             <th style="width:90px">Reviewer-1</th>
                             <th style="width:90px">Reviewer-2</th>
                             <th style="width:90px">Reviewer-1</th>
                             <th style="width:90px">Reviewer-2 </th>
                             <th>Action</th>

                         </tr>
                     </thead>
                 </table>
                 <div class="heading">
                     Graded Projects
                 </div>
             </div>

         </div>
     </body>



     <script type="text/javascript">
         $(document).ready(function() {
             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('ajaxListGradedprojects') }}",
                 columns: [{
                         data: 'old_project_id',
                         name: 'old_project_id'
                     },
                     {
                         data: 'title',
                         name: 'title'
                     },
                     {
                         data: 'prg_Reviewer1',
                         name: 'prg_Reviewer1'
                     },
                     {
                         data: 'prg_Reviewer2',
                         name: 'prg_Reviewer2'
                     },
                     {
                         data: 'fin_Reviewer1',
                         name: 'fin_Reviewer1'
                     },
                     {
                         data: 'fin_Reviewer2',
                         name: 'fin_Reviewer2'
                     },
                     {
                         data: 'action',
                         name: 'Action',
                         orderable: false,
                         searchable: false
                     },

                 ],
                 dom: 'lBfrtip',

                 buttons: [
                     'copyHtml5',
                     'excelHtml5',
                     'csvHtml5',
                     'pdfHtml5',
                     {
                         extend: 'print',
                         customize: function(win) {
                             $(win.document.body).prepend(

                                 '<img src="{{ asset('images/research_logo.png') }}" style="position:absolute; top:0; left:500; float:right;" />'
                             );

                         }
                     }
                 ],
                 createdRow: function(row, data, dataIndex) {

                     if (data.Reviewer1 === 'Rejected') {
                         $(row).removeClass('odd');
                         $(row).addClass('red-background');
                     }
                     if (data.Reviewer2 === 'Rejected') {
                         $(row).removeClass('odd');
                         $(row).addClass('red-background');
                     }
                 }
             });
         });
     </script>
 @endsection
