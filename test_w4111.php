<?php
	// Init the connection to database
	ini_set('display_errors', 'On');
	$db = "w4111g.cs.columbia.edu:1521/adb";
	$conn = oci_connect("xx2153", "xinan", $db);
	











	
	echo "<br>";
	echo "<br>";
	echo "<br>";
	echo "<br>";
	echo "<br>";
	
	$stmt = oci_parse($conn, "select user from dual");
	oci_execute($stmt, OCI_DEFAULT);
	while ($res = oci_fetch_row($stmt))
	{
		echo "User Name: ". $res[0]."<br>" ;
	}
	echo "<br>";

	$stmt = oci_parse($conn, "select table_name from user_tables");
	oci_execute($stmt, OCI_DEFAULT);
	while ($res = oci_fetch_row($stmt))
	{
		echo "Table: ". $res[0] ."<br>" ;
	}
	echo "<br>";
	$stmt = oci_parse($conn, "select name from users");
	oci_execute($stmt, OCI_DEFAULT);
	while ($res = oci_fetch_row($stmt))
	{
		echo "Users: ". $res[0] ."<br>" ;
	}
	oci_close($conn);
?>
