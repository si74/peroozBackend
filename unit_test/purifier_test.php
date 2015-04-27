<?php

require_once('../controller/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);

$dirtyHtml = "www.cnn.com";

$cleanHtml = $purifier->purify($dirtyHtml);

echo $cleanHtml;

?>