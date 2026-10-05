<body class="body">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Register Project</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')

        <br>
        <br>




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

            .footer1 {
                position: absolute;
                bottom: 40;
                left: 30;
                font-size: 14px;
                font-style: italic;
                color: red;
            }

            .footer2 {
                position: absolute;
                bottom: 20;
                left: 30;
                font-size: 14px;
                font-style: italic;
                color: red;
            }

            .footer3 {
                position: absolute;
                bottom: 1;
                left: 30;
                font-size: 14px;
                font-style: italic;
                color: red;
            }


            .btn-teal {
                color: #fff;
                background-color: #008080;
                border-color: #008080;
                float: "rght";
            }

            .btn-teal:hover {
                color: #fff;
                background-color: #005959;
                border-color: #005959;
            }

            .teal-header th:nth-child(2),
            .teal-header th:nth-child(4),
            .teal-header th:nth-child(8) {
                width: 150px;
            }

            .teal-header th {
                background-color: #008080;
                font-size: 14px;
                color: #fff;
                border: none;
            }

            .rounded-table {
                padding: 20px;
                border-radius: 20px;
                overflow: hidden;
            }


            .rounded-table {
                border-radius: 10px;
                overflow: hidden;
            }

            .teal-header {
                background-color: teal;
                color: white;
                text-align: center;
            }

            .table-display td {
                padding: 8px;
                vertical-align: middle;
            }

            .input-sm {
                width: 200px;
                text-align: center;
            }

            .rounded-table {
                border-radius: 10px;
                overflow: hidden;
                font-size: 14px;
                /* Smaller font size */
            }

            .teal-header {
                background-color: teal;
                color: white;
                text-align: center;
                font-size: 14px;
            }

            .table-display td {
                padding: 6px;
                vertical-align: middle;
                font-size: 13px;
            }

            .input-sm {
                width: 100px;
                text-align: center;
                font-size: 13px;
                padding: 2px;
            }

            select.input-sm {
                width: 100px;
                font-size: 13px;

                height: 20px;
            }
        </style>



        <form method="POST" action="{{ route('createProjectStep3') }}" id="box-form">
            @csrf
            <div class="row" style="margin:0; ">
                <div class="col-md-12">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                        <div style=" margin: 15px;margin-top: 40px;">
                            <div class="row">
                                <div class="col-md-8">
                                    <div style="border: 2px solid teal; ">
                                        @if (session('project'))
                                            @php
                                                $filePath = storage_path(
                                                    'app/uploads/lpi_project_proposals/' .
                                                        trim(session('cycle')) .
                                                        '/' .
                                                        str_replace('/', '', session('project')['old_project_id']) .
                                                        '.pdf',
                                                );

                                            @endphp

                                            @if (file_exists($filePath))
                                                <iframe
                                                    src="{{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ session('cycle') }}/{{ str_replace('/', '', session('project')['old_project_id']) }}.pdf"
                                                    id="pdfViewer"
                                                    style="height:1200px; width:100%; padding:10px;"></iframe>
                                            @else
                                                <div class="alert alert-warning" role="alert">
                                                    Proposal file does not exist.
                                                    {{ URL::to('/') }}/serveFile2?file=lpi_project_proposals/{{ session('cycle') }}/{{ str_replace('/', '', session('project')['old_project_id']) }}
                                                </div>
                                            @endif
                                        @else
                                            <p>Proposal not available</p>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="col-md-12">
                                        <table class="table table-sm rounded-table">
                                            <thead class="teal-header">
                                                <tr>
                                                    <th scope="col">Publications </th>
                                                    <th scope="col" class="text-center">Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1. Articles in Thomson Reuters Web of Science-SCI</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp;&emsp;<i>a. Quartile-1 journal</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="Q1"></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp;&emsp;<i>b. Quartile-2 journal</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="Q2"></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp;&emsp;<i>c. Quartile-3 journal</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="Q3"></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp;&emsp;<i>d. Quartile-4 journal</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="Q4"></td>
                                                </tr>
                                                <tr>
                                                    <td>2. Articles in indexed international conferences</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="conf"></td>
                                                </tr>
                                                <tr>
                                                    <td>3. Books to be published</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="book_publish"></td>
                                                </tr>
                                                <tr>
                                                    <td>4. Books to be edited</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="book_edit"></td>
                                                </tr>
                                                <tr>
                                                    <td>5. Book chapters to be published</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="chap"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-md-12">
                                        <table class="table table-sm rounded-table">
                                            <thead class="teal-header">
                                                <tr>
                                                    <th scope="col">Intellectual property </th>
                                                    <th scope="col" class="text-center">Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1. Intellectual Property Disclosure forms to be submitted</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="form"></td>
                                                </tr>
                                                <tr>
                                                    <td>2. Provisional Patents to be filed</td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="patent"></td>
                                                </tr>
                                                <tr>
                                                    <td>3. Open source software developed</td>
                                                    <td class="text-center">
                                                        <select class="form-control input-sm" name="sw">

                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>4. Creation of a startup</td>
                                                    <td class="text-center">
                                                        <select class="form-control input-sm" name="start_up">

                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>5. Does this project require ethical approval?</td>
                                                    <td class="text-center">
                                                        <select class="form-control input-sm" name="ethical">

                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <?php if ($errors->any()) : ?>
                                        <span class="error">Kindly fill all the fields</span>
                                        <?php endif; ?>


                                    </div>

                                    <div class="col-md-12">
                                        <table class="table table-sm rounded-table">
                                            <thead class="teal-header">
                                                <tr>
                                                    <th scope="col">Student engagement</th>
                                                    <th scope="col" class="text-center">Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1. Inclusion of Students</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp; <i>a. No. of Undergraduate Students</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="UG"></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp; <i>b. No. of Masters Students</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="masters"></td>
                                                </tr>
                                                <tr>
                                                    <td>&emsp; <i>c. No. of Ph.D Students</i></td>
                                                    <td class="text-center"><input class="form-control input-sm"
                                                            value="0" type="text" name="phd"></td>
                                                </tr>
                                                <tr>
                                                    <td>2. Includes Cross College Participation?</td>
                                                    <td class="text-center">
                                                        <select class="form-control input-sm" name="crossClg"
                                                            id="crossClg">

                                                            <option value="1">Yes</option>
                                                            <option value="0">No</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <br>
                                        <div class="row" style="float:right; margin-right:40px">
                                            {{-- <a  href="{{ url()->previous() }}" class="btn btn-teal">Back</a> &nbsp; --}}
                                            <button id="next" class="btn btn-teal" type="submit">Save</button>
                                        </div>

                                        <p class="footer2">* Kindly fill all the fields</p>
                                        <p class="footer3">* All textboxes need numeric values. From 0-99</p>

                                    </div>
                                </div>
                            </div>
                            <div class="heading">
                                Scholarly commitments
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>


        <!-- Borderless Modal -->
        <div class="modal fade borderless-modal" id="success" tabindex="-1" role="dialog"
            aria-labelledby="borderlessModalLabel" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="false">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header" style="background-color: teal; text-align: center;">
                        <h5 class="modal-title text-white" id="modalTitle">Project Registration</h5>

                    </div>
                    <!-- Modal Body -->
                    <div class="modal-body">

                        <p>Project has been registered successfully</p>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">


                        <a href="{{ url('/home') }}" class="btn btn-sm text-white" style="background-color: teal;">Go to
                            Home</a>

                    </div>

                </div>
            </div>
        </div>



        </div>


    </body>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if ("{{ session('success123') }}" === "1") {
                var myModal = new bootstrap.Modal(document.getElementById('success'));
                myModal.show();
            }
        });
    </script>


@endsection
