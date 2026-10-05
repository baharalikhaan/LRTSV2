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


    <style>
        .text-teal {
            color: #008080;
        }

        .btn-teal {
            background-color: #008080;
            color: white;
            border-color: #008080;
        }

        .btn-teal:hover {
            background-color: #006f6f;
            border-color: #006f6f;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25;
            border-radius: 0.2rem;
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
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                        class="bg3">
                        <div style=" margin: 10px;  margin-bottom:30; ">
                            <br>




                            {{-- @if (session('EmailNotFound'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('EmailNotFound') }}
                                </div>
                            @endif --}}
                            @if (isset($message))
                                <div class="message" align="center">
                                    {{ $message }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('verifyUsersPost') }}" id="box-form"
                                enctype="multipart/form-data">
                                @csrf
                                <tag class="error">
                                    <?php if ($errors->any()) : ?>
                                    <?php echo $error; ?>
                                    <?php endif; ?>
                                </tag>

                                <input type="hidden" class="form-control" id="id" name="id" value="2">
                                <div class="container">

                                    <div class="form-group row">
                                        <label class="col-sm-5 col-form-label">Conf Tool Excel File</label>
                                        <div class="col-sm-7">
                                            <input type="file" class="form-control" name="excel">
                                        </div>
                                    </div>


                                </div>




                                <div class="row" style="margin-top: 10; padding-left:10">
                                    <div class="col-md-6" style="margin-top: 10;">
                                    </div>

                                    <div class="col-md-6" style="margin-top: 10;">
                                    </div>


                                </div>


                                <div class="container">
                                    <button type="submit"
                                        style="float:right; background-color: teal; border-color: teal; color: white;"
                                        class="btn btn-primary"> Verify
                                    </button>
                                </div>
                                <br>
                                <br>
                            </form>


                            @if (session('file_response'))
                                @php
                                    $response = session('file_response');
                                    $emailsNotFound = json_decode($response['EmailNotFound'], true);
                                    $emailsNeedUpdated = json_decode($response['EmailFound'], true);
                                @endphp

                                <div class="row">
                                    <!-- Left Column: Emails Not Found -->
                                    <div class="col-md-6">
                                        <div>
                                            <h5 class="text-teal">Emails Not Found </h5>
                                            @if (count($emailsNotFound) > 0)
                                                <ul class="list-group" id="emailsNotFoundList">
                                                    @foreach ($emailsNotFound as $email)
                                                        <li class="list-group-item">{{ $email }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>No emails were found in the database.</p>
                                            @endif

                                        </div>
                                        <br>
                                        <button type="button" class="btn btn-teal btn-sm"
                                            onclick="exportToExcel('emailsNotFoundList', 'emails_not_found.xlsx')">
                                            Export
                                        </button>

                                    </div>

                                    <!-- Right Column: Emails Need Update -->
                                    <div class="col-md-6">
                                        <div>
                                            <h5 class="text-teal">Emails Need Update </h5>
                                            @if (count($emailsNeedUpdated) > 0)
                                                <ul class="list-group" id="emailsNeedUpdatedList">
                                                    @foreach ($emailsNeedUpdated as $email)
                                                        <li class="list-group-item">{{ $email }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>All emails are up to date.</p>
                                            @endif
                                            <br>
                                            <button type="button" class="btn btn-teal btn-sm "
                                                onclick="exportToExcel('emailsNeedUpdatedList', 'emails_need_updated.xlsx')">
                                                Export
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            @endif


                            <div class="heading">
                                User Verification
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
<script>
    function exportToExcel(listId, fileName) {
        // Get the list of emails (ul with list items)
        var list = document.getElementById(listId);

        // Convert the list items into an array of arrays
        var data = [];
        var items = list.getElementsByTagName('li');

        for (var i = 0; i < items.length; i++) {
            data.push([items[i].innerText]); // Each email goes into a new row
        }

        // Create a worksheet
        var ws = XLSX.utils.aoa_to_sheet(data);

        // Create a workbook and add the worksheet to it
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Emails');

        // Export the workbook to Excel
        XLSX.writeFile(wb, fileName);
    }
</script>
