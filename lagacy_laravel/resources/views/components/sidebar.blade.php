<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    #bg {
        background-color: teal;
        border-right: 2px solid teal;

    }

    .bg3 {
             background-color: #f0f0f0;
             background-image: url("{{ asset('images/infographs-pattern.png') }}");

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
        @if (Auth::user())

            @if (!request()->routeIs('home'))
                <a href="{{ url()->previous() }}" class="w3-bar-item w3-button"><i class="fa fa-arrow-left"
                        title="Back"></i></a>
                <hr>
            @endif


            <!-- <a href="{{ route('home') }}" class="w3-bar-item w3-button"><i class="fa fa-fw fa-home" title="Home"></i></a> -->
            @if (Auth::user()->type == 'LPI')
                <a href="{{ route('userDetails') }}" class="w3-bar-item w3-button"><i class="fa fa-bar-chart"
                        title="Dashboard"></i></a>
                <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-briefcase"
                        title="Projects"></i></a>
            @endif

            @if (Auth::user()->type == 'Reviewer')
                {{-- <a href="{{ route('reviewerDetails') }}" class="w3-bar-item w3-button"><i class="fa fa-bar-chart"
                        title="Dashboard"></i></a> --}}
                <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-briefcase"
                        title="Projects"></i></a>
            @endif

            @if (Auth::user()->type == 'Admin')
                <a href="{{ route('dashboard') }}" class="w3-bar-item w3-button"><i class="fa fa-dashboard"
                        title="Dashboard"></i></a>
                <hr>



                {{-- <a href="{{ route('cycle') }}" class="w3-bar-item w3-button" title="Cycle Settings"><i
                        class="fa fa-recycle" title="cycle"></i></a> --}}

                <a href="{{ route('cycles') }}" class="w3-bar-item w3-button"><i class="fa fa-briefcase"
                        title="Projects"></i></a>


                {{-- <a href="{{ route('announcementSetting') }}" class="w3-bar-item w3-button"
                    title="Announcement Settings"><i class="fa fa-bullhorn"></i></a> --}}


                <a href="{{ route('settings') }}" class="w3-bar-item w3-button"><i class="fa fa-gears"
                        title="Settings"></i></a>

                <!-- <a href="#" class="w3-bar-item w3-button"><i class="fa fa-trash"></i></a> -->
            @endif
        @endif
    </div>

    <div style="margin-left:70px">
    </div>

</body>

</html>
