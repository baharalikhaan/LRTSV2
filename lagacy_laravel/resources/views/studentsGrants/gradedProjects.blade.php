<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS - Graded Projects</title>


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

        .red-background {
            background-color: #f0bdb4 !important;
            color:white;
            /* You can adjust the background color here */
        }
    </style>

</head>

<body class="body">
    @include('components.projectSideBar')
    @include('components.navbar')


    <div class="row" style="margin: 20; padding-left:40">
        <div class="col-md-12" style="margin-top: 10;">
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


                                <th>Project Id</th>
                                <th>Project Title</th>
                                <th>Reviewer-1</th>
                                <th>Reviewer-2</th>
                                <th>Total</th>
                                <th>Avg Grages</th>

                                <th>Action</th>

                            </tr>
                        </thead>
                    </table>
                    <div class="heading">
                        Graded Projects
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

            ajax: "{{ route('ajaxListGradedprojects') }}",
            columns: [{
                    data: 'old_project_id',
                    name: 'old_project_id'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'Reviewer1',
                    name: 'Reviewer1'
                },
                {
                    data: 'Reviewer2',
                    name: 'Reviewer2'
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

                if (data.Reviewer1 === 'Rejected') {
                    $(row).removeClass('odd');
                    $(row).addClass('red-background');
                }
                if (data.Reviewer2 === 'Rejected') {
                    $(row).removeClass('odd');
                    $(row).addClass('red-background');
                }


            }
        });




    });
</script>
