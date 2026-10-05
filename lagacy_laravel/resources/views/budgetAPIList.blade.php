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



         <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6" class="bg3">

             <div style=" margin: 40px;">
                 <form action="{{ 'budgetAPISync' }}" method="post" enctype="multipart/form-data">
                     @csrf
                     <button type="submit" class="btn btn-teal" style='float:right'>
                         Sync Budget Data
                     </button>
                     <span style=' font-size:12;float:right; padding-right:20px;'>Last updated<br> <strong>
                             {{ $date }}</span></strong>
                     <br>
                 </form>
                 <br>
                 <br>
                 @if (session('SyncApi'))
                     <div class="alert alert-success" role="alert">
                         {{ session('SyncApi') }}
                     </div>
                 @endif

                 @if (session('budgetemail'))
                     <div class="alert alert-success" role="alert">
                         {{ session('budgetemail') }}
                     </div>
                 @endif

                 @if (isset($message))
                     <div class="message" align="center">
                         {{ $message }}
                     </div>
                 @endif


                 <table id="usertable" class="table table-striped">
                     <thead>
                         <tr>
                             <th>Project ID</th>
                             <th>Budget Amount</th>
                             <th>Actual Expense Amount</th>
                             <th>Commitment Amount</th>
                             <th>Avalable Balance</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                 </table>
             </div>
         </div>
     </body>


     <script type="text/javascript">
         $(document).ready(function() {
             $('#usertable').DataTable({
                 processing: true,
                 serverSide: true,

                 ajax: "{{ route('ajaxBudgetAPIList') }}",
                 columns: [{
                         data: 'project_name',
                         name: 'project_name'
                     },
                     {
                         data: 'budget_amount',
                         name: 'budget_amount'
                     },

                     {
                         data: 'actual_exp_amount',
                         name: 'actual_exp_amount'
                     },
                     {
                         data: 'committment_amount',
                         name: 'committment_amount'
                     },

                     {
                         data: 'available_balance',
                         name: 'available_balance'
                     },
                     {
                         data: 'action',
                         name: 'action'
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
