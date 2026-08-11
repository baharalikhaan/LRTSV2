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
        bottom: 5;
        left: 30;
        font-size: 14px;
        font-style: italic;
        color: red;
    }

    .btn-teal {
        color: #fff;
        background-color: #008080;
        border-color: #008080;
        float: "rght";
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

            <div class="col-md-3" style="margin-top: 10;">
            </div>

            <div class="col-md-6" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                    <div style=" margin: 40px;">
                        <form method="POST" action="{{ route('createProjectStep2') }}" id="box-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6" style="margin-top: 10;">



                                    <i class="fa fa-sitemap">
                                    </i>
                                    <b><label>Pillars affiliation of the project</label></b>
                                    <br>
                                    <br>
                                    <!-- <ul>
                                                    @foreach (session('pillars') as $pillar)
    <li>{{ $pillar->pillar }}</li>
    @endforeach
                                                </ul> -->

                                    <ul>
                                        @php
                                            $previousPillar = null;
                                        @endphp
                                        @foreach (session('pillars') as $pillar)
                                            @if ($pillar->pillar !== $previousPillar)
                                                <li><b>{{ $pillar->pillar }}</b></li>
                                                @php
                                                    $previousPillar = $pillar->pillar;
                                                @endphp
                                            @endif
                                            <ul>
                                                <li>{{ $pillar->subpillar }}</li>
                                            </ul>
                                        @endforeach
                                    </ul>

                                    @if (count(session('pillars')) < 1)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <!-- Main Dropdown for Pillar -->
                                                <div class="form-group">
                                                    <label for="pillar">Pillar</label>
                                                    <select class="form-control" id="pillar" name="pillar"
                                                        onchange="getSubpillars(this.value)">
                                                        <option value="">Select Pillar</option>
                                                        @foreach (session('uniquePillars') as $pillar)
                                                            <option value="{{ $pillar }}">{{ $pillar }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <!-- Dependent Dropdown for Subpillar -->
                                                <div class="form-group">
                                                    <label for="subpillar">Sub-Pillar</label>
                                                    <select class="form-control" id="subpillar" name="subpillar">
                                                        <option value="">Select Sub-Pillar</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <div class="col-md-6" style="margin-top: 10;">
                                    <i class="fa-solid fa-building-columns">
                                    </i><b> <label>College affiliation of the project</label> </b>

                                    <br>
                                    <br>
                                    <ul>
                                        @foreach (session('tags') as $tag)
                                            <li>{{ $tag->tagtitle }}</li>
                                        @endforeach
                                    </ul>

                                    @if (count(session('tags')) < 1)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <!-- Main Dropdown for Pillar -->
                                                <div class="form-group">

                                                    <select class="form-control" id="tags" name="tags">
                                                        <option value="">Select College</option>
                                                        @foreach (session('tags2') as $pillar)
                                                            <option value="{{ $pillar }}">{{ $pillar }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </div>

                            <br>

                            {{-- <a  href="{{ url()->previous() }}" class="btn btn-teal float-right" style="margin-left:10">Back</a> --}}

                            <button id="next" class="btn btn-teal" type="submit" style="float:right">

                                @php
                                    if (session('project')['grant_type'] == 'regular') {
                                        echo 'Next';
                                    } else {
                                        echo 'Save';
                                    }

                                @endphp
                            </button>
                            <br>
                            <br>
                        </form>
                    </div>
                    <div class="heading">
                        Projects & Pillars
                    </div>
                    <p class="footer">*Pillar & college affiliation already selected in the data comes from conf-tool</p>
                </div>
            </div>

            <div class="col-md-3" style="margin-top: 10;">
            </div>

        </div>

    </body>

    <script>
        // Function to populate subpillar dropdown based on selected pillar
        function getSubpillars(pillar) {
            var pillars2 = @json(session('pillars2'));

            // Clear previous options
            $('#subpillar').empty().append('<option value="">Select Subpillar</option>');

            // Populate subpillar options based on selected pillar
            if (pillars2[pillar]) {
                $.each(pillars2[pillar], function(index, subpillar) {
                    $('#subpillar').append('<option value="' + subpillar + '">' + subpillar + '</option>');
                });
            }
        }
    </script>
@endsection
