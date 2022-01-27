<?php

	// Require some files that we also need.
	require(__DIR__ . "/proccessor/player.php");

	// If there is no tournament id in the get then go to index.php
	if(!isset($_GET["tournamentid"]) || empty($_GET["tournamentid"])){ header('Location: ./index.php'); }

	// Check if the HTML template file exists.
	if(file_exists(__DIR__ . "/pages/addplayers.html")){
		
		// Get the data from the html template.
		$page = file_get_contents(__DIR__ . "/pages/addplayers.html");
		
		// Add the tournament id to the template.
		$page = str_replace("[tournamentid]", $_GET["tournamentid"], $page);
		
		// Check if the register button is clicked.
		if(isset($_POST["register"])) {
			
			// Check if all the expected variables have a value.
			if(!empty($_POST["firstname"]) && !empty($_POST["lastname"]) && !empty($_POST["school"])){
				
				// Proccess the request and display the result of that request to the user.
				$page = str_replace("<div class=\"registererror\"></div>", "<div class=\"registererror\">" . addPlayer($_POST["firstname"], $_POST["lastname"], $_POST["school"])["info"] . "</div>", $page);
			} else {
				
				// Display an error message on the page.
				$page = str_replace("<div class=\"registererror\"></div>", "<div class=\"registererror\">Er ontbreekt informatie.</div>", $page);
			}
		}
		
		// Check if the upload button is clicked.
		if(isset($_POST["upload"])) {
			
			// Check if a file is given.
			if($_FILES["files"]["name"][0] != null){
				
				// Proccess the request and display the result of that request to the user.
				$page = str_replace("<div class=\"registererror\"></div>", "<div class=\"registererror\">" . addPlayersWithFile()["info"] . "</div>", $page);
			} else {
				
				// Display an error message on the page.
				$page = str_replace("<div class=\"uploaderror\"></div>", "<div class=\"uploaderror\">Er ontbreekt een bestand.</div>", $page);
			}
		}
		
		// Display the page.
		echo $page;
	} else {
		die("Helaas kunnen wij deze pagina momenteel niet weergeven. Probeer het later nog is.");
	}
?>