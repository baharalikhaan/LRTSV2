<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<style>
    .heading {
        position: absolute;
        top: -15;
        left: 35;
        background-color: teal;
        color: white;
        padding: 6px;
        border-radius: 15px 15px 15px 15px;
    }

    .heading2 {
        position: absolute;
        top: -15;
        left: 35;
        background-color: #623C21;
        color: white;
        padding: 6px;
        border-radius: 15px 15px 15px 15px;
    }

    .footer {
        position: absolute;
        bottom: 1;
        right: 55;
        font-size: 11px;
        font-style: italic;
        color: #623C21;
    }

    .btn-teal {
        color: #fff;
        background-color: #008080;
        border-color: #008080;
    }

    .btn-teal:hover {
        color: #fff;
        background-color: #005959;
        border-color: #005959;
    }
</style>



<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Register Project</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')

        <br>

        <div class="row" style="margin:20; ">
            {{-- <div class="col-md-8" style="margin-top: 10;">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                <div style=" margin: 40px;">


                    @php
                        use Illuminate\Support\Facades\Storage;

                        $filePath =
                            'uploads/lpi_project_proposals/' .
                            $projid_title_user->cycle_title .
                            '/' .
                            $projid_title_user->old_project_id .
                            '.pdf';
                        $fileExists = Storage::exists($filePath);

                    @endphp

                    @if (!$fileExists)
                        <p>
                            Proposal not available

                        </p>
                    @else
                        <iframe
                            src="{{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ $projid_title_user->cycle_title }}/{{ $projid_title_user->old_project_id }}.pdf"
                            id="pdfViewer" style="height:1200; width:100%; padding:10;"></iframe>
                    @endif

                </div>

                <div class="heading">
                    Project Proposal
                </div>
            </div>
        </div> --}}
            <div class="col-md-3" style="margin-top: 10;">
            </div>
            <div class="col-md-6" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                    <div style=" margin: 40px;">


                        <form method="POST" action="{{ route('mintProject') }}" id="box-form">
                            @csrf
                            <div>
                                <div id="header" class="w3-bar-block w3-large">
                                    <h5 class="h5" align="center">Project Information</h5>
                                </div>
                                <div id="input">
                                    <br>

                                    <input type="hidden" name="conf_tool_id" value={{ $projid_title_user->conf_tool_id }}>
                                    </input>
                                    <input type="hidden" name="grant_type" value={{ $projid_title_user->grant_type }}>
                                    </input>


                                    @error('project_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong></span>
                                    @enderror
                                    <br>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label text-right">Project
                                            ID</label>
                                        <div class="col-sm-10">
                                            <input class="form-control" type="text"
                                                value="{{ $projid_title_user->old_project_id }}" disabled> </input>
                                            <input type="hidden" name="old_project_id"
                                                value="{{ $projid_title_user->old_project_id }}"> </input>
                                        </div>
                                    </div>



                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong></span>
                                    @enderror
                                    <br>
                                    <div class="form-group row">
                                        <label for="title" class="col-sm-2 col-form-label text-right">Project
                                            Title</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" disabled>{{ $projid_title_user->title }}</textarea>
                                            <input type="hidden" name="title" value="{{ $projid_title_user->title }}">
                                            </input>
                                        </div>
                                    </div>


                                    @error('cycle')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong></span>
                                    @enderror
                                    <br>
                                    <div class="form-group row">
                                        <label for="cycle" class="col-sm-2 col-form-label text-right">Project
                                            Cycle</label>
                                        <div class="col-sm-10">


                                            <input class="form-control" type="text"
                                                value="{{ $projid_title_user->cycle_title }}" disabled> </input>
                                            <input type="hidden" name="cycle" value="{{ $projid_title_user->cycleid }}">
                                            </input>

                                        </div>
                                    </div>



                                    @error('users')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong></span>
                                    @enderror
                                    <br>
                                    <div class="form-group row">
                                        <label for="users" class="col-sm-2 col-form-label text-right">Project
                                            User</label>
                                        <div class="col-sm-10">


                                            <input class="form-control" type="text"
                                                value="{{ $projid_title_user->email }}" disabled> </input>
                                            <input type="hidden" name="users" value="{{ $projid_title_user->userid }}">
                                            </input>

                                        </div>
                                    </div>

                                </div>
                                <br>
                                {{-- <a  href="{{ url('/home') }}" class="btn btn-teal float-right" style="margin-left:10">Back</a> --}}
                                <button id="next" class="btn btn-teal" type="submit" style="float:right">Next</button>
                            </div>
                            <br>
                            <br>
                    </div>
                    </form>
                    <div class="heading">
                        New Project Registration
                    </div>

                </div>
            </div>

            <div class="col-md-3" style="margin-top: 10;">
            </div>
        </div>
    </body>
@endsection
