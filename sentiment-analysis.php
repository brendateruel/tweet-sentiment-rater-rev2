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
			//header('Location: welcome.php'); 
			//echo "<a href=login.php>Sign in</a>";
			header('Location: login.php');
		}
		
	/*Alchemy API SDK*/ 
	include('module/AlchemyAPI_CURL.php');
	include('module/AlchemyAPIParams.php');
	$alchemyObj = new AlchemyAPI();
	$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

//ENDS

/* Selecting user's friends */
$stmt = $mysqli->stmt_init();
if (!($stmt = $mysqli->prepare("SELECT user_handle FROM {$new_friends_table}"))) {
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->execute()) {
	 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$stmt->bind_result($user);
while($row = $stmt->fetch()) {
/* Selecting each friend's tweets, NEED TO ADD DATE SELECTION? */
		$stmt = $mysqli->stmt_init();
		if(!($stmt = $mysqli->prepare("SELECT tweet, status_ID FROM {$new_temp_timeline} WHERE user_handle='{$user}' AND sentiment_score IS NULL AND date_time >= SYSDATE() - INTERVAL 1 DAY"))) {
				 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
		if (!$stmt->execute()) {
				 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
		$stmt->bind_result($tweet, $status_ID);
/* Running each tweet through Alchemy API sentiment analysis */
		while($row2 = $stmt->fetch()) {
			set_time_limit(0);
			$status_ID = $mysqli->real_escape_string($status_ID);
			$response = $alchemyObj->TextGetTextSentiment($tweet);
			$result = simpleXML_load_string($response);
			$sentiment = $result->docSentiment;
			$mood = $sentiment->type;
			$score = $sentiment->score;
			echo $score . "\n";
			$score = $mysqli->real_escape_string($score);
/* Writing sentiment score to timeline table */
			$stmt = $mysqli->stmt_init();
			if(!($stmt = $mysqli->prepare("UPDATE {$new_temp_timeline} SET sentiment_score='{$score}' WHERE status_ID='{$status_ID}'"))) {
					 echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
			$stmt->bind_param('i', $score);
			if (!$stmt->execute()) {
				 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
			$stmt->close();
			}
}

$stmt = $mysqli->stmt_init();
if(!($check = $mysqli->prepare("SELECT tweet, status_ID FROM {$new_temp_timeline} WHERE user_handle='{$user}' AND sentiment_score IS NULL"))){  
	echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$stmt->bind_result($tweet, $status_ID);
if (!$check->execute()) {
	echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
}	else {
		if($check->num_rows==0) {
			header('Location: ratings.php');
			} else {
			echo $check->num_rows . "not analyzed";
			}
		}
?>