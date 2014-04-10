
<?php

require_once "bin/common.php";

$success = 0;
$email = refine_post("email");
$password = refine_post("password");

if(!empty($email) && !empty($password))
{
	// Submit the result to backend
	$success = 1;
	header( 'Location: index.php' ) ;
}


<!DOCTYPE HTML>
<html>
<body>
<h1> Welcome to Online shopping platform </h1>
<h3> User login: </h3>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
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
<input type="submit" name="submit">
</form>

</body>
</html>

