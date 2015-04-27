<?php

class author{

	protected $api = false; 

	/*Protected class variables*/
	protected $author_id; 
	protected $perooz_author_id; 
	protected $first_name; 
	protected $last_name;
	protected $date; 

	/*Relevant sql statements*/
	private $select_stmt = 'Select AuthorId,PeroozAuthorId,AuthorFirstName,AuthorLastName From Authors Where ';
	private $insert_stmt = 'Insert Into Authors (PeroozAuthorId,AuthorFirstName,AuthorLastName) Values (:PeroozAuthorId,:AuthorFirstName,:AuthorLastName)';
	private $update_stmt = 'Update Authors Set ';

	private $search_stmt = 'Select PeroozAuthorId,AuthorFirstName,AuthorLastName From Authors Where AuthorFirstName=:AuthorFirstName And AuthorLastName=:AuthorLastName';

	/*Relevant properties of object values*/
	protected $prop_parameters = array(':AuthorId' => PDO::PARAM_INT,
									   ':PeroozAuthorId' => PDO::PARAM_STR,
									   ':AuthorFirstName' => PDO::PARAM_STR,
									   ':AuthorLastName' => PDO::PARAM_STR);


	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){
				$this->$key = $value; 
			}
			if (isset($this->con)){
				if (isset($this->author_id) || isset($this->perooz_author_id)){
					$properly_set = $this->set_from_db($this->con);
					if (!$properly_set){
						echo 'This has not worked.';
					}
				}
			}
		}
		$this->date = time();
	}

	function __destruct(){

	}

	/*Setter and getter methods*/
	/*Get function for note object*/
	public function __get($name){
		return $this->$name;
	}

	/*Set function for note object*/
	public function __set($name, $value){
		$this->$name = $value;
	}

	/*Insert into database method*/
	public function insert_db($con){
		
		/*Generate uuid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_author_id = 'pz_'.$uid['uuid()'];

		$values = array(':PeroozAuthorId' => $this->perooz_author_id,
						':AuthorFirstName' => $this->first_name,
						':AuthorLastName' => $this->last_name);

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}
		
		$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Author');
		
		return $result;
	}

	/*Retrieve from database method*/
	public function set_from_db($con){

		$returned_thing = false;
	
		/*Values and stmt set depending on whether pk or uuid provided*/
		if (isset($this->author_id)){
			$values = array(':AuthorId' => $this->author_id);
			$final_stmt = $this->select_stmt.'AuthorId=:AuthorId';
		}elseif(isset($this->perooz_author_id)){
			$values = array(':PeroozAuthorId' => $this->perooz_author_id);
			$final_stmt = $this->select_stmt.'PeroozAuthorId=:PeroozAuthorId';
		}

		/*Bind and set values*/
		foreach ($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		/*Obtain values from database*/
		$result = $con->multi_query($final_stmt,$values,$value_options);
	
		if (!empty($result)){
			$returned_thing = true;

			if($this->api){
				$this->perooz_author_id = $result[0]['PeroozAuthorId'];
				$this->first_name = $result[0]['AuthorFirstName'];
				$this->last_name = $result[0]['AuthorLastName'];
			}else{
				$this->author_id = $result[0]['AuthorId'];
				$this->perooz_author_id = $result[0]['PeroozAuthorId'];
				$this->first_name = $result[0]['AuthorFirstName'];
				$this->last_name = $result[0]['AuthorLastName'];
			}
	
		}

		return $returned_thing;
	}

	/*Update entry in database method*/
	public function update_to_db($values,$con){

		$stmt_set = '';
	
		if (array_key_exists(':AuthorId',$values)){
			$stmt_where = 'AuthorId=:AuthorId';
		}elseif(array_key_exists(':PeroozAuthorId', $values)){
			$stmt_where = 'PeroozAuthorId=:PeroozAuthorId';
		}

		$count_value = 1;
		foreach ($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
			if ($count_value == 1){
				$stmt_set = $stmt_set.substr($key,1).'='.$key; 
			}else{
				$stmt_set = $stmt_set.','.substr($key,1).'='.$key;
			}
			$count_value++;
		}

		$stmt = $this->update_stmt.' '.$stmt_set.' Where '.$stmt_where;
		
		$result = $con->update($stmt,$values,$value_options); 

		return $result;
	}

	/*Search method using valid values - both Firstname and LastName*/
	public function search_author($values,$con){
		$returned_thing = false;

		if (!array_key_exists('first_name',$values) || !array_key_exists('last_name', $values)){
			return $returned_thing;
		}

		$values = array(':AuthorFirstName' => $values['first_name'], ':AuthorLastName' => $values['last_name']);
		$value_options = array(':AuthorFirstName' => $this->prop_parameters[':AuthorFirstName'],
							   ':AuthorLastName' => $this->prop_parameters[':AuthorLastName']);

		$results = $con->multi_query($this->search_stmt,$values,$value_options);

		if (!empty($results)){
			$returned_thing = array('perooz_author_id' => $results[0]['PeroozAuthorId'],
									'first_name' => $results[0]['AuthorFirstName'],
									'last_name' => $results[0]['AuthorLastName']);
		}

		return $returned_thing;

	}
	
}

?>