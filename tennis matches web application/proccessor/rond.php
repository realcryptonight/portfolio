<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	// Require some files that we also need.
	require(__DIR__ . "/editplayers.php");
	
	/*
	 * Generate the first rond for the tournament.
	 * @param $firstrond An array with information about the first rond.
	 * @return true on success. | false on failure.
	*/
	function firstRond($firstrond = null){
		// Check if we have the info needed.
		if($firstrond != null){
			
			// Get all the players.
			$players = getAllPlayers();
			
			// Randomize the player order.
			shuffle($players);
			
			// Check if we need a elimination rond.
			if($firstrond["allplayers"] == "true"){
				// We do not need an elimination rond so just add everyone to the first rond.
				$loops = count($players) / 2 - 1;
			} else {
				// We need an elimination rond so create an rond as discribed by the firstrond variable.
				$loops = $firstrond["amount"] - 1;
			}
			
			// This will keep track of the array key we need to access.
			$entrykey = 0;
			
			// For debug
			//echo $firstrond["amount"];
			
			// Make as much matches as there are needed.
			for($i = 0; $i <= $loops; $i++){
				//For debug
				//echo "|i=" . $i;
				//echo "|en=" . $entrykey;
				
				// Get the player id from the players array.
				$player1 = $players[$entrykey];
				$entrykey++;
				
				//For debug
				//echo "|en=" . $entrykey;
				
				// Get the player id from the players array.
				$player2 = $players[$entrykey];
				$entrykey++;
				
				// Crate the match.
				createMatch(1, $player1, $player2);
			}
		}
		return true;
	}
	
	/*
	 * Create a new match in the database.
	 * @param $rond The rond number.
	 * @param $player1 The first player.
	 * @param @player2 The second player.
	*/
	function createMatch($rond, $player1, $player2){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Check if we need to add a tournament.
		if($result = $conn->query("SELECT `id` FROM `tournaments`")){
			$rows = resultToArray($result);
			$result->close();
			
			// If its empty then that means we do not have a tournament and we need to create one.
			if(empty($rows)){
				// Create a tournament.
				if(!$data = $conn->query("INSERT INTO `tournaments` (`rond`) VALUES (1)")){
					die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
				}
			}
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
		}
		
		// Add the match to the database.
		if($stmt = $conn->prepare("INSERT INTO `matches`(`tournament`, `rond`, `player_1`, `player_2`) VALUES (1, ?, ?, ?)")){
			$stmt->bind_param("sss", $rond, $player1["id"], $player2["id"]);
			$stmt->execute();
			$stmt->close();
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-3)");
		}
	}
?>