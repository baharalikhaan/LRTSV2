<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
    img {
        width: 200px;
        height: 300px;
        object-fit: cover;
    }

    .error {
        color: red;
        font-weight: bold;
    }

    .container {
        margin-left: 5%;
    }

    .card {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        margin: 4%;
    }

    .heading {
        height: 7%;
        width: 80%;
        padding: 0.5%;
        margin-left: 10%;
        color: teal;
        margin-bottom: 3%;
    }

    td {
        padding: 2%;
        font-size: 80%;
    }

    .message {
        border-radius: 5px;
        border: 2px solid teal;
        background-color: #54BAB9;
        height: 7%;
        width: 80%;
        padding: 0.5%;
        margin-left: 10%;
        color: teal;
        font-weight: bold;
        color: beige;
    }
</style>
@include('components.sidebar',['test'=>'1'])
@include('components.navbar')
<div class="container">
    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
        <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Edit & Update About Us Page</legend>
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
                        <input type="file" name="image" accept=accept="image/png, image/gif, image/jpeg" /></td>
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
                        <input type="text" name="name" class="form-control" value={{$about->name}}>
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
                        <input type="text" name="role" class="form-control" value={{$about->role}}>
                    </td>
                </tr>
                <tr>
                    <td><label for=""><br>Introduction</label></td>
                    <td>@error('introduction')
                        <span class="error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror<br>
                        <textarea name="introduction" class="form-control" rows="4" cols="50">{{$about->introduction}}</textarea></td>
                </tr>
                <tr>
                    <td><label for=""><br>Email</label></td>
                    <td>@error('email')
                        <span class="error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror<br>
                        <input type="text" name="email" class="form-control" value={{$about->email}}></td>
                </tr>
            </table>
            <div class="form-group mb-3" style="font-size:80%">
                <button type="submit" class="btn btn-primary">Update Information</button>
            </div>
        </form>
</div>