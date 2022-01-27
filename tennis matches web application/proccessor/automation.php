<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	// Require some files that we also need.
	require(__DIR__ . "/start.php");
	require(__DIR__ . "/rond.php");
	
	/*
	 * Check if an rond has ended. If it has then start a new one.
	 * @param $tournamentid The id of the tournament.
	*/
	function checkForNewRond($tournamentid){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($_GET["tournamentid"], ENT_QUOTES);
		
		// Get the current rond number from the database.
		if($stmt = $conn->prepare("SELECT `rond` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $tournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// Get all the records that match the current rond and do not have a winner.
			if($stmt = $conn->prepare("SELECT `id` FROM `matches` WHERE `tournament` = ? AND `rond` = ? AND `winner` IS NULL")){
				
				$stmt->bind_param("ss", $tournamentid, $rows[0]["rond"]);
				$stmt->execute();
				$result2 = $stmt->get_result();
				$rows2 = resultToArray($result2);
				$stmt->close();
				
				// Check if there are no records that do not have a winner.
				if(empty($rows2)){
					
					// Check if it is the first rond.
					// If it is the first rond then we might need to use different logic.
					if($rows[0]["rond"] == 1){
						
						// Check if we need to do different logic.
						if(getFirstRoundInfo($safetournamentid)["allplayers"] == "false"){
							
							// Use different logic since the players cannot be divided by two.
							
							// For debug
							//echo "Not standard!";
							
							// Get all the players ids that do not go to the next rond.
							$excludedplayers = getOpponents($tournamentid);
							
							// For debug
							//echo "<pre>";
							//print_r($excludedplayers);
							//echo "</pre>";
							
							// Put all the excluded players in a stirng for sql query.
							$excludedplayersasstring = implode("', '", $excludedplayers);
							
							// Get all the players that go to the next rond.
							if($stmt2 = $conn->prepare("SELECT `id` FROM `players` WHERE `tournament_id` = ? AND `id` NOT IN (?)")){
								
								$stmt2->bind_param("ss", $tournamentid, $excludedplayersasstring);
								$stmt2->execute();
								$result2 = $stmt2->get_result();
								$nextplayers = resultToArray($result2);
								$stmt2->close();
								
								// For debug
								//echo "<pre>";
								//print_r($nextplayers);
								//echo "</pre>";
								
								// Randomize the player order.
								shuffle($nextplayers);
								
								// For debug
								//echo "<pre>";
								//print_r($nextplayers);
								//echo "</pre>";
								
								// This will keep track of the array key we need to access.
								$entrykey = 0;
								
								// loop though the array and everytime grab 2 players and put them into a match.
								for($i = 0; $i <= count($nextplayers) / 2 - 1; $i++){
									
									$player1 = $nextplayers[$entrykey];
									$entrykey++;
									$player2 = $nextplayers[$entrykey];
									$entrykey++;
									createMatch($tournamentid, 2, $player1, $player2);
								}
								
								// Increase the rond by one in the database.
								updateRond($tournamentid);
							} else {
								
								die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
							}
							
						} else {
							
							// Use standard logic.
							createNewStandardRond($safetournamentid);
						}
					} else {
						
						// Use standard logic.
						createNewStandardRond($safetournamentid);
					}
				}
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
		}
	}
	
	/*
	 * Increase the rond number of the tournament by one.
	 * @param $tournamentid The id of the tournament.
	*/
	function updateRond($tournamentid){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the current rond number from the database.
		if($stmt = $conn->prepare("SELECT `id`, `rond` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $tournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// Increase the rond number of the first tournament by one.
			$newrondnum = $rows[0]["rond"] + 1;
			
			// Update the rond number in the database with the new rond number.
			if($stmt = $conn->prepare("UPDATE `tournaments` SET `rond`= ? WHERE `id` = ?")){
				
				$stmt->bind_param("ss", $newrondnum, $tournamentid);
				$stmt->execute();
				$stmt->close();
			} else {
				
				die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-1)");
			}
			
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-2)");
		}
	}
	
	/*
	 * This will get all the opponents who have lost in the first rond.
	 * @param $tournamentid The id of the tournament.
	 * @return The ids of the players who have lost in the first rond.
	*/
	function getOpponents($tournamentid){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		if($stmt = $conn->prepare("SELECT `id`, `winner`, `player_1`, `player_2` FROM `matches` WHERE `tournament` = ? AND `rond` = 1 AND `winner` IS NOT NULL")){
			
			$stmt->bind_param("s", $tournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$rows = resultToArray($result);
			$stmt->close();
			
			// Check if we have record(s).
			if(!empty($rows)){
				
				// For debug
				//echo "<pre>";
				//print_r($rows);
				//echo "</pre>";
				
				// An array with the player ids that needs to be returned.
				$excudeids = array();
				
				// Add the ids of the players who have lost to the excludedplayers array.
				for($i = 0; $i <= count($rows) - 1; $i++){
					
					// Check if the first player is the winner.
					if($rows[$i]["winner"] == $rows[$i]["player_1"]){
						
						// For debug
						//echo $rows[$i]["player_2"];
						
						// Since player 1 won we know that player 2 lost and thus needs to be added to the excludedplayers array.
						$excudeids[] = $rows[$i]["player_2"];
					} else {
						
						// For debug
						//echo $rows[$i]["player_1"];
						
						// Since player 2 won we know that player 1 lost and thus needs to be added to the excludedplayers array.
						$excudeids[] = $rows[$i]["player_1"];
					}
				}
				
				// Return the result.
				return $excudeids;
			} else {
				
				die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-1)");
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 3-2)");
		}
	}
	
	/*
	 * Create a new rond by getting all the winners from
	 * the previous rond and put them into a new rond.
	 * @param $tournamentid The id of the tournament.
	*/
	function createNewStandardRond($tournamentid){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// For debug
		//echo "Standard!";
		
		// Get the current rond number.
		if($stmt = $conn->prepare("SELECT `rond` FROM `tournaments` WHERE `id` = ?")){
			
			$stmt->bind_param("s", $tournamentid);
			$stmt->execute();
			$result = $stmt->get_result();
			$currentrond = resultToArray($result)[0]["rond"];
			$rond = $currentrond + 1;
			$stmt->close();
			
			// If currentrond is null then there is no tournament.
			if($currentrond != null) {
				
				// Get all the winner ids of the current rond.
				if($stmt = $conn->prepare("SELECT `winner` AS `id` FROM `matches` WHERE `tournament` = ? AND `rond` = ?")){
					
					$stmt->bind_param("ss", $tournamentid, $currentrond);
					$stmt->execute();
					$result2 = $stmt->get_result();
					$nextplayers = resultToArray($result2);
					$stmt->close();
					
					// For debug
					//echo "<pre>";
					//print_r($nextplayers);
					//echo "</pre>";
					
					// Check if we need a next rond or if it is already the final rond.
					if(count($nextplayers) > 1){
						
						// This will keep track of the array key we need to access.
						$entrykey = 0;
						
						// loop though the array and everytime grab 2 players and put them into a match.
						for($i = 0; $i <= count($nextplayers) / 2 - 1; $i++){
							
							$player1 = $nextplayers[$entrykey];
							$entrykey++;
							$player2 = $nextplayers[$entrykey];
							$entrykey++;
							createMatch($tournamentid, $rond, $player1, $player2);
						}
						
						// Increase the rond by one in the database.
						updateRond($tournamentid);
					}
				} else {
					
					die("Er is iets fout gegaan. Probeer het later nog is. (Error: 4-1)");
				}
			} else {
				
				die("Er is iets fout gegaan. Probeer het later nog is. (Error: 4-2)");
			}
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 4-3)");
		}
	}
?>