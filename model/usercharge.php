<?php

class usercharge{

	/*Protected object variables*/
	protected $user_charge_id; 
	protected $perooz_user_charge_id; 
	protected $stripe_charge_id; 
	protected $user_id; 
	protected $stripe_subscription_id;
	protected $charge_success; //boolean saying true or false
	protected $date_last_attempt; //datetime of last charge attempt
	protected $num_attempts; 
	protected $date; 

	/*General sql statements*/
	private $select_stmt = 'Select UserChargeId,PeroozUserChargeId,StripeChargeId,UserId,StripeSubscriptionId,ChargeSuccess,DateofLastAttempt From UserCharges Where ';
	private $insert_stmt = 'Insert Into UserCharges (PeroozUserChargeId,StripeChargeId,UserId,StripeSubscriptionId,ChargeSuccess,DateofLastAttempt,NumAttempts) Values (:PeroozUserChargeId,:StripeChargeId,:UserId,:StripeSubscriptionId,:ChargeSuccess,:DateofLastAttempt,:NumAttempts)';
	private $update_stmt = 'Update UserCharges Set';

	/*General parameter requirements for class properties*/

	private $prop_parameters = array(':UserChargeId' => PDO::PARAM_INT,
									 ':PeroozUserChargeId' => PDO::PARAM_STR,
									 ':StripeChargeId' => PDO::PARAM_STR,
									 ':UserId' => PDO::PARAM_INT,
									 ':StripeSubscriptionId' => PDO::PARAM_STR,
									 ':ChargeSuccess' => PDO::PARAM_INT,
									 ':DateofLastAttempt' => PDO::PARAM_STR,
									 ':NumAttempts' => PDO::PARAM_INT);

	/*Constructor for object*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			foreach($args[0] as $key => $value){
				$this->$key = $value;
			}
			if (isset($this->con)){
				if (isset($this->user_charge_id) || isset($this->perooz_user_charge_id)){
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

	/*set and get methods*/
	public function __get($name){
		return $this->$name;
	}

	/*Function set method*/
	public function __set($name, $value){
		$this->$name = $value;
	}
	
	/*Function to insert in db*/
	public function insert_db($con){

		/*First generate a new uuid and set the value for it*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_user_charge_id = 'pz_'.$uid['uuid()'];

		/*Insert into database*/
		$values = array(':PeroozUserChargeId' => $this->perooz_user_charge_id,
						':StripeChargeId' => $this->stripe_charge_id,
						':UserId' => $this->user_id,
						':StripeSubscriptionId' => $this->stripe_subscription_id,
						':ChargeSuccess' => $this->charge_success,
						':DateofLastAttempt' => $this->date_last_attempt,
						':NumAttempts' => $this->num_attempts);

		foreach($values as $key=>$value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->insert($this->insert_stmt,$values,$value_options);

		return $result; 

	}

	/*Function to set object values from database*/
	public function set_from_db($con){

		$returned_thing = false; 

		/*Set values and select statement based upon whether pk or uuid was provided*/
		if (isset($this->user_charge_id)){
			$values = array(':UserChargeId' => $this->user_charge_id);
			$final_stmt = $this->select_stmt.'UserChargeId=:UserChargeId';
		}elseif(isset($this->perooz_user_charge_id)){
			$values = array(':PeroozUserChargeId' => $this->perooz_user_charge_id);
			$final_stmt = $this->select_stmt.'PeroozUserChargeId=:PeroozUserChargeId';
		}

		/*Select object from db*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key];
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){
			$returned_thing = true;
			$this->user_charge_id = $result[0]['UserChargeId'];
			$this->perooz_user_charge_id = $result[0]['PeroozUserChargeId'];
			$this->stripe_charge_id = $result[0]['StripeChargeId']; 
			$this->user_id = $result[0]['UserId']; 
			$this->stripe_subscription_id = $result[0]['StripeSubscriptionId']; 
			$this->charge_success = $result[0]['ChargeSuccess']; 
			$this->date_last_attempt = $result[0]['DateofLastAttempt'];
		}

		return $returned_thing;

	}

	/*Function to update object values*/
	public function update_to_db($values,$con){
		$stmt_set = '';

		if (array_key_exists(':UserChargeId', $values)){
			$stmt_where = 'UserChargeId=:UserChargeId';
		}elseif(array_key_exists(':PeroozUserChargeId', $values)){
			$stmt_where = 'PeroozUserChargeId=:PeroozUserChargeId';
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


}

?>