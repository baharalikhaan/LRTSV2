 <body class="body">
     <!DOCTYPE html>
     <html lang="en">

     <head>
         <meta charset="UTF-8">
         <title>Budget API Settings</title>
     </head>

     @extends('layouts.app')
     @section('title', 'Home Page')
     @section('content')
         <div class="row"  >
             <div class="col-md-12" style="margin-top: 10;">
                 <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                     class="bg3">
                     <div style=" margin: 40px;">


                         @if (session('successmarkprocess'))
                             <div class="alert alert-success" role="alert">
                                 {!! session('successmarkprocess') !!}
                             </div>
                             @php
                                 session()->forget('successmarkprocess');
                             @endphp
                         @endif

                         <div style="float:right">
                             @if (session('failedEmails'))
                                 {!! session('failedEmails') !!}
                                 @php
                                     session()->forget('failedEmails');
                                 @endphp
                             @endif

                             <a href = "{{ route('fetchFailedEmails') }}" class="btn btn-teal btn-sm">
                                 Fetch Failed Emails
                             </a>
                         </div>
                         <br><br>
                         @if (session('successcycle'))
                             {!! session('successcycle') !!}
                             @php
                                 session()->forget('successcycle');
                             @endphp
                         @endif



                         <table id="usertable" class="table table-striped">
                             <thead>
                                 <tr>
                                     <th>Category</th>
                                     <th>Email</th>
                                     <th>Subject</th>
                                     <th>Body</th>
                                     <th>Status</th>
                                     <th>Comments</th>
                                     <th>DateTime</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>
                         </table>
                         <div class="heading">
                             Email Sending Status
                         </div>
                     </div>
                 </div>
             </div>
         </div>




     </body>

     <script type="text/javascript">
         $(document).ready(function() {

             //modal window


             //datatable
             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('ajaxEmailSendingStatus') }}",
                 columns: [{
                         data: 'category',
                         name: 'category'
                     },
                     {
                         data: 'email',
                         name: 'email'
                     },
                     {
                         data: 'title',
                         name: 'title'
                     },

                     {
                         data: 'body',
                         name: 'body'
                     },
                     {
                         data: 'sending_status',
                         name: 'sending_status'
                     },
                     {
                         data: 'error_message',
                         name: 'error_message'
                     },
                     {
                         data: 'datetime',
                         name: 'datetime'
                     },
                     {
                         data: 'action',
                         name: 'action'
                     }



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
