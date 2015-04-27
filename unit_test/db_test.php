<?php

include('../config.php'); 
include('../model/note.php'); 
include ('../db/mysqldb.php');

/*(1) General db Connection Test*/
//$stuff = 'mysql:host='.$server.(isset($port)?';port='.$port:'').';dbname='.$database_name.';charset='.$charset;
//$con = new PDO($stuff, $db_username, $db_pwd);

/*(2) Test General Connection*/
//$select_stmt = ('Select ArticleId,ContributorId,NoteTypeId,InlineText,NoteText,NoteGroupId,SortOrder From Notes Where NoteId=:NoteId');
//$con = new mysqldb($db_settings,false); 

/*(3) Test single value query function*/
// $stmt = ('Select UserTypeText From UserTypes Where UserTypeId=:UserTypeId');
// $user_id = 1; 
// $values = array(':UserTypeId' => $user_id);
// $value_options = array(':UserTypeId' => PDO::PARAM_INT);
// $columnName = 'UserTypeText';

// $result = $con->single_query($stmt,$values,$value_options,$columnName); 

// echo "Single query Result: $result <br/>";
 
/*(4)Test multi row query function*/
/*$stmt2 = ('Select UserTypeId,UserTypeText From UserTypes Where UserTypeId IN (:UserTypeId1)');
$user_id1 = 1; 
$user_id2 = 2; 
$values = array(':UserTypeId1' => $user_id1);
$value_options = array(':UserTypeId1' => PDO::PARAM_INT);
$results2 = $con->multi_query($stmt2,$values,$value_options);
print_r($results2)
*//*if (isset($results2)){
	$i = 1; 
	echo "Multi Query Result: <br/>";
	foreach($results2 as $row){
		echo $i.': '.$row['UserTypeId'].' '.$row['UserTypeText']."<br/>"; 
		$i++;
	}
}*/

/*Test error in multi row query function*/
// $stmt_etest = ('Select NoteText From Notes Where NoteId=:NoteId');
// $yo = $con->prepare($stmt_etest);
// $val = 1;
// $yo->bindParam(':NoteId', $val, PDO::PARAM_INT); 
// $yo->execute();
// $result = $yo->fetchAll();

// print_r($result);

// if (empty($result)){
// 	echo 'yay';
// }

// if (!isset($result)){
// 	echo 'yay';
// }

/*(5)Test Insert Function*/
//$stmt3 = ('Insert Into UserTypes (UserTypeText) Values (:test1)');
//$test1 = 'test1'; 
//$values = array (':test1' => $test1); 
//$value_options = array(':test1' => PDO::PARAM_INT); 
//$results3 = $con->insert($stmt3,$values,$value_options);
//echo "Insert Result [last insert id]: ".$results3."<br/>"; 

/*(6)Test Update Function- STILL MESSED UP FXN - MUST FIX*/
/*$stmt4 = ('Update UserTypes Set UserTypeText=:test3 Where UserTypeId=:userid3');
$test3= 'testmutha';
$userid3 = 5; 
$values = array (':test3' => $test3, ':userid3' => $userid3);
$value_options = array(':test3' => PDO::PARAM_INT, ':userid3' => PDO::PARAM_INT);
$results4 = $con->update($stmt4,$values,$value_options);
if ($results4){
	$printthingy="Yes!";
}else{
	$printthingy="false"; 
}
echo "Update Result: ".$printthingy."<br/>";
*/
/*Not working so will test rowCount() normally with mysql*/
/*$db = new mysqli($server, $db_username, $db_pwd, $database_name); 
if ($db->connect_error) {
  trigger_error('Database connection failed: '  . $db->connect_error, E_USER_ERROR);
}
try{
	$result = $db->query("Update UserTypes Set UserTypeText=\'tesyo\' Where UserTypeId=4");
}catch(Exception $e){
	echo $e->getMessage(); 
}
if (!$result){
	echo "Fuck"; 
}
$fixed = $result->num_rows; 
echo "This is working: $fixed";*/

/*(7)Test Class Related Select Function- STILL REMAINING MUST DO [5/30/2014]*/

/*(8)Test general query function*/
// $result = $con->query('Select uuid()')->fetch();
// $uuid = $result['uuid()']; 
// var_dump($uuid);
//foreach ($result as $row){
//	var_dump($row);
//}

/*(9) Test delete function*/
$con = new mysqldb($db_settings1,false);
$stmt = "Delete FROM UserSessions Where UserSessionToken=:UserSessionToken";
$values = array(":UserSessionToken" => "5c32243000e6e5c6165a80da4353ba6e");
$value_prop = array(":UserSessionToken" => PDO::PARAM_STR);
$result = $con->delete($stmt,$values,$value_prop);
var_dump($result); 

?>