<?php

session_start();

require_once "../bin/common.php";
require_once "../bin/backend.php";

if(!isset($_SESSION['email'])){
	header( "Location: login.php" );

}
$changeInfo = "";
$b = Backend::instance();
$email = $_SESSION['email'];
$r = $_SESSION['session'];
$sk = $r['session_key'];
$user = $b->dispatch("/user/" . $email . "/")->get(
		["email" => $email,
		"session_key" => $sk]);

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$new_address = refine_post("new_address");
	$new_phone = refine_post("new_phone");
	$new_user = $b->dispatch("/user/" . $email . "/")->put(
			["email" => $email,
			"session_key" => $sk,
			"address" => $new_address,
			"phone" => $new_phone ]);
	if($new_user["result"] == "success"){
		$changeInfo = "Profile Change Success!";
		$user = $b->dispatch("/user/" . $email . "/")->get(
				["email" => $email,
				"session_key" => $sk]);
	}
	else
		$changeInfo = "Profile Change Failed!" + $new_user["reason"];
}

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

    <title>UMarket</title>

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
          <a class="navbar-brand" href="#">UMarket</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container theme-showcase" role="main">

      <h1>Dashboards</h1>

      <div class="page-header">
        <h2>Recent Items</h2>
      </div>

<?php
$items = $b->dispatch("/item/") -> get(
	["email" => $email,
	"session_key" => $sk]);
if($items["result"] == "success"){
?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Seller</th>
        <th>Post date</th>
      </tr>
    </thead>
    <tbody>
<?php
   $items = $items["items"];
   foreach ($items as $item) {
   $item_username = $b->dispatch("/user/" . $item["email"] . "/") ->get([]);
?>
<tr><td><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></td><td><?=$item["idesc"]?></td><td><?=$item_username["name"]?></td><td><?=$item["post_date"]?></td></tr>
<?php
   }
?>
    </tbody>
  </table>
</div>

<?php }else{ ?>
	<p>Something wrong happen when retrieving items: <?=$items["reason"]?>
<?php } ?>

<div class="page-header">
  <h2>Your Items</h2>
</div>

<?php
   $items = $b->dispatch("/item/") -> get(
["email" => $email,
"session_key" => $sk,
"owner_email" => $email]);
if($items["result"] == "success"){
?>
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>Description</th>
        <th>Post date</th>
      </tr>
    </thead>
    <tbody>
      <?php
         $items = $items["items"];
         foreach ($items as $item) {
      ?>
      <tr><td><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></td><td><?=$item_username["name"]?></td><td><?=$item["post_date"]?></td></tr>
      <?php
         }
         ?>
    </tbody>
  </table>
</div>

<?php }else{ ?>
	<p>Something wrong happen when retrieving items: <?=$items["reason"]?>
      <?php } ?>
                
      <div class="page-header">
        <h2>On Going Transactions</h2>
      </div>

<?php
   $trans = $b->dispatch("/transaction/") -> get(
      ["email" => $email,
       "session_key" => $sk]);
  if (count($trans["guest_tx"]) + count($trans["owner_tx"]) === 0) { ?>
<div style="text-align: center">oops ... no transaction now</div>
  <?php } else { ?>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Item</th>
          <th>Buyer</th>
          <th>Seller</th>
          <th>Last Updated</th>
          <th>Action</th>
        </tr>
      </thead>
<?php
   foreach($trans["guest_tx"] as $guest_tx){
		$seller = $b->dispatch("/user/" . $guest_tx["email"] . "/") ->get([]);
		$item = $b->dispatch("/item/" . $guest_tx["item_id"] . "/") ->get([]);
?>
      <tr><td><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></td><td><span style="color: darkblue">You</span></td>
        <td><?=$seller["name"]?></td><td><?=$guest_tx["last_date"]?></td><td><a href="show_transaction.php" class="btn btn-primary btn-xs" role="button">detail</a></td></tr>
<?php } ?>
<?php
   foreach($trans["owner_tx"] as $owner_tx){
		$buyer = $b->dispatch("/user/" . $owner_tx["email"] . "/") ->get([]);
		$item = $b->dispatch("/item/" . $owner_tx["item_id"] . "/") ->get([]);
?>
      <tr><td><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></td><td><?=$buyer["name"]?></td>
        <td><span style="color: darkblue">You</span></td><td><?=$owner_tx["last_date"]?></td><td><a href="show_transaction.php" class="btn btn-primary btn-xs" role="button">detail</a></td></tr>
<?php } ?>

      <tbody>
      </tbody>
    </table>
  </div>
 <?php } ?>
        

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="../../assets/js/docs.min.js"></script>
  </body>
</html>
