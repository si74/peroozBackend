<?php

/***

Author: Sneha Inguva
Date: 7-16-2014
Description: Controller for Web Api for Perooz App

***/

/*Necessary include files*/
include('../config.php');
include ('../db/mysqldb.php');

include ('api.php');
include('analyze_uri.php');
include('search_api.php');
include('resource_api.php');

include('../model/article.php');
include('../model/author.php'); 
include('../model/contributor.php');
include('../model/email.php');
include('../model/note.php');
include('../model/notegroup.php'); 
include('../model/source.php');
include('../model/user.php');

require_once('../controller/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php');

/*Include the oauth php library*/
//require_once('../oauth2-server-php/src/OAuth2/Autoloader.php');
//Ouath2\Autoloader::register(); 

/*Important headers*/
//header("Access-Control-Allow-Origin: *");

$query_string = $_SERVER['REQUEST_URI'];
$query_method = $_SERVER['REQUEST_METHOD'];

$client_id = ''; 
$sess_token = ''; 

$headers = getallheaders(); 
if ($headers){
	foreach($headers as $header => $value){
		if ($header == 'Client-Id'){
			$client_id = $value; 
		}else if($header == "Session-Token"){
			$sess_token = $value; 
		}
	}
}

/**If returned, will be of the following format: 
$request = array('call_type' => ''
					 'resource' => ''
				     'resource_id' => ''
				     'subresource => '',
				     'values' => array());
**/

/*RELEVANT RULES FOR API------------------------------------------------------------------------------*/
/*(a) allowed resources & subresources in the api*/
/*allowed post variables for create and retrieve */
$possible_resource = array('articles','notes','notegroups','sources','contributors','authors'); 
$possible_subresource = array('articles' => array('note_lists','notegroup_lists'),
							  'notegroups' => array('note_lists'));
$possible_subresource_param = array('note_lists' => array('max','start'),
								    'notegroup_lists' => array('max','start'));

$search_resource = array('articles','sources','contributors','authors');
$search_resource_param = array('articles' => array('url','title'), //note url should exclude the http protocal and slashes [i.e. no http:// or http - gives errors for mod_security]
 							   'sources' => array('url'),
 							   'contributors' => array('first_name','last_name'),
 							   'authors' => array('first_name','last_name'));

$search_num_param = array('articles' => 1,
						  'sources' => 1,
						  'contributors' => 2,
						  'authors' => 2);

$temp_db_resource = array('articles','sources','authors','notes');

$temp_db_method = array("POST");

$temp_db_crud = array("create","update");

/*(b) allowed methods in the api*/
$possible_method = array ('GET','POST'); 

/*main code------------------------------------------*/
$response = array(); //main response request 
$response_code; //http response code; 

$good = redirect_request(); //run main request 

/*Send/finalize results to end user*/
http_response_code($response_code);
echo json_encode($response); //echo overall response to end user 

/*RELEVANT FUNCTIONS---------------------------------------------------------------------------------------------------*/
/*check that user authentication provided is legitimate*/
function user_authenticate($con,$sess_token){
	$valid = false; 

	$stmt = "Select UserSessionId,ExpiryTime From UserSessions Where UserSessionToken=:UserSessionToken";
	$stmt_param = array(":UserSessionToken" => PDO::PARAM_STR);
	$stmt_values = array(":UserSessionToken" => md5($sess_token));
	$result = $con->multi_query($stmt,$stmt_values,$stmt_param);
	
	if (!$result){
		return $valid;
	}
	
	if (time() <= strtotime($result[0]['ExpiryTime'])){
		$valid = true; 
	}

	return $valid; 
}

/*check if api resource call is create,update,or retrieve*/
function get_crud($request,$method){

	$call_type = false;

	if (array_key_exists('resource_id',$request)){
		if ($method == 'POST'){
			$call_type = 'update'; 
		}else{
			$call_type = 'retrieve';
		}
		return $call_type;
	}else{
		$call_type = 'create';
		return $call_type; 
	}

	return $call_type;
}

