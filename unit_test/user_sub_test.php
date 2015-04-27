<?php

include('../config.php'); 
include('../model/usersubscription.php'); 
include ('../db/mysqldb.php');

/*Database connection*/
$con = new mysqldb($db_settings,false); 

/*Relevant settings*/
$settings = array('user_subscription_id' => 1, 'con' => $con); 
$settings1 = array('perooz_user_subscription_id' => 'pz_asdf234', 'con' => $con); 
$settings2 = array('user_id' => 1, 'stripe_customer_id' => 'ch_lkj10', 
				   'stripe_card_id' => 'ch_2lk', 'user_plan_id' => 1, 'recurring' => 0, 
				   'expiry_date' => '2014-07-01 00:00:00');

$values = array('user_subscription_id' => 1, 'con' => $con); 
$values2 = array(':PeroozUserSubscriptionId' => 'pz_asdf234',':StripeCardId' => 'BATMAN'); 

/*Initialize object*/
$ex = new usersubscription();
// $ex1 = new usersubscription($settings); 
$ex2 = new usersubscription($settings1);
$ex3 = new usersubscription($settings2);
/*Test setter and getter methods*/
//$ex->perooz_user_subscription_id = 'BATMAN'; 

//echo 'Get method test: '.$ex->perooz_user_subscription_id.'<br/><br/>';

/*Test set from db methods*/
//echo 'Yay: '.$ex1->user_subscription_id.'<br/>';
//echo 'Set method works: '.$ex1->stripe_customer_id.'<br/><br/>';

//echo  'Yay2: '.$ex2->user_subscription_id.'<br/>'; 

/*Test insert db*/
//$result = $ex3->insert_db($con);
//var_dump($result); 

$result = $ex->update_to_db($values2,$con);
var_dump($result);

?>