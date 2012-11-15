﻿<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <!-- <link rel="stylesheet" href="style.css" media="screen" /> -->
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
			$allfriends = $twitteroauth->get('friends/ids', array('screen_name' => $session_username));
		}

		echo "<div class=logo>happy meter</div>";
		echo "<div id='user-login'>";
		if(!empty($_SESSION['username'])){  
			echo "<div id=menu>";
			echo "{$session_username}<img src=images/arrow-down-white.gif />";
			echo "<div class=logout><a href=logout.php>Log Out</a></div>";
			echo "</div>";
			} else {
					//header('Location: welcome.php'); 
					echo "<a href=login.php class=button>Sign in</a>";
					//header('Location: login.php');
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
			<img src="images/ajax-loader.gif" class="loading" />
			<a href="sentiment-analysis.php" class="button">Analyze Now!</a>
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
					//print_r($allfriends);
					//$result = simpleXML_load_string($allfriends);
					//print_r($result);
					//foreach($allfriends->ids as $ids) {
						//$id = $ids->id;
						//print_r($id);
					$ids = $allfriends->ids;
					//print_r($ids);
					foreach($ids->id as $id) {
					//$result = simpleXML_load_string($id);
						//$allids = array();
						echo $id;
						$allids[] = $id;
						print_r($allids);
					while($user_timeline = $twitteroauth->get('statuses/user_timeline', array('user_id' => '$id', 'count' => 1))) {
						//print_r($user_timeline);
						//foreach($user_timeline->status as $status) {
						while($status = $user_timeline->status) {
							$user = $status->user;
							$tweet = $status->text;
							$tweet = $mysqli->real_escape_string($tweet);
							$date_time = date("Y-m-d H:i:s", strtotime($status->created_at)); 
							echo $user;
							echo $tweet;
							echo $date_time;
							echo ($user->screen_name);
							echo $session_username;
							
						if(!($user->screen_name == $session_username)) {
							if(($query = $mysqli->query("INSERT INTO {$new_friends_table} (user_handle, user_image_URL) VALUES ('{$user->screen_name}', '{$user->profile_image_url}')"))) {
									echo "<img src='{$user->profile_image_url}' />";
							}
						} else {
							echo "this is me";
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
						}}
						unset($id);
						unset($user_timeline);
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
			$stmt = $mysqli->stmt_init();
			if (!($stmt = $mysqli->prepare("SELECT DISTINCT
				{$new_friends_table}.user_handle,
				{$new_friends_table}.user_image_URL,
				{$new_temp_timeline}.tweet,
				{$new_temp_timeline}.sentiment_score,
					MAX({$new_temp_timeline}.date_time)
						FROM {$new_friends_table}
						LEFT JOIN {$new_temp_timeline} ON
							{$new_friends_table}.user_handle={$new_temp_timeline}.user_handle
						GROUP BY {$new_friends_table}.user_handle
						ORDER BY {$new_temp_timeline}.date_time"))) {
				 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (!$stmt->execute()) {
				 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			$stmt->bind_result($user, $user_image, $tweet, $sentiment_score, $date_time);
			/*$res = $stmt->fetch();*/
			while($row = $stmt->fetch()) {
				//$user = $row['user_handle'];
				//$user_image = $row['user_image_URL'];
				//$tweet = $row['tweet'];
					$tweet = twitterify($tweet);
					echo "<div id='default-friends'>";
					echo "<img src={$user_image} class=user-image />";
					echo "<div class='user'><a href=http://www.twitter.com/{$user} target=_blank>{$user}</a>";
					/*$sentiment_score = $row['sentiment_score'];*/
						if (is_null($sentiment_score)) {
						echo "<span class=new-marker>new update!</span>";
						}
					echo "</div>";
					if(strtotime($date_time) >= strtotime('now -24 hours')) {
						echo "<div class='latest-tweet'>Latest ({$date_time}): {$tweet}</div>";
					} else {
						echo "<div class='latest-tweet'>No tweets imported recently within 24 hours.</div>";
					}
					echo "</div>";
			}
		
			?>
	</div>
	<!-- end #content -->

	<div id="sidebar"></div>
	<!-- end #sidebar -->

	<div id="footer">
		<p class="footer">
			credit Alchemy API + other libraries... contact bgteruel[at]gmail[dot]com
		</p>
	</div>
	<!-- end #footer -->

</div>
<!-- End #wrapper -->


</body>
</html>