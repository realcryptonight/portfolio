<?php
	// Require some files that we also need.
	require(__DIR__ . "/proccessor/editplayers.php");
	
	// If there is no tournament id in the get then go to index.php
	if(!isset($_GET["tournamentid"]) || empty($_GET["tournamentid"])){ header('Location: ./index.php'); }
	
	// Check if the update existing player button is clicked.
	if(isset($_POST["update"])){
		
		// Check if all needed variables have a value.
		if(!empty($_POST["id"]) && !empty($_POST["firstname"]) && !empty($_POST["lastname"]) && !empty($_POST["school"])){
			
			// Trigger the update player function.
			updatePlayer();
			
			// Display the page with the new result and a success message.
			echo renderPage("De speler is aangepast.");
		} else {
			
			// Display the page with an error message.
			echo renderPage("De speler is niet aangepast door ontbrekende informatie.");
		}
		
	} else {
		// Check if the user wants to do an action.
		if(empty($_GET["action"])){
			
			// no action wanted so just render the page.
			echo renderPage();
		} else {
			
			// Check if the user wants to edit a player.
			if($_GET["action"] == "edit"){
				
				// Check if there is an player id given.
				if(!empty($_GET["id"])){
					
					// Get the player that is specified.
					$player = getPlayer($_GET["id"]);
					
					// Check if the getPlayer was successfull.
					if($player["success"] == "true"){
						
						// Display the edit page.
						echo renderEditPlayerPage($player["data"]);
					} else {
						
						// Failed to get the player. Just render the normal page with a message.
						renderPage("Helaas kunnen wij de speler momenteel niet ophalen. Probeer het later nog is.");
					}
				} else {
					
					// No player id so redirect to editplayers.php without the GET parameters.
					header('Location: ./editplayers.php?tournamentid=' . $_GET["tournamentid"]);
				}
			} else {
				
				// Check if the user wants to delete a player.
				if($_GET["action"] == "delete"){
					
					if(!empty($_GET["id"])){
						
						// Try deleting the user.
						if(deletePlayer($_GET["id"])){
							
							// Display the page with the new result and a success message.
							echo renderPage("Als de speler niet in een toernooi zit dan is hij nu verwijderd.");
						} else {
							
							// Display the page with an error message.
							echo renderPage("Helaas is de speler verwijderen mislukt.");
						}
					} else {
						
						// No valid player id found. Redirecting to the editplayers.php without the GET parameters.
						header('Location: ./editplayers.php?tournamentid=' . $_GET["tournamentid"]);
					}
				} else {
					
					// No valid action found. Redirecting to the editplayers.php without the GET parameters.
					header('Location: ./editplayers.php?tournamentid=' . $_GET["tournamentid"]);
				}
			}
		}
	}
	
	/*
	 * Render the player page.
	 * @param $message The message that also needs to be displayed. (Optional)
	 * @return The page.
	*/
	function renderPage($message = null) {
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/editplayers.html")){
			
			// Get the data from the HTML template.
			$page = file_get_contents(__DIR__ . "/pages/editplayers.html");
			
			// Add the tournament id to the template.
			$page = str_replace("[tournamentid]", $_GET["tournamentid"], $page);
			
			// Get all players.
			$data = getAllPlayers($_GET["tournamentid"]);
			
			// For debug
			//echo "<pre>";
			//print_r($data);
			//echo "</pre>";
			
			// Check if we need to display a message and handle it acordenly.
			if($message == null){
				
				$content = "
						<table>
							<tr>
								<th>Voornaam</th>
								<th>Achternaam</th>
								<th>School</th>
								<th>Opties</th>
							</tr>";
			} else {
				
				$content = "
						<div class=\"message\">" . $message . "</div>
						<table>
							<tr>
								<th>Voornaam</th>
								<th>Achternaam</th>
								<th>School</th>
								<th>Opties</th>
							</tr>";
			}
			
			// Loop though all the players and add them to the display with the options.
			foreach($data as $player){
				
				$content .= "
							<tr>
								<td>" . $player["firstname"] . "</td>
								<td>" . $player["lastname"] . "</td>
								<td>" . $player["school"] . "</td>
								<td><a href=\"./editplayers.php?tournamentid=" . $_GET["tournamentid"] . "&action=edit&id=" . $player["id"] ."\">Aanpassen</a> | <a href=\"./editplayers.php?tournamentid=" . $_GET["tournamentid"] . "&action=delete&id=" . $player["id"] ."\">Verwijderen</a></td>";
			}
			
			$content .= "</table>";
			
			// Return the page with the players info.
			return str_replace("[content]", $content, $page);
		} else {
			die("Helaas kunnen wij deze pagina momenteel niet weergeven. Probeer het later nog is.");
		}
	}
	
	/*
	 * Render the edit player page.
	 * @param $player The player array.
	 * @return The page.
	*/
	function renderEditPlayerPage($player){
		
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/editexistingplayer.html")){
			
			// Get the data from the HTML template.
			$page = file_get_contents(__DIR__ . "/pages/editexistingplayer.html");
			
			// Add the tournament id to the template.
			$page = str_replace("[tournamentid]", $_GET["tournamentid"], $page);
			
			// Replace the place holders with the correct information.
			$page = str_replace("[id]", $player["id"], $page);
			$page = str_replace("[firstname]", $player["firstname"], $page);
			$page = str_replace("[lastname]", $player["lastname"], $page);
			
			// Return the edit page with the player info.
			return str_replace("[school]", $player["school"], $page);
		} else {
			die("Helaas kunnen wij deze pagina momenteel niet weergeven. Probeer het later nog is.");
		}
	}
?>