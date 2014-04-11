<?php

session_start();
require_once "bin/backend.php";
require_once "bin/common.php";
$postInfo = "";
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

$iname = refine_post("iname");
$idesc = refine_post("idesc");
$price = refine_post("price");
$cname = refine_post("cname");

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$result = $b->dispatch("/item/") -> get([
			"iname" => $iname,
			"idesc" => $idesc,
			"price" => $price,
			"cname" => $cname,
			"email" => $email,
			"session_key" => $sk]);
	if($result["result"] == "success"){
		$postInfo = "Post Item success!";
	}
	else{
		$postInfo = "Failure" . $result["reason"];
	}
}
?>

<!DOCTYPE=HTML>
<html>
<body>
<h1>New Item</h1>
<form method="post">
<table>
<tr>
	<td>Item Name:</td>
	<td> <input type="text" name="iname"> </td>
</tr>
<tr>
	<td>Description:</td>
	<td> <input type="text" name="idesc"> </td>
</tr>
<tr>
	<td>Price:</td>
	<td> <input type="text" name="price"> </td>
</tr>
<tr>
	<td>Category:</td>
	<td> <select name="cname">
			<option value="Electronics">Electronics</option>
			<option value="Books">Books</option>
			<option value="Misc">Misc</option>
		</select>
	</td>
</tr>
</table>
<td> <input type="submit" name="new_item" value="Post Item"> </td>
</form>
<?php echo $postInfo; ?>

<form method="post" action="logout.php"> <input type="submit" name="logout" value="Logout"> </form>

</body>
</html>
