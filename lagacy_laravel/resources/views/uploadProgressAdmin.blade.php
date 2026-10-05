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

                  @if (session('successprogressupload'))
                      {!! session('successprogressupload') !!}
                      @php
                          session()->forget('successprogressupload');
                      @endphp
                  @endif


                  <table id="usertable" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Project Id</th>
                              <th>Project Title</th>
                              <th>Progress Report Deadline</th>
                              <th>Extended Progress Report Deadline</th>
                              <th>Final Report Deadline</th>
                              <th>Extended Final Report Deadline</th>

                              <th>Upload Reports</th>

                          </tr>
                      </thead>
                  </table>
                  <div class="heading">
                      Upload Progress Reports (Deadline Passed)
                  </div>
              </div>
          </div>
          </div>
          </div>
      </body>



      <script type="text/javascript">
          $(document).ready(function() {
              $('#progress').value = 'Progress';

              $('#usertable').DataTable({
                  processing: true,
                  serverSide: true,

                  ajax: "{{ route('ajaxUploadProgressAdmin') }}",
                  columns: [{
                          data: 'old_project_id',
                          name: 'old_project_id'
                      },
                      {
                          data: 'title',
                          name: 'title'
                      },

                      {
                          data: 'prog_rpt_deadline',
                          name: 'prog_rpt_deadline'
                      },

                      {
                          data: 'extended_prog_rpt_deadline',
                          name: 'extended_prog_rpt_deadline'
                      },
                      {
                          data: 'final_rpt_deadline',
                          name: 'final_rpt_deadline'
                      },

                      {
                          data: 'extended_final_rpt_deadline',
                          name: 'extended_final_rpt_deadline'
                      },
                      {
                          data: 'action',
                          name: 'Action',
                          orderable: false,
                          searchable: false,

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
