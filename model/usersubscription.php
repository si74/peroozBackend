<?php

class usersubscription{

	/*General object variables*/
	protected $user_subscription_id; 
	protected $perooz_user_subscription_id;
	protected $user_id; 
	protected $perooz_user_id; //usually not set
	protected $stripe_customer_id;
	protected $stripe_card_id; 
	protected $user_plan_id; //id represents which plan this is
	protected $recurring; 
	protected $expiry_date;

	/*Sql statements*/
	private $select_stmt = 'Select UserSubscriptionId,PeroozUserSubscriptionId,UserId,StripeCustomerId,StripeCardId,UserPlanId,Recurring,ExpiryDate From UserSubscriptions Where ';
	private $insert_stmt = 'Insert Into UserSubscriptions (PeroozUserSubscriptionId,UserId,StripeCustomerId,StripeCardId,UserPlanId,Recurring,ExpiryDate) Values (:PeroozUserSubscriptionId,:UserId,:StripeCustomerId,:StripeCardId,:UserPlanId,:Recurring,:ExpiryDate)';
	private $update_stmt = 'Update UserSubscriptions Set ';

	/*variable properties*/
	private $prop_parameters = array(':UserSubscriptionId' => PDO::PARAM_INT,
									 ':PeroozUserSubscriptionId' => PDO::PARAM_STR,
									 ':UserId' => PDO::PARAM_INT,
									 ':PeroozUserId' => PDO::PARAM_STR,
									 ':StripeCustomerId' => PDO::PARAM_STR,
									 ':StripeCardId' => PDO::PARAM_STR,
									 ':UserPlanId' => PDO::PARAM_INT,
									 ':Recurring' => PDO::PARAM_INT,
									 ':ExpiryDate' => PDO::PARAM_STR);

	/*Constructor*/
	function __construct(){
		if (func_num_args() > 0){
			$args = func_get_args(0);
			
			foreach($args[0] as $key => $value){
				$this->$key = $value; 
			}
			
			if (isset($this->con)){
				if (isset($this->user_subscription_id) || isset($this->perooz_user_subscription_id) 
					|| isset($this->user_id) || isset($this->perooz_user_id)){
					$properly_set = $this->set_from_db($this->con);
					if (!$properly_set){
						echo 'This has not worked.';
					}
				}
			}

		}
		$this->date = time();
	}

	/*Destructive*/
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

	/*Insert values into the database*/
	public function insert_db($con){

		/*Generate uuid*/
		$uid = $con->query('Select uuid()')->fetch();
		$this->perooz_user_subscription_id = 'pz_'.$uid['uuid()'];

		/*Insert entry to database*/
		$values = array(':PeroozUserSubscriptionId' => $this->perooz_user_subscription_id,
						':UserId' => $this->user_id,
			            ':StripeCustomerId' => $this->stripe_customer_id, 
			            ':StripeCardId' => $this->stripe_card_id, 
			            ':UserPlanId' => $this->user_plan_id,
			            ':Recurring' => $this->recurring, 
			            ':ExpiryDate' => $this->expiry_date);

		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}
		
		$result = $con->insert($this->insert_stmt,$values,$value_options);
		return $result;

	}

	/*Select values from the database*/
	public function set_from_db($con){

		$returned_thing = false;
	
		/*Values and stmt set depending on whether pk or uuid provided*/
		if (isset($this->user_subscription_id)){
			$values = array(':UserSubscriptionId' => $this->user_subscription_id);
			$final_stmt = $this->select_stmt.'UserSubscriptionId=:UserSubscriptionId';
		}elseif(isset($this->perooz_user_subscription_id)){
			$values = array(':PeroozUserSubscriptionId' => $this->perooz_user_subscription_id);
			$final_stmt = $this->select_stmt.'PeroozUserSubscriptionId=:PeroozUserSubscriptionId';
		}elseif(isset($this->user_id)){
			$values = array(':UserId' => $this->user_id);
			$final_stmt = $this->select_stmt.'UserId=:UserId';
		}elseif(isset($this->perooz_user_id)){
			$values = array(':PeroozUserId' => $this->perooz_user_id);
			$final_stmt = $this->select_stmt.'UserId=(Select UserId From Users Where PeroozUserId=:PeroozUserId)';
		}
		
		/*object set from db*/
		foreach($values as $key => $value){
			$value_options[$key] = $this->prop_parameters[$key]; 
		}

		$result = $con->multi_query($final_stmt,$values,$value_options);

		if (!empty($result)){
			$returned_thing = true;

			$this->user_subscription_id = $result[0]['UserSubscriptionId'];
			$this->perooz_user_subscription_id = $result[0]['PeroozUserSubscriptionId'];
			$this->user_id = $result[0]['UserId']; 
			$this->stripe_customer_id = $result[0]['StripeCustomerId'];
			$this->stripe_card_id = $result[0]['StripeCardId'];
			$this->user_plan_id = $result[0]['UserPlanId'];
			$this->recurring = $result[0]['Recurring']; 
			$this->expiry_date = $result[0]['ExpiryDate'];
		}
		
		return $returned_thing;
	}

	/*Update values to the database*/
	public function update_to_db($values,$con){

		$stmt_set = '';

		if (array_key_exists(':UserSubscriptionId', $values)){
			$stmt_where = 'UserSubscriptionId=:UserSubscriptionId'; 
		}elseif(array_key_exists(':PeroozUserSubscriptionId', $values)){
			$stmt_where = 'PeroozUserSubscriptionId=:PeroozUserSubscriptionId';
		}elseif(array_key_exists(':UserId', $values)){
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
	
}

?>