<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Settings</title>


</head>

@extends('layouts.app')
@section('title', 'Home Page')
@section('content')


    <body class="body">

        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6" class="bg3">
            <div style=" margin: 40px;">

                @if (session('successcycle'))
                    {!! session('successcycle') !!}
                    @php
                        session()->forget('successcycle');
                    @endphp
                @endif


                <div class="row">

                    <div class="col-md-12">

                        <a href="{{ route('newAnnouncement') }}" class="btn btn-sm btn-teal"
                            style="margin:0; margin-right:30; float:right"> New Announcement </a>



                    </div>
                </div>

                <br>

                <table id="usertable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Content</th>
                            <th>Visibility</th>
                            <th style="width: 100px;">Due Date</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                </table>
                <div class="heading">
                    Announcements
                </div>
            </div>

        </div>
    </body>


    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#usertable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('ajaxListAnnouncement') }}",
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'subject',
                        name: 'subject'
                    },
                    {
                        data: 'content',
                        name: 'content'
                    },

                    {
                        data: 'type',
                        name: 'type'
                    },

                    {
                        data: 'duedate',
                        name: 'duedate'
                    },

                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
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
                                '<img src="{{ asset('images/research_logo.png') }}" style="position:absolute; top:0; left:500;float:right;" />'
                            );

                        }
                    }
                ]
            });

            console.log(table.buttons().container()[0]);
            // Check if DataTables Buttons are initialized properly
            if ($.fn.DataTable.Buttons) {
                console.log('DataTables Buttons initialized successfully');
            } else {
                console.error('DataTables Buttons initialization failed');
            }

        });
    </script>
@endsection
