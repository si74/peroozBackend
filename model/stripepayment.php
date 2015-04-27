<?php

/*PHP Class for stripe payment - creates payment object*/
/*6-12-2014*/
/*Author:Sneha Inguva*/

class stripepayment{

	/*Note - these values to be initialized via constructor*/
	/*Not all valueas will be set every time*/
	protected $api_key; //private stripe key
	protected $date; //creation date

	/*Acceptable values for insert and updates using stripe api-------------------------------------------------------------------------------------------------------*/
	
	/*Customer values*/
	protected $customer_create = array('account_balance' => 'integer', //remaining balance on the account in cents [negative can be utilized towards invoice, pos is debt]
									   'card' => 'string', //card can be provided as dictionary or token, which is recommended [as created by Stripe.js]
									   'coupon' => 'string', //if coupon code provided, customer has discount applied
									   'description' => 'string', //general description of customer
									   'email' => 'string', //email address
									   'metadata' => 'array', //array of descriptors for customer
									   'plan' => 'string', //applies plan to customer - creating/returning a subscription
									   'quantity' => 'integer', //how many x plan fee to pay
									   'trial_end' => 'integer'); //Unix timestamp giving end of free trial [overrides default trial of plan]
	
	protected $customer_update = array('account_balance' => 'integer', //remaining balance on the account in cents 
									   'card' => 'string', //if card given, will override default [use add card to add additional cards]
									   'coupon' => 'string', //if coupon code provided, customer has discount applied
									   'default_card' => 'integer', //id of default card
									   'description' => 'string', 
									   'email' => 'string',
									   'metadata' => 'array'); 

	/*Card values*/
	protected $card_create = array('number' => 'string',
								   'exp_month' => 'integer',
								   'exp_year' => 'integer',
								   'cvc' => 'integer', //optional but recommended
								   'name' => 'string', //full name - optional
								   'address_line1' => 'string', //all address lines optional but recommended
								   'address_line2' => 'string',
								   'address_city' => 'string',
								   'address_zip' => 'string',
								   'address_state' => 'string',
								   'address_country' => 'string');
	
	protected $card_update = array('address_city' => 'string', //can change basic features but not actual number or cvc
								   'address_country' => 'string',
								   'address_line1' => 'string', 
								   'address_line2' => 'string',
								   'address_city' => 'string',
								   'address_zip' => 'string',
								   'address_state' => 'string',
								   'exp_month' => 'integer',
								   'exp_year' => 'integer',
								   'name' => 'string');

	/*Charge object to be created*/
	protected $charge_create = array('amount' => 'integer', //charge in cents
							  'currency' => 'string', //ISO  code for currency 
							  'customer' => 'string', //EITHER ENTER ID FOR CUSTOMER OR CARD. [or enter dictionary for card]
							  'card' => 'string', //if card provided, must also provide customer (if only giving string) or give associative array or token [like provided by Stripe.js]
							  'metadata' => 'array',//key-value pairs to store about the charge 
							  'capture' => 'boolean', //whether or not to immediately capture charge or issue authorization. default in this class is true
							  'statement_description' => 'boolean', //how charge is to appear ccard bill
							  'receipt_email' => 'string', //email of individual to receive receipt
							  'application_fee' => 'integer'); //fee in cents that will be applied to the charge and transferred to owner's Stripe account
	
	protected $charge_cancel = array('amount' => 'integer', //default is entire amount [if not specified]
									 'refund_application_fee' => 'boolean',
									 'metadata' => 'array'); //potentially useful key-value pair

	protected $charge_update = array('description' => 'string', //basic description of the charge
									 'metadata' => 'array'); //associative array that can be used to describe the charge

