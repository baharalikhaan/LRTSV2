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
    </style>
</head>

@include('components.announcementSideBar', ['case' => '4'])
@include('components.navbar')

<body class="body">
    <br>
    <br>
    <br>
    <div class="row">

        <div class="col-md-3">


        </div>
        <div class="col-md-6">

            <div class="row" style="margin-top: 10; padding-left:10">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6" class="bg3">
                        <div style=" margin: 10px;  margin-bottom:30; ">


                            <br>

                            @if (session('successsmtp'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('successsmtp') }}
                                </div>
                            @endif
                            @if (isset($message))
                                <div class="message" align="center">
                                    {{ $message }}
                                </div>
                            @endif
                            <form method="POST" action="{{ route('savesmtpSettings') }}">
                                @csrf
                                <tag class="error">
                                    <?php if ($errors->any()) : ?>
                                    <?php echo $error; ?>
                                    <?php endif; ?>
                                </tag>

                                <input type="hidden" class="form-control" id="id" name="id" value="2">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="redFrom">Host</label>
                                            <input type="text" class="form-control" id="host" name="host"
                                                value="{{ $settings->host }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label for="smtp_auth">SMTP Authentication</label>
                                            <select class="form-control" id="smtp_auth" name="smtp_auth">
                                                <option value="0"
                                                    {{ $settings->smtp_auth == 0 ? 'selected' : '' }}>No</option>
                                                <option value="1"
                                                    {{ $settings->smtp_auth == 1 ? 'selected' : '' }}>Yes</option>
                                            </select>
                                        </div>


                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="smtp_secure">SMTP Security</label>
                                            <input type="text" class="form-control" id="smtp_secure"
                                                name="smtp_secure" value="{{ $settings->smtp_secure }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="port">Port</label>
                                            <input type="text" class="form-control" id="port" name="port"
                                                value="{{ $settings->port }}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="username">User Name</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="{{ $settings->username }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="password">Password</label>
                                            <input type="text" class="form-control" id="password" name="password"
                                                value="{{ $settings->password }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="setfrom_name">Set From Name</label>
                                            <input type="text" class="form-control" id="setfrom_name" name="setfrom_name"
                                                value="{{ $settings->setfrom_name }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="setfrom_email">Set From Email</label>
                                            <input type="text" class="form-control" id="setfrom_email" name="setfrom_email"
                                                value="{{ $settings->setfrom_email }}">
                                        </div>
                                    </div>

                                    <br>
                                    <hr>
                                </div>

                                <div class="container">
                                    <button type="submit"
                                        style="float:right; background-color: teal; border-color: teal; color: white;"
                                        class="btn btn-primary">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                                <br>
                            </form>

                            <div class="heading">
                              SMTP Settings
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">


        </div>



        <br>
</body>

