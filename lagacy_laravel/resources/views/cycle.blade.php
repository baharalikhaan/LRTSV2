<head>
    <title>All Cycles</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS Cycles List</title>

    <style>
        .heading {
            position: absolute;
            top: -15;
            left: 35;
            background-color: teal;
            color: white;
            padding: 6px;
            border-radius: 15px 15px 15px 15px;
        }

        .btn-teal {
            color: #fff;
            background-color: #008080;
            /* Teal color */
            border-color: #008080;
            /* Teal color */
        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            /* Darker Teal color on hover */
            border-color: #005959;
            /* Darker Teal color on hover */
        }
    </style>

</head>

<body class="body">


    <head>
        <meta charset="UTF-8">
        <title>Register Project</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')


        <div class="row">

            <div class="col-md-12">


                <a href="{{ route('newCycle') }}" class="btn btn-sm btn-teal"
                    style="margin:0; margin-right:30; float:right"> Add Cycle </a>

            </div>
        </div>


        <div class="row">
            <div class="col-md-12" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
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
                                    <th>Cycle Title</th>
                                    <th>Progress Report</th>
                                    <th>Extension (Progress Report)</th>
                                    <th>Final Report</th>
                                    <th>Extension (Final Report)</th>
                                    <th>Upload Outcomes</th>
                                    <th>Status</th>
                                    <th>Action</th>

                                </tr>
                            </thead>
                        </table>
                        <div class="heading">
                            Cycles
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>



    <script type="text/javascript">
        $(document).ready(function() {
            $('#usertable').DataTable({
                processing: true,
                serverSide: true,

                ajax: "{{ route('home.cycle') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'cycle_title',
                        name: 'cycle_title'
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
                        data: 'upload_outcomes',
                        name: 'upload_outcomes'
                    },

                    {
                        data: 'status',
                        name: 'Status'
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
                    'print'
                ]
            });
        });
    </script>
@endsection
