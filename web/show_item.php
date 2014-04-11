<?php

session_start();
require_once "../bin/backend.php";
require_once "../bin/common.php";

if(!isset($_SESSION['email'])){
	header( "Location: login.php" );
}

$buyInfo = "";
$b = Backend::instance();
$email = $_SESSION['email'];
$r = $_SESSION['session'];
$sk = $r['session_key'];
$user = $b->dispatch("/user/" . $email . "/")->get(
		["email" => $email,
		"session_key" => $sk]);

// Retrieving the detail for this item
$item_id = $_GET["item_id"];
$item = $b->dispatch("/item/" . $item_id . "/")->get([
"email" => $email,
"session_key" => $sk]);
// Must success, no need for sanity check?
$item_username = $b->dispatch("/user/" .$item["email"] . "/") ->get([]);
$photos = $item["photos"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
	echo "BUYING...............";
	$transaction = $b->dispatch("/transaction/")->post([
			"item_id" => $item_id,
			"email" => $email,
			"session_key" => $sk,
			"price" => $item["price"]]);
	if($transaction["result"] == "success"){
		header( "location: show_trans.php?tid=".$transaction["trans_id"]);
	}
	else{
		$buyInfo = "Failed..." . $transaction["reason"];
	}
}

?>

<?php function show_actions() {
      global $email, $item;
      if ($item["email"] != $email) { ?>
<p style="text-align: center">
  <?php if (count($item["transactions"]) == 0) { ?>
  <?php } else { ?>
  You already have transaction on this item. <a href="show_trans.php?tid=<?=$item["transactions"][0]["trans_id"]?>" class="btn btn-primary btn-xs" role="button">View</a>
  <?php } ?>
</p>
<?php } } ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>UMarket - Item Detail</title>

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
        <h2><?=$item["iname"]?></h2>
      </div>

      <?php show_actions() ?>

      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Description</h3>
        </div>
        <div class="panel-body">
          <?=$item["idesc"]?>
        </div>

        <div class="panel-heading">
          <h3 class="panel-title">Price</h3>
        </div>
        <div class="panel-body">
          <?=$item["price"]?>
<?php if ($item["email"] != $email && count($item["transactions"]) === 0) { ?>
	<form action="<?php echo $_SERVER['PHP_SELF']."?item_id=".$item_id; ?>" method="post">
	<input type="submit" name="buy" value="BUY"> <?php echo $buyInfo; ?>
	</form>
<?php } ?>
        </div>

        <div class="panel-heading">
          <h3 class="panel-title">Photos</h3>
        </div>
        <div class="panel-body">
<?php foreach($photos as $photo) { ?>
<div style="text-align: center"><img src="<?=$photo["image_url"]?>" alt="Whoops..." style="max-width: 40%"/></div>
<?php } ?>
        </div>

        <?php if ($item["email"] === $email && count($item["transactions"]) > 0) { ?>
        <div class="panel-heading">
          <h3 class="panel-title">Buying Transaction</h3>
        </div>
        <div class="panel-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Buyer</th>
                <th>Seller</th>
                <th>Price</th>
                <th>Last Updated</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($item["transactions"] as $tx) {
		            $buyer = $b->dispatch("/user/" . $tx["email"] . "/") ->get([]);
              ?>
              <tr><td><a href="show_user.php?email=<?=$tx["email"]?>"><?=$buyer["name"]?></a></td>
                <td><span style="color: darkblue">You</span></td><td><?=$tx["price"]?></td><td><?=$tx["last_date"]?></td><td><a href="show_trans.php?tid=<?=$tx["trans_id"]?>" class="btn btn-primary btn-xs" role="button">detail</a></td></tr>
              <?php } ?>
            </tbody>
          </table>
          <?php } ?>
          
        </div>
        
        <?php show_actions() ?>
      </div>
    </div>
  </body>
</html>
