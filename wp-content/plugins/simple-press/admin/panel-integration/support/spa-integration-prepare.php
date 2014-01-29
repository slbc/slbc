<?php
/*
Simple:Press
Admin integration Update Global Options Support Functions
$LastChangedDate: 2013-09-14 14:49:56 -0700 (Sat, 14 Sep 2013) $
$Rev: 10682 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_integration_page_data() {
	global $wp_rewrite;

	$sfoptions = array();
	$sfoptions['sfslug'] = sp_get_option('sfslug');
	$sfoptions['sfpage'] = sp_get_option('sfpage');
	$sfoptions['sfpermalink'] = sp_get_option('sfpermalink');

	$sfoptions['sfuseob'] = sp_get_option('sfuseob');
	$sfoptions['sfwplistpages'] = sp_get_option('sfwplistpages');
	$sfoptions['sfscriptfoot'] = sp_get_option('sfscriptfoot');

	$sfoptions['sfinloop'] = sp_get_option('sfinloop');
	$sfoptions['sfmultiplecontent'] = sp_get_option('sfmultiplecontent');
	$sfoptions['sfwpheadbypass'] = sp_get_option('sfwpheadbypass');

	return $sfoptions;
}

function spa_get_storage_data() {
	$sfstorage = array();
	$sfstorage = sp_get_option('sfconfig');
	return $sfstorage;
}

?>