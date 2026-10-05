 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>All Cycles</title>
 </head>

 @extends('layouts.app')
 @section('title', 'Home Page')
 @section('content')

     <body class="body">

         <form method="POST" action="{{ route('bulk') }}">
             @csrf

             <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                 class="bg3">
                 <div style=" margin: 40px;">

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
                                 <th>Project ID</th>
                                 <th>Pillar</th>

                                 <th>Title</th>
                                 <th>Status</th>
                                 <th>LPI</th>
                                 <th>College</th>
                                 <th>Reviewer-1</th>
                                 <th>Reviewer-2</th>
                                 <th>Action</th>
                             </tr>
                         </thead>
                     </table>
                     <br>

                     <button type="submit"
                         style="float:right; margin-right:20; ; padding-top:3; padding-bottom:3; color:white"
                         class="btn btn-teal" id="button">
                         Bulk Assign
                     </button>
         </form>
         <br>
         <div class="heading">
             Assign Reviewer
         </div>
         </div>

         </div>

     </body>

     <div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-xl" role="document">
             <div class="modal-content">
                 <div class="modal-header" style="background-color: teal; text-align: center;">
                     <h5 class="modal-title text-white" id="modalTitle">Project Details</h5>
                 </div>
                 <div class="modal-body" id="popupContent">
                     <!-- Content will be loaded here -->
                 </div>
             </div>
         </div>
     </div>


     <script type="text/javascript">
         function openPopup(url) {
             $.ajax({
                 url: url,
                 type: 'GET',
                 success: function(response) {
                     $('#popupContent').html(response);
                     $('#popupModal').modal('show');
                 }
             });
         }

         var cycle = <?php echo json_encode($cycle); ?>;

         $(document).ready(function() {


             $(document).on('change', '.abc', function() {
                 var selectedOption = $(this).val();
                 var selectName = $(this).attr('name');
                 $.ajax({
                     url: "{{ route('ajaxListgetcount', ['r_id' => ':r_id']) }}".replace(':r_id',
                         selectedOption),
                     method: 'GET',
                     success: function(response) {
                         console.log(selectName);
                         $('[id="' + selectName + '"]').text(response);
                     },
                     error: function(xhr, status, error) {
                         console.error(xhr.responseText);
                     }
                 });
             });

             $(document).on('change', '.xyz', function() {
                 var selectedOption = $(this).val();
                 var selectName = $(this).attr('name');
                 console.log(selectName);
                 $.ajax({
                     url: "{{ route('ajaxListgetcount', ['r_id' => ':r_id']) }}".replace(':r_id',
                         selectedOption),
                     method: 'GET',
                     success: function(response) {
                         console.log(selectName);
                         $('[id="' + selectName + '"]').text(response);
                     },
                     error: function(xhr, status, error) {
                         console.error(xhr.responseText);
                     }
                 });
             });



             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('ajaxListreviewer', ['cycle' => ':cycle']) }}".replace(':cycle', cycle),
                 "columnDefs": [{
                             "width": "250",
                             "targets": 2
                         },
                         {
                             "width": "150",
                             "targets": 4
                         },
                         {
                             "width": "150",
                             "targets": 5
                         }
                     ]

                     ,
                 columns: [{
                         data: 'old_project_id',
                         name: 'old_project_id'
                     },
                     {
                         data: 'pillar',
                         name: 'pillar'
                     },
                     {
                         data: 'title',
                         name: 'title'
                     },
                     {
                         data: 'status',
                         name: 'Status'
                     },
                     {
                         data: 'email',
                         name: 'email'
                     },


                     {
                         data: 'tag',
                         name: 'tag'
                     },

                     {
                         data: 'reviewer1',
                         name: 'reviewer1',
                         orderable: false,
                         searchable: false,

                     },
                     {
                         data: 'reviewer2',
                         name: 'reviewer2',
                         orderable: false,
                         searchable: false
                     },
                     {
                         data: 'action',
                         name: 'Action',
                         orderable: false,
                         searchable: false
                     },

                 ]
             });
         });
     </script>
 @endsection
