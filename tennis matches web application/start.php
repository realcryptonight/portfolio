<?php

	// Tell the tournamentview.php to not render the page by default.
	$render = false;

	// Require some files that we also need.
	require(__DIR__ . "/proccessor/start.php");
	require(__DIR__ . "/proccessor/rond.php");
	require(__DIR__ . "/tournamentview.php");
	
	// Check if the start button is clicked.
	if(!isset($_POST["start"])){
		
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/tournamentname.html")){
			
			// Get the data from the HTML template.
			$page = file_get_contents(__DIR__ . "/pages/tournamentname.html");
			
			// We need a die here since we do not want the rest of the code to be executed.
			die($page);
		} else {
			die("Er is iets mis gegaan. Probeer het later nog is.");
		}
		
	} else {
		
		// Check if we have a value in the POST name variable.
		if(empty($_POST["name"])){
			die(renderTournamentPage("Reden: Er is geen naam opgegeven."));
		}
	}
	
	// Get the next tournament id.
	$tournamentid = getNextTournamentID();
	
	// Get the amount of players.
	$playercount = getPlayerCount($tournamentid);
	
	// For debug
	//echo $playercount;
	
	// Check if we do not already have a tournament.
	// It should not but we check just to be safe.
	if(hasStartedTournament($tournamentid)){
		
		echo renderTournamentPage("Reden: Er is al een toernooi bezig.");
	} else {
		
		// Check if we have the miminum amount of players.
		if($playercount > 4){
			
			// Check if we have not more then the max amount of players.
			if($playercount <= 128){
				
				// Get the info on how the first ron should be made.
				$info = getFirstRoundInfo($tournamentid);
				
				// For debug
				//echo $info;
				
				// If it successfully adds the matches then redirect to index.php
				if(firstRond($tournamentid, $info, $_POST["name"])){
					
					header('Location: ./tournamentview.php?tournamentid=' . $tournamentid);
				} else {
					
					die("Er is iets mis gegaan. Probeer het later nog is.");
				}
			} else {
				
				// Display the page with an error message.
				echo renderTournamentPage("Reden: Er zijn meer dan 128 spelers.");
			}
		} else {
			
			// Display the page with an error message.
			echo renderTournamentPage("Reden: Er zijn minder dan 4 spelers.");
		}
	}
?>