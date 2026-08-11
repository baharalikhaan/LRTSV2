<body class="body">

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>RTS Downloads</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')
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
                            <th>Reviewer's Name</th>
                            <th>Email</th>
                            <th>Project</th>
                            <th>Agreement Status</th>
                            <th>Agreement Date</th>
                            <th>Agreement</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>


        <!-- Borderless Modal -->
        <div class="modal fade borderless-modal" id="modalagreement" tabindex="-1" role="dialog"
            aria-labelledby="borderlessModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header" style="background-color: teal; text-align: center;">
                        <h5 class="modal-title text-white" id="modalTitle"></h5>
                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body" id="body">
                        <!-- Your modal body content goes here -->
                    </div>
                </div>
            </div>
        </div>


    </body>

    <script type="text/javascript">
        function showModal(id) {
            $('#modalTitle').text(id);
            $('#body').html('<iframe src="{{ url('/') }}/serveFile2?file=reviewers_agreements/' + id +
                '.pdf" id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>');
            $('#modalagreement').modal('show');
        }

        $(document).ready(function() {

            $('#usertable').DataTable({
                processing: true,
                serverSide: true,

                ajax: "{{ route('ajaxreviewerAgrementsAdmin') }}",
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'old_project_id',
                        name: 'old_project_id'
                    },

                    {
                        data: 'proposalstatus',
                        name: 'proposalstatus'
                    },
                    {
                        data: 'statusdate',
                        name: 'statusdate'
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
        });
    </script>


@endsection
