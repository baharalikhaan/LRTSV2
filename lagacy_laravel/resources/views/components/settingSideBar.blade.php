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

        <a href="{{ url()->previous() }}" class="w3-bar-item w3-button"><i class="fa fa-arrow-left"
                title="Go Back"></i></a>
        <hr>


        <a href="{{ route('user') }}" class="w3-bar-item w3-button"><i class="fa fa-user" title="System Users"></i></a>

        <a href="{{ route('cycle') }}" class="w3-bar-item w3-button" title="Cycle Settings"><i class="fa fa-recycle"
                title="Project Cycles"></i></a>

        <a href="{{ route('sendEmailAdmin') }}" class="w3-bar-item w3-button"><i class="fa fa-envelope"
                title="Send General Emails"></i></a>

        <a href="{{ route('announcementSetting') }}" class="w3-bar-item w3-button" title="Announcements"><i
                class="fa fa-bullhorn"></i></a>

        <a href="{{ route('uploadProgressAdmin') }}" class="w3-bar-item w3-button"><i class="fa fa-line-chart"
                title="Add Progress"></i></a>

        <a href="{{ route('reviewerAgrementAdmin') }}" class="w3-bar-item w3-button"><i class="fa fa-handshake-o"
                title="Reviewer Agrement"></i></a>

        <a href="{{ route('budgetAPIList') }}" class="w3-bar-item w3-button"><i class="fa fa-money"
                title="Bedget API Data"></i></a>

        <a href="{{ route('aboutUsSettings') }}" class="w3-bar-item w3-button" title="About Us"><i
                class="fa fa-id-card-o" title="Our Team Settings"></i></a>



        <hr>
        <a href="{{ route('emailSetting') }}" class="w3-bar-item w3-button" title="Email Template Settings"><i
                class="fa fa-envelope"></i></a>
        <a href="{{ route('smtpSettings') }}" class="w3-bar-item w3-button" title="Email SMTP Settings"><i
                class="fa fa-sliders" title="SMTP Settings"></i></a>
        <a href="{{ route('guageSetting') }}" class="w3-bar-item w3-button" title="Guage Settings"><i
                class="fa fa-dashboard" title="Gauge Settings"></i></a>
        <hr>









    </div>

    <div style="margin-left:70px">
    </div>

</body>

</html>
