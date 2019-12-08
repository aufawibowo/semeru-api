<?php
	$server 	= "103.253.107.45";
	$username 	= "ngsemeru";
	$password	= "NGSemeru#2017";
	$database 	= "telkomsel_semeru";
	
	$con = mysqli_connect($server, $username, $password, $database);
	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
?>