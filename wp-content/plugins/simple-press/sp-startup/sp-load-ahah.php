<?php
/*
Simple:Press
Global ahah loader support
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	AHAH
# 	This file exposes the core functions needed by AHAH calls (front/back end)
#
# ==========================================================================================

# ------------------------------------------------------------------------------------------
# spa_admin_ahah_support()
#
# Loads admin constants and includes to support AHAH calls
# ------------------------------------------------------------------------------------------
function spa_admin_ahah_support() {
	include_once(SPBOOT.'sp-load-core.php');
	sp_load_current_user();
	include_once(SPBOOT.'sp-load-core-admin.php');
	include_once(SPBOOT.'sp-load-admin.php');
}

# ------------------------------------------------------------------------------------------
# sp_forum_api_support()
#
# Loads forum constants and includes to support AHAH calls
# ------------------------------------------------------------------------------------------
function sp_forum_api_support() {
	include_once(SPBOOT.'sp-load-core.php');
	sp_set_server_timezone();
	sp_load_current_user();
	include_once(SPBOOT.'sp-load-site.php');
	include_once(SPBOOT.'sp-load-forum.php');
	sp_get_track_id();
}

do_action('sph_ahah_startup');

?>