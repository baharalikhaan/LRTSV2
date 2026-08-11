<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTS - Budget balances</title>

    <style>
        .tooltip-text {
            display: block;
            /* Ensure the text is displayed on its own line */
            color: dimgray;
            /* Dim gray color */
            font-style: italic;
            /* Italic text */
            font-size: 0.9rem;
            /* Optional: smaller font size to resemble a tooltip */
            margin-top: 5px;
            /* Space between the control and the tooltip text */
        }

        .label-teal {
            background-color: #c23e3e;
            /* Teal color */
            color: white;
            /* Text color */
            padding: 0.05em 0.4em;
            /* Reduced padding for a more oval shape */
            border-radius: 30px;
            /* Oval shape */
            display: inline-block;
            /* Ensure proper spacing */
        }

        .label-teal2 {
            background-color: teal;
            /* Teal color */
            color: white;
            /* Text color */
            padding: 0.05em 0.4em;
            /* Reduced padding for a more oval shape */
            border-radius: 30px;
            /* Oval shape */
            display: inline-block;
            /* Ensure proper spacing */
        }


        .heading {
            position: absolute;
            top: -15;
            left: 35;
            background-color: teal;
            color: white;
            padding: 6px;
            border-radius: 15px 15px 15px 15px;
        }

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

        .container {
            display: flex;
            margin: 0;
        }

        .section {
            text-align: left;
            border-right: 1px solid #ccc;
            padding-left: 20px;
            padding-right: 20px;
        }

        .section4 {
            text-align: left;
            border-left: 1px solid #ccc;
            padding-left: 20px;
            padding-right: 20px;
        }

        .section3 {
            text-align: left;
            border-right: 1px solid #ccc;
            padding: 30px;

        }


        .section2 {
            text-align: left;
            padding: 20px;
        }

        .section:last-child {
            border-right: none;
        }

        .form-check-input:checked {
            background-color: teal;
            border-color: teal;
        }


        .table-rounded {
            border-radius: 15px;
            overflow: hidden;
            /* Ensures rounded corners work properly */
            border-collapse: separate;
            /* Prevents the corners from being squared */
            border-spacing: 0;
            border: 2px teal solid;
            /* Ensures no gaps between cells */
        }

        /* Rounding specific table corners */
        .table-rounded thead tr:first-child th:first-child {
            border-top-left-radius: 15px;
        }

        .table-rounded thead tr:first-child th:last-child {
            border-top-right-radius: 15px;
        }

        .table-rounded tbody tr:last-child td:first-child {
            border-bottom-left-radius: 15px;
        }

        .table-rounded tbody tr:last-child td:last-child {
            border-bottom-right-radius: 15px;

        }
    </style>

</head>


