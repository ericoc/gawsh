<?php

// Create function to set header and display error message on bad URLs
function showError ($error) {

	$pretty = ucwords($error);
	header($_SERVER['SERVER_PROTOCOL'] . ' ' . $pretty);
	echo <<< END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="gaw.sh url shortener $error">
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" type="text/css" href="/gawsh.css">
<title>gaw.sh URL short... $error</title>
</head>
<body>
<center>
<h1><a href="/">gaw.sh url shortener</a></h1><br>
<span id="error">$pretty</span>
</center>
</body>
</html>
END;

}

// Start the search for the URL if an alias was given
if ( (isset($_GET['x'])) && (!empty($_GET['x'])) ) {

	// Require configuration; do not need functions.php here
	require('config.php');

	// Connect to MySQL and choose database
	$link = mysql_connect($sqlhost, $sqluser, $sqlpass) OR die('Cannot connect to DB!');
	mysql_select_db($sqldb, $link);

	// Parse URL alias
	$alias = mysql_real_escape_string(trim($_GET['x']));

	// Check if the alias exists
	$check = mysql_query("SELECT id, url, status FROM `urls` WHERE `alias` = '$alias'", $link);

	// Get ID, long URL, and status if the alias exists
	if (mysql_num_rows($check) >= 1) {

		while ($row = mysql_fetch_array($check)) {
			$id = $row['id'];
			$to = $row['url'];
			$status = $row['status'];
		}

		// Add an entry to the visits table
		mysql_query("INSERT INTO `visits` VALUES ('$id', '$ip', '$browser', '$referrer', '$time')", $link);
	}

	// Disconnect from MySQL
	mysql_close($link);
}

// Redirect to long URL if it was found and is active; show error message if it has been disabled, is in a weird status, or was not found
if ( (isset($to)) && ($status == '1') ) {
	header("Location: $to", TRUE, 301);

} elseif ( (isset($to)) && ($status == '0') ) {
	showError('410 gone');

} elseif ($alias == '403') {
	showError('403 forbidden');

} else {
	showError('404 not found');
}

?>
