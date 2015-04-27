<?php

class api{

	function __construct(){

	}

	function __destruct(){
		
	}

	/*Create setter and getter functions--------------------------------------------------*/
	/*Get method*/
	public function __get($name){
		return $this->$name;
	}

	/*Set method*/
	public function __set($name, $value){
		$this->$name = $value;
	}
	/*------------------------------------------------------------------------------------*/

}

?>