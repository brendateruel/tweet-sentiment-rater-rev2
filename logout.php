<!DOCTYPE>
<html>
<head>

<link rel="stylesheet" type"text/css" href="style.css" media="screen" />

<title>Welcome - Tweet Sentiment Rater</title>

</head>

<body>
<div id="wrapper">

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
/* 			header('Location: welcome.php'); 
			echo "<a href=twitter_login.php>Sign in</a>"; */
			header('Location: login.php');
		}
		
	/*Alchemy API SDK*/ 
	include('module/AlchemyAPI_CURL.php');
	include('module/AlchemyAPIParams.php');
	$alchemyObj = new AlchemyAPI();
	$alchemyObj->loadAPIKey("../../alchemy_api_key.txt");

//ENDS
?>

	<div id="nav"></div> 
	<!-- end #nav -->

	<div id="content">
	<?php 
/* 	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	session_destroy(); 
	$_SESSION = array();*/
	print_r($_SESSION);
	print_r($_SESSION['username']);
	echo "Thanks for logging out. Come back soon.";
	
/* 	unset($_SESSION['username']);
	if (!(is_null($_SESSION['username'])))	{
		print_r($_SESSION);
	} else {
		echo "unset";
		} */
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);
	if(count($_SESSION) == 0) {
		echo "NOPE";
		$_SESSION=array();
		session_destroy();
	}	
		
	print_r($_SESSION);
	print_r($_SESSION['username']);	
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
