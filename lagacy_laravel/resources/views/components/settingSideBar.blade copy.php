<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    #bg {
        background-color: #54BAB9;
        border-right: 2px solid teal;
    }

    #bg,
    .w3-button {
        color: beige;
    }
</style>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<body>

    <div id='bg' class="w3-sidebar w3-bar-block w3-xlarge" style="width:50px">
        <br>
        <br>
        <br>
        <a href="{{ route('emailSetting') }}" class="w3-bar-item w3-button" title="Email Settings"><i class="fa fa-envelope"></i></a>
        <a href="{{ route('announcementSetting') }}" class="w3-bar-item w3-button" title="Announcement Settings"><i class="fa fa-bullhorn"></i></a>
        <a href="" class="w3-bar-item w3-button" title="User Settings"><i class="fa fa-user"></i></a>
        <a href="{{ route('cycle') }}" class="w3-bar-item w3-button" title="Cycle Settings"><i class="fa fa-superpowers" title="cycle"></i></a>
        <a href="{{ route('guageSetting') }}" class="w3-bar-item w3-button" title="Cycle Settings"><i class="fa fa-clock-o" title="cycle"></i></a>
        <a href="{{ route('aboutUsSettings') }}" class="w3-bar-item w3-button" title="System Settings"><i class="fa fa-gears" title=></i></a>

        <!-- <a href="{{route('excelForm')}}" class="w3-bar-item w3-button"><i class="fa fa-file-excel-o" title="Import Conf Tool data"></i></a>
        <a href="{{route('pdfForm')}}" class="w3-bar-item w3-button"><i class="fa fa-file-pdf-o" title="Import Proposals"></i></a> -->

    </div>

    <div style="margin-left:70px">
    </div>

</body>