<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.js"></script>
<style>
.yourProjects{
  margin-right: 5%;
  padding:4%;
}
</style>
@include('components.announcementSideBar',['case'=>'0'])
@include('components.navbar')
<br>
<br>
<br>
@if($type==='LPI')
<div class="yourProjects">
<table id="table_id">
  <thead>
        <tr style="color:teal;">
            <!-- <th>Id</th> -->
            <th>Project ID</th>
            <th>Project Title</th>
            <th>Status</th>
            <th>File</th>
        </tr>
  </thead>
  <tbody>
        @foreach($projects as $project)
        <tr>
            <!-- <td> {{$project['id']}} </td> -->
            <td> {{$project['old_project_id']}} </td>
            <td align="left"> {{$project['title']}} </td>
            <td <?php 
            if($project['status']=='Pending'){
              echo "style='color:LightBlue'";
            }
            else if($project['status']=='Accepted'){
              echo "style='color:teal'";}
            else if($project['status']=='Rejected'){
                echo "style='color:red'";
            }
            ?>
            ><b> {{$project['status']}}<b></td>
           <?php $p_id=$project['id'] ?>
            <td><a href="{{route('upload',['p_id'=>$p_id])}}" class="w3-bar-item w3-button"><i class="fa fa-file" style="color:teal;"></i></a></td>
        </tr>
        @endforeach
  </tbody>
    </table>
</div>
@endif
@if($type==='Reviewer' || $type==='LPI+Reviewer')
<div class="projectsReviewer">
<table id="table_id">
  <thead>
        <tr>
            <th>Id</th>
            <th>Project Title</th>
            <th>Status</th>
            <th>File</th>
        </tr>
  </thead>
  <tbody>
        @foreach($reviewerProject as $rp)
        <tr>
            <td> {{$rp['id']}} </td>
            <td> {{$rp['title']}} </td>
            <td <?php 
            if($rp['status']=='Pending'){
              echo "style='color:LightBlue'";
            }
            else if($rp['status']=='Accepted'){
              echo "style='color:teal'";
            }
            ?>
            ><b> {{$rp['status']}}<b></td>
           <?php $p_id=$rp['id'] ?>
            <td><a href="{{route('grading',['p_id'=>$p_id])}}" class="w3-bar-item w3-button"><i class="fa fa-file" style="color:teal;"></i></a></td>
        </tr>
        @endforeach
  </tbody>
    </table>
</div>
@endif

<script type="text/javascript">
  $(document).ready(function () {
  $('#table_id').DataTable({
    paging: true,
    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    pageLength: 10,
    info: false
  });
  $('#table_id').css({
    'margin-right': '10%',
    'margin-left':'5%',
    'font-size':'11px',
    'padding':'0',
  });
});
  $(document).ready(function () {
  $('#table_id_length > label').css({
    'position': 'relative',
    'top': '2%',
    'left': '45%'
  });
});
  $(document).ready(function () {
  $('#table_id_filter > label').css({
    'margin-left': '0% ',
    'position': 'relative',
    'bottom': '30%',
    'right': '25%',
    'align':'center'
});
});
  $(document).ready(function () {
  $('#table_id_paginate').css({
    'position': 'relative',
    'bottom': '0.25%',
    'right': '50%'
});
});
</script>