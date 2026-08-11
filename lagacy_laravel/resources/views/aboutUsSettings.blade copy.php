<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<style>
#main{
    margin-left:5%;
    margin-top:2%;
}

table{
        margin-left: auto;
        margin-right: auto;
    }
table,
td,
th {
        border-bottom: 1px solid #ddd;
        font-size: 11px;
    }
th{
    text-align: center;
}
tr:hover {
        background-color: beige;
    }
.heading {
     height: 7%;
     width: 80%;
     padding: 0.5%;
     margin-left: 10%;
     color: teal;
     margin-bottom:3%;
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
@include('components.announcementSideBar',['case'=>'0'])
@include('components.navbar')
<div id='main'>
    <div class="heading" align="center">
      Cycle Settings
   </div>
    <table class="display nowrap" width="50%">
        <tr style="color:teal;">
            <th>#</th>
            <th><i class="fa fa-image"></i></th>
            <th>Name</th>
            <th>Role</th>
            <th>introduction</th>
            <th>Email</th>
            <th><i class="fa fa-pencil"></i></th>
        </tr>
        <tbody>
        @foreach($about as $about)
        <tr>
            <td> {{$about['id']}} </td>
            <td> {{$about['path']}} </td>
            <td> {{$about['name']}} </td>
            <td> {{$about['role']}} </td>
            <td> {{$about['introduction']}} </td>
            <td> {{$about['email']}} </td>
            <form action="{{route('aboutUsEdit',$about['id'])}}">
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>
            </form>
            <td></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

