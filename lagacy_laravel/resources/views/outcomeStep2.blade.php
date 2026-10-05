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


                    <form method="POST" action="{{ route('projectOutcome3') }}">
                        @csrf
                        <table style="width:90%;">
                            <!-- table content here -->
                            <tr>
                                <td>1.</td>
                                <td>List the student id of Under Graduates Students involved in the Project</td>
                                <td><input type="button" onclick="addFunction('UG','UG')" value="+" /></td>
                                <td>
                                    <table id="UG">
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>2.</td>
                                <td>List the student id of Masters Students involved in the Project</td>
                                <td><input type="button" onclick="addFunction('masters','masters')" value="+" />
                                </td>
                                <td>
                                    <table id="masters">
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>3.</td>
                                <td>List the student id of Ph.D Students involved in the Project</td>
                                <td><input type="button" onclick="addFunction('PhD','PhD')" value="+" /></td>
                                <td>
                                    <table id="PhD">
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>4.</td>
                                <td>List the hired researchers</td>
                                <td><input type="button" onclick="addResearcher('researcherTable')" value="+" />
                                </td>
                                <td>
                                    <table id="researcherTable">
                                    </table>
                                </td>
                            </tr>

                            <!-- Add similar rows for other types (masters, phd, etc.) here -->
                            <tr>
                                <td>5.</td>
                                <td>Is there any Cross College Participation?</td>
                                <td>
                                    <input type="radio" name="colgPart" value="1" checked="true">
                                    <label>Yes</label><br>
                                    <input type="radio" name="colgPart" value="0">
                                    <label>No</label><br>
                                </td>
                            </tr>
                            <tr>
                                <td>6.</td>
                                <td>Research Awards</td>
                                <td>
                                    <input type="radio" name="award" value="1" checked="true">
                                    <label>Yes</label><br>
                                    <input type="radio" name="award" value="0">
                                    <label>No</label><br>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <div style="text-align:center">
                            <button type="submit" class="btn btn-primary" position="center">
                                Submit
                            </button>
                        </div>
                    </form>


                    <div class="heading">
                        Project Outcome Step - 2
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>



<script>
    function addFunction($name, $num) {
        var name = $name;
        var id = $num;
        var table = document.getElementById(id);
        var rowlen = table.rows.length;
        var row = table.insertRow(rowlen);
        row.id = rowlen;
        for (i = 0; i < 4; i++) { // Change to 4 to add two textboxes, a remove button, and a spacer
            var x = row.insertCell(i);
            if (i === 0) {

            } else if (i === 1) {
                x.innerHTML = "<input type='text' name='" + name + "[]' placeholder='Student ID' required>";
            } else if (i === 2) {
                x.innerHTML = "<input type='text' name='" + name + "[]' placeholder='Number of Days' required>";
            } else if (i === 3) {
                x.innerHTML = "<input type='button' onclick='removeRow(" + row.id + ")' value='x'>";
            }
        }
    }

    function removeRow(rowid) {
        document.getElementById(rowid).remove();
    }

    function addResearcher(tableId) {
        var table = document.getElementById(tableId);
        var row = table.insertRow(table.rows.length);

        // Add emp_id input
        var empIdCell = row.insertCell(0);
        var empIdInput = document.createElement("input");
        empIdInput.type = "text";
        empIdInput.name = "emp_id[]";
        empIdInput.placeholder = "emp_id";
        empIdInput.required = true;
        empIdCell.appendChild(empIdInput);

        // Add dropdown
        var dropdownCell = row.insertCell(1);
        var dropdown = document.createElement("select");
        dropdown.name = "role[]";
        dropdown.required = true;

        var roles = ["RA", "GA", "Student"];
        for (var i = 0; i < roles.length; i++) {
            var option = document.createElement("option");
            option.value = roles[i];
            option.text = roles[i];
            dropdown.appendChild(option);
        }
        dropdownCell.appendChild(dropdown);

        // Add remove button
        var removeCell = row.insertCell(2);
        var removeButton = document.createElement("input");
        removeButton.type = "button";
        removeButton.value = "x";
        removeButton.onclick = function() {
            table.deleteRow(row.rowIndex);
        };
        removeCell.appendChild(removeButton);
    }
</script>
@endsection
