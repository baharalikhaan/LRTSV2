
 <head>

     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Announcement Details</title>


 </head>

 @extends('layouts.app')
 @section('title', 'Home Page')
 @section('content')


<body class="body">


    <br>
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @if (isset($message))
        <div class="message" align="center">
            {{ $message }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">

                    <div style="border: 2px solid teal;   border-radius: 30px 30px 30px 30px; background-color:#E9F6F6">
                        <div style=" margin: 40px;">

                            <h1>{{ $announcement->subject }}</h1>
                            <p>{{ $announcement->content }} <span style="float:right"><b>Due Date:</b>
                                    {{ $announcement->duedate }}</span></p>

                            <img src="{{ $announcement->image }}" width="100%">
                            <div class="heading">
                                Announcement
                            </div>
                        </div>
                    </div>

        </div>

    </div>



</body>

@endsection
