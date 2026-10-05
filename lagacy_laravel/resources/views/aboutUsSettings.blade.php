 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>System Users</title>
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
                             <th>#</th>
                             <th>Name</th>
                             <th>Role</th>
                             <th>Introduction</th>
                             <th>Email</th>
                             <th>Phone</th>
                             <th>Address</th>
                             <th>Action</th>

                         </tr>
                     </thead>
                 </table>
                 <div class="heading">
                     About Us Settings
                 </div>
             </div>
         </div>
     </body>

     <script type="text/javascript">
         $(document).ready(function() {
             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('home.ajaxListAboutus') }}",
                 columns: [{
                         data: 'id',
                         name: 'id'
                     },
                     {
                         data: 'name',
                         name: 'name'
                     },
                     {
                         data: 'role',
                         name: 'role'
                     },

                     {
                         data: 'introduction',
                         name: 'introduction'
                     },


                     {
                         data: 'email',
                         name: 'email'
                     },

                     {
                         data: 'phone',
                         name: 'phone'
                     },

                     {
                         data: 'address',
                         name: 'address'
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
