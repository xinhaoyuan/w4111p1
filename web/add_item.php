<?php

session_start();
require_once "../bin/backend.php";
require_once "../bin/common.php";
$postInfo = "";
if(!isset($_SESSION['email'])){
	header( "Location: login.php" );
}
$b = Backend::instance();
$email = $_SESSION['email'];
$r = $_SESSION['session'];
$sk = $r['session_key'];
$user = $b->dispatch("/user/" . $email . "/")->get(
		["email" => $email,
		"session_key" => $sk]);

$iname = refine_post("iname");
$idesc = refine_post("idesc");
$price = refine_post("price");
$cname = refine_post("cname");
$photo_url = refine_post("photo_url");

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$result = $b->dispatch("/item/") -> post([
			"iname" => $iname,
			"idesc" => $idesc,
			"price" => $price,
			"cname" => $cname,
			"email" => $email,
			"session_key" => $sk]);
	if($result["result"] == "success"){
		if(!empty($photo_url)){
			$result = $b->dispatch("/item/" . $result["item_id"] . "/photo/")->post([
					"image_url" => $photo_url]);
			if($result["result"] == "success")
				$postInfo = "Post Item success!";
			else
				$postInfo = "Failure" . $result["reason"];
		}
		else{
			$postInfo = "Post Item success!";
		}
	}
	else{
		$postInfo = "Failure" . $result["reason"];
	}
}
?>

<!DOCTYPE=HTML>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>UMarket - Login</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap theme -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/theme.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body role="document">

    <!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">UMarket</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="#about">About</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container theme-showcase" role="main">

      <div class="page-header">
        <h1>New Item</h1>
      </div>
      
      <p>
		<form method="post">
		<table>
		<tr style="height: 30;">
			<td>Item Name:</td>
			<td> <input type="text" name="iname"> </td>
		</tr>
		<tr style="height: 30;">
			<td>Description:</td>
			<td> <input type="text" name="idesc"> </td>
		</tr>
		<tr style="height: 30;">
			<td>Price:</td>
			<td> <input type="text" name="price"> </td>
		</tr>
		<tr style="height: 30;">
			<td>Category:</td>
			<td> <select name="cname">
					<option value="Electronics">Electronics</option>
					<option value="Books">Books</option>
					<option value="Misc">Misc</option>
				</select>
			</td>
		</tr>
		<tr style="height: 30;">
			<td>Photo URL:</td>
			<td> <input type="text" name="photo_url"> </td>
		</tr>
		<tr style="height: 5px;"><td></td><td style="text-align: right">
			<input type="submit" name="new_item" value="Post Item" class="btn btn-lg btn-primary">
		</td></tr>
		</table>
	</form>
        <div style="text-align: center; padding-top: 5px;">
<?php echo $postInfo; ?>
        </div>
      </p>
      
    </div> <!-- /container -->
    
    
    <!-- Bootstrap core JavaScript
         ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="../../assets/js/docs.min.js"></script>
  </body>
</html>
<body>
