<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  * {
    box-sizing: border-box;
  }

  /* Create two unequal columns that floats next to each other */
  .column {
    float: left;
    padding: 1%;
    height: 100%;
    /* Should be removed. Only for demonstration */
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

  div {
    border: 2px solid black;
    font-size: 10;
    padding: 1%;
  }
</style>

<h2>Two Unequal Columns</h2>

<div class="row">
  <div class="column left">
    <div class="frame">
      <iframe src="" height="70%" width="100%" align="center" title="abcd"></iframe>
    </div>
    <div class='authors' , height="15%" width="100%" border='red'>
      Co-authors
      <p>asa</p>
    </div>
    <div class='reviewers' , height="30%" width="100%" border='red'>
      Reviewers
    </div>
  </div>
  <div class="column right">
    <div id='pillars' , height="50%" width="100%" padding="5%">
      <p>Energy and Environment</p>
      <p> and Biomedical Sciences</p>
      <p>Information and Communication Technology</p>
      <p>Social Sciences and Humanities</p>

    </div>
    <div id='tags' , height="50%" width="100%">
      <p>College of Arts and Sciences</p>
      <p>College of Business and Economics</p>
      <p>College of Education</p>
      <p>College of Engineering</p>
      <p>College of Health Sciences</p>
      <p>College of Law</p>
      <p>College of Medicine</p>
      <p>College of Pharmacy</p>
      <p>College of Sharia and Islamic Studies </p>
      <p>College of Dental Medicine</p>
    </div>
  </div>
</div>

<br>

<form method="POST" action="{{route('print')}}">
  @csrf
  <label for="cars">Choose a car:</label>
  <select name="cars[]" id="cars" multiple="multiple">
    <option value="volvo">Volvo</option>
    <option value="saab">Saab</option>
    <option value="opel">Opel</option>
    <option value="audi">Audi</option>
  </select>
  <br><br>
  <input type="submit" value="Submit">
</form>