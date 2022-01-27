<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	/*
	Check if an tournament is already started or not.
	@param $tournamentid The id of the tournament.
	@return true if a tournament is started. | fals of no tournament is started.
	*/
	function hasStartedTournament($tournamentid) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Get all the records that are in the matches table.
		if($stmt = $conn->prepare("SELECT `id` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $safetournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			
			// For debug
			//echo "<pre>";
			//print_r($rows);
			//echo "</pre>";
			
			$stmt->close();
			
			// If the array is empty then there are not matches and thus no started tournament.
			if(empty($rows)){
				
				return false;
			} else {
				
				return true;
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
	}
	
	/*
	Get all the data from the tournament.
	@param $tournamentid The id of the tournament. (Default: 1)
	@return The tournament data and information.
	*/
	function getTournament($tournamentid = 1) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Check if an tournament has started.
		if(hasStartedTournament($safetournamentid) == false){
			
			// The tournament has not started. So we cannot get information about it.
			return array("success" => "false");
		}
		
		// Get all the matches record(s).
		if($stmt = $conn->prepare("SELECT `id`, `rond`, `winner`, `winner_points`, `opponent_points`, `player_1`, `player_2` FROM `matches` WHERE `tournament` = ?")){
			
			$stmt->bind_param("s", $safetournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// If the rows is empty then there are no records found.
			if(empty($rows)){
				
				// The tournament has no matches. So we cannot get information about it.
				return array("success" => "false");
			} else {
				
				// Loop though all the records and collect the correct player info.
				for($i = 0; $i <= count($rows) - 1; $i++){
					
					// Get the two player records with the player information.
					if($stmt = $conn->prepare("SELECT `id`, CONCAT(`firstname`, ' ', `lastname`) as `name`, `school` FROM `players` WHERE `id` = ? OR `id` = ?")){
						
						$stmt->bind_param("ss", $rows[$i]["player_1"], $rows[$i]["player_2"]);
						$stmt->execute();
						$result = $stmt->get_result();
						$rows2 = resultToArray($result);
						$stmt->close();
						
						// Add a new key called players.
						// Then a key that is the player id and the player info as value.
						$rows[$i]["players"][$rows2[0]["id"]] = $rows2[0];
						$rows[$i]["players"][$rows2[1]["id"]] = $rows2[1];
					} else {
						
						die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-1)");
					}
				}
				// Get the current value of the rond from the tournament id.
				if($stmt = $conn->prepare("SELECT `rond` FROM `tournaments` WHERE `id` = ?")){
					
					$stmt->bind_param("s", $safetournamentid);
					$stmt->execute();
					$stmt->bind_result($mysqlrond);
					$stmt->fetch();
					$stmt->close();
					
					// Return success and the result.
					return array("success" => "true", "current_rond" => $mysqlrond, "data" => $rows);
				} else {
					
					die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-2)");
				}
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-3)");
		}
	}
	
	/*
	 * Get all the data from the current rond.
	 * @param $tournamentid The id of the tournament.
	 * @return The rond data and information.
	*/
	function getRond($tournamentid) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Check if an tournament has started.
		if(hasStartedTournament($safetournamentid) == false){
			
			// The tournament has not started. So we cannot get information about it.
			return array("success" => "false");
		}
		
		// Get the current rond number of the first tournament.
		if($stmt = $conn->prepare("SELECT `rond` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $tournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			
			// Onlt keep the the rond value. Get rid of the array.
			$rond = resultToArray($result)[0]["rond"];
			$stmt->close();
			
			// If its null then there is no tournament.
			if($rond == null) { return array("success" => "false"); }
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-1)");
		}
		
		// Get all the matches that are in the current rond.
		if($stmt = $conn->prepare("SELECT `id`, `rond`, `winner`, `winner_points`, `opponent_points`, `player_1`, `player_2` FROM `matches` WHERE `tournament` = " . $tournamentid . " AND `rond` = ?")){
			
			$stmt->bind_param("s", $rond);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// If the rows is empty then there are no records found.
			if(empty($rows)){
				
				// The tournament has no matches with the given rond. So we cannot get information about it.
				return array("success" => "false");
			} else {
				
				// Loop though all the records and collect the correct player info.
				for($i = 0; $i <= count($rows) - 1; $i++){
					
					// Get the two player records with the player information.
					if($stmt = $conn->prepare("SELECT `id`, CONCAT(`firstname`, ' ', `lastname`) as `name`, `school` FROM `players` WHERE `id` = ? OR `id` = ?")){
						
						$stmt->bind_param("ss", $rows[$i]["player_1"], $rows[$i]["player_2"]);
						$stmt->execute();
						$result = $stmt->get_result();
						$rows2 = resultToArray($result);
						$stmt->close();
						
						// Add a new key called players.
						// Then a key that is the player id and the player info as value.
						$rows[$i]["players"][$rows2[0]["id"]] = $rows2[0];
						$rows[$i]["players"][$rows2[1]["id"]] = $rows2[1];
					} else {
						
						die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-2)");
					}
				}
				
				// Return success with the data.
				return array("success" => "true", "current_rond" => $rond, "data" => $rows);
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-3)");
		}
	}
	
	// This function is build for ticket: 3
	/*
	 * Get all the tourment IDs of the latest tournament.
	 * @return The current tournament ID.
	*/
	function getTournamentIDs(){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the current rond number of the current tournament.
		if($result = $conn->query("SELECT `id`, `name` FROM `tournaments`")){
			
			// Onlt keep the the rond value. Get rid of the array.
			$tournamentids = resultToArray($result);
			$result->close();
			
			// If its null then there is no tournament.
			if($tournamentids == null) {
				
				return array("success" => "false");
			} else {
				
				return array("success" => "true", "data" => $tournamentids);
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-1)");
		}
	}
?>