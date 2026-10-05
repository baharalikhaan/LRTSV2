<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.js"></script>
<style>
#parent {
    position: relative;
  }
.float{
	position:fixed;
	width:3%;
	height:6%;
	bottom:3%;
	right:3%;
	background-color:teal;
	color:#FFF;
	border-radius:50%;
  text-align: center;
}
.my-float{
	margin-top:20%;
}
  table{
        margin-left: 20%;
        margin-right: 20%;
    }
  table,
  td{
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

.message{
 border-radius: 5px;
 border:2px solid lightseagreen;
 background-color: lightblue;
 height: 9%;
 width:80%;
 padding:0.5%;
 margin-left:10%;
 color:teal;
 font-weight: bold;
}
#inside{
  margin-right:5%;
  padding:4%;
}
.button {
  display: inline-block;
  padding:4%;
  font-size: 100%;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  outline: none;
  color: #fff;
  background-color: #4CAF50;
  border: none;
  border-radius: 8%;
}

.button:hover {
    background-color: #3e8e41
    }

.button:active {
  background-color: #3e8e41;
  transform: translateY(4px);
    }

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div id='format'>

@include('components.navbar')
<br>
</div>
<br>
<br>
<div id="inside">
<table id="table_id" class="display">
<thead>
        <tr>
        
            <th>Id</th>
            <th>User Name</th>
            <th>email</th>
            <th></th>
            <th></th>
        </tr>
</thead>
<tbody>
        @foreach($users as $user)
        <tr>
            <td> {{$user['id']}} </td>
            <td> {{$user['name']}} </td>
            <td> {{$user['email']}} </td>
            <td> {{$user['type']}} </td>
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>

        </tr>
        @endforeach
    </tbody>
    </table>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
  $('#table_id').DataTable({
    paging: true,
    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    pageLength: 10,
    info: false,
  });
  $('#table_id').css({
    'margin-right': '90%',
    'margin-left':'6%',
    'font-size':'8px',
    'padding':'0',
  });
});
  $(document).ready(function () {
  $('#table_id_length > label').css({
    'position': 'relative',
    'top': '2%',
    'left': '90%',
    'right':'40%',
  });
});
  $(document).ready(function () {
  $('#table_id_filter > label').css({
    'margin-left': '0%',
    'position': 'relative',
    'bottom': '30%',
    'left':'1%',
    'right': '2%',
    'align':'center'
});
});
  $(document).ready(function () {
  $('#table_id_paginate').css({
    'position': 'relative',
    'bottom': '0.25%',
    'right': '20%'
});
});
</script>