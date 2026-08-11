<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


    <meta charset="UTF-8">
</head>
<style>
    input[type="radio"]:checked {
        background-color: teal;
        border-color: teal;
    }

    /* Customize the color for the unchecked radio button */
    input[type="radio"] {
        background-color: white;
        border-color: teal;
    }

    /* Style to hide the default radio button appearance */
    input[type="radio"] {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid teal;
        outline: none;
        display: inline-block;
        vertical-align: middle;
        cursor: pointer;
    }
</style>

<body class="body">



    <br>
    <br>

    <div class="container" id="box-form" style="border: 2px solid teal; border-radius: 18px; width:500px ;  padding: 0px;">
        <h5 style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">Update About Us Settings</h5>
        <div class="container" id="box-form" style="  margin: 10; ">

            <form action="{{route('aboutUsUpdate',$about->id)}}" method="POST" enctype="multipart/form-data">
                @csrf
                <table>
                    <colgroup>
                        <col span="1" style="width: 15%;">
                        <col span="1" style="width: 70%;">
                    </colgroup>
                    <tr>
                        <td><i class="fa fa-fw fa-image" id="home-btn" title="image"></i></td>
                        <div class="card">
                            <td>
                                <img src="{{asset($about->path)}}" style="width:100%">
                            </td>
                        </div>
                    </tr>
                    <tr>
                        <td> </td>
                        <td>@error('image')
                            <span class="error" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror<br>
                            <input type="file" name="image" accept=accept="image/png, image/gif, image/jpeg" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <br><label for="">Name</label>
                        </td>
                        <td>@error('name')
                            <span class="error" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror<br>
                            <input type="text" name="name" class="form-control" value="{{$about->name}}">
                        </td>
                    </tr>
                    <tr>
                        <td><label for=""><br>Role</label></td>
                        <td>
                            @error('role')
                            <span class="error" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror<br>
                            <input type="text" name="role" class="form-control" value="{{$about->role}}">
                        </td>
                    </tr>
                    <tr>
                        <td><label for=""><br>Introduction</label></td>
                        <td>@error('introduction')
                            <span class="error" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror<br>
                            <textarea name="introduction" class="form-control" rows="4" cols="50">{{$about->introduction}}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><label for=""><br>Email</label></td>
                        <td>@error('email')
                            <span class="error" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror<br>
                            <input type="text" name="email" class="form-control" value="{{$about->email}}">
                        </td>
                    </tr>
                </table>
                <div class="form-group mb-3" style="font-size:80%">
                    <button type="submit" class="btn btn-primary">Update Information</button>
                </div>
            </form>

        </div>
    </div>
</body>