<?php
/*
Simple:Press
DESC:
$LastChangedDate: 2013-10-03 16:43:12 -0700 (Thu, 03 Oct 2013) $
$Rev: 10780 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# 	SITE - This file loads at core level - all page loads
#	SP Base User handling Rourtines
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_load_current_user()
#
# Version: 5.0
# Filter Call
# Create the spThisUser object (the current user)
# ------------------------------------------------------------------
function sp_load_current_user() {
    global $current_user, $spThisUser, $spGuestCookie;

	if(empty($current_user)) $current_user = wp_get_current_user();
	$spThisUser = sp_get_user($current_user->ID, true);

	# check for a cookie if a guest
	$spGuestCookie = new stdClass();
	$spGuestCookie->guest_name = '';
	$spGuestCookie->guest_email = '';
	$spGuestCookie->display_name = '';
	if($spThisUser->guest && empty($spThisUser->offmember)) {
		# so no record of them being a current member
		$sfguests = sp_get_option('sfguests');
		if($sfguests['storecookie']) {
			if (isset($_COOKIE['guestname_'.COOKIEHASH])) $spGuestCookie->guest_name = sp_filter_name_display($_COOKIE['guestname_'.COOKIEHASH]);
			if (isset($_COOKIE['guestemail_'.COOKIEHASH])) $spGuestCookie->guest_email = sp_filter_email_display($_COOKIE['guestemail_'.COOKIEHASH]);
			$spGuestCookie->display_name = $spGuestCookie->guest_name;
		}
	}
}

# ------------------------------------------------------------------
# sp_create_member_data()
#
# Version: 5.0
# Filter Call
# On user registration sets up the new 'members' data row
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sp_create_member_data($userid) {
	if (!$userid) return;

    # see if member has already been created since wp multisite can fire both user creation hooks in some cases
	$user = spdb_table(SFMEMBERS, "user_id=$userid", 'row');
    if ($user) return;

	# Grab the data we need
	$sfprofile = sp_get_option('sfprofile');
	$user = spdb_table(SFUSERS, "ID=$userid", 'row');

	# Display Name validation
	$display_name = '';

	if ($sfprofile['nameformat']) {
		$display_name = $user->display_name;
	} else {
		$first_name = get_user_meta($userid, 'first_name', true);
		$last_name  = get_user_meta($userid, 'last_name', true);
		switch ($sfprofile['fixeddisplayformat']) {
			default:
			case '0':
				$display_name = $user->display_name;
				break;
			case '1':
				$display_name = $user->user_login;
				break;
			case '2':
				$display_name = $first_name;
				break;
			case '3':
				$display_name = $last_name;
				break;
			case '4':
				$display_name = $first_name.' '.$last_name;
				break;
			case '5':
				$display_name = $last_name.', '.$first_name;
				break;
			case '6':
				$display_name = $first_name[0].' '.$last_name;
				break;
			case '7':
				$display_name = $first_name.' '.$last_name[0];
				break;
			case '8':
				$display_name = $first_name[0].$last_name[0];
				break;
		}
	}

	# If the display name is empty for any reason, default to the username
	if (empty($display_name)) $display_name = $user->user_login;

	$display_name = apply_filters('sph_set_display_name', $display_name, $userid);
	$display_name = sp_filter_name_save($display_name);

	# now ensure it is unique
	$display_name = sp_unique_display_name($display_name, $display_name);

    # do we need to force user to change password?
	if ($sfprofile['forcepw']) add_user_meta($userid, 'sp_change_pw', true, true);

	$admin = 0;
	$moderator = 0;
	$avatar = 'a:1:{s:8:"uploaded";s:0:"";}';
	$signature = '';
	$posts = -1;
	$lastvisit = current_time('mysql');
	$checktime = current_time('mysql');
	$newposts = '';
	$admin_options = '';

	$useropts = array();
	$useropts['hidestatus'] = 0;

	$useropts['timezone'] = get_option('gmt_offset');
	if (empty($useropts['timezone'])) $useropts['timezone'] = 0;
	$tz = get_option('timezone_string');
	if (empty($tz) || substr($tz, 0, 3) == 'UTC') $tz = 'UTC';
	$useropts['timezone_string'] = $tz;

	$useropts['editor'] = 1;
	$useropts['namesync'] = 1;

    # unread posts
	$sfcontrols = sp_get_option('sfcontrols');
	$useropts['unreadposts'] = $sfcontrols['sfdefunreadposts'];

	$user_options = serialize($useropts);

	# generate feedkey
	$feedkey = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );

	# save initial record
	$sql = 'INSERT INTO '.SFMEMBERS."
		(user_id, display_name, admin, moderator, avatar, signature, posts, lastvisit, checktime, newposts, admin_options, user_options, feedkey)
		VALUES
		($userid, '$display_name', $admin, $moderator, '$avatar', '$signature', $posts, '$lastvisit', '$checktime', '$newposts', '$admin_options', '$user_options', '$feedkey')";
	spdb_query($sql);

	# insert new user into usergroup based on wp role
	sp_map_role_to_ug($userid);

	# update recent member list
	sp_push_newuser($userid, $display_name);

    do_action('sph_member_created', $userid);
}

# ------------------------------------------------------------------
# sp_update_member_data()
#
# Version: 5.0
# Filter Call
# On user wp profile updates, check if any spf stuff needs updating
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sp_update_member_data($userid) {
	if (!$userid) return '';

	# are we syncing display names between WP and SPF?
	$member = sp_get_member_row($userid);
    $options = unserialize($member['user_options']);
	if ($options['namesync']) {
		$display_name = sp_filter_name_save(spdb_table(SFUSERS, "ID=$userid", 'display_name'));
		sp_update_member_item($userid, 'display_name', $display_name);

        # update recent members list
		sp_update_newuser_name($member['display_name'], $display_name);
	}
}

# ------------------------------------------------------------------
# sp_map_role_to_ug()
#
# Version: 5.0
# helper function
# update usergroup memberships based on wp role
#	$userid:		id of user to check memberships
# ------------------------------------------------------------------
function sp_map_role_to_ug($userid, $role='') {
	# make sure user has been created first since wp role hook fires before create hook
	if (!$userid || !sp_get_member_item($userid, 'user_id')) return '';

	# get the user's wp role
	if (empty($role)) {
		$user = new WP_User($userid);
		$roles = $user->roles;
	} else {
		$roles = (array) $role;
	}

	# grab the user group for that wp role
	# if the role doesnt have a mapping, use the defaul user group for new members
	foreach ($roles as $role) {
		$value = sp_get_sfmeta('default usergroup', $role);
		if (!empty($value)) {
			$ug = $value[0]['meta_value'];
		} else {
			$value = sp_get_sfmeta('default usergroup', 'sfmembers');
			$ug = $value[0]['meta_value'];
		}

		sp_add_membership($ug, $userid);
	}
}

# ------------------------------------------------------------------
# sp_delete_member_data()
#
# Version: 5.0
# Filter Call
# On user deletion remove 'members' data row
#	$userid:		Passed in to filter
# ------------------------------------------------------------------
function sp_delete_member_data($userid) {
	if(!$userid) return '';

	# 1: get users email address
	$user_email = sp_filter_email_save(spdb_table(SFUSERS, "ID=$userid", 'user_email'));

	# 2: get the users display name from members table
	$display_name = sp_filter_name_save(sp_get_member_item($userid, 'display_name'));

	# 3: Set user name and email to guest name and meail in all of their posts
	spdb_query('UPDATE '.SFPOSTS." SET user_id=NULL, guest_name='$display_name', guest_email='$user_email' WHERE user_id=$userid");

	# 7: Remove from recent members list if present
	sp_remove_newuser($userid);

	# 8: Remove from Members table
	spdb_query('DELETE FROM '.SFMEMBERS." WHERE user_id=$userid");

	# 9: Remove user group memberships
	spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$userid");

    #10 check if forum moderator list needs updating
    sp_update_forum_moderators();

    do_action('sph_member_deleted', $userid);
}

# ------------------------------------------------------------------
# sp_add_membership()
#
# Version: 5.0
# Adds the specified user to the specified user group
#	$usergroup_id:		user group to which to add the user
#	$userid:			user to be added
# ------------------------------------------------------------------
function sp_add_membership($usergroup_id, $user_id) {
	# make sure we have valid membership to set
	if (empty($usergroup_id) || empty($user_id)) return false;

    # dont allow admins to be added to user groups
    if (sp_is_forum_admin($user_id)) return false;
	$success = false;

	# if only one membership allowed, remove all current memberships
	$sfmemberopts = sp_get_option('sfmemberopts');
	if ($sfmemberopts['sfsinglemembership']) spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$user_id");

	# dont add membership if it already exists
	$check = sp_check_membership($usergroup_id, $user_id);
	if (empty($check)) {
		$sql = 'INSERT INTO '.SFMEMBERSHIPS.' (user_id, usergroup_id) ';
		$sql.= "VALUES ('$user_id', '$usergroup_id');";
		$success = spdb_query($sql);

        # reset auths and memberships for added user
        sp_reset_memberships($user_id);
        sp_reset_auths($user_id);

	    sp_update_member_moderator_flag($user_id);
	}
	return $success;
}

# ------------------------------------------------------------------
# sp_remove_membership()
#
# Version: 5.0
# Removes the specified user from the specified user group
#	$usergroup_id:		user group to which to add the user
#	$userid:			user to be added
# ------------------------------------------------------------------
function sp_remove_membership($usergroup_id, $user_id) {
    spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$user_id AND usergroup_id=$usergroup_id");

    # reset auths and memberships for added user
    sp_reset_memberships($user_id);
    sp_reset_auths($user_id);

    sp_update_member_moderator_flag($user_id);

	return true;
}

# Version: 5.0
function sp_check_membership($usergroup_id, $user_id) {
	if (!$usergroup_id || !$user_id) return '';
	return spdb_table(SFMEMBERSHIPS, "user_id=$user_id AND usergroup_id=$usergroup_id", '', '', '', ARRAY_A);
}

# Version: 5.0
function sp_reset_memberships($userid = '') {
    # reset all the members memberships
    $where = '';
    if (!empty($userid)) $where = ' WHERE user_id='.$userid;

	spdb_query('UPDATE '.SFMEMBERS." SET memberships=''".$where);

    # reset guest auths if global update
    if (empty($userid)) sp_update_option('sf_guest_memberships', '');
}

# ------------------------------------------------------------------
# sp_update_member_moderator_flag()
#
# Version: 5.0
# checks an updates moderator flag for specified user
#	$userid:		User to lookup
# ------------------------------------------------------------------
function sp_update_member_moderator_flag($userid) {
	$ugs = sp_get_user_memberships($userid);
	if ($ugs) {
		foreach ($ugs as $ug) {
			$mod = spdb_table(SFUSERGROUPS, "usergroup_id={$ug['usergroup_id']}", 'usergroup_is_moderator');
			if ($mod) {
				sp_update_member_item($userid, 'moderator', 1);

                # see if our forum moderator list changed
                sp_update_forum_moderators();
				return;
			}
		}
	}

	# not a moderator if we get here
	sp_update_member_item($userid, 'moderator', 0);

}

# ------------------------------------------------------------------
# sp_update_forum_moderators()
#
# Version: 5.0
# updates the list of moderators for each forum
#	$forumid:		specific forum to update; otherwise does all
# ------------------------------------------------------------------
function sp_update_forum_moderators($forumid='') {
    if (empty($forumid)) {
        $forums = spdb_select('col', 'SELECT forum_id FROM '.SFFORUMS, ARRAY_A);
    } else {
        $forums = (array) $forumid;
    }
    if (empty($forums)) return;

    # udpate moderators list for each forum
    $mods = array();
    foreach ($forums as $forum) {
    	$sql = 'SELECT DISTINCT '.SFMEMBERSHIPS.'.user_id, display_name
    			FROM '.SFMEMBERSHIPS.'
    			JOIN '.SFUSERGROUPS.' ON '.SFUSERGROUPS.'.usergroup_id = '.SFMEMBERSHIPS.'.usergroup_id
    			JOIN '.SFPERMISSIONS.' ON '.SFPERMISSIONS.".forum_id = $forum AND ".SFMEMBERSHIPS.".usergroup_id = ".SFUSERGROUPS.'.usergroup_id
    			JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id
    			WHERE usergroup_is_moderator=1';
        $mods[$forum] = spdb_select('set', $sql, ARRAY_A);
    }

    sp_add_sfmeta('forum_moderators', 'users', $mods, 1);
}

# Version: 5.0
function sp_get_user_memberships($user_id) {
	if (!$user_id) return '';

	$sql = 'SELECT '.SFMEMBERSHIPS.'.usergroup_id, usergroup_name, usergroup_desc, usergroup_badge, usergroup_join
			FROM '.SFMEMBERSHIPS.'
			JOIN '.SFUSERGROUPS.' ON '.SFUSERGROUPS.'.usergroup_id = '.SFMEMBERSHIPS.".usergroup_id
			WHERE user_id=$user_id";
	return spdb_select('set', $sql, ARRAY_A);
}

# ------------------------------------------------------------------
# sp_get_forum_memberships()
#
# Version: 5.0
# Returns an indexed array of all forum ids the current user is
# allowed to view. Note: This includes admins.
# Uses the spThisUser object so is only valid for current user
# ------------------------------------------------------------------
function sp_get_forum_memberships() {
	global $spThisUser;
	if ($spThisUser->admin) {
		$sql = 'SELECT forum_id FROM '.SFFORUMS;
	} elseif ($spThisUser->guest) {
		$value = sp_get_sfmeta('default usergroup', 'sfguests');
		$sql = 'SELECT forum_id FROM '.SFPERMISSIONS." WHERE usergroup_id={$value[0]['meta_value']}";
	} else {
		$sql = 'SELECT forum_id
				FROM '.SFPERMISSIONS.'
				JOIN '.SFMEMBERSHIPS.' ON '.SFPERMISSIONS.'.usergroup_id = '.SFMEMBERSHIPS.'.usergroup_id
				WHERE user_id='.$spThisUser->ID;
	}
	$forums = spdb_select('set', $sql);
	$fids = array();
	if ($forums) {
		foreach ($forums as $thisForum) {
			if (sp_get_auth('view_forum', $thisForum->forum_id) ||
                sp_get_auth('view_forum_lists', $thisForum->forum_id) ||
                sp_get_auth('view_forum_topic_lists', $thisForum->forum_id)) {
                $fids[] = $thisForum->forum_id;
            }
		}
	}
	return $fids;
}

# ------------------------------------------------------------------
# sp_push_newuser()
#
# Version: 5.0
# Adds new user stats new user list
#	$name:		new users display name
# ------------------------------------------------------------------
function sp_push_newuser($id, $name) {
	$spControls = sp_get_option('sfcontrols');
	$num = $spControls['shownewcount'];
	if (empty($num)) $num = 0;

	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		# is this name already listed?
		foreach ($newuserlist as $user) {
			if ($user['name'] == $name) return;
		}

		# is the array full? if so pop one off
		$ccount = count($newuserlist);
		while ($ccount > ($num-1)) {
			array_pop($newuserlist);
			$ccount--;
		}

		# add new user
		array_unshift($newuserlist, array('id' => esc_sql($id), 'name' => esc_sql($name)));
	} else {
		# first name nto the emoty array
		$newuserlist[0]['id'] = esc_sql($id);
		$newuserlist[0]['name'] = esc_sql($name);
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_remove_newuser()
#
# Version: 5.0
# Removes new user from new user list
#	$id:		new users id
# ------------------------------------------------------------------
function sp_remove_newuser($id) {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		# remove the user if present
		foreach ($newuserlist as $index => $user) {
			if ($user['id'] == $id) unset($newuserlist[$index]);
		}
		$newuserlist = array_values($newuserlist);
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_update_newuser_name()
#
# Version: 5.0
# Updates display name of recent members if profile updated
#	$oldname:		users old name
#	$newname:		users new name
# ------------------------------------------------------------------
function sp_update_newuser_name($oldname, $newname) {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		for ($x=0; $x<count($newuserlist); $x++) {
			if ($newuserlist[$x]['name'] == $oldname) $newuserlist[$x]['name'] = $newname;
		}
	}
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_update_recent_members()
#
# Version: 5.3
# Updates display name of recent members list if profile option display name format changed
# -----------------------------------------------------------------------------------------
function sp_update_recent_members () {
	$newuserlist = sp_get_option('spRecentMembers');
	if (is_array($newuserlist)) {
		for ($x=0; $x<count($newuserlist); $x++) {
            $newuserlist[$x]['name'] = sp_get_member_item($newuserlist[$x]['id'], 'display_name');
        }
    }
	sp_update_option('spRecentMembers', $newuserlist);
}

# ------------------------------------------------------------------
# sp_check_unlogged_user()
#
# Version: 5.0
# checks if 'guest' is a user not logged in and returns their name
# ------------------------------------------------------------------
function sp_check_unlogged_user() {
	if (is_user_logged_in() == true) return;
	$sfmemberopts = sp_get_option('sfmemberopts');
	if (isset($_COOKIE['sforum_'.COOKIEHASH]) && $sfmemberopts['sfcheckformember']) {
		# Yes it is - a user not logged in
		$username = $_COOKIE['sforum_'.COOKIEHASH];
		return $username;
	}
	return;
}

# Version: 5.0
function sp_user_visible_forums() {
	global $spThisUser, $spGlobals;

	if (empty($spThisUser->auths)) return '';

	$forum_ids = '';
	foreach ($spThisUser->auths as $forum => $forum_auth) {
//		if ($forum != 'global' && $forum_auth[$spGlobals['auths_map']['view_forum']]) $forum_ids[] = $forum;
		if ($forum != 'global' && sp_can_view($forum, 'forum-title')) $forum_ids[] = $forum;
	}
	return $forum_ids;
}

# ------------------------------------------------------------------
# sp_validate_user()
#
# Version: 5.2
# checks account name user is attempting to regsiter against a blacklist of unallowed account names
# ------------------------------------------------------------------
function sp_validate_registration($errors, $sanitized_user_login, $user_email) {
    $blockedAccounts = sp_get_option('account-name');
    if (!empty($blockedAccounts)) {
        $names = explode(',', $blockedAccounts);
        foreach ($names as $name) {
            if (strtolower(trim($name)) == strtolower($sanitized_user_login)) {
                $errors->add('login_blacklisted', '<strong>'.sp_text('ERROR').'</strong>: '.sp_text('The account name you have chosen is not allowed on this site'));
                break;
            }
        }
    }
    return $errors;
}

# ------------------------------------------------------------------
# sp_validate_display_name()
#
# Version: 5.2
# checks account name user is attempting to regsiter against a blacklist of unallowed account names
# ------------------------------------------------------------------
function sp_validate_display_name($errors, $update, $user) {
    $blockedDisplay = sp_get_option('display-name');
    if (!empty($blockedDisplay)) {
        $names = explode(',', $blockedDisplay);
        foreach ($names as $name) {
            if (strtolower(trim($name)) == strtolower($user->display_name)) {
                $errors->add('display_name_blacklisted', '<strong>'.sp_text('ERROR').'</strong>: '.sp_text('The display name you have chosen is not allowed on this site'));
                break;
            }
        }
    }
    return $errors;
}

# ------------------------------------------------------------------
# sp_unique_display_name()
#
# Version: 5.3.2
# checks display name is unique and if not adds a number on the end
# ------------------------------------------------------------------
function sp_unique_display_name($startname, $modname, $suffix=1) {
	$check = true;
	while($check) {
		$check = spdb_table(SFMEMBERS, "display_name='$modname'");
		if ($check) {
			$modname = $startname.'_'.$suffix;
			$suffix++;
		}
	}
	return $modname;
}

?>