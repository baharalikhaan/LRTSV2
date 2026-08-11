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

@include('components.settingSideBar')
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
                            <a href="{{ route('user') }}" class="icon-link">
                                <i class="fa fa-user fa-3x"></i>
                                <span class="icon-text">System Users</span>
                            </a>
                            <a href="{{ route('cycle') }}" class="icon-link">
                                <i class="fa fa-recycle fa-3x"></i>
                                <span class="icon-text">Project Cycles</span>
                            </a>
                            <a href="{{ route('sendEmailAdmin') }}" class="icon-link">
                                <i class="fa fa-envelope-open fa-3x"></i>
                                <span class="icon-text">Send General Emails</span>
                            </a>
                            <a href="{{ route('announcementSetting') }}" class="icon-link">
                                <i class="fa fa-bullhorn fa-3x"></i>
                                <span class="icon-text">Announcements</span>
                            </a>
                            <a href="{{ route('uploadProgressAdmin') }}" class="icon-link">
                                <i class="fa fa-line-chart fa-3x"></i>
                                <span class="icon-text">Upload Report</span>
                            </a>
                            <a href="{{ route('reviewerAgrementAdmin') }}" class="icon-link">
                                <i class="fa fa-handshake-o fa-3x"></i>
                                <span class="icon-text">Reviewer's Agreement</span>
                            </a>
                            <a href="{{ route('budgetAPIList') }}" class="icon-link">
                                <i class="fa fa-money fa-3x"></i>
                                <span class="icon-text">Budget API</span>
                            </a>
                            <a href="{{ route('aboutUsSettings') }}" class="icon-link">
                                <i class="fa fa-id-card-o fa-3x"></i>
                                <span class="icon-text">Our Team</span>
                            </a>
                            <a href="{{ route('emailSetting') }}" class="icon-link">
                                <i class="fa fa-envelope fa-3x"></i>
                                <span class="icon-text">Email Templates</span>
                            </a>

                            <a href="{{ route('EmailSendingStatus') }}" class="icon-link">
                                <i class="fa fa-list fa-3x"></i>
                                <span class="icon-text">Email Sending Logs</span>
                            </a>

                            <a href="{{ route('file.explorer') }}" class="icon-link">
                                <i class="fa fa-download fa-3x"></i>
                                <span class="icon-text">RTS Downloads</span>
                            </a>

                            <div class="heading">
                                Administration
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>

    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <div class="row" style="margin-top: 20; margin-left:20; padding-left:40">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                        class="bg3">
                        <div style=" margin: 40px;">
                            <a href="{{ route('smtpSettings') }}" class="icon-link">
                                <i class="fa fa-sliders fa-3x"></i>
                                <span class="icon-text">SMTP Settings</span>
                            </a>
                            <a href="{{ route('guageSetting') }}" class="icon-link">
                                <i class="fa fa-dashboard fa-3x"></i>
                                <span class="icon-text">Gauge Settings</span>
                            </a>

                            <a href="{{ route('listFilesAndFolders') }}" class="icon-link">
                                <i class="fa fa-hdd-o fa-3x"></i>
                                <span class="icon-text">RTS Backups</span>
                            </a>


                            <div class="heading">
                                System Settings
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>
</body>
