<?php
/*
Simple:Press
DESC: Loads forum specific admin code
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM ADMIN
# 	This file loads the specific SP Admin support needed by back end for admin forum pages
#
# ==========================================================================================

# ------------------------------------------------------------------------------------------
# Include forum admin constants and code files

include_once(SPBOOT.'admin/spa-admin-framework.php');
include_once(SF_PLUGIN_DIR.'/admin/library/spa-support.php');
include_once(SF_PLUGIN_DIR.'/forum/database/sp-db-management.php');

# ------------------------------------------------------------------------------------------

# try to increase some php settings
sp_php_overrides();

# ------------------------------------------------------------------------------------------
# Load the forum admin CSS files

add_action('admin_print_styles', 'spa_load_admin_css');
# ------------------------------------------------------------------------------------------

# ------------------------------------------------------------------------------------------
# Set up Admin support WP Hooks

# Load admin Javascript, header and Footer

add_action('admin_enqueue_scripts', 'spa_load_admin_scripts');
add_action('admin_head', 'spa_admin_header', 1);
add_action('admin_print_footer_scripts', 'spa_admin_init_scripts');
add_action('in_admin_footer', 'spa_admin_footer');
# ------------------------------------------------------------------------------------------

do_action('sph_admin_startup');
?>