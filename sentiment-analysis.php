<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
    <link rel="stylesheet" href="style.css" media="screen" /> 
	<link href="http://fonts.googleapis.com/css?family=Homemade+Apple" rel="stylesheet" type="text/css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<title>Loading... This may take a while - Tweet Sentiment Rater</title>
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
			
			echo "<div class='logo'>happy meter</div>";


			if(!empty($_SESSION['username'])){  
				//echo "{$session_username}'s Friends";
				} else {
						//header('Location: welcome.php'); 
						//echo "<a href=login.php>Sign in</a>";
						header('Location: welcome.php');
					}
					
				/*Alchemy API SDK*/ 
				include('module/AlchemyAPI_CURL.php');
				include('module/AlchemyAPIParams.php');
				$alchemyObj = new AlchemyAPI();
				$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

			//ENDS

			?>								
		</div>
		
		<!-- <div id="nav"></div> --> 
		<!-- end #nav -->

		<div id="content">
		
			<p class="still-loading">
				This may take a while... Analyzing happiness ain't easy!<br />
				<img src="images/ajax-loader.gif" alt="loading icon" />
			</p>
		</div>
		<!-- end #content -->
		
		<div id="footer">
			<p class="footer">
				this web application uses the <a href="http://www.twitter.com" target="_blank">Twitter</a> API + <a href="http://www.alchemyapi.com" target="_blank"><img src="images/alchemy-api.png" alt="Alchemy API logo"/></a> + <a href="http://github.com/abraham/twitteroauth/" target="_blank">twitteroauth PHP library</a> by Abraham Williams<br />
				for more info: contact <a href="mailto:bgteruel@gmail.com">bgteruel[at]gmail[dot]com</a>
			</p>

			<?php

				/* Selecting user's friends */
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
						if(!($stmt2 = $mysqli->prepare("SELECT tweet, status_ID FROM {$new_temp_timeline} WHERE user_handle='{$user}' AND sentiment_score IS NULL"))) {
								 echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
							}
						if(!$stmt2->execute()) {
								 echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
							}
						$res2 = $stmt2->get_result();
				/* Running each tweet through Alchemy API sentiment analysis */
						while($row2 = $res2->fetch_assoc()) {
							set_time_limit(0);
							$a = $row2['tweet'];
							//echo $a;
							$a_cnt = strlen($a);
							if($a_cnt<15) {
								$a = str_pad($a, 15);
								//echo $a;
								}
							$b = $row2['status_ID'];
							$a = $mysqli->real_escape_string($a);
							//echo $a;
							$response = $alchemyObj->TextGetTextSentiment($a);
							$result = simpleXML_load_string($response);
							$sentiment = $result->docSentiment;
							$mood = $sentiment->type;
							//echo $mood;
							$score = $sentiment->score;
							//echo $score . "\n";
							$score = $mysqli->real_escape_string($score);
				/* Writing sentiment score to timeline table */
							if(!($stmt3 = $mysqli->query("UPDATE {$new_temp_timeline} set sentiment_score='{$score}' WHERE status_ID='{$b}'"))) {
									echo "Statement failed: (" . $mysqli->errno . ") " . $mysqli->error;
									//if($statusInfo == "unsupported-text-language") {
										//$stmt4 = $mysqli->query("UPDATE {$new_temp_timeline} set sentiment_score='9.7' WHERE status_ID='{$b}'");
									//}
								 }
						}
				}

				if(!($check = $mysqli->prepare("SELECT tweet, status_ID FROM {$new_temp_timeline} WHERE user_handle='{$user}' AND sentiment_score IS NULL"))){  
					echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
				if (!$check->execute()) {
					echo "Execution failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}	else {
						if($check->num_rows==0) {
							//header('Location: ratings.php');
							printf("<script>location.href='ratings.php'</script>");
							} else {
							echo $check->num_rows . "not analyzed";
							}
						}
				?>
				
		</div>
		<!-- end #footer -->

	</div>
	<!-- End #wrapper -->



</body>
</html>