<?php 

/*PHP Article class for article in db*/
/*Date: 6-4-2014*/
/*Author:Sneha Inguva*/

class contributor{

	/*Is this model being used in an api*/
	protected $api = false; 

	/*Either automatically set via constructor or set using get and set methods*/
	protected $contributor_id; 
	protected $perooz_contributor_id; 
	protected $user_id; 
	protected $perooz_user_id;
	protected $bio; 
	protected $photo; 
	protected $profession; 
	protected $country;
	protected $stance; 
	protected $bio_hyperlink;
	protected $create_date; 

	/*Relevant statements for insertion or selection from database*/
	private $select_stmt = 'Select ContributorId,PeroozContributorId,UserId,Bio,Photo,Profession,Country,PoliticalStance,BioHyperlink From Contributors Where ';
	private $insert_stmt =  'Insert Into Contributors (PeroozContributorId,UserId,Bio,Photo,Profession,Country,PoliticalStance,BioHyperlink) Values (:PeroozContributorId,:UserId,:Bio,:Photo,:Profession,:Country,:PoliticalStance,:BioHyperlink)';
	private $update_stmt =  'Update Contributors Set';

	private $search_stmt = 'Select PeroozContributorId,UserId,Bio,Photo,Profession,Country,PoliticalStance,BioHyperlink From Contributors Where UserId=(Select UserId From Users Where FirstName=:FirstName And LastName=:LastName)';

	/*Relevant bound parameters for sql statements*/
	private $prop_parameters = array(':ContributorId' => PDO::PARAM_INT,
									 ':PeroozContributorId' => PDO::PARAM_STR,
									 ':UserId' => PDO::PARAM_INT,
									 ':PeroozUserId' => PDO::PARAM_STR,
									 ':Bio' => PDO::PARAM_STR,
									 ':Photo' => PDO::PARAM_STR,
									 ':Profession' => PDO::PARAM_STR,
									 ':Country' => PDO::PARAM_STR, 
									 ':PoliticalStance' => PDO::PARAM_STR,
									 ':BioHyperlink' => PDO::PARAM_STR,
									 ':FirstName' => PDO::PARAM_STR,
									 ':LastName' => PDO::PARAM_STR);

	/*Object constructor*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){ //value set from argument array
				$this->$key = $value; 
			}
			if (isset($this->con)){ //if database connection and key given, set from db
				if (isset($this->contributor_id) || isset($this->perooz_contributor_id) || isset($this->user_id) || isset($this->perooz_user_id)){
					$properly_set = $this->set_from_db($this->con);
					if (!$properly_set){
						echo 'This has not worked.';
					}
				}
			}
		}

	}

	/*Object destructor*/
	function __destruct(){

	}

	/*Set function*/
	public function __set($name,$value){
		return $this->$name = $value;
	}

	/*Get function*/
	public function __get($name){
		return $this->$name;
	}

	/*Insert into databases*/
	/*Argument $con - database connection*/
	public function set_from_db($con){
		$returned_thing = false; 

		/*Set values and complete statement depending on if primary key or uuid*/
		if (isset($this->contributor_id)){
			$values = array(':ContributorId' => $this->contributor_id);
			$final_stmt = $this->select_stmt.'ContributorId=:ContributorId';
		}elseif(isset($this->perooz_contributor_id)){
			$values = array(':PeroozContributorId' => $this->perooz_contributor_id);
			$final_stmt = $this->select_stmt.'PeroozContributorId=:PeroozContributorId';
		}elseif(isset($this->user_id)){
			$values = array(':UserId' => $this->user_id);
			$final_stmt = $this->select_stmt.'UserId=:UserId';
		}elseif(isset($this->perooz_user_id)){
			$values = array(':PeroozUserId' => $this->perooz_user_id);
			$final_stmt = $this->select_stmt.'UserId=(Select UserId From Users Where PeroozUserId=:PeroozUserId)';
		}

		/*Complete the select query to initialize object*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){

			if ($this->api){

				$user_stmt = 'Select PeroozUserId,FirstName,LastName From Users Where UserId=:UserId';
				$user_val = array(':UserId' => $result[0]['UserId']);
				$user_prop = array(':UserId' => PDO::PARAM_INT); 
				$user_result = $con->multi_query($user_stmt,$user_val,$user_prop);

				if (!empty($user_result)){
					$returned_thing = true;

					$this->perooz_contributor_id = $result[0]['PeroozContributorId'];
					$this->user_id = $user_result[0]['PeroozUserId'];
					$this->bio = $result[0]['Bio'];
					$this->photo = $result[0]['Photo'];
					$this->profession = $result[0]['Profession']; 
					$this->country = $result[0]['Country']; 
					$this->stance = $result[0]['PoliticalStance'];
					$this->bio_hyperlink = $result[0]['BioHyperlink'];
					$this->first_name = $user_result[0]['FirstName'];
					$this->last_name = $user_result[0]['LastName'];

				}

			}else{
				$returned_thing = true;

				$this->contributor_id = $result[0]['ContributorId'];
				$this->perooz_contributor_id = $result[0]['PeroozContributorId'];
				$this->user_id = $result[0]['UserId'];
				$this->bio = $result[0]['Bio'];
				$this->photo = $result[0]['Photo'];
				$this->profession = $result[0]['Profession']; 
				$this->country = $result[0]['Country']; 
				$this->stance = $result[0]['PoliticalStance'];
				$this->bio_hyperlink = $result[0]['BioHyperlink'];
			}
		}
		return $returned_thing;

	}

	/*Insert contributor values into the database*/
	/*Argument $con - database connection*/
	public function insert_db($con){

		$result = false; 

		/*First, generate a uid for the database*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_contributor_id = 'pz_'.$uid['uuid()'];

		if ($this->api){

			$user_stmt = 'Select UserId From Users Where PeroozUserId=:PeroozUserId';
			$user_val = array(':PeroozUserId' => $this->perooz_user_id);
			$user_prop = array(':PeroozUserId' => PDO::PARAM_STR); 
			$user_result = $con->multi_query($user_stmt,$user_val,$user_prop);

			if (!empty($user_result)){

				/*Insert into the database*/
				$values = array(':PeroozContributorId' => $this->perooz_contributor_id,
								':UserId' => $user_result[0]['UserId'],
								':Bio' => $this->bio,
								':Photo' => $this->photo,
								':Profession' => $this->profession,
								':Country' => $this->country,
								':PoliticalStance' => $this->stance,
								':BioHyperlink' => $this->bio_hyperlink);

				foreach($values as $key => $value){
					$value_options[$key] = $this->prop_parameters[$key];
				}

				$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'Contributor');

			}

