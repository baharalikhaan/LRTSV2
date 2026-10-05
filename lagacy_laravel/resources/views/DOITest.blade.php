<?php
$rows = array(
//These are just sample doi which I have put, You can put your own DOI in here.
  array('doi' => 'example1'),   
  array('doi' => 'example2'),
  array('doi' => 'example3')
);
?>

<table>
  <tr>
    <th>DOI</th>
    <th>Results</th>
  </tr>
  <?php
  // this will loop through all the rows of the table.
  foreach ($rows as $row) {
    $doi = $row['doi'];

    // To send requesst to API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://put-the-api-over-here/api?doi=$doi");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse the API response and update the table
    $result = json_decode($response, true)['result'];
    echo "<tr><td>$doi</td><td>$result</td></tr>";
  }
  ?>
</table>
