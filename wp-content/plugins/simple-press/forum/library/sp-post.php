<?php
/*
Simple:Press
Forum Topic/Post Saves
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# set up required globals and load support files -----------------------------------
global $spThisUser, $spGlobals;

sp_forum_api_support();
sp_load_editor(0,1);

include_once(SF_PLUGIN_DIR.'/forum/library/sp-post-support.php');

# Initialise the class -------------------------------------------------------------
$p = new spPost;

# Set up curret user details needed to keep class user agnostic
$p->userid		= $spThisUser->ID;
$p->admin 		= $spThisUser->admin;
$p->moderator	= $spThisUser->moderator;
$p->member		= $spThisUser->member;
$p->guest		= $spThisUser->guest;

# Set data items needed for initial needed permission checks -----------------------
if (isset($_POST['action']))  $p->action = $_POST['action'];

if(isset($_POST['forumid']))	$p->newpost['forumid'] 		= sp_esc_int($_POST['forumid']);
if(isset($_POST['forumslug']))	$p->newpost['forumslug'] 	= sp_esc_str($_POST['forumslug']);

if($p->action == 'post') {
	if(isset($_POST['topicid']))	$p->newpost['topicid'] 		= sp_esc_int($_POST['topicid']);
	if(isset($_POST['topicslug']))	$p->newpost['topicslug'] 	= sp_esc_str($_POST['topicslug']);
}

# Permission checks on forum data --------------------------------------------------
$p->validatePermission();
if($p->abort) {
	sp_notify(1, $p->message);
	wp_redirect($p->returnURL);
	die();
}

# setup and prepare post data ready for validation ---------------------------------
if ($p->action == 'topic') {
	if (!empty($_POST['newtopicname']))	$p->newpost['topicname'] 	= $_POST['newtopicname'];
	if (isset($_POST['topicpin'])) 		$p->newpost['topicpinned'] 	= 1;
}

if ($p->action == 'post') {
	$p->newpost['topicname'] = spdb_table(SFTOPICS, 'topic_id='.$p->newpost['topicid'], 'topic_name');
	if (isset($_POST['postpin'])) 		$p->newpost['postpinned'] 	= 1;
}

# Both
if (!empty($_POST['postitem'])) 	$p->newpost['postcontent'] 	= $_POST['postitem'];
if ($spThisUser->guest) {
	if (!empty($_POST['guestname']))	$p->newpost['guestname'] 	= $_POST['guestname'];
	if (!empty($_POST['guestemail']))	$p->newpost['guestemail'] 	= $_POST['guestemail'];
} else {
	$p->newpost['postername'] 	= $spThisUser->display_name;
	$p->newpost['posteremail'] 	= $spThisUser->user_email;
	$p->newpost['userid'] 		= $spThisUser->ID;
}
$p->newpost['posterip'] = sp_get_ip();

if (isset($_POST['topiclock'])) 	$p->newpost['topicstatus'] 	= 1;

if (!empty($_POST['editTimestamp'])) {
	$yy = sp_esc_int($_POST['tsYear']);
	$mm = sp_esc_int($_POST['tsMonth']);
	$dd = sp_esc_int($_POST['tsDay']);
	$hh = sp_esc_int($_POST['tsHour']);
	$mn = sp_esc_int($_POST['tsMinute']);
	$ss = sp_esc_int($_POST['tsSecond']);
	$dd = ($dd > 31 ) ? 31 : $dd;
	$hh = ($hh > 23 ) ? $hh -24 : $hh;
	$mn = ($mn > 59 ) ? $mn -60 : $mn;
	$ss = ($ss > 59 ) ? $ss -60 : $ss;
	$p->newpost['postdate'] = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $yy, $mm, $dd, $hh, $mn, $ss );
}

# Permission checks on forum data --------------------------------------------------
$p->validateData();
if($p->abort) {
	sp_return_to_post($p->returnURL, $p->message);
	die();
}

# let any plugins perform their stuff ----------------------------------------------
do_action('sph_pre_post_create', $p->newpost);
$p->newpost = apply_filters('sph_new_forum_post', $p->newpost);

# make sure plugin didnt cancel the save -------------------------------------------
if (!empty($p->newpost['error'])) {
	sp_return_to_post($p->returnURL, $p->newpost['error']);
	die();
}

# ready for some unique and topic/post form specific checks ------------------------
if($p->action == 'topic') {
	check_admin_referer('forum-userform_addtopic', 'forum-userform_addtopic');
} else {
	check_admin_referer('forum-userform_addpost', 'forum-userform_addpost');
}

$spamcheck = sp_check_spammath($p->newpost['forumid']);
if ($spamcheck[0] == true) {
	sp_return_to_post($p->returnURL, $spamcheck[1]);
	die();
}

# now we can save to the database --------------------------------------------------
$p->saveData();
if($p->abort) {
	sp_return_to_post($p->returnURL, $p->message);
	die();
} else {
	if($p->action == 'topic') {
		sp_notify(0, sp_text('New topic saved').$p->newpost['submsg']);
	} else {
		sp_notify(0, sp_text('New post saved').$p->newpost['submsg']);
	}
}
do_action('sph_post_create', $p->newpost);
$p->returnURL = apply_filters('sph_new_forum_post_returnurl', $p->returnURL);

wp_redirect($p->returnURL);

die();


# ==================================================================================

# Return to editor if problem
function sp_return_to_post($returnURL, $message) {
	# place details in transient cache
	$failure = array();
	$failure['message'] = sp_text('Unable to save').'<br />'.$message;
	if (isset($_POST['newtopicname']) ? $failure['newtopicname'] = $_POST['newtopicname'] : $failure['newtopicname'] = '');
	if (isset($_POST['guestname']) ? $failure['guestname'] = $_POST['guestname'] : $failure['guestname'] = '');
	if (isset($_POST['guestemail']) ? $failure['guestemail'] = $_POST['guestemail'] : $failure['guestemail'] = '');
	$failure['postitem'] = $_POST['postitem'];

	sp_add_transient(1, $failure);
	wp_redirect($returnURL);
}

?>