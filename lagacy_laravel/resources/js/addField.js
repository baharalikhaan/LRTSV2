
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
        x.innerHTML = "  " + [row.id] + "<input type='textbox' name=" + arr + "[]>"
      }
    }
  }

  function removeCell(rowid) {
    var table = document.getElementById(rowid).remove();
  }
