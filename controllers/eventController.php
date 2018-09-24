<?php
$root = '../';
require_once($root . 'models/event.php');
require_once($root . 'models/session.php');

//build event
if(isset($_POST['session'])) {
	$sessionHash = $_POST['session'];
}else{
	die("eventController.php missing: sessionHash");
}
if(isset($_POST['action'])) {
	$action = $_POST['action'];
}else{
	die("eventController.php missing: action");
}
if(isset($_POST['object'])) {
	$object = $_POST['object'];
}else{
	$object = "";
}
if(isset($_POST['details'])) {
	$details = $_POST['details'];
}else{
	$details = "";
}

$session = Session::getSession($sessionHash);

new Event($session,$action,$object,$details);



	
?>