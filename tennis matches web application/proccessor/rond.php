<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	// Require some files that we also need.
	require(__DIR__ . "/editplayers.php");
	
	/*
	 * Generate the first rond for the tournament.
	 * @param $tournamentid The id of the tournament.
	 * @param $firstrond An array with information about the first rond.
	 * @param $tournamentname The name of the tournament.
	 * @return true on success. | false on failure.
	*/
	function firstRond($tournamentid, $firstrond = null, $tournamentname){
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		$safetournamentname = htmlspecialchars($tournamentname, ENT_QUOTES);
		
		// Check if we have the info needed.
		if($firstrond != null){
			
			// Get all the players.
			$players = getAllPlayers($safetournamentid);
			
			// For debug
			//echo "<pre>";
			//print_r($players);
			//echo "</pre>";
			
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
			// For debug
			//echo $loops;
			
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
				
				// For debug
				//echo "<pre>";
				//print_r($player1);
				//echo "</pre>";
				
				// For debug.
				//echo "<pre>";
				//print_r($player2);
				//echo "</pre>";
				
				// Crate the match.
				createMatch($safetournamentid, 1, $player1, $player2, $safetournamentname);
			}
		} else {
			
			return false;
		}
		
		return true;
	}
	
	/*
	 * Create a new match in the database.
	 * @param $tournamentid The id of the tournament.
	 * @param $rond The rond number.
	 * @param $player1 The first player.
	 * @param @player2 The second player.
	 * @param $tournamentname The name of the tournament. (Only used when the tournament does not exists yet.)
	*/
	function createMatch($tournamentid, $rond, $player1, $player2, $tournamentname = null){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Check if we need to add a tournament.
		if($stmt = $conn->prepare("SELECT `id` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $safetournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// If its empty then that means we do not have a tournament and we need to create one.
			if(empty($rows)){
				
				// Create a tournament.
				if($stmt = $conn->prepare("INSERT INTO `tournaments` (`rond`, `name`) VALUES (1, ?)")){
					
					$stmt->bind_param("s", $tournamentname);
					$stmt->execute();
					$stmt->close();
				} else {
					
					die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
				}
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
		}
		
		// Add the match to the database.
		if($stmt2 = $conn->prepare("INSERT INTO `matches`(`tournament`, `rond`, `player_1`, `player_2`) VALUES (?, ?, ?, ?)")){
			
			$stmt2->bind_param("ssss", $safetournamentid, $rond, $player1["id"], $player2["id"]);
			
			// For debug.
			//echo $safetournamentid . " " . $rond . " " . $player1["id"] . " " . $player2["id"];
			
			$stmt2->execute();
			$stmt2->close();
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-3)");
		}
	}
?>