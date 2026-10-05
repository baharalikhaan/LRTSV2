<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="resources/css/dynamic.css"></script>
<html lang="en">
<head>
    <!-- Your head content here -->
</head>
<body>
    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
    <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Outcomes Continued</legend>
        <form method="POST" action="{{route('projectOutcome3')}}">
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
                    <td><input type="button" onclick="addFunction('masters','masters')" value="+" /></td>
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
                    <td><input type="button" onclick="addResearcher('researcherTable')" value="+" /></td>
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
    </fieldset>

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
</body>
</html>
