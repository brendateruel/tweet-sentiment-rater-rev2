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
			//echo "<a href=twitter_login.php>Sign in</a>";
			header('Location: login.php');
		}
		
	/*Alchemy API SDK*/ 
	include('module/AlchemyAPI_CURL.php');
	include('module/AlchemyAPIParams.php');
	$alchemyObj = new AlchemyAPI();
	$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

//ENDS
		
/* Selecting user's friends */

function average($solution){
	return array_sum($solution)/count($solution) ;
}
	
if (!($stmt = $mysqli->prepare("SELECT user_handle FROM {$new_friends_table}"))) {
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->execute()) {
	 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$res = $stmt->get_result();
while($row = $res->fetch_assoc()) {
	set_time_limit(0);
	$user = $row['user_handle'];	
	$all = $mysqli->query("SELECT sentiment_score FROM {$new_temp_timeline} WHERE user_handle='{$user}'");
	$row_cnt = $all->num_rows;
	$solution = array();
	while ($array = $all->fetch_array(MYSQLI_BOTH)) {
		$solution[]=$array['sentiment_score'];
		}
	echo $user;
	if ($row_cnt > 0) {
		$array_sum = array_sum($solution);
		echo $array_sum;
		if ($array_sum == 0) {
			echo "Average of array: 0";
		} else {
			echo "sum(a) = " . $array_sum . "\n";
			$avg_sentiment_rating = average($solution);
			echo "Average of array:". $avg_sentiment_rating;
			if(!($stmt = $mysqli->query("UPDATE {$new_friends_table} set avg_sentiment_rating='{$avg_sentiment_rating}' WHERE user_handle='{$user}'"))) {
					 echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
		}
	} else {
		echo 'no values to fetch';
		/* Unsure if these should be 0 or NULL...
		if(!($stmt2 = $mysqli->query("UPDATE {$new_friends_table} set avg_sentiment_rating='0' WHERE user_handle='{$user}'"))) {
					 echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error;
				} */
	}
}

/* Need to redirect to results page once completed
if(!empty($_SESSION['username'])){  
	// User is logged in, redirect  
	header('Location: twitter_update.php');
}		
*/


?>

<html>

<h2>Hello <?=(!empty($_SESSION['username']) ? '@' . $_SESSION['username'] : 'Guest'); ?></h2>
<p>

<?php
if (!($stmt = $mysqli->prepare("SELECT user_handle, avg_sentiment_rating FROM {$new_friends_table}"))) {
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->execute()) {
	 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$res = $stmt->get_result();

while($row = $res->fetch_assoc()) {
	set_time_limit(0);
	$user = $row['user_handle'];
	$avg_sentiment_rating = $row['avg_sentiment_rating'];
	if($avg_sentiment_rating > 0) {
		$mood_bg = "positive";
		} elseif($avg_sentiment_rating < 0) {
			$mood_bg = "negative";
			} else {
				$mood_bg = "neutral";
				}
		echo "<div id='{$mood_bg}'>";
	echo $user . "\n";
	echo $avg_sentiment_rating . "\n";
	echo "</div>";
}

?>

</p>
</html>
