
<?php

session_start();

require_once "bin/common.php";
require_once "bin/backend.php";

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

<!DOCTYPE HTML>
<html>
<body>
<h1> Welcome to Online shopping platform </h1>
<h3> User login: </h3>
<form method="post">
<table>
<tr>
	<td> Email Address </td>
	<td> <input type="text" name="email" value="<?php echo $email;?>"> </td>
</tr>
<tr>
	<td> Password </td>
	<td> <input type="password" name="password" value="<?php echo $password;?>"> </td>
</tr>
</table>
<input type="submit" name="submit"> <?php echo $logInfo; ?>
</form>

Not a User? <a href="register.php"> Register Now </a>

</body>
</html>

