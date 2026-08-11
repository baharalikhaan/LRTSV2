<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<style>
    * {
        box-sizing: border-box
    }

    body,
    html {
        height: 100%;
        margin: 0;
        font-family: Arial;
    }

    /* Style tab links */
    .tablink {
        background-color: #66b2b2;
        color: beige;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        font-size: 17px;
        font-weight: bold;
        width: 50%;
    }

    .tablink:hover {
        background-color: #004c4c;
    }

    /* Style the tab content (and add height:100% for full page content) */
    .tabcontent {
        display: none;
        padding: 100px 20px;
        height: 100%;
    }

    #Commitments {
        background-color: white;
    }
    #Outcomes {
        background-color: white;
    }
    table,
    th,
    tr,
    td {
        border-bottom: 1px solid #ddd;
        text-align: center;
        text-align: left;
        padding: 0.2%;
    }

    .column1 {
        float: left;
        width: 66%;
        padding: 1%;
        border: 1px solid teal;
        height: 200%
    }

    .column2 {
        font-size: 60%;
        color: teal;
        float: left;
        width: 33%;
        padding: 2%;
        height: 100%,
    }

    .alert {
        color: red;
    }

    /* Clear floats after the columns */
    .row:after {
        content: "";
        display: table;
        clear: both;
    }
</style>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <button class="tablink" onclick="openPage('Commitments', this,'teal')" id="defaultOpen">Commitments</button>
    <button class="tablink" onclick="openPage('Outcomes', this, 'teal')">Outcomes</button>

    <div id="Commitments" class="tabcontent" align="center">
        @if($commitments)
        <h2 style="color:teal;">Project Title: {{$project->title}}</h2>
        <table>
      <colgroup>
        <col span="1" style="width: 5%;text-align:center">
        <col span="1" style="width: 70%;text-align:center">
        <col span="1" style="width: 10%; text-align:center">
      </colgroup>
      <td></td>
      <th style="color:teal;text-align:center">Commitments</th>
      <th style="color:teal;text-align:center">No.</th>
      <th style="color:teal;text-align:center">Score.</th>
      <tr>
        <td>-</td>
        <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q1)
        </td>
        <td style="text-align:center">{{$commitments->q1article}}</td>
        <td style="text-align:center">8</td>
      <tr>
        <td>-</td>
        <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q2)
        </td>
        <td style="text-align:center">{{$commitments->q2article}}</td>
        <td style="text-align:center">6</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q3)</td>
        <td style="text-align:center">{{$commitments->q3article}}</td>
        <td style="text-align:center">4</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q4)</td>
        <td style="text-align:center">{{$commitments->q4article}}</td>
        <td style="text-align:center">3</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Public articles in indexed international conferences </td>
        <td style="text-align:center">{{$commitments->confArticle}}</td>
        <td style="text-align:center">2</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Published Books</td>
        <td style="text-align:center">{{$commitments->books}}</td>
        <td style="text-align:center">8</td>
      </tr>
      <tr>
        <td>-</td>
        <td>
          Edited Books (collection)
        </td>
        <td style="text-align:center">{{$commitments->editBooks}}</td>
        <td style="text-align:center">6</td>
      </tr>
      <tr>
        <td>-</td>
        <td>
          Book Chapters
        </td>
        <td style="text-align:center">{{$commitments->chapters}}</td>
        <td style="text-align:center">4</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Submitted IP Disclosure form submitted</td>
        <td style="text-align:center">{{$commitments->ip}}</td>
        <td style="text-align:center">4</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Filed Provisional Patent</td>
        <td style="text-align:center">{{$commitments->filedPatent}}</td>
        <td style="text-align:center">7</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Open Source Software</td>
        <td style="text-align:center">{{$commitments->openSourceSW}}</td>
        <td style="text-align:center">8</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Created a Start-Up
        <td style="text-align:center">{{$commitments->startUp}}</td>
        <td style="text-align:center">10</td>
      </tr>
      <tr>
        <td>-</td>
        <td>No. of UnderGrad Students Involved in Project
        <td style="text-align:center">{{$commitments->UG}}</td>
        <td style="text-align:center">1</td>
      </tr>
      <tr>
        <td>-</td>
        <td>No. of Masters Students Involved in Project
        <td style="text-align:center">{{$commitments->master}}</td>
        <td style="text-align:center">2</td>
      </tr>
      <tr>
        <td>-</td>
        <td>No. of PhD Student Involved in Project
        <td style="text-align:center">{{$commitments->Phd}}</td>
        <td style="text-align:center">3</td>
      </tr>
      <tr>
        <td>-</td>
        <td>Cross College Participation
        <td style="text-align:center">{{$commitments->startUp}}</td>
        <td style="text-align:center">2</td>
      </tr>
    </table>
        @else
        <b class='alert'>Commitments for this project are not avaialable</b> <br>
        <br>
        @endif
    </div>

    <div id="Outcomes" class="tabcontent" align="center">
        @if($outcomes)
        <table id="position">
        <colgroup>
                <col span="1" style="width: 18%;">
                <col span="1" style="width: 50%;">
                <col span="2" style="width: 10%; text-align:center">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 10%;">
                <col span="1" style="width: 5%;">
        </colgroup>
        <thead style="text-align:center;color:teal;font-weight:bold;" >
          <tr>
            <th scope="col"  style="text-align:center">Identifier</th>
            <th scope="col"  style="text-align:center">Title</th>
            <td scope="col"  style="text-align:center">Date</th>
            <td scope="col"  style="text-align:center">Venue</th>
            <td scope="col"  style="text-align:center">Type</th>
            <td scope="col"  style="text-align:center">Score</th>
          </tr>
        </thead>
        <tbody style="text-align:left">
          @foreach($outcomes as $outcome)
          <tr>
            <td> {{$outcome->identifier}} </td>
            <td><a href="{{$outcome->url}}"> {{$outcome->title}} </a> </td>
            <td> {{$outcome->publication_date}} </td>
            <td> {{$outcome->venue}} </td>
            <td> {{$outcome->type}}</td>
            <td style="text-align:center"> {{$outcome->score}} </td>
          </tr>
          @endforeach
        </tbody>
      </table>
        @endif
