<?php
/*
Simple:Press Permissions Admin
Ahah form loader - Permissions
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

global $spStatus;
if ($spStatus != 'ok') {
	echo $spStatus;
	die();
}

include_once(SF_PLUGIN_DIR.'/admin/panel-permissions/spa-permissions-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-permissions/support/spa-permissions-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-permissions/support/spa-permissions-save.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

global $adminhelpfile;
$adminhelpfile = 'admin-permissions';

# ----------------------------------
# Check Whether User Can Manage Forums
if (!sp_current_user_can('SPF Manage Permissions')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

if (isset($_GET['loadform'])) {
	spa_render_permissions_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'addperm') 	{
		echo spa_save_permissions_new_role();
		die();
	}
	if ($_GET['saveform'] == 'editperm') {
		echo spa_save_permissions_edit_role();
		die();
	}
	if ($_GET['saveform'] == 'delperm') {
		echo spa_save_permissions_delete_role();
		die();
	}
	if ($_GET['saveform'] == 'resetperms') {
		echo spa_save_permissions_reset();
		die();
	}
	if ($_GET['saveform'] == 'newauth') {
		echo spa_save_permissions_new_auth();
		die();
	}
}

die();

?>