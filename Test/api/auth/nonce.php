<?php

/***
Description: File to generate unique nonce
Author: Sneha Inguva
Date: 7-30-2014
**/

require_once('../../config.php'); 
require_once('../../db/mysqldb.php');

$client_id = ""; 
$request_method = $_SERVER['REQUEST_METHOD'];

/*Receive GET request*/
/*Iterate through the relevant http request headers and obtain client id*/
$headers = getallheaders(); 
if ($headers){
	foreach($headers as $header => $value){
		if ($header =='Client-Id'){
			$client_id = $value; 
		}
	}
}

/*Ensure that the client id is correct and a get method is being called*/
if ($client_id == "13adfewasdf432dae" && $request_method == "GET"){

	/*Create database connection*/
	$con = new mysqldb($db_settings,false); 

	/*Generate uuid which is nonce*/
	$nonce_exists = true; 

	/*Generate uuid which is nonce*/
	$uid = $con->query('Select uuid()')->fetch(); 
	$nonce = $uid['uuid()'];
	$nonce_hash = $con->quote(md5($nonce)); //hash nonce value using md5 to store in db
	$expiry_date = date('Y-m-d H:i:s', strtotime('+1 day')); //nonce value expires 24 hours after creation

	/*Set up statement,bind values, and insert into db*/
	$stmt = 'Insert Into nonce_values (nonce_value,expiry_time) Values (:nonce,:expiry_time)';
	$parameters = array(':nonce' => PDO::PARAM_STR,':expiry_time' => PDO::PARAM_STR);
	$values = array(':nonce' => $nonce_hash,':expiry_time' => $expiry_date); 

	$result = $con->insert($stmt,$values,$parameters); 

	/*Set response header and return nonce*/
	if ($result){ //if nonce successfully added to the db

		http_response_code(200); 
		echo json_encode(array('nonce' => $nonce)); 

	}else{ //if nonce not successfully added to the db

		http_response_code(500); 

	}

}else{ //send back authentication failure or incorrect request error

	http_response_code(400); 

}

?>

