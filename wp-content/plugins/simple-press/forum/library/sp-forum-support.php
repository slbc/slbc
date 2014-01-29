<?php
/*
Simple:Press
Support Routines
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	FORUM
# 	This file loads the SP support needed by all front end forum page loads
#	This file should contain only functions not required by plugins or plugin authors
#	and is specific to foirum display only.
#
#	sp_push_topic_page()
#	sp_pop_topic_page()
#	sp_display_banner()
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_push_topic_page()
#
# called on forum display to note current topic page user is viewing.
#	$forumid:
#	$page:
# ------------------------------------------------------------------
function sp_push_topic_page($forumid, $page) {
	sp_add_transient(4, $forumid.'@'.$page);
}

# ------------------------------------------------------------------
# sp_pop_topic_page()
#
# called on topic display to set breadcrumb to correct page
# if same forum
#	$forumid:
# ------------------------------------------------------------------
function sp_pop_topic_page($forumid) {
	$page = 1;
	$check = sp_get_transient(4, true);

	# if no record then resort to page 1
	if ($check == '') return $page;
	$check = explode('@', $check);

	# is it the same forum?
	if ($check[0] == $forumid) $page = $check[1];
	return $page;
}

# ------------------------------------------------------------------
# sp_display_banner()
#
# displays optional banner instead of page title
# ------------------------------------------------------------------
function sp_display_banner() {
	global $spGlobals;
	if (!empty($spGlobals['display']['pagetitle']['banner'])) return '<img id="sfbanner" src="'.esc_url($spGlobals['display']['pagetitle']['banner']).'" alt="" />';
}

?>