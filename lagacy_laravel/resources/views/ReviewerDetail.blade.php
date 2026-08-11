<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <!DOCTYPE html>
    <html lang="en">


    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')
        <div class="row" style="margin: 20;">
            <div class="col-md-3">
                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px;;  height:300px; background-color:#E9F6F6"
                    class="bg3">
                    <table class="table" style="margin: 0; margin-top: 50; padding:10">
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
                <div class="heading">Reviewers' Information</div>
            </div>

            <div class="col-md-2">
                <div style="border: 2px solid teal; border-radius:8%; padding-top:70px; height:300px; background-color:#E9F6F6"
                    align="center" class="bg3">
                    <h5>Cycles Entertained </h5>
                    <h2 style="color:teal">[{{ $cycles }}]</h2>
                    <br>
                    <h5>Projects Reviewed</h5>
                    <h2 style="color:teal">[{{ $projects }}]</h2>
                    <div class="heading">Cycles/Projects</div>
                </div>
            </div>

            <div class="col-md-2">
                <div style="border: 2px solid teal; border-radius:8%; height:300px; padding-top:30px; background-color:#E9F6F6"
                    align="center" class="bg3">
                    <div id="chart_div"></div>
                    <div class="heading">Average Rating (Overall)</div>
                </div>
            </div>

            <div class="col-md-5">
                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px; height:300px; padding-top:30px; padding-bottom:30px; background-color:#E9F6F6"
                    class="bg3">
                    <canvas id="myChart" height="100%"></canvas>
                    <div class="heading">Average Rating (Cycle-Wise)</div>
                </div>
            </div>
        </div>

        <div class="row" style="margin: 20; ">

            <div class="col-md-12">
                <a href="{{ route('reviewerEvaluation', ['u_id' => $user->id]) }}" class="btn btn-sm btn-teal"
                    style="margin:0; margin-right:30; float:right"> Print Report </a>
            </div>
        </div>
        <!-- <div class="col-md-12">
                  <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px;;    background-color:#E9F6F6">
                    <a href="{{ route('reviewerEvaluation', ['u_id' => $user->id]) }}" class="btn btn-sm btn-teal" style="margin:20"> Print </a>
                    @isset($data2[0])
        <tr>
                                  <th>Scope Of Supply / Service</th>
                                  <td>{{ $data2[0]->scope_of_supply }}</td>
                                  <th>Mode Of Selection</th>
                                  <td>{{ $data2[0]->mode_of_selection }}</td>
                                </tr>
                                <tr>
                                  <th>Basis Of Approval</th>
                                  <td>{{ $data2[0]->basis_of_approval }}</td>
                                  <th>Type & Extent Of Control</th>
                                  <td>{{ $data2[0]->type_extent_of_control }}</td>
                                </tr>
                                <tr>
                                  <th>Designation Of Approver</th>
                                  <td>{{ $data2[0]->designation_of_approver }}</td>
                                  <th></th>
                                  <td></td>
                                </tr>
    @else
        <tr>
                                  <td colspan="4">No data available</td>
                                </tr>
    @endisset
                  </div>
                  <div class="heading">Internal Provider List</div>
                </div> -->

        <div class="row" style="margin: 20;  ">
            <div class="col-md-6">

                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px;;    background-color:#E9F6F6"
                    class="bg3">
                    <table class="table table bordered" style="  margin-top: 50; padding:30">
                        <tr>
                            <th>Cycle</th>
                            <th>Mode Of Selection</th>
                            <th>Score</th>

                        </tr>
                        @foreach ($data2 as $dt)
                            <tr>
                                <th>{{ $dt->cycle_title }}</th>
                                <td>{{ $dt->mode_of_selection }}</td>
                                <td>{{ $dt->conflict + $dt->comprehensiveness + $dt->responsiveness + $dt->no_reviewers + $dt->behaviour }}
                                </td>


                            </tr>
                        @endforeach


                    </table>
                </div>
                <div class="heading">Re-Evaluation</div>

            </div>


            <div class="col-md-6" style="margin-top: 10;">
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

        <div class="row" style="margin: 20;  ">
            <div class="col-md-12">
                <div style="border: 2px solid teal; border-radius: 30px 30px 30px 30px;;    background-color:#E9F6F6"
                    class="bg3">
                    <table class="table table bordered" style="  margin-top: 50; padding:30">
                        <tr>
                            <th>Cycle</th>
                            <th>Mode Of Selection</th>
                            <th>Conflict</th>
                            <th>Responsiveness</th>
                            <th>Comprehensiveness</th>
                            <th>No. Of Reviews</th>
                            <th>Behaviour</th>
                        </tr>
                        @foreach ($data2 as $dt)
                            <tr>
                                <th>{{ $dt->cycle_title }}</th>
                                <td>{{ $dt->mode_of_selection }}</td>
                                <td>{{ $dt->conflict }}</td>
                                <td>{{ $dt->responsiveness }}</td>
                                <td>{{ $dt->comprehensiveness }}</td>
                                <td>{{ $dt->no_reviewers }}</td>
                                <td>{{ $dt->behaviour }}</td>

                            </tr>
                        @endforeach

                    </table>
                </div>
                <div class="heading">Evaluation</div>
            </div>


        </div>
    </body>


    <script type="text/javascript">
        $(document).ready(function() {});

        const labels = @json($labels);
        const score = @json($data);

        const data = {
            labels: labels,
            datasets: [{
                label: 'Average Rating',
                backgroundColor: 'teal',
                borderColor: 'teal',
                data: score,
            }]
        };

        const config = {
            type: 'bar',
            data: data,

        };
        const chartContainer = document.getElementById('myChart').getContext('2d');
        chartContainer.canvas.height = 50;
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
                ['Score', <?php echo $avg->total; ?>],
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
                minorTicks: 1,
                max: "{{ $guage->greento }}",
            };

            var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
            chart.draw(data, options);

        }
    </script>
@endsection
