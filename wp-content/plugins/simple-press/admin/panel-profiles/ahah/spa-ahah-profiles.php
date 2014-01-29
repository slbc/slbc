<?php
/*
Simple:Press
profiles Specials
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

# Check Whether User Can Manage Profiles
if (!sp_current_user_can('SPF Manage Profiles')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

global $spPaths;

$action = $_GET['action'];

if ($action == 'delavatar') {
	$file = $_GET['file'];
	$path = SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/'.$file;
	@unlink($path);
	echo '1';
}

if ($action == 'delete-tab') {
	$slug = sp_esc_str($_GET['slug']);
	sp_profile_delete_tab_by_slug($slug);
}

die();

?>