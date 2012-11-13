<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" />
	<link href='http://fonts.googleapis.com/css?family=Homemade+Apple' rel='stylesheet' type='text/css'>
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
			$home_timeline = $twitteroauth->get('statuses/home_timeline', array('count' => 500));
		}

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
		echo "<div class='logo'>happy meter</div>";
		
			/*Alchemy API SDK*/ 
			include('module/AlchemyAPI_CURL.php');
			include('module/AlchemyAPIParams.php');
			$alchemyObj = new AlchemyAPI();
			$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

		//ENDS
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
		<?php
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
		if (!($stmt = $mysqli->prepare("SELECT sentiment_score FROM {$new_temp_timeline} WHERE user_handle='{$user}' AND {$new_temp_timeline}.date_time >= SYSDATE() - INTERVAL 1 DAY"))) {
			 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		if (!$stmt->execute()) {
			 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
		}
		$stmt->bind_result($score);
		$row_cnt = $stmt->num_rows;
			$solution = array();
			while ($array = $stmt->fetch()) {
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

		?>
				

		<!-- SELECT DISPLAY ORDER -->
		<select id='sort' 
			name='sort'
			onchange="window.location='?sort='+this.value;">
				      <option value='name' <?php if(!isset($_GET['sort']) || $_GET['sort']=='name'){$_GET['sort'] = 'user_handle'; echo "selected";} ?>>Alphabetical</option>
					  <option value='rating-desc' <?php if(isset($_GET['sort']) && $_GET['sort']=='rating-desc'){$_GET['sort'] = 'avg_sentiment_rating DESC'; echo "selected";} ?>>Rating - High to Low</option>
					  <option value='rating-asc' <?php if(isset($_GET['sort']) && $_GET['sort']=='rating-asc'){$_GET['sort'] = 'avg_sentiment_rating ASC'; echo "selected";} ?>>Rating - Low to High</option>
		</select>
		
		<?php		
		
		$friends_list_default = "SELECT 
				{$new_friends_table}.user_handle,
				{$new_friends_table}.user_image_URL,
				{$new_friends_table}.last_tweet,
				{$new_friends_table}.avg_sentiment_rating,
				{$new_friends_table}.last_update
						FROM {$new_friends_table}
						ORDER BY {$new_friends_table}.".$_GET['sort'];
		/*
		$friends_list_desc = "SELECT 
				{$new_friends_table}.user_handle,
				{$new_friends_table}.user_image_URL,
				{$new_friends_table}.last_tweet,
				{$new_friends_table}.avg_sentiment_rating,
				{$new_friends_table}.last_update
						FROM {$new_friends_table}
						ORDER BY {$new_friends_table}.avg_sentiment_rating DESC";
		$friends_list_asc = "SELECT
				{$new_friends_table}.user_handle,
				{$new_friends_table}.user_image_URL,
				{$new_friends_table}.last_tweet,
				{$new_friends_table}.avg_sentiment_rating,
				{$new_friends_table}.last_update
						FROM {$new_friends_table}
						ORDER BY {$new_friends_table}.avg_sentiment_rating ASC";
		*/
						
		function twitterify($tweet) {
		  $tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $tweet);
		  $tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $tweet);
		  $tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);
		  $tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweet);
		return $tweet;
		}
		
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
			$last_tweet = $row['last_tweet'];
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
				echo "<a href=http://www.twitter.com/{$user} target=_blank><img src={$user_image} class=user-image /></a>";
				echo "<div class='user'><a href=http://www.twitter.com/{$user} target=_blank>{$user}</a></div>";
				//echo $row['date_time'];
				$last_tweet = twitterify($last_tweet);
				if(strtotime($row['last_update']) >= strtotime('now -24 hours')) {
					echo "<div class='latest-tweet'>Latest ({$row['last_update']}): {$last_tweet}</div>";
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