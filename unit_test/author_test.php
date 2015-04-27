<?php

include('../config.php'); 
include('../model/author.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false); 

/*Settings*/
// $settings = array('con' => $con, 'perooz_author_id' => 'a082e065-06bf-11e4-9b35-002590d1d1c4');
// $settings1 = array('con' => $con, 'author_id' => 3);
// $settings2 = array('con' => $con, 'author_first_name' => 'Shaniqua',
// 				   'author_last_name' => 'Gatsby');

/*Initialize object*/
//$ex = new author(); 
// $ex1 = new author($settings); 
// $ex2 = new author($settings1); 
// $ex3 = new author($settings2);
$ex4 = new author(); 

/*Test setter and getter methods*/
//$ex->author_first_name = 'Yay'; 
//$ex->author_last_name = 'TestCrazy'; 
//echo 'Did set and get methods work: AuthorFirstName- '.$ex->author_first_name.'<br/>'; 
//echo 'AuthorLastName-'.$ex->author_last_name.'<br/>';

/*Set from db using perooz author id*/
// echo 'Author Name: '.$ex1->author_first_name.'<br/>';
// echo 'Author Lst Name: '.$ex1->author_last_name.'<br/>';
// echo 'Author FName: '.$ex2->author_first_name.'<br/>';

/*Insert db*/
//$result = $ex3->insert_db($con); 
//var_dump($result);

/*Test Update Db*/
// $values = array(':AuthorFirstName' => 'Shaniquanatest',':PeroozAuthorId' => 'a082e065-06bf-11e4-9b35-002590d1d1c4'); 
// $result = $ex1->update_to_db($values,$con);
// var_dump($result);

/*Test the search function*/
$result = $ex4->search_author(array('first_name' => 'Samantha','last_name' => 'Lachman'),$con);
var_dump($result);

?> 
