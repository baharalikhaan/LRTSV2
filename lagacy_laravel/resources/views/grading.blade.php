<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
    .error {
        font-weight: bold;
        color: red;
    }

    .sidebar {
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1;
        top: 0;
        right: 0;
        background-color: whitesmoke;
        overflow-x: hidden;
        transition: 0.5s;
        padding-top: 60px;
        border: 2px solid teal;
        font-size: 8;
        padding-right: 1%;
        padding-left: 1%;
        font-weight: bold;
    }

    .sidebar a {
        padding: 8px 8px 8px 32px;
        text-decoration: none;
        font-size: 25px;
        color: #818181;
        display: block;
        transition: 0.3s;
    }

    .sidebar a:hover {
        color: #f1f1f1;
    }

    .sidebar .closebtn {
        position: absolute;
        top: 0;
        left: 0px;
        font-size: 16px;
        margin-right: 5px;
        font-weight: bold;
    }

    .openbtn {
        font-size: 20px;
        cursor: pointer;
        background-color: teal;
        color: beige;
        padding: 10px 15px;
        border: none;
    }

    .openbtn:hover {
        background-color: #444;
    }

    #main {
        transition: margin-right .5s;
        padding: 16px;
    }

    #header .title {
        font-weight: bold;
        color: teal;
        font-size: 25;
    }

    table {
        font-size: 12;
        color: teal;
    }

    .radio {
        margin-left: 2%;
        font-size: 10px;
        font-weight: bold;
        color: teal;
    }

    .alert {
        color: red;
    }

    b {
        color: teal;
        font-size: 10;
    }

    #box-form {
        font-family: "Times New Roman", Times, serif;
        border-radius: 15px;
        height: 70%;
        margin: 0 auto;
        position: relative;
    }

    #input {
        margin: 0 auto;
        position: relative;
        width: 100%;
        font-size: 120%;
        color: teal;
        padding: 10%
    }

    #submit {
        margin: auto;
        display: block;
        width: 20%;
        height: 6%;
        border: 3px solid teal;
        border-radius: 5px;
    }

    #header {
        background-color: teal;
        font-weight: bold;
        border-radius: 2px;
        width: 100%;
        height: 7%;
    }

    /* Create three equal columns that floats next to each other */
    .column {
        float: left;
        width: 31%;
        padding: 10px;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }

    .h5 {
        color: beige;
        padding-top: 6px;
    }

    #icons {
        font-size: 130%;
    }

    #act {
        color: teal;
    }

    #non {
        color: #aaa;
    }

    table {
        font-size: 80%;
        font-weight: bold;
    }

    #bottom {

        position: absolute;
        bottom: 1%;
        font-size: 80%;
        color: #CC7722;
    }

    /* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
    @media screen and (max-height: 450px) {
        .sidebar {
            padding-top: 15px;
        }

        .sidebar a {
            font-size: 18px;
        }

        /* Additional styles for the link */
        .custom-link {
            cursor: pointer;
            /* Set cursor to the pointer (finger) */
            color: blue;
            /* Change link color as desired */
            text-decoration: none;
            /* Remove default underline */
        }

        /* Additional styles for when hovering over the link */
        .custom-link:hover {
            text-decoration: underline;
            /* Add underline on hover */
        }

    }
</style>
</head>
<div id="main">
    <div id='header'>
        <h6 class='title' align="center">{{ $project->title }} </h6>
        <input type="radio" id="proposal" name="type" class="radio" onclick="proposal()">
        <label class="radio">Project Propsal</label>
        <input type="radio" id="progress" name="type" class="radio" onclick="progress()">
        <label class="radio">Progress Report</label>
        <input type="radio" id="final" name="type" class="radio" onclick="final()">
        <label class="radio">Final Report</label>

        <button class="openbtn w3-button w3-right" onclick="openNav()"><i class="fa-regular fa-left"></i>
        </button>
    </div>
</div>

