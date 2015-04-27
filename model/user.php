<?php 

class user{

	/*Does the class user the api*/
	protected $api = false; 

	/*General class properties*/
	protected $user_id; 
	protected $perooz_user_id; 
	protected $user_type_id; // [1] - general, [2] - contributor, [3] - admin
	protected $user_payment_type_id; //[1] - free, [2] - paid
	protected $first_name; 
	protected $last_name; 
	protected $user_ip; 
	protected $email; 
	protected $phone; 
	protected $date;
	protected $charge_list = array(); 

	/*General sql statements*/
	private $select_stmt = 'Select UserId,PeroozUserId,UserTypeId,UserPaymentTypeId,FirstName,LastName,UserIP,Email,Phone From Users Where ';
	private $insert_stmt = 'Insert Into Users (PeroozUserId,UserTypeId,UserPaymentTypeId,FirstName,LastName,UserIP,Email,Phone) Values (:PeroozUserId,:UserTypeId,:UserPaymentTypeId,:FirstName,:LastName,:UserIP,:Email,:Phone)';
	private $update_stmt = 'Update Users Set';
	private $charge_list_stmt = 'Select UserChargeId From UserCharges Where UserId=:UserId';

	/*General parameter requirements for class properties - store in associative array*/
	private $prop_parameters = array(':UserId' => PDO::PARAM_INT,
									 ':PeroozUserId' => PDO::PARAM_STR, //Note is set upon insertion into the db and should not be reset
									 ':UserTypeId' => PDO::PARAM_INT,
									 ':UserPaymentTypeId' => PDO::PARAM_INT,
									 ':FirstName' => PDO::PARAM_STR,
									 ':LastName' => PDO::PARAM_STR,
									 ':UserIP' => PDO::PARAM_STR,
									 ':Email' => PDO::PARAM_STR,
									 ':Phone' => PDO::PARAM_STR,
									 ':UserChargeId' => PDO::PARAM_INT);

	/*Constructor and destructor methods*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){
				$this->$key = $value;
			}
			if (isset($this->con)){
				if (isset($this->user_id) || isset($this->perooz_user_id)){
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

	/*set and get methods*/
	public function __get($name){
		return $this->$name;
	}

	/*Function set method*/
	public function __set($name, $value){
		$this->$name = $value;
	}

	/*Insert new set method into the database*/
	public function insert_db($con){
		/*First generate a new uuid and set the value for the uid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_user_id = 'pz_'.$uid['uuid()'];

		/*Complete the insert query*/
		$values = array(':PeroozUserId' => $this->perooz_user_id,
						':UserTypeId' => $this->user_type_id,
						':UserPaymentTypeId' => $this->user_payment_type_id,
						':FirstName' => $this->first_name,
						':LastName' => $this->last_name,
						':UserIP' => $this->user_ip,
						':Email' => $this->email,
						':Phone' => $this->phone);
		
		foreach($values as $key=>$value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->insert($this->insert_stmt,$values,$value_options,$this->api,'User');

		return $result; 

	}

	/*Setting the variable from the db*/
	public function set_from_db($con){

		$returned_thing = false; 

		/*Set values and select statement based upon the whether primary key or uuid is provided*/
		if (isset($this->user_id)){
			$values = array(':UserId' => $this->user_id);
			$final_stmt = $this->select_stmt.'UserId=:UserId';
		}elseif(isset($this->perooz_user_id)){
			$values = array(':PeroozUserId' => $this->perooz_user_id);
			$final_stmt = $this->select_stmt.'PeroozUserId=:PeroozUserId';
		}

		/*Select from db*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){
			$returned_thing = true; 
			$this->user_id = $result[0]['UserId'];
			$this->perooz_user_id = $result[0]['PeroozUserId'];
			$this->user_type_id = $result[0]['UserTypeId'];
			$this->user_payment_type_id = $result[0]['UserPaymentTypeId'];	
			$this->first_name = $result[0]['FirstName'];
			$this->last_name = $result[0]['LastName'];
			$this->user_ip = $result[0]['UserIP'];
			$this->email = $result[0]['Email'];
			$this->phone = $result[0]['Phone'];
		}
		return $returned_thing;
	}

	/*Update the user entry in the database*/
	public function update_to_db($values,$con){
		$stmt_set = '';

		if (array_key_exists(':UserId', $values)){
			$stmt_where = 'UserId=:UserId';
		}elseif(array_key_exists(':PeroozUserId', $values)){
			$stmt_where = 'PeroozUserId=:PeroozUserId';
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

	/*Obtain list of all charges*/
	public function set_charges_list($con,$count = null,$offset = null){

		$returned_thing = false; 

		$stmt_end =  ''; 

		/*If limit and offset are given for retrieval of bulk object*/
		if (isset($count)){
			if (isset($offset)){
				$stmt_end = 'LIMIT '.$offset.','.$count; 
			}else{
				$stmt_end = 'LIMIT '.$count; 
			}
		}

		/*Set final statement*/
		$final_stmt = $this->charge_list_stmt.' '.$stmt_end;

		/*Bind values necessary for pdo sql query*/
		$values = array(':UserId' => $this->user_id);

		foreach ($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		/*Complete query and obtain results*/
		$results = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($results)){
			$returned_thing = true; 
			foreach($results as $row){
				$this->charge_list[] = $row['UserChargeId'];
			}
		}

		return $returned_thing; 
		
	}
	
}

?>