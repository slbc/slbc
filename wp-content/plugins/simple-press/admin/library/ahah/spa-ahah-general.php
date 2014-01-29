<?php
/*
Simple:Press
Admin General Ahah file
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

if (isset($_GET['action']) && $_GET['action'] == 'news') {
	$news = sp_get_sfmeta('news', 'news');
	if (!empty($news)) {
		$news[0]['meta_value']['show'] = 0;
		sp_update_sfmeta('news', 'news', $news[0]['meta_value'], $news[0]['meta_id'], 0);
	}
}

die();

?>