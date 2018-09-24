<?php
class Database {

	// object instance
	private static $instance;
	private $connection;
	
	// The protected construct prevents instantiating the class externally.  The construct can be
	// empty, or it can contain additional instructions...
	protected function __construct() {
		$host = 'localhost';
		$dbname = 'bart_iot';
		$user = 'bart_iot';
		$password = '4ZBVzEmThpS4VP4b';
		
		
		$this->connection = mysqli_connect($host, $user, $password, $dbname);
	}
 
	//This method must be static, and must return an instance of the object if the object
	//does not already exist.
	public static function getInstance() {
		if (!self::$instance instanceof self) { 
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	function performQuery($query){

		$start = microtime(true);

		$result = mysqli_query($this->connection, $query);
		if($result){
			if($result === true){
				$autoInc = mysqli_insert_id($this->connection);

				//$this->logW($start, $query);

				return $autoInc;
			}else{
				$data = array();
				while($row = mysqli_fetch_assoc($result)){
					$data[]=$row;
				}

				//$this->logW($start, $query);

				return $data;
			}
		}else{
			trigger_error(mysqli_error($this->connection), E_USER_ERROR);
		}
	}

	function logW($start, $query){
		$stop = microtime(true);
		$duration = round(($stop - $start) * 1000,2);
		//if($duration > 0.5){
			$q = preg_replace('/\s\s+|\r\n+|\r+|\n+\t+/', ' ', $query);
			preg_match('/\.([0-9]+) /', microtime(), $u);
			file_put_contents('log.txt', date('Y-m-d H:i:s.').round($u[1],3)."\t".$duration."\t".$q."\n", FILE_APPEND);
		//}
	}
}


?>