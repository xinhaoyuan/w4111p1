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
$user = $b->dispatch("/user/" . $email . "/")->get(
		["email" => $email,
		"session_key" => $sk]);
// Retrieving the detail for this item
$trans_id = $_GET["tid"];
$transaction = $b->dispatch("/transaction/" . $trans_id . "/")->get(
		["email" => $email,
         "session_key" => $sk]);

// Send message
$content = refine_post("content");
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(!empty($content)){
		$result = $b->dispatch("/transaction/" . $trans_id . "/")->post(
				["email" => $email,
				"session_key" => $sk,
				"content" => $content]);
		if($result["result"] == "success"){
			$sendInfo="Message Sent!";
			// Get the updated version
			$transaction = $b->dispatch("/transaction/" . $trans_id . "/")->get(
					["email" => $email,
					"session_key" => $sk]);
		}
		else
			$sendInfo="Message Sent failure: ".$result["reason"];
	}
}

// Must success, no need for sanity check?
$item = $b->dispatch("/item/" .$transaction["item_id"] . "/") ->get(["email" => $email, "session_key" => $sk]);
$owner = $b->dispatch("/user/" .$item["email"]."/")->get([]);
$buyer = $b->dispatch("/user/" . $transaction["email"]."/")->get([]);
$messages = $transaction["messages"];



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

    <title>UMarket - Transaction Detail</title>

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
      <div class="page-header">
        <h1>Transaction Detail</h1>
      </div>

      <div class="container">
        <!-- Example row of columns -->
        <div class="row">
          <div class="col-md-4">
            <h3>Item</h3>
            <p><a href="show_item.php?item_id=<?=$item["item_id"]?>"><?=$item["iname"]?></a></p>
          </div>
          <div class="col-md-4">
            <h3>Owner</h3>
            <p><?=$owner["name"]?></p>
          </div>
          <div class="col-md-4">
            <h3>Buyer</h3>
            <p><?=$buyer["name"]?></p>
          </div>
        </div>
        <!-- Example row of columns -->
        <div class="row">
          <div class="col-md-4">
            <h3>Price</h3>
            <p><?=$transaction["price"]?></p>
          </div>
          <div class="col-md-4">
            <h3>Last Updated</h3>
            <p><?=$transaction["last_date"]?></p>
          </div>
        </div>
      </div>
      
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Messages</h3>
        </div>

        <div class="panel-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <td>Sender</td>
		          <td>Date</td>
                  <td>Content</td>
                </tr>
              </thead>
              <tbody>
            <?php
	           foreach($messages as $message) {
		       $sender = $b->dispatch("/user/" . $message["sender"]."/")->get([]); ?>
                <tr><td><?=$sender["name"]?></td><td><?=$message["date"]?></td><td><?=$message["content"]?></td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="panel-heading">
          <h3 class="panel-title">New Message</h3>
        </div>

        <div class="panel-body">
          <form method="post" action="<?php echo $_SERVER['PHP_SELF']."?tid=".$trans_id ."#content"; ?>">
            <div><textarea id="content" name="content" rows="10" cols="80">Type your message here...</textarea></div>
            <input type="submit" name="post" value="Send message">
          </form>
          <?php echo $sendInfo; ?>
        </div>
      </div>
    </div>
  </body>
</html>
