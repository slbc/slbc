<?php
/*
Simple:Press
Admin Forums
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# Check Whether User Can Manage Forums
global $spStatus;
if (!sp_current_user_can('SPF Manage Forums')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

include_once(SF_PLUGIN_DIR.'/admin/panel-forums/spa-forums-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-forums/support/spa-forums-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

if ($spStatus != 'ok') {
    include_once(SPLOADINSTALL);
    die();
}

global $adminhelpfile;
$adminhelpfile = 'admin-forums';
# --------------------------------------------------------------------

if (isset($_GET['tab']) ? $tab=$_GET['tab'] : $tab='forums');
spa_panel_header();
spa_render_forums_panel($tab);
spa_panel_footer();
?>