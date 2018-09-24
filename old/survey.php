<?php

if(isset($_GET['session'])) {
	$sessionHash = $_GET['session'];
}else{
	die("survey.php missing: session");
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		<title>IoT Survey</title>
		<script src="js/survey.js"></script>
		<link rel=stylesheet type="text/css" href="css/style.css" />
	</head>

	<body onLoad="setSession('<?php echo $sessionHash; ?>'); updateInterface('')">
	
		<div id="surveyContent"></div>
		
		<div id="debug"></div>

	</body>
</html>