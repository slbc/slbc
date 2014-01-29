<?php
/*
Simple:Press
DESC: Global Site Constants
$LastChangedDate: 2013-09-02 12:49:30 -0700 (Mon, 02 Sep 2013) $
$Rev: 10637 $
*/

# ==========================================================================================
#
#	CORE
# 	Loaded by core - globally required by back end/admin for all pages
#
# ==========================================================================================

global $wpdb, $spPaths, $spMobile, $spDevice;

if (!defined('SFBLOGID'))			define('SFBLOGID', $wpdb->blogid);

# Charset
if (!defined('SFCHARSET'))			define('SFCHARSET', get_bloginfo('charset'));
if (!defined('SF_STORE_DIR'))		define('SF_STORE_DIR',   WP_CONTENT_DIR);
if (!defined('SF_STORE_URL'))		define('SF_STORE_URL',   content_url());

# Location of themes
if (!defined('SPTHEMEBASEURL'))		define('SPTHEMEBASEURL',    SF_STORE_URL.'/'.$spPaths['themes'].'/');
if (!defined('SPTHEMEBASEDIR'))		define('SPTHEMEBASEDIR',    SF_STORE_DIR.'/'.$spPaths['themes'].'/');

$curTheme = sp_get_current_sp_theme();

# Dir of templates, Dir of images and url of CSS file
if (!defined('SPTEMPLATES'))		define('SPTEMPLATES', 	SPTHEMEBASEDIR.$curTheme['theme'].'/templates/');
if (!defined('SPTHEMEURL'))			define('SPTHEMEURL',    SPTHEMEBASEURL.$curTheme['theme'].'/styles/');
if (!defined('SPTHEMEDIR'))			define('SPTHEMEDIR',    SPTHEMEBASEDIR.$curTheme['theme'].'/styles/');
if (!defined('SPTHEMEICONSURL')) {
	$p = ($spDevice=='mobile' && file_exists(SPTHEMEBASEDIR.$curTheme['theme'].'/images/mobile/')) ? $curTheme['theme'].'/images/mobile/' : $curTheme['theme'].'/images/';
	define('SPTHEMEICONSURL',	SPTHEMEBASEURL.$p);
	define('SPTHEMEICONSDIR',	SPTHEMEBASEDIR.$p);
}

if (!defined('SPTHEMECSS'))			define('SPTHEMECSS', 	SPTHEMEBASEURL.$curTheme['theme'].'/styles/'.$curTheme['style']);

# Location of uploaded Avatars, Smileys and Ranks
if (!defined('SFAVATARURL'))		define('SFAVATARURL',	  SF_STORE_URL.'/'.$spPaths['avatars'].'/');
if (!defined('SFAVATARDIR'))		define('SFAVATARDIR',	  SF_STORE_DIR.'/'.$spPaths['avatars'].'/');
if (!defined('SFAVATARPOOLURL'))	define('SFAVATARPOOLURL', SF_STORE_URL.'/'.$spPaths['avatar-pool'].'/');
if (!defined('SFAVATARPOOLDIR'))	define('SFAVATARPOOLDIR', SF_STORE_DIR.'/'.$spPaths['avatar-pool'].'/');
if (!defined('SFSMILEYS'))			define('SFSMILEYS',		  SF_STORE_URL.'/'.$spPaths['smileys'].'/');
if (!defined('SFRANKS'))			define('SFRANKS',		  SF_STORE_URL.'/'.$spPaths['ranks'].'/');

# Location of plugins
if (!defined('SFPLUGINURL'))		define('SFPLUGINURL',     SF_STORE_URL.'/'.$spPaths['plugins'].'/');
if (!defined('SFPLUGINDIR'))		define('SFPLUGINDIR',     SF_STORE_DIR.'/'.$spPaths['plugins'].'/');

# Location of custom icons
if (!defined('SFCUSTOMDIR'))      	define('SFCUSTOMDIR',     SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/');
if (!defined('SFCUSTOMURL'))   		define('SFCUSTOMURL',  	  SF_STORE_URL.'/'.$spPaths['custom-icons'].'/');

# Location of scripts
if (!defined('SFJSCRIPT'))			define('SFJSCRIPT',       SF_PLUGIN_URL.'/forum/resources/jscript/');
if (!defined('SFIJSCRIPT'))			define('SFIJSCRIPT',      SF_PLUGIN_URL.'/install/resources/jscript/');
if (!defined('SFCJSCRIPT'))			define('SFCJSCRIPT',      SF_PLUGIN_URL.'/resources/jscript/');
if (!defined('SFAJSCRIPT'))			define('SFAJSCRIPT',      SF_PLUGIN_URL.'/admin/resources/jscript/');

# Combined CSS/JS cache file
if (!defined('SP_COMBINED_CACHE_URL'))	     define('SP_COMBINED_CACHE_URL', SF_STORE_URL.'/'.$spPaths['cache'].'/');
if (!defined('SP_COMBINED_CACHE_DIR'))	     define('SP_COMBINED_CACHE_DIR', SF_STORE_DIR.'/'.$spPaths['cache'].'/');

if (!defined('SP_COMBINED_CSS_BASE_NAME'))	     define('SP_COMBINED_CSS_BASE_NAME', 'sp-plugin-styles-');
if (!defined('SP_COMBINED_SCRIPTS_BASE_NAME'))   define('SP_COMBINED_SCRIPTS_BASE_NAME', 'sp-plugin-scripts-');

# Location of forum non-theme images
if (!defined('SPFIMAGES'))			define('SPFIMAGES',       SF_PLUGIN_URL.'/forum/resources/images/');

# these are constants no longer used in 5.0+ except for upgrade support and uninstall
# for users who upgraded to 5.0 put didnt use the equivalent plugins
if (!defined('SFPOSTRATINGS'))		define('SFPOSTRATINGS', SF_PREFIX.'sfpostratings');
if (!defined('SFMESSAGES'))			define('SFMESSAGES',	SF_PREFIX.'sfmessages');

# WP tables needed
if (!defined('SFWPPOSTS'))			define('SFWPPOSTS',    $wpdb->posts);
if (!defined('SFWPPOSTMETA'))		define('SFWPPOSTMETA', $wpdb->postmeta);
if (!defined('SFWPCOMMENTS'))		define('SFWPCOMMENTS', $wpdb->comments);

if (defined('CUSTOM_USER_TABLE')) {
	if (!defined('SFUSERS'))		define('SFUSERS', CUSTOM_USER_TABLE);
} else {
	if (!defined('SFUSERS'))		define('SFUSERS', $wpdb->users);
}
if (defined('CUSTOM_USER_META_TABLE')) {
	if (!defined('SFUSERMETA'))		define('SFUSERMETA', CUSTOM_USER_META_TABLE);
} else {
	if (!defined('SFUSERMETA'))		define('SFUSERMETA', $wpdb->usermeta);
}

if (!defined('SFDATES'))			define('SFDATES', sp_get_option('sfdates'));
if (!defined('SFTIMES'))			define('SFTIMES', sp_get_option('sftimes'));

if (!defined('SPLOADINSTALL')) 		define('SPLOADINSTALL', SPBOOT.'sp-load-install.php');
if (!defined('SPHELP')) 			define('SPHELP', SF_PLUGIN_DIR.'/admin/help/');

do_action('sph_global_site_constants');

?>