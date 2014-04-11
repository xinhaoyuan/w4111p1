<?php

session_start();
require_once "bin/backend.php";
require_once "bin/common.php";

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

<!DOCTYPE=HTML>
<html>
<body>
<form method="post" action="logout.php"> <input type="submit" name="logout" value="Logout"> </form>
<h1>Transaction Detail:</h1>
<h2>Item</h2>
<?php echo $item["iname"];?>
<h2>Owner</h2>
<?php echo $owner["name"];?>
<h2>Buyer</h2>
<?php echo $buyer["name"];?>
<h2>Last Date </h2>
<?php echo $transaction["last_date"];?>

<h1>Messages:</h1>
<table border="1">
    <tr>
        <td>Sender</td>
		<td>Content</td>
        <td>Date</td>
    </tr>
<?php
	foreach($messages as $message){
		$sender = $b->dispatch("/user/" . $message["sender"]."/")->get([]);
		echo "<tr><td>" . $sender["name"] . "</td><td>" .
			$message["date"] . "</td><td>" .
			$message["content"] . "</td></tr>";
	}
?>
</table>
<h3>New Message</h3>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']."?tid=".$trans_id ."#content"; ?>">
<textarea id="content" name="content" rows="10" cols="80">Type your message here...</textarea>
<input type="submit" name="post" value="Send message">
</form>
<?php echo $sendInfo; ?>
</body>
</html>
