<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
  .grades {
    margin-left: auto;
    margin-right: auto;
  }
  .body {
  background-image:url('storage/images/background2.jpg');
  background-repeat: no-repeat;
  background-size: 100%;}
  table,
  td,
  th {
    border-bottom: 1px solid #ddd;
    text-align: left;
    font-size: 15;
  }

  th,
  h2,
  h3 {
    color: teal;
  }
  #circle-container {
  position: fixed;
  top: 2%;
  right: 2%;
}

#number-circle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 220%;
  height: 220%;
  margin: 5%;
  border-radius: 50%;
  font-weight: bold;
}
  #base {
    padding: 1%;
    margin: 5%;
    margin-top: 1%;
  }

  #bottom {
    position: absolute;
    bottom: 0;
    left: 0;
    color: #CC7722;
  }
  #next {
    position: absolute;
    bottom: 0;
    right: 0%;
  }

  #parent {
    position: relative;
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

  .my-float {
    margin-top: 20%;
  }
  .button {
  display: inline-block;
  padding:4%;
  font-size: 100%;
  cursor: pointer;
  text-align: center;
  font-weight: bold;
  outline: none;
  color: beige;
  background-color: #FFA500;
  border: none;
  border-radius: 8%;
}

.button:hover {
    background-color: #ff6600
    }

.button:active {
  background-color: #ff781f;
  transform: translateY(4px);
    }
</style>

<body class="body">
  <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:1%;border-radius:3%;border:1px solid teal;width:90%;">
    <legend style="background:teal;color:beige;padding:1% 1%;font-size:12px;border-radius:10%;margin-left:20px;box-shadow:0 0 0 2px #ddd">Detailed Report of Project Evaluation</legend>

    <div id="base">
      <table style="width:100%">
        <tr>
          <td>
            <img src="{{asset('storage/images/Qu logo.jpg')}}" width="75%" height="75%">
          </td>
          <td></td>
          <td>
            <h2>&nbsp; Project Title :</h2>
          </td>
          <td>
            <h2>{{$project->title}}</h2>
          </td>
          <td>
            <img src="{{asset('storage/images/ORGS logo.jpg')}}" width="50%" height="50%" align="right">
          </td>
        </tr>
      </table>
      @if($progressGrades)
      <h3 align="center">Progress Report Remarks</h3>
      <br><br>
      @foreach($progressGrades as $pg)
      <table style="width:50%">
        <colgroup>
          <col span="1" style="width: 25%;">
          <col span="1" style="width: 25%;">
        </colgroup>
        <tr>
          <td>
            <b>Evaluated By: {{$pg['name']}}</b>
          </td>
          <td>
            &emsp; Contact: {{$pg['email']}}
          </td>
        </tr>
      </table>
      <br>
      <table class='grades' style="width:100%" align="left">
        <colgroup>
          <col span="1" style="width: 5%;">
          <col span="1" style="width: 15%;">
          <col span="1" style="width: 80%;">
        </colgroup>
        <tr>
          <th>#</th>
          <th>Criteria</th>
          <th>Comment</th>
        </tr>
        <tr>
          <td>1</td>
          <td style="color:teal;">Analysis:</th>
          <td>{{$pg['analysis']}}</td>
        </tr>
        <tr>
          <td>2</td>
          <td style="color:teal;">Comments:</td>
          <td>{{$pg['comments']}}</td>
        </tr>
        <tr>
          <td>3</td>
          <td style="color:teal;">Recommendations:</td>
          <td>{{$pg['recommendation']}}</td>
        </tr>
      </table>
      @endforeach
      <br>
      <br>
      <br>
      <br>
      <br>
      @endif

      @if($finalGrades)
      <h3 align="center">Final Report Evaluation</h3>
      <br>
      @foreach($finalGrades as $fg)
      <table style="width:50%">
        <colgroup>
          <col span="1" style="width: 25%;">
          <col span="1" style="width: 25%;">
        </colgroup>
        <tr>
          <td>
            <b>Evaluated By: {{$fg['name']}}</b>
          </td>
          <td>
            &emsp; Contact: {{$fg['email']}}
          </td>
        </tr>
      </table>
      <br>
      <table class='grades' style="width:100%">
        <colgroup>
          <col span="1" style="width: 5%;">
          <col span="1" style="width: 20%;">
          <col span="1" style="width: 15%;">
          <col span="1" style="width: 60%;">
        </colgroup>
        <tr>
          <th>#</th>
          <th>Criteria</th>
          <th scope="col" style="text-align:center;">Scores</th>
          <th scope="col">Comments</th>
        </tr>
        <tr>
          <td>1</td>
          <td style="color:teal;">Results and Outcomes</td>
          <td style="text-align:center;">{{$fg['gradeA']}}</td>
          <td>{{$fg['commentA']}}</td>
        </tr>
        <tr>
          <td>2</td>
          <td style="color:teal;">Publications</td>
          <td style="text-align:center;"">{{$fg['gradeB']}}</td>
          <td>{{$fg['commentB']}}</td>
         </tr>
        <tr>
          <td>3</td>
          <td style=" color:teal;">Yound Researcher Supervision</td>
          <td style=" text-align:center;">{{$fg['gradeD']}}</td>
          <td>{{$fg['commentD']}}</td>
        </tr>
        <tr>
          <td>4</td>
          <td style="color:teal;">Project:</td>
          <td style="text-align:center;">{{$fg['gradeC']}}</td>
          <td>{{$fg['commentC']}}</td>
        </tr>
      </table>
      <br>
      @endforeach
      @endif
      <br><br>
      <table id='sum' align='right' style="text-align:left;">
                    <div id="circle-container">
                      <div class="float">
                    <legend style="background:teal;color:beige;padding:1% 1%;font-size:12px;border-radius:10%;margin-top:0px;box-shadow:0 0 0 2px #ddd">Average Grades</legend>

                        {{$avg->avg}}</div>
                    </div>
        <tr>
          <th>Sum of Grades </th>
          <td>&nbsp;</td>
          <td><i>{{$sum->sum}}</i></td>
        </tr>
        <tr>
          <th>Average of Grades </th>
          <td>&nbsp;</td>
          <td><i>{{$avg->avg}}</i></td>
        </tr>
      </table>
    </div>
    <div id="parent">
      <div id="bottom">
        &emsp;&emsp;&emsp;&emsp; &emsp;Some confidentiality note here
      </div>


</body>
