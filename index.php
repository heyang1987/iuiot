<?php
if(isset($_GET['experiment'])) {
	$experiment = $_GET['experiment'];
}else{
	$experiment = 1;
}

if(isset($_GET['from'])) {
	$from = $_GET['from'];
}else{
	$from = 'start';
}

header('Location: controllers/sessionController.php?experiment='.$experiment.'&from='.$from);

?>