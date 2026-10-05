
<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<style>
.chip {
  display: inline-block;
  padding: 0 25px;
  height: 50px;
  font-size: 18px;
  line-height: 50px;
  border-radius: 25px;
  background-color: #f1f1f1;
  margin-top: 10px;
}

.chip img {
  float: left;
  margin: 0 10px 0 -25px;
  height: 50px;
  width: 50px;
  border-radius: 50%;
}

.closebtn {
  padding-left: 10px;
  color: #888;
  font-weight: bold;
  float: right;
  font-size: 20px;
  cursor: pointer;
}

.closebtn:hover {
  color: #000;
}
</style>
</head>
<body>
<div style="margin: 10px;">
	<button  class="btn btn-sm btn-success addmore">+item</button>
</div>	

<div class="parent"></div>

<script> 
$(".addmore").click(function(){
  
	
  $(".parent").append('<div class="chip more_warapper"><img src="https://www.w3schools.com/howto/img_avatar.png" alt="Person" width="96" height="96">John Doe<span class="closebtn removebtn">&times;</span></div><br>');
});

$("body").on("click",".removebtn",function(){ 
    $(this).parents(".more_warapper").remove();
});
</script>

</body>
</html>