<head>
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

        .icon-link {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            color: teal;
            margin: 10px;
            margin-right: 30px;
        }

        .icon-link:hover {
            text-decoration: none;
            color: teal;
        }

        .icon-link .icon-text {
            display: block;
            font-size: 0.2in;
            margin-top: 5px;
        }
    </style>
</head>

@include('components.announcementSideBar', ['case' => '4'])
@include('components.navbar')

<body class="body">
    <br>
    <br>
    <br>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <div class="row" style="margin-top: 20; margin-left:20; padding-left:40">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                        class="bg3">
                        <div style=" margin: 40px;">

                            <div>
                                <h3> <a href="{{ url('listFilesAndFolders') }}">Home:</a>
                                    {{ $directory }}</h3>
                                <ul>
                                    @foreach ($folders as $folder)

                                   {{-- {{dd($directory.$folder);}} --}}
                                        <li class="folder">
                                            📁        <a href="{{ url('listFilesAndFolders?dir=' . urlencode($directory .'/'. $folder)) }}">
                                                {{ basename($folder) }}
                                            </a>
                                        </li>
                                    @endforeach

                                    @foreach ($files as $file)

                                    <li class="file">
                                        📄 {{ basename($file) }}
                                        <!-- Add download link -->
                                        <a href="{{ url('downloadFile?file=' . urlencode($directory .'/'. $folder.'/'.$file))}}" class="download-link">Download</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>


                            <div class="heading">
                                RTS Baclups
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>


</body>
