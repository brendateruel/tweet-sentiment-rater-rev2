﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
	<link href="http://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet" type="text/css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<title>Goodbye - Tweet Sentiment Rater</title>
</head>

<body>
	<div id="wrapper">

		<div id="header">
			<?php
				//GOES IN HEADER & VARIABLES FILE
				require("../../secret.php");
				require("twitteroauth/twitteroauth-xml.php");
				session_start();

				if(!empty($_SESSION['username'])){  
					$twitteroauth = new TwitterOAuth($tOauth_apiKey, $tOauth_apiSecret, $_SESSION['oauth_token'], $_SESSION['oauth_secret']);  
					$session_username = $_SESSION['username'];
				/* Updating user's friends and timeline tables */
					$new_friends_table = "friends_" . $session_username;
					$new_temp_timeline = "temp_timeline_" . $session_username; 
					$home_timeline = $twitteroauth->get('statuses/home_timeline', array('count' => 200));
				}

				echo "<div class='logo'>happy meter</div>";

				/*Alchemy API SDK*/ 
				include('module/AlchemyAPI_CURL.php');
				include('module/AlchemyAPIParams.php');
				$alchemyObj = new AlchemyAPI();
				$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

				//ENDS
			?>
		</div>
		
		<!-- DON'T NEED NAV FOR THIS PAGE -->		
		<!-- <div id="nav"></div> --> 
		<!-- end #nav -->

		<div id="content">
			<?php
				if(!empty($_SESSION['username'])){  
					if(!($stmt = $mysqli-> prepare("DELETE FROM users WHERE username='{$session_username}'"))) {
						echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if(!($stmt->execute())) {
						echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
					} else {
						echo "<div class='goodbye'>Goodbye.</div>";
					}
					$_SESSION = array();
					session_destroy();
					echo "<div class='welcome'>You've been logged out. Come back soon!</div>";
				} else {
					header('Location: welcome.php');
				}
				echo "<div id='user-login'>";
				if(!empty($_SESSION['username'])){  
					echo "Welcome, {$session_username}!";
				} else {
					//header('Location: welcome.php'); 
					echo "<a href='login.php' class='button'>Sign in</a>";
				}
				echo "</div>";
			?>
		</div>
		<!-- end #content -->

		<div id="footer">
			<p class="footer">
				this web application uses the <a href="http://www.twitter.com" target="_blank">Twitter</a> API + <a href="http://www.alchemyapi.com" target="_blank"><img src="images/alchemy-api.png" alt="Alchemy API logo"/></a> + <a href="http://github.com/abraham/twitteroauth/" target="_blank">twitteroauth PHP library</a> by Abraham Williams<br />
				for more info: contact <a href="mailto:bgteruel@gmail.com">bgteruel[at]gmail[dot]com</a>
			</p>
		</div>
		<!-- end #footer -->

	</div>
	<!-- End #wrapper -->

</body>
</html>