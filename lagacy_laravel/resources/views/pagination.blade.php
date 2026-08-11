<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.13.2/datatables.min.js"></script>

<table id="table_id" class="display" style="background-color: aliceblue; ">
  <thead>
    <tr>
      <th>Column 1</th>
      <th>Column 2</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Row 1 Data 1</td>
      <td>Row 1 Data 1</td>
    </tr>
    <tr>
      <td>Row 1 Data 1</td>
      <td>Row 2 Data 2</td>
    </tr>
  </tbody>
</table>

<script type="text/javascript">
  $(document).ready(function () {
  $('#table_id').DataTable({
    paging: true,
    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
    pageLength: 10,
    info: false
  });
  $('#table_id').css({
    'margin': '25% auto'
  });
});
  $(document).ready(function () {
  $('#table_id_length > label').css({
    'margin': '100%',
    'position': 'relative',
    'top': '2%',
    'left': '3%'
  });
});
  $(document).ready(function () {
  $('#table_id_filter > label').css({
    'margin': '10% ',
    'position': 'relative',
    'bottom': '30%',
    'right': '40%'
});
});
  $(document).ready(function () {
  $('#table_id_paginate').css({
    'position': 'relative',
    'bottom': '0.25%',
    'right': '40%'
});
});
</script>