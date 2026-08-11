<head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.h2{
  font-weight: bold;
  margin: auto;
}
*{
  box-sizing: border-box;
}
.column1 {
  float: left;
  width:10%; /* Should be removed. Only for demonstration */
}
/* Create three equal columns that floats next to each other */
.column {
  float: left;
  width: 40%;
  padding: 1% /* Should be removed. Only for demonstration */
}

.row:after {
  content: "";
  display: table;
  clear: both;
}
#Dashboard{
  padding-left: 5%;
}
</style>
</head>

@include('components.sidebar')
@include('components.navbar')
<body>
<div id="Dashboard">
  <div class="row">
    <div class="column1" >
      <div class="first" style="background-color:	#FFFF8F;height:33%">
        <h2 align="center">{{$cycle}}</h2>
        <p>No. of active Cycle</p>
      </div>
      <div class="second" style="background-color:#FBCEB1;">
        <h2>{{$projects}}</h2>
        <p>No. of Projects</p>
      </div>
      <div class="third" style="background-color:#EADDCA">
        <h2>{{$users}}</h2>
        <p>No. of Users</p>
      </div>
    </div>
    <div class="column" style="background-color:#aaa;">
      <div height="33%">
        <canvas id="myChart" height="100%"></canvas>
      </div>
    </div>
    <div class="column" style="background-color:#ddd;">
      <div>
        <canvas id="myChart" height="90px"></canvas>
      </div>
    </div>
  </div>
</div>
</body>

<script type="text/javascript">
  var labels =  {{ Js::from($labels) }};
     var users =  {{ Js::from($data) }};


      const data = {
        labels: labels,
        datasets: [{
          label: 'My First dataset',
          backgroundColor: 'rgb(255, 99, 132)',
          borderColor: 'rgb(255, 99, 132)',
          data: users,
        }]
      };
  
      const config = {
        type: 'line',
        data: data,
        options: {}
      };
  
      const myChart = new Chart(
        document.getElementById('myChart'),
        config
      );
</script>