<?php

include('../api/analyze_uri.php');

$query_string = "request=search/article/jojo?url=50";
$query_method = "GET";

echo $_SERVER['REQUEST_URI'];

//var_dump(analyze_uri($query_string,$query_method) );

?>