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

                 <p style="float:left; color:teal; " id="label">
                     @if (session('successbulkreviewer'))
                         {!! session('successbulkreviewer') !!}
                         @php
                             session()->forget('successbulkreviewer');
                         @endphp
                     @endif
                 </p>



                 <table id="usertable" class="table table-striped">
                     <thead>
                         <tr>

                             <th>Cycle</th>
                             <th>Project Id</th>
                             <th>Project Title</th>
                             <th>LPI</th>
                             <th>Reviewer</th>
                             <th>Action</th>

                         </tr>
                     </thead>
                 </table>
                 <div class="heading">
                     Assigned Reviewers
                 </div>
             </div>

         </div>
     </body>



     <script type="text/javascript">
         $(document).ready(function() {
             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('ajaxListAssignedReviewers') }}",
                 columns: [{
                         data: 'cycle_title',
                         name: 'cycle_title'
                     },
                     {
                         data: 'old_project_id',
                         name: 'old_project_id'
                     },
                     {
                         data: 'title',
                         name: 'title'
                     },
                     {
                         data: 'email',
                         name: 'email'
                     },
                     {
                         data: 'reviewer',
                         name: 'reviewer'
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
                 ]

             });

         });
     </script>
@endsection
