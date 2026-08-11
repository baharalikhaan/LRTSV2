<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
            padding: 7px;
            border-radius: 15px 15px 15px 15px;
        }
    </style>
</head>

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

        <div class="row " style="margin: 10; ">
            <div class="col-md-3">
                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px;;  height:300px; background-color:#E9F6F6"
                    class="bg3">
                    <table class="table bg2" style="margin-top: 30; padding:30">
                        <tr>
                            <th>ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>{{ $user->type }}</td>
                        </tr>
                    </table>
                </div>
                <div class="heading">User Information</div>
            </div>

            <div class="col-md-2">
                <div style="border: 2px solid teal; border-radius:8%; padding-top:30px; height:300px; background-color:#E9F6F6"
                    align="center" class="bg3">
                    <div id="chart_div"></div>
                    <div class="heading">Total Outcome</div>
                </div>
            </div>

            <div class="col-md-2">
                <div style="border: 2px solid teal; border-radius:8%; height:300px; padding-top:30px; background-color:#E9F6F6"
                    align="center" class="bg3">
                    <div id="chart_div2"></div>
                    <div class="heading">Average Score</div>
                </div>
            </div>

            <div class="col-md-5">
                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px; height:300px; padding-top:30px; padding-bottom:30px; background-color:#E9F6F6"
                    class="bg3">
                    <canvas id="myChart" height="100%"></canvas>
                    <div class="heading">Average Score (Cycle-Wise)</div>
                </div>
            </div>
        </div>


        <div class="row" style="margin: 10; ">
            {{-- <div class="col-md-8" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin: 40px;">
                        <table id="usertable" class="display">
                            <thead>
                                <tr>
                                    <th>Project ID</th>
                                    <th>Cycle</th>
                                    <th>Title</th>

                                    <th>Reviewer-1</th>
                                    <th>Reviewer-2</th>
                                    <th>Status</th>
                                    <!-- <th>Action</th> -->
                                    <!-- Add more columns as needed -->
                                </tr>
                            </thead>
                        </table>
                        @if (Auth::user()->type === 'LPI' || Auth::user()->type === 'LPI+Reviewer')

                            <div class="heading">My Projects</div>
                        @else
                            <div class="heading">LPIs Projects</div>
                        @endif
                    </div>
                </div>
            </div> --}}


            <div class="col-md-12" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin-top: 20px;">

                        <ul>
                            @if (isset($announcement))
                                @foreach ($announcement as $announcements)
                                    <li class="d-flex no-block card-body border-top" style="margin:2px;">
                                        <div>
                                            <h5 class="text-muted">{{ $announcements->subject }}</h5>
                                            <span class="text-muted">{{ $announcements->content }}</span>
                                            <a href="{{ route('announcementDetail', $announcements->id) }}">Read
                                                more...</a>
                                        </div>
                                        <div class="ml-auto"
                                            style="border: 1px solid teal;  padding:10; border-radius: 15px; background-color:#E9F6F6">
                                            <div class="tetx-center">
                                                <h5 class="text-muted m-b-0" style="text-align:center">
                                                    @php echo date('d', strtotime($announcements->duedate)) @endphp</h5>
                                                <span class="text-muted font-16">@php echo date('F', strtotime($announcements->duedate)) @endphp</span>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                        <div class="heading">
                            Announcements
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </body>


    <script type="text/javascript">
        $(document).ready(function() {
            var url = "{{ route('ajaxList', ['id' => ':userId']) }}";
            url = url.replace(':userId', '{{ $user->id }}');
            console.log(url);
            $('#usertable').DataTable({
                processing: true,
                serverSide: true,

                ajax: url,
                //  ajax: url,
                columns: [{
                        data: 'old_project_id',
                        name: 'old_project_id'
                    },
                    {
                        data: 'cycle_title',
                        name: 'Cycle_title'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },



                    {
                        data: 'total_1',
                        name: 'Reviewer-1'
                    },

                    {
                        data: 'total_2',
                        name: 'Reviewer2'
                    },
                    {
                        data: 'status',
                        name: 'Status'
                    },
                    // {
                    //   data: 'action',
                    //   name: 'action',
                    //   orderable: false,
                    //   searchable: false
                    // },
                ]
            });
        });


        const labels = @json($labels);
        const score = @json($data);

        const data = {
            labels: labels,
            datasets: [{
                label: 'Average Score',
                backgroundColor: 'teal',
                borderColor: 'teal',
                data: score,
            }]
        };

        const config = {
            type: 'bar',
            data: data,

        };
        // Set the height of the chart
        const chartContainer = document.getElementById('myChart').getContext('2d');
        chartContainer.canvas.height = 50; // Set the desired height
        chartContainer.canvas.width = "100%";
        const myChart = new Chart(
            chartContainer,
            config
        );



        google.charts.load('current', {
            'packages': ['gauge']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Score', <?php echo $sumOfoutcome; ?>],
            ]);

            var options = {
                width: "100%",
                height: "100%",
                redFrom: "{{ $guage->redfrom }}",
                redTo: "{{ $guage->redto }}",
                yellowFrom: "{{ $guage->yellowfrom }}",
                yellowTo: "{{ $guage->yellowto }}",
                greenFrom: "{{ $guage->greenfrom }}",
                greenTo: "{{ $guage->greento }}",
                minorTicks: 10,
                max: "{{ $guage->greento }}"
            };


            var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
            chart.draw(data, options);



            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Score', <?php echo $sumOfAvgScores; ?>],
            ]);
            var chart = new google.visualization.Gauge(document.getElementById('chart_div2'));
            chart.draw(data, options);


        }
    </script>
@endsection
