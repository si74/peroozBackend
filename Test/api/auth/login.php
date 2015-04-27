<?php

/**
 * Login script that returns session token with expiry date upon login
 * Author: Sneha Inguva
 * Date: 8-1-2014
 */

require_once('../../config.php'); 
require_once('../../db/mysqldb.php');
require_once('../../controller/lgn/PasswordHash.php');

$client_id = '';
$nonce = ''; 
$session_token = ''; 
$request_method = $_SERVER['REQUEST_METHOD']; 

/*Go through relevant headers to obtain the session token and client id*/
$headers = getallheaders(); 
if ($headers){
	foreach($headers as $header => $value){
		if ($header == 'Client-Id'){
			$client_id = $value; 
		}else if ($header == 'Nonce'){
			$nonce = $value; 
		}
	}
}

/*Check login function is called*/
check_login($client_id,$request_method,$nonce,$db_settings1);

/**
 * [fxn that gives session token if necessary tests satisfied]
 * @param  [str] $client_id      [app client id]
 * @param  [str] $request_method [http request method]
 * @param  [str] $nonce          [nonce value]
 * @return [bool]                 [description]
 */
function check_login($client_id,$request_method,$nonce,$db_settings){

	/*Check that the request used the correct client id*/
	if ($client_id != '13adfewasdf432dae'){
		http_response_code(400);
		echo json_encode(array('message' => 'Authentication failure. Please try again.'));
		return false; 
	}

	/*Check that the correct http method was utilized*/
	if ($request_method != 'POST'){
		http_response_code(400);
		echo json_encode(array('message' => 'Incorrect request format. Please try again.'));
		return false; 
	}

	/*Initialize Database*/
	$con = new mysqldb($db_settings,false);

	/*set variable values*/
	$username = ($_POST['uname']); 
	$password = ($_POST['pwd']); 

	/*Check existence of nonce value*/
	$stmt_get_nonce = "Select nonce_id,expiry_time from nonce_values where nonce_value=:nonce_value";
	$stmt_nonce_prop = array(':nonce_value' => PDO::PARAM_STR);
	$stmt_nonce_values = array(':nonce_value' => md5($nonce));
	$nonce_result = $con->multi_query($stmt_get_nonce,$stmt_nonce_values,$stmt_nonce_prop);

	/*Check if nonce value exists and has not expired*/
	if (!$nonce_result){
		http_response_code(400);
		echo json_encode(array('message' => 'Token expired.Please refresh page.'));
		return false; 
	}

	/*Check if nonce value has expired*/
	if(strtotime($nonce_result[0]['expiry_time']) <= time()){
		http_response_code(400); 
		echo json_encode(array('message' => 'Token expired. Please refresh page.')); 
		return false; 
	}

	/*Remove nonce from database*/
	$stmt_remove_nonce = 'Delete From nonce_values where nonce_value=:nonce_value'; 
	$stmt_remove_prop = array(':nonce_value' => PDO::PARAM_STR); 
	$stmt_remove_values = array(':nonce_value' => md5($nonce)); 
	$result = $con->delete($stmt_remove_nonce,$stmt_remove_values,$stmt_remove_prop);

	/*Create password hash*/
	$hasher = new PasswordHash(8,false); 

	$stmt_get_pw = "Select UserId,IsLocked,Password From UserLogins Where Username=:Username"; 
	$stmt_pw_prop = array(':Username' => PDO::PARAM_STR);
	$stmt_pw_values = array(':Username' => $username); 
	$hashed_pwd = $con->multi_query($stmt_get_pw,$stmt_pw_values,$stmt_pw_prop); 

	/*If username not in db*/
	if (!$hashed_pwd){
		http_response_code(200); 
		echo json_encode(array('message' => 'Username or password not correct. Please try again.')); 
		return false; 
	}

	/*If account is locked due to incorrect attempts*/
	if ($hashed_pwd[0]['IsLocked']){
		http_response_code(200); 
		echo json_encode(array('message' => 'User account locked. Please try again.')); 
		return false; 
	}

	$check = $hasher->CheckPassword($password, $hashed_pwd[0]['Password']);

	/*If password does not match*/
	if (!$check){
		http_response_code(200);
		echo json_encode(array('message' => 'Username or password incorrect. Please try again.'));
		return false;
	}

	/*UserId of individual*/
	$user_id = $hashed_pwd[0]['UserId']; 

	/*Create and return session token if password correct and authentication achieved*/
	$uid = $con->query('Select uuid()')->fetch(); 
	$sess_token = $uid['uuid()'];
	$sess_hash = $con->quote(crypt($nonce)); //hash session_token value using crypt to store in db
	$expiry_time = date('Y-m-d H:i:s', strtotime('+7 days'));

	$stmt_sess = 'Insert Into UserSessions (UserId,UserSessionToken,ExpiryTime) Values (:UserId,:UserSessionToken,date(:ExpiryTime))';
	$stmt_sess_prop = array(':UserId' => PDO::PARAM_INT, ':UserSessionToken' => PDO::PARAM_STR, ':ExpiryTime' => PDO::PARAM_STR);
	$stmt_sess_values = array(':UserId' => $user_id, ':UserSessionToken' => $sess_hash, ':ExpiryTime' => $expiry_time);
	$result = $con->insert($stmt_sess,$stmt_sess_values,$stmt_sess_prop);

	if ($result){
		http_response_code(200);
		echo json_encode(array('message' => 'OK', 'session_token' => $sess_token));
		return true; 
	}else{
		http_response_code(200);
		echo json_encode(array('message' => 'Error creating session token.Please try again.')); 
		return false; 
	}
}

?>