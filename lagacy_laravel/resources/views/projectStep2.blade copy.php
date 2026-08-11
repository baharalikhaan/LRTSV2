<link href="{{ asset('css/step2.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    #non {
        color: grey;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>

<body>


    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;width:70%">
        <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Registering New Project in RTS</legend>
        <div align="center" id="icons">
            <i class="fa fa-circle-info" id='non'></i> &emsp;&emsp;&emsp;
            <i class="fa fa-fw fa-link" id="act"></i>&emsp;&emsp;&emsp;
            <i class="fa fa-fw fa-list-check" id="non"></i>
        </div>
        <br>

        <form method="POST" action="{{route('createProjectStep2')}}" id="box-form">
            @csrf







            <div>
                <div id="header" class="w3-padding w3-bar-block w3-large">
                    <h5 class="h5" align="center">Project Affiliation</h5>
                </div>
                <div class="input" align="center">
                    <br>
                    <table style="width:100%">
                        <th align="left">
                            <i class="fa-solid fa-building-columns"></i>&emsp;
                            <label style="font-weight:bold;">College affiliation of the project</label>
                            <br>
                        </th>
                        <th align="left"><i class="fa fa-sitemap"></i>&emsp;<label>Pillars affiliation of the project</label><br>
                        </th>
                        <br>
                        <br>
                        <tr>
                            <br>
                            <br>

                            <td>

                                <ul>
                                    @foreach(session('tags') as $tag)
                                    <li>{{ $tag }}</li>
                                    @endforeach
                                </ul>

                            </td>
                            <td>


                                <ul>
                                    @foreach(session('pillars') as $pillar)
                                    <li>{{ $pillar }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                </div>
                <br>
                </table>
                <button id="next" type="submit">Next</button>
            </div>
            <br>
            </div>
        </form>

</body>