<?php

	$root = '../';
	require_once($root . 'database.php');
	require_once($root . 'models/session.php');
	require_once($root . 'views/survey.php');
	
	if(isset($_POST['session'])) {
		$sessionHash = $_POST['session'];
		unset($_POST['session']);
		$answers = $_POST;
	}else{
		die("index.php missing: session");
	}
	
	echo " ";
	
	$group = -1;
	
	$session = Session::getSession($sessionHash);
	
	if(sizeof($answers) > 0){
		$inserts = array();
		foreach($answers as $id => $answer){
			
			if(substr($answer,0,-1) == "wrong"){
				$cond = $session->condition . " w";
				$query = "
					UPDATE 
						sessions
					SET
						cond = '$cond'
					WHERE
						id = '$session->id'
					AND
						experiment = '$session->experiment'
				";
				Database::getInstance()->performQuery($query);
				return;
			}else{
				$answer = addslashes($answer);
				$insert = "('$session->id','$id','$answer')";
				array_push($inserts,$insert);
			}
		}
		$insertString = implode(",",$inserts);
		
		$query = "
			INSERT INTO
				answers
				(sessionId, questionId, answer)
			VALUES
				$insertString
		";
		Database::getInstance()->performQuery($query);
		
		$survey = new Survey($session);
	}
	echo('done');
?>