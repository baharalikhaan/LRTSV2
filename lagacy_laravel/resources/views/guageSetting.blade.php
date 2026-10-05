<head>
    <title>RTS - Conf-Tool Projects</title>
    <style>
        body {
            font-size: 0.65rem;
        }

        table {
            font-size: 0.80rem;
        }
    </style>

</head>

@extends('layouts.app')
@section('title', 'Hauge Settings')
@section('content')

    <body class="body">
        <br>

        <div class="row">

            <div class="col-md-4">

                <div class="row"  >
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 10px;  margin-bottom:30; ">


                                <div id="chart_div1" align="center">


                                </div>
                                <br>

                                @if (session('successguage1'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('successguage1') }}
                                    </div>
                                @endif
                                @if (isset($message))
                                    <div class="message" align="center">
                                        {{ $message }}
                                    </div>
                                @endif


                                <form method="POST" action="{{ route('guage') }}">
                                    @csrf
                                    <tag class="error">
                                        <?php if ($errors->any()) : ?>
                                        <?php echo $error; ?>
                                        <?php endif; ?>
                                    </tag>

                                    <input type="hidden" class="form-control" id="id" name="id" value="1">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="redFrom">Red From</label>
                                                <input type="text" class="form-control" id="redFrom" name="redFrom"
                                                    value="{{ $settings[0]->redfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="redTo">Red To</label>
                                                <input type="text" class="form-control" id="redTo" name="redTo"
                                                    value="{{ $settings[0]->redto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="yellowFrom">Yellow From</label>
                                                <input type="text" class="form-control" id="yellowFrom" name="yellowFrom"
                                                    value="{{ $settings[0]->yellowfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="yellowTo">Yellow To</label>
                                                <input type="text" class="form-control" id="yellowTo" name="yellowTo"
                                                    value="{{ $settings[0]->yellowto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="greenFrom">Green From</label>
                                                <input type="text" class="form-control" id="greenFrom" name="greenFrom"
                                                    value="{{ $settings[0]->greenfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="greenTo">Green To</label>
                                                <input type="text" class="form-control" id="greenTo" name="greenTo"
                                                    value="{{ $settings[0]->greento }}">
                                            </div>
                                        </div>
                                        <br>
                                        <hr>
                                    </div>

                                    <div class="container">
                                        <button type="submit"
                                            style="float:right; background-color: teal; border-color: teal; color: white;"
                                            class="btn btn-primary">
                                            {{ __('Update') }}
                                        </button>
                                    </div>
                                    <br>
                                </form>

                                <div class="heading">
                                    LPI Outcome
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">

                <div class="row" style="margin-top: 10; padding-left:10">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 10px;  margin-bottom:30; ">

                                <div id="chart_div2" align="center">


                                </div>
                                <br>

                                @if (session('successguage2'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('successguage2') }}
                                    </div>
                                @endif
                                @if (isset($message))
                                    <div class="message" align="center">
                                        {{ $message }}
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('guage') }}">
                                    @csrf
                                    <tag class="error">
                                        <?php if ($errors->any()) : ?>
                                        <?php echo $error; ?>
                                        <?php endif; ?>
                                    </tag>

                                    <input type="hidden" class="form-control" id="id" name="id" value="2">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="redFrom">Red From</label>
                                                <input type="text" class="form-control" id="redFrom" name="redFrom"
                                                    value="{{ $settings[1]->redfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="redTo">Red To</label>
                                                <input type="text" class="form-control" id="redTo" name="redTo"
                                                    value="{{ $settings[1]->redto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="yellowFrom">Yellow From</label>
                                                <input type="text" class="form-control" id="yellowFrom"
                                                    name="yellowFrom" value="{{ $settings[1]->yellowfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="yellowTo">Yellow To</label>
                                                <input type="text" class="form-control" id="yellowTo"
                                                    name="yellowTo" value="{{ $settings[1]->yellowto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="greenFrom">Green From</label>
                                                <input type="text" class="form-control" id="greenFrom"
                                                    name="greenFrom" value="{{ $settings[1]->greenfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="greenTo">Green To</label>
                                                <input type="text" class="form-control" id="greenTo" name="greenTo"
                                                    value="{{ $settings[1]->greento }}">
                                            </div>
                                        </div>
                                        <br>
                                        <hr>
                                    </div>

                                    <div class="container">
                                        <button type="submit"
                                            style="float:right; background-color: teal; border-color: teal; color: white;"
                                            class="btn btn-primary">
                                            {{ __('Update') }}
                                        </button>
                                    </div>
                                    <br>
                                </form>

                                <div class="heading">
                                    LPI Average Score
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">

                <div class="row" style="margin-top: 10; margin-right:20; padding-left:10">
                    <div class="col-md-12" style="margin-top: 10; ">
                        <div style="border: 2px solid teal;  border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 10px;  margin-bottom:30; ">



                                <div id="chart_div3" align="center">


                                </div>
                                <br>


                                @if (session('successguage3'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('successguage3') }}
                                    </div>
                                @endif
                                @if (isset($message))
                                    <div class="message" align="center">
                                        {{ $message }}
                                    </div>
                                @endif


                                <form method="POST" action="{{ route('guage') }}">
                                    @csrf
                                    <tag class="error">
                                        <?php if ($errors->any()) : ?>
                                        <?php echo $error; ?>
                                        <?php endif; ?>
                                    </tag>

                                    <input type="hidden" class="form-control" id="id" name="id"
                                        value="3">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="redFrom">Red From</label>
                                                <input type="text" class="form-control" id="redFrom" name="redFrom"
                                                    value="{{ $settings[2]->redfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="redTo">Red To</label>
                                                <input type="text" class="form-control" id="redTo" name="redTo"
                                                    value="{{ $settings[2]->redto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="yellowFrom">Yellow From</label>
                                                <input type="text" class="form-control" id="yellowFrom"
                                                    name="yellowFrom" value="{{ $settings[2]->yellowfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="yellowTo">Yellow To</label>
                                                <input type="text" class="form-control" id="yellowTo"
                                                    name="yellowTo" value="{{ $settings[2]->yellowto }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="greenFrom">Green From</label>
                                                <input type="text" class="form-control" id="greenFrom"
                                                    name="greenFrom" value="{{ $settings[2]->greenfrom }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="greenTo">Green To</label>
                                                <input type="text" class="form-control" id="greenTo" name="greenTo"
                                                    value="{{ $settings[2]->greento }}">
                                            </div>
                                        </div>
                                        <br>
                                        <hr>
                                    </div>

                                    <div class="container">
                                        <button type="submit"
                                            style="float:right; background-color: teal; border-color: teal; color: white;"
                                            class="btn btn-primary">
                                            {{ __('Update') }}
                                        </button>
                                    </div>
                                    <br>
                                </form>

                                <div class="heading">
                                    Reviewer Grading
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <br>
    </body>


    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['gauge']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            var data1 = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Score', 500],
            ]);

            var data2 = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Score', 1000],
            ]);

            var data3 = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Score', 7],
            ]);

            var options1 = {
                width: 300,
                height: 150,
                redFrom: {{ $settings[0]->redfrom }},
                redTo: {{ $settings[0]->redto }},
                yellowFrom: {{ $settings[0]->yellowfrom }},
                yellowTo: {{ $settings[0]->yellowto }},
                greenFrom: {{ $settings[0]->greenfrom }},
                greenTo: {{ $settings[0]->greento }},
                minorTicks: 10,
                max: {{ $settings[0]->greento }}
            };

            var options2 = {
                width: 300,
                height: 150,
                redFrom: {{ $settings[1]->redfrom }},
                redTo: {{ $settings[1]->redto }},
                yellowFrom: {{ $settings[1]->yellowfrom }},
                yellowTo: {{ $settings[1]->yellowto }},
                greenFrom: {{ $settings[1]->greenfrom }},
                greenTo: {{ $settings[1]->greento }},
                minorTicks: 10,
                max: {{ $settings[1]->greento }}
            };


            var options3 = {
                width: 300,
                height: 150,
                redFrom: {{ $settings[2]->redfrom }},
                redTo: {{ $settings[2]->redto }},
                yellowFrom: {{ $settings[2]->yellowfrom }},
                yellowTo: {{ $settings[2]->yellowto }},
                greenFrom: {{ $settings[2]->greenfrom }},
                greenTo: {{ $settings[2]->greento }},
                minorTicks: 1,
                max: {{ $settings[2]->greento }}
            };


            var chart1 = new google.visualization.Gauge(document.getElementById('chart_div1'));
            chart1.draw(data1, options1);

            var chart2 = new google.visualization.Gauge(document.getElementById('chart_div2'));
            chart2.draw(data2, options2);


            var chart3 = new google.visualization.Gauge(document.getElementById('chart_div3'));
            chart3.draw(data3, options3);

            setInterval(function() {
                data1.setValue(0, 1, 1000);
                chart1.draw(data1, options1);

                data2.setValue(0, 1, 500);
                chart2.draw(data2, options2);

                data3.setValue(0, 1, 5);
                chart3.draw(data3, options3);
            });



        }
    </script>
@endsection
