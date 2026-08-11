<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cycles</title>
</head>

<body class="body">
    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')
        <div class="col-md-12" style="margin-top: 10;">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                <div style=" margin: 40px;">
                    <table id="usertable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cycle Title</th>
                                <th>Grant Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="heading">
                        Research Grant Cycles
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
                pageLength: 100,
                ajax: "{{ route('AdminajaxListCycle') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'cycle_title',
                        name: 'cycle_title'
                    },
                    {
                        data: 'grant_type',
                        name: 'grant_type',
                        orderable: false,
                        searchable: false
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
