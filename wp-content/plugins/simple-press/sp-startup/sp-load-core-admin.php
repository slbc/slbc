<?php
/*
Simple:Press
DESC: Loads core admin code - both forum and not forum
$LastChangedDate: 2013-09-30 10:55:47 -0700 (Mon, 30 Sep 2013) $
$Rev: 10764 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	CORE ADMIN
# 	This file loads the core SP admin support needed by the back end for all page loads -
# 	not just for the forum. It exposes forum and non-forum admin hooks like dashboard, the
#	'block admoin' feature, user registration etc.
#
# ==========================================================================================

# ------------------------------------------------------------------------------------------
# Global variables needed

global $spIsAdmin, $spIsForumAdmin, $spAPage, $spStatus;
# ------------------------------------------------------------------------------------------

# ------------------------------------------------------------------------------------------
# Include Admin specific constant and API files

include_once(SPBOOT.'forum/sp-global-forum-constants.php');
include_once(SPBOOT.'admin/spa-admin-global-functions.php');
include_once(SPBOOT.'admin/spa-admin-updater.php');
include_once(SPBOOT.'admin/spa-admin-menu.php');
# ------------------------------------------------------------------------------------------
include_once(SF_PLUGIN_DIR.'/forum/editor/sp-text-editor-filters.php');


# ------------------------------------------------------------------------------------------
# Set up core admin specific support WP Hooks

# Load admin menu CSS and the Menu Defintions

add_action('admin_print_styles', 	'spa_load_menu_css');
add_action('admin_menu', 			'spa_admin_menu');
# ------------------------------------------------------------------------------------------

# Load spThisUser for admin side
add_action('init', 'sp_load_current_user', 1);
# ------------------------------------------------------------------------------------------

# Dashboard notifications

add_action('wp_dashboard_setup', 	'spa_dashboard_setup', 1 );
# ------------------------------------------------------------------------------------------

# WP admin access

if ($spStatus == 'ok' && sp_get_option('sfblockadmin')) {
	add_action('init', 'spa_block_admin', 2);
}
# ------------------------------------------------------------------------------------------

# Change forum permalink if needed

add_action('permalink_structure_changed', 'spa_permalink_changed', 10, 2);
# ------------------------------------------------------------------------------------------

# check sp version for upgrades
if (is_main_site()) {
    add_action('update-custom_update-sp-plugins',   'spa_load_updater');
    add_action('update-custom_update-sp-themes',    'spa_load_updater');

	# main core checks
	add_action('wp_update_plugins', 	                   'sp_update_check_sp_version');
	add_action('load-update.php', 		                   'sp_update_check_sp_version');
	add_action('load-update-core.php', 	                   'sp_update_check_sp_version');
	add_action('core_upgrade_preamble',                    'sp_update_check_sp_version');
	add_filter('plugins_api_result', 	                   'sp_core_plugin_info', 10, 3);
	add_filter('update_bulk_plugins_complete_actions', 	   'sp_plugin_upgrade_link', 10, 2);

	# sp plugin checks
	add_action('core_upgrade_preamble', 					'sp_update_check_sp_plugins');
	add_action('update-core-custom_do-sp-plugin-upgrade', 	'sp_update_plugins');
	add_action('update-custom_update-sp-plugins',			'sp_do_plugins_update');
	add_action('update-custom_upload-sp-plugin',			'sp_do_plugin_upload');

	# sp theme checks
	add_action('core_upgrade_preamble', 					'sp_update_check_sp_themes');
	add_action('update-core-custom_do-sp-theme-upgrade', 	'sp_update_themes');
	add_action('update-custom_update-sp-themes',			'sp_do_themes_update');
	add_action('update-custom_upload-sp-theme',			    'sp_do_theme_upload');

	# remove wp upgrade info for sp core so only ours is displayed
	add_action('after_plugin_row_simple-press/sp-control.php', 'sp_remove_plugin_info', 0, 2 );

    # add our updates into wp update coumt
	add_filter('wp_get_update_data',           'sp_update_wp_counts', 10, 2);
}

# Plugin page updating and links

add_action('after_plugin_row', 						'sp_plugins_check_sp_version' );
add_filter('network_admin_plugin_action_links', 	'spa_add_plugin_action', 10, 2);
add_filter('plugin_action_links', 					'spa_add_plugin_action', 10, 2);
add_action('admin_head', 							'spa_check_removal');
# ------------------------------------------------------------------------------------------

# Actiating, Deactivating and Uninstall

add_action('activate_simple-press/sp-control.php', 'spa_activate_plugin');
add_action('deactivate_simple-press/sp-control.php', 'spa_deactivate_plugin');
# ------------------------------------------------------------------------------------------

do_action('sph_admin_core_startup');
?>