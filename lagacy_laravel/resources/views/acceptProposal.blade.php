<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


    <meta charset="UTF-8">
</head>
<style>
    input[type="radio"]:checked {
        background-color: teal;
        border-color: teal;
    }

    /* Customize the color for the unchecked radio button */
    input[type="radio"] {
        background-color: white;
        border-color: teal;
    }

    /* Style to hide the default radio button appearance */
    input[type="radio"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid teal;
        outline: none;
        display: inline-block;
        vertical-align: middle;
        cursor: pointer;
    }

    /* Custom checkbox styling */
    .teal-checkbox {
        position: relative;
        width: 20px;
        height: 20px;
        appearance: none;
        /* Remove default checkbox */
        border: 2px solid #ccc;
        border-radius: 3px;
        outline: none;
        cursor: pointer;
    }

    /* The box itself when checked */
    .teal-checkbox:checked {
        border-color: teal;
        background-color: white;
        /* Keep the box background white */
    }

    /* Create the custom checkmark */
    .teal-checkbox:checked::after {
        content: '';
        position: absolute;
        width: 5px;
        height: 10px;
        border: solid teal;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        top: 2px;
        left: 6px;
    }

    /* Add a slight hover effect */
    .teal-checkbox:hover {
        border-color: teal;
    }

    /* Align the checkbox and label text */
    .form-check {
        display: flex;
        align-items: center;
    }

    .form-check-label {
        margin-left: 8px;
        /* Spacing between the checkbox and label */
    }
</style>

<body class="body">



    <br>
    <br>


    <div class="container" id="box-form"
        style="border: 2px solid teal; border-radius: 18px; width:700px ;  padding: 0px;">
        <h5
            style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
            Agreement</h5>
        <div class="container" id="box-form" style="  margin: 10; ">

            @if (session('successacceptproposal'))
                {!! session('successacceptproposal') !!}
                @php
                    session()->forget('successacceptproposal');
                @endphp
            @endif

            @if ($data)

                <form method="POST" action="{{ route('acceptProposalPost') }}" id="box-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="container">
                        <div class="row">
                            @if ($data->proposalstatus === 'Pending')
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <!-- <label class="col-sm-7 col-form-label">Cycle:</label> -->
                                        <div class="col-sm-8">
                                            <input type="hidden" name="r_id" value="{{ $r_id }}">
                                        </div>
                                    </div>


                                    <div class="form-group row">

                                        <div class="col-sm-12">
                                            <div class="form-check">
                                                <label class="form-check-label" for="confidentiality">
                                                    I declare that I am not the co-author in the project
                                                    <strong>{{ $data->old_project_id }} </strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label"> </label>
                                        <div class="col-sm-8">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="accept"
                                                    id="active" value="Accepted" checked>
                                                <label class="form-check-label" for="active">
                                                    Accept
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="accept"
                                                    id="inactive" value="Rejected">
                                                <label class="form-check-label" for="inactive">
                                                    Reject
                                                </label>
                                            </div>

                                        </div>
                                    </div>





                                    {{-- <div class="form-group row " id="file-upload">
                                        <label class="col-sm-5 col-form-label">Upload Agreement</label>
                                        <div class="col-sm-7">

                                            <input type="file" class="form-check-input" name="file">

                                        </div>
                                    </div> --}}

                                    <div class="form-group row " id="file-upload">
                                        <label class="col-sm-5 col-form-label"></label>
                                        <div class="col-sm-7">

                                            @if ($errors->any())
                                                <div class="alert alert-danger">
                                                    @foreach ($errors->all() as $error)
                                                        {{ $error }}
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <div class="col-sm-7 col-form-label"></div>
                                        <div class="col-sm-5">
                                            <button type="submit"
                                                style="background-color:  teal;float:right; border-color: teal; color: white;"
                                                class="btn btn-sm">
                                                {{ __('Save') }}
                                            </button>
                                        </div>
                                    </div>

                                </div>
                        </div>
                    @elseif ($data->proposalstatus === 'Accepted')
                        <table>
                            <tr>
                                <td colspan="2" style="padding-bottom: 10px;">You have
                                    @if ($data->proposalstatus == 'Accepted')
                                        <b style="color:green">Accepted</b>
                                    @else
                                        <b style="color:red">Rejected</b>
                                    @endif
                                    the invitation.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-right: 10px;"><a
                                        href="{{ route('grading', ['p_id' => $data->project_id]) }}">Review Project</a>
                                </td>
                            </tr>
                            {{-- <tr>
                                <td style="padding-right: 10px;"><a
                                        href="{{ route('grading', ['p_id' => $data->project_id]) }}">Review Project</a>
                                </td>
                                <td>
                                    @php
                                        $str = $data->cycle_title . '/' . $data->old_project_id . '-' . $data->email;
                                        $link =
                                            '<a href="#" onClick="showModal(\'' .
                                            $str .
                                            '\')" class="btn btn-teal btn-sm">View Agreement</a>';
                                    @endphp
                                    {!! $link !!}
                                </td>
                            </tr> --}}
                        </table>
                    @else
                        <p>You have <b style="color:green;">{{ $data->proposalstatus }}</b> the invitation.</p>
                        <p> <a href="{{ route('cycles') }}">Click Here </a> to go to your dashboard.
                        </p>
            @endif

        </div>
        </form>
    @else
        {{ 'The project is no longer available' }}
        @endif

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


<script>
    function showModal(id) {

        $('#modalTitle').text(id);
        $('#body').html('<iframe src="{{ url('/') }}/serveFile2?file=reviewers_agreements/' + id +
            '.pdf" id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>');


        $('#modalagreement').modal('show');
    }

    $(document).ready(function() {

        // Listen for changes in the radio buttons
        $('input[type=radio][name=accept]').change(function() {
            if (this.value === 'Accepted') {
                // If "Accept" is selected, show the file upload control
                $('#file-upload').show();
            } else if (this.value === 'Rejected') {
                // If "Reject" is selected, hide the file upload control
                $('#file-upload').hide();
            }
        });
    });
</script>