<div id="proposalFrame">
    <div id="header" class="w3-padding w3-bar-block w3-large">
        <h5 class="h5" align="center">Project Commitments</h5>
    </div>
    <br>

</div>



<div id="progressFrame">
    @if ($progress_report)
        <iframe src="http://localhost:8000/serveFile?file={{ $progress_report->path }}" height="90%" width="95%"
            align="center"></iframe>
    @else
        <b class='alert'>Progress Report for this project is not avaialable</b>
    @endif
</div>

<div id="finalFrame">
    @if ($final_report)
        <iframe src="http://localhost:8000/serveFile?file={{ $final_report->path }}" height="90%" width="95%"
            align="center"></iframe>
    @else
        <b class='alert'>Final Report for this project is not avaialable</b> <br>
        <br>
    @endif
</div>

<div id="Sidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <?php if ($errors->any()) : ?>
    <div class="error">
        <?php foreach ($errors->all() as $error) : ?>
        <?php echo $error; ?><br>
        <?php endforeach; ?>
    </div><br>
    <?php endif; ?>

    @if ($finalGrades)
        <div id="final_form">
            <b>Results and Outcomes</b><br><br>
            {{ $finalGrades->gradeA }}<br><br>
            {{ $finalGrades->commentA }}
            <br><br>
            <b>Publications</b> <br><br>
            {{ $finalGrades->gradeB }}<br><br>
            {{ $finalGrades->commentB }}
            <br><br>
            <b>Young Researcher Supervision</b> <br><br>
            {{ $finalGrades->gradeD }}<br><br>
            {{ $finalGrades->commentD }}
            <br><br>
            <b>Project</b> <br><br>
            {{ $finalGrades->gradeC }}<br><br>
            {{ $finalGrades->commentC }}
        </div>
    @else
        <div id="final_form">
            @if ($final_report)
                <div id='inner'>
                    <form method="POST" action="{{ route('finalGrades') }}">
                        @csrf
                        <h6 align="center"><b> Kindly fill in the following form </b></h6>

                        <input type="text" name="p_id" value={{ $p_id }} hidden>
                        <label
                            class="col-md-4 col-form-label text-md-end"><b>{{ __('1. Results and Outcomes') }}</b></label><br>
                        <b>A. Achievements</b>
                        <p>Degree of realization of the proposed outcomes in the project</p>
                        <p>Does the project produced a Prototype, Patent, Open Source Software, etc.?</p>
                        <p>If a Prototype is achieved, state its TRL level (or SRL for society readiness)</p>
                        <input id="gradeA" type="text" name='gradeA'>
                        <br>
                        <label>Comment</label>
                        <textarea id="commentA" name="commentA" rows="4" cols="35"></textarea><br><br>

                        <label
                            class="col-md-4 col-form-label text-md-end"><b>{{ __('2. Publications') }}</b></label><br>
                        <input id="gradeB" type="text" name='gradeB'><br>
                        <br>
                        <label>Comment</label>
                        <textarea id="commentB" name="commentB" rows="4" cols="35"></textarea><br><br>
                        <b>Q. Were new Researchers Involved in the project</b><br>
                        <input type="radio" id="Yes" value="Yes" name="yesno" onclick="show();">
                        <label for="Yes">Yes</label><br>
                        <input type="radio" id="No" value="No" name="yesno" onclick="hide();">
                        <label for="No">No</label><br>
                        <div id="YR">
                            <label
                                class="col-md-4 col-form-label text-md-end"><b>{{ __('3. Young Researcher Supervision') }}</b></label><br>
                            <input id="gradeD" type="text" name='gradeD'><br>
                            <label>Comment</label>
                            <textarea id="commentD" name="commentD" rows="4" cols="35"></textarea><br><br>
                            <br>
                        </div>
                        <label class="col-md-4 col-form-label text-md-end"><b>{{ __('4. Project') }}</b></label><br>
                        <input id="gradeC" type="text" name='gradeC'><br>
                        <label>Comment</label>
                        <textarea id="commentC" name="commentC" rows="4" cols="35"></textarea>
                        <br>
                        <br>

                        <button type="submit" class="btn btn-primary" position="center">
                            {{ __('Submit Grades') }}
                        </button>

                        <div class="w3-container">
                            <button onclick="document.getElementById('id01').style.display='block'"
                                class="w3-button w3-display-bottomcenter">Help</button>

                            <div id="id01" class="w3-modal">
                                <div class="w3-modal-content">
                                    <div class="w3-container">
                                        <span onclick="document.getElementById('id01').style.display='none'"
                                            class="w3-button w3-display-topright">&times;</span>
                                        @include('components.helpFinal')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <br>
                <?php if ($permit == 'yes') : ?>
                <div id='inner'>
                    <form method="POST" action="{{ route('outcome') }}">
                        @csrf
                        <input type="text" name="p_id" value={{ $p_id }} hidden>
                        <select name="select" id="select" value="">
                            <option value='' disabled selected>Update Status</option>
                            <option value="Outstanding">Outstanding</option>
                            <option value="Satisfied">Satisified</option>
                            <option value="ImprovementDesired">Improvement Desired</option>
                            <option value="Reserved">Reserved</option>
                        </select><br><br>
                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </form>


                </div>
            @endif
            <?php endif ?>
    @endif
