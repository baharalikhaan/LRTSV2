<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS - Budget balances</title>

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

    <body class="body">


        <head>
            <meta charset="UTF-8">
            <title>Register Project</title>
        </head>

        @extends('layouts.app')
        @section('title', 'Home Page')
        @section('content')


            <div class="row" style="margin: 20; padding-left:40">
                <div class="col-md-12" style="margin-top: 10;">
                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                        <div style=" margin: 40px;">


                            <form method="POST" action="{{ route('projectOutcome2') }}">
                                @csrf
                                <input type="text" name='p_id' value="{{ $p_id }}" hidden><br>
                                <tag class="error">
                                    <?php if ($errors->any()) : ?>
                                    <?php echo 'Kindly fill all the fields from 9-13 ' . "\n\n"; ?>
                                    <?php endif; ?>
                                </tag>
                                <table id="table" style="width:90%">
                                    <legend
                                        style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">
                                        1- Scholarly Outcomes</legend>
                                    <colgroup>
                                        <col span="1" style="width: 5%;">
                                        <col span="1" style="width: 70%;">
                                        <col span="1" style="width: 5%; text-align:center">
                                        <col span="1" style="width: 20%;">
                                    </colgroup>
                                    <tr>
                                        <th align="left">#</th>
                                        <th>Outcomes</th>
                                        <th></th>
                                        <th>List</th>
                                    </tr>
                                    <tr>

                                        <td>1.</td>
                                        <td>List the DOI of Published articles in journals listed in Thomson Reuters Web of
                                            Science-SCI (Quartile in Category Q1)</td>
                                        <td><input type="button" onclick="addFunction('q1','q1')" value="+" /></td>
                                        <td>
                                            <table id="q1">
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2.</td>
                                        <td> List the DOI of Published articles in journals listed in Thomson Reuters Web of
                                            Science-SCI (Quartile in Category Q2) </td>
                                        <td><input type="button" onclick="addFunction('q2','q2')" value="+" /></td>
                                        <td>
                                            <table id="q2">
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3.</td>
                                        <td> List the DOI of Published articles in journals listed in Thomson Reuters Web of
                                            Science-SCI (Quartile in Category Q3) </td>
                                        <td><input type="button" onclick="addFunction('q3','q3')" value="+" /></td>
                                        <td>
                                            <table id="q3">
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>4.</td>
                                        <td> List the DOI of Published articles in journals listed in Thomson Reuters Web of
                                            Science-SCI (Quartile in Category Q4) </td>
                                        <td><input type="button" onclick="addFunction('q4','q4')" value="+" /></td>
                                        <td>
                                            <table id="q4">
                                            </table>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>5.</td>
                                        <td> List the DOI of Public articles in indexed international conferences</td>
                                        <td><input type="button" onclick="addFunction('conf','conf')" value="+" />
                                        </td>
                                        <td>
                                            <table id="conf">
                                            </table>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>6.</td>
                                        <td> List the DOI of any Published Books</td>
                                        <td><input type="button" onclick="addFunction('pubBook','pubBook')"
                                                value="+" />
                                        </td>
                                        <td>
                                            <table id="pubBook">
                                            </table>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>7.</td>
                                        <td> List the DOI of any Edited Books (collection)</td>
                                        <td><input type="button" onclick="addFunction('editBook','editBook')"
                                                value="+" />
                                        </td>
                                        <td>
                                            <table id="editBook">
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>8.</td>
                                        <td> List the DOI of any Book Chapters</td>
                                        <td><input type="button" onclick="addFunction('bookChap','bookChap')"
                                                value="+" />
                                        </td>
                                        <td>
                                            <table id="bookChap">
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>9.</td>
                                        <td> Have any Intellectual Property Disclosure form been submitted?</td>
                                        <td></td>
                                        <td>
                                            <select name="IP" id="IP" onchange="showTextbox('IP' , 'IPText')">
                                                <option disabled selected value>-- select an option --</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select><br><br>
                                            <textarea name="IPText" id="IPText" style="display: none;" rows="4" cols="50"
                                                placeholder="Enter details"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>10.</td>
                                        <td> Have any Provisional Patent been filed as an outcome of this project?</td>
                                        <td></td>
                                        <td>
                                            <select name="FP" id="FP" onchange="showTextbox('FP' , 'FPText')">
                                                <option disabled selected value>-- select an option --</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select><br><br>
                                            <textarea name="FPText" id="FPText" style="display: none;" rows="4" cols="50"
                                                placeholder="Enter details"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>11.</td>
                                        <td> Have any Patents been granted from this project?</td>
                                        <td></td>
                                        <td>
                                            <select name="GP" id="GP" onchange="showTextbox('GP' , 'GPText')">
                                                <option disabled selected value>-- select an option --</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select><br><br>
                                            <textarea name="GPText" id="GPText" style="display: none;" rows="4" cols="50"
                                                placeholder="Enter details"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>12.</td>
                                        <td> Have any Open Source Software been developed from thsi project?</td>
                                        <td></td>
                                        <td>
                                            <select name="SW" id="SW" onchange="showTextbox('SW' , 'SWText')">
                                                <option disabled selected value>-- select an option --</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select><br><br>
                                            <textarea name="SWText" id="SWText" style="display: none;" rows="4" cols="50"
                                                placeholder="Enter details"></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>13.</td>
                                        <td> Have any Start-Up been created from this project?</td>
                                        <td></td>
                                        <td>
                                            <select name="SUp" id="SUp"
                                                onchange="showTextbox('SUp' , 'SUpText')">
                                                <option disabled selected value>-- select an option --</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select><br><br>
                                            <textarea name="SUpText" id="SUpText" style="display: none;" rows="4" cols="50"
                                                placeholder="Enter details"></textarea>
                                        </td>
                                    </tr>
                                </table>
                                <br>
                                <div style="text-align:center">

                                    <button type="submit"
                                        style="background-color:  teal;float:right; border-color: teal; color: white; margin-right:20px;"
                                        class="btn">
                                        Next
                                    </button>

                                    {{-- <button type="submit" class="btn btn-primary" position="center">
                                Next
                            </button> --}}
                                </div>
                            </form>


                            <div class="heading">
                                Project Outcome Step - 1
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </body>


        <script>
            function addFunction(arrayName, id) {
                var index = $("#" + arrayName + " tr").length;
                $("#" + arrayName).append("<tr><td><input type='text' placeholder='DOI' name='" + id +
                    "[]' required/></td><td><input type='button'  value='x' onclick='removeCell(\"" + arrayName + "\", " +
                    index + ")'/></td></tr>");
            }

            function removeCell(arrayName, rowIndex) {
                $("#" + arrayName + " tr").eq(rowIndex).remove();
            }

            function showTextbox(dropdownId, textboxId) {
                var dropdown = document.getElementById(dropdownId);
                var textbox = document.getElementById(textboxId);

                if (dropdown.value == "1") {
                    textbox.style.display = "block"; // Show the textbox
                } else {
                    textbox.style.display = "none"; // Hide the textbox
                }
            }
        </script>
@endsection
