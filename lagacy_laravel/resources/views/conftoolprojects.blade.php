
<body class="body">

    <head>
        <meta charset="UTF-8">
        <title>Conf Tool Projects</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')



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
            <div class="row" >
                <div class="col-md-12" style="margin-top: 10; margin-right:10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"  class="bg3">
                        <div style=" margin: 40px;">

                            <h6 style="color:teal"><b>Cycle:</b> {{ $cycle->cycle_title }}</h6>
                            @if (session('lpiemail'))
                                {!! session('lpiemail') !!}
                                @php
                                    session()->forget('lpiemail');
                                @endphp
                            @endif

                            <a href=" {{ route('confprojectadd', $cycle->id) }}" class="btn btn-teal btn-sm" style="float:right">Add Project</a>

                            <br>
                            <br>
                            <table id="conftooltable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Project ID</th>
                                        <th>Title</th>
                                        <th>LPI</th>
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
    </div>
</body>

<script>
    $(document).ready(function() {
        var cycle = {{ $cycle->id }};
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
            //  ajax: "{{ route('home.ajaxList') }}",
            ajax: "{{ route('home.ajaxList2', ['cycle' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER',
                cycle),
            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],

        });

    });
</script>
@endsection
