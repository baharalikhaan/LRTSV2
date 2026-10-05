<style>
  table,
  tr,
  th,
  td {
    border-bottom: 1px solid #ddd;
    font-size: 14;
  }
</style>

<input type="text" style="display:none" name="p_id" value={{$p_id}}>
<table id="position">
  <colgroup>
    <col span="1" style="width: 8%;">
    <col span="1" style="width: 50%;">
    <col span="2" style="width: 10%; text-align:center">
  </colgroup>
  <thead style="text-align:center;color:teal;font-weight:bold;">
    <tr>
      <th scope="col" rowspan="2" style="text-align:center">Type</th>
      <th scope="col" rowspan="2" style="text-align:center">Identifier</th>
      <td scope="col" colspan="4" style="text-align:center">Status</th>
    </tr>

  </thead>
  <tbody style="text-align:left">
    @foreach($verify_outcomes as $outcome)
    <tr>
      <td style="display:none;"> {{$outcome['id']}} </td>
      <td> {{$outcome['type']}} </td>
      <td> {{$outcome['identifier']}} </td>
      <td> {{$outcome['status']}} </td>
    </tr>
    @endforeach
  </tbody>
</table>