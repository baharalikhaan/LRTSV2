<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
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
</head>


<body class="body">

    @include('components.projectSideBar')
    @include('components.navbar')

    <div style="padding-left:200">
        <form method="POST" action="{{route('bulk')}}">
            @csrf
            <table>
                <thead style="text-align:center;color:teal;font-weight:bold;">
                    <tr>
                        <th>Id</th>
                        <th>Project Title</th>
                        <th>Status</th>
                        <th>Reviewer A</th>
                        <th>Reviewer B</th>
                    </tr>
                </thead>
                <tbody style="text-align:left;">
                    @foreach($projects as $project)
                    <tr>
                        <td> {{$project['id']}} </td>
                        <td> {{$project['title']}} </td>
                        <td> {{$project['status']}} </td>
                        <td id="reference"> <select name={{$project['id']."A"}}>
                                <option disabled selected>Assign Reviewer</option>
                                @foreach($reviewerByPillar as $pillarId => $reviewers)
                                @if($pillarId == 1)
                                <optgroup label="Energy and Environment">
                                    @elseif($pillarId == 2)
                                <optgroup label="Health and Biomedical Sciences">
                                    @elseif($pillarId == 3)
                                <optgroup label="ICT">
                                    @elseif($pillarId == 4)
                                <optgroup label="Social Sciences and Humanities">
                                    @else
                                <optgroup label="Unknown Pillar">
                                    @endif

                                    @foreach($reviewers as $reviewer)
                                    <option value={{$reviewer->id}}>{{$reviewer->name}}</option>
                                    @endforeach

                                </optgroup>
                                @endforeach
                            </select></td>
                        <td> <select name={{$project['id']."B"}}>
                                <option value='' disabled selected>Assign Reviewer</option>
                                @foreach($reviewerByPillar as $pillarId => $reviewers)
                                @if($pillarId == 1)
                                <optgroup label="Energy and Environment">
                                    @elseif($pillarId == 2)
                                <optgroup label="Health and Biomedical Sciences">
                                    @elseif($pillarId == 3)
                                <optgroup label="ICT">
                                    @elseif($pillarId == 4)
                                <optgroup label="Social Sciences and Humanities">
                                    @else
                                <optgroup label="Unknown Pillar">
                                    @endif

                                    @foreach($reviewers as $reviewer)
                                    <option value={{$reviewer->id}}>{{$reviewer->name}}</option>
                                    @endforeach

                                </optgroup>
                                @endforeach
                            </select></td>
                        <?php $p_id = $project['id'] ?>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            <br>
            <div id="center">
                <button type="submit" class="btn btn-primary" id="button">
                    Submit
                </button>
            </div>
        </form>
    </div>
</body>