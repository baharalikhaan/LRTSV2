<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
  #box-form {
    font-family: "Times New Roman", Times, serif;
    border-radius: 15px;
    height: 70%;
    margin: 0 auto;
    position: relative;
  }

  #input {
    margin: 0 auto;
    position: relative;
    width: 100%;
    font-size: 120%;
    color: teal;
    padding: 10%
  }

  #next {
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

  #icons {
    font-size: 130%;
  }

  #non {
    color: grey;
  }

  #act {
    color: teal;
  }

  .invalid-feedback {
    color: red;
    font-size: 70%;
  }
</style>

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>

<body>


  <fieldset style="margin-left:2%;margin-right:2%;font-family:sans-serif;padding:2%;border-radius:5%;background:#f2f2f2;border:1px solid teal;width:30%">
    <legend style="background:teal;color:beige;font-size:12px;border-radius:5%;padding:1%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Registering New Project in RTS</legend>
    <div align="center" id="icons">
      <i class="fa fa-circle-info" id="act"> </i> &emsp;&emsp;&emsp;
      <i class="fa fa-fw fa-link" id="non"></i>&emsp;&emsp;&emsp;
      <i class="fa fa-fw fa-list-check" id="non"></i>
    </div>
    <form method="POST" action="{{route('mintProject')}}" id="box-form">
      @csrf
      <div>
        <div id="header" class="w3-bar-block w3-large">
          <h5 class="h5" align="center">Project Information</h5>
        </div>
        <div id="input">
          <br>

          <input type="hidden" name="conf_tool_id" value={{$conf_tool_id}}> </input>

          @error('project_id')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong></span>
          @enderror<br>
          <label style="font-size:90%;">Project ID</label>
          <br><i class="fa-solid fa-keyboard"></i>&emsp;
          <input type="text" value="{{$projid_title_user->old_project_id}}" disabled> </input>
          <input type="hidden" name="old_project_id" value="{{$projid_title_user->old_project_id}}"> </input>
          <br><br>

          @error('title')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong></span>
          @enderror<br>
          <label style="font-size:90%;">Title</label>
          <br><i class="fa-solid fa-keyboard"></i>&emsp;
          <textarea style="width:70%;" disabled>{{$projid_title_user->title}}</textarea>
          <input type="hidden" name="title" value="{{$projid_title_user->title}}"> </input>
          <br><br>

          @error('cycle')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
          </span>
          @enderror<br>
          <label style="font-size:90%;">Cycle</label><br>
          <i class="fa fa-arrows-spin"></i>&emsp;&nbsp;
          <select name="cycle" style="width:60%;">
            <option value='' selected>Select the cycle</option>
            @foreach($cycle as $row)
            <option value="{{$row->id}}">{{$row->cycle_title}}</option>
            @endforeach

          </select>&nbsp;&nbsp;<br><br><br>

          <label style="font-size:90%;">Users</label><br>
          <i class="fa fa-user"></i>&emsp;&nbsp;
          <select name="users" style="width:60%;">
            <option value='' disabled selected>Select the LPI</option>
            @foreach($user as $row)
            <option value="{{$row->id}}">{{$row->email}}</option>
            @endforeach
          </select>&nbsp;&nbsp;


          <!-- @error('users')
          <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong></span>
          @enderror<br>
          <label style="font-size:90%;">User</label>
          <br><i class="fa-solid fa-keyboard"></i>&emsp;
          <input type="text" name="users" value={{$projid_title_user->email}}> </input> -->



        </div>
        <br>
        <button id="next" class="btn btn-primary" type="submit">Next</button>
      </div>
      <br>
      <br>
      </div>
    </form>
    <div><br></div>


</body>