<?php
	// Check if we need to start a database connection.
	if(!isset($conn)) { require(__DIR__ . "/../config/connection.php"); }

	/*
	 * Add a player to the players table.
	 * @param $firstname The first name of the player.
	 * @param $lastname The last name of the player.
	 * @param $school The name of the school of the player.
	 * @return Success of failure information.
	*/
	function addPlayer($firstname = null, $lastname = null, $school = null){
		
		// Make the connection variable accessible in this function.
		global $conn;
		
		// Prevent HTML injection.
		$safefirstname = htmlspecialchars($firstname, ENT_QUOTES);
		$safelastname = htmlspecialchars($lastname, ENT_QUOTES);
		$safeschool = htmlspecialchars($school, ENT_QUOTES);
		
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
		} else {
			
			die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-1)");
		}
		
		// Check if the vaiables are not empty.
		if(!empty($safefirstname) && !empty($safelastname) && !empty($safeschool)){
			
			// Insert the player into the players table.
			if($stmt = $conn->prepare("INSERT INTO `players`(`firstname`, `lastname`, `school`, `tournament_id`) VALUES (?,?,?,?)")){
				
				$stmt->bind_param("ssss", $safefirstname, $safelastname, $safeschool, $newtournamentid);
				$stmt->execute();
				$stmt->close();
				
				// Return a success.
				return array("success" => "true", "info" => "De speler is toegevoegt.");
			} else {
				
				die("Er is iets fout gegaan. Probeer het later nog is. (Error: 1-2)");
			}
		} else {
			
			// We are missing some info. So return a failure.
			return array("success" => "false", "info" => "Er ontbreekt informatie.");
		}
	}
	
	function addPlayersWithFile() {
		
		// Count the amount of uploaded files.
		$countfiles = count($_FILES['files']['name']);
		
		// For debug
		//echo $countfiles;
		//echo "<pre>";
		//print_r($_FILES);
		//echo "</pre>";
		
		// Loop though all the uploaded records.
		for($i=0;$i<$countfiles;$i++){
			
			// Check if we have an uploaded file.
			$data = file_get_contents($_FILES["files"]["tmp_name"][$i]);
			
			// Load the data as xml.
			$data = simplexml_load_string($data);
			
			// Convert the xml data into json.
			$json = json_encode($data);
			  
			// Convert the json data into php array.
			$data = json_decode($json, true);
			
			// Trigger for each player record an addPlayer function.
			foreach($data["aanmelding"] as $player){
				
				$firstname = $player["spelervoornaam"];
				$lastname = (empty($player["spelertussenvoegsels"]) ? $player["spelerachternaam"] : $player["spelertussenvoegsels"] . " " . $player["spelerachternaam"]);
				$school = $player["schoolnaam"];
				addPlayer($firstname, $lastname, $school);
			}
		}
		// Return success.
		return array("success" => "true", "info" => "Alle spelers zijn toegevoegt.");
	}
?>