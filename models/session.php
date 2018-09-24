<?php

require_once('event.php');

class Session{
	public $id;
	public $hash;
	public $userId;
	public $ip;
	public $experiment;
	public $state; //(nog niet begonnen, bezig, afgerond)

	public static function getSession($sessionHash){
		$query="
			SELECT SQL_CACHE
				userid, experiment
			FROM
				sessions
			WHERE
				hash = '$sessionHash'
		";
		$result = Database::getInstance()->performQuery($query);
		if(sizeof($result) > 0){
			return new Session($result[0]['userid'], $result[0]['experiment']);
		}else{
			die("session not found, tried $query");
		}
	}
	
	public static function getNew($experiment){
		$okay = false;
		srand((double)microtime()*1234567);
		while(!$okay){
			$id = rand(0,99999);
			$okay = !Session::checkOld($id, $experiment);
		}
		return $id;
	}
	
	public function nextState($from){
		if($from == 'presurvey'){
			$this->condition = $this->getNeededCondition();
			
			$query="
				UPDATE
					conditions
				SET
					assigned = assigned + 1 
				WHERE 
					cond = '$this->condition'
			";
			Database::getInstance()->performQuery($query);
			
			$this->state = 'postsurvey';
		} else if($from == 'postsurvey'){
			$this->state = 'done';
		}
		
		$query = "
			UPDATE
				sessions
			SET
				state = '$this->state',
				cond = '$this->condition'
			WHERE
				id = $this->id
		";
		Database::getInstance()->performQuery($query);
		new Event($this, 'start',$this->state,'');
	}

	public function __construct($userId,$experiment){
		//save the session
		$this->userId = $userId;
		$this->experiment = $experiment;
		$mySession = $this->getSessionData();
		if(isset($mySession)){
			$this->condition = $mySession['cond'];
			$this->state = $mySession['state'];
			$this->hash = $mySession['hash'];
			$this->ip = $mySession['ip'];
			$this->id = $mySession['id'];
		}else{
			$this->ip = 0;
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			    $this->ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			    $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
			    $this->ip = $_SERVER['REMOTE_ADDR'];
			}
			
			$this->condition = 'temp';
			
			$this->state = 'presurvey';
			$this->hash = md5($userId+time()+rand(0,1000000000));
			$this->save();
			//create a first event and initialize
			new CreateEvent($this);
			new Event($this,'init',$this->experiment,$this->condition);
		}
	}
	
	public function getNeededCondition(){
		$query="
			SELECT
				cond
			FROM 
				conditions 
			WHERE 
				experiment = '$this->experiment'
			ORDER BY 
				assigned asc, 
				rand() asc 
			LIMIT 1
		";
		$result = Database::getInstance()->performQuery($query);
		return $result[0]['cond'];
	}
	
	public function getSessionData(){
		$query="
			SELECT SQL_CACHE
				*
			FROM
				sessions
			WHERE
				userid = '$this->userId'
			AND	experiment = '$this->experiment'
		";
		$result = Database::getInstance()->performQuery($query);
		if(sizeof($result) > 0){
			return $result[0];
		}else{
			return null;
		}
	}
	
	public static function checkOld($id, $experiment){
		$query="
			SELECT SQL_CACHE
				userid
			FROM
				sessions
			WHERE
				userid = '$id'
			AND
				experiment = '$experiment'
		";
		$result = Database::getInstance()->performQuery($query);
		if(sizeof($result) > 0){
			return true;
		}else{
			return false;
		}
	}

	private function save(){
		$query="
			INSERT INTO
				sessions
			(
				hash,
				userid,
				ip,
				experiment,
				cond,
				state
			)
			VALUES
			(
				'$this->hash',
				'$this->userId',
				'$this->ip',
				'$this->experiment',
				'$this->condition',
				'$this->state'
			)
		";
		$this->id = Database::getInstance()->performQuery($query);
		
	}
	
	private function update(){
		$query="
			UPDATE
				sessions
			SET
				hash = '$this->hash',
				userid = '$this->userId',
				ip = '$this->ip',
				cond = '$this->condition',
				state = '$this->state'
			WHERE
				id = '$this->id'
			AND
				experiment = '$this->experiment'
		";
		Database::getInstance()->performQuery($query);
	}
}

?>
