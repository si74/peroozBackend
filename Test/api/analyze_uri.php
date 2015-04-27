<?php

/** 
 * Date: 7-18-2014
 * Author: Sneha Inguva
 * Description: Breaks down POST/GET request URI and ensures in correct format
 * Return: array of uri breakdown or false if call to nonexistent URI  
 */

/**Note - returned resource array will be of the following type- 
	$request = array('call_type' => ''
					 'resource1' => ''
				     'resource_id' => ''
				     'resource2' => '',
				     'values' => array());
**/
function analyze_uri($query_string,$query_method){

	$returned_request = false; 
	$max_length = 3; //at most 4 parts to the URI delimited by backslash

	$query_array = explode("/",$query_string);

	if (count($query_array) < $max_length + 1){

		/*If part of search resource, break down and create request array------------------------------*/
		if ($query_array[0] == "search"){

			//search resource must be GET method only 
			if ($query_method == "GET"){

				$query_array1 = explode("?",$query_array[1]);
				$query_array2 = explode("&",$query_array1[1]);

				$returned_request = array('call_type' => 'retrieve',
									  'resource1' => 'search',
									  'resource2' => $query_array1[0]); 
				$values = array(); 
				for ($i = 0; $i < count($query_array2); $i++){
					$ex = explode("=",$query_array2[i]); 
					$values[$ex[0]] = $ex[1]; 
				}
				$returned_request['values'] = $values;
			}
		/*--------------------------------------------------------------------------------------------*/
		}else{
			//if standard resource, break down and classify URI parts 
			
			//if only one resource given
			if (count($query_array) = 1){

				//if no additional id provided, must be a POST method to create
				if ($query_method == "POST"){
					$returned_request = array('call_type' => 'create',
										  	  'resource1' => $query_array[0]);
				}

			//if resource and id provided	
			}else if (count($query_array) = 2){

				//request method indicates if query is retrieve or update
				if ($query_method == "GET"){
					$call_type = 'retrieve';
				}else if($query_method == "POST"){
					$call_type = 'update';
				}

				$returned_request = array('call_type' => $call_type,
										  'resource1' => $query_array[0],
										  'resource_id' => $query_array[1]);

			}else if (count($query_array) = 3){

				if ($query_method == "GET"){

					$query_array1 = explode("?",$query_array[2]);
					$query_array2 = explode("&",$query_array1[1]);

					$returned_request = array('call_type' => 'retrieve',
									  'resource1' => $query_array[0],
									  'resource_id' => $query_array[1],
									  'resource2' => $query_array1[0]);

					$values = array(); 
					for ($i = 0; $i < count($query_array2); $i++){
						$ex = explode("=",$query_array2[i]); 
						$values[$ex[0]] = $ex[1]; 
					}
					$returned_request['values'] = $values; 


				}

			}
		}
		

	}

	return $returned_request; 

}

?>