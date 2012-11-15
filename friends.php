<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
	<link href="http://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet" type="text/css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<title>Friends - Tweet Sentiment Rater</title>
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
					$allfriends = $twitteroauth->get('friends/ids', array('screen_name' => $session_username));
				}

				echo "<div class='logo'>happy meter</div>";
				echo "<div id='user-login'>";
				if(!empty($_SESSION['username'])){  
					echo "<div id='menu'>";
					echo "{$session_username}<img src='images/arrow-down-white.gif' alt='arrow down' />";
					echo "<div class='logout'><a href='logout.php'>Log Out</a></div>";
					echo "</div>";
					} else {
						header('Location: welcome.php'); 
						//echo "<a href=login.php class=button>Sign in</a>";
					}
				echo "</div>";
			?>
			
			<script>
				$("#menu").click(function() {
					$(".logout").show();
					$("#menu").toggleClass("login", true);
				});
				$("body").click(function() {
					$(".logout").hide();
					$("#menu").toggleClass("login", false);
				});
				$("#menu").click(function(e) {
					e.stopPropagation();
				});
			</script>

			<?php			
				/*Alchemy API SDK*/ 
				include('module/AlchemyAPI_CURL.php');
				include('module/AlchemyAPIParams.php');
				$alchemyObj = new AlchemyAPI();
				$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

				//ENDS
			?>
		</div>
		
		<div id="nav">
			<div id="button">
				<img src="images/ajax-loader.gif" class="loading" alt="loading icon" />
				<a href="sentiment-analysis.php" class="button">Analyze now!</a>
				<script>
					$(".button").click(function() {
						$(".button").hide();
						$(".loading").show();
					});
				</script>
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
									echo "<img src='{$user->profile_image_url}' alt='{$user}' />";
								}
							$query = $mysqli->query("INSERT INTO {$new_temp_timeline} (user_handle, status_id, date_time, tweet) VALUES ('{$user->screen_name}', '{$status->id}', '{$date_time}', '{$tweet}')");
						}
					
						/* Selecting most recent tweets */
						if (!($stmt = $mysqli->prepare("SELECT user_handle FROM {$new_friends_table}"))) {
							 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						if (!$stmt->execute()) {
							 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						$res = $stmt->get_result();
						while($row = $res->fetch_assoc()) {
							$user = $row['user_handle'];
						/* Selecting each friend's tweets, NEED TO ADD DATE SELECTION? */
							if(!($stmt2 = $mysqli->prepare("SELECT tweet, date_time FROM {$new_temp_timeline} WHERE user_handle='{$user}' ORDER BY date_time DESC LIMIT 1"))) {
									 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
								}
							if(!$stmt2->execute()) {
									 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
								}
							$res2 = $stmt2->get_result();
							while($row2 = $res2->fetch_assoc()) {
								set_time_limit(0);
								$max = $row2['date_time'];
								$latest = $row2['tweet'];
								$latest = $mysqli->real_escape_string($latest);
								//echo $user;
								//echo $max;
								//echo $latest;
								if(!($stmt3 = $mysqli->query("UPDATE {$new_friends_table} SET last_tweet='{$latest}', last_update='{$max}' WHERE user_handle='{$user}'"))) {
									echo "Query failed: (" . $mysqli->errno . ") " . $mysqli->error;
								}
							}
						}
					?>
				</div>	
				
				<?php
					function twitterify($tweet) {
					  $tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $tweet);
					  $tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $tweet);
					  $tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);
					  $tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweet);
					  return $tweet;
					}
					
					/* ALL FRIENDS */
					echo "<div id='default-friends-subhead'><h2>Friends</h2></div>";
					if (!($stmt4 = $mysqli->prepare("SELECT 
						{$new_friends_table}.user_handle,
						{$new_friends_table}.user_image_URL,
						{$new_friends_table}.last_tweet,
						{$new_temp_timeline}.sentiment_score,
						{$new_friends_table}.last_update
								FROM {$new_friends_table}
									JOIN {$new_temp_timeline}
										ON {$new_friends_table}.last_tweet={$new_temp_timeline}.tweet AND {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle
											GROUP BY {$new_friends_table}.user_handle"))) {
						 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if (!$stmt4->execute()) {
						 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					$stmt4->bind_result($user, $user_image, $last_tweet, $sentiment_score, $last_update);
					while($row = $stmt4->fetch()) {
						$last_tweet = twitterify($last_tweet);
						echo "<div id='default-friends'>";
						echo "<img src='{$user_image}' class='user-image' alt='{$user}' />";
						echo "<div class='user'><a href='http://www.twitter.com/{$user}' target='_blank'>{$user}</a>";
					/*$sentiment_score = $row['sentiment_score'];*/
						if (is_null($sentiment_score)) {
							echo "<span class='new-marker'>new update!</span>";
						}
						echo "</div>";
						if(strtotime($last_update) >= strtotime('now -24 hours')) {
							echo "<div class='latest-tweet'>Latest ({$last_update}): {$last_tweet}</div>";
						} else {
							echo "<div class='latest-tweet'>No tweets imported recently within 24 hours.</div>";
						}
						echo "</div>";
					}
			
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