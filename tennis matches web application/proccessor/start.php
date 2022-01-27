<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	/*
	 * Count the amount of players.
	 * @param $tournamentid The id of the tournament.
	 * @return The amount of players.
	*/
	function getPlayerCount($tournamentid) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Get all the records that are in the players table and count them.
		if($stmt = $conn->prepare("SELECT COUNT(`id`) AS playercount FROM `players` WHERE `tournament_id` = ?")){
			
			$stmt->bind_param("s", $safetournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$data = resultToArray($result);
			$stmt->close();
			
			// Since the data (player count) is in an array we need to access the correct array key to just get the player count.
			return $data[0]["playercount"];
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
	}
	
	/*
	 * Check how the first rond should be made.
	 * @param $tournamentid The id of the tournament.
	 * @return The information on how to make the first rond.
	*/
	function getFirstRoundInfo($tournamentid) {
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Get the player count.
		$playercount = getPlayerCount($safetournamentid);
		
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
	
	function getNextTournamentID() {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the heighest tournament id from the database.
		if($result = $conn->query("SELECT `id` FROM `tournaments` WHERE `id` = (SELECT MAX(`id`) FROM `tournaments`)")){
			
			$tournamentid = resultToArray($result);
			
			// Check if we have a tournament id or not.
			if(!empty($tournamentid)){
				
				// Add one to the current tournament id.
				$newtournamentid = $tournamentid[0]["id"] + 1;
				
			} else {
				
				// No tournament id so we start at 1.
				$newtournamentid = 1;
			}
			
			$result->close();
			return $newtournamentid;
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
	}
?>