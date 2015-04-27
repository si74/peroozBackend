<?php

include('../config.php'); 
include('../model/note.php'); 
include('../model/article.php'); 
include ('../db/mysqldb.php');

/*Database connection*/
$con = new mysqldb($db_settings,false); 


/*Arguments to construct objects*/
$settings = array('article_id' => 1, 'con' => $con);
$settings1 = array('author_id' => 1, 'source_id' => 1, 
	               'source_hyperlink' => 'http://www.huffingtonpost.com/2014',
	               'article_title' => 'Iowa May Elect A Woman To Congress For The First Time',
	               'article_hyperlink' => 'http://www.huffingtonpost.com/2014/06/03/iowa-woman-congress-_n_5441915.html');
$settings2 = array('author_id' => 3, 'source_id' => 2,
				   'source_hyperlink' => 'http://www.nytimes.com/',
				   'article_title' => "BlaBlaCar, a Ride Sharing Service in Europe, Looks to Expand It''s Map",
				   'article_hyperlink' => 'nytimes.com/2014/07/02/business/international/a-service-for-sharing-european-road-trips-looks-to-expand-its-map.html');

/*(1) Test the constructors*/
// $ex = new article();
// $ex1 = new article($settings); 
// $ex2 = new article($settings1);
// $ex3 = new article($settings2);
// $ex4 = new article(array('article_id' => 3));
//$ex5 = new article(array('con' => $con, 'perooz_article_id' => 'pz_95a4fe1e-05fe-11e4-9b35-002590d1d1c4'));
$ex6 = new article(); 

/*(2) Test the setter methods*/
//$ex->article_id = 5; 
//$ex->source_hyperlink = 'http://www.cnn.com';

/*(3) Test the getter method*/
//echo 'ex id: '.$ex->article_id.'<br>';
//echo 'ex hyperlink: '.$ex->source_hyperlink.'<br><br>'; 

/*Test of set_from_db method*/
//echo 'ex1 source: '.$ex1->source_hyperlink.'<br>';
//echo 'ex1 article: '.$ex1->article_hyperlink.'<br><br>';

/*Test of set from constructor*/
//echo 'ex2 source: '.$ex2->source_hyperlink.'<br>';
//echo 'ex2 article: '.$ex2->article_hyperlink.'<br>';
//echo 'ex3 article: '.$ex3->article_hyperlink.'<br>';

/*(4) Test insert into db method*/
//$result = $ex2->insert_db($con);
//echo 'ex2 insert: '.$result.'<br>';

/*(5) Test update into db method*/
/*Functions correctly*/
//$values = array(':AuthorId' => 2, ':ArticleId' => $ex1->article_id); 
//$result = $ex1->update_to_db($values,$con);
//echo 'Was article 1 updated: '.$result;
//

/*(6) Test getting the note list*/
/*Test an array*/
/*$yes = array();
$yes[] = 5; 
$yes[] = 2; 
var_dump($yes);
echo $yes[0]*/

/*(7) Test creation of note group list and note list*/
/*Did the note list get set*/
//$result = $ex1->set_note_list($con); 
//echo 'Did something get printed:'.($result).'<br>'; 
//var_dump($ex1->note_list); 

/*Test note group list*/
//$result = $ex1->set_note_group_list($con);
//echo 'Did something get printed:'.($result).'<br>';
//var_dump($ex1->note_group_list);


/*(8) Test creation of note list and note group list with limit and offset*/
//Check note list
//$result = $ex4->set_note_list($con,5,1);
//echo 'Did the note list get set?: '.$result.'<br/>';
//var_dump($ex4->note_list);
//echo '<br/>';

//Check note group list
//$result = $ex4->set_note_group_list($con); 
//echo 'Did the note group get set?: '.$result.'<br/>';
//var_dump($ex4->note_group_list); 

/*(9) Test to see if object can be set from perooz article id*/
//echo '<br/>'; 
//echo htmlspecialchars($ex5->article_title).'<br/>';  

/*(10) Test search fxn*/
$result = $ex6->search_article(array('url' => 'www.huffingtonpost.com/2014/06/03/iowa-woman-congress-_n_5441915.html'),$con);
var_dump($result); 

?>