<br><br>
        @if($contribution)
        <table id="position">
        <colgroup>
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 20%;">
                <col span="2" style="width: 70%; text-align:center">
                <col span="1" style="width: 15%;">
        </colgroup>
        <thead style="text-align:center;color:teal;font-weight:bold;" >
          <tr>
          <th scope="col"  style="text-align:center"> </th>
            <th scope="col"  style="text-align:center">Type</th>
            <th scope="col"  style="text-align:center">Details</th>
            <th scope="col"  style="text-align:center">Score</th>
          </tr>
        </thead>
        <tbody style="text-align:left">
          @foreach($contribution as $contribution)
          <tr>
            <td><b>-</b></td>
            <td> {{$typeMappings[$contribution->type] ?? $contribution->type}} </td>
            <td> {{$contribution->detail}} </td>
            <td style="text-align:center">{{$contribution->score}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
        @endif

        <br><br>
        @if($students)
        <table id="position">
        <colgroup>
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 20%;">
                <col span="2" style="width: 20%;">
                <col span="1" style="width: 15%;">
                <col span="1" style="width: 15%;">
        </colgroup>
        <thead style="text-align:center;color:teal;font-weight:bold;" >
          <tr>
          <th scope="col"  style="text-align:center"> </th>
            <th scope="col"  style="text-align:center">ID</th>
            <th scope="col"  style="text-align:center">Level</th>
            <th scope="col"  style="text-align:center">Days of Working</th>
            <th scope="col"  style="text-align:center">Score</th>
          </tr>
        </thead>
        <tbody style="text-align:left">
          @foreach($students as $student)
          <tr>
            <td><b>-</b></td>
            <td> {{$student->std_id}} </td>
            <td>{{$typeMappings[$student->type] ?? $student->type}} </td>
            <td style="text-align:center"> {{$student->days}} </td>
            <td style="text-align:center">{{$student->score}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
        @endif      
    </div>

    <script>
        function openPage(pageName, elmnt, color) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablink");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].style.backgroundColor = "";
            }
            document.getElementById(pageName).style.display = "block";
            elmnt.style.backgroundColor = color;
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
    </script>

</body>