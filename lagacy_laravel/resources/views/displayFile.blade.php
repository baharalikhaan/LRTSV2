<head>
    <title>All Cycles</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS Cycles List</title>

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

        .btn-teal {
            color: #fff;
            background-color: #008080;
            /* Teal color */
            border-color: #008080;
            /* Teal color */
        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            /* Darker Teal color on hover */
            border-color: #005959;
            /* Darker Teal color on hover */
        }
    </style>

</head>

<body class="body">
    @include('components.announcementSideBar', ['case' => '2'])
    @include('components.navbar')
    <div class="row" style="margin: 20; padding-left:40">
        <div class="col-md-12" style="margin-top: 10;">
            <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                <div style=" margin: 40px;">
                    <iframe src="{{ $fileUrl }}" width="100%" height="600px"></iframe>
                    <div class="heading">
                        Cycles
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


