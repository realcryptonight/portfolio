<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	/*
	Check if an tournament is already started or not.
	@return true if a tournament is started. | fals of no tournament is started.
	*/
	function hasStartedTournament() {
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get all the records that are in the matches table.
		if($result = $conn->query("SELECT `id` FROM `matches`")){
			$rows = resultToArray($result);
			$result->close();
			
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
	@param $tournamentid The id of the tournament. (Optional)
	@return The tournament data and information.
	*/
	function getTournament($tournamentid = 1) {
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Check if an tournament has started.
		if(hasStartedTournament() == false){
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
	Get all the data from the current rond.
	@return The rond data and information.
	*/
	function getRond() {
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Check if an tournament has started.
		if(hasStartedTournament() == false){
			// The tournament has not started. So we cannot get information about it.
			return array("success" => "false");
		}
		
		// Get the current rond number of the first tournament.
		if($result = $conn->query("SELECT `rond` FROM `tournaments` WHERE `id` = 1")){
			// Onlt keep the the rond value. Get rid of the array.
			$rond = resultToArray($result)[0]["rond"];
			$result->close();
			
			// If its null then there is no tournament.
			if($rond == null) { return array("success" => "false"); }
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-1)");
		}
		
		// Get all the matches that are in the current rond.
		if($stmt = $conn->prepare("SELECT `id`, `rond`, `winner`, `winner_points`, `opponent_points`, `player_1`, `player_2` FROM `matches` WHERE `rond` = ?")){
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
?>