			return $result;

		}else{

			/*Insert into the database*/
			$values = array(':PeroozContributorId' => $this->perooz_contributor_id,
							':UserId' => $this->user_id,
							':Bio' => $this->bio,
							':Photo' => $this->photo,
							':Profession' => $this->profession,
							':Country' => $this->country,
							':PoliticalStance' => $this->stance,
							':BioHyperlink' => $this->bio_hyperlink);

			foreach($values as $key => $value){
				$value_options[$key] = $this->prop_parameters[$key];
			}

			$result = $con->insert($this->insert_stmt,$values,$value_options);

			return $result;

		}
	}

	/*Update values as specified in associative array $values*/
	/*$values = array(':xxx' => xxx, ':yyy' =>, ...)
	  Note $values represents all bound values in sql statement*/
	public function update_to_db($values,$con){

		$result = false; 
		
		$stmt_set = '';

		if (array_key_exists(':ContributorId',$values)){
			$stmt_where = 'ContributorId=:ContributorId'; 
		}elseif(array_key_exists(':PeroozContributorId', $values)){
			$stmt_where = 'PeroozContributorId=:PeroozContributorId';
		}elseif(array_key_exists(':UserId',$values)){
			$stmt_where = 'UserId=:UserId';
		}elseif(array_key_exists(':PeroozUserId',$values)){
			$stmt_where = 'UserId=(Select UserId From Users Where PeroozUserId=:PeroozUserId)';
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

	/*Search for contributor using first and last name*/
	public function search_contributor($values,$con){
		
		$returned_thing = false; 

		/*Ensure that only one array key-value pair exists*/
		if (count($values) != 2){
			return $returned_thing;
		}

		if (!array_key_exists('first_name',$values) || !array_key_exists('last_name', $values)){
			return $returned_thing;
		}

		$values = array(':FirstName' => $values['first_name'], ':LastName' => $values['last_name']);
		$value_options = array(':FirstName' => $this->prop_parameters[':FirstName'],
							   ':LastName' => $this->prop_parameters[':LastName']);

		$results = $con->multi_query($this->search_stmt,$values,$value_options);

		if (!empty($results)){
			if ($this->api){
				
				$user_stmt = 'Select PeroozUserId,FirstName,LastName From Users Where UserId=:UserId';
				$user_val = array(':UserId' => $results[0]['UserId']);
				$user_prop = array(':UserId' => PDO::PARAM_INT); 
				$user_result = $con->multi_query($user_stmt,$user_val,$user_prop);

				if (!empty($user_result)){
					$returned_thing = array('perooz_contributor_id' => $results[0]['PeroozContributorId'],
											'user_id' => $user_result[0]['PeroozUserId'],
											'bio' => $results[0]['Bio'],
											'photo' => $results[0]['Photo'],
											'profession' => $results[0]['Profession'],
											'country' => $results[0]['Country'],
											'political_stance' => $results[0]['PoliticalStance'],
											'bio_hyperlink' => $results[0]['BioHyperlink'],
											'first_name' => $user_result[0]['FirstName'],
											'last_name' => $user_result[0]['LastName']); 
				}
			}else{
				$returned_thing = array('perooz_contributor_id' => $results[0]['PeroozContributorId'],
									'user_id' => $results[0]['UserId'],
									'bio' => $results[0]['Bio'],
									'photo' => $results[0]['Photo'],
									'profession' => $results[0]['Profession'],
									'country' => $results[0]['Country'],
									'political_stance' => $results[0]['PoliticalStance'],
									'bio_hyperlink' => $results[0]['BioHyperlink']); 
			}
		}

		return $returned_thing;

	}
	
}

?>