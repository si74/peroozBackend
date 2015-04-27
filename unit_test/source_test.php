<?php

include('../config.php'); 
include('../model/source.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false);

/*Create relevant settings*/
// $settings = array('con' => $con, 'source_id' => 1);
// $settings1 = array('con' => $con, 'perooz_source_id' => 'pz_d423a842-05fc-11e4-9b35-002590d1d1c4'); 
// $settings2 = array('source_name' => 'Time','source_site' => 'http://www.time.com/','source_type_id' => 1); 
// $values =  array(':SourceName' => 'Reuters', ':SourceSite' => 'http://www.reuters.com/',':SourceTypeId' => 1,':SourceId' => 1);
// $values2 = array(':SourceName' => 'CNN', ':SourceSite' => 'http://www.cnn.com/',':SourceTypeId' => 1,':PeroozSourceId' => 'pz_81daf0b2-0956-11e4-8db0-002590d1d1c4');

/*Initialize objects*/
// $ex = new source();
// $ex1 = new source($settings);
// $ex2 = new source($settings1);
// $ex3 = new source($settings2);
$ex4 = new source(); 

/*Test setter and getter methods*/
// $ex->source_name = 'Twinky'; 

// echo 'SourceName Getter: '.$ex->source_name.'<br/><br/>';

/*Test set from db methods*/

// echo 'SourceName 2: '.$ex1->source_name.'<br/><br/>';

// echo 'SourceName 3: '.$ex2->source_site.'<br/><br/>';

/*Test insert into db*/
//$result = $ex3->insert_db($con); 
//var_dump($result); 

/*Test update to db*/
//$result = $ex->update_to_db($values,$con); 
//var_dump($result);

// $result = $ex->update_to_db($values2,$con);
// var_dump($result); 

$result = $ex4->search_source(array('url' => 'www.reuters.com/'),$con);
var_dump($result);

?>