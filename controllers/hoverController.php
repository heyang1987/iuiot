<?php
$root = '../';
require_once($root . 'models/event.php');
require_once($root . 'models/session.php');

if(isset($_POST['session'])) {
	$sessionHash = $_POST['session'];
	unset($_POST['session']);
	$answers = $_POST;
}else{
	die("index.php missing: session");
}

echo " ";

$session = Session::getSession($sessionHash);

if(sizeof($answers) > 0){
	$inserts = array();
	foreach($answers as $id => $answer){
		$answer = str_replace(",","','",$answer);
		$insert = "('$session->id','$answer')";
		array_push($inserts,$insert);
	}
	$insertString = implode(",",$inserts);
	
	$query = "
		INSERT INTO
			events
			(sessionId, action, obj, details)
		VALUES
			$insertString
	";
	Database::getInstance()->performQuery($query);

}
	
?>