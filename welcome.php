﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
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
			$home_timeline = $twitteroauth->get('statuses/home_timeline', array('count' => 500));
		}

		echo "Happy Meter";
		echo "<div id='user-login'>";
		if(!empty($_SESSION['username'])){  
			echo "Welcome, {$session_username}!";
			} else {
					//header('Location: welcome.php'); 
					echo "<a href=twitter_login.php>Sign in</a>";
					//header('Location: login.php');
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
	
	<div id="nav"></div> 
	<!-- end #nav -->

	<div id="content">
		<?php
/* 		if(!empty($_SESSION['username'])){  
			// User is logged in, redirect  
			header('Location: friends.php');
		} */
		?>
		<p class="welcome">
		They say,</br>
		"happiness is contagious”</br>
		“birds of a feather flock together”</br>
		and...</br>
		“misery loves company”</br>
		so did you ever think your flock</br>
		was bringing you down or picking you up?</br>
		Use the Tweet Sentiment Rater App to:</br>
		* Pick out your friends that tend to</br> 
		lift up your spirits</br>
		* Identify friends that could use</br> 
		a little extra sunshine today</br>
		</p>
		<p class="welcome">
		<a href="login.php">Login or Sign up with Twitter now</a>
		</p>
	</div>
	<!-- end #content -->

	<div id="sidebar"></div>
	<!-- end #sidebar -->

	<div id="footer"></div>
	<!-- end #footer -->

</div>
<!-- End #wrapper -->


</body>
</html>