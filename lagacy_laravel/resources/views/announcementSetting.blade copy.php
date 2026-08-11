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
@include('components.announcementSideBar',['case'=>'1'])
@include('components.navbar')
<div id='main'>
    <div class="heading" align="center">
      Announcement Settings
   </div>
    <table class="display nowrap" width="50%">
        <tr style="color:teal;">
            <th style="display:none;">Id</th>
            <th>Subject</th>
            <th>Content</th>
            <th>Audience</th>
            <th>visibilty</th>
            <th><i class="fa fa-pencil"></i></th>
        </tr>
        <tbody>
        @foreach($announcements as $announcement)
        <tr>
            <td style="display:none;"> {{$announcement['id']}} </td>
            <td> {{$announcement['subject']}} </td>
            <td> {{$announcement['content']}} </td>
            <td> {{$announcement['type']}} </td>
            <td> {{$announcement['status']}} </td>
            <form action="{{route('announcementEdit',$announcement['id'])}}">
            <td><button class="button" type="submit">&nbsp;&nbsp;Edit&nbsp;&nbsp;</button></td>
            </form>
            <td></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

