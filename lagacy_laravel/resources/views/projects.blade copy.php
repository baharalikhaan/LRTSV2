<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.js"></script>
<style>
  #parent {
    position: relative;
  }

  .float {
    position: fixed;
    width: 3%;
    height: 6%;
    bottom: 3%;
    right: 3%;
    background-color: teal;
    color: #FFF;
    border-radius: 50%;
    text-align: center;
  }

  .my-float {
    margin-top: 20%;
  }

  table {
    margin-left: 20%;
    margin-right: 20%;
  }

  table,
  td {
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 11px;
  }

  th {
    border-bottom: 1px solid #ddd;
    text-align: center;
    font-size: 11px;
  }

  tr:hover {
    background-color: beige;
  }

  #inside {
    margin-right: 5%;
    padding: 4%;
  }

  .message {
    border-radius: 5px;
    border: 2px solid lightseagreen;
    background-color: lightblue;
    height: 9%;
    width: 80%;
    padding: 0.5%;
    margin-left: 10%;
    color: teal;
    font-weight: bold;
  }

  .body {
    background-image: url('storage/images/background2.jpg');
    background-size: 100%;
  }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<body class="body">
  <div id='format'>
    @include('components.projectSideBar')
    @include('components.navbar')
    <br>
  </div>
  <br>
  <br>
  @if (isset($message))
  <div class="message" align="center">
    {{$message}}
  </div>
  @endif
  <div>
    <div id="inside">
      <table id="table_id" class="display" style="background-color: #E9F6F6;">
        <thead>
          <tr>

            <!-- <th>Id</th> -->
            <th>Project ID</th>
            <th>Project Title</th>
            <th>Status</th>
            <th>File</th>
            @if($permit=='yes')
            <th> Info </th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($projects as $project)
          <tr>
            <!-- <td> {{$project['id']}} </td> -->
            <td> {{$project['old_project_id']}} </td>
            <td> {{$project['title']}} </td>
            <td <?php
                if ($project['status'] == 'Pending') {
                  echo "style='color:LightBlue'";
                } else if ($project['status'] == 'Accepted') {
                  echo "style='color:teal'";
                } else if ($project['status'] == 'Rejected') {
                  echo "style='color:red'";
                }
                ?>><b> {{$project['status']}}</b> </td>

            <?php $p_id = $project['id'] ?>
            <td><?php if ($project['status'] == 'Pending')
                  $route = 'proposal';
                else if ($project['status'] == 'Accepted')
                  $route = 'grading';
                ?>
              <a href="{{route($route,['p_id'=>$p_id])}}" class="w3-bar-item w3-button"><i class="fa fa-file" style="color:	#66b2b2"></i></a>
            </td>
            @if($permit=='yes')
            <td id='info'><a href="{{route('projectDetails',['p_id'=>$p_id])}}" class="w3-bar-item w3-button"><i class="fa fa-info" style="color:	#66b2b2"></i></a></td>
            @endif
          </tr>


          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</body>
<script type="text/javascript">
  $(document).ready(function() {
    $('#table_id').DataTable({
      paging: true,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ],
      pageLength: 10,
      info: false
    });
    $('#table_id').css({
      'margin-right': '10%',
      'margin-left': '5%',
      'font-size': '11px',
      'padding': '0',
    });
  });
  $(document).ready(function() {
    $('#table_id_length > label').css({
      'position': 'relative',
      'top': '2%',
      'left': '45%'
    });
  });
  $(document).ready(function() {
    $('#table_id_filter > label').css({
      'margin-left': '0% ',
      'position': 'relative',
      'bottom': '30%',
      'right': '25%',
      'align': 'center'
    });
  });
  $(document).ready(function() {
    $('#table_id_paginate').css({
      'position': 'relative',
      'bottom': '0.25%',
      'right': '30%'
    });
  });
</script>