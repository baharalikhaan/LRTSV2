<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    .card {
        width: 400px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border-radius: 8px;
    }

    .header {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        background-color: teal;
        color: #fff;
    }

    .header h2 {
        margin: 0;
    }

    .header p {
        margin: 5px 0 0;
        font-size: 14px;
    }

    .content3 {
        padding: 20px;
    }

    .content img {
        width: 100%;
        height: auto;
        border-radius: 4px;
    }

    .image-column {
        width: 40%;
        padding-right: 10px;
    }

    .info-column {
        width: 60%;
        padding: 10px;
        display: flex;
        flex-direction: column;
    }

    .contact {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }

    .contact i {
        margin-right: 10px;
        color: #66b2b2;
    }

    .row {
        display: flex;
    }

    .contact div {
        display: flex;
        flex-direction: column;
    }

    .contact div span {
        margin-bottom: 5px;
        font-size: 14px;
    }


    img {
        height: 100px;
        width: 100%;
        padding-right: 65px;
        margin-left: 50px;
    }

    .bg2 {
        background-color: #f0f0f0;
        background-image: url("{{ asset('images/infographs-pattern.png') }}");

    }

    .bg {
        background-color: #e2ecec;
        background-image: url("{{ asset('images/infographs-pattern.png') }}");
        border: 2px solid teal;
        border-radius: 30px 30px 30px 30px;

    }

    .bg3 {
        background-color: #e2ecec;
        background-image: url("{{ asset('images/infographs-pattern.png') }}");
        border: 2px solid teal;
        border-radius: 30px 30px 30px 30px;
        position: relative;
        overflow: hidden;
        height: 250;
    }


    .box {
        position: absolute;
        height: 270;
        width: 700;
        right: -10px;
        bottom: -10px;
        border: 2px solid teal;
        overflow: hidden;
        background-color: #f6f8f3;
        clip-path: polygon(150px 0%, 100% 0%, 100% 100%, 0% 100%, 0% 270px);
    }


    .background-video {
        width: 100%;
        height: 200%;
        object-fit: cover;
        transform-origin: center;
    }
</style>
</head>




<body>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <title>Budget API Settings</title>
    </head>

    @extends('layouts.app')
    @section('title', 'Home Page')
    @section('content')


        <div class="bg2">

            <img src="{{ asset('images/title3.png') }}" alt="Example Image">

        </div>

        <div class="row" >
            <div class="col-md-12" style="margin-top: 10;">
                <div class="bg3">
                    <div style=" margin: 40px;">
                        <h3 style="color:teal;">About Us</h3>
                        <p style="width:1000">The RTS is designed to digitally register the growth of awarded grants,
                            monitor research
                            outcomes, and provide project leaders with performance comparisons against their peers. The
                            Office of Research Support (ORS) can leverage the system to track progress and make informed
                            decisions, contributing to the overall improvement of grant performance and the establishment of
                            best practices for higher education and research institutions.</p>


                    </div>
                    <div class="box">
                        <div class="video-wrapper">
                            <video autoplay muted loop class="background-video">
                                <source src="{{ asset('images/video3.mp4') }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<br>
        <div class="row"  >
            <div class="col-md-12" style="margin-top: 10;">
                <div class="bg">
                    <div style=" margin: 40px;">

                        <div class="container-fluid">
                            <div class="row">
                                @foreach ($about as $about)
                                    <div class="col-md-3" style="padding:10">
                                        <div class="card" style="border: 1px solid teal; border-radius:15px;">
                                            <div class="header">
                                                <h4>{{ $about['name'] }}</h4>
                                                <p>{{ $about['role'] }}</p>
                                            </div>
                                            <div class="content3">
                                                <div class="row">
                                                    <div class="info-column">
                                                        <div class="contact">
                                                            <i class="fas fa-envelope"></i>
                                                            <span>{{ $about['email'] }}</span>
                                                        </div>
                                                        <div class="contact">
                                                            <i class="fas fa-phone"></i>
                                                            <span>{{ $about['phone'] }}</span>
                                                        </div>
                                                        <div class="contact">
                                                            <i class="fas fa-fax"></i>
                                                            <span>{{ $about['phone'] }}</span>
                                                        </div>
                                                        <div class="contact">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                            <span>{{ $about['address'] }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <div class="heading">
                            Our Team
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>

    </html>
@endsection
