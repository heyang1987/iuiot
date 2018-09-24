<?php

require_once($root . 'database.php');

class Event{
	public $id;
	public $session;
	public $action;	//(select, click)
	public $obj;		//(product, widget)
	public $details;	//(table, asc)
	
	public function __construct($session, $action, $obj, $details){
		//save variables
		$this->session = $session;
		$this->action = $action;
		$this->obj = $obj;
		$this->details = $details;
		
		$this->save();
		if($action == "skip"){
			$query = "
				SELECT
					count(distinct obj) AS cnt
				FROM
					events
				WHERE
					sessionId = '".$this->session->id."'
				AND
					action = 'skip'
			";
		$result = Database::getInstance()->performQuery($query);
		echo $result[0]['cnt'];
		}
	}
	
	protected function save(){
		$query = "
			INSERT INTO
				events
			(
				sessionId,
				action,
				obj,
				details
			)
			VALUES
			(
				'".$this->session->id."',
				'$this->action',
				'$this->obj',
				'$this->details'
			)
		";
		$this->id = Database::getInstance()->performQuery($query);
		
	}
	
	public function getPrevId($sessionId){
		
		$query = "
			SELECT
				MAX(id) as 'id'
			FROM
				events
			WHERE
				sessionId = '$sessionId'
		";
		
		$result = Database::getInstance()->performQuery($query);
		$prevEventId = $result[0]['id'];
		return $prevEventId;
	}
	
	public static function getPrevEvent($sessionId){
		$query = "
			SELECT 	* 
			FROM 	events 
			WHERE 	id = ( 
					SELECT	MAX(id) 
					FROM 	events
					WHERE	sessionId = '$sessionId'
 			)
		";
		
		$result = Database::getInstance()->performQuery($query);
		$prevEvent = new Event($result[0]['sessionId'],$result[0]['action'],$result[0]['obj'],$result[0]['details']);
		$prevEvent->id = $result[0]['id'];
		return $prevEvent;
	}
}

class CreateEvent extends Event{
	
	public function __construct($session){
		
		$this->session = $session;
		$this->action = 'create';
		$this->obj = '';
		$this->details = '';
		
		parent::save();
	}
}

?>