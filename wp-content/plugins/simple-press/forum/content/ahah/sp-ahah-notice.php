<?php
/*
Simple:Press
Remove a user notice in demand
$LastChangedDate: 2012-11-19 15:00:20 -0700 (Mon, 19 Nov 2012) $
$Rev: 9330 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_api_support();

if (isset($_GET['notice'])) {
	$id = (int) $_GET['notice'];
	if ($id) spdb_query('DELETE FROM '.SFNOTICES." WHERE notice_id=$id");
}

die();
?>