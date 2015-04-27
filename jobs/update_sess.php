<?php

/**
 * Chronjob that will delete expired user session tokens every hour
 * Author: Sneha Inguva
 * Date: 8-2-2014
 */

require_once('../config.php'); 
require_once('../db/mysqldb.php');

$con = new mysqldb($db_settings1,false);

$stmt = "Delete FROM UserSessions Where ExpiryTime <= CURRENT_TIMESTAMP"; 

$result = $con->query($stmt);

?>