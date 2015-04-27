<?php

$test = '';
$test = 'aha'; 
echo ($test.'<br/>');
//$test += ', yay'; 
//echo $test; 

/*$i = 3; 
$h = 'aha';
$x = 'fook';
$h = $h.$x; 
echo $h;
*/

$a = '';
$b = '';
$c = '';

$tests = array('a' => 1, 'b' => 2, 'c' => 3); 

foreach($tests as $key => $value){
	$key = $value; 
}

echo "a:".$a; 

?>