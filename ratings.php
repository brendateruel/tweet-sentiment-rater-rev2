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

		echo "<div class='logo'>Happy Meter</div>";
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
		<?php
		/* Selecting user's friends */

		function average($solution){
			return array_sum($solution)/count($solution) ;
		}
		$stmt2 = $mysqli->stmt_init();	
		if (!($stmt = $mysqli->prepare("SELECT user_handle FROM {$new_friends_table}"))) {
			 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (!$stmt->execute()) {
			 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$stmt->store_result();
		$stmt->bind_result($user);
		while($row = $stmt->fetch()) {
			set_time_limit(0);
			echo $user;
			//$user = $row['user_handle'];	
			$stmt2 = $mysqli->stmt_init();
			if (!($stmt2 = $mysqli->prepare("SELECT sentiment_score FROM {$new_temp_timeline}"))) {
				 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			if (!$stmt2->execute()) {
				 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
			}
			$stmt2->bind_result($score);
			$row_cnt = $stmt2->num_rows;
			$solution = array();
			while ($array = $stmt2->fetch()) {
				$solution[]=$score;
				}
			//echo $user;
			if ($row_cnt >= 0) {
				$array_sum = array_sum($solution);
				//echo $array_sum;
				if ($array_sum == 0) {
					//echo "Average of array: 0";
					if(!($stmt = $mysqli->query("UPDATE {$new_friends_table} set avg_sentiment_rating='0' WHERE user_handle='{$user}'"))) {
							 echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
				} else {
					//echo "sum(a) = " . $array_sum . "\n";
					$avg_sentiment_rating = average($solution);
					//echo "Average of array:". $avg_sentiment_rating;
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

		/* SHOW FRIENDS WITH SENTIMENT RATINGS */
		echo "<div id='default-friends-subhead'><h2>Friends</h2></div>";

		/* SELECT DISPLAY ORDER */
		$friends_list_default = "SELECT DISTINCT {$new_friends_table}.user_handle, {$new_friends_table}.user_image_URL, {$new_temp_timeline}.tweet, {$new_friends_table}.avg_sentiment_rating, {$new_temp_timeline}.date_time FROM {$new_friends_table} JOIN {$new_temp_timeline} ON {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle GROUP BY {$new_friends_table}.user_handle";
		$friends_list_desc = "SELECT DISTINCT {$new_friends_table}.user_handle, {$new_friends_table}.user_image_URL, {$new_temp_timeline}.tweet, {$new_friends_table}.avg_sentiment_rating, {$new_temp_timeline}.date_time FROM {$new_friends_table} JOIN {$new_temp_timeline} ON {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle GROUP BY {$new_friends_table}.avg_sentiment_rating DESC";
		$friends_list_asc = "SELECT DISTINCT {$new_friends_table}.user_handle, {$new_friends_table}.user_image_URL, {$new_temp_timeline}.tweet, {$new_friends_table}.avg_sentiment_rating, {$new_temp_timeline}.date_time FROM {$new_friends_table} JOIN {$new_temp_timeline} ON {$new_friends_table}.user_handle={$new_temp_timeline}.user_handle WHERE {$new_temp_timeline}.date_time >= SYSDATE() - INTERVAL 1 DAY GROUP BY {$new_friends_table}.avg_sentiment_rating ASC";
			
		if (!($stmt = $mysqli->prepare("{$friends_list_default}"))) {
			 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (!$stmt->execute()) {
			 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$res = $stmt->get_result();

		while($row = $res->fetch_assoc()) {
			set_time_limit(0);
			$user = $row['user_handle'];
			$user_image = $row['user_image_URL'];
			$tweet = $row['tweet'];
			$avg_sentiment_rating = $row['avg_sentiment_rating'];
			if($avg_sentiment_rating > 0) {
				$mood_bg = "positive";
				} elseif($avg_sentiment_rating < 0) {
					$mood_bg = "negative";
					} else {
						$mood_bg = "neutral";
						}
			echo "<div id='default-friends' class='{$mood_bg}'>";
				if(is_null($avg_sentiment_rating)) {
					echo "<div id='rating'><img src=images/{$mood_bg}.png />0%</div>";
					} else {
						$percent = $avg_sentiment_rating * 100;
						echo "<div id='rating'><img src=images/{$mood_bg}.png />{$percent}%</div>";
						}
				echo "<img src={$user_image} class=user-image />";
				echo "<div class='user'>{$user}</div>";
				echo $row['date_time'];
				if(strtotime($row['date_time']) >= strtotime('now -24 hours')) {
					echo "<div class='latest-tweet'>Latest {$row['date_time']}: {$tweet}</div>";
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

	<div id="footer"></div>
	<!-- end #footer -->

</div>
<!-- End #wrapper -->


</body>
</html>