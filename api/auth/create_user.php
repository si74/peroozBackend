<?php

require_once('../../config.php'); 
require_once('../../db/mysqldb.php');
require_once('../../controller/lgn/PasswordHash.php');

$client_id = '';
$request_method = $_SERVER['REQUEST_METHOD']; 

/*(1) Go through relevant headers to get the request header and client id*/
$headers = getallheaders(); 
if ($headers){
	foreach($headers as $header => $value){
		if ($header == 'Client-Id'){
			$client_id = $value; 
		}
	}
}

/*(2) User creation function is invoked*/
create_user($client_id,$request_method,$db_settings);

function create_user($client_id,$request_method,$db_settings){

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

	/*Create Pwd Hash Item*/
	$hasher = new PasswordHash(8,false); 

	/*set variable values*/
	$fname = $_POST['fname']; 
	$lname = $_POST['lname'];
	$email = $_POST['email']; 
	$username = $_POST['username'];
	$password = $_POST['password'];
	$password_crypt = $hasher->HashPassword($password);
	
	/*Obtain user ip address*/
	$ip = ip2long($_SERVER["REMOTE_ADDR"]);

	/*Prepare necessary pdo statement*/
	$stmt_uuid = "Select uuid()";
	
	$stmt_insert_user = "Insert Into Users (PeroozUserId,UserTypeId,UserPaymentTypeId,FirstName,LastName,UserIP,Email) Values (:PeroozUserId,:UserTypeId,:UserPaymentTypeId,:FirstName,:LastName,:UserIP,:Email)";
	$user_param = array(":PeroozUserId" => PDO::PARAM_STR, 
						":UserTypeId" => PDO::PARAM_INT,
						":UserPaymentTypeId" => PDO::PARAM_INT, 
						":FirstName" => PDO::PARAM_STR, 
						":LastName" => PDO::PARAM_STR, 
						":UserIP" => PDO::PARAM_INT,
						":Email" => PDO::PARAM_STR);

	$stmt_insert_login = "Insert Into UserLogins (UserId,Username,Password,IsLocked) Values (:UserId,:Username,:Password,:IsLocked)";
	$login_param = array(":UserId" => PDO::PARAM_INT, 
						 ":Username" => PDO::PARAM_STR, 
						 ":Password" => PDO::PARAM_STR, 
						 ":IsLocked" => PDO::PARAM_BOOL); 

	/*First obtain the perooz user id*/
	$result_uuid = $con->query('Select uuid()')->fetch();
	$uuid = $result_uuid["uuid()"];
	
	/*Add to user table and obtain relevant user id*/
	$user_values = array(":PeroozUserId" => $uuid, 
						 ":UserTypeId" => 1, 
						 ":UserPaymentTypeId" => 1, 
						 ":FirstName" => $fname, 
						 ":LastName" => $lname, 
						 ":UserIP" => $ip,
						 ":Email" => $email);
	$userid = $con->insert($stmt_insert_user,$user_values,$user_param,false,'User');

	if (!$userid){
		http_response_code(500);
		echo json_encode(array("message" => "Unable to add user. Please try again."));
		return false;
	}

	/*Add to user login table*/
	$login_values = array(":UserId" => $userid,
						  ":Username" => $username,
						  ":Password" => $password_crypt,
						  ":IsLocked" => 0);
	$result = $con->insert($stmt_insert_login,$login_values,$login_param,false,'User'); 

	if ($result){
		http_response_code(200);
		echo json_encode(array("message" => 'OK'));
		return true;
	}else{
		http_response_code(500);
		echo json_encode(array("message" => "Unable to add user. Please try again."));
		return false;
	}

}

?>