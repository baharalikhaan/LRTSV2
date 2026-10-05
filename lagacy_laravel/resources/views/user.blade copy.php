<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
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

  /* Add this to your CSS file or style section */
  .btn-teal {
    color: #FFFFFF;
    /* Replace with your specific teal color code */
    background-color: #00CED1;
    /* Replace with your specific teal color code */
    border-color: #00CED1;
    /* Replace with your specific teal color code */
  }

  .btn-teal:hover {
    color: #FFFFFF;
    /* Change text color on hover if needed */
    background-color: #008080;
    /* Change background color on hover if needed */
    border-color: #008080;
    /* Change border color on hover if needed */
  }

  #inside {
    margin-left: 5%;
    padding-left: 4%;
    margin-right: 5%;
    padding-right: 4%;
  }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div id='format'>
  @include('components.sidebarUser')
  @include('components.navbar')
  <br>
</div>
<br>
<br>

<div id="inside">
  <table id="table_id" class="table table-striped table-bordered">

    <thead>

      <tr>

        <th>User ID</th>
        <th>User Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $user)
      <tr>
        <td> {{$user['id']}} </td>
        <td>{{$user['name']}} </td>
        <td> {{$user['email']}} </td>
        <td> {{$user['type']}} </td>

        <td>
          <div class="btn-group" role="group" aria-label="User Actions">
            <a href="{{route('edit',$user['id'])}}" class="btn btn-teal btn-sm">Edit</a>
            <a href="{{ route('userDetail', ['u_id' => $user['id']]) }}" class="btn btn-success btn-sm">Details</a>
          </div>
        </td>

      </tr>
      @endforeach
    </tbody>
  </table>
</div>
</div>
<script type="text/javascript">
  $(document).ready(function() {
    $('#table_id').DataTable({
      paging: true,
      lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "All"]
      ],
      pageLength: 10, // Change this value according to your preference
      info: true,
      order: [[0, 'asc']] // Default sorting on the first column (User ID)
    });
  });
</script>
