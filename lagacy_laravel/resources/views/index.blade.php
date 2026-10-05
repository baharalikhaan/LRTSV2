<head>
    <title>Folders / Files</title>
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


                    <h1>File Listing</h1>
                    <ul>
                        @foreach ($folders as $folder)
                            <li>
                                <a href="{{ route('folders.show', $folder) }}">
                                    <i class="folder-icon"></i> {{ $folder }}
                                </a>
                            </li>
                        @endforeach
                        @foreach ($files as $file)
                            <li>
                                <a href="{{ route('files.display', $file) }}">
                                    <i class="file-icon"></i> {{ $file }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="heading">
                        Cycles
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
