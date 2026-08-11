<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
</style>


<body class="body">


    <head>
        <meta charset="UTF-8">
        <title>Register Project</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')


        <div class="container" id="box-form"
            style="border: 2px solid teal; border-radius: 18px; width:800px ;  padding: 0px;">
            <h5
                style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">
                Add New Cycle</h5>
            <div class="container" id="box-form" style="  margin: 10; ">

                @if (session('successexcel'))
                    {!! session('successexcel') !!}
                    @php
                        session()->forget('successexcel');
                    @endphp
                @endif

                @if (session('successcycle'))
                    {!! session('successcycle') !!}
                    @php
                        session()->forget('successcycle');
                    @endphp
                @endif


                <form method="POST" action="{{ route('createCycle') }}" id="box-form" enctype="multipart/form-data">
                    @csrf
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <!-- <label class="col-sm-7 col-form-label">Cycle:</label> -->
                                    <div class="col-sm-7">
                                        <!-- <input type="text" class="form-control" value="{{ $cycle }}" disabled> -->
                                        <input type="hidden" name="cycle" value="{{ $cycle }}">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Cycle Title:</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control" name="cycle_title">
                                        <small class="form-text text-muted" style="color: dimgray; font-style: italic;">
                                            [If you are adding a grant to an existing cycle, make sure the cycle title is
                                            exactly the same as it already exists in the system.]
                                        </small>
                                    </div>


                                </div>


                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Grant Type:</label>
                                    <div class="col-sm-7">
                                        <select id="grant_type" name="grant_type" autocomplete="off" class="form-control">
                                            <option value="regular">Regular Grant</option>
                                            <option value="student">Student Grant</option>
                                        </select>
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Progress Report Deadline:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="prg_rpt_deadline">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Extended deadline for progress Report:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="extended_prg_rpt_deadline">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Progress Report 2 Deadline:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="prog2_rpt_deadline">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Extended deadline for Progress Report 2:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="extended_prog2_rpt_deadline">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Final Report Deadline:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="final_rpt_deadline">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Extended final report deadline:</label>
                                    <div class="col-sm-7">
                                        <input type="date" class="form-control" name="extended_final_rpt_deadline">
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Conf-Tool Data</label>
                                    <div class="col-sm-7">
                                        <input type="file" class="form-control" name="excel">
                                        <small class="form-text text-muted" style="color: dimgray; font-style: italic;">
                                            The conf-tool excel file must be according to the following templates.
                                            <br>
                                            <!-- Links with Excel icons and teal color -->
                                            <a href="{{ url('downloads/conftool_data_regular_grant.xlsx') }}"
                                                target="_blank" style="color: teal;">
                                                <i class="fas fa-file-excel" style="color: teal;"></i> Regular Grant
                                                Template
                                            </a><br>
                                            <a href="{{ url('downloads/conftool_data_student_grant.xlsx') }}"
                                                target="_blank" style="color: teal;">
                                                <i class="fas fa-file-excel" style="color: teal;"></i> Student Grant
                                                Template
                                            </a>
                                        </small>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Proposal (Zip)</label>
                                    <div class="col-sm-7">
                                        <input type="file" class="form-control" name="pdf">
                                        <small class="form-text text-muted" style="color: dimgray; font-style: italic;">
                                            The zip folder contains of proposal (pdf) of all the grants included in the
                                            cycle. the name of each pdf file will be [project id]
                                            [Example: QUST-2-CBE-2019-7.pdf]
                                        </small>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label">Can LPI upload outcomes:</label>
                                    <div class="col-sm-7">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="upload_outcomes"
                                                id="active" value="active" checked>
                                            <label class="form-check-label" for="active">
                                                Active
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="upload_outcomes"
                                                id="inactive" value="inactive">
                                            <label class="form-check-label" for="inactive">
                                                Inactive
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="upload_outcomes"
                                                id="finish" value="finish">
                                            <label class="form-check-label" for="finish">
                                                Finish
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-7 col-form-label"></div>
                                    <div class="col-sm-5">


                                        <button type="submit"
                                            style="background-color:   teal;margin-left:10px; float:right; border-color: teal; color: white;"
                                            class="btn btn-sm">
                                            {{ __('Save') }}
                                        </button>

                                        <a href="{{ route('cycle') }}"
                                            style="background-color:  teal;float:right; border-color: teal; color: white;"
                                            class="btn btn-sm">Back</a>

                                    </div>
                                </div>



                            </div>
                        </div>

                    </div>
                </form>


            </div>
        </div>
    </body>
@endsection
