<?php

/**
 * Chronjob that will delete expired nonce tokens every hour
 * Author: Sneha Inguva
 * Date: 8-2-2014
 */

require_once('../config.php'); 
require_once('../db/mysqldb.php');

$con = new mysqldb($db_settings1,false);

$stmt = "Delete FROM nonce_values Where expiry_time <= CURRENT_TIMESTAMP"; 

$result = $con->query($stmt);

?>