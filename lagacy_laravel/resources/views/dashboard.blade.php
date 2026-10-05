<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Budget API Settings</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')
        <div class="row">
            <div class="col-md-2">
                <div class="row" style="margin-top: 20; margin-left:20;">
                    <div class="col-md-12" style="margin-top: 20;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">
                                <div id='num' style="font-size:48px; text-align:center; color:teal">
                                    <b>{{ $cycle[0] }}</b>
                                </div>
                                <div class="heading">
                                    Active Cycles
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 70; margin-left:20; ">
                    <div class="col-md-12" style="margin-top: 20;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">
                                <div id='num' style="font-size:48px; text-align:center; color:teal">
                                    <b>{{ $projects[0] }}</b>
                                </div>
                                <div class="heading">
                                    Active Projects
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 70; margin-left:20; ">
                    <div class="col-md-12" style="margin-top: 20;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">
                                <div id='num' style="font-size:48px; text-align:center; color:teal">
                                    <b>{{ $users[0] }}</b>
                                </div>
                                <div class="heading">
                                    Active Users
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-10">

                <div class="row" style="margin: 20;">
                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">

                                        <div class="col-md-9" style="margin-bottom:5;">
                                        </div>
                                        <div class="col-md-3" style="margin-bottom:10; float:right">
                                            <select class="form-control" id="dataOption">
                                                <option value="college">College-wise</option>
                                                <option value="pillar">Pillar-wise</option>
                                            </select>
                                        </div>

                                        <div id="chartContainer1"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            College/Pillar wise Projects
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">
                                        <div id="chartContainer2"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            Cycle-wise Projects
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row" style="margin: 20;">
                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">

                                        <div class="col-md-9" style="margin-bottom:5;">
                                        </div>
                                        <div class="col-md-3" style="margin-bottom:10; float:right">
                                            <select class="form-control" id="dataOption2">
                                                <option value="Reviewer">Reviewer</option>
                                                <option value="LPI">LPI</option>
                                            </select>
                                        </div>
                                        <div id="chartContainer3"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            Pillar-wise Users
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">
                                        <div id="chartContainer4"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            Pillar-wise Projects
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--
                <div class="row" style="margin: 20;">
                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">

                                        <div class="col-md-9" style="margin-bottom:5;">
                                        </div>
                                        <div class="col-md-3" style="margin-bottom:10; float:right">
                                            <select class="form-control" id="dataOption3">


                                                @foreach ($activeCycles as $cycle)
                                                    <option value="{{ $cycle->id }}">{{ $cycle->cycle_title }}</option>
                                                @endforeach
                                            </select>

                                        </div>
                                        <div id="chartContainer5"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            Cycle-Wise Commitments
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6" style="margin-top: 10;">
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 10;">
                                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                                    class="bg3">
                                    <div style=" margin: 40px;">



                                        <div id="chartContainer6"
                                            style="width: 100%; height: 300px;display: inline-block; position:relative; left: 20px;">
                                        </div>
                                        <div class="heading">
                                            Cycle/Commitments-wise count
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div> --}}


            </div>


        </div>
    </body>

    <script>
        window.onload = function() {
            var customColorSet = [

                "#F5F5DC", // Beige
                "#008080", // Teal
                "#66CCCC", // Light Teal
                "#DAA520", // Goldenrod
                "#8B4513", // SaddleBrown
                "#6B8E23", // OliveDrab
                "#FFE4B5", // Moccasin
            ];

            var customColorSet2 = [
                "#008080", // Teal
            ];
            CanvasJS.addColorSet("customColorSet2", customColorSet2);
            CanvasJS.addColorSet("customColorSet", customColorSet);

            var pillarCounts = @json($projectsincycle);
            var dataPoints = [];
            pillarCounts.forEach(function(pillar) {
                dataPoints.push({
                    y: pillar.total,
                    legendText: pillar.cycle_title,
                    indexLabel: pillar.cycle_title
                });
            });

            var chart = new CanvasJS.Chart("chartContainer2", {
                animationEnabled: true,
                title: {
                    text: "Cycle-wise Projects",
                },
                data: [{
                    type: "pie",
                    showInLegend: true,
                    dataPoints: dataPoints
                }],
                colorSet: "customColorSet"
            });
            chart.render();

            var collegesData = {!! json_encode(
                $collegewise->map(function ($item, $key) {
                    return ['y' => $item['total'], 'label' => $item['tagtitle'] ?? 'Unknown'];
                }),
                JSON_NUMERIC_CHECK,
            ) !!};

            var pillarsData = {!! json_encode(
                $pillarwise->map(function ($item, $key) {
                    return ['y' => $item['total'], 'label' => $item['pillar'] ?? 'Unknown'];
                }),
                JSON_NUMERIC_CHECK,
            ) !!};

            var chart = new CanvasJS.Chart("chartContainer1", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Project Counts"
                },
                axisY: {
                    title: "Total Projects",
                    interval: 1,
                    minimum: 0
                },
                axisX: {
                    title: "Colleges"
                },
                data: [{
                    type: "column",
                    dataPoints: collegesData // Initial data
                }],
                colorSet: "customColorSet2"
            });
            chart.render();


            //initilize chart cycle-wise commitments
            var chart = new CanvasJS.Chart("chartContainer3", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "Project Counts"
                },
                axisY: {
                    title: "Total Projects",
                    interval: 1,
                    minimum: 0
                },
                axisX: {
                    title: "Colleges"
                },
                data: [{
                    type: "column",
                    dataPoints: collegesData // Initial data
                }],
                colorSet: "customColorSet2"
            });
            chart.render();


            // Event listener for data option change
            document.getElementById('dataOption').addEventListener('change', function() {
                var selectedOption = this.value;
                if (selectedOption === 'college') {
                    chart.options.data[0].dataPoints = collegesData;
                    chart.options.axisX.title = "Colleges";
                    chart.options.title.text = "College-wise";

                } else if (selectedOption === 'pillar') {
                    chart.options.data[0].dataPoints = pillarsData;
                    chart.options.axisX.title = "Pillars";
                    chart.options.title.text = "Pillar-Wise";
                }
                chart.render();
            });


            var pillarreviewerData = {!! json_encode(
                $pillarreviewer->map(function ($item, $key) {
                    return [
                        'y' => $item->total ?? 0, // Access 'total' property using->operator
                        'label' => $item->pillar ?? 'Unknown', // Access 'pillar' property using->operator
                    ];
                }),
                JSON_NUMERIC_CHECK,
            ) !!};

            var pillarslpiData = {!! json_encode(
                $pillarlpi->map(function ($item, $key) {
                    return [
                        'y' => $item->total ?? 0, // Access 'total' property using->operator
                        'label' => $item->pillar ?? 'Unknown', // Access 'pillar' property using->operator
                    ];
                }),
                JSON_NUMERIC_CHECK,
            ) !!};





            var chart2 = new CanvasJS.Chart("chartContainer3", {
                animationEnabled: true,
                theme: "light2",
                title: {
                    text: "User Counts"
                },
                axisY: {
                    title: "Total Users",
                    interval: 1,
                    minimum: 0
                },
                axisX: {
                    title: "Pillars"
                },
                data: [{
                    type: "column",
                    dataPoints: pillarreviewerData // Initial data
                }],
                colorSet: "customColorSet2"
            });
            chart2.render();

            // Event listener for data option change
            document.getElementById('dataOption2').addEventListener('change', function() {
                var selectedOption = this.value;
                if (selectedOption === 'Reviewer') {
                    chart2.options.data[0].dataPoints = pillarreviewerData;
                    chart2.options.axisX.title = "Pillars";
                    chart2.options.title.text = "Pillar-Wise Reviewers";

                } else if (selectedOption === 'LPI') {
                    chart2.options.data[0].dataPoints = pillarslpiData;
                    chart2.options.axisX.title = "Pillars";
                    chart2.options.title.text = "Pillar-Wise LPIs";
                }
                chart2.render();
            });


            // Event listener for data option change
            document.getElementById('dataOption3').addEventListener('change', function() {
                var selectedOption = this.value;
                if (selectedOption === 'Reviewer') {
                    chart2.options.data[0].dataPoints = pillarreviewerData;
                    chart2.options.axisX.title = "Pillars";
                    chart2.options.title.text = "Pillar-Wise Reviewers";

                }
                chart3.render();
            });

        }
    </script>

    </html>


@endsection
