<?php

/** 
 * Date: 7-18-2014
 * Author: Sneha Inguva
 * Description: Breaks down POST/GET request URI and ensures in correct format
 * Return: array of uri breakdown or false if call to nonexistent URI  
 */

/**Note - returned resource array will be of the following type- 
	$request = array('search_type' => ''
					 'resource' => ''
				     'resource_id' => ''
				     'subresource' => '',
				     'query_params' => array());
**/

function analyze_uri($query_string,$query_method){

	$uri_breakdown = false; 

	$max_length = 3; //at most 4 parts to the URI delimited by backslash

	$query_string = str_replace("/api/","",$query_string);
	
	$resource_type = strpos($query_string,"/") ? substr($query_string,0, strpos($query_string,"/")) : $query_string; //break down query string into components

	/*Two types of main queries can exist - search or general*/
	if ($resource_type == "search"){

		/*search can only have get query method*/
		if ($query_method != "GET"){
			return $uri_breakdown; 
		}

		//parse through remainder of search string and extract relevant info
		$search_string = str_replace("search/","",$query_string); 

		if (empty($search_string)){
			return $uri_breakdown; 
		}

		$resource = substr($search_string,0,strpos($search_string,"?"));

		$param_string = str_replace($resource."?","",$search_string); 
		
		if (empty($param_string)){
			return $uri_breakdown;
		}
		
		parse_str($param_string, $query_param);

		foreach($query_param as $param => $value){
			if ($param == 'url'){
				$decoded_url = urldecode($value);
				$query_param['url'] = $decoded_url;
			}
		}

		$uri_breakdown = array('search_type' => true,
							   'resource' => $resource, 
							   'query_params' => $query_param);

	}else{

		$uri_breakdown = array('search_type' => false);
		
		if (!strpos($query_string,"/")){
			$uri_breakdown['resource'] = $query_string;
			return $uri_breakdown;
		} 
		
		$resource = substr($query_string,0,strpos($query_string,"/"));

		$uri_breakdown['resource'] = $resource; 

		$resourceid_string = str_replace(($resource."/"), "", $query_string);

		if (!strpos($resourceid_string,"/")){
			$uri_breakdown['resource_id'] = $resourceid_string;
			return $uri_breakdown;
		}

		$resource_id = substr($resourceid_string,0,strpos($resourceid_string,"/"));
		
		$uri_breakdown['resource_id'] = $resource_id;

		$subresource_string = str_replace(($resource_id."/"),"",$resourceid_string);

		if (!strpos($subresource_string,"?")){
			$uri_breakdown['subresource'] = $subresource_string;
			return $uri_breakdown;
		}

		$subresource = substr($subresource_string,0,strpos($subresource_string,"?"));

		$uri_breakdown['subresource'] = $subresource; 

		$param_string = str_replace(($subresource."?"),"",$subresource_string);

		if (empty($param_string)){
			return $uri_breakdown;
		}

		parse_str($param_string,$query_param);

		$uri_breakdown['query_params'] = $query_param;

	}

	return $uri_breakdown;
}

?>