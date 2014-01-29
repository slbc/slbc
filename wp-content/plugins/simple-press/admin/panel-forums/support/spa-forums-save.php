<?php
/*
Simple:Press
Admin Forums Data Sae Support Functions
$LastChangedDate: 2013-08-24 08:44:54 -0700 (Sat, 24 Aug 2013) $
$Rev: 10582 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_forums_create_group() {
	global $spVars;

	check_admin_referer('forum-adminform_groupnew', 'forum-adminform_groupnew');

	$ug_list = array_unique($_POST['usergroup_id']);
	$perm_list = $_POST['role'];

	$seq = (spdb_max(SFGROUPS, 'group_seq') + 1);

	$groupdata = array();

	if (empty($_POST['group_name'])) {
		$groupdata['group_name'] = spa_text('New forum group');
	} else {
		$groupdata['group_name'] = sp_filter_title_save(trim($_POST['group_name']));
	}
	if (empty($_POST['group_seq'])) {
		$groupdata['group_seq'] = $seq;
	} else {
		if (is_numeric($_POST['group_seq'])) {
		   $groupdata['group_seq'] = sp_esc_int($_POST['group_seq']);
		} else {
			$mess = spa_text('New group creation failed as the sequence must be an integer');
			return $mess;
		}
	}

	if (!empty($_POST['group_icon'])) {
		# Check new icon exists
		$groupdata['group_icon'] = sp_filter_title_save(trim($_POST['group_icon']));
		$path = SFCUSTOMDIR.$groupdata['group_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $groupdata['group_icon']);
			return $mess;
		}
	} else {
		$groupdata['group_icon'] = NULL;
	}

	$groupdata['group_desc'] = sp_filter_text_save(trim($_POST['group_desc']));
	$groupdata['group_message'] = sp_filter_text_save(trim($_POST['group_message']));

	# check if we need to shuffle sequence numbers
	if ($groupdata['group_seq'] < $seq) {
		$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
		foreach ($groups as $group) {
			if ($group->group_seq >= $groupdata['group_seq']) {
				spa_bump_group_seq($group->group_id, ($group->group_seq + 1));
			}
		}
	}

	# create the group
	$sql = 'INSERT INTO '.SFGROUPS.' (group_name, group_desc, group_seq, group_icon, group_message) ';
	$sql.= "VALUES ('".$groupdata['group_name']."', '".$groupdata['group_desc']."', ".$groupdata['group_seq'].", '".$groupdata['group_icon']."', '".$groupdata['group_message']."')";
	$success = spdb_query($sql);
	$group_id = $spVars['insertid'];

	# save the default permissions for the group
	for( $x=0; $x<count($ug_list); $x++) {
		if ($perm_list[$x] != -1) spa_add_defpermission_row($group_id, (int) $ug_list[$x], (int) $perm_list[$x]);
	}

	if ($success == false) {
		$mess = spa_text('New group creation failed');
	} else {
		$mess = spa_text('New group created');

		do_action('sph_forum_group_create', $group_id);
	}

	return $mess;
}

function spa_save_forums_create_forum() {
	global $spVars;

	check_admin_referer('forum-adminform_forumnew', 'forum-adminform_forumnew');

	$forumdata = array();

	if ($_POST['forumtype'] == 1) {
		# Standard forum
		$forumdata['group_id'] = sp_esc_int($_POST['group_id']);
	} else {
		# Sub forum
		$parentforum = spdb_table(SFFORUMS, 'forum_id='.sp_esc_int($_POST['forum_id']),'row');
		$forumdata['group_id'] = $parentforum->group_id;
	}

	$seq = (spdb_max(SFFORUMS, 'forum_seq', 'group_id='.$forumdata['group_id']) + 1);

	if (!isset($_POST['forum_seq']) || sp_esc_int($_POST['forum_seq'] == 0)) {
		$forumdata['forum_seq'] = $seq;
	} else {
		$forumdata['forum_seq'] = sp_esc_int($_POST['forum_seq']);
	}

	$forumdata['forum_desc'] = sp_filter_text_save(trim($_POST['forum_desc']));

	$forumdata['forum_status'] = 0;
	if (isset($_POST['forum_status'])) $forumdata['forum_status'] = 1;

	$forumdata['forum_rss_private'] = 0;
	if (isset($_POST['forum_private'])) $forumdata['forum_rss_private'] = 1;

	if (empty($_POST['forum_name'])) {
		$forumdata['forum_name'] = spa_text('New forum');
	} else {
		$forumdata['forum_name'] = sp_filter_title_save(trim($_POST['forum_name']));
	}

	$forumdata['forum_keywords'] = sp_filter_title_save(trim($_POST['forum_keywords']));

	$forumdata['forum_message'] = sp_filter_text_save(trim($_POST['forum_message']));

	if (!empty($_POST['forum_icon'])) {
		# Check new icon exists
		$forumdata['forum_icon'] = sp_filter_title_save(trim($_POST['forum_icon']));
		$path = SFCUSTOMDIR.$forumdata['forum_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['forum_icon']);
			return $mess;
		}
	} else {
		$forumdata['forum_icon'] = NULL;
	}

	if (!empty($_POST['forum_icon_new'])) {
		# Check new icon exists
		$forumdata['forum_icon_new'] = sp_filter_title_save(trim($_POST['forum_icon_new']));
		$path = SFCUSTOMDIR.$forumdata['forum_icon_new'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['forum_icon_new']);
			return $mess;
		}
	} else {
		$forumdata['forum_icon_new'] = NULL;
	}

	if (!empty($_POST['topic_icon'])) {
		# Check new icon exists
		$forumdata['topic_icon'] = sp_filter_title_save(trim($_POST['topic_icon']));
		$path = SFCUSTOMDIR.$forumdata['topic_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['topic_icon']);
			return $mess;
		}
	} else {
		$forumdata['topic_icon'] = NULL;
	}

	if (!empty($_POST['topic_icon_new'])) {
		# Check new icon exists
		$forumdata['topic_icon_new'] = sp_filter_title_save(trim($_POST['topic_icon_new']));
		$path = SFCUSTOMDIR.$forumdata['topic_icon_new'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['topic_icon_new']);
			return $mess;
		}
	} else {
		$forumdata['topic_icon_new'] = NULL;
	}

	# check if we need to shuffle sequence numbers
	if ($forumdata['forum_seq'] < $seq) {
		$forums = spa_get_forums_in_group($forumdata['group_id']);
		foreach ($forums as $forum) {
			if ($forum->forum_seq >= $forumdata['forum_seq']) spa_bump_forum_seq($forum->forum_id, ($forum->forum_seq + 1));
		}
	}

	# create the forum
	if ($_POST['forumtype'] == 2) {
		$parentdata = $parentforum->forum_id;
	} else {
		$parentdata = '0';
	}

	# do slug
	if (!isset($_POST['thisforumslug']) || empty($_POST['thisforumslug'])) {
		$forumslug = sp_create_slug($forumdata['forum_name'], true, SFFORUMS, 'forum_slug');
		$forumslug = sp_create_slug($forumslug, true, SFWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
	} else {
		$forumslug = sp_esc_str($_POST['thisforumslug']);
	}

	$sql = 'INSERT INTO '.SFFORUMS.' (forum_name, forum_slug, forum_desc, group_id, forum_status, forum_seq, forum_rss_private, forum_icon, forum_icon_new, topic_icon, topic_icon_new, parent, forum_message, keywords) ';
	$sql.= "VALUES ('".$forumdata['forum_name']."', '".$forumslug."', '".$forumdata['forum_desc']."', ".$forumdata['group_id'].", ".$forumdata['forum_status'].", ".$forumdata['forum_seq'].", ".$forumdata['forum_rss_private'].", '".$forumdata['forum_icon']."', '".$forumdata['forum_icon_new']."', '".$forumdata['topic_icon']."', '".$forumdata['topic_icon_new']."', ".$parentdata.", '".$forumdata['forum_message']."', '".$forumdata['forum_keywords']."');";
	$thisforum = spdb_query($sql);
	$forum_id = $spVars['insertid'];

	# now check the slug was populated and if not replace with forum id
	if (empty($forumslug)) {
		$forumslug = 'forum-'.$forum_id;
		$thisforum = spdb_query('UPDATE '.SFFORUMS." SET forum_slug='$forumslug' WHERE forum_id=$forum_id");
	}
	$success = $thisforum;

	# add the user group permission sets
	$usergroup_id_list = array_unique($_POST['usergroup_id']);
	$role_list = $_POST['role'];
	$perm_prob = false;
	for ($x=0; $x<count($usergroup_id_list); $x++) {
		$usergroup_id = sp_esc_int($usergroup_id_list[$x]);
		$role = sp_esc_int($role_list[$x]);
		if ($role == -1) {
			$defrole = spa_get_defpermissions_role($forumdata['group_id'], $usergroup_id);
			if ($defrole == '') {
				$perm_prob = true;
			} else {
				spa_add_permission_data($forum_id, $usergroup_id, $defrole);
			}
		} else {
			spa_add_permission_data($forum_id, $usergroup_id, $role);
		}
	}

	# reset auths and memberships for everyone
	sp_reset_memberships();
	sp_reset_auths();

	# if the forum was created, signal success - doesnt check user group permission set though
	if ($success == false) {
		$mess = spa_text('New forum creation failed');
	} else {
		if ($perm_prob) {
			$mess = spa_text('New forum created but permission sets not set for all usergroups');
		} else {
			$mess = spa_text('New forum created');
		}

		do_action('sph_forum_forum_create', $forum_id);
	}

	spa_clean_forum_children();
	spa_resequence_forums($forumdata['group_id'], 0);

	return $mess;
}

# function to add a permission set globally to all forum
function spa_save_forums_global_perm() {
	check_admin_referer('forum-adminform_globalpermissionnew', 'forum-adminform_globalpermissionnew');

	if ($_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$usergroup_id = sp_esc_int($_POST['usergroup_id']);
		$permission = sp_esc_int($_POST['role']);

		# loop through all the groups
		$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
		if ($groups) {
			$mess = '';
			foreach ($groups as $group) {
				# use group permission set helper function to actually set the permission set
				$mess.= spa_set_group_permission($group->group_id, $usergroup_id, $permission);
			}

			# reset auths and memberships for everyone
			sp_reset_memberships();
			sp_reset_auths();

			do_action('sph_forum_global_permission');
		} else {
			$mess = spa_text('There are no groups or gorums so no permission set was added');
		}
	} else {
		$mess = spa_text('Adding usergroup permission set failed');
	}

	return $mess;
}

# function to add a permission set to every forum within a group
function spa_save_forums_group_perm() {
	check_admin_referer('forum-adminform_grouppermissionnew', 'forum-adminform_grouppermissionnew');

	if (isset($_POST['group_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$group_id = sp_esc_int($_POST['group_id']);
		$usergroup_id = sp_esc_int($_POST['usergroup_id']);
		$permission = sp_esc_int($_POST['role']);

		# reset auths and memberships for everyone
		sp_reset_memberships();
		sp_reset_auths();

		$mess = spa_set_group_permission($group_id, $usergroup_id, $permission);

        if (isset($_POST['adddef'])) {
    		if (spa_get_defpermissions_role($group_id, $usergroup_id)) {
    			$sql = 'UPDATE '.SFDEFPERMISSIONS."
    					SET permission_role=$permission
    					WHERE group_id=$group_id AND usergroup_id=$usergroup_id";
    			spdb_query($sql);
    		} else {
                if ($permission != -1) spa_add_defpermission_row($group_id, $usergroup_id, $permission);
    		}
        }

		do_action('sph_forum_group_permission', $group_id);
	} else {
		$mess = spa_text('Adding usergroup permission set failed');
	}

	return $mess;
}

# helper function to loop through all forum in a group and add a permission set
function spa_set_group_permission($group_id, $usergroup_id, $permission) {
	$forums = spa_get_forums_in_group($group_id);

	if ($forums) {
		$mess = '';
		foreach ($forums as $forum) {
			# If user group has a current permission set for this forum, remove the old one before adding the new one
			$current = spdb_table(SFPERMISSIONS, "forum_id=$forum->forum_id AND usergroup_id=$usergroup_id", 'row');

			if ($current) spa_remove_permission_data($current->permission_id);

			# add the new permission set
			$success = spa_add_permission_data($forum->forum_id, $usergroup_id, $permission);

			if ($success == false) {
				$mess.= sp_filter_title_display($forum->forum_name).': '. spa_text('Adding usergroup permission set failed').'<br />';
			} else {
				$mess.= sp_filter_title_display($forum->forum_name).': '. spa_text('Usergroup permission set added to forum').'<br />';
			}
		}
	} else {
		$mess = spa_text('Group has no forums so no permission sets were added');
	}

	return $mess;
}

# function to remove all permission set from all forum
function spa_save_forums_remove_perms() {
	check_admin_referer('forum-adminform_allpermissionsdelete', 'forum-adminform_allpermissionsdelete');

	# remove all permission set
	spdb_query('TRUNCATE TABLE '.SFPERMISSIONS);

	# reset auths and memberships for everyone
	sp_reset_memberships();
	sp_reset_auths();

	do_action('sph_forum_remove_perms');

	$mess = spa_text('All permission sets removed');
	return $mess;
}

# function to add a new permission set to a forum
function spa_save_forums_forum_perm() {
	check_admin_referer('forum-adminform_permissionnew', 'forum-adminform_permissionnew');

	if (isset($_POST['forum_id']) && $_POST['usergroup_id'] != -1 && $_POST['role'] != -1) {
		$usergroup_id = sp_esc_int($_POST['usergroup_id']);
		$forum_id = sp_esc_int($_POST['forum_id']);
		$permission = sp_esc_int($_POST['role']);

		# If user group has a current permission set for this forum, remove the old one before adding the new one
		$current = spdb_table(SFPERMISSIONS, "forum_id=$forum_id.AND usergroup_id=$usergroup_id", 'row');

		if ($current) spa_remove_permission_data($current->permission_id);

		# add the new permission set
		$success = spa_add_permission_data($forum_id, $usergroup_id, $permission);
		if ($success == false) {
			$mess = spa_text('Adding usergroup permission set failed');
		} else {
			$mess = spa_text('Usergroup permission set added to forum');

			# reset auths and permissions for everyone
			sp_reset_memberships($uid);
			sp_reset_auths();

			do_action('sph_forum_perm_add', $forum_id, $usergroup_id, $permission);
		}
	} else {
		$mess = spa_text('Adding usergroup permission set failed');
	}

	return $mess;
}

function spa_save_forums_delete_forum() {
	check_admin_referer('forum-adminform_forumdelete', 'forum-adminform_forumdelete');

	$group_id = sp_esc_int($_POST['group_id']);
	$forum_id = sp_esc_int($_POST['forum_id']);
	$cseq = sp_esc_int($_POST['cforum_seq']);

	# If subforum or parent remove the relationship first.
	# Read the 'children' from the database because it is serialised

	$children = spdb_table(SFFORUMS, "forum_id=$forum_id", "children");
	if ($children) {
		$children = unserialize($children);
		foreach ($children as $child) {
			spdb_query('UPDATE '.SFFORUMS.' SET parent=null WHERE forum_id='.sp_esc_int($child));
		}
	}

	# need to delete all topics in the forum using standard routine to clean up behind it
	$topics = spdb_table(SFTOPICS, "forum_id=$forum_id");
	if ($topics) {
		foreach ($topics as $topic) {
			sp_delete_topic($topic->topic_id, $forum_id, false);
		}
	}

	# now delete the forum itself
	$thisForum = spdb_table(SFFORUMS, "forum_id=$forum_id");
	spdb_query('DELETE FROM '.SFFORUMS." WHERE forum_id=$forum_id");

	# remove permissions for this forum
	$perms = sp_get_forum_permissions($forum_id);
	if ($perms) {
		foreach ($perms as $perm) {
			spa_remove_permission_data($perm->permission_id);
		}
	}

	# reset auths and memberships for everyone
	sp_reset_memberships();
	sp_reset_auths();

	# need to iterate through the groups
	$forums = spa_get_forums_in_group($group_id);
	foreach ($forums as $forum) {
		if ($forum->forum_seq > $cseq) spa_bump_forum_seq($forum->forum_id, ($forum->forum_seq - 1));
	}

	$mess = 'Forum deleted';

	spa_clean_forum_children();
	spa_resequence_forums($group_id, 0);

	do_action('sph_forum_forum_del', $thisForum);

	return $mess;
}

function spa_save_forums_disable_forum() {
	check_admin_referer('forum-adminform_forumdisable', 'forum-adminform_forumdisable');

	$forum_id = sp_esc_int($_POST['forum_id']);

	$sql = 'UPDATE '.SFFORUMS." SET forum_disabled=1 WHERE forum_id=$forum_id";
	$success = spdb_query($sql);
	if ($success) 	{
		$mess = spa_text('Forum disabled');
		do_action('sph_forum_forum_disable', $forum_id);
	} else {
		$mess = spa_text('Forum disable failed');
	}

	return $mess;
}

function spa_save_forums_enable_forum() {
	check_admin_referer('forum-adminform_forumenable', 'forum-adminform_forumenable');

	$forum_id = sp_esc_int($_POST['forum_id']);

	$sql = 'UPDATE '.SFFORUMS." SET forum_disabled=0 WHERE forum_id=$forum_id";
	$success = spdb_query($sql);
	if ($success) 	{
		$mess = spa_text('Forum enabled');
		do_action('sph_forum_forum_enable', $forum_id);
	} else {
		$mess = spa_text('Forum enable failed');
	}

	return $mess;
}

function spa_save_forums_delete_group() {
	check_admin_referer('forum-adminform_groupdelete', 'forum-adminform_groupdelete');

	$group_id = sp_esc_int($_POST['group_id']);
	$cseq = sp_esc_int($_POST['cgroup_seq']);

	# remove permissions for each forum in group
	$forums = spa_get_forums_in_group($group_id);
	if ($forums) {
		foreach ($forums as $forum) {
			# remove permissions for this forum
			$perms = sp_get_forum_permissions($forum->forum_id);
			if ($perms) {
				foreach ($perms as $perm) {
					spa_remove_permission_data($perm->permission_id);
				}
			}
		}
	}

	# reset auths and memberships for everyone
	sp_reset_memberships();
	sp_reset_auths();

	# select all the forums in the group
	$forums = spa_get_forums_in_group($group_id);

	# remove the topics and posts in each forum
	foreach ($forums as $forum) {
		# need to delete all topics in the forum using standard routine to clean up behind it
		$topics = spdb_table(SFTOPICS, "forum_id=$forum->forum_id");
		if ($topics) {
			foreach ($topics as $topic) {
				sp_delete_topic($topic->topic_id, $forum->forum_id, false);
			}
		}
	}

	#now remove the forums themselves
	spdb_query('DELETE FROM '.SFFORUMS." WHERE group_id=$group_id");

	# and finaly remove the group
	spdb_query('DELETE FROM '.SFGROUPS." WHERE group_id=$group_id");

	# need to iterate through the groups
	$groups = spdb_table(SFGROUPS, '', '', "group_seq");
	foreach ($groups as $group) {
		if ($group->group_seq > $cseq) spa_bump_group_seq($group->group_id, ($group->group_seq - 1));
	}

	# remove the default permissions for the group being deleted
	spdb_query('DELETE FROM '.SFDEFPERMISSIONS." WHERE group_id=$group_id");

	do_action('sph_forum_group_del', $group_id);

	$mess = spa_text('Group Deleted');
	return $mess;
}

# function to delete an existing permission set for a forum
function spa_save_forums_delete_perm() {
	check_admin_referer('forum-adminform_permissiondelete', 'forum-adminform_permissiondelete');

	$permission_id = sp_esc_int($_POST['permission_id']);

	# remove the permission set from the forum
	$success = spa_remove_permission_data($permission_id);
	if ($success == false) {
		$mess = spa_text('Permission set delete failed');
	} else {
		$mess = spa_text('Permission set deleted');

		# reset auths and memberships for everyone
		sp_reset_memberships();
		sp_reset_auths();

		do_action('sph_forum_perm_del', $permission_id);
	}

	return $mess;
}

function spa_save_forums_edit_forum() {
	check_admin_referer('forum-adminform_forumedit', 'forum-adminform_forumedit');

	$forumdata = array();
	$forum_id = sp_esc_int($_POST['forum_id']);
	$forumdata['forum_name'] = sp_filter_title_save(trim($_POST['forum_name']));
	if (!empty($_POST['thisforumslug'])) {
		$forumdata['forum_slug'] = sp_create_slug($_POST['thisforumslug'], false);
	} else {
		$forumdata['forum_slug'] = sp_create_slug($forumdata['forum_name'], true, SFFORUMS, 'forum_slug');
		$forumdata['forum_slug'] = sp_create_slug($forumdata['forum_slug'], true, SFWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
	}
	$forumdata['forum_desc'] = sp_filter_text_save(trim($_POST['forum_desc']));

	if (!isset($_POST['forum_seq']) || sp_esc_int($_POST['forum_seq'] == 0)) {
		$mess = spa_text('Unable to save until display position is set');
		return $mess;
	} else {
		$forumdata['forum_seq'] = sp_esc_int($_POST['forum_seq']);
	}

	$forumdata['group_id'] = sp_esc_int($_POST['group_id']);

	$forumdata['forum_status'] = 0;
	if (isset($_POST['forum_status'])) $forumdata['forum_status'] = 1;

	$forumdata['forum_rss_private'] = 0;
	if (isset($_POST['forum_private'])) $forumdata['forum_rss_private'] = 1;

	$forumdata['forum_keywords'] = sp_filter_title_save(trim($_POST['forum_keywords']));

	if (!empty($_POST['forum_icon'])) {
		# Check new icon exists
		$forumdata['forum_icon'] = sp_filter_title_save(trim($_POST['forum_icon']));
		$path = SFCUSTOMDIR.$forumdata['forum_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['forum_icon']);
			return $mess;
		}
	} else {
		$forumdata['forum_icon'] = NULL;
	}
	if (!empty($_POST['forum_icon_new'])) {
		# Check new icon exists
		$forumdata['forum_icon_new'] = sp_filter_title_save(trim($_POST['forum_icon_new']));
		$path = SFCUSTOMDIR.$forumdata['forum_icon_new'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['forum_icon_new']);
			return $mess;
		}
	} else {
		$forumdata['forum_icon_new'] = NULL;
	}

	if (!empty($_POST['topic_icon'])) {
		# Check new icon exists
		$forumdata['topic_icon'] = sp_filter_title_save(trim($_POST['topic_icon']));
		$path = SFCUSTOMDIR.$forumdata['topic_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['topic_icon']);
			return $mess;
		}
	} else {
		$forumdata['topic_icon'] = NULL;
	}
	if (!empty($_POST['topic_icon_new'])) {
		# Check new icon exists
		$forumdata['topic_icon_new'] = sp_filter_title_save(trim($_POST['topic_icon_new']));
		$path = SFCUSTOMDIR.$forumdata['topic_icon_new'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $forumdata['topic_icon_new']);
			return $mess;
		}
	} else {
		$forumdata['topic_icon_new'] = NULL;
	}

	if (isset($_POST['forum_rss'])) {
		$forumdata['forum_rss'] = sp_filter_save_cleanurl($_POST['forum_rss']);
	} else {
		$forumdata['forum_rss'] = sp_filter_save_cleanurl($_POST['cforum_rss']);
	}

	$forumdata['forum_message'] = sp_filter_text_save(trim($_POST['forum_message']));

	# has the forum changed to a new group
	if ($forumdata['group_id'] != $_POST['cgroup_id']) {
		# let's resequence old group list first
		$forums = spdb_table(SFFORUMS, "group_id=".sp_esc_int($_POST['cgroup_id'])." AND forum_id <> ".$forum_id, '', 'forum_seq');
		$cnt = count($forums);
		for ($i = 0; $i < $cnt; $i++) {
			spa_bump_forum_seq($forums[$i]->forum_id, ($i + 1));
		}

		# now we can make room in new group
		$seq = (spdb_max(SFFORUMS, 'forum_seq', "group_id=". $forumdata['group_id']) + 1);

		if ($forumdata['forum_seq'] < $seq) {
			$forums = spa_get_forums_in_group($forumdata['group_id']);
			foreach ($forums as $forum) {
				if ($forum->forum_seq >= $forumdata['forum_seq']) spa_bump_forum_seq($forum->forum_id, ($forum->forum_seq + 1));
			}
		}
	} else {
		# same group but has the seq changed?
		if ($forumdata['forum_seq'] != $_POST['cforum_seq']) {
			$forums = spdb_table(SFFORUMS, "group_id=".sp_esc_int($_POST['cgroup_id'])." AND forum_id <> ".$forum_id, '', 'forum_seq');

			$cnt = count($forums);
			for ($i = 0; $i < $cnt; $i++) {
				if (($i + 1) < $forumdata['forum_seq']) {
					spa_bump_forum_seq($forums[$i]->forum_id, ($i + 1));
				} else {
					spa_bump_forum_seq($forums[$i]->forum_id, ($i + 2));
				}
			}
		}
	}

	# Finally - we can save the updated forum record!
	if (empty($forumdata['forum_slug'])) {
		$forumslug = sp_create_slug($forumdata['forum_name'], true, SFFORUMS, 'forum_slug');
		$forumslug = sp_create_slug($forumslug, true, SFWPPOSTS, 'post_name'); # must also check WP posts table as WP can mistake forum slug for WP post
		if (empty($forumslug)) $forumslug = 'forum-'.$forum_id;
	} else {
		$forumslug = $forumdata['forum_slug'];
	}

	# Let's make sure parent is set
	if ($_POST['forumtype'] == 1) {
		$parent = 0;
	} else {
		$parent = sp_esc_int($_POST['forum_parent']);
	}

	$sql = 'UPDATE '.SFFORUMS.' SET ';
	$sql.= 'forum_name="'.$forumdata['forum_name'].'", ';
	$sql.= 'forum_slug="'.$forumslug.'", ';
	$sql.= 'forum_desc="'.$forumdata['forum_desc'].'", ';
	$sql.= 'group_id='.$forumdata['group_id'].', ';
	$sql.= 'forum_status='.$forumdata['forum_status'].', ';
	$sql.= 'forum_rss_private='.$forumdata['forum_rss_private'].', ';
	$sql.= 'forum_icon="'.$forumdata['forum_icon'].'", ';
	$sql.= 'forum_icon_new="'.$forumdata['forum_icon_new'].'", ';
	$sql.= 'topic_icon="'.$forumdata['topic_icon'].'", ';
	$sql.= 'topic_icon_new="'.$forumdata['topic_icon_new'].'", ';
	$sql.= 'forum_rss="'.$forumdata['forum_rss'].'", ';
	$sql.= 'parent='.$parent.', ';
	$sql.= 'forum_message="'.$forumdata['forum_message'].'", ';
	$sql.= 'forum_seq='.$forumdata['forum_seq'].", ";
	$sql.= 'keywords="'.$forumdata['forum_keywords'].'" ';
	$sql.= "WHERE forum_id=$forum_id";
	$success = spdb_query($sql);
	if ($success == false) 	{
		$mess = spa_text('Forum record update failed');
	} else {
		$mess = spa_text('Forum record update');

		do_action('sph_forum_forum_edit', $forum_id);
	}

	spa_clean_forum_children();
	spa_resequence_forums($forumdata['group_id'], 0);

	# if the slug as changed we can try and update internal links in posts
	if($_POST['cforum_slug'] != $forumslug) {
		sp_update_post_urls(sp_esc_str($_POST['cforum_slug']), $forumslug);
	}

	return $mess;
}

function spa_save_forums_edit_group() {
	check_admin_referer('forum-adminform_groupedit', 'forum-adminform_groupedit');

	$groupdata = array();
	$group_id = sp_esc_int($_POST['group_id']);
	$groupdata['group_name'] = sp_filter_title_save(trim($_POST['group_name']));
	$groupdata['group_seq'] = sp_filter_title_save(trim($_POST['group_seq']));
	$groupdata['group_desc'] = sp_filter_text_save(trim($_POST['group_desc']));
	$groupdata['group_message'] = sp_filter_text_save(trim($_POST['group_message']));

	$ug_list = array_unique($_POST['usergroup_id']);
	$perm_list = $_POST['role'];

	if (!empty($_POST['group_icon'])) {
		# Check new icon exists
		$groupdata['group_icon'] = sp_filter_title_save(trim($_POST['group_icon']));
		$path = SFCUSTOMDIR.$groupdata['group_icon'];
		if (!file_exists($path)) {
			$mess = sprintf(spa_text('Custom icon %s does not exist'), $groupdata['group_icon']);
			return $mess;
		}
	} else {
		$groupdata['group_icon'] = NULL;
	}

	if (isset($_POST['group_rss'])) {
		$groupdata['group_rss'] = sp_filter_save_cleanurl($_POST['group_rss']);
	} else {
		$groupdata['group_rss'] = sp_filter_save_cleanurl($_POST['cgroup_rss']);
	}

	# save the default permissions for the group
	for ($x=0; $x<count($ug_list); $x++) {
	    $ug = sp_esc_int($ug_list[$x]);
        $perm = sp_esc_int($perm_list[$x]);
		if (spa_get_defpermissions_role($group_id, $ug)) {
			$sql = 'UPDATE '.SFDEFPERMISSIONS."
					SET permission_role=$perm
					WHERE group_id=$group_id AND usergroup_id=$ug";
			spdb_query($sql);
		} else {
            if ($perm != -1) spa_add_defpermission_row($group_id, $ug, $perm);
		}
	}

	if ($groupdata['group_name'] == $_POST['cgroup_name'] &&
		$groupdata['group_seq'] == $_POST['cgroup_seq'] &&
		$groupdata['group_desc'] == $_POST['cgroup_desc'] &&
		$groupdata['group_rss'] == $_POST['cgroup_rss'] &&
		$groupdata['group_message'] == $_POST['cgroup_message'] &&
		$groupdata['group_icon'] == $_POST['cgroup_icon']) {
		$mess = spa_text('No data changed');
	} else {
		# has the sequence changed?
		if ($groupdata['group_seq'] != $_POST['cgroup_seq']) {
			# need to iterate through the groups to change sequence number
			$groups = spdb_table(SFGROUPS, "group_id <> $group_id", '', 'group_seq');
			$cnt = count($groups);
			for ($i = 0; $i < $cnt; $i++) {
				if (($i + 1) < $groupdata['group_seq']) {
					spa_bump_group_seq($groups[$i]->group_id, ($i + 1));
				} else {
					spa_bump_group_seq($groups[$i]->group_id, ($i + 2));
				}
			}
		}

		$sql = 'UPDATE '.SFGROUPS.' SET ';
		$sql.= 'group_name="'.$groupdata['group_name'].'", ';
		$sql.= 'group_desc="'.$groupdata['group_desc'].'", ';
		$sql.= 'group_icon="'.$groupdata['group_icon'].'", ';
		$sql.= 'group_rss="'.$groupdata['group_rss'].'", ';
		$sql.= 'group_message="'.$groupdata['group_message'].'", ';
		$sql.= 'group_seq='.$groupdata['group_seq']." ";
		$sql.= "WHERE group_id=$group_id";
		$success = spdb_query($sql);
		if ($success == false) {
			$mess = spa_text('Group record update failed');

			do_action('sph_forum_group_edit', $group_id);
		} else {
			$mess = spa_text('Forum group record updated');
		}
	}

	return $mess;
}

# function to update an existing permission set for a forum
function spa_save_forums_edit_perm() {
	check_admin_referer('forum-adminform_permissionedit', 'forum-adminform_permissionedit');

	$permissiondata = array();
	$permission_id = sp_esc_int($_POST['permission_id']);
	$permissiondata['permission_role'] = sp_esc_int($_POST['role']);

	# dont do anything if the permission set wasnt actually updated
	if ($permissiondata['permission_role'] == $_POST['ugroup_perm']) {
		$mess = spa_text('No data changed');
		return $mess;
	}

	# save the updated permission set info
	$sql = 'UPDATE '.SFPERMISSIONS.' SET ';
	$sql.= 'permission_role="'.$permissiondata['permission_role'].'" ';
	$sql.= "WHERE permission_id=$permission_id";
	$success = spdb_query($sql);
	if ($success == false) {
		$mess = spa_text('Permission set update failed');
	} else {
		$mess = spa_text('Permission set updated');

		# reset auths and memberships for everyone
		sp_reset_memberships();
		sp_reset_auths();

		do_action('sph_forum_perm_edit', $permission_id);
	}

	return $mess;
}

function spa_bump_group_seq($id, $seq) {
	$sql = 'UPDATE '.SFGROUPS.' SET ';
	$sql.= "group_seq=$seq ";
	$sql.= "WHERE group_id=$id";

	spdb_query($sql);
}

function spa_bump_forum_seq($id, $seq) {
	$sql = 'UPDATE '.SFFORUMS.' SET ';
	$sql.= "forum_seq=$seq ";
	$sql.= "WHERE forum_id=$id";

	spdb_query($sql);
}

function spa_add_permission_data($forum_id, $usergroup_id, $permission) {
	$forumid = esc_sql($forum_id);
	$usergroupid = esc_sql($usergroup_id);
	$perm = esc_sql($permission);

	$sql = 'INSERT INTO '.SFPERMISSIONS.' (forum_id, usergroup_id, permission_role) ';
	$sql.= "VALUES ('$forumid', '$usergroupid', '$perm')";
	return spdb_query($sql);
}

function spa_add_defpermission_row($group_id, $usergroup_id, $role) {
	$sql = 'INSERT INTO '.SFDEFPERMISSIONS." (group_id, usergroup_id, permission_role)
			VALUES ($group_id, $usergroup_id, $role)";
	return spdb_query($sql);
}

function spa_resequence_forums($groupid, $parent) {
	global $sequence;

	$forums = spa_get_group_forums_by_parent($groupid, $parent);
	if ($forums) {
		foreach ($forums as $forum) {
			$sequence++;
			spa_bump_forum_seq($forum->forum_id, $sequence);

			if ($forum->children) {
				$childlist = array(unserialize($forum->children));
				if (count($childlist) > 0) spa_resequence_forums($groupid, $forum->forum_id);
			}
		}
	}
}

function spa_clean_forum_children() {
	# Remove all child records from forums
	spdb_query('UPDATE '.SFFORUMS.' set children=""');

	# Now get ALL forums
	$forums = spdb_table(SFFORUMS);
	if ($forums) {
		foreach ($forums as $forum) {
	 		if ($forum->parent != 0) {
				$spdb = new spdbComplex;
					$spdb->table		= SFFORUMS;
					$spdb->fields		= 'children, group_id';
					$spdb->where		= 'forum_id='.$forum->parent;
				$childlist = $spdb->select();

				if (!empty($childlist[0]->children)) {
					$children = unserialize($childlist[0]->children);
				} else {
					$children = array();
				}
				$children[]=$forum->forum_id;
				spdb_query('UPDATE '.SFFORUMS." set children='".serialize($children)."' WHERE forum_id=$forum->parent");
				spdb_query('UPDATE '.SFFORUMS." set group_id=".$childlist[0]->group_id." WHERE forum_id=$forum->forum_id");
				spdb_flush();
			}
		}
	}
}

function spa_save_forums_global_rss() {
	check_admin_referer('forum-adminform_globalrss', 'forum-adminform_globalrss');

	# update the globla rss replacement url
	sp_update_option('sfallRSSurl', sp_filter_save_cleanurl($_POST['sfallrssurl']));
	$mess = spa_text('Global RSS settings updated');

	do_action('sph_forum_global_rss');

	return $mess;
}

function spa_save_forums_global_rssset() {
	check_admin_referer('forum-adminform_globalrssset', 'forum-adminform_globalrssset');

	$private = sp_esc_int($_POST['sfglobalrssset']);

	$sql = 'UPDATE '.SFFORUMS.' SET ';
	$sql.= "forum_rss_private=$private";
	$success = spdb_query($sql);

	do_action('sph_forum_rss');

	$mess = spa_text('Global RSS settings updated');

	return $mess;
}

function spa_save_forums_merge() {
	check_admin_referer('forum-adminform_mergeforums', 'forum-adminform_mergeforums');
	$source = $target = 0;
	if(isset($_POST['source'])) $source = (int) $_POST['source'];
	if(isset($_POST['target'])) $target = (int) $_POST['target'];
	if(empty($source) || empty($target) || ($source == $target)) {
		return spa_text('Selections invalid');
	}

	$sourceForum = spdb_table(SFFORUMS, "forum_id=$source", 'row');
	$targetForum = spdb_table(SFFORUMS, "forum_id=$target", 'row');

	# 1 - Move sub-forums
	if(!empty($sourceForum->children)) {
		spdb_query("UPDATE ".SFFORUMS." SET parent=$target WHERE parent=$source");
	}

	# 2 - Change forum ids in requirted tables
	spdb_query("UPDATE ".SFTOPICS." SET forum_id=$target WHERE forum_id=$source");
	spdb_query("UPDATE ".SFPOSTS." SET forum_id=$target WHERE forum_id=$source");
	spdb_query("UPDATE ".SFTRACK." SET forum_id=$target WHERE forum_id=$source");
	spdb_query("UPDATE ".SFWAITING." SET forum_id=$target WHERE forum_id=$source");

	# 3 - Delete forum id rows in following tables
	spdb_query("DELETE FROM ".SFPERMISSIONS." WHERE forum_id=$source");

	# 4 - Run clean up operations
	sp_reset_memberships();
	sp_reset_auths();
	sp_update_post_urls($sourceForum->forum_slug, $targetForum->forum_slug);
	sp_build_forum_index($target);

	# 5 - Delete the old forum record
	spdb_query("DELETE FROM ".SFFORUMS." WHERE forum_id=$source");
	spa_clean_forum_children();
	spa_resequence_forums($targetForum->group_id, 0);

	# 6 - Update Sitemap
	do_action('sm_rebuild');

	# 7 - Update Stats
	do_action('sph_stats_cron');

	# 8 - Let plugins in on the secret
	do_action('sph_merge_forums', $source, $target);

	$mess = spa_text('Forum Merge Completed');
	return $mess;
}

?>