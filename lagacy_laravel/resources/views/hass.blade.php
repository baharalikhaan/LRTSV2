<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="resources/css/dynamic.css"></script>

<fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
  <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Project Outcomes</legend>
  <form method="POST" action="{{route('hassOutcomes')}}">
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
        <td> List the articles published in non-indexed journals</td>
        <td><input type="button" onclick="addFunction('non', 'non', ['Title', 'URL', 'Publication Date', 'Venue'])" value="+" /></td>
        <td>
          <table id="non">
          </table>
        </td>
      </tr>

      <tr>
        <td>2.</td>
        <td> List any Published Books</td>
        <td><input type="button" onclick="addFunction('pubBook', 'pubBook', ['Title', 'URL', 'Publication Date', 'Venue'])" value="+" />
        </td>
        <td>
          <table id="pubBook">
          </table>
        </td>
      </tr>

      <tr>
        <td>3.</td>
        <td> List any Edited Books (collection)</td>
        <td><input type="button" onclick="addFunction('editBook', 'editBook', ['Title', 'URL', 'Publication Date', 'Venue'])" value="+" />
</td>
        <td>
          <table id="editBook">
          </table>
        </td>
      </tr>
      <tr>
        <td>4.</td>
        <td> List any Book Chapters</td>
        <td><input type="button" onclick="addFunction('bookChap', 'bookChap', ['Title', 'URL', 'Publication Date', 'Venue'])" value="+" />
</td>
        <td>
          <table id="bookChap">
          </table>
        </td>
      </tr>
      <tr> 
      
    </table>  

    <div style="text-align:center">
      <button type="submit" class="btn btn-primary" position="center">
        Submit
      </button>
    </div>
  </form>
</fieldset>

<script>
function addFunction(arrayName, id, fields) {
    var index = $("#" + arrayName + " tr").length;
    var row = "<tr>";

    for (var i = 0; i < fields.length; i++) {
        row += "<td>" + fields[i] + ": <input type='text' name='" + id + "_" + fields[i] + "[]' required/></td>";
    }

    row += "<td><input type='button' value='x' onclick='removeCell(\"" + arrayName + "\", " + index + ")'/></td></tr>";

    $("#" + arrayName).append(row);
}




  function removeCell(rowid) {
    var table = document.getElementById(rowid).remove();
  }
</script>