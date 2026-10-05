<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

<script type="text/javascript" language="javascript" src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.js"></script>

<style>
  table {
    margin-left: auto;
    margin-right: auto;
  }

  table,
  td,
  th {
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 11px;
  }

  tr:hover {
    background-color: beige;
  }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<body>
  @include('components.projectSideBar')
  @include('components.navbar')
  <br>

  <br>
  <div id="inside">
    <table id="table_id" class="display" style="background-color: #E9F6F6;">
      <thead>
        <tr>
          <th>Id</th>
          <th>Project Title</th>
          <th>Status</th>
          <th>Total Grades</th>
          <th>Final Grades</th>
          @if ($permit=="yes")
          <th>Details</th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach($projects as $project)
        <tr>
          <td> {{$project['id']}} </td>
          <td> {{$project['title']}} </td>
          <td <?php echo "style='color:teal'"; ?>><b> {{$project['status']}}<b> </td>
          <td> {{$project['sum']}} </td>
          <td id="a" <?php
                      $mod = $project['avg'] % 10;
                      if ($project['avg'] <= 60  && $project['avg'] > 0) {
                        echo "style='background-color:#FF00$mod$mod'";
                      } else if ($project['avg'] > 60 && $project['avg'] < 85) {
                        echo "style='background-color:#FFFF$mod$mod'";
                      } else if ($project['avg'] > 85 && $project['avg'] <= 100) {
                        echo "style='background-color:#00FF$mod$mod'";
                      } else {
                        echo "style='background-color:white'";
                      }
                      ?>> {{round($project['avg'],2)}} </td>
          <?php $p_id = $project['id'] ?>
          @if ($permit=="yes")
          <td><a href="{{route('gradingDetails',['p_id'=>$p_id])}}" class="w3-bar-item w3-button"><i class="fa fa-info" style="color:teal"></i></a></td>
          @endif
        </tr>
        @endforeach
      </tbody>
    </table>
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