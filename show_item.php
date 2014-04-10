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

// Retrieving the detail for this item
$item_id = $_GET["item_id"];
$item = $b->dispatch("/item/" . $item_id . "/")->get([]);
// Must success, no need for sanity check?
$item_username = $b->dispatch("/user/" .$item["email"] . "/") ->get([]);
$photos = $item["photos"];

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
<h2> Item Name:</h2>
<?php echo $item["iname"];?>
<h2> Item Description </h2>
<?php echo $item["idesc"];?>
<h2> Category </h2>
<?php echo $item["cname"];?>
<h2> Posted by </h2>
<?php echo $item_username["name"];?>
<h2> Posted Date </h2>
<?php echo $item["post_date"];?>

<h2> Pictures: </h2>
<?php
foreach($photos as $photo){
	echo "<img src=\"" . $photo["image_url"] . "\" alt=\"Whoops....\" height=\"400\">\n";
}
?>

<h2> My Transactions: </h2>

</body>




</html>
