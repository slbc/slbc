<?php
/*
Simple:Press Admin
Ahah form loader - plugins
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

include_once(SF_PLUGIN_DIR.'/admin/panel-plugins/spa-plugins-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-plugins/support/spa-plugins-save.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

global $adminhelpfile;
$adminhelpfile = 'admin-plugins';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Plugins
# dont check for admin panels loaded/saved by plugins - the plugins api will do that
if ((isset($_GET['loadform']) && $_GET['loadform'] != 'plugin') || (isset($_GET['saveform']) && $_GET['saveform'] != 'plugin')) {
    if (!sp_current_user_can('SPF Manage Plugins')) {
   		spa_etext('Access denied - you do not have permission');
    	die();
    }
}

if (isset($_GET['loadform'])) {
	spa_render_plugins_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	if ($_GET['saveform'] == 'list') {
		echo spa_save_plugin_list_actions();
		die();
	}

	if ($_GET['saveform'] == 'activation') {
		echo spa_save_plugin_activation();
		die();
	}

	if ($_GET['saveform'] == 'plugin') {
		echo spa_save_plugin_userdata(sp_esc_str($_GET['func']));
		die();
	}
}

die();

?>