<?php
/*
Simple:Press Admin
Ahah form loader - Admins
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

include_once(SF_PLUGIN_DIR.'/admin/panel-admins/spa-admins-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-admins/support/spa-admins-save.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

global $adminhelpfile;
$adminhelpfile = 'admin-admins';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
global $spThisUser;
$modchk = ($spThisUser->admin || $spThisUser->moderator) && ((isset($_GET['saveform']) && $_GET['saveform'] == 'youradmin') || (isset($_GET['loadform']) && $_GET['loadform'] == 'youradmin'));
if (!sp_current_user_can('SPF Manage Admins') && !$modchk) {
	spa_etext('Access denied - you do not have permission');
	die();
}

if (isset($_GET['loadform'])) {
	spa_render_admins_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'youradmin') {
		echo spa_save_admins_your_options_data();
		die();
	}
	if ($_GET['saveform'] == 'globaladmin') {
		echo spa_save_admins_global_options_data();
		die();
	}
	if ($_GET['saveform'] == 'manageadmin') {
		echo spa_save_admins_caps_data();
		die();
	}
	if ($_GET['saveform'] == 'addadmin') {
		echo spa_save_admins_newadmin_data();
		die();
	}
	if ($_GET['saveform'] == 'colorrestore') {
		echo spa_save_admins_restore_color();
		die();
	}
}

die();

?>