/*obtain relevant response - default value false - from api*/
function redirect_request(){

	$result = false; 

	global $client_id,$sess_token,$query_method,$query_string;
	global $possible_method,$possible_resource,$possible_subresource,$possible_subresource_param;
	global $search_resource,$search_num_param,$search_resource_param; 
	global $temp_db_method,$temp_db_crud,$temp_db_resource;
	global $db_settings,$predb_settings; 

	global $response,$response_code; 

	/*Check api key is valid*/
	if ($client_id != '13adfewasdf432dae'){
		$response_code = 401; 
		$response['message'] = 'Invalid client id.';
		return $result; 
	}

	/*Is there is no session token present*/
	if (!$sess_token){
		$response_code = 401;
		$response['message'] = 'Session token expired. Please login again.';
		return $result; 
	}

	/*Create database connection for main database*/
	$con = new mysqldb($db_settings,false);

	/*Ensure session token is valid*/
	if (!user_authenticate($con,$sess_token)){
		$response_code = 401;
		$response['message'] = 'Session token expired. Please login again.';
		return $result; 
	}

	/*If query is possible method*/
	if (!in_array($query_method,$possible_method)){
		$response_code = 400;
		$response['message'] = 'Invalid query method. Only POST and GET methods permitted.';
		return $result;
	}

	/*Analyze resource uri*/
	$request = analyze_uri($query_string,$query_method);

	/*If resource is a legitimate uri endpoint*/
	if (!$request){	
		$response_code = 400;
		$response['message'] = 'Invalid URI endpoint.';
		return $result; 
	}


	/*Check if uri request is valid*/
	if ($request['search_type']){

		/*Is resource request valid*/
		if (!in_array($request['resource'],$search_resource)){
			$response_code = 400;
			$response['message'] = 'Invalid search resource.';
			return $result; 
		}

		/*Are resource search parameters valid*/
		$ex_resource = $request['resource'];
		$ex_params = $request['query_params'];

		/*Are sufficient parameters provided*/
		if (count($ex_params) != $search_num_param[$ex_resource] ){
			$response_code = 400;
			$response['message'] = 'Insufficient search parameters.';
			return $result;
		}

		/*Are parameters valid*/
		foreach ($ex_params as $param => $value){
			if (!in_array($param,$search_resource_param[$ex_resource])){
				$response_code = 400;
				$response['message'] = 'Invalid search parameters.';
				return $result;
			}
		}

		$call_type = 'retrieve';


		/*Complete the search request and return result(s)*/
		$result = search_api($request,$call_type,$con);

		if ($result){
			$response_code = 200; 
			$response['message'] = 'OK';
			$response['values'] = $result; 
		}else{
			$response_code = 200; 
			$response['message'] = 'None';
		}

	}else{

		/*Check if resource request is valid*/
		if (!in_array($request['resource'],$possible_resource)){
			$response_code = 400;
			$response['message'] = 'Invalid search request.';
			return $result; 
		}

		/*Check if resource_id is not present, it is a post (create) request */
		if (!array_key_exists('resource_id',$request)){
			if ($query_method != 'POST'){
				$response_code = 400;
				$response['message'] = 'Resource identifier missing.';
				return $result;
			}
		}

		/*If subresource present*/
		if (array_key_exists('subresource',$request)){

			/*Should only be get request*/
			if ($query_method != 'GET'){
				$response_code = 400;
				$response['message'] = 'Invalid subresource query method.';
				return $result; 
			}

			$ex_resource = $request['resource'];

			/*Should be valid subresource*/
			if (!in_array($request['subresource'],$possible_subresource[$ex_resource])){
				$response_code = 400;
				$response['message'] = 'Invalid subresource.';
				return $result; 
			} 

			/*If query parameters present, should be valid*/
			if (array_key_exists('query_params',$request)){
				$ex_subresource = $request['subresource']; 
				$ex_params = $request['query_params']; 
				foreach($ex_params as $param => $value){
					if (!in_array($param,$possible_subresource_param[$ex_subresource])){
						$response_code = 400;
						$response['message'] = 'Invalid subresource parameters.';
						return $result; 
					}
				}
			}

		}

		/*Analyze which CRUD operation for the resource*/
		$call_type = get_crud($request,$query_method);

		/*If call type cannot be determined*/
		if (!$call_type){
			$response_code = 400;
			$response['message'] = 'Invalid resource call.';
			return $result;
		}

		/*If create or update function, grab post parameters*/
		$vals = array();
		if ($call_type == 'update' || $call_type == 'create'){

			/*Initialize HTML purifier to sanitize the inputs*/
			$pur_config = HTMLPurifier_Config::createDefault();
			$purifier = new HTMLPurifier($pur_config);
			
			foreach($_POST as $key => $val){
				$vals[$key] = $purifier->purify($val);
			}
		}

		/*If creating or updating note,article,source,or article, added to temp db*/
		//NOTE - for now, everything is to be added to the main database 
		// if (in_array($request['resource'],$temp_db_resource) && in_array($call_type,$temp_db_crud) && in_array($query_method,$temp_db_method)){
		// 	$con = new mysqldb($predb_settings,false);
		// } 

		/*Complete the resource request and return result(s)*/
		if (!empty($vals)){
			$result = resource_api($request,$call_type,$con,$vals);
		}else{
			$result = resource_api($request,$call_type,$con);
		}
		
		if ($result){
			$response_code = 200; 
			$response['message'] = 'OK';
			$response['values'] = $result;
		}else{
			if (!isset($response_code)){
				$response_code = 400; 
				$response['message'] = 'Bad api request.';
			}
		}

	}

	return $result;

}

?>