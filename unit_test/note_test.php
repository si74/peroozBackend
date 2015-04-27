<?php 

include('../config.php'); 
include('../model/note.php'); 
include ('../db/mysqldb.php');

/*Database connection*/
$con = new mysqldb($db_settings,false); 

/*Arguments to construct objects*/
$settings = array('note_id' => 2, 'article_id' => 1);
$settings2 = array('con' => $con, 'note_id' => 1);
$settings3 = array('article_id' => 1, 'contributor_id' => 1, 'note_type_id' => 1, 'note_text' => 'test yaya', 'sort_order' => 1);
$settings4 = array(':InlineText' => 'this is ridic', ':NoteText' => 'what the heck', ':NoteId' => 1); 

/*(1) Testing of basic constructor that incorporates values--------------------------------------*/
/*Empty constructor*/
$fay_note = new note();

/*Constructor assigning two values*/
$test_note = new note($settings); 

/*Constructor passing in database connection*/
$set_note = new note($settings2);

/*Constructor passing in many values - intended to insert into db*/
$insert_note = new note($settings3);

/*Constructor passing in two values - intended to update into db*/
$up_note = new note($settings4);

/*(2) Testing of set methods--------------------------------------------------------------------*/
$fay_note->note_id = 5; 
$fay_note->note_text = 'yay is this the case';

/*(3)  Testing of get methods--------------------------------------------------------------------*/
echo 'fay-note id: '.$fay_note->note_id.'<br>';
echo 'fay_note text: '.$fay_note->note_text.'<br><br>';

echo 'test_note id: '.$test_note->note_id.'<br>'; 
echo 'test_node article id: '.$test_note->article_id.'<br><br>';

echo 'set_note notetext: '.$set_note->note_text.'<br>';
echo 'set_note inlinetext: '.$set_note->inline_text.'<br>';
echo 'set_note note_type_id: '.$set_note->note_type_id.'<br>';

/*(4) Testing set from database methods---------------------------------------------------------*/
/*Worked via constructor used to create $set_note*/

/*(5) Testing insertion of note into db---------------------------------------------------------*/
/*Functions correctly*/
$result = $insert_note->insert_db($con);
var_dump($con->pdo->errorInfo());
//echo $result; 
var_dump($result);

/*(6) Testing update of note in db--------------------------------------------------------------*/
/*Function correctly*/
//$result = $set_note->update_to_db($settings4,$con); 
//var_dump($result);

?>