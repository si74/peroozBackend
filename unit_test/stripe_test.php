<?php

include('../config.php');
include('../model/stripepayment.php');
include('../db/mysqldb.php');
include('../stripe-php-master/lib/Stripe.php');

/*[1] Check constructor-----------------------------------------*/
$example = new stripepayment($stripe_key);

/*CUSTOMER FUNCTIONS-------------------------------------------------------*/
/*[2] Check get customer function-------------------------------*/

//$result = $example->get_customer('cus_4DrR2edfXK7muE');
//$result_decode = json_decode($result);

//[2][a] Check error catching expression for get customer bad id
//$bad_result = $example->get_customer('bcus_12');
//var_dump($bad_result);

//[2][b] Provide false id for skype authentications 
// $ex2 = 'asdf';
// $bad_result = $example->get_customer($ex2);
// var_dump($bad_result);

/*[3] Check update customer function---------------------------*/
//$result = $example->update_customer('cus_4DrR2edfXK7muE', array('email' => 'testsnehabutts360@gmail.com'));
//var_dump($result);

/*[4] Check create customer function---------------------------*/
/*$values = array('description' => 'cool test girl', 'email' => 'sneha.inguva@gmail.com');
									
$result = $example->create_customer($values);
var_dump($result);*/

//check invalid values
// $values = array('ya' => '1');
// $result = $example->create_customer($values);
// var_dump($result);
 
/*-------------------------------------------------------------------------*/

/*CARD FUNCTIONS*/
/*[5] Check create card function*/
// $card = array('number' => '4242424242424242',
// 			  'exp_month' => '12',
// 			  'exp_year' => '2014',
// 			  'cvc' => '232'); 
// $cus_id = 'cus_4GDPwIsxSUw1QO';
// $result = $example->create_card($cus_id,$card);
// var_dump($result);

/*[6] Check get card function*/
// $cd_id = 'card_104GN147ymvTiU7X7FCq4AOI';
// $cus_id = 'cus_4GDPwIsxSUw1QO';
//$cus = $example->get_customer($cus_id);

/*Retrieve card*/
//$result = $example->get_card($cus,$cd_id);
//var_dump($result);

/*Update card*/
// $values = array('name' => 'Shaniqua', 'exp_month' => 6);
// $result = $example->update_card($cus_id,$cd_id,$values);
// var_dump($result);

/*Delete card*/
// $result = $example->delete_card($cus_id,$cd_id);
// var_dump($result);

/*-------------------------------------------------------------------------*/

/*CHARGE FUNCTIONS*/
/*[7] Create Charge*/
// $charge = array('amount' => 200, 
// 				'currency' => 'USD',
// 				'customer' =>  'cus_4GDPwIsxSUw1QO'); 
// $charge2 = array('amount' => 300,
// 				 'currency' => 'USD',
// 				 'customer' => 'cus_4GDPwIsxSUw1QO',
// 				 'card' => 'card_104GT547ymvTiU7XdCyZjScg');
// $result = $example->create_charge($charge);
// $result2 = $example->create_charge($charge2);
// var_dump($result);
// echo '<br>';
// var_dump($result2);

/*[8] Get Charge*/

// $result = $example->get_charge('ch_104GTX47ymvTiU7X4CMyke0B');
// var_dump($result);

/*[9] Update Charge*/
// $values = array('description' => 'fun stuff shaniqua');
// $result = $example->update_charge('ch_104GTX47ymvTiU7X4CMyke0B',$values);
// echo "<tt><pre> $result </pre></tt>";

/*[10] Cancel charge*/
//$result = $example->cancel_charge('ch_104GTX47ymvTiU7X4CMyke0B');
//echo "<tt><pre> $result </pre></tt>";

// $values = array('amount' => 100);
// $result = $example->cancel_charge('ch_104GTX47ymvTiU7XGovQb3Xz',$values);
// echo "<tt><pre> $result </pre></tt>";

/*-------------------------------------------------------------------------*/

/*Subscription Functions*/
//Create subscription

$cus = 'cus_4GDPwIsxSUw1QO';
//$values = array('plan' => 'perooz_monthly');
//$result = $example->create_subscription($cus,$values);
//echo "<tt><pre> $result </pre></tt>";

//Retrieve subscription
$sub = 'sub_4GUod1tmbxkLWP';
// $cust = $example->get_customer($cus);
// $result = $example->get_subscription($cust,$sub);
//echo "<tt><pre> $result </tt></pre>";

//Update Subscription 
// $values = array('quantity' => 2); 
// $result2 = $example->update_subscription($cus,$sub,$values);
// echo "<tt><pre> $result2 </pre></tt>";

//Cancel subscription 
//$val = true;
//$result2 = $example->cancel_subscription($cus,$sub,$val);
//echo "<tt><pre> $result2 </pre></tt>";
														  

?>