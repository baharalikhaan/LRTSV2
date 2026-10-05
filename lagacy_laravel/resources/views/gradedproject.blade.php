<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graded Projects</title>


</head>
<style>
    body {
        font-size: 0.65rem;
    }

    .label-teal {
        background-color: #008080;
        color: white;
        padding: 0.05em 0.4em;
        border-radius: 30px;
        display: inline-block;
    }

    table {
        font-size: 0.80rem;
    }

    .container {
        display: flex;
        margin: 0;
    }

    .section {
        text-align: left;
        border-right: 1px solid #ccc;
        padding-left: 20px;
        padding-top: 20px;
        padding-right: 20px;
    }

    .section2 {
        text-align: left;
        padding: 20px;
    }
</style>


@extends('layouts.app')

@section('title', 'Home Page')

@section('content')

    <body class="body">
        <div class="col-md-12">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                class="bg3">
                <div class="container">
                    <div class="section">
                        <h2> <a href="{{ route('gradedcycles') }}">
                                <i class="fa fa-arrow-left" style="color: teal;" title="Back to List of projects"></i>
                            </a>
                        </h2>
                    </div>
                    <div class="section2">
                        <b>Grant Type:</b>
                        <span
                            style="color: white; background-color: {{ $cycles->grant_type == 'student' ? 'maroon' : 'teal' }}; padding: 2px 5px; border-radius: 12px;">
                            {{ $cycles->grant_type }}
                        </span>
                        <br>


                        <b> Cycle Title: </b>
                        <span>{{ $cycles->cycle_title }}</span><br>
                        {{-- <b> Total Projects: </b>
                        <span>{{ $cycles->total }}</span> --}}
                    </div>
                </div>



            </div>
        </div>

        <br>



        @if ($type == 'LPI')
            <div class="col-md-12" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                    <div style=" margin: 40px;">
                        <table id="lpigraded" class="table table-striped2">
                            <thead>

                                <tr>
                                    <th colspan="3"></th>
                                    <th colspan="2" class="progress-report">Progress Report Grading</th>
                                    <th colspan="2" class="final-report">Final Report Grading</th>

                                    <th></th>
                                </tr>

                                <tr>
                                     <th>ID</th>
                                    <th>Project Id</th>
                                    <th>Project Title</th>
                                    <th style="width:90px">Reviewer-1</th>
                                    <th style="width:90px">Reviewer-2</th>
                                    <th style="width:90px">Reviewer-1</th>
                                    <th style="width:90px">Reviewer-2 </th>

                                    <th style="width:300px;">Action</th>
                                </tr>
                            </thead>
                        </table>
                        <div class="heading">My Graded Projects</div>
                    </div>
                </div>
            </div>
        @endif


        <!-- Borderless Modal -->
        <div class="modal fade borderless-modal" id="apiModal" tabindex="-1" role="dialog"
            aria-labelledby="borderlessModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header" style="background-color: teal; text-align: center;">
                        <h5 class="modal-title text-white" id="modalTitle">DOI Verification</h5>
                        <span aria-hidden="true">&times;</span>

                    </div>

                    <div class="modal-body">

                    </div>
                </div>
            </div>
        </div>


    </body>




    <script type="text/javascript">
        function ttb(val) {
            $('#apiModal').modal('show');
        }


        var cycle = <?php echo json_encode($cycle); ?>;

        $(document).ready(function() {


            $('#lpigraded').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('project.ajaxListLPIGraded', ['cycle' => ':cycle']) }}".replace(':cycle',
                    cycle),

                columns: [{
                        data: 'id',
                        name: 'id'
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
                        data: 'prg-Reviewer1',
                        name: 'prg-Reviewer1'
                    },
                    {
                        data: 'prg-Reviewer2',
                        name: 'prg-Reviewer2'
                    },


                    {
                        data: 'fin-Reviewer1',
                        name: 'fin-Reviewer1'
                    },
                    {
                        data: 'fin-Reviewer2',
                        name: 'fin-Reviewer2'
                    },




                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },

                ],
                dom: 'lBfrtip',
                "pageLength": 100,
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

                    if (data.status === 'Rejected') {
                        $(row).removeClass('odd');
                        $(row).addClass('red-background');
                    }

                }
            });

            $('#reviewergraded').DataTable({

                processing: true,
                serverSide: true,

                ajax: "{{ route('project.ajaxListReviewerGraded', ['c_id' => ':c_id']) }}".replace(':c_id',
                    cycle),
                columns: [{
                        data: 'old_project_id',
                        name: 'old_project_id'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },

                    {
                        data: 'total',
                        name: 'total'
                    },
                    {
                        data: 'avg',
                        name: 'avg'
                    },



                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },

                ],
                dom: 'lBfrtip',
                "pageLength": 100,
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

                    if (data.status === 'Rejected') {
                        $(row).removeClass('odd');
                        $(row).addClass('red-background');
                    }

                }
            });


        });
    </script>

@endsection
