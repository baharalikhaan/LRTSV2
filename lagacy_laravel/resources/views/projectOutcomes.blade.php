<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="resources/css/dynamic.css"></script>

<fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
  <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Outcomes</legend>
  <form method="POST" action="{{route('projectOutcomes')}}">
    @csrf
    <table id="table">
      <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Scholarly Outcomes</legend>
      <tr>
        <th>#</th>
        <th>Outcomes</th>
        <th></th>
        <th>List</th>
      </tr>
      <tr>

        <td>1.</td>
        <td> List the Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q1)</td>
        <td><input type="button" onclick="addFunction('q1','q1')" value="+" /></td>
        <td>
          <table id="q1">
          </table>
        </td>
      </tr>
      <tr>
        <td>2.</td>
        <td> List the Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q2) </td>
        <td><input type="button" onclick="addFunction('q2','q2')" value="+" /></td>
        <td>
          <table id="q2">
          </table>
        </td>
      </tr>
      <tr>
        <td>3.</td>
        <td> List the Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q3) </td>
        <td><input type="button" onclick="addFunction('q3','q3')" value="+" /></td>
        <td>
          <table id="q3">
          </table>
        </td>
      </tr>

      <tr>
        <td>4.</td>
        <td> List the Published articles in journals listed in Thomson Reuters Web of Science-SCI (Quartile in Category Q4) </td>
        <td><input type="button" onclick="addFunction('q4','q4')" value="+" /></td>
        <td>
          <table id="q4">
          </table>
        </td>
      </tr>

      <tr>
        <td>5.</td>
        <td> List the Public articles in indexed international conferences</td>
        <td><input type="button" onclick="addFunction('conf','conf')" value="+" /></td>
        <td>
          <table id="conf">
          </table>
        </td>
      </tr>

      <tr>
        <td>6.</td>
        <td> List any Published Books</td>
        <td><input type="button" onclick="addFunction('pubBook','pubBook')" value="+" /></td>
        <td>
          <table id="pubBook">
          </table>
        </td>
      </tr>

      <tr>
        <td>7.</td>
        <td> List any Edited Books (collection)</td>
        <td><input type="button" onclick="addFunction('editBook','editBook')" value="+" /></td>
        <td>
          <table id="editBook">
          </table>
        </td>
      </tr>
      <tr>
        <td>8.</td>
        <td> List any Book Chapters</td>
        <td><input type="button" onclick="addFunction('bookChap','BookChap')" value="+" /></td>
        <td>
          <table id="bookChap">
          </table>
        </td>
      </tr>
      <tr>
        <td>9.</td>
        <td> Have any Intellectual Property Disclosure form been submitted?</td>
        <td></td>
        <td>
          <select name="IP" id="IP">
            <option disabled selected value>-- select an option --</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>10.</td>
        <td> Have any Provisional Patent been filed?</td>
        <td></td>
        <td>
          <select name="FP" id="FP">
            <option disabled selected value>-- select an option --</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>11.</td>
        <td> Have any Patents been granted?</td>
        <td></td>
        <td>
          <select name="GP" id="GP">
            <option disabled selected value>-- select an option --</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>12.</td>
        <td> Have any Open Source Software been developed?</td>
        <td></td>
        <td>
          <select name="SW" id="SW">
            <option disabled selected value>-- select an option --</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>13.</td>
        <td> Have any Start-Up been created?</td>
        <td></td>
        <td>
          <select name="SUp" id="SUp">
            <option disabled selected value>-- select an option --</option>
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </td>
      </tr>
    </table>
    <table><br>
      <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Research Grants and Contracts</legend>
      <tr>
        <th>#</th>
        <th>Outcomes</th>
        <th>Type</th>
      </tr>
      <tr>
        <td>1.</td>
        <td>Research Agency Funding</td>

        <td>
          <input type="radio" name="agencyFunding" value="None">
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
          <input type="radio" name="indusFunding" value="None">
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
    <table><br>
      <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Academic and Professional Contribution</legend>
      <tr>
        <th>#</th>
        <th>Outcomes</th>
        <th>Type</th>
      </tr>
      <tr>
        <td>1.</td>
        <td>Inclusion of Student</td>

        <td>
          <input type="radio" name="agencyFunding" value="None">
          <label>None</label><br>
          <input type="radio" name="agencyFunding" value="UG">
          <label>Under Graduate</label><br>
          <input type="radio" name="agencyFunding" value="Masters">
          <label>Masters</label><br>
          <input type="radio" name="agencyFunding" value="PhD">
          <label>PhD</label>
        </td>
      </tr>
      <tr>
        <td>2.</td>
        <td>Industry Funded Research Services</td>

        <td>
          <input type="radio" name="indusFunding" value="None">
          <label>None</label><br>
          <input type="radio" name="indusFunding" value="Small">
          <label>Small</label><br>
          <input type="radio" name="indusFunding" value="Medium">
          <label>Medium</label><br>
          <input type="radio" name="indusFunding" value="Large">
          <label>Large</label>
        </td>
      </tr><br>
      <tr>
        <td>3.</td>
        <td>Is there any Cross College Participation?</td>
        <td>
          <input type="radio" name="colgPart" value="1">
          <label>Yes</label><br>
          <input type="radio" name="colgPart" value="0">
          <label>No</label><br>
        </td>
      </tr><br>
      <tr>
        <td>4.</td>
        <td>Research Awards</td>
        <td>
          <input type="radio" name="indusFunding" value="1">
          <label>Yes</label><br>
          <input type="radio" name="indusFunding" value="0">
          <label>No</label><br>
        </td>
      </tr><br>
    </table>

    <div style="text-align:center">
      <button type="submit" class="btn btn-primary" position="center">
        Submit
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
        x.innerHTML = "  " + [row.id] + "<input  type='textbox' name=" + arr + "[]>"
      }
    }
  }

  function removeCell(rowid) {
    var table = document.getElementById(rowid).remove();
  }
</script>
