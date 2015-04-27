<?php

include('../config.php'); 
include('../model/notegroup.php'); 
include ('../db/mysqldb.php');

/*Set up database connection*/
$con = new mysqldb($db_settings,false); 

/*Set the necessary settings*/
$settings = array('con' => $con, 'note_group_id' => 1);
$settings1 = array('con' => $con, 'perooz_note_group_id' => 'pz_61099d39-060c-11e4-9b35-002590d1d1c4');
$settings2 = array('article_id' => 3, 'note_text_overlap' => 'Yay this is the case.'); 
$values = array(':NoteGroupId' => 2, ':NoteTextOverlap' => 'Shaniqua stalked Gatsby.'); 
$values2 = array(':PeroozNoteGroupId' => 'pz_61099d39-060c-11e4-9b35-002590d1d1c4', ':NoteTextOverlap' => 'Gatsby was unhappy.');

/*Initialize the notegroup object*/
$ex = new notegroup(); 
$ex1 = new notegroup($settings); 
$ex2 = new notegroup($settings1); 
$ex3 = new notegroup($settings2); 

/*Test setter and getter methods*/
$ex->note_text_overlap = 'Poop'; 
$ex->article_id = 3; 

echo 'Overlap method: '.$ex->note_text_overlap.'<br/>'; 
echo 'Article Id: '.$ex->article_id.'<br/><br/>'; 

/*Test set from db methods*/
/*Test setting from NoteId*/
echo 'Check if set: PeroozNoteGroupId - '.$ex1->perooz_note_group_id.'<br/>'; 
echo 'Check if set: Text Overlap - '.$ex1->note_text_overlap.'<br/><br/>'; 

echo 'Check if pz set: NoteGroupId - '.$ex2->note_group_id.'<br/>';
echo 'Check if pz set: Text Overlap - '.$ex2->note_text_overlap.'<br/><br/>'; 

/*Test insert db method*/
//$result = $ex3->insert_db($con); 
//var_dump($result); 

/*Test update db methods*/
$result = $ex->update_to_db($values2,$con); 
var_dump($result);



?>