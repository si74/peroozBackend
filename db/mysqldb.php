<?php

/*Basic db accessor class*/
/*Author: Sneha Inguva*/
/*Date Updated: 10-22-2014*/

class mysqldb{

	protected $server='crap'; 
	protected $db_username=''; 
	protected $db_pw='';
	protected $db_name=''; 

	protected $port=''; 
	protected $charset = 'utf8'; 

	/*Constructor for db class*/
	/*If persistent set to true, then persistent database connection is made*/
	public function __construct($options,$persistent){

		/*Grab th values from the array*/
		foreach($options as $option => &$value){
			if ($option == 'server'){
				$this->server = $value;
			}else if($option == 'db_username'){
				$this->db_username = $value;  
			}else if($option == 'db_pw'){
				$this->db_pw = $value; 
			}else if($option == 'db_name'){
				$this->db_name = $value;
			}else if($option == 'port'){
				$this->port = $value;
			}else if($option == 'charset'){
				$this->charset = $value;
			}else{
			}
		}

		try{
			$sql_settings = 'mysql:host='.$this->server.(isset($this->port)?';port='.$this->port:'').';dbname='.$this->db_name.';charset='.$this->charset;
			if ($persistent){	
				$this->pdo=new PDO($sql_settings,
								$this->db_username,
								$this->db_pw,
								array(PDO::ATTR_PERSISTENT => true));
			}else{
				$this->pdo=new PDO($sql_settings,
								$this->db_username,
								$this->db_pw);
			}
		}catch(Exception $e){
			$time = date ('Y-m-d H:i:s');
			error_log("Mistake with db connection at [ $time ]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

	}

	public function __destruct(){
		$this->pdo = null; 
	}

	/*PDO Quote Function*/
	/*Escapes special characters*/
	public function quote($value){
		$result = $this->pdo->quote($value);
		return $result;
	}
	
	/*GENERIC SELECT FUNCTIONS*/
	/*Very general statement that doesn't require a prepared pdo statements*/
	/*For simple mysql functions, etc*/
	public function query($stmt){
		$result = $this->pdo->query($stmt);
		return $result; 
	}

	/*Select Statement Returning a single value*/
	/*Note this uses prepared statements*/
	/*NOT A GOOD FUNCTION. CAN LATER BE REMOVED*/
	public function single_query($stmt,$values,$value_prop,$columnName){
		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		try{
			$query_stmt->execute();
			$f = $query_stmt->fetch();
			$result = $f[$columnName];
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			error_log("Issue with single query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

		if (!isset($result)){
			$result = '';
		}

		return $result;
	}

	/*Select Statement Returning an array - single or multiple rows*/
	public function multi_query($stmt,$values,$value_prop){
		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		try{
			$query_stmt->execute();
			$result = $query_stmt->fetchAll();
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			error_log("Issue with multi query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		} 

		if (!isset($result)){
			$result = '';
		}

		return $result;

	}
	/*************************************************/

	/*Insert Statement*/
	/*Returns id of last inserted item if successful*/
	public function insert($stmt,$values,$value_prop,$api,$class){

		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		try{
			$query_stmt->execute();
			if (($query_stmt->rowCount() > 0) && $api){
				$pz_stmt = 'Select Perooz'.$class.'Id From '.$class.'s Where '.$class.'Id=:'.$class.'Id';
				$pz_val = array(':'.$class.'Id' => $this->pdo->lastInsertId());
				$pz_prop = array(':'.$class.'Id' => PDO::PARAM_INT);
				$pz_result = $this->multi_query($pz_stmt,$pz_val,$pz_prop);
				if ($pz_result){
					$result = $pz_result[0]['Perooz'.$class.'Id'];
				}
			}else{
				$result = $this->pdo->lastInsertId();
			}
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			error_log("Issue with single query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

		if (!isset($result)){
			$result = '';
		}

		return $result;

	}

	/*Update Statement*/
	/*Returns id of last updated item if successful*/
	public function update($stmt,$values,$value_prop){
		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		$result = false; 

		try{
			$fact = $query_stmt->execute();
			if ($query_stmt->rowCount() > 0){
				$result = true;
			}
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			error_log("Issue with single query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

		return $result;

	}

	/*Delete Statements*/
	/*Returns whether deleted or not*/
	public function delete($stmt,$values,$value_prop){
		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		try{
			$result = $query_stmt->execute(); 
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s'); 
			error_log("Issue with single query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

		return $result; 
	}

	/*SELECT STATEMENT ALLOWING CLASSES TO BE INSTANTIATED FROM RESULT SET*/
	/*Using fetch_class property*/
	public function multi_query_class($stmt,$values,$value_prop,$classname){
		$query_stmt = $this->pdo->prepare($stmt);
		foreach($values as $key => &$val){
			$query_stmt->bindParam($key,$val,$value_prop[$key]);
		}

		try{
			$query_stmt->execute();
			$result = $query_stmt->fetchAll(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $classname);
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			error_log("Issue with single query at [$time]: $e->getMessage()", 3, '/../error_log/db_error.txt');
		}

		if (!isset($result)){
			$result = '';
		}

		return $result;
	}


}

?>