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

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
if ($action === "join") {
    $gname = $_REQUEST["gname"];
    $r = $b->dispatch("/group/$gname")->post(
          array("email" => $email, "session_key" => $sk));
    if ($r["result"] === "success")
       $msg = "Join success";
    else $msg = "Join failed - " . $r["reason"];
    $groups = $b->dispatch("/group/list/")->get([])["groups"];
} else if ($action === "search") {
    $keyword = $_REQUEST["keyword"];
    $r = $b->dispatch("/group/list/")->get(["keyword" => $keyword]);
    $groups = $r["groups"];
    $msg = "Search result for keyword \"$keyword\"";
} else {
    $r = $b->dispatch("/group/list/")->get([]);
    $groups = $r["groups"];
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

    <title>UMarket - Groups</title>

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
        <h1>Groups</h1>
      </div>

      <div>
        <form method="get">
<input type="hidden" name="action" value="search"/>
<div class="input-group">
  <span class="input-group-addon">Search</span>
  <input type="text" name="keyword" class="form-control" placeholder="Any keyword to search ...">
  <span class="input-group-btn">
    <button class="btn btn-default" type="submit">Go!</button>
  </span>
</div>
        </form>

      </div>

      <?php if (isset($msg)) { echo $msg; } ?>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Name</th>
              <th>Description</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
<?php foreach ($groups as $group) { ?>
<tr><td><?=$group["gname"]?></td><td><?=$group["gdesc"]?></td><td><a href="list_groups.php?action=join&gname=<?=$group["gname"]?>" class="btn btn-primary btn-xs" role="button">Join</td></tr>
<?php } ?>
          </tbody>
        </table>
      </div>

    </div>
  </body>
</html>
