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

<h2> Transactions of this item: </h2>
<table border="1">
    <tr>
        <td>Transaction ID</td>
        <td>Buyer</td>
        <td>Last date</td>
    </tr>
<?php
	foreach($item["transactions"] as $transaction){
		var_dump($transaction);
		$buyer = $b->dispatch("/user/" . $transaction["email"] . "/") ->get([]);
		echo "<tr><td>" .
			"<a href=\"show_trans.php?tid=" . $transaction["trans_id"] ."\">"
			. $transaction["trans_id"] . "</a></td><td>" .
			$buyer["name"] . "</td><td>" .
			$transaction["last_date"] . "</td></tr>";
	}
?>
</table>

</body>
</html>
