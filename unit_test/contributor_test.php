<?php

include('../config.php'); 
include('../model/contributor.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false); 

// $settings = array('con' => $con, 'perooz_contributor_id' => 'pz_62f9ee38-07d6-11e4-9b35-002590d1d1c4');
// $settings1 = array('con' => $con, 'contributor_id' => 1);
// $settings2 = array('user_id' => 2, 'bio' => 'She is a animal lover.', 'photo' => '', 'profession' => 'cuddler', 'country' => 'France', 'stance' => 'liberal', 'bio_hyperlink' => '');
// $values = array(':Bio' => 'She is a werewolf',':Profession' => 'Wolfpack leader', ':ContributorId' => 3); 

/*Test initialization*/
// $ex = new contributor(); 
// $ex1 = new contributor($settings);
// $ex2 = new contributor($settings1); 
// $ex3 = new contributor($settings2);
$ex4 = new contributor(); 

/*Test setter and getter mthods*/
// $ex->bio = 'yay'; 
// $ex->profession = 'snake wrangler'; 

/*Test get methods*/
// echo 'Bio: '.$ex->bio.'<br/>';
// echo 'Profession: '.$ex->profession.'<br/>'; 

/*Test set from db methods*/
//var_dump($ex1); 
//var_dump($ex2);

/*Test insert db methods*/
//$result = $ex3->insert_db($con);
//var_dump($result); 

/*Test update db methods*/
//$result = $ex->update_to_db($values,$con); 
// echo $result.'<br/>';
// var_dump($result);

/*Test search methods*/
$result = $ex4->search_contributor(array('first_name' => 'ScoutGatsby','last_name' => 'Test'),$con);
var_dump($result);

?>