<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


  <style>
    .heading {
      position: absolute;
      top: -15;
      left: 35;
      background-color: teal;
      color: white;
      padding: 6px;
      border-radius: 15px 15px 15px 15px;
    }

    .heading2 {
      position: absolute;
      top: -15;
      left: 35;
      background-color: #623C21;
      color: white;
      padding: 6px;
      border-radius: 15px 15px 15px 15px;
    }

    .footer {
      position: absolute;
      bottom: 1;
      right: 55;
      font-size: 11px;
      font-style: italic;
      color: #623C21;
    }

    .btn-teal {
      color: #fff;
      background-color: #008080;
      border-color: #008080;
    }

    .btn-teal:hover {
      color: #fff;
      background-color: #005959;
      border-color: #005959;
    }
  </style>
</head>


<body class="body">
  @include('components.ProjectSidebar')
  @include('components.navbar')
  <br>
  <div id="inside">
    <table id="table_id" class="table table-striped">
      <thead>
        <tr>

          <th>Id</th>
          <th>Project Title</th>
          @if(Auth::user()->type=='Admin')
          <th>Reviewers</th>
          @endif
          <th><i class="fa fa-pencil"></i></th>
        </tr>
      </thead>
      <tbody>
        @foreach($projects as $project)
        <tr>
          <td> {{$project['project_id']}} </td>
          <td> {{$project['title']}} </td>
          @if(Auth::user()->type=='Admin')
          <td>{{$project['reviewers']}}</td>
          @endif
          <form action="">
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>
          </form>
        </tr>
 
        @endforeach
      </tbody>
    </table>
  </div>
  </div>
  <script type="text/javascript">

  </script>