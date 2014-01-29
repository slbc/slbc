<?php
/*
Simple:Press
Main database routines
$LastChangedDate: 2012-11-18 11:04:10 -0700 (Sun, 18 Nov 2012) $
$Rev: 9312 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	GLOBAL Database Module
# 	Main Forum Select Database Routines
#
#	sp_get_group_name_from_forum()
#	sp_get_group_record_from_slug()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_get_group_name_from_forum()
#
# Returns the Group Name when only the forum id is known
#	$forumid:		forum to lookup for group name
# ------------------------------------------------------------------
function sp_get_group_name_from_forum($forumid)
{
	if(!$forumid) return '';

	return spdb_select('var',
			"SELECT ".SFGROUPS.".group_name
			 FROM ".SFGROUPS."
			 JOIN ".SFFORUMS." ON ".SFFORUMS.".group_id = ".SFGROUPS.".group_id
			 WHERE ".SFFORUMS.".forum_id=".$forumid);
}

# ------------------------------------------------------------------
# sp_get_group_record_from_slug()
#
# Returns a single group and forum row
#	$forumslug:		forum_slug of group and forum to return
#	$asArray:		return as an array if true
# Note: No permission checking is performed
# ------------------------------------------------------------------
function sp_get_group_record_from_slug($forumslug, $asArray=false)
{
	if(!$forumslug) return '';

	$sql=(
			"SELECT *
			 FROM ".SFFORUMS."
			 JOIN ".SFGROUPS." ON ".SFFORUMS.".group_id = ".SFGROUPS.".group_id
			 WHERE forum_slug='".$forumslug."';");
	if($asArray) return spdb_select('row', $sql, ARRAY_A);
	return spdb_select('row', $sql);
}

?>