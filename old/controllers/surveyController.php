<?php
	$root = '../';
	require_once($root . 'database.php');
	require_once($root . 'models/session.php');
	require_once($root . 'views/survey.php');
	
	if(isset($_POST['session'])) {
		$sessionHash = $_POST['session'];
	}else{
		die("surveyController.php missing: session");
	}

	echo " ";
		
	$session = Session::getSession($sessionHash);
	$survey = new Survey($session);
	echo $survey->getHTML();

?>