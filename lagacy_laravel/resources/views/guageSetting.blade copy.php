<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  img {
    width: 200px;
    height: 300px;
    object-fit: cover;
  }

  .error {
    color: red;
    font-weight: bold;
  }

  .container {
    margin-left: 5%;
  }

  .heading {
    height: 7%;
    width: 80%;
    padding: 0.5%;
    margin-left: 10%;
    color: teal;
    margin-bottom: 3%;
  }

  td {
    padding: 2%;
    font-size: 80%;
  }

  .message {
    border-radius: 5px;
    border: 2px solid teal;
    background-color: #54BAB9;
    height: 7%;
    width: 80%;
    padding: 0.5%;
    margin-left: 10%;
    color: teal;
    font-weight: bold;
    color: beige;
  }
</style>

<body>
  @include('components.AnnouncementSideBar',['case'=>'0'])
  @include('components.navbar')
  <div id="chart_div" style="padding-left:4%">
   

  </div>
  <div class="container">
    <fieldset style="margin-left:5%;margin-right:5%;font-family:sans-serif;padding:15px;border-radius:5px;background:#f2f2f2;border:1px solid teal">
      <legend style="background:teal;color:beige;padding:5px 10px;font-size:12px;border-radius:5px;margin-left:20px;box-shadow:0 0 0 2px #ddd">Edit & Update User Performance Guage</legend>
      <form method="POST" action="{{route('guage')}}">
        @csrf
        <tag class="error">
          <?php if ($errors->any()) : ?>
            <?php echo $error ?>
          <?php endif; ?>
        </tag>
        <table>
          <colgroup>
            <col span="1" style="width: 15%;">
            <col span="1" style="width: 70%;">
          </colgroup>
          <tr>
            <td><label for="">red From</label></td>
            <td><input type="text" name="redFrom"></td>
          </tr>
          <tr>
            <td><label for="">red To</label></td>
            <td><input type="text" name="redTo"></td>
          </tr>
          <tr>
            <td><label for="">yellow From</label></td>
            <td><input type="text" name="yellowFrom"></td>
          </tr>
          <tr>
            <td><label for="">yellow To</label></td>
            <td><input type="text" name="yellowTo"></td>
          </tr>
          <tr>
            <td><label for="">green From</label></td>
            <td><input type="text" name="greenFrom">
            </td>
          </tr>
          <tr>
            <td><label for="">green To</label></td>
            <td><input type="text" name="greenTo">
            </td>
          </tr>
        </table>
        <div style="font-size:80%">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
  </div>
</body>
<script type="text/javascript">
   var avg =  44;
      google.charts.load('current', {'packages':['gauge']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Score',  44],
        ]);

        var options = {
          width: 300, height: 150,
          redFrom: <?php echo $redFrom ?>, redTo: <?php echo $redTo ?>,
          yellowFrom:<?php echo $yellowFrom ?>, yellowTo: <?php echo $yellowTo ?>,
          greenFrom:<?php echo $greenFrom ?>, greenTo:<?php echo $greenTo ?>,
          minorTicks: 10,
          max:<?php echo $greenTo ?>
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));

        chart.draw(data, options);

        setInterval(function() {
          data.setValue(0, 1,   44);
          chart.draw(data, options);
        });

      }
</script>
