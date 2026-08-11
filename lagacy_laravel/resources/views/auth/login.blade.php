<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta charset="UTF-8">
</head>
<style>
    #box-form {
        font-family: "Times New Roman", Times, serif;
        border: 3px solid teal;
        border-radius: 15px;
        height: 400px;
        width: 200px;
        margin: 0 auto;
        position: relative;
        padding: 20px;
    }

    .input {
        margin: 0 auto;
        position: relative;
        width: 100%
    }
    .body {
  background-image:url('storage/images/research_building_1.jpg');
  background-repeat: no-repeat;
  background-size: 100%;}
    #btn {
        margin: auto;
        display: block;
        width:40%;
        height: 6%;
        border: 3px solid teal;
        border-radius: 5px;
    }
    #header{
        background-color: teal;
        font-weight: bold;
        border-radius: 2px;
        width:100%;
        height:8%;
    }
    .h5{
        color: beige;
        padding-top: 8px;
    }
    .material-icons{
        color:teal;
    }
</style>

<body class="body">

    </br>
    <div class="container" id="box-form" style="background-color:#F9F9F3">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                </br>
                <div id="header" class="w3-padding w3-bar-block w3-large">
                    <h5 class="h5" align="center" style="font-size:100%">LOGIN FORM</h5>
                </div>
                <div class="inputs">
                    </br>
                    <i class="material-icons">mail</i>
                    <br><input id="email" type="email" placeholder="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus> </label>
                    </br>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    </br>
                    <i class="material-icons">lock</i>
                    <br><input id="password" type="password" placeholder="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password"></label>

                    <br>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror<br>
                    <div class="remember-me--forget-password">
                        <!-- Angular -->
                        <label>
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span class="text-checkbox">Remember me</span>
                        </label>
                    </div>
                    <br>
                    <button id="btn" type="submit" class="btn btn-primary">Login</button>
                </div>
                <br>
                @if (Route::has('password.request'))
                <a class="btn btn-link" href="{{ route('password.request') }}" style="font-size:80%">
                    {{ __('Forgot Your Password?') }}
                </a>
                @endif
            </div>
        </form>
    </div>
</body>

</html>