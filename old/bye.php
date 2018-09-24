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
		<title>IoT survey</title>
		<link rel=stylesheet type="text/css" href="css/style.css" />
	</head>

	<body>
	
		<div id="surveyContent">
			<div id="surveyHeader">
				<div id="surveyTitle">Survey completed</div>
				<div id="explanation">Thanks for your help! You may close this window now.</div>
			</div>
		</div>
		
		<div id="debug"></div>

	</body>
</html>