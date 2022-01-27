<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }
	
	// Require some files that we also need.
	require(__DIR__ . "/start.php");
	require(__DIR__ . "/rond.php");
	
	/*
	 * Check if an rond has ended. If it has then start a new one.
	*/
	function checkForNewRond(){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the current rond number from the database.
		if($result = $conn->query("SELECT `rond` FROM `tournaments` WHERE `id` = 1")){
			$rows = resultToArray($result);
			$result->close();
			
			// Get all the records that match the current rond and do not have a winner.
			if($stmt = $conn->prepare("SELECT `id` FROM `matches` WHERE `rond` = ? AND `winner` IS NULL")){
				$stmt->bind_param("s", $rows[0]["rond"]);
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
						if(getFirstRoundInfo()["allplayers"] == "false"){
							
							// Use different logic since the players cannot be divided by two.
							
							// For debug
							//echo "Not standard!";
							
							// Get all the players ids that do not go to the next rond.
							$excludedplayers = getOpponents($rows);
							
							// For debug
							//echo "<pre>";
							//print_r($excludedplayers);
							//echo "</pre>";
							
							// Get all the players that go to the next rond.
							if($result2 = $conn->query("SELECT `id` FROM `players` WHERE `id` NOT IN ('" . implode("', '", $excludedplayers) . "')")){
								$nextplayers = resultToArray($result2);
								$result2->close();
								
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
									createMatch(2, $player1, $player2);
								}
								
								// Increase the rond by one in the database.
								updateRond();
							} else {
								die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
							}
							
						} else {
							// Use standard logic.
							createNewStandardRond();
						}
					} else {
						// Use standard logic.
						createNewStandardRond();
					}
				}
			}
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
		}
	}
	
	/*
	 * Increase the rond number of the tournament by one.
	*/
	function updateRond(){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get the record(s) of the tournaments.
		if($result = $conn->query("SELECT `id`, `rond` FROM `tournaments`")){
			$rows = resultToArray($result);
			$result->close();
			
			// Increase the rond number of the first tournament by one.
			$newrondnum = $rows[0]["rond"] + 1;
			
			// Update the rond number in the database with the new rond number.
			if(!$conn->query("UPDATE `tournaments` SET `rond`= " . $newrondnum . " WHERE `id` = 1")){
				die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-1)");
			}
		} else {
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 2-2)");
		}
	}
	
	/*
	 * This will get all the opponents who have lost in the first rond.
	 * @return The ids of the players who have lost in the first rond.
	*/
	function getOpponents(){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Get all the records from the first rond that have a winner.
		if($result = $conn->query("SELECT `id`, `winner`, `player_1`, `player_2` FROM `matches` WHERE `rond` = 1 AND `winner` IS NOT NULL")){
			$rows = resultToArray($result);
			$result->close();
			
			// Check if we have record(s).
			if(!empty($rows)){
				
				// For debug
				//echo "<pre>";
				//print_r($rows2);
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
	*/
	function createNewStandardRond(){
		// Make the connection variable accessible in this function.
		global $conn;
		
		// For debug
		//echo "Standard!";
		
		// Get the current rond number.
		if($result = $conn->query("SELECT `rond` FROM `tournaments` WHERE `id` = 1")){
			$currentrond = resultToArray($result)[0]["rond"];
			$rond = $currentrond + 1;
			$result->close();
			
			// If currentrond is null then there is no tournament.
			if($currentrond != null) {
				
				// The rond is an number (and can only be an number) and thus no risk for sql injection.
				// Get all the winner ids of the current rond.
				if($result2 = $conn->query("SELECT `winner` AS `id` FROM `matches` WHERE `rond` = " . $currentrond)){
					$nextplayers = resultToArray($result2);
					$result2->close();
					
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
							createMatch($rond, $player1, $player2);
						}
						// Increase the rond by one in the database.
						updateRond();
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