<?php

require_once "../bin/backend.php";
require_once "../bin/common.php";

$successInfo = "";
$email = $name = $password = $address = $phone = "";
$email = refine_post("email");
$name = refine_post("name");
$password = refine_post("password");
$address = refine_post("address");
$phone = refine_post("phone");

if($_SERVER["REQUEST_METHOD"] == "POST")
{
// Email, Name, Password NOT NULL
if(empty($email))
	js_alert("Your email address should not be blank");
else if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))
	js_alert("Your email address is invalid");
else if(empty($name))
	js_alert("Your name should not be blank");
else if(empty($password))
	js_alert("Your password should not be blank");
else{
	// Submit the result to backend
	$b = Backend::instance();
	$r = $b->dispatch("/user/")->post(
		["email" => $email,
		 "name"  => $name,
		 "password" => $password,
		 "address" => $address,
		 "phone" => $phone]);
	if($r["result"] == "success"){
		$successInfo = "Register Success !!!";
	}
	else{
		$successInfo = "Sorry..." . $r["reason"];
	}
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
        <h1>Register</h1> or <a href="login.php">already has account</a>?
      </div>
      
      <p>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
          <table align="center">
            <tr>
              <td>Email Address</td>
              <td style="padding-left: 5px;"><input type="text" name="email" value="<?php echo $email;?>"></td>
            </tr><tr style="height: 5px;"></tr>
            <tr>
              <td>Name</td>
              <td style="padding-left: 5px;"><input type="text" name="name" value="<?php echo $name;?>"></td>
            </tr><tr style="height: 5px;"></tr>
            <tr>
              <td>Password</td>
              <td style="padding-left: 5px;"><input type="password" name="password" value="<?php echo $password;?>"></td>
            </tr><tr style="height: 5px;"></tr>
            <tr>
              <td>Address</td>
              <td style="padding-left: 5px;"><input type="text" name="address" value="<?php echo $address;?>"></td>
            </tr>
            <tr><tr style="height: 5px;"></tr>
              <td>Phone Number</td>
              <td style="padding-left: 5px;"><input type="text" name="phone" value="<?php echo $phone;?>"></td>
            </tr><tr style="height: 5px;"></tr>
            <tr><td></td><td style="text-align: right">
                <input type="submit" name="submit" value="Register" class="btn btn-lg btn-primary">
            </td></tr>
          </table>
        </form>
        <div style="text-align: center; padding-top: 5px;">
          <?php echo $successInfo; ?>
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
