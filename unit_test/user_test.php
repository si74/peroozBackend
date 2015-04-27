<?php

include('../config.php'); 
include('../model/user.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false); 

/*Settings for object*/
$settings = array('con' => $con, 'user_id' => 1); 
$settings1 = array('con' => $con, 'perooz_user_id' => 'pz_3ef28ffb-095b-11e4-8db0-002590d1d1c4'); 
$settings2 = array('user_type_id' => 1, 'user_payment_type_id' => 1, 'first_name' => 'Shaniqua', 'last_name' => 'Inguva', 'email' => 'testshaniqua@gmail.com' , 'phone' => '5555555555', 'user_ip' => null);
$values = array(':UserId' => 1, ':FirstName' => 'Daniela'); 
$values2 = array(':PeroozUserId' => 'pz_3ef28ffb-095b-11e4-8db0-002590d1d1c4', ':FirstName' => 'ScoutGatsby'); 

/*Initialize object*/
$ex = new user(); 
$ex1 = new user($settings); 
$ex2 = new user($settings1);
$ex3 = new user($settings2); 

/*Check setter and getter methods*/
$ex->first_name = 'coolio'; 

echo 'FirstName example: '.$ex->first_name.'<br/><br/>'; 

/*Check set from db method*/
echo 'User check from db: '.$ex1->first_name.'<br/>';
echo 'User check from db 2: '.$ex2->first_name.'<br/><br/>';

/*Check insert db method*/
//$result = $ex3->insert_db($con); 
//var_dump($result);

/*Check update db method*/
//$result = $ex->update_to_db($values,$con); 
//var_dump($result);

//$result2 = $ex->update_to_db($values2,$con);
//var_dump($result2); 

/*Check getting the user charge list*/
/*With absolutely no limits*/
$result = $ex1->set_charges_list($con,2,1); 
Var_dump($result);
echo '<br/>';
var_dump($ex1->charge_list); 
?>