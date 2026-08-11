


<head>
    <title>RTS - Conf-Tool Projects</title>
    <style>
        body {
            font-size: 0.85rem;
            /* or try 12px or 13px */
        }

        table {
            font-size: 0.85rem;
            /* ensure tables shrink too */
        }
    </style>

</head>



<body class="body">
    @include('components.sidebar')
    @include('components.navbar')

    <br>
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
        <div class="col-md-8">
            <div class="row" style="margin-top: 20; margin-left:20; padding-left:40">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                        class="bg3">
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
                                        <th>Project ID</th>
                                        <th>Title</th>
                                        <th>LPI</th>
                                        <th>Grant Type</th>
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
            </div>
        </div>

        <div class="col-md-4">
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
        </div>
    </div>
</body>

<script>
    $(document).ready(function() {
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
            ajax: "{{ route('home.ajaxList') }}",

            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],

        });

    });
</script>
