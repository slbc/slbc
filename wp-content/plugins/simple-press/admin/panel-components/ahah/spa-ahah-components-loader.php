<?php
/*
Simple:Press Admin
Ahah form loader - Components
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

include_once(SF_PLUGIN_DIR.'/admin/panel-components/spa-components-display.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-components/support/spa-components-prepare.php');
include_once(SF_PLUGIN_DIR.'/admin/panel-components/support/spa-components-save.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-tab-support.php');

global $adminhelpfile;
$adminhelpfile = 'admin-components';
# --------------------------------------------------------------------

# ----------------------------------
# Check Whether User Can Manage Options
if (!sp_current_user_can('SPF Manage Components')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

if (isset($_GET['loadform'])) {
	spa_render_components_container($_GET['loadform']);
	die();
}

if (isset($_GET['saveform'])) {
	switch($_GET['saveform']) {
		case 'smileys':
			echo spa_save_smileys_data();
			break;

		case 'login':
			echo spa_save_login_data();
			break;

		case 'seo':
			echo spa_save_seo_data();
			break;

		case 'forumranks':
			echo spa_save_forumranks_data();
			break;

		case 'specialranks':
			switch ($_GET['action']) {
				case 'newrank':
					echo spa_add_specialrank();
					break;
				case 'updaterank':
					echo spa_update_specialrank(sp_esc_int($_GET['id']));
					break;
				case 'addmember':
					echo spa_add_special_rank_member(sp_esc_int($_GET['id']));
					break;
				case 'delmember':
					echo spa_del_special_rank_member(sp_esc_int($_GET['id']));
					break;
			}
			break;

		case 'messages':
			echo spa_save_messages_data();
			break;

		case 'icons':
			echo spa_save_icons_data();
			break;

		case 'policies':
			echo spa_save_policies_data();
			break;
	}
	die();
}

die();

?>