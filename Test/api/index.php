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

include('../model/article.php');
include('../model/author.php'); 
include('../model/contributor.php');
include('../model/email.php');
include('../model/note.php');
include('../model/notegroup.php'); 
include('../model/source.php');
include('../model/user.php');

/*Include the oauth php library*/
require_once('../oauth2-server-php/src/OAuth2/Autoloader.php');
Ouath2\Autoloader::register(); 

/*Important headers*/
header("Access-Control-Allow-Origin: *");

/*Testing string*/
echo 'Hello! Redirected correctly.'; 

$query_string = $_SERVER['QUERY_STRING'];
$query_method = $_SERVER['REQUEST_METHOD'];

/*If post method obtain relevant variables*/
if ($query_method == "POST"){

}

/**If returned, will be of the following format: 
$request = array('call_type' => ''
					 'resource1' => ''
				     'resource_id' => ''
				     'resource2' => '',
				     'values' => array());
**/
$request_type = ''; 

/*RELEVANT RULES FOR API------------------------------------------------------------------------------*/
/*(a) allowed resources & subresources in the api*/
$possible_resource = array('articles','notes','notegroups','sources','contributors','authors','users','search'); 
$possible_subresource = array('articles' => array('notelist','notegrouplist'));
/*(b) allowed methods in the api*/
$possible_method = array ('GET','POST'); 

/*(c) associative array - call types and allowed methods*/
$possible_call_method = array('create' => 'POST', 'update' => 'POST', 'retrieve' => 'GET'); 

/*---------------------------------------------------------------------------------------------------*/

//response codes
$response_code = array()

/*(4) First check that user authentication provided is legitimate*/

/*(2) Check that api key is valid*/

/*(3) Ensure that request methods are of the valid type*/
if (in_array($query_method,$possible_method)){

	//analyze resource uri
	$request_type = analyze_uri($query_string,$query_method);

	//if resource uri is correctly formatted and broken down, check to ensure that correct resources are being accessed
	if ($request_type){

		//if resource exists make api call
		if (in_array($request_type['resource1'],$possible_resource)){

			//if there is a subresource possible for resource
			if (array_key_exists($request['resource1'],$possible_subresource)){

					//check if subresource exists
					$resource = $request['resource1']; 
					$subresources = $possible_subresources[$resource]; 
					if (array_key_exists($request['resource2'], $subresources)){

						$api = new api($request_type,$values); 

					}

			}


		} 

	}
}



?>