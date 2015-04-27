<?php

include('../config.php'); 
include('../model/note.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false); 

/*Settings*/
$settings = array('con' => $con, 'note_id' => 4);
$settings1 = array('con' => $con, 'perooz_note_id' => 'pz_0bdcc3fd-060c-11e4-9b35-002590d1d1c4');
$settings2 = array('inline_text' => 'For many Europeans, the likes of Twitter and Amazon hold too much information about what people do online.',
				   'article_id' => 3,
				   'contributor_id' => 2,
				   'note_type_id' => 2,
				   'note_text' => 'Not only here but people in East Asia feel the same.',
				   'sort_order' => 3); 
$values = array(':NoteText' => 'This is evaluationally true.', ':NoteId' => 1); 
$values2 = array(':NoteText' => 'This is freakilicious.' , ':PeroozNoteId' => 'pz_4057cf54-060c-11e4-9b35-002590d1d1c4');

/*Example*/
$ex = new note($settings); 
//$ex1 = new note($settings1); 
//$ex2 = new note($settings2);
//$ex3 = new note();

/*Test setter and getter methods*/
//$ex->inline_text = 'asdf'; 
//$ex->note_text = 'asdfdasdfasd'; 

//echo 'Inline Text: '.$ex->inline_text; 
//cho 'Note Text: '.$ex->note_text; 

/*Test set from db methods*/
//var_dump($ex); 
//var_dump($ex1);

/*Insert into database*/
//$result = $ex2->insert_db($con); 
//echo $result; 
//var_dump($result); 
//var_dump($ex2); 

/*Check update to database*/
//$result = $ex->update_to_db($values,$con);
//var_dump($result); 

//$result = $ex->update_to_db($values2,$con); 
//var_dump($result); 



?>