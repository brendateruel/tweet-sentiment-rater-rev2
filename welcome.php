﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
	<link href="http://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet" type="text/css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<title>Welcome - Tweet Sentiment Rater</title>
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
				echo "<div id='user-login'>";
				if(!empty($_SESSION['username'])){  
					echo "Welcome back, {$session_username}!";
					} else {
						echo "<a href='login.php' class='button'>Sign in</a>";
					}
				echo "</div>";
				
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

			<p class="welcome">
				<img src="images/positive.png" id="happy" alt="happy icon" />
				<img src="images/negative.png" id="sad" alt="sad icon" /><br />
				They say,<br />
				"happiness is contagious”<br />
				“birds of a feather flock together”<br />
				and...<br />
				“misery loves company”<br />
				so did you ever think your flock<br />
				was bringing you down or picking you up?<br />
				Use the Tweet Sentiment Rater App to:<br />
				* Pick out your friends that tend to<br /> 
				lift up your spirits<br />
				* Identify friends that could use<br /> 
				a little extra sunshine today<br />
			</p>
			<p class="welcome">
				<a href="login.php" class="button">Login or Sign up with Twitter now</a>
			</p>
			
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