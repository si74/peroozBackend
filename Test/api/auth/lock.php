<?php

/**
 * Script that locks a user account after ten consecutive login attempts 
 * Author: Sneha Inguva
 * Date: 8-4-2014
 */

require_once('../../config.php'); 
require_once('../../db/mysqldb.php');

$client_id = '';
$username = $_POST['username']; 
$request_method = $_SERVER['REQUEST_METHOD'];

$headers = getallheaders();
if ($headers){
	foreach($headers as $header => $value){
		if ($header == 'Client-Id'){
			$client_id = $value;
		}
	}
}

/*Ensure correct client id and http method*/
if ($client_id == '13adfewasdf432dae' && $request_method == 'POST'){

	$con = new mysqldb($db_settings1,false);

	$stmt = 'Update UserLogins Set IsLocked=1 Where Username=:Username';
	$stmt_prop = array(':Username' => PDO::PARAM_STR); 
	$stmt_values = array(':Username' => $username); 
	$result = $con->update($stmt,$stmt_values,$stmt_prop); 

	if($result){
		http_response_code(200);
		echo json_encode(array('message' => 'OK')); 
	}else{
		http_response_code(200); 
		echo json_encode(array('message' => 'Unable to complete request'));
	}

}else{
	http_response_code(400); 
	echo json_encode(array('message' => 'Incorrect authentication or request method.')); 
}

?>