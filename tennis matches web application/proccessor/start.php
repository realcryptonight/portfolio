<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	/*
	 * Count the amount of players.
	 * @return The amount of players.
	*/
	function getPlayerCount() {
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get all the records that are in the players table and count them.
		if($result = $conn->query("SELECT COUNT(`id`) AS playercount FROM `players`")){
			$data = resultToArray($result);
			$result->close();
			// Since the data (player count) is in an array we need to access the correct array key to just get the player count.
			return $data[0]["playercount"];
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
	}
	
	/*
	 * Check how the first rond should be made.
	 * @return The information on how to make the first rond.
	*/
	function getFirstRoundInfo() {
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the player count.
		$playercount = getPlayerCount();
		
		// Variable that will store the value of the table of 2.
		$players = 128;
		
		// 128 can only be devided 7 times so we only loop max 7 times.
		for($i = 0; $i <= 7; $i++){ 
			// Check if the playercount is greater or equal to the players.
			if($playercount >= $players){
				// Check if the playercount is equal to the players.
				if($playercount == $players){
					// It is equal so we do not return an amount. Just a allplayer true value.
					return array("allplayers" => "true");
				} else {
					// Calculate the differents in playercount and players.
					$firstamount = $playercount - $players;
					// It is not equal so we do need to return a amount.
					return array("allplayers" => "false", "amount" => $firstamount);
				}
			} else {
				// Divide by 2 and start again.
				$players = $players / 2;
			}
		}
	}
?>