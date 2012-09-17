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

if(!empty($_SESSION['username'])){  
	echo "{$session_username}'s Friends";
	} else {
			header('Location: twitter_login.php');
		}
		
	/*Alchemy API SDK*/ 
	include('module/AlchemyAPI_CURL.php');
	include('module/AlchemyAPIParams.php');
	$alchemyObj = new AlchemyAPI();
	$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

//ENDS
?>

	<div id="header">
		<h2>hello

		</h2>
		
	</div>
	<!-- end #header -->