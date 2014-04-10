<?php

session_start();
require_once "bin/backend.php";
require_once "bin/common.php";

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

<!DOCTYPE=HTML>
<html>
<body>
Login Success! <br>
Dear <?php echo $user['name'];?>, How are you today? <br>
We have your address and phone number on file: <br>
<form method="post">
<table>
<tr>
	<td> Address: </td>
	<td> <?php echo empty($user['address'])?"Not On File":$user['address'];?> </td>
	<td> <input type="text" name="new_address"> </td>
</tr>
<tr>
	<td> Phone number: </td>
	<td> <?php echo empty($user['phone'])?"Not On File":$user['phone'];?> </td>
	<td> <input type="text" name="new_phone"> </td>
</tr>
</table>
<td> <input type="submit" name="change_profile" value="Change Profile"> </td>
</form>
<?php echo $changeInfo; ?>
<form method="post" action="logout.php"> <input type="submit" name="logout" value="Logout"> </form>
<h2> Check we have for you today:</h2>
<?php
$items = $b->dispatch("/item/") -> get(
		["email" => $email,
		"session_key" => $sk]);
if($items["result"] == "success"){
	$items = $items["items"];
	echo '<table border="1" > <tr> <td> Item name </td><td> Description </td><td> Category </td>
		<td> Posted by </td> <td> Post Date</td></tr>';
	foreach ($items as $item) {
		$item_username = $b->dispatch("/user/" . $item["email"] . "/") ->get([]);
		echo "<tr><td>" . $item["iname"] . "</td><td>" . $item["idesc"] . "</td><td>" . $item["cname"] . "</td><td>" . $item_username["name"] . "</td><td>" . $item["post_date"] . "</td></tr>";
	}
	echo "</table>";
}
else{
	echo "Something wrong happen when retrieving items" . $items["reason"];
}

?>
</body>
</html>
