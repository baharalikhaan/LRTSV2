<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="resources/css/dynamic.css"></script>

<style>
    table,
    td,
    th {
        border-bottom: 1px solid #ddd;
        font-size: 11px;
        text-align: left;
        padding-left: 1%;
    }

    tr:hover {
        background-color: beige;
    }
</style>

<fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
  <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Outcomes</legend>
  <form method="POST" action="{{route('projectOutcome2')}}">
    @csrf
    <table style="width:90%"><br>
      <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">2- Research Grants and Contracts</legend>
      <colgroup>
                <col span="1" style="width: 5%;">
                <col span="1" style="width: 70%;">
                <col span="1" style="width: 25%;">
      </colgroup>
      <tr>
        <th>#</th>
        <th>Outcomes</th>
        <th>Type</th>
      </tr>
      <tr>
        <td>1.</td>
        <td>Research Agency Funding</td>

        <td>
          <input type="radio" name="agencyFunding" value="None" checked="true">
          <label>None</label><br>
          <input type="radio" name="agencyFunding" value="Small">
          <label>Small</label><br>
          <input type="radio" name="agencyFunding" value="Medium">
          <label>Medium</label><br>
          <input type="radio" name="agencyFunding" value="Large">
          <label>Large</label>
        </td>
      </tr>
      <tr>
        <td>2.</td>
        <td>Industry Funded Research Services</td>

        <td>
          <input type="radio" name="indusFunding" value="None" checked="true">
          <label>None</label><br>
          <input type="radio" name="indusFunding" value="Small">
          <label>Small</label><br>
          <input type="radio" name="indusFunding" value="Medium">
          <label>Medium</label><br>
          <input type="radio" name="indusFunding" value="Large">
          <label>Large</label>
        </td>
      </tr>
    </table>
    <br>
    <div style="text-align:center">
      <button type="submit" class="btn btn-primary" position="center">
        Next
      </button>
    </div>
  </form>
</fieldset>

<script>
    
  function addFunction($name, $num) {
    var name = $name;
    var id = $num;
    var table = document.getElementById(id);
    var rowlen = table.rows.length;
    var row = table.insertRow(rowlen);
    row.id = rowlen;
    var arr = $name;
    for (i = 0; i < 2; i++) {
      var x = row.insertCell(i)
      if (i == 1) {
        x.innerHTML = "<input type='button' onclick='removeCell(" + row.id + ")' value= x >"
      } else {
        x.innerHTML = "<input type='textbox' name=" + arr + "[]>"
      }
    }
  }

  function removeCell(rowid) {
    var table = document.getElementById(rowid).remove();
  }

</script>