<body class="body">


    <head>
        <meta charset="UTF-8">
        <title>Report Upload</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')


        <div class="row">
            <div class="col-md-12">

                <div class="row" >
                    <div class="col-md-12" style="margin-top: 10;">
                        <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                            <div style=" margin: 5px;">
                                <div class="container">
                                    <div class="section" style="padding-top:25">
                                        <b> {{ $user->name }} </b><br>
                                        {{ $user->email }}
                                    </div>
                                    <div class="section2">
                                        <b> Grant Type: </b>
                                        <span class="label-teal2"> {{ ucfirst($project->grant_type) }}</span><br>
                                        <b> Project ID: </b>
                                        <span>{{ $project->old_project_id }}</span><br>
                                        <b> Project Title: </b>
                                        <span>{{ $project->title }}</span><br>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" >
            <div class="col-md-12" style="margin-top: 10;">
                <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6"
                    class="bg3">
                    <div style=" margin: 40px;">
                        @php
                            $readonly = $project->student_project_draft === 'save' ? 'readonly' : '';
                            $disabled = $project->student_project_draft === 'save' ? 'disabled' : '';
                            $msg =
                                $project->student_project_draft === 'save'
                                    ? 'The Information has been saved, to modify any details, contact administration'
                                    : 'Kindly note that this form can only be submitted once';
                        @endphp

                        <form id="myform" action="{{ route('projectOutcomesstudent') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">

                            <!-- Student Information -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Student Information</label>
                                <div class="col-sm-8">

                                    <br>
                                    <table class="table table-bordered table-rounded">
                                        <thead class="table-light" style="background-color: teal; color: white;">
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Level</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                                <tr>
                                                    <td>{{ $student->student_id }}</td>
                                                    <td>{{ $student->first_name . ' ' . $student->last_name }}</td>
                                                    <td>{{ $student->std_level }}</td>
                                                    <td>
                                                        <input type="hidden" name="students[{{ $loop->index }}][id]"
                                                            value="{{ $student->id }}">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="students[{{ $loop->index }}][category]"
                                                                value="Qatri"
                                                                {{ old("students.$loop->index.category", $student->nationality ?? '') == 'qatri' ? 'checked' : '' }}
                                                                {{ $disabled }} required>
                                                            <label class="form-check-label">Qatari</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio"
                                                                name="students[{{ $loop->index }}][category]"
                                                                value="Non Qatri"
                                                                {{ old("students.$loop->index.category", $student->nationality ?? '') == 'non qatri' ? 'checked' : '' }}
                                                                {{ $disabled }} required>
                                                            <label class="form-check-label">Non Qatari</label>
                                                            <b><span style="color:red;">*</span></b>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <hr>

                            <!-- Publications -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Publications <small
                                        class="tooltip-text">[Author],[Title], [Conference Title],
                                        [City], [Country], [Year],[Month],[Day]</small></label>
                                <div class="col-sm-8" id="publication-container">
                                    <div class="input-group mb-2">
                                        <input type="text" name="publications[]" class="form-control"
                                            value="{{ old('publications', $project->publications) }}" placeholder=""
                                            {{ $readonly }}>
                                    </div>

                                    {{-- @php
                                    $publications = json_decode($project->publications, true) ?? [];
                                @endphp
                                @foreach ($publications as $index => $publication)
                                    @if (!empty($publication))
                                        <div class="input-group mb-2">
                                            <input type="text" name="publications[]" class="form-control"
                                                value="{{ old('publications.' . $index, $publication) }}"
                                                placeholder="Enter publication details" {{ $readonly }}>
                                        </div>
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" name="publications[]" class="form-control"
                                                value="{{ old('publications.' . $index, '') }}"
                                                placeholder="Enter publication details" {{ $readonly }}>
                                        </div>
                                    @endif
                                @endforeach --}}
                                </div>
                            </div>

                            <hr>

                            <!-- Spending -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Budget / Spendings <small class="tooltip-text"> Enter
                                        spending details against the allocated budget</small></label>
                                <div class="col-sm-8">
                                    <small> Allocated Budget: </small>
                                    <input type="number" readonly name="requested_budget_qar" id="requested_budget_qar"
                                        class="form-control"
                                        value="{{ old('requested_budget_qar', (int) str_replace(',', '', $project->requested_budget_qar)) }}">
                                    <br>

                                    <small> <b><span style="color:red;">*</span></b> Spendings:</small> <small
                                        style="color:red" id="overflow"></small>
                                    <input type="number" required name="spending" id="spending"
                                        class="form-control @error('spending') is-invalid @enderror"
                                        value="{{ old('spending', $project->spending) }}" placeholder=""
                                        {{ $readonly }}>
                                    @error('spending')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <br>

                                    <small> Spending Details: </small>
                                    <textarea name="spending_details" class="form-control" {{ $readonly }}>{{ old('spending_detail', $project->spending_detail) }}</textarea>
                                </div>
                            </div>

                            <hr>

                            <!-- Student Engagement -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Student Engagement <b><span
                                            style="color:red;">*</span></b></label>
                                <div class="col-sm-8">
                                    <textarea required id="student_engagement" name="student_engagement" class="form-control" {{ $readonly }}>{{ old('student_engagement', $project->student_engagement) }}</textarea>
                                </div>
                                <div class="invalid-feedback">
                                    This field is required and cannot contain only spaces.
                                </div>

                            </div>

                            <hr>

                            <!-- Ethical Approval -->
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Ethical Approval (If applicable) <small
                                        class="tooltip-text"> PDF only. You can attach multiple files</small></label>
                                <div class="col-sm-8">
                                    <ul class="list-group">
                                        @foreach ($fileNames as $file)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <a target="_blank"
                                                        href="{{ URL::to('/') }}/serveFile2?file=ethical_approvals/{{ $cycle->cycle_title }}/{{ $project->old_project_id }}/{{ $file }}">
                                                        <i class="fas fa-file-pdf" style="color:green"></i>
                                                        <span class="ml-2"
                                                            style="color:green">{{ $file }}</span>
                                                    </a>
                                                </div>
                                                @if ($project->student_project_draft !== 'save')
                                                    <div>
                                                        <button class="btn btn-sm btn-danger delete-btn"
                                                            data-file="{{ $cycle->cycle_title }}/{{ $project->old_project_id }}/{{ $file }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>

                                    <input type="file" name="ethical_approval[]" class="form-control" multiple
                                        {{ $disabled }}>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group row">
                                <label class="col-sm-12 col-form-label" style="color:red; font-style:italic;float-right">
                                    - If you have zero spending, please enter 0 in the spending box, write N/A in the other
                                    fields, and submit the form </label>
                                <label class="col-sm-12 col-form-label" style="color:red; font-style:italic;float-right">-
                                    {{ $msg }}</label>
                            </div>

                            <!-- Submit Buttons -->
                            @if ($project->student_project_draft !== 'save')
                                <div class="form-group row">
                                    <label class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-8">
                                        <button type="submit" name="action" value="save" class="btn"
                                            style="background-color: teal; float:right; border-color: teal; color: white; margin-right:30px;">
                                            Save
                                        </button>

                                        {{-- <button type="submit" name="action" value="draft" class="btn"
                                        style="background-color: teal; float:right; border-color: teal; color: white; margin-right:30px;">
                                        Save As Draft
                                    </button> --}}
                                    </div>
                                </div>
                            @endif
                        </form>




                    </div>
                </div>
            </div>
        </div>
    </body>





    <script>
        const form = document.getElementById('myform');
        const input = document.getElementById('student_engagement');


        // ✅ Check on form submit
        form.addEventListener('submit', function(event) {
            validateInput();

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });

        // ✅ Also check on every user input
        input.addEventListener('input', validateInput);

        function validateInput() {
            const trimmedValue = input.value.trim();
            if (trimmedValue === '') {
                input.setCustomValidity('This field cannot be empty or spaces only.');
            } else {
                input.setCustomValidity('');
            }
        }


        function updateSpendingStatus() {
            const maxValue = parseFloat(document.getElementById("requested_budget_qar").value);
            const currentValue = parseFloat(document.getElementById("spending").value);

            if (!isNaN(maxValue) && !isNaN(currentValue)) {
                const percentage = parseInt((currentValue / maxValue) * 100);

                if (currentValue > maxValue) {
                    document.getElementById("overflow").textContent = `Exceeded: ${percentage}%`;
                } else {
                    document.getElementById("overflow").textContent = `Used: ${percentage}%`;
                }
            } else {
                document.getElementById("overflow").textContent = "";
            }
        }

        // Attach the event listener
        document.getElementById("spending").addEventListener("input", updateSpendingStatus);

        // ✅ Call it once on page load
        window.addEventListener("DOMContentLoaded", updateSpendingStatus);




        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.addEventListener("click", async function() {
                    let fileName = this.getAttribute("data-file");
                    let button = this;
                    let originalText = button.innerText;

                    if (confirm(`Are you sure you want to delete ${fileName}?`)) {
                        button.innerText = "Deleting...";
                        button.disabled = true; // Disable the button to prevent multiple clicks

                        try {
                            let response = await fetch("{{ route('deletefile') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    file: fileName
                                })
                            });

                            let data = await response.json();

                            if (data == 1) {


                                button.closest("li")
                                    .remove(); // Remove the list item from the UI

                            } else {

                                button.innerText = originalText;
                                button.disabled = false; // Re-enable the button on failure
                            }
                        } catch (error) {
                            console.error("Error:", error);
                            alert("Something went wrong.");
                            button.innerText = originalText;
                            button.disabled = false; // Re-enable the button on error
                        }
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Add Student ID Field
            $('.add-student-id').click(function() {
                $('#student-id-container').append(`
                <div class="input-group mb-2">
                    <input type="text" name="student_ids[]" class="form-control" placeholder="Enter Student ID">
                    <button type="button" class="btn btn-outline-danger remove-field">-</button>
                </div>
            `);
            });

            // Add Publication Field
            $('.add-publication').click(function() {
                $('#publication-container').append(`
                <div class="input-group mb-2">
                    <input type="text" name="publications[]" class="form-control" placeholder="Enter publication details">
                    <button type="button" class="btn btn-outline-danger remove-field">-</button>
                </div>
            `);
            });

            // Remove dynamically added fields
            $(document).on('click', '.remove-field', function() {
                $(this).closest('.input-group').remove();
            });
        });
    </script>

    <!-- JavaScript to Add & Remove Publications -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".add-publication").addEventListener("click", function() {
                let container = document.getElementById("publication-container");
                let newInput = document.createElement("div");
                newInput.classList.add("input-group", "mb-2");
                newInput.innerHTML = `
            <input type="text" name="publications[]" class="form-control" placeholder="Enter publication details">
            <button type="button" class="btn btn-outline-danger remove-publication">-</button>
        `;
                container.appendChild(newInput);
            });

            document.getElementById("publication-container").addEventListener("click", function(event) {
                if (event.target.classList.contains("remove-publication")) {
                    event.target.closest(".input-group").remove();
                }
            });
        });
    </script>
@endsection
