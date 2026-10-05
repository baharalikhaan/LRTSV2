<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/css/bootstrap-select.min.css"
        rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

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
            float: right
        }

        .btn-teal:hover {
            color: #fff;
            background-color: #005959;
            /* Darker Teal color on hover */
            border-color: #005959;
            /* Darker Teal color on hover */
        }



        /* Style the custom button */
        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 6px 12px;
            cursor: pointer;
            background-color: #f9f9f9;
        }
    </style>

</head>


@extends('layouts.app')
@section('title', 'Home Page')
@section('content')


    <body class="body">


        <div class="row" style=" ">
            <div class="col-md-8" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin: 40px; margin-bottom:70">



                        <div style="align:center; ">

                            <form action="{{ route('sendEmailAdminSave') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="recipients">Recipient(s):</label>
                                    <select id="exampleSelect" name='recipients[]' class="selectpicker form-control"
                                        multiple data-live-search="true">
                                        <option>All</option>
                                        <option>All LPIs</option>
                                        <option>All Reviewers</option>
                                        {{var_dump($emails)}}
                                        @foreach ($emails as $email)
                                            <option value="{{ $email->email }}">{{ $email->email }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="subject">CC:</label>
                                    <input type="text" class="form-control" id="cc" name="cc">
                                </div>

                                <div class="form-group">
                                    <label for="subject">Subject:</label>
                                    <input type="text" class="form-control" id="subject" name="subject">
                                </div>

                                <div class="form-group">
                                    <label for="body">Message:</label>
                                    <textarea class="form-control" id="body" name="body" rows="15"></textarea>
                                </div>


                                <div class="form-group">
                                    <label for="attachment">Attachment:</label>
                                    <input type="file" class="form-control-file" id="attachment" name="attachment[]"
                                        multiple>
                                    <button type="submit" class="btn btn-teal">Send Email</button>
                                </div>



                            </form>
                        </div>

                        <div class="heading">
                            General Email
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin: 40px; margin-bottom:70">

                        <div align="left">
                            @if (session('successadminemails'))
                                @foreach (json_decode(session('successadminemails'), true) as $key => $value)
                                    {!! $value !!}
                                @endforeach
                                @php
                                    session()->forget('successadminemails');
                                @endphp
                            @endif
                        </div>

                    </div>
                    <div class="heading">
                        Email Sending Status
                    </div>
                </div>
            </div>
    </body>

    </html>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.18/js/bootstrap-select.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Bootstrap Selectpicker
            $('.selectpicker').selectpicker();
        });
    </script>
    </body>

@endsection
