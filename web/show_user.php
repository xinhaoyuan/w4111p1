<?php

session_start();
require_once "../bin/backend.php";
require_once "../bin/common.php";

if(!isset($_SESSION['email'])){
	header( "Location: login.php" );
}

$b = Backend::instance();
$email = $_SESSION['email'];
$r = $_SESSION['session'];
$sk = $r['session_key'];

// Retrieving the detail for this user
$useremail = $_GET["email"];
$user = $b->dispatch("/user/" . $email . "/")->get([]);

// Must success, no need for sanity check?

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>UMarket - User Detail</title>

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
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container theme-showcase" role="main">

      <div class="page-header">
        <h2><?=$user["name"]?></h2>
      </div>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Email Address</h3>
        </div>
        <div class="panel-body">
          <?=$user["email"]?>
        </div>
      </div>
<div class="page-header">
  <h2>His/Her Items</h2>
</div>

<?php
   $items = $b->dispatch("/item/") -> get(
["email" => $email,
"session_key" => $sk,
"owner_email" => $useremail]);
if($items["result"] == "success"){
?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Catagoty</th>
        <th>Post date</th>
      </tr>
    </thead>
    <tbody>
      <?php
         $items = $items["items"];
         foreach ($items as $item) {
      ?>
      <tr><td><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></td><td><?=$item["idesc"]?></td><td><?=$item["price"]?></td><td><?php if ($item["cname"] != NULL) echo $item["cname"]; ?></td><td><?=$item["post_date"]?></td></tr>
      <?php
         }
         ?>
    </tbody>
  </table>
</div>

<?php }else{ ?>
	<p>Something wrong happen when retrieving items: <?=$items["reason"]?>
      <?php } ?>

    </div>
  </body>
</html>
