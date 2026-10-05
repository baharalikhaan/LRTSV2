<style>
    table {
        margin-left: auto;
        margin-right: auto;
    }
    .my-float {
    margin-top: 20%;
  }
  .float {
    position: fixed;
    width: 9%;
    height: 15%;
    top: 23%;
    right: 3%;
    padding-top:2.5%;
    font-size:35;
    color: teal;
    border: 2px solid teal;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    text-align: center;
  }
    #outcomes,
    #outcomes.td,
    #outcomes.th {
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    tr:hover {
        background-color: beige;
    }

    .outcomes {
        margin-left: 10%;
    }
</style>
<fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:1%;border-radius:3%;border:1px solid teal;width:90%;">
    <legend style="background:teal;color:beige;padding:1% 1%;font-size:12px;border-radius:10%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Detailed Report of Project Outcomes</legend>
    <table style="width:100%;color:teal">
        <tr>
          <td>
            <img src="{{asset('storage/images/Qu logo.jpg')}}" width="75%" height="75%">
          </td>
          <td></td>
          <td>
            <h2>&nbsp; Project Title :</h2>
          </td>
          <td>
            <h2> {{$project->title}}</h2>
          </td>
          <td>
            <img src="{{asset('storage/images/ORGS logo.jpg')}}" width="50%" height="50%" align="right">
          </td>
        </tr>
      </table>
<br><br>
@if($project->status=='Completed')
  @include('components.completedProjectDetail',['outcomes'=>$outcomes])
@elseif(isset($outcomes))
    @include('components.acceptProjectOutcome',['outcomes'=>$outcomes])

<div id="circle-container">
    
                      <div class="float">
                      <legend style="background:teal;color:beige;padding:1% 1%;font-size:12px;border-radius:10%;margin-top:0px;box-shadow:0 0 0 2px #ddd">Expected Grades</legend>
                        {{$expected_sum}}</div>
                    </div>
@endif