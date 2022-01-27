<?php

	// Require some files that we also need.
	require(__DIR__ . "/proccessor/tournament.php");
	
	// Check if there is already an tournamentid set.
	if(isset($_GET["tournamentid"])){
		
		// We have already an tournament id. So redirect to the tournament view with the given id.
		header('Location: ./tournamentview.php?tournamentid=' . $_GET["tournamentid"]);
	} else {
		
		// Check if the HTML template file exists.
		if(file_exists(__DIR__ . "/pages/index.html")){
			
			// Get the data from the HTML template.
			$page = file_get_contents(__DIR__ . "/pages/index.html");

			// Get the IDs of all existing tournaments.
			$tournamentids = getTournamentIDs();
			
			// Display all the tournaments.
			$content = "";
			
			if($tournamentids["success"] == "true"){
				
				foreach($tournamentids["data"] as $tournament){
					
					// Add the hmtl code with the link to the tournament view page.
					$content .= "<p>Toernooi: <a href=\"tournamentview.php?tournamentid=" . $tournament["id"] . "\">" . $tournament["name"] . "</a></p>";
				}
			}
			
			// Add the content variable to the template page.
			$page = str_replace("[content]", $content, $page);
			
			echo $page;
		} else {
			die("error 1");
		}
	}
?>