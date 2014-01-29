<?php
/*
Simple:Press Admin
Ahah call for Users
$LastChangedDate: 2013-03-02 10:15:32 -0700 (Sat, 02 Mar 2013) $
$Rev: 9944 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

# ----------------------------------
# Check Whether User Can Manage Users
if (!sp_current_user_can('SPF Manage Users')) {
	spa_etext('Access denied - you do not have permission');
	die();
}

$action = $_GET['action'];
if (isset($action) && $action == 'delete') {
	$userid = sp_esc_int($_GET['id']);
	if (!current_user_can('delete_user', $userid)) {
		wp_die(spa_text( "You can't delete that user."));
	} else {
		require_once(ABSPATH.'wp-admin/includes/user.php');
		wp_delete_user($userid);
	}
}

die();

?>