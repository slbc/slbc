<?php
/*
Simple:Press
Deprecated - global code
$LastChangedDate: 2013-08-24 04:15:38 -0700 (Sat, 24 Aug 2013) $
$Rev: 10579 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Deprecated Functions - where deprecated functions come to die
#   Wrappers for old deprecated functions which call the new, appropriate functions
#   Emit a deprecated warning
#   handled like wp deprecations (same logging)
#
# ==========================================================================================


# --------------------------------------------------------------
# Global Variables
#
# Deprecated: version 5.3.1
#
# The following variables are being renamed in version 5.3.1
# and the original names - while cfontunuing to work - will
# be removed in due course
# --------------------------------------------------------------

global
	$SPALLOPTIONS, $spAllOptions,
	$SPPATHS, $spPaths,
	$ISADMIN, $spIsAdmin,
	$spIsForumADMIN, $spIsForumAdmin,
	$SPSTATUS, $spStatus,
	$APAGE, $spAPage,
	$spIsForum, $spIsForum,
	$SPCACHE, $spBootCache,
	$CONTENTLOADED, $spContentLoaded,
	$SFMOBILE, $spMobile,
	$SFDEVICE, $spDevice,
	$sfglobals, $spGlobals,
	$sfvars, $spVars,
	$sfmeta, $spMeta;

	$SPALLOPTIONS =& $spAllOptions;
	$SPPATHS =& $spPaths;
	$ISADMIN =& $spIsAdmin;
	$spIsForumADMIN =& $spIsForumAdmin;
	$SPSTATUS =& $spStatus;
	$APAGE =& $spAPage;
	$spIsForum =& $spIsForum;
	$SPCACHE =& $spBootCache;
	$CONTENTLOADED =& $spContentLoaded;
	$SFMOBILE =& $spMobile;
	$SFDEVICE =& $spDevice;
	$sfglobals =& $spGlobals;
	$sfvars =& $spVars;
	$sfmeta =& $spMeta;


# --------------------------------------------------------------
# sp_build_qurl()
#
# Deprecated: version 5.3.1
# Main Query String URL building routine
# Must have at least one parameter of 'var=value' string
# Up to five can be passed in.
# --------------------------------------------------------------
function sp_build_qurl($param1, $param2='', $param3='', $param4='', $param5='', $param6='', $param7='') {

	trigger_error('The function sp_build_qurl() has been deprecated and will be reomoved in a future update', E_USER_WARNING);

	$url = sp_url();

	# first does it need the ?
	if (strpos($url, '?') === false) {
		$url .= '?';
		$and = '';
	} else {
		$and = '&amp;';
	}

	$url.= $and.$param1;
	$and = '&amp;';
	if (!empty($param2)) $url.= $and.$param2;
	if (!empty($param3)) $url.= $and.$param3;
	if (!empty($param4)) $url.= $and.$param4;
	if (!empty($param5)) $url.= $and.$param5;
	if (!empty($param6)) $url.= $and.$param6;
	if (!empty($param7)) $url.= $and.$param7;
	return $url;
}

?>