</div>



@if ($progressComments)
    <div id="progress_form">
        <b color='teal'>Analysis</b><br><br>
        {{ $progressComments->analysis }}<br><br>
        <b>Comments</b><br><br>
        <i> {{ $progressComments->comments }}</i><br><br>
        <b>Recommendations</b><br><br>
        <i>{{ $progressComments->recommendation }}</i><br><br>
    </div>
@else
    <div id='progress_form'>
        @if ($progress_report)
            <form method="POST" action="{{ route('progressGrade') }}">
                @csrf
                <input type="text" name="p_id" value={{ $p_id }} hidden>
                <label class="col-md-4 col-form-label text-md-end">
                    <h5 align="center"><b>{{ __('Progress Report Form') }}</b></h5>
                </label><br>
                <b>
                    <p> Kindly fill in the following form</p>
                </b>
                <label><b>Analysis</b></label><br>
                <textarea id="analysis" name="analysis" rows="4" cols="35"></textarea><br><br>
                <label><b>Summative Comments and Remarks</b></label><br>
                <textarea id="remarks" name="comments" rows="4" cols="35"></textarea><br><br>
                <label><b>Recommendation</b></label><br>
                <textarea id="recommendation" name="recommendation" rows="4" cols="35"></textarea><br><br>
                <button type="submit" class="btn btn-primary">
                    {{ __('Submit') }}
                </button>

            </form>
        @endif
    </div>
@endif
</div>


</div>

<script>
    function openNav() {
        document.getElementById("Sidebar").style.width = "250px";
        document.getElementById("main").style.marginRight = "250px";
    }

    function closeNav() {
        document.getElementById("Sidebar").style.width = "0";
        document.getElementById("main").style.marginRight = "0";
    }



    function proposal() {
        document.getElementById('progress_form').style.display = 'none';
        document.getElementById("final_form").style.display = 'none';
        document.getElementById("finalFrame").style.display = 'none';
        document.getElementById("progressFrame").style.display = 'none';
        document.getElementById("proposalFrame").style.display = 'block';
    }

    function progress() {
        document.getElementById('proposalFrame').style.display = 'none';
        document.getElementById('final_form').style.display = 'none';
        document.getElementById('finalFrame').style.display = 'none';
        document.getElementById('progress_form').style.display = 'block';
        document.getElementById('progressFrame').style.display = 'block';
    }

    function final() {
        document.getElementById('proposalFrame').style.display = 'none';
        document.getElementById('progress_form').style.display = 'none';
        document.getElementById('progressFrame').style.display = 'none';
        document.getElementById('final_form').style.display = 'block';
        document.getElementById('finalFrame').style.display = 'block';
    }
</script>
