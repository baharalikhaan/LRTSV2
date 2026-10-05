<!DOCTYPE html>

<head>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.1.1/chart.min.js"></script>
    <style>
        .sidebar {
            float: left;
            width: 20%;
            text-align: left;
        }

        #dashboard {
            padding-left: 10%;
        }

        #num {
            font-size: 140%;
            color: teal;
            font-weight: bold;
        }

        p {
            font-size: 70%;
            font-weight: bold;
        }
    </style>
</head>
@include('components.sidebar')
@include('components.navbar')

<body>
    <canvas id="myChart" height="100px"></canvas>
    <div id=dashboard>
        <div class="sidebar">
            <p>No. of active Cycle</p>
            <div id='num'>{{$cycle}}</div>
        </div>
        <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        <div id="chartContainer1" style="width: 35%; height: 300px;display: inline-block; position:relative; left: 20px;"></div>
        <div id="chartContainer2" style="width: 35%; height: 300px;display: inline-block; position:relative; left: 100px;"></div><br />
        <div class="sidebar">
            <p>No. of Projects</p>
            <div id='num'>{{$projects}}</div>
        </div>
        <div id="chartContainer3" style="width: 35%; height: 300px;display: inline-block; position:relative; left: 20px;"></div>
        <div id="chartContainer4" style="width: 35%; height: 300px;display: inline-block; position:relative; left: 100px;"></div>
        <div class="sidebar">
            <p>No. of Users</p>
            <div id='num'>{{$users}}</div>
        </div>
    </div>

    <div>

    </div>
</body>
<script>
    var chart = new CanvasJS.Chart("chartContainer1", {
        animationEnabled: true,
        title: {
            text: "Spline Area Chart"
        },
        axisX: {
            interval: 10,
        },
        data: [{
            type: "splineArea",
            color: "rgba(255,12,32,.3)",
            dataPoints: [{
                    x: new Date(1992, 0),
                    y: 2506000
                },
                {
                    x: new Date(1993, 0),
                    y: 2798000
                },
                {
                    x: new Date(1994, 0),
                    y: 3386000
                },
                {
                    x: new Date(1995, 0),
                    y: 6944000
                },
                {
                    x: new Date(1996, 0),
                    y: 6026000
                },
                {
                    x: new Date(1997, 0),
                    y: 2394000
                },
                {
                    x: new Date(1998, 0),
                    y: 1872000
                },
                {
                    x: new Date(1999, 0),
                    y: 2140000
                },
                {
                    x: new Date(2000, 0),
                    y: 7289000
                },
                {
                    x: new Date(2001, 0),
                    y: 4830000
                },
                {
                    x: new Date(2002, 0),
                    y: 2009000
                },
                {
                    x: new Date(2003, 0),
                    y: 2840000
                },
                {
                    x: new Date(2004, 0),
                    y: 2396000
                },
                {
                    x: new Date(2005, 0),
                    y: 1613000
                },
                {
                    x: new Date(2006, 0),
                    y: 2821000
                }
            ]
        }, ]
    });
    chart.render();

    var pillarCounts = @json($pillar); 
    var dataPoints = [];  
    pillarCounts.forEach(function(pillar) {
        dataPoints.push({
            y: pillar.project_count,
            legendText: pillar.pillar_name,
            indexLabel: pillar.pillar_name
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
        }]
    });
    chart.render();

    var chart = new CanvasJS.Chart("chartContainer3", {
        animationEnabled: true,
        title: {
            text: "Line Chart"
        },
        axisX: {
            valueFormatString: "MMM",
            interval: 1,
            intervalType: "month"
        },
        axisY: {
            includeZero: false
        },
        data: [{
            type: "line",
            dataPoints: [{
                    x: new Date(2012, 00, 1),
                    y: 450
                },
                {
                    x: new Date(2012, 01, 1),
                    y: 414
                },
                {
                    x: new Date(2012, 02, 1),
                    y: 520,
                    indexLabel: "highest",
                    markerColor: "red",
                    markerType: "triangle"
                },
                {
                    x: new Date(2012, 03, 1),
                    y: 460
                },
                {
                    x: new Date(2012, 04, 1),
                    y: 450
                },
                {
                    x: new Date(2012, 05, 1),
                    y: 500
                },
                {
                    x: new Date(2012, 06, 1),
                    y: 480
                },
                {
                    x: new Date(2012, 07, 1),
                    y: 480
                },
                {
                    x: new Date(2012, 08, 1),
                    y: 410,
                    indexLabel: "lowest",
                    markerColor: "DarkSlateGrey",
                    markerType: "cross"
                },
                {
                    x: new Date(2012, 09, 1),
                    y: 500
                },
                {
                    x: new Date(2012, 10, 1),
                    y: 480
                },
                {
                    x: new Date(2012, 11, 1),
                    y: 510
                }
            ]
        }]
    });
    chart.render();

    var chart = new CanvasJS.Chart("chartContainer4", {
        animationEnabled: true,
        title: {
            text: "Column Chart"
        },
        axisX: {
            interval: 10,
        },
        data: [{
            type: "column",
            legendMarkerType: "triangle",
            legendMarkerColor: "green",
            color: "rgba(255,12,32,.3)",
            showInLegend: true,
            legendText: "Country wise population",
            dataPoints: [{
                    x: 10,
                    y: 297571,
                    label: "India"
                },
                {
                    x: 20,
                    y: 267017,
                    label: "Saudi"
                },
                {
                    x: 30,
                    y: 175200,
                    label: "Canada"
                },
                {
                    x: 40,
                    y: 154580,
                    label: "Iran"
                },
                {
                    x: 50,
                    y: 116000,
                    label: "Russia"
                },
                {
                    x: 60,
                    y: 97800,
                    label: "UAE"
                },
                {
                    x: 70,
                    y: 20682,
                    label: "US"
                },
                {
                    x: 80,
                    y: 20350,
                    label: "China"
                }
            ]
        }, ]
    });
    chart.render();
</script>

</html>