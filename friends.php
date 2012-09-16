<?php

//GOES IN HEADER & VARIABLES FILE
require("../../secret.php");
require("twitteroauth/twitteroauth-xml.php");
session_start();

/* Authenticating session */
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
			header('Location: twitter_login.php');
		}
//ENDS
		
echo "<div id='button'><a href=sentiment-analysis.php>Analyze now</a></div>";

/* CREATING A NEW TABLE FOR EACH USER */

$new_friends_table = "friends_" . $session_username;
$new_temp_timeline = "temp_timeline_" . $session_username;

if (!($mysqli->query("CREATE TABLE {$new_friends_table} LIKE friends"))) {
	echo "Welcome back, " . $session_username;
	echo "\r\n";
}
if (!($mysqli->query("CREATE TABLE {$new_temp_timeline} LIKE temp_timeline"))) {
	echo "We'll just add your latest friend updates to your history.";
	echo "\n";
}

/* NEW TABLE CREATION END */

echo "<div id='new-friends'>";
echo "NEW FRIENDS:";
echo "\n";

/* UPDATING NEW FRIENDS AND STATUS UPDATES */
foreach($home_timeline->status as $status) {
	$user = $status->user;
	$tweet = $status->text;
	$tweet = $mysqli->real_escape_string($tweet);
	$date_time = date("Y-m-d H:i:s", strtotime($status->created_at)); 

	if (($query = $mysqli->query("INSERT INTO {$new_friends_table} (user_handle, user_image_URL) VALUES ('{$user->screen_name}', '{$user->profile_image_url}')"))) {
			echo "<div class='new'>";
			echo "<img src='{$user->profile_image_url}' />";
			echo "</div>";
		}
	$query = $mysqli->query("INSERT INTO {$new_temp_timeline} (user_handle, status_id, date_time, tweet) VALUES ('{$user->screen_name}', '{$status->id}', '{$date_time}', '{$tweet}')");
		/* FRIENDS WITH NEW UPDATES
			echo "No new updates.";
			}	else {
				echo "The most recent tweets from your timeline have been added to our database.";
				echo "<div class='new'>";
				echo "{$user->screen_name} has a new status update";
				echo "\n";
				echo "</div>";
				}
		*/
	/* mysqli_free_result($mysqli); -- is this needed?*/
}

echo "</div>";	
	
if (!($stmt = $mysqli->prepare("SELECT DISTINCT {$new_friends_table}.user_handle, {$new_friends_table}.user_image_URL, {$new_temp_timeline}.sentiment_score FROM {$new_friends_table} JOIN {$new_temp_timeline} ON {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle GROUP BY {$new_friends_table}.user_handle"))) {
	 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
if (!$stmt->execute()) {
	 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$res = $stmt->get_result();

while($row = $res->fetch_assoc()) {
	$user = $row['user_handle'];
	$user_image = $row['user_image_URL'];
		echo "<div id='default-friends'>";
		echo "<img src='{$user_image}' />";
		echo $user . "\n";
	$sentiment_score = $row['sentiment_score'];
		if (is_null($sentiment_score)) {
			echo "new update";
		}
	echo "</div>";
}
?>