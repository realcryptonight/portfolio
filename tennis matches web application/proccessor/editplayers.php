<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	/*
	 * Get all the players that are in the player table.
	 * @param $tournamentid The id of the tournament.
	 * @return An array with all the players from the players table.
	*/
	function getAllPlayers($tournamentid){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Get all the records that are in the players table.
		if($stmt = $conn->prepare("SELECT `id`, `firstname`, `lastname`, `school` FROM `players` WHERE `tournament_id` = ?")){
			
			$stmt->bind_param("s", $safetournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// Return all the players from the players table.
			return $rows;
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
	}
	
	/*
	 * Get the player information from a specific player.
	 * @param $playerid The id of the player.
	 * @return an array with the player information.
	*/
	function getPlayer($playerid){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safeplayerid = htmlspecialchars($playerid, ENT_QUOTES);
		
		// Get the record that belongs to the specified player.
		if($stmt = $conn->prepare("SELECT `id`, `firstname`, `lastname`, `school` FROM `players` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $safeplayerid);
			$stmt->execute();
			
			// Store the result in variables.
			$stmt->bind_result($mysqlid,$mysqlfirstname,$mysqllastname,$mysqlschool);
			$stmt->fetch();
			$stmt->close();
			
			// Check if the mysqlid is not null. Since that would mean no records found.
			if($mysqlid != null){
				
				return array("success" => "true", "data" => array("id" => $mysqlid, "firstname" => $mysqlfirstname, "lastname" => $mysqllastname, "school" => $mysqlschool));
			} else {
				
				return array("success" => "false");
			}
		} else {
			
			return array("success" => "false");
		}
	}
	
	/*
	 * Update an existing player with other information.
	 * @param $_POST["id"] The id of the existing player.
	 * @param $_POST["firstname"] The new firstname of the player.
	 * @param $_POST["lastname"] The new lastname of the player.
	 * @param $_POST["school"] The new name of the school.
	 * @return true if the update was successfull. | false if the update failed.
	*/
	function updatePlayer() {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safeplayerid = htmlspecialchars($_POST["id"], ENT_QUOTES);
		$safefirstname = htmlspecialchars($_POST["firstname"], ENT_QUOTES);
		$safelastname = htmlspecialchars($_POST["lastname"], ENT_QUOTES);
		$safeschool = htmlspecialchars($_POST["school"], ENT_QUOTES);
		
		// Update the player with the new information.
		if($stmt = $conn->prepare("UPDATE `players` SET `id`= ?,`firstname`= ?,`lastname`= ?,`school`=? WHERE `id` = ?")){
			
			$stmt->bind_param("sssss", $safeplayerid, $safefirstname, $safelastname, $safeschool, $safeplayerid);
			$stmt->execute();
			$stmt->close();
			
			// Return true. Meaning success.
			return true;
		} else {
			
			// Return false. Meaning failure.
			return false;
		}
	}
	
	/*
	 * Delete a player from the players table.
	 * @param $playerid The id of the player.
	 * @return true if player is delete if it was possible. | false if the player deletion failed.
	*/
	function deletePlayer($playerid) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safeplayerid = htmlspecialchars($playerid, ENT_QUOTES);
		
		// Delete the given user from the player table if possible.
		if($stmt = $conn->prepare("DELETE FROM `players` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $safeplayerid);
			$stmt->execute();
			
			// Return true. Meaning success.
			return true;
		} else {
			
			// Return false. Meaning failure.
			return false;
		}
	}
?>