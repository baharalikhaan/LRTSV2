<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS - All Projects</title>


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
    @include('components.projectSideBar')
    @include('components.navbar')


        <div class="row" style="margin: 20; padding-left:40">
            <div class="col-md-12" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                    <div style=" margin: 40px;">
                        <table id="usertable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width:150px;">Project ID</th>

                                    <th>Project Title</th>

                                    {{-- <th>Status</th> --}}
                                    <th style="width:450px;">Action</th>

                                </tr>
                            </thead>
                        </table>
                        <div class="heading">
                       Projects

                        </div>
                    </div>
                </div>
            </div>
        </div>


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


        $('#usertable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('AdminajaxListProjects', ['cycle' => ':cycle']) }}".replace(':cycle', cycle),
            columns: [{
                    data: 'old_project_id',
                    name: 'old_project_id'
                },

                {
                    data: 'title',
                    name: 'title'
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

        $('#reviewertable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('project.ajaxList2', ['c_id' => ':c_id']) }}".replace(':c_id', cycle),
            columns: [{
                    data: 'old_project_id',
                    name: 'old_project_id'
                },
                {
                    data: 'cycle_title',
                    name: 'Cycle_title'
                },
                {
                    data: 'title',
                    name: 'title'
                },

                {
                    data: 'proposalstatus',
                    name: 'proposalStatus'
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

                            '<img src="{{ asset('images/research_logo.png') }}" style="position:absolute; top:0; left:50;" />'
                        );

                    }
                }
            ]
        });

        $('#lpigraded').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('project.ajaxListLPIGraded', ['cycle' => ':cycle']) }}".replace(':cycle',
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
