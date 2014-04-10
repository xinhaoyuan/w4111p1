<?php

session_start();
require_once "bin/backend.php";
require_once "bin/common.php";

if(!isset($_SESSION['email'])){
	echo "Access Forbidden";
	return;
}
$b = Backend::instance();
$email = $_SESSION['email'];
$r = $_SESSION['session'];
$sk = $r['session_key'];
$user = $b->dispatch("/user/" . $email . "/")->get([]);
?>

<!DOCTYPE=HTML>
<html>
<body>
Login Success! <br>
Dear <?php echo $user['name'];?>, How are you today? <br>
We have your address and phone number on file: <br>
Address: <?php echo $user['address'];?> <br>
Phone number: <?php echo $user['phone'];?> <br>
You can <form method="post" action="logout.php"> <input type="submit" name="logout" value="Logout"> </form> at any time.

</body>
</html>
