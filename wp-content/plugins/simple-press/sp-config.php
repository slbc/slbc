<?php
/*
Simple:Press
sp-config.php - location support for WP 2.6 and later
$LastChangedDate: 2012-12-26 13:42:34 -0700 (Wed, 26 Dec 2012) $
$Rev: 9582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE
#	This is the first file to be included and contains some base settings that can be
#	overridden by the user
#
# ==========================================================================================

global $wpdb;

# ------------------------------------------------------------------------------------------
# After WP 2.6 it is possible to relocate the wp-config.php file. Simple:Press should be
# able to find it. However, if it does not, then you will need to change the SF_BASEPATH
# constant below to point to the path of:
#
#	your wp-load.php file
#
# This must be a 'path' NOT 'URL' and necessary if you have moved your wp-content folder.

define('SF_BASEPATH', dirname(dirname(dirname(dirname(__FILE__)))));
# ------------------------------------------------------------------------------------------

# ------------------------------------------------------------------------------------------
# Normally, Simple:Press tables are given the same table prefix as all of your other
# WordPress tables. You can change this by specifying an alternative below.

define('SF_PREFIX', $wpdb->prefix);
# ------------------------------------------------------------------------------------------

# ------------------------------------------------------------------------------------------
# A small javascript program has been used to replace checkboxes and radio buttons with
# more appealing graphics. A small number of users have experienced a conflict with this
# js library. If you have this problem please set SP_USE_PRETTY_CBOX_ADMIN to false.
# Note this applies to the admin only. For the forum itself this is defined in the theme

define('SP_USE_PRETTY_CBOX_ADMIN', true);
# ------------------------------------------------------------------------------------------

# Script concatenation is introduced in WP version 2.8. Sadly, out if the box, some of
# the jQuery code throws an error so this is turned off for the forum.

define('CONCATENATE_SCRIPTS', false);
?>