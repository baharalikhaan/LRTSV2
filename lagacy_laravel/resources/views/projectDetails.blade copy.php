<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  * {
    box-sizing: border-box;
  }

  b,
  h2 {
    color: teal;
  }

  /* Create two unequal columns that floats next to each other */
  .column {
    float: left;
    padding: 1%;
    height: 100%;
  }

  .left {
    width: 75%;
  }

  .right {
    width: 25%;
  }

  /* Clear floats after the columns */
  .row:after {
    content: "";
    display: table;
    clear: both;
  }

  table {
    border: 2px solid black;
    font-size: 10;
    padding: 1%;
  }

  div,
  select {

    font-size: 12;
  }

  .button {
    font-size: 12;
    width: 50px;
    height: 18px
  }
</style>



@include('components.announcementSideBar',['case'=>'0'])
@include('components.navbar')
<h2 align="center">Project Title: {{$project->title}}</h2>

<div class="row">

  <div class="column left">
    <div id="Commitments" align="center">
      @if($commitments)
      <table>
        <colgroup>
          <col span="1" style="width: 5%;text-align:center">
          <col span="1" style="width: 70%;text-align:center">
          <col span="1" style="width: 10%; text-align:center">
        </colgroup>
        <td></td>
        <th style="color:teal;text-align:center">Commitments</th>
        <th style="color:teal;text-align:center">No.</th>
        <tr>
          <td>-</td>
          <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q1)
          </td>
          <td style="text-align:center">{{$commitments->q1article}}</td>
        <tr>
          <td>-</td>
          <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q2)
          </td>
          <td style="text-align:center">{{$commitments->q2article}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q3)</td>
          <td style="text-align:center">{{$commitments->q3article}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q4)</td>
          <td style="text-align:center">{{$commitments->q4article}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Public articles in indexed international conferences </td>
          <td style="text-align:center">{{$commitments->confArticle}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Published Books</td>
          <td style="text-align:center">{{$commitments->books}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>
            Edited Books (collection)
          </td>
          <td style="text-align:center">{{$commitments->editBooks}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>
            Book Chapters
          </td>
          <td style="text-align:center">{{$commitments->chapters}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Submitted IP Disclosure form submitted</td>
          <td style="text-align:center">{{$commitments->ip}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Filed Provisional Patent</td>
          <td style="text-align:center">{{$commitments->filedPatent}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Open Source Software</td>
          <td style="text-align:center">{{$commitments->openSourceSW}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Created a Start-Up
          <td style="text-align:center">{{$commitments->startUp}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>No. of UnderGrad Students Involved in Project
          <td style="text-align:center">{{$commitments->UG}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>No. of Masters Students Involved in Project
          <td style="text-align:center">{{$commitments->master}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>No. of PhD Student Involved in Project
          <td style="text-align:center">{{$commitments->Phd}}</td>
        </tr>
        <tr>
          <td>-</td>
          <td>Cross College Participation
          <td style="text-align:center">{{$commitments->startUp}}</td>
        </tr>

      </table>
      @else
      <b class='alert'>Commitments for this project are not avaialable</b> <br>
      <br>
      @endif
    </div>
    <br>
    <div class='authors' , height="15%" width="100%" border='red'>
      <b> Authors contributing in this Project </b>
      <br><br>
      <table>
        <tr>
          <th>Authors</th>
          <th>Email</th>
        </tr>
        @foreach($stakeholder as $stakeholder)
        <tr>
          <td>{{$stakeholder['name']}}</td>
          <td>{{$stakeholder['email']}}</td>
        </tr>
        @endforeach
      </table>
    </div>
    <br><br>
    @if($reviewers)
    <div class='reviewers' , height="30%" width="100%" border='red'>
      <b> Reviewers of the Project </b><br><br>
      <table>
        <tr>
          <th>Reviewers</th>
          <th>Email</th>
        </tr>
        @foreach($reviewers as $reviewer)
        <tr>
          <td>{{$reviewer['name']}}</td>
          <td>{{$reviewer['email']}}</td>
        </tr>
        @endforeach
      </table>

    </div>
    @else
    <div>
      <b> Reviewers of the Project </b><br>
      <form method="POST" action="{{route('assignReviewer',['p_id'=>$project->id])}}">
        @csrf
        <br><label> Select Reviewer<br><br>
          <select name="reviewerA" id="reviewerA">
            <option value='' disabled selected>Assign Reviewer</option>
            @foreach($list as $row)
            <?php $id = $row->id ?>
            <option value={{$id}}>{{$row->email}}</option>
            @endforeach
          </select>
          <nbr>
            <select name="reviewerB" id="reviewerB">
              <option value='' disabled selected>Assign Reviewer</option>
              @foreach($list as $row)
              <?php $id = $row->id ?>
              <option value={{$id}}>{{$row->email}}</option>
              @endforeach
            </select>
            <br><br>
            <input class="button" type="submit" value="Submit">
      </form>
    </div>
    @endif
  </div>
  <div class="column right">
    @if($pillars)
    <div id='pillars' , height="50%" width="100%" padding="20%">
      <b> Pillars of the Project </b>
      <table>
        <tr>
          <th>ID</th>
          <th>Pillars</th>
        </tr>
        @foreach($pillars as $pillar)
        <tr>
          <td>{{$pillar['pillar_id']}}</td>
          <td>{{$pillar['pillar']}}</td>
        </tr>
        @endforeach
      </table>
    </div>
    @else
    <div>

      <form method="POST" action="{{route('updateProjectPillar',['p_id'=>$project->id])}}">
        @csrf
        <br>
        <b> Pillars of the Project </b>
        <br>
        <input type="checkbox" id="pillar1" name="pillar[]" value="4">
        <label for="pillar1">Social Sciences and Humanities</label><br>
        <input type="checkbox" id="pillar2" name="pillar[]" value="3">
        <label for="pillar2"> Information and Communication Technology</label><br>
        <input type="checkbox" id="pillar3" name="pillar[]" value="2">
        <label for="pillar3"> Health and Biomedical Sciences</label><br>
        <input type="checkbox" id="pillar4" name="pillar[]" value="1">
        <label for="pillar4"> Energy and Environment</label><br><br>
        <input class="button" type="submit" value="Submit">
      </form>
    </div>
    @endif
    <br>

    @if($tags)

    <div id='tags' , height="50%" width="100%">
      <b> College Affiliation of the Project </b>
      <table>
        <tr>
          <th>ID</th>
          <th>College</th>
        </tr>
        @foreach($tags as $tag)
        <tr>
          <td>{{$tag['tag_id']}}</td>
          <td>{{$tag['tag']}}</td>
        </tr>
        @endforeach
      </table>
    </div>
    @else
    <div>
      <form method="POST" action="{{route('updateProjectTag',['p_id'=>$project->id])}}">
        @csrf
        <br>
        <b> Tags of the Project </b>
        <br><br>
        <input type="checkbox" id="tag11" name="tag[]" value="11">
        <label for="tag11"> College of Nursing</label><br>
        <input type="checkbox" id="tag10" name="tag[]" value="10">
        <label for="tag10"> College of Dental Medicine</label><br>
        <input type="checkbox" id="tag9" name="tag[]" value="9">
        <label for="tag9"> College of Sharia and Islamic Studies </label><br>
        <input type="checkbox" id="tag8" name="tag[]" value="8">
        <label for="tag8">College of Pharmacy</label><br>
        <input type="checkbox" id="tag7" name="tag[]" value="7">
        <label for="tag7"> College of Medicine</label><br>
        <input type="checkbox" id="tag6" name="tag[]" value="6">
        <label for="tag6"> College of Law</label><br>
        <input type="checkbox" id="tag5" name="tag[]" value="5">
        <label for="tag5"> College of Health Sciences</label><br>
        <input type="checkbox" id="tag4" name="tag[]" value="4">
        <label for="tag4">College of Engineering</label><br>
        <input type="checkbox" id="tag3" name="tag[]" value="3">
        <label for="tag3"> College of Education</label><br>
        <input type="checkbox" id="tag2" name="tag[]" value="2">
        <label for="tag2"> College of Business and Economics</label><br>
        <input type="checkbox" id="tag1" name="tag[]" value="1">
        <label for="tag1"> College of Arts and Sciences</label><br><br>

        <input class="button" type="submit" value="Submit">
      </form>
    </div>
    @endif
  </div>
</div>

<br>