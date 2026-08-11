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

    <h5 style="background-color: teal; color: white; padding: 10px; border-top-left-radius: 15px; border-top-right-radius: 15px; text-align:center; margin: 0;">Missing Information</h5>

    <div class="container" id="box-form" style="  margin: 10; ">
      <form method="POST" action="{{ route('updateNationality')}}">
        @csrf


        <div align="center">
          @if(session('successuser'))
          {!! session('successuser') !!}
          @php
          session()->forget('successuser');
          @endphp
          @endif
        </div>


        <div>
          <div id="header" class="w3-padding w3-bar-block w3-small">
            <div align="center">
              <h5> Welcome!</h5>
              <h3 style="color:teal;"><b>{{$user->name}}</b></h3>
            </div>
            <p>It appears you're accessing the RTS platform for the first time. To proceed, we require some additional information from you. Please provide the following details.</p>
          </div>
          <br>
          <div class="inputs">

            <div>
              <label for="nationality" class="col-md-4 col-form-label text-md-end">{{ __('Nationality') }}</label>
              <div>
                <select id="nationality" style="width: 450px;" class="form-control form-control-sm @error('nationality') is-invalid @enderror" name="nationality" required autocomplete="nationality">
                  <option value="Qatri" {{ old('email') == 'qatri' ? 'selected' : '' }}>Qatri</option>
                  <option value="Non-Qatri" {{ old('email') == 'non-qatri' ? 'selected' : '' }}>Non-Qatri</option>
                </select>
              </div>
              @error('nationality')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <br>
            <br>

            <div class="container">
              <button type="submit" style="background-color: teal; border-color: teal; color: white;" class="btn btn-primary" align="right">
                {{ __('Submit') }}
              </button>
            </div>
          </div>
      </form>
    </div>
  </div>
</body>