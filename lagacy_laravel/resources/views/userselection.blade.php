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

        body p {

            /* Set margin to 0 to remove default top and bottom margins */
            margin-bottom: 3;
            /* Add a bottom margin to create space between paragraphs */
        }
    </style>
</head>




<body class="body">
    <br>




    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="row" style="margin-top: 20; margin-left:20; padding-left:40">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                        <div style=" margin: 40px;">

                            <div align="center">

                                <p><b>You have multiple roles. Kindly selection one of them.</b></p>




                                <form action="{{ route('userselection') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label for="role">Select Role:</label>
                                        <select id="role" name="role" class="form-control">
                                            @foreach ($roles as $role)
                                                <option value="{{ $role }}">{{ $role }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>



                            </div>
                            <br>
                            <br>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
        </div>
    </div>

</body>
