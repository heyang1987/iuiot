<?php

	if(!isset($root)){
		$root = '../';
	}
	require_once($root . 'models/event.php');
	require_once($root . 'models/session.php');	

	//Extract URI
	$session = null;

	if(isset($_GET['session'])) {
		$sessionHash = $_GET['session'];
		$session = Session::getSession($sessionHash);
		if(isset($_GET['from'])) {
			$from = $_GET['from'];
			$session->nextState($from);
		}
	}else{

		if(isset($_GET['experiment'])) {
			$experiment = $_GET['experiment'];
		}else{
			die("sessionController.php missing: experiment");
		}
		$id = Session::getNew($experiment);
		$session = new Session($id, $experiment);
		if(isset($_GET['from'])) {
			$from = $_GET['from'];
			$session->nextState($from);
		}

	}
	if($session->state == 'presurvey' || $session->state == 'postsurvey'){
		header('Location: ' .$root. 'survey.php?session='.$session->hash);
	}else if($session->state == 'done'){
		header('Location: ' .$root. 'bye.php?session='.$session->hash);
	}
	

?>