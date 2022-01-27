<?php

	// Tell the index.php to not render the page by default.
	$render = false;

	// Require some files that we also need.
	require(__DIR__ . "/proccessor/start.php");
	require(__DIR__ . "/proccessor/rond.php");
	require(__DIR__ . "/index.php");
	
	// Get the amount of players.
	$playercount = getPlayerCount();
	
	// For debug
	//echo $playercount;
	
	// Check if we do not already have a tournament.
	if(hasStartedTournament()){
		echo renderTournamentPage("Reden: Er is al een toernooi bezig.");
	} else {
		// Check if we have the miminum amount of players.
		if($playercount > 4){
			// Check if we have not more then the max amount of players.
			if($playercount <= 128){
				// Get the info on how the first ron should be made.
				$info = getFirstRoundInfo();
				
				// For debug
				//echo $info;
				
				// If it successfully adds the matches then redirect to index.php
				if(firstRond($info)){
					header('Location: ./index.php');
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