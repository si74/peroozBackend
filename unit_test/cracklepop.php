<?php

for($i=1 ; $i <= 100; $i++){

	$values = array('Crackle' => false, 'Pop' => false);

	if ($i % 3 == 0){
		$values['Crackle'] = true;
	}

	if ($i % 5 == 0){
		$values['Pop'] = true;
	}

	$to_print = '';
	foreach($values as $key => $value){
		if ($values[$key]){
			$to_print = $to_print.$key;
		}
	}

	if (empty($to_print)){
		echo $i.'<br/>';
	}else{
		echo $to_print.'<br/>';
	}
}

?>