<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown</title>
    <style>
        .dropdown-container {
            display: flex;
            align-items: center;
        }
        .dropdown {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <form id="myForm" method="POST" action="{{route('printed')}}">
        @csrf
        <div id="dropdown-container">
            <div>
                <select name="reviewerA[]" class="dropdown"> <!-- Change the name to an array format -->
                    <option value="" disabled selected>Assign Reviewer</option>
                    @foreach($list as $row)
                        <?php $id=$row['id']; ?>
                        <option value="{{$id}}">{{$row['email']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="button" onclick="addDropdown()">+</button> <!-- Change the button type to "button" to prevent form submission -->
        <br>

        <input type="submit" value="Submit" />
    </form>
    <script>
        function addDropdown() {
            var container = document.getElementById("dropdown-container");

            var newDropdownContainer = document.createElement("div");
            newDropdownContainer.className = "dropdown-container";

            var newDropdown = document.createElement("select");
            newDropdown.name = "reviewerA[]"; // Change the name to an array format
            newDropdown.className = "dropdown";

            var defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            defaultOption.textContent = "Assign Reviewer";
            newDropdown.appendChild(defaultOption);

            @foreach($list as $row)
                var option = document.createElement("option");
                option.value = "{{$row['id']}}";
                option.textContent = "{{$row['email']}}";
                newDropdown.appendChild(option);
            @endforeach

            var removeButton = document.createElement("button");
            removeButton.textContent = "x";
            removeButton.onclick = function() {
                container.removeChild(newDropdownContainer);
            };

            newDropdownContainer.appendChild(newDropdown);
            newDropdownContainer.appendChild(removeButton);

            container.appendChild(newDropdownContainer);
            container.appendChild(document.createTextNode(' ')); // Add space between dropdown containers
        }

        var submitButton = document.getElementById("submit");
        submitButton.addEventListener("click", function(event) {
            event.preventDefault(); 
            document.getElementById("myForm").submit(); 
        });
    </script>
</body>
</html>
