<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Cycles</title>
</head>



@extends('layouts.app')
@section('title', 'Home Page')
@section('content')

    <body class="body">

        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
            <div style=" margin: 40px;">
                <table id="usertable" class="table">
                    <thead>
                        <tr>
                            <th>Cycle Title</th>
                            <th>Status</th>
                            <th>Grant Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
                <div class="heading">
                    Cycles
                </div>
            </div>

        </div>
    </body>

    <script type="text/javascript">
        $(document).ready(function() {
            const params = new URLSearchParams(window.location.search);
            const cycle = params.get('cycle'); // "john"

            $('#usertable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('ajaxListcycleAssignView') }}",

                columns: [{
                        data: 'cycle_title',
                        name: 'cycle_title'
                    },
                    {
                        data: 'status',
                        name: 'Status'
                    },
                    {
                        data: 'grant_type',
                        name: 'grant_type'
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
                ],
                rowCallback: function(row, data, index) {
                    if (data.grant_type === 'student') {
                        $(row).css('background-color', ' #95cead');
                        $(row).css('color', 'black'); // Optional: Change text color for contrast
                    }
                }
            });
        });
    </script>
@endsection
