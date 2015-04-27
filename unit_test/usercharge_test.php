<?php

include('../config.php'); 
include('../model/usercharge.php'); 
include ('../db/mysqldb.php');

/*Database connection*/
$con = new mysqldb($db_settings,false); 

/*Settings for the database*/
$settings = array('user_charge_id' => 1, 'con' => $con); 
$settings1 = array('perooz_user_charge_id' => 'pz_23432dfslasdf', 'con' => $con); 
$settings2 = array('stripe_charge_id' => 'ch_asfasdfa', 'user_id' => 1, 
				'stripe_subscription_id' => null, 'charge_success' => 1, 
				'date_last_attempt' => '2014-07-02 00:00:00', 'num_attempts' => 1); 
$values1 = array(':UserChargeId' => 1, 
				':StripeChargeId' => 'ch_asfaff32sdfaBATMAN',':NumAttempts' => 1);
$values2 = array(':PeroozUserChargeId' => 'im batman');

/*Check initialization*/
$ex = new usercharge(); 
$ex1 = new usercharge($settings); 
$ex2 = new usercharge($settings1);
$ex3 = new usercharge($settings2);

/*Check getter and setter methods*/
$ex->perooz_user_charge_id = 'wohoo'; 

echo 'Checking Getter Method: '.$ex->perooz_user_charge_id.'<br/><br/>'; 

/*Check set from db methods*/
echo 'Checking set from db method: '.$ex1->perooz_user_charge_id.'<br/>'; 
echo 'Checking set2: '.$ex2->user_charge_id.'<br/>'; 

/*Check insert db method*/
$result = $ex3->insert_db($con); 
var_dump($result);

// $result = $ex->update_to_db($values1,$con);
// var_dump($result);


?>