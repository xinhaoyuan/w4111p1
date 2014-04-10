<?php

session_start();

require_once "../bin/common.php";
require_once "../bin/backend.php";

$email = refine_post("email");
$password = refine_post("password");
$logInfo = "";
if($_SERVER["REQUEST_METHOD"] == "POST")
{
	// Submit the result to backend

	$b = Backend::instance();
	$r = $b->dispatch("/session/")->post(
			array("email" => $email,
				"password" => $password)
			);
	// How to determine whether it success ?

	if($r["result"] == "success"){
		$_SESSION['email'] = $email;
		$_SESSION['session'] = $r;
		header( 'Location: index.php' );
	}
	else{
		$logInfo = "Login failed: " . $r["reason"];
	}
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
          <a class="navbar-brand" href="#">UMarket</a>
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
        <h1>Login</h1> or <a href="register.php">new user</a>?
      </div>
      
      <p>
        <form method="post">
          <table align="center">
            <tr>
	          <td style="padding-right: 10px">Email Address</td>
	          <td> <input type="text" name="email" value="<?php echo $email;?>"> </td>
            </tr><tr style="height: 5px"></tr>
            <tr>
	          <td style="padding-right: 10px">Password</td>
	          <td><input type="password" name="password" value="<?php echo $password;?>"> </td>
            </tr><tr style="height: 5px"></tr>
            <tr>
              <td></td><td style="text-align: right;">
                <input type="submit" name="submit" value="Login" class="btn btn-lg btn-primary">
              </td>
            </tr>
          </table>
          <div style="text-align: center; margin-top: 5px;">
            <?php echo $logInfo; ?>
          </div>
        </form>
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
