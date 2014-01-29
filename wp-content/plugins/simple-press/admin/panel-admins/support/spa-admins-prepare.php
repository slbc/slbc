<?php
/*
Simple:Press
Admin Admins Update Global Options Support Functions
$LastChangedDate: 2013-08-11 12:03:54 -0700 (Sun, 11 Aug 2013) $
$Rev: 10506 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_admins_your_options_data() {
	global $spThisUser;
	$sfadminoptions = sp_get_member_item($spThisUser->ID, 'admin_options');
	$sfadminsettings['setmods'] = false;
	return $sfadminoptions;
}

function spa_get_admins_global_options_data() {
	$sfadminsettings = sp_get_option('sfadminsettings');
	return $sfadminsettings;
}

?>