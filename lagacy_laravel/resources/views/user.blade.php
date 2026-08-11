 <head>

     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>System Users</title>


 </head>

 @extends('layouts.app')
 @section('title', 'Home Page')
 @section('content')


     <body class="body">


         <br>
         @if (session('status'))
             <div class="alert alert-success" role="alert">
                 {{ session('status') }}
             </div>
         @endif
         @if (isset($message))
             <div class="message" align="center">
                 {{ $message }}
             </div>
         @endif

         <div class="row">

             <div class="col-md-12">

                 <a href="{{ route('newUser') }}" class="btn btn-sm btn-warning"
                     style="margin:0; margin-right:30; float:right"> Add New User </a>

                 <a href="{{ route('downloadISO') }}" class="btn btn-sm btn-teal"
                     style="margin:0; margin-right:30; float:right"> Download ISO List </a>

                 <a href="{{ route('verifyUsers') }}" class="btn btn-sm btn-teal"
                     style="margin:0; margin-right:30; float:right"> Verify User's List </a>

             </div>
         </div>
         <br>
         <div class="row">


             <div class="col-md-12">

                 <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                     class="bg3">
                     <div style=" margin: 40px; padding-right:20px">

                         <table id="usertable" class="table table-striped">
                             <thead>
                                 <tr>
                                     <th>ID</th>
                                     <th>Name</th>
                                     <th>Username</th>
                                     <th>Email</th>
                                     <th>Role</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>
                         </table>

                         <div class="heading">
                             System Users
                         </div>
                     </div>
                 </div>

             </div>

         </div>
     </body>

     <script>
         $(document).ready(function() {

             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,
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
                                 '<img src="{{ asset('images/research_logo.png') }}" style="position:absolute; top:0; left:500;float:right;" />'
                             );

                         }
                     }
                 ],
                 ajax: "{{ route('user.ajaxList') }}",
                 columns: [{
                         data: 'id',
                         name: 'id'
                     },
                     {
                         data: 'name',
                         name: 'name',
                     },
                     {
                         data: 'username',
                         name: 'username',
                     },
                     {
                         data: 'email',
                         name: 'email'
                     },
                     {
                         data: 'type',
                         name: 'type'
                     },

                     {
                         data: 'action',
                         name: 'action',
                         orderable: false,
                         searchable: false
                     },

                 ]
             });
         });
     </script>
 @endsection
