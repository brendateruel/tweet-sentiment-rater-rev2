<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
	<link href='http://fonts.googleapis.com/css?family=Homemade+Apple' rel='stylesheet' type='text/css'>
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
			echo $session_username;
			} else {
					//header('Location: welcome.php'); 
					echo "<a href=login.php>Sign in</a>";
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
	
	<div id="nav">
		<div id='button'>
			<a href=sentiment-analysis.php><img src=images/analyze.png /></a>
		</div>
	<?php 
			/* CREATING A NEW TABLE FOR EACH USER */

			$new_friends_table = "friends_" . $session_username;
			$new_temp_timeline = "temp_timeline_" . $session_username;

			if (!($mysqli->query("CREATE TABLE {$new_friends_table} LIKE friends"))) {
				echo "<p>Welcome back, " . $session_username . "<br />";
			}
			if (!($mysqli->query("CREATE TABLE {$new_temp_timeline} LIKE temp_timeline"))) {
				echo "We've added your new friends and latest friend updates to your history.</p>";
			}
			/* NEW TABLE CREATION END */
	?>
	</div> 
	<!-- end #nav -->

	<div id="content">
			<div id="new-friends">
				<p>New friends added:</p>
				<?php

					/* UPDATING NEW FRIENDS AND STATUS UPDATES */
					foreach($home_timeline->status as $status) {
						$user = $status->user;
						$tweet = $status->text;
						$tweet = $mysqli->real_escape_string($tweet);
						$date_time = date("Y-m-d H:i:s", strtotime($status->created_at)); 

						if (($query = $mysqli->query("INSERT INTO {$new_friends_table} (user_handle, user_image_URL) VALUES ('{$user->screen_name}', '{$user->profile_image_url}')"))) {
								echo "<img src='{$user->profile_image_url}' />";
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
				?>
			</div>	
			
			<?php
			/* ALL FRIENDS */
			echo "<div id='default-friends-subhead'><h2>Friends</h2></div>";
			//$stmt = $mysqli->stmt_init();
			if (!($stmt = $mysqli->prepare("SELECT DISTINCT {$new_friends_table}.user_handle, {$new_friends_table}.user_image_URL, {$new_temp_timeline}.tweet, {$new_temp_timeline}.sentiment_score, {$new_temp_timeline}.date_time FROM {$new_friends_table} JOIN {$new_temp_timeline} ON {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle WHERE {$new_friends_table}.user_handle = ? GROUP BY {$new_friends_table}.user_handle, {$new_temp_timeline}.date_time DESC ORDER BY {$new_friends_table}.user_handle"))) {
				 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			//$stmt->bind_param('s', $a);
			if (!$stmt->execute()) {
				 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			//$stmt->bind_result($result);
			$res = $stmt->get_result();
			while($row = $res->fetch_assoc()) {
				$user = $row['user_handle'];
				$user_image = $row['user_image_URL'];
				$tweet = $row['tweet'];
					echo "<div id='default-friends'>";
					echo "<img src={$user_image} class=user-image />";
					echo "<div class='user'>{$user}";
					$sentiment_score = $row['sentiment_score'];
						if (is_null($sentiment_score)) {
						echo "<img src=images/new-update.png class=new-marker />";
						}
					echo "</div>";
					echo "<div class='latest-tweet'>Latest ({$row['date_time']}): {$tweet}</div>";
					/*if(strtotime($row['date_time']) >= strtotime('now -24 hours')) {
						echo "<div class='latest-tweet'>Latest {$row['date_time']}: {$tweet}</div>";
						} else {
								echo "<div class='latest-tweet'>No tweets imported recently within 24 hours.</div>";
								}*/
					echo "</div>";
			}
			?>
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