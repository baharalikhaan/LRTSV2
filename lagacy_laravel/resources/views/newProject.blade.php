<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta charset="UTF-8">
</head>
<style>
    #box-form {
        font-family: "Times New Roman", Times, serif;
        border: 3px solid teal;
        border-radius: 15px;
        height: 85%;
        width: 25%;
        margin: 0 auto;
        position: relative;
        padding: 20px;
    }

    .input {
        margin: 0 auto;
        position: relative;
        width: 100%
    }

    .btn {
        margin: auto;
        display: block;
        width: 40%;
        height: 6%;
        border: 3px solid teal;
        border-radius: 5px;
    }

    #header {
        background-color: teal;
        font-weight: bold;
        border-radius: 2px;
        width: 100%;
        height: 8%;
    }

    .h5 {
        color: beige;
        padding-top: 8px;
    }

    .invalid-feedback {
        color: red;
    }
    #title{
        width:100%;
</style>

<body class="body">

    </br>
    <div class="container" id="box-form">
        <form method="POST" action="{{ route('CreateProject')}}" enctype="multipart/form-data">
            @csrf
            <div>
                <div id="header" class="w3-padding w3-bar-block w3-large">
                    <h5 class="h5" align="center">Create New Project</h5>
                </div>
                <br>
                <br>
                @error('title')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
                <label class="col-md-4 col-form-label text-md-end"><b>{{ __('Title') }}</b></label>
                <input id="title" type="text" name='title'>
                <br>

                @error('stakeholder')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror<br>
                <label><b>Choose the other co-authors of the project:</b></label><br>
                <div id='add'>
                <select id="stakeholder" name="stakeholder[]" multiple>
                    <option value='' disabled selected>Select Co-Author</option>
                    @foreach($user as $row)
                    <option value={{$row->id}}>{{$row->email}}</option>
                    @endforeach
                </select>
                </div>
                <br><br>
                @error('file')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
                <br>
                <label for="file"><b>Select a file:</b></label><br>
                <input type="file" id="file" name="file">
                <br>
                <br>
                <br>
                <button type="submit" class="btn btn-primary">
                    {{ __('Submit') }}
                </button>
            </div>
        </form>
    </div>
</body>
