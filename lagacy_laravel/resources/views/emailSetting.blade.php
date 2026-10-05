<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Template Settings</title>


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

                        <a href="{{ route('emailNew') }}" class="btn btn-sm btn-teal"
                            style="margin:0; margin-right:30; float:right"> New Email Template </a>
                    </div>
                </div>

                <br>
                <table id="usertable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Action</th>

                        </tr>
                    </thead>
                </table>
                <div class="heading">
                    Email Template Settings
                </div>
            </div>
        </div>
    </body>



    <script type="text/javascript">
        $(document).ready(function() {
            $('#usertable').DataTable({
                processing: true,
                serverSide: true,

                ajax: "{{ route('ajaxListemailSetting') }}",
                columnDefs: [{
                    targets: 'sgnature',
                    wrap: true
                }],
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'subject',
                        name: 'subject'
                    },

                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endsection
