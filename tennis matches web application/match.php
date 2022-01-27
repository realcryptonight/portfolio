<?php
	// Require some files that we also need.
	require(__DIR__ . "/proccessor/tournament.php");
	require(__DIR__ . "/proccessor/match.php");
	
	// Check if we need to update an record.
	if(isset($_POST["update"])){
		// Check if all variables are given.
		if(!empty($_POST["id"]) && !empty($_POST["winner"]) && isset($_POST["scorewinner"]) && isset($_POST["scoreopponent"])){
			// Check if the scores are actually numbers.
			if(is_numeric($_POST["scorewinner"]) && is_numeric($_POST["scoreopponent"])){
				// Check if the scores are bellow 10 since 10 or more cannot be saved in the database.
				if(intval($_POST["scorewinner"]) < 10 && intval($_POST["scoreopponent"]) < 10){
					// Update the match with the result.
					updateMatch($_POST["id"], $_POST["winner"], $_POST["scorewinner"], $_POST["scoreopponent"]);
					// Display the page.
					echo renderMatchesPage("De wedstrijd uitslag is opgeslagen.");
				} else {
					// Display the page with an error message.
					echo renderMatchesPage("De score(s) waren te hoog.<br>De uitslag is niet aangepast.");
				}
			} else {
				// Display the page with an error message.
				echo renderMatchesPage("De score(s) waren geen getallen. Maar moeten wel getallen zijn.<br>De uitslag is niet aangepast.");
			}
		} else {
			// Display the page with an error message.
			echo renderMatchesPage("Er ontbraken gegevens bij het doorgeven van de wedstrijd uitslag.<br>De uitslag is niet aangepast.");
		}
	} else {
		// Check if there is a match given.
		if(isset($_GET["match"])){
			// Get the given match info.
			$match = getMatch($_GET["match"]);
			// Check if the getMatch was successfull.
			if($match["success"] == "true"){
				// Check if the HTML template file exists.
				if(file_exists(__DIR__ . "/pages/match.html")){
					// Get the data from the HTML template.
					$page = file_get_contents(__DIR__ . "/pages/match.html");
					
					// For debug
					//echo "<pre>";
					//print_r($match);
					//echo "</pre>";
					
					// Replace the place holder with the correct information.
					$page = str_replace("[id]", $match["data"][0]["id"], $page);
					$page = str_replace("[winnaar1]", "<option value=\"" . $match["data"][0]["player_1"] . "\">" . $match["data"][0]["players"][$match["data"][0]["player_1"]]["name"] ."</option>", $page);
					$page = str_replace("[winnaar2]", "<option value=\"" . $match["data"][0]["player_2"] . "\">" . $match["data"][0]["players"][$match["data"][0]["player_2"]]["name"] ."</option>", $page);
					
					// Display the page.
					echo $page;
				}
			} else {
				// Match was not found. Redirecting to the main match page.
				header('Location: ./match.php');
			}
		} else {
			// Display the page.
			echo renderMatchesPage();
		}
	}
	
	/*
	 * Get the match page with all current ronds.
	 * @param $message The message you want to display on the page. (Optional)
	 * @return The page.
	*/
	function renderMatchesPage($message = null) {
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/viewmatches.html")){
			// Get the data from the html template.
			$page = file_get_contents(__DIR__ . "/pages/viewmatches.html");
			
			// Get the current rond info.
			$data = getRond();
			// For debug
			//echo "<pre>";
			//print_r($data);
			//echo "</pre>";
			
			// Check if the getRond was successfull.
			if($data["success"] == "true"){
				$content = "";
				
				// Loop though the matches and add them to the content variable.
				foreach($data["data"] as $match){
					$content .= $match["id"] . ". <a href=\"./match.php?match=" . $match["id"] . "\">" . $match["players"][$match["player_1"]]["name"] . " - " . $match["players"][$match["player_2"]]["name"] . "</a><br>";
				}
				
				// Replace the place holder with the content.
				$page = str_replace("[content]", $content, $page);
				
				// Check if we need to add a message.
				if($message != null){
					// Add the message to the page.
					$page = str_replace("<div class=\"updateerror\"></div>", "<div class=\"updateerror\">" . $message . "</div><br>", $page);
				}
				
				// Return the page.
				return $page;
			} else {
				// Replace the place holder with the correct information.
				$page = str_replace("[content]", "", $page);
				$page = str_replace("<div class=\"updateerror\"></div>", "<div class=\"updateerror\">Het lijkt erop dat er nog geen toernooi is gestart.</div><br>", $page);
				
				// Return the page.
				return $page;
			}
		}
	}

?>