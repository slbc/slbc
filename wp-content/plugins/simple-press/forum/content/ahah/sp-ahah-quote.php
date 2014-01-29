<?php
/*
Simple:Press
Quote handing for posts
$LastChangedDate: 2013-04-26 11:15:33 -0700 (Fri, 26 Apr 2013) $
$Rev: 10210 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

sp_forum_api_support();
sp_load_editor(0,1);

global $s0ThisUser;

$postid = sp_esc_int($_GET['post']);
$forumid = sp_esc_int($_GET['forumid']);
if (empty($forumid) || empty($postid)) die();

if (!sp_get_auth('reply_topics', $forumid)) {
	if (!is_user_logged_in()) {
		sp_etext('Access denied - are you logged in?');
	} else {
		sp_etext('Access denied - you do not have permission');
	}
	die();
}

$post = spdb_table(SFPOSTS, "post_id=$postid", 'row');

if (!sp_get_auth('view_admin_posts', $forumid) && sp_is_forum_admin($post->user_id)) die();
if (sp_get_auth('view_own_admin_posts', $forumid) && !sp_is_forum_admin($post->user_id) && !sp_is_forum_mod($post->user_id) && $spThisUser->ID != $post->user_id) die();

$content = sp_filter_content_edit($post->post_content);
echo $content;

die();
?>