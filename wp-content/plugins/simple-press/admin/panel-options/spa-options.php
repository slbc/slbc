<?php
/*
Simple:Press
Admin Panels - Option Management
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Options
if (!sp_current_user_can('SPF Manage Options')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

global $spStatus;

include_once(SF_PLUGIN_DIR.'/admin/panel-options/spa-options-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-options/support/spa-options-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

if ($spStatus != 'ok') {
	include_once(SPLOADINSTALL);
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-options';
# --------------------------------------------------------------------

if (isset($_GET['tab']) ? $tab = $_GET['tab'] : $tab = 'global');

spa_panel_header();
spa_render_options_panel($tab);
spa_panel_footer();

?>