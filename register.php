<!DOCTYPE HTML>

<?php

require_once "bin/backend.php";
require_once "bin/common.php";

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

<html>
<body>
<h1> User Registration: </h1>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table>
<tr>
 <td> Email Address </td>
 <td> <input type="text" name="email" value="<?php echo $email;?>"> </td>
</tr>
<tr>
 <td> Name </td>
 <td> <input type="text" name="name" value="<?php echo $name;?>"> </td>
</tr>
<tr>
 <td> Password </td>
 <td> <input type="password" name="password" value="<?php echo $password;?>"> </td>
</tr>
<tr>
 <td> Address </td>
 <td> <input type="text" name="address" value="<?php echo $address;?>"> </td>
</tr>
<tr>
 <td> Phone Number </td>
 <td> <input type="text" name="phone" value="<?php echo $phone;?>"> </td>
</tr>
</table>
<input type="submit" name="submit">
</form>
<?php
echo $successInfo;
?>
Go back to </a href="login.php"> Login</a>
</body>
</html>
