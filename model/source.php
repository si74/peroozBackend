<?php 

class source{

	/*Test if the model is being called via the api*/
	protected $api = false;

	/*General object values*/
	protected $source_id; 
	protected $perooz_source_id;
	protected $source_name; 
	protected $source_site; 
	protected $source_type_id;
	protected $date; 

	/*Sql statements*/
	private $select_stmt = 'Select SourceId,PeroozSourceId,SourceName,SourceSite,SourceTypeId From Sources Where ';
	private $insert_stmt = 'Insert Into Sources (PeroozSourceId,SourceName,SourceSite,SourceTypeId) Values (:PeroozSourceId,:SourceName,:SourceSite,:SourceTypeId)';
	private $update_stmt = 'Update Sources Set ';
	private $search_stmt = 'Select SourceId,PeroozSourceId,SourceName,SourceSite,SourceTypeId From Sources Where ';

	/*General parameter requirements for class properties*/
	private $prop_parameters = array(':SourceId' => PDO::PARAM_INT,
							 ':PeroozSourceId' => PDO::PARAM_STR,
							 ':SourceName' => PDO::PARAM_STR,
							 ':SourceSite' => PDO::PARAM_STR,
							 ':SourceTypeId' => PDO::PARAM_INT);

	/*Constructor for object*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){ //value set from argument array
				$this->$key = $value; 
			}
			if (isset($this->con)){ //if database connection and key given, set from db
				if (isset($this->source_id) || isset($this->perooz_source_id)){
					$properly_set = $this->set_from_db($this->con);
					if (!$properly_set){
						echo 'This has not worked.';
					}
				}
			}
		}
		$this->date = time(); 
	}

	/*Destructor for object*/
	function __destruct(){
	}

	/*Set and Get Methods*/
	public function __get($name){
		return $this->$name;
	}

	/*Function set method*/
	public function __set($name, $value){
		$this->$name = $value;
	}

	/*Insert values to a databases*/
	public function insert_db($con){

		/*Generate uuid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_source_id = 'pz_'.$uid['uuid()']; 

		$values = array(':PeroozSourceId' => $this->perooz_source_id,
			 			':SourceName' => $this->source_name,
			 			':SourceSite' => $this->source_site,
			 			':SourceTypeId' => $this->source_type_id);

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Source');

		return $result;

	}

	/*Set source values from the database*/
	public function set_from_db($con){

		$returned_thing = false;
	
		/*Values and stmt set depending on whether pk or uuid provided*/
		if (isset($this->source_id)){
			$values = array(':SourceId' => $this->source_id);
			$final_stmt = $this->select_stmt.'SourceId=:SourceId';
		}elseif(isset($this->perooz_source_id)){
			$values = array(':PeroozSourceId' => $this->perooz_source_id);
			$final_stmt = $this->select_stmt.'PeroozSourceId=:PeroozSourceId';
		}

		/*object set from db*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);
		
		if (!empty($result)){
			if ($this->api){
				$returned_thing = true;
				$this->perooz_source_id = $result[0]['PeroozSourceId'];
				$this->source_name = $result[0]['SourceName'];
				$this->source_site = $result[0]['SourceSite'];
				$this->source_type_id = $result[0]['SourceTypeId'];
			}else{
				$this->source_id = $result[0]['SourceId'];
				$this->perooz_source_id = $result[0]['PeroozSourceId'];
				$this->source_name = $result[0]['SourceName'];
				$this->source_site = $result[0]['SourceSite'];
				$this->source_type_id = $result[0]['SourceTypeId'];
			}
		}

		return $returned_thing;

	}

	/*Update source values*/
	public function update_to_db($values,$con){

		$stmt_set = '';
	
		if (array_key_exists(':SourceId', $values)){
			$stmt_where = 'SourceId=:SourceId';
		}elseif(array_key_exists(':PeroozSourceId', $values)){
			$stmt_where = 'PeroozSourceId=:PeroozSourceId';
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

	/*Search for a source based on valid values - source hyperlink, sourcename*/
	public function search_source($values,$con){
		$returned_thing = false;
		$stmt='';

		/*Ensure that only one array key-value pair exists*/
		if (count($values) != 1){
			return $returned_thing;
		}

		/*Check the array key and create final statement*/
		if (array_key_exists('url',$values)){
			$stmt = $this->search_stmt.'SourceSite=:SourceSite';
			$values = array(':SourceSite' => $values['url']);
			$value_options = array(':SourceSite' => $this->prop_parameters[':SourceSite']);
		}

		if ($stmt){
			$results = $con->multi_query($stmt,$values,$value_options);
			
			if (!empty($results)){
				$returned_thing = array('perooz_source_id' => $results[0]['PeroozSourceId'],
										'source_name' => $results[0]['SourceName'],
										'source_site' => $results[0]['SourceSite'],
										'source_type_id' => $results[0]['SourceTypeId']);
			}
		}

		return $returned_thing;
	}
	
}

?>
