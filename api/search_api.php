<?php

function search_api($request,$call_type,$con){

	$result = false;
	
	$resource_class = substr($request['resource'],0,-1);

	$obj = new $resource_class(array('api' => true)); 

	$fxn = "search_".$resource_class;

	$result = $obj->$fxn($request['query_params'],$con); 

	return $result; 

}


?>