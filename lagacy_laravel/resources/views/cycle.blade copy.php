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
@include('components.announcementSideBar',['case'=>'2'])
@include('components.navbar')
<div id='main'>
    <div class="heading" align="center">
      Cycle Deadlines Settings
   </div>
    <table class="display nowrap" width="50%">
        <tr style="color:teal;">
            <th>#</th>
            <th>Progress Report</th>
            <th>Extension (Progress Report)</th>
            <th>Final Report</th>
            <th>Extension (Final Report)</th>
            <th>Upload Outcomes</th>
            <th>Status</th>
            <th><i class="fa fa-pencil"></i></th>
        </tr>
        <tbody>
        @foreach($cycle as $cycle)
        <tr>
            <td> {{$cycle['id']}} </td>
            <td> {{$cycle['prog_rpt_deadline']}} </td>
            <td> {{$cycle['extended_prog_rpt_deadline']}} </td>
            <td> {{$cycle['final_rpt_deadline']}} </td>
            <td> {{$cycle['extended_final_rpt_deadline']}} </td>
            <td> {{$cycle['upload_outcomes']}} </td>
            <td> {{$cycle['status']}} </td>
            <form action="{{route('cycleEdit',$cycle['id'])}}">
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>
            </form>
            <td></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

