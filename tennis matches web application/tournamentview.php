<?php

	// Require some files that we also need.
	require(__DIR__ . "/proccessor/tournament.php");
	
	// Check if we need to display the page.
	if(!isset($render)){
		// If there is no tournament id in the get then go to index.php
		if(!isset($_GET["tournamentid"]) || empty($_GET["tournamentid"])){ header('Location: ./index.php'); }
		
		// Render the tournament page.
		echo renderTournamentPage();
	}
	
	/*
	 * Display the tournament page with the results.
	 * @param $message The message you also want to display. (Optional)
	*/
	function renderTournamentPage($message = null) {
		
		// Prevent HTML injection.
		$safetournamentid = htmlspecialchars($_GET["tournamentid"], ENT_QUOTES);
		
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/tournament.html")){
			
			// Get the data from the HTML template.
			$page = file_get_contents(__DIR__ . "/pages/tournament.html");
			
			// Add the tournament id to the template.
			$page = str_replace("[tournamentid]", $_GET["tournamentid"], $page);
			
			// Check if the tournament is started.
			if(hasStartedTournament($safetournamentid)){
				
				// Save the data from the tournament for later.
				$data = getTournament($safetournamentid);
				
				// A string that will contain the HTML code for the content.
				$content = "";
				
				// This will keep track of the rond info for later.
				$rondinfo = array();
				
				// For debug
				//echo "<pre>";
				//print_r($data["data"]);
				//echo "</pre>";
				
				// For later when checking if its the final rond.
				$i = 0;
				
				// Store the total match count.
				$totalmatches = count($data["data"]);
				
				// Loop though the tournament data.
				foreach($data["data"] as $rond){
					
					$i++;
					// If the key exists then add 1 and if not then create the key in the array and give it the value 1.
					if(empty($rondinfo[$rond["rond"]])){
						
						// Set the key value to one.
						$rondinfo[$rond["rond"]] = 1;
						
						// Check if this is an new rond and if so then add the closing div for the previous rond.
						if(!empty($rondinfo[$rond["rond"]-1])){
							$content .= "</div>";
						}
						
						// Add the divs for correct display.
						$content .= "
							<div class=\"central rond" . $rond["rond"] . "\">
							<p>Ronde: " . $rond["rond"] . "</p>
						";
					} else {
						
						// Increase the key value by one.
						$rondinfo[$rond["rond"]]++;
					}
					
					// Add the first player to the content.
					$content .= "
							<div class=\"player" . (empty($rond["winner"]) ? "" : ($rond["winner"] == $rond["player_1"] ? " winner" : " lost")) . " rond" . $rond["rond"] . "\">
								<p>Naam: " . $rond["players"][$rond["player_1"]]["name"] . "</p>
								<p>School: " . $rond["players"][$rond["player_1"]]["school"] ."</p>
								<p>Score: " . ($rond["winner"] == $rond["player_1"] ? $rond["winner_points"] : $rond["opponent_points"]) . " - " . ($rond["winner"] == $rond["player_2"] ? $rond["winner_points"] : $rond["opponent_points"]) ."</p>
							</div>";
					
					// Add the second player to the content.
					$content .= "
							<div class=\"player" . (empty($rond["winner"]) ? "" : ($rond["winner"] == $rond["player_2"] ? " winner" : " lost")) . " rond" . $rond["rond"] . "\">
								<p>Naam: " . $rond["players"][$rond["player_2"]]["name"] . "</p>
								<p>School: " . $rond["players"][$rond["player_2"]]["school"] ."</p>
								<p>Score: " . ($rond["winner"] == $rond["player_2"] ? $rond["winner_points"] : $rond["opponent_points"]) . " - " . ($rond["winner"] == $rond["player_1"] ? $rond["winner_points"] : $rond["opponent_points"]) . "</p>
							</div>";
					
					// If the current rond only has one match and its not the first match then it is the final match.
					// So we need to add the winner of that match one more time.
					if($data["current_rond"] > 1 && !empty($rondinfo[$data["current_rond"]]) && $i == $totalmatches){
						
						// Check if the final round has a winner.
						if(!empty($rond["winner"])){
							
							// Add the divs with the winner.
							$content .= "
									</div>
									<div class=\"central rond" . ($data["current_rond"] + 1) . "\">
										<p>Winnaar: </p>
									<div class=\"player winner rond" . ($data["current_rond"] + 1) . "\">
										<p>Naam: " . $rond["players"][$rond["winner"]]["name"] ."</p>
										<p>School: " . $rond["players"][$rond["winner"]]["school"] ."</p>
									</div>";
						}
					}
				}
				
				// Place the content variable data into the place holder.
				$page = str_replace("[content]", $content, $page);
			} else {
				
				// Check if we need to display a message.
				if($message == null){
					// Display the text that there is not yet an tournament.
					$page = str_replace("[content]", "Er is  nog geen toernooi gestart met de opgegeven informatie.", $page);
				} else {
					// Display the text that there is not yet an tournament. And the message.
					$page = str_replace("[content]", "Er is  nog geen toernooi gestart.<br>" . $message, $page);
				}
			}
			
			// Return the page.
			return $page;
		} else {
			die("Helaas kunnen wij deze pagina momenteel niet weergeven. Probeer het later nog is.");
		}
	}
?>