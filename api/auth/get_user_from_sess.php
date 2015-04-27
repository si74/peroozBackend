<?php

/**
 * Script to grab user id from session token
 * Author: Sneha Inguva
 * Date: 10-27-2014
 */

require_once('../../config.php'); 
require_once('../../db/mysqldb.php');
include('../../model/user.php');

$client_id = '';
$session_token = ''; 
$request_method = $_SERVER['REQUEST_METHOD'];


/*Go through relevant headers to obtain the session token and client id*/
$headers = getallheaders(); 
if ($headers){
	foreach($headers as $header => $value){
		if ($header == 'Client-Id'){
			$client_id = $value; 
		}else if ($header == 'Session-Token'){
			$session_token = $value; 
		}
	}
}

/*Call get user function*/
get_user(); 

/*Get user function*/
function get_user(){

	$result = false;

	global $db_settings;
	global $client_id,$session_token,$request_method;

	/*Check api key is valid*/
	if ($client_id != '13adfewasdf432dae'){
		http_response_code(401); 
		echo json_encode(array('message' => 'Invalid client id.'));
		return $result; 
	}

	/*Is there is no session token present*/
	if (!$session_token){
		http_response_code(401);
		echo json_encode(array('message' => 'Session token expired. Please login again.'));
		return $result; 
	}

	$stmt = "Select PeroozUserId From Users Where UserId=(Select UserId From UserSessions Where UserSessionToken=:UserSessionToken)";
	$prop_param = array(':UserSessionToken' => PDO::PARAM_STR);
	$prop_values = array(':UserSessionToken' => md5($session_token));

	$con = new mysqldb($db_settings,false);

	$query_result = $con->multi_query($stmt,$prop_values,$prop_param);

	if ($query_result){
		http_response_code(200);
		echo json_encode(array('message' => 'OK', 'perooz_article_id' => $query_result[0]['PeroozUserId']));
	}else{
		http_response_code(200);
		echo json_encode(array('message' => 'None.'));
	}

}

?>