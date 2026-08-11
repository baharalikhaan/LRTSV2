<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    #bg {
        background-color: teal;
        border-right: 2px solid teal;

    }

    /* #bg {
        background-color: #54BAB9;
        border-right: 2px solid teal;
        background-image: linear-gradient(#35B5AC, #89D5D2);
    } */

    #bg,
    .w3-button {
        color: beige;
    }

    #bg i:hover {
        background: #ccc;
        color: #f58216;
    }
</style>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<body>


    <div id='bg' class="w3-sidebar w3-bar-block w3-xlarge" style="width:50px">
        <br>
        <br>
        <br>
        <hr>

        <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-arrow-left" title="Back"></i></a>
        <hr>
        @if (Auth::user()->type == 'Admin')
            <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-briefcase"
                    title="Projects"></i></a>
            <a href="{{ route('assignReviewCycle') }}" class="w3-bar-item w3-button" title="Assign Reviewers"><i
                    class="fa fa-pencil-square-o"></i></a>
            <a href="{{ route('AssignedReviewers') }}" class="w3-bar-item w3-button" title="Assigned Reviewers"><i
                    class="fa fa-tags"></i></a>
            <a href="{{ route('gradedProjects') }}" class="w3-bar-item w3-button"><i class="fa fa-certificate"
                    title="Graded Projects"></i></a>

            <a href="{{ route('AdminCycles') }}" class="w3-bar-item w3-button"><i class="fa fa-file-text"
                    title="Cycle Summary"></i></a>
        @endif
        @if (Auth::user()->type == 'Reviewer')
            <a href="#" class="w3-bar-item w3-button"><i class="fa fa-briefcase"></i></a>
        @endif

        @if (Auth::user()->type == 'LPI')
            <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-briefcase"></i></a>
            {{--
            <a href="{{ route('gradedProjects') }}" class="w3-bar-item w3-button"><i class="fa fa-certificate"
                title="Graded Projects"></i></a> --}}
        @endif

    </div>

    <div style="margin-left:70px">
    </div>

</body>

</html>
