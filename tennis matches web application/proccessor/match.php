<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	// Require some files that we also need.
	require(__DIR__ . "/automation.php");
	
	/*
	Get the data from an match.
	@param $match The match you want to get.
	@return The rond data and information.
	*/
	function getMatch($match) {
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safematch = htmlspecialchars($match, ENT_QUOTES);
		
		// Get the match record.
		if($stmt = $conn->prepare("SELECT `id`, `winner`, `winner_points`, `opponent_points`, `player_1`, `player_2` FROM `matches` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $safematch);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// If the rows variable is empty then there are no records found.
			if(empty($rows)){
				
				return array("success" => "false");
			} else {
				
				// Get the player info that needs to be added to the return data.
				if($stmt = $conn->prepare("SELECT `id`, CONCAT(`firstname`, ' ', `lastname`) as `name`, `school` FROM `players` WHERE `id` = ? OR `id` = ?")){
					
					$stmt->bind_param("ss", $rows[0]["player_1"], $rows[0]["player_2"]);
					$stmt->execute();
					$result = $stmt->get_result();
					$rows2 = resultToArray($result);
					$stmt->close();
					
					// Add a new key called players.
					// Then a key that is the player id and the player info as value.
					$rows[0]["players"][$rows2[0]["id"]] = $rows2[0];
					$rows[0]["players"][$rows2[1]["id"]] = $rows2[1];
				} else {
					
					die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
				}
				
				// Return the result.
				return array("success" => "true", "data" => $rows);
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
		}
	}
	
	// This function is rebuild for ticket: 2
	/*
	 * Set the match result into the database.
	 * And also check if a new rond is needed.
	 * @param $id The id of the match.
	 * @param $oneid The players id of the first player.
	 * @param $twoid The players id of the second player.
	 * @param $scoreone The score of the first player.
	 * @param $scoretwo The score of the second player.
	 * @param $tournamentid The id of the tournament.
	 * @return A success or failure message.
	*/
	function updateMatch($id, $oneid, $twoid, $scoreone, $scoretwo, $tournamentid){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safeid = htmlspecialchars($id, ENT_QUOTES);
		$safeoneid = htmlspecialchars($oneid, ENT_QUOTES);
		$safetwoid = htmlspecialchars($twoid, ENT_QUOTES);
		$safescoreone = htmlspecialchars($scoreone, ENT_QUOTES);
		$safescoretwo = htmlspecialchars($scoretwo, ENT_QUOTES);
		$safetournamentid = htmlspecialchars($tournamentid, ENT_QUOTES);
		
		// Check if player one has the heighst score.
		if($safescoreone > $safescoretwo) {
			
			// Set the variables for player one as the winner.
			$winnerid = $safeoneid;
			$winnerscore = $safescoreone;
			$opponentscore = $safescoretwo;
		} else {
			
			// Check if player two has the heighst score.
			if($safescoreone < $safescoretwo){
				
				// Set the variables for player two as the winner.
				$winnerid = $safetwoid;
				$winnerscore = $safescoretwo;
				$opponentscore = $safescoreone;
			} else {
				
				// Scores are equal so check if there is a winner selected.
				if(isset($_POST["winner"])){
					
					// Winner is selected. So set the variables for the winning player.
					$safewinnner = htmlspecialchars($_POST["winner"], ENT_QUOTES);
					$winnerid = $safewinnner;
					$winnerscore = ($safewinnner == $oneid ? $safescoreone : $safescoretwo);
					$opponentscore = ($safewinnner == $oneid ? $safescoretwo : $safescoreone);
				} else {
					
					// No winner is selected. So stop and return a error message.
					return "De score is gelijk. Dus er moet een winnaar worden gekozen.<br>De uitslag is niet aangepast.";
				}
			}
		}
		
		// Update the match record in the database.
		if($stmt = $conn->prepare("UPDATE `matches` SET `winner`= ?, `winner_points`= ?, `opponent_points`= ? WHERE `id` = ?")){
			
			$stmt->bind_param("ssss", $winnerid, $winnerscore, $opponentscore, $safeid);
			$stmt->execute();
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-1)");
		}
		
		// Check if new rond is needed.
		checkForNewRond($safetournamentid);
		return "De wedstrijd uitslag is opgeslagen.";
	}
?>