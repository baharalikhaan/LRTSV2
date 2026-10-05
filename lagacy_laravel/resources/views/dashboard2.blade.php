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


        <div class="row" style="margin: 20;">
            <div class="col-md-3" style="margin-top: 10;">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">

                                <div id="grantTypeChart" style="height: 300px; width: 100%;"></div>

                                <div class="heading">
                                    Regular/Student Grants
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3" style="margin-top: 10;">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">

                                <div id="registeredChart" style="height: 300px; width: 100%;"></div>

                                <div class="heading">
                                    Registered/Unregistered Projects
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
                                <div id="pillarChart" style="height: 300px; width: 100%;"></div>
                                <div class="heading">
                                    Pillar-wise Projects
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row" style="margin: 20;">
            <div class="col-md-12" style="margin-top: 10;">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">
                                <div id="cycleChart" style="height: 300px; width: 100%;"></div>
                                <div class="heading">
                                    Cycle wise Projects
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="row" style="margin: 20;">
            <div class="col-md-12" style="margin-top: 10;">
                <div class="row">
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                            class="bg3">
                            <div style=" margin: 40px;">

                                <div id="tagChart" style="height: 300px; width: 100%;"></div>

                                <div class="heading">
                                    College/Tag wise Projects
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<script>
    window.onload = function() {

        // Define custom teal + light blue color set
        CanvasJS.addColorSet("tealBlueShades", [
            "#008080", // teal
            "#20B2AA", // light sea green
            "#40E0D0", // turquoise
            "#5F9EA0", // cadet blue
            "#87CEEB", // sky blue
            "#B0E0E6"  // powder blue
        ]);

        var grantTypeChart = new CanvasJS.Chart("grantTypeChart", {
            animationEnabled: true,
            colorSet: "tealBlueShades",
            title: {
                text: "Projects by Grant Type"
            },
            data: [{
                type: "pie",
                dataPoints: [
                    @foreach ($grantTypeData as $item)
                        {
                            label: "{{ $item->grant_type }}",
                            y: {{ $item->total }}
                        },
                    @endforeach
                ]
            }]
        });
        grantTypeChart.render();

        var pillarChart = new CanvasJS.Chart("pillarChart", {
            animationEnabled: true,
            colorSet: "tealBlueShades",
            title: {
                text: "Projects per Pillar"
            },
            axisY: {
                title: "Number of Projects"
            },
            data: [{
                type: "column",
                dataPoints: [
                    @foreach ($pillarData as $item)
                        {
                            label: "{{ $item->pillar }}",
                            y: {{ $item->total }}
                        },
                    @endforeach
                ]
            }]
        });
        pillarChart.render();

        var cycleChart = new CanvasJS.Chart("cycleChart", {
            animationEnabled: true,
            colorSet: "tealBlueShades",
            title: {
                text: "Projects per Cycle"
            },
            axisY: {
                title: "Number of Projects"
            },
            data: [{
                type: "bar",
                dataPoints: [
                    @foreach ($cycleData as $item)
                        {
                            label: "{{ $item->cycle_title }}",
                            y: {{ $item->total }}
                        },
                    @endforeach
                ]
            }]
        });
        cycleChart.render();

        var tagChart = new CanvasJS.Chart("tagChart", {
            animationEnabled: true,
            colorSet: "tealBlueShades",
            title: {
                text: "Projects per Tag"
            },
            axisY: {
                title: "Number of Projects"
            },
            data: [{
                type: "column",
                dataPoints: [
                    @foreach ($tagData as $item)
                        {
                            label: "{{ $item->tag }}",
                            y: {{ $item->total }}
                        },
                    @endforeach
                ]
            }]
        });
        tagChart.render();

        var registeredChart = new CanvasJS.Chart("registeredChart", {
            animationEnabled: true,
            colorSet: "tealBlueShades",
            title: {
                text: "Registered vs Unregistered Projects"
            },
            data: [{
                type: "pie",
                dataPoints: [
                    @foreach ($registeredData as $item)
                        {
                            label: "{{ $item->status }}",
                            y: {{ $item->total }}
                        },
                    @endforeach
                ]
            }]
        });
        registeredChart.render();
    }
</script>

    </body>

    </html>
@endsection
