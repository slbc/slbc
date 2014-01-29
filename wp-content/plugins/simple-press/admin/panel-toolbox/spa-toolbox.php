<?php
/*
Simple:Press
Admin Panels - Toolbox
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Toolbox
if (!sp_current_user_can('SPF Manage Toolbox')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

global $spStatus;

include_once(SF_PLUGIN_DIR.'/admin/panel-toolbox/spa-toolbox-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-toolbox/support/spa-toolbox-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

if ($spStatus != 'ok') {
	include_once(SPLOADINSTALL);
	die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-toolbox';
# --------------------------------------------------------------------

if (isset($_GET['tab'])) {
	$formid = $_GET['tab'];
} else {
	if (isset($_GET['form'])) {
		$formid = $_GET['form'];
	} else {
		$formid = 'toolbox';
	}
}

spa_panel_header();
spa_render_toolbox_panel($formid);
spa_panel_footer();

?>