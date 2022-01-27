<?php

	// Database connection settings.
	$HOSTNAME = "localhost";
	$PORT = 3306;
	$USERNAME = "root";
	$PASSWORD = "";
	$DATABASE = "toernooien";

	// Start a mysql connection to the sql server as specified in the connection settings.
	$conn = new mysqli($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE, $PORT);

	// Make sure that the connection was successfull.
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
	/*
	Put the multiple records that it got from the database into an array.
	@param result The result of the database query that contains the records.
	@return An array with the records from the result.
	*/
	function resultToArray($result) {
		$rows = array();
		// While there are still rows to fetch, keep fetching.
		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}
?>