	/*Subscription values*/
	protected $subscription_create = array('plan' => 'string', //identifier of the plan
										   'coupon' => 'string', //optional
										   'trial_end' => 'integer',
										   'card' => 'string', //can be string token or associative array 
										   'quantity' => 'integer', //# of subscriptions
										   'application_fee_percent' => 'double', //how much of subscription due/invoice
										   'metadata' => 'array'); //associative array 
	protected $subscription_update = array('plan' => 'string',
										   'coupon' => 'string', //optional
										   'prorate' => 'boolean', //default true. whether or not to prorate when switching in mid cycle
										   'trial_end' => 'integer',
										   'card' => 'string',
										   'quantity' => 'integer',
										   'application_fee_percent' => 'double',
										   'metadata' => 'array');

	/*---------------------------------------------------------------------------------------------------------------------------------------------------------------*/
    /*public constructor*/
    /*Arguments can be of the following - the stripe api key*/
	function __construct($api_key){
		$this->api_key = $api_key;
		Stripe::setApiKey($this->api_key);

		$this->date = time(); 
	}

	function __destruct(){
	}

	/*Function get method - works for nonarray member variables*/
	public function __get($name){
		return $this->$name;
	}

	/*Function set method - works for nonarray member variables*/
	public function __set($name, $value){
		$this->$name = $value;
	}

	/*Retrieve error code and message from error message--------------*/
	public function get_error($error){
		$body = $error->getJsonBody();
		$err = $body['error']; 
		$msg = $err['message'];

		return $msg;
	}

	/*CUSTOMER RELATED--------------------------------------*/

	/*CREATE Customer*/
	/*If customer created returns json object with customer details, otherwise returns false*/
	/*Can later convert json object to associative array or stdobject using json_decode()*/
	public function create_customer($values){
		$valid_values = false;
		$result = false; 

		/*Verify that the values of the customer in associative array are valid and of the valid type*/
		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->customer_create)){
				if (gettype($value) == $this->customer_create[$key]){
					$valid_values = true;
				}
			}
		}


		/*Create customer 
			AND
		 check for all possible stripe errors and exceptions - but NOT card errors 
		-card should be validated separatedly or card added independently
		-only card token to be used when creating new customer*/
		if ($valid_values){
			try{
				$result = Stripe_Customer::create($values);
			}catch(Stripe_InvalidRequestError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_customer($e);
				error_log("Mistake with stripe customer create function call at [$time]: Invalid parameters supplied: $msg.",3,'../error_log/stripe_error.txt');
			}catch(Stripe_AuthenticationError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_customer($e);
				error_log("Authentication with stripe''s api failed at [$time]: $msg",3,'../error_log/stripe_error.txt');
			}catch(Stripe_ApiConnectionError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_customer($e);
				error_log("Network communication with stripe error at [$time]: $msg",3,'../error_log/stripe_error.txt');
			}catch(Stripe_Error $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_customer($e);
				error_log("Stripe error at [$time]: $msg",3,'../error_log/stripe_error.txt');
			}catch(Exception $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_customer($e);
				error_log("Error during stripe customer create call at [$time]: $msg",3,'../error_log/stripe_error.txt');
			}
		}else{
			$time = date('-m-d H:i:s');
			error_log('Mistake with stripe customer create function call at [ $time ]: Invalid values entered for customer.', 3, '../error_log/stripe_error.txt');
		}

		return $result; 
	}

	/*UPDATE Customer*/
	/*If customer updated, returns json object with customer details, otherwise returns false*/
	/* arguments - (1) stripe customer id (2) values to be updated*/
	public function update_customer($customer_id,$values){
		$valid_values = false;
		$result = false; 

		/*Verify that the values of the customer in associative array are valid and of the valid type*/
		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->customer_update)){
				if (gettype($value) == $this->customer_update[$key]){
					$valid_values = true;
				}
			}
		}

		/*Try to update customer and check for errors*/
		if ($valid_values){
			$cus = $this->get_customer($customer_id);

			/*If customer exists*/
			if ($cus){
				foreach($values as $key => $value){
					$cus->$key = $value;
				}

				/*Try to save changes to object*/
				try{
					$result = $cus->save(); 
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe customer update function call at [$time]: Invalid parameters supplied: $msg.",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg",3,'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg",3,'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Error during stripe customer update call at [$time]: $msg",3,'../error_log/stripe_error.txt');
				}
			}
		}else{
			$time = date('Y-m-d H:i:s');
			error_log('Mistake with stripe customer create function call at [ $time ]: Invalid values entered for customer.', 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Retrieve customer*/
	/*Provide stripe customer id to retrieve*/
	/*If valid customer retrieved, json object with customer returned. Otherwise boolean false returned*/
	/*arguments - (1) stripe customer id*/
	public function get_customer($customer_id){
		$result = false; 

		/*Try to retrieve customer and check for errors*/
		try{
			$result = Stripe_Customer::retrieve($customer_id);
		}catch(Stripe_InvalidRequestError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Mistake with stripe customer get call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_AuthenticationError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_ApiConnectionError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_Error $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Error during stripe customer get call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}

		/*Check if item was deleted*/
		if (gettype($result) == 'array'){
			$result_decode = json_decode($result,true); 
			
			/*If customer object retrieved has actually been deleted - return error and false*/
			if (array_key_exists('deleted', $result_decode)){
				if ($result_decode['deleted']){
					$result = false; 
					$time = date('Y-m-d H:i:s');
					error_log("Mistake with stripe customer get call at [$time]: Calling deleted customer.\r\n",3,'../error_log/stripe_error.txt');
				}
			}
		}

		/*Check if item was null*/
		if (empty($result)){
			$result = false;
		}

		return $result;
	}
	
	/*CARD RELATED-------------------------------------------*/
	/*Create new credit card*/
	/* arguments - (1) stripe customer id (2)associative array of values to be updated*/
	public function create_card($customer_id,$values){
		$valid_values = false;
		$result = false; 

		/*Check what type of argument is given in $values*/

		if (gettype($values) == 'array'){
			/*If array, check for valid values*/
			foreach ($values as $key => $value){
				if (array_key_exists($key,$this->card_create)){
					if (gettype($value) == $this->card_create[$key]){
						$valid_values = true;
					}
				}
			}
		}elseif (gettype($values) == 'string'){
			$valid_values = true; 
		}

		if ($valid_values){
			$card_values = array('card' => $values);
			$cus = $this->get_customer($customer_id);

			if ($cus){
				try{	
					$result = $cus->cards->create($card_values);
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe card create call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg.\r\n",'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					error_log("Error during stripe card create call at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}
			}
		}else{
			$time = date('-m-d H:i:s');
			error_log("Mistake with stripe card create function call at [ $time ]: Invalid values entered for card.\r\n", 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Update the credit card*/
	/* arguments - (1) stripe customer id (2) stripe card id (3) associative array of values to be updated*/
	public function update_card($customer_id,$card_id,$values){
		$valid_values = false; 
		$result = false; 

		/*Check for valid values*/
		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->card_update)){
				if (gettype($value) == $this->card_update[$key]){
					$valid_values = true;
				}
			}
		}

		/*If valid values utilized in the array, proceed*/
		if ($valid_values){
			$cus = $this->get_customer($customer_id);
			
			if ($cus){
				$cd = $this->get_card($cus,$card_id);
			
				if ($cd){

					foreach($values as $key => $value){
						$cd->$key = $value;
					}

					try{
						$result = $cd->save();	
					}catch(Stripe_InvalidRequestError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
					}catch(Stripe_AuthenticationError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Authentication with stripe''s api failed at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
					}catch(Stripe_ApiConnectionError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Network communication with stripe error at [$time]: $msg.\r\n",'../error_log/stripe_error.txt');
					}catch(Stripe_Error $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Stripe error at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
					}catch(Exception $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Error during stripe card update call at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
					}
				}

			}
		}else{
			$time = date('Y-m-d H:i:s');
			error_log("Mistake with stripe card update function call at [ $time ]: Invalid values entered for card.\r\n", 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Delete credit card*/
	/* arguments - (1) stripe customer id  (2) stripe card id*/
	public function delete_card($customer_id,$card_id){
		$result = false; 

		$cus = $this->get_customer($customer_id); 

		if ($cus){

			$cd = $this->get_card($cus,$card_id);

			if ($cd){
				try{
					$result = $cd->delete(); 
					
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied:$msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg.\r\n",'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Error during stripe card update call at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}
			}
		}

		return $result;
	}

	/*Retrieve credit card*/
	/*arguments -(1) stripe customer [json] object (2) stripe card id*/
	/*must call get_customer and insert returned object into the get_card fxn as $cus*/
	public function get_card($cus,$card_id){
		$result = false;

		try{
			$result = $cus->cards->retrieve($card_id);
		}catch(Stripe_InvalidRequestError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Mistake with stripe card get function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_AuthenticationError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Authentication with stripe''s api failed at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_ApiConnectionError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Network communication with stripe error at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_Error $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Stripe error at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Error during stripe card get call at [$time]: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}

		/*Check if credit card has actually been deleted*/

		return $result;

	}

	/*CHARGE RELATED-------------------------------------*/
	/*Creating a charge - can either create a charge on a customer or a card*/
	/*arguments -(1) values for the charge objects*/
	public function create_charge($values){
		$valid_values = false;
		$result = false;

		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->charge_create)){
				if (gettype($value) == $this->charge_create[$key]){
					$valid_values = true;
				}
			}
		}

		if ($valid_values){
			try{
				$result = Stripe_Charge::create($values);
			}catch(Stripe_CardError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Mistake with stripe customer charge at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_InvalidRequestError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Mistake with stripe customer get call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_AuthenticationError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
			}catch(Stripe_ApiConnectionError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_Error $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Exception $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Error during stripe customer get call at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
			}
		}else{
			$time = date('-m-d H:i:s');
			error_log("Mistake with stripe charge create function call at [ $time ]: Invalid values entered for charge.\r\n", 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Refund the charge object*/
	/*May have specific information given regarding the refund*/
	public function cancel_charge($charge_id,$values = null){
		$result = false; 
		$valid_values = false; 

		$chg = $this->get_charge($charge_id); 

		if ($chg){

			if (!empty($values)){
				foreach ($values as $key => $value){
					if (array_key_exists($key,$this->charge_cancel)){
						if (gettype($value) == $this->charge_cancel[$key]){
							$valid_values = true;
						}
					}
				}
			} 

			try{
				if (empty($values)){
					$result = $chg->refund();
				}else{
					if ($valid_values){
						$result = $chg->refund($values);
					}else{
						$time = date('Y-m-d H:i:s');
						error_log("Invalid parameters supplied to refund charge at [$time].\r\n",3,'../error_log/stripe_error.txt');
					}
				}
			}catch(Stripe_InvalidRequestError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Mistake with stripe cancel charge function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_AuthenticationError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_ApiConnectionError $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Stripe_Error $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}catch(Exception $e){
				$time = date('Y-m-d H:i:s');
				$msg = $this->get_error($e);
				error_log("Error during stripe charge cancel call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
			}
		}

		return $result;
	}

	/*Update the charge object*/
	public function update_charge($charge_id,$values){
		$result = false; 
		$valid_values = false; 

		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->charge_update)){
				if (gettype($value) == $this->charge_update[$key]){
					$valid_values = true;
				}
			}
		}

		if ($valid_values){

			$chg = $this->get_charge($charge_id);

			if ($chg){

				foreach($values as $key => $value){
					$chg->$key = $value;
				}

				try{
					$result = $chg->save(); 
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe customer get call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Error during stripe update charge call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}

			}
		}

		return $result;

	}

	/*Retrieve the charge object*/
	public function get_charge($charge_id){
		$result = false; 

		try{
			$result = Stripe_Charge::retrieve($charge_id);
		}catch(Stripe_InvalidRequestError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Mistake with stripe customer get call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_AuthenticationError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
		}catch(Stripe_ApiConnectionError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_Error $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Error during stripe get charge call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}

		return $result;

	}

	/*SUBSCRIPTION RELATED---------------------------*/ 
	/*When a plan is applied to a customer - creates a subscription*/
	/*arguments -(1) stripe customer id (2) values for subscription object*/
	public function create_subscription($customer_id,$values){
		$valid_values = false;
		$result = false;

		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->subscription_create)){
				if (gettype($value) == $this->subscription_create[$key]){
					$valid_values = true;
				}
			}
		}

		if ($valid_values){
			$cus = $this->get_customer($customer_id);

			if ($cus){
				try{
					$result = $cus->subscriptions->create($values);
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Error during stripe card update call at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
				}
			}

		}else{
			$time = date('-m-d H:i:s');
			error_log("Mistake with stripe subscription create function call at [ $time ]: Invalid values entered for subscription.\r\n", 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Update subscription object*/
	/*arguments -(1) stripe customer id (2) stripe subscription id (3) values for the subscription object*/
	public function update_subscription($customer_id,$subscription_id,$values){
		$valid_values = false; 
		$result = false;

		foreach ($values as $key => $value){
			if (array_key_exists($key,$this->subscription_update)){
				if (gettype($value) == $this->subscription_update[$key]){
					$valid_values = true;
				}
			}
		}

		if ($valid_values){
			$cus = $this->get_customer($customer_id);

			if($cus){
				$subscrip = $this->get_subscription($cus,$subscription_id);
			
				if ($subscrip){

					foreach($values as $key => $value){
						$subscrip->$key = $value;
					}

					try{
						$result = $subscrip->save();
					}catch(Stripe_InvalidRequestError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
					}catch(Stripe_AuthenticationError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
					}catch(Stripe_ApiConnectionError $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
					}catch(Stripe_Error $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
					}catch(Exception $e){
						$time = date('Y-m-d H:i:s');
						$msg = $this->get_error($e);
						error_log("Error during stripe card update call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
					}
				}
			}
		}else{
			$time = date('-m-d H:i:s');
			error_log("Mistake with stripe subscription update function call at [ $time ]: Invalid values entered for subscription.\r\n", 3, '../error_log/stripe_error.txt');
		}

		return $result;
	}

	/*Cancel subscription object*/
	/*arguments -(1) stripe customer id (2) stripe subscription id (3) if to be cancelled at period end*/
	public function cancel_subscription($customer_id,$subscription_id,$at_period_end = false){
		$result = false;

		$cus = $this->get_customer($customer_id);

		if ($cus){

			$subscrip = $this->get_subscription($cus,$subscription_id);

			if ($subscrip){
				try{
					$result = $subscrip->cancel(array('at_period_end' => $at_period_end));
				}catch(Stripe_InvalidRequestError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_AuthenticationError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",'../error_log/stripe_error.txt');
				}catch(Stripe_ApiConnectionError $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Stripe_Error $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}catch(Exception $e){
					$time = date('Y-m-d H:i:s');
					$msg = $this->get_error($e);
					error_log("Error during stripe card update call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
				}	
			}	
		}

		return $result;

	}

	/*Retrieve subscription object*/
	/*arguments - (1) stripe customer object (2) stripe subscription id*/
	public function get_subscription($cus,$subscription_id){
		$result = false;

		try{
			$result = $cus->subscriptions->retrieve($subscription_id);
		}catch(Stripe_InvalidRequestError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Mistake with stripe card update function call at [$time]: Invalid parameters supplied: $msg.\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_AuthenticationError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Authentication with stripe''s api failed at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_ApiConnectionError $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Network communication with stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Stripe_Error $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Stripe error at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}catch(Exception $e){
			$time = date('Y-m-d H:i:s');
			$msg = $this->get_error($e);
			error_log("Error during stripe card update call at [$time]: $msg\r\n",3,'../error_log/stripe_error.txt');
		}	

		/*Check if subscription actually exists*/

		return $result;
	}

	
}

?>