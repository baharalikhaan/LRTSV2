<head>
    <title>RTS - Conf-Tool Projects</title>
    <style>
        body {
            font-size: 0.65rem;
            /* or try 12px or 13px */
        }

        .label-teal {
            background-color: #008080;
            /* Teal color */
            color: white;
            /* Text color */
            padding: 0.05em 0.4em;
            /* Reduced padding for a more oval shape */
            border-radius: 30px;
            /* Oval shape */
            display: inline-block;
            /* Ensure proper spacing */
        }

        table {
            font-size: 0.80rem;
            /* ensure tables shrink too */
        }

        .container {
            display: flex;
            margin: 0;
        }

        .section {
            text-align: left;
            border-right: 1px solid #ccc;
            padding-left: 20px;
            padding-top: 40px;
            padding-right: 20px;
        }




        .section2 {
            text-align: left;
            padding: 20px;
        }
    </style>

</head>

@extends('layouts.app')

@section('title', 'Home Page')

@section('content')

    <body class="body">

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if (isset($message))
            <div class="message" align="center">
                {{ $message }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">



                    <div class="container">

                        <div class="section">
                            <h2> <a href="{{ route('confcycles') }}">
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
                            <b> Total Projects: </b>
                            <span>{{ $cycles->total }}</span>
                        </div>
                    </div>



                </div>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-12">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">


                    @if (session('success_message'))
                        <div class="alert alert-success" style=" border-radius: 30px 30px 30px 30px;" role="alert">
                            {{ session('success_message') }}
                        </div>
                    @endif


                    <div style=" margin: 40px;">
                        @if ($user->type == 'Admin')
                            <a href="{{ route('registerProjectReminder') }}" class="btn btn-teal btn-sm"
                                style="float:left">Notify All LPIs</a>
                        @endif
                        &nbsp; &nbsp; &nbsp;
                        <br>
                        @if (session('lpiemail'))
                            {!! session('lpiemail') !!}
                            @php
                                session()->forget('lpiemail');
                            @endphp
                        @endif
                        <br>
                        <table id="conftooltable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th style="width:150px;">Project ID</th>
                                    <th>Title</th>
                                    <th>LPI</th>
                                    <th style="width:100px;">Grant Type</th>
                                    <th>College</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                        <div class="heading">
                            Projects From Conf-Tool
                        </div>
                    </div>
                </div>
            </div>


            {{-- <div class="col-md-4">
                <div class="row" style="margin-top: 20; margin-right:5; padding-left:0">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin-top: 20px;">
                                <ul>
                                    @if (isset($announcement))
                                        @foreach ($announcement as $announcements)
                                            <li class="d-flex no-block card-body border-top" style="margin:2px;">
                                                <div>
                                                    <h5 class="text-muted">{{ $announcements->subject }}</h5>
                                                    <span class="text-muted">{{ $announcements->content }}</span>
                                                    <a href="{{ route('announcementDetail', $announcements->id) }}">Read
                                                        more...</a>
                                                </div>
                                                <div class="ml-auto"
                                                    style="border: 1px solid teal;  padding:10; border-radius: 15px; background-color:#E9F6F6">
                                                    <div class="tetx-center">
                                                        <h5 class="text-muted m-b-0" style="text-align:center">
                                                            @php echo date('d', strtotime($announcements->duedate)) @endphp</h5>
                                                        <span class="text-muted font-16">@php echo date('F', strtotime($announcements->duedate)) @endphp</span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                                <div class="heading">
                                    Announcements
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </body>

    <script>
        $(document).ready(function() {
            let cycle = "{{ $cycle }}"; // Pass PHP variable to JS

            table = $('#conftooltable').DataTable({

                "processing": true,
                "serverSide": true,
                columns: [{
                        data: 'old_project_id',
                        name: 'old_project_id'
                    },
                    {
                        data: 'title',
                        name: 'title',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'grant_type',
                        name: 'grant_type'
                    },
                    {
                        data: 'tags',
                        name: 'tags'
                    },
                    {
                        data: 'action',
                        name: 'action',
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
                                '<img src="{{ asset('images/research_logo.png') }}" style="position:absolute; top:0; left:500;float:right;" />'
                            );
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('home.ajaxList') }}",
                    data: function(d) {
                        d.cycle = cycle; // Send $cycle to server
                    }
                },
                "columnDefs": [{
                    "targets": [-1],
                    "orderable": false
                }],
            });
        });
    </script>






@endsection
