<?php

/**
 * Script that removes a particular session token from the database
 * Author: Sneha Inguva
 * Date: 10-22-2014
 */

//necessary required files
require_once('../../config.php'); 
require_once('../../db/mysqldb.php');

//relevant tokens
$client_id = ''; 
$sess_token = ''; 
$request_method = $_SERVER['REQUEST_METHOD'];

//go through headers to get client id and session token
$headers = getallheaders();
if ($headers){
	foreach ($headers as $header => $value){
		if($header == "Client-Id"){
			$client_id = $value;
		}else if($header == "Session-Token"){
			$sess_token = $value;
		}
	}
}

//run remove session function; 
remove_sess(); 

/**
 * Remove session token fxn
 * @return [bool] [removal_success]
 */
function remove_sess(){

	$result = false; 

	global $client_id, $sess_token, $request_method,$db_settings1; 

	/*Check if api key is valid*/
	if ($client_id != '13adfewasdf432dae'){
		http_response_code(401); 
		echo json_encode(array('message' => 'Invalid client id.'));
		return $result;
	}

	/*If there is no session token present*/
	if (!$sess_token){
		http_response_code(401); 
		echo json_encode(array('message' => 'Session token expired. Please login again.'));
		return $result;
	}

	/*Create db connection for main database*/
	$con = new mysqldb($db_settings1, false);

	$stmt = "Delete From UserSessions Where UserSessionToken=:UserSessionToken";
	$values = array(":UserSessionToken" => md5($sess_token));
	$value_prop = array(":UserSessionToken" => PDO::PARAM_STR);
	$result = $con->delete($stmt,$values,$value_prop);

	if ($result){
		http_response_code(200);
		echo json_encode(array('message' => 'OK'));
	}else{
		http_response_code(400);
		echo json_encode(array('message' => 'Bad request.'));
	}

	return $result;

}

?>