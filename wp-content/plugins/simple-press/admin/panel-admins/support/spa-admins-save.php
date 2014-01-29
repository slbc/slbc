<?php
/*
Simple:Press
Admin Admins Update Your Options Support Functions
$LastChangedDate: 2013-08-11 13:53:53 -0700 (Sun, 11 Aug 2013) $
$Rev: 10509 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_save_admins_your_options_data() {
	global $spThisUser;

    check_admin_referer('my-admin_options', 'my-admin_options');

	# admin settings group
	$sfadminoptions = '';
    $sfadminoptions['sfnotify'] = isset($_POST['sfnotify']);
    $sfadminoptions['bypasslogout'] = isset($_POST['bypasslogout']);
    $sfadminoptions['notify-edited'] = isset($_POST['notify-edited']);
	$sfadminoptions['sfstatusmsgtext'] = sp_filter_text_save(trim($_POST['sfstatusmsgtext']));

	$sfadminoptions = apply_filters('sph_admin_your_options_change', $sfadminoptions);

	sp_update_member_item($spThisUser->ID, 'admin_options', $sfadminoptions);

	$mess = spa_text('Your admin options have been updated');

	# do we update moderator options as well?
	if($spThisUser->admin && isset($_POST['setmods'])) {
		$mods = sp_get_option('spModStats');
		if($mods) {
			$sfadminoptions['sfstatusmsgtext'] = '';
			foreach ($mods as $mod) {
				sp_update_member_item($mod['user_id'], 'admin_options', $sfadminoptions);
			}
			$mess.= '<br />'.spa_text('Your moderators options have been updated');
		}
	}

    do_action('sph_admin_your_options_save');

	return $mess;
}

function spa_save_admins_global_options_data() {
    check_admin_referer('global-admin_options', 'global-admin_options');

	# admin settings group
	$sfadminsettings = array();
    $sfadminsettings['sfdashboardstats'] = isset($_POST['sfdashboardstats']);
    $sfadminsettings['sfadminapprove'] = isset($_POST['sfadminapprove']);
    $sfadminsettings['sfmoderapprove'] = isset($_POST['sfmoderapprove']);
    $sfadminsettings['editnotice'] = isset($_POST['editnotice']);
    $sfadminsettings['movenotice'] = isset($_POST['movenotice']);
	sp_update_option('sfadminsettings', $sfadminsettings);

    do_action('sph_admin_global_options_save');

	$mess = spa_text('Admin global options updated');
	return $mess;
}

function spa_save_admins_caps_data() {
	global $spThisUser;

    check_admin_referer('forum-adminform_sfupdatecaps', 'forum-adminform_sfupdatecaps');

    $users = array_unique($_POST['uids']);

    if (isset($_POST['remove-admin'])) { $remove_admin = $_POST['remove-admin']; } else { $remove_admin = ''; }

    if (isset($_POST['manage-opts'])) { $manage_opts = $_POST['manage-opts']; } else { $manage_opts = ''; }
    if (isset($_POST['manage-forums'])) { $manage_forums = $_POST['manage-forums']; } else { $manage_forums = ''; }
    if (isset($_POST['manage-ugs'])) { $manage_ugs = $_POST['manage-ugs']; } else { $manage_ugs = ''; }
    if (isset($_POST['manage-perms'])) { $manage_perms = $_POST['manage-perms']; } else { $manage_perms = ''; }
    if (isset($_POST['manage-comps'])) { $manage_comps = $_POST['manage-comps']; } else { $manage_comps = ''; }
    if (isset($_POST['manage-users'])) { $manage_users = $_POST['manage-users']; } else { $manage_users = ''; }
    if (isset($_POST['manage-profiles'])) { $manage_profiles = $_POST['manage-profiles']; } else { $manage_profiles = ''; }
    if (isset($_POST['manage-admins'])) { $manage_admins = $_POST['manage-admins']; } else { $manage_admins = ''; }
    if (isset($_POST['manage-tools'])) { $manage_tools = $_POST['manage-tools']; } else { $manage_tools = ''; }
    if (isset($_POST['manage-plugins'])) { $manage_plugins = $_POST['manage-plugins']; } else { $manage_plugins = ''; }
    if (isset($_POST['manage-themes'])) { $manage_themes = $_POST['manage-themes']; } else { $manage_themes = ''; }

    if (isset($_POST['old-opts'])) { $old_opts = $_POST['old-opts']; } else { $old_opts = ''; }
    if (isset($_POST['old-forums'])) { $old_forums = $_POST['old-forums']; } else { $old_forums = ''; }
    if (isset($_POST['old-ugs'])) { $old_ugs = $_POST['old-ugs']; } else { $old_ugs = ''; }
    if (isset($_POST['old-perms'])) { $old_perms = $_POST['old-perms']; } else { $old_perms = ''; }
    if (isset($_POST['old-comps'])) { $old_comps = $_POST['old-comps']; } else { $old_comps = ''; }
    if (isset($_POST['old-users'])) { $old_users = $_POST['old-users']; } else { $old_users = ''; }
    if (isset($_POST['old-profiles'])) { $old_profiles = $_POST['old-profiles']; } else { $old_profiles = ''; }
    if (isset($_POST['old-admins'])) { $old_admins = $_POST['old-admins']; } else { $old_admins = ''; }
    if (isset($_POST['old-tools'])) { $old_tools = $_POST['old-tools']; } else { $old_tools = ''; }
    if (isset($_POST['old-plugins'])) { $old_plugins = $_POST['old-plugins']; } else { $old_plugins = ''; }
    if (isset($_POST['old-themes'])) { $old_themes = $_POST['old-themes']; } else { $old_themes = ''; }

	$data_changed = false;
    for ($index = 0; $index < count($users); $index++) {
		# get user index and sanitize
		$uid = intval($users[$index]);
		$user = new WP_User($uid);

        # do we need to remove all admin caps for user?
        if (isset($remove_admin[$uid])) {
            unset($manage_opts[$uid]);
            unset($manage_forums[$uid]);
            unset($manage_ugs[$uid]);
            unset($manage_perms[$uid]);
            unset($manage_comps[$uid]);
            unset($manage_users[$uid]);
            unset($manage_profiles[$uid]);
            unset($manage_admins[$uid]);
            unset($manage_tools[$uid]);
            unset($manage_plugins[$uid]);
            unset($manage_themes[$uid]);
        }

		# Is user still an admin?
		$still_admin = (isset($manage_opts[$uid]) ||
		    			isset($manage_forums[$uid]) ||
		    			isset($manage_ugs[$uid]) ||
		    			isset($manage_perms[$uid]) ||
	    				isset($manage_comps[$uid]) ||
		    			isset($manage_users[$uid]) ||
		    			isset($manage_profiles[$uid]) ||
		    			isset($manage_admins[$uid]) ||
		    			isset($manage_tools[$uid]) ||
		    			isset($manage_plugins[$uid]) ||
		    			isset($manage_themes[$uid]));
		$still_admin = apply_filters('sph_admin_caps_update', $still_admin, $remove_admin, $user);
		if (empty($still_admin)) sp_update_member_item($uid, 'admin', 0);

		if (isset($manage_opts[$uid])) {
			$user->add_cap('SPF Manage Options');
		} else {
			$user->remove_cap('SPF Manage Options');
		}

		if (isset($manage_forums[$uid])) {
			$user->add_cap('SPF Manage Forums');
		} else {
			$user->remove_cap('SPF Manage Forums');
		}

		if (isset($manage_ugs[$uid])) {
			$user->add_cap('SPF Manage User Groups');
		} else {
			$user->remove_cap('SPF Manage User Groups');
		}

		if (isset($manage_perms[$uid])) {
			$user->add_cap('SPF Manage Permissions');
		} else {
			$user->remove_cap('SPF Manage Permissions');
		}

		if (isset($manage_comps[$uid])) {
			$user->add_cap('SPF Manage Components');
		} else {
			$user->remove_cap('SPF Manage Components');
		}

		if (isset($manage_users[$uid])) {
			$user->add_cap('SPF Manage Users');
		} else {
			$user->remove_cap('SPF Manage Users');
		}

		if (isset($manage_profiles[$uid])) {
			$user->add_cap('SPF Manage Profiles');
		} else {
			$user->remove_cap('SPF Manage Profiles');
		}

		if (isset($manage_admins[$uid])) {
			$user->add_cap('SPF Manage Admins');
		} else {
			$user->remove_cap('SPF Manage Admins');
		}

		if (isset($manage_tools[$uid])) {
			$user->add_cap('SPF Manage Toolbox');
		} else {
			$user->remove_cap('SPF Manage Toolbox');
		}

		if (isset($manage_plugins[$uid])) {
			$user->add_cap('SPF Manage Plugins');
		} else {
			$user->remove_cap('SPF Manage Plugins');
		}

		if (isset($manage_themes[$uid])) {
			$user->add_cap('SPF Manage Themes');
		} else {
			$user->remove_cap('SPF Manage Themes');
		}

        # reset auths and memberships for updated admins
        sp_reset_memberships($uid);
        sp_reset_auths($uid);
	}

    do_action('sph_admin_update_save');

    $mess = spa_text('Admin capabilities updated!');
    return $mess;
}

function spa_save_admins_newadmin_data() {
    check_admin_referer('forum-adminform_sfaddadmins', 'forum-adminform_sfaddadmins');

    if (isset($_POST['member_id'])) {
		$newadmins = array_unique($_POST['member_id']);
	} else {
	    $mess = spa_text('No users selected!');
		return $mess;
    }

    if (isset($_POST['add-opts'])) { $opts = $_POST['add-opts']; } else { $opts = ''; }
    if (isset($_POST['add-forums'])) { $forums = $_POST['add-forums']; } else { $forums = ''; }
    if (isset($_POST['add-ugs'])) { $ugs = $_POST['add-ugs']; } else { $ugs = ''; }
    if (isset($_POST['add-perms'])) { $perms = $_POST['add-perms']; } else { $perms = ''; }
    if (isset($_POST['add-comps'])) { $comps = $_POST['add-comps']; } else { $comps = ''; }
    if (isset($_POST['add-users'])) { $users = $_POST['add-users']; } else { $users = ''; }
    if (isset($_POST['add-profiles'])) { $profiles = $_POST['add-profiles']; } else { $profiles = ''; }
    if (isset($_POST['add-admins'])) { $admins = $_POST['add-admins']; } else { $admins = ''; }
    if (isset($_POST['add-tools'])) { $tools = $_POST['add-tools']; } else { $tools = ''; }
    if (isset($_POST['add-plugins'])) { $plugins = $_POST['add-plugins']; } else { $plugins = ''; }
    if (isset($_POST['add-themes'])) { $themes = $_POST['add-themes']; } else { $themes = ''; }

	$added = false;
    for ($index = 0; $index < count($newadmins); $index++) {
		# get user index and sanitize
		$uid = intval($newadmins[$index]);
		$user = new WP_User(sp_esc_int($uid));

		if ($opts == 'on') $user->add_cap('SPF Manage Options');
		if ($forums == 'on') $user->add_cap('SPF Manage Forums');
		if ($ugs == 'on') $user->add_cap('SPF Manage User Groups');
		if ($perms == 'on') $user->add_cap('SPF Manage Permissions');
		if ($comps == 'on') $user->add_cap('SPF Manage Components');
		if ($users == 'on') $user->add_cap('SPF Manage Users');
		if ($profiles == 'on') $user->add_cap('SPF Manage Profiles');
		if ($admins == 'on') $user->add_cap('SPF Manage Admins');
		if ($tools == 'on') $user->add_cap('SPF Manage Toolbox');
		if ($plugins == 'on') $user->add_cap('SPF Manage Plugins');
		if ($themes == 'on') $user->add_cap('SPF Manage Themes');

		$newadmin =  $opts == 'on' || $forums == 'on' || $ugs == 'on' || $perms == 'on' || $comps == 'on' || $users == 'on'|| $profiles == 'on'|| $admins == 'on' || $tools == 'on' || $plugins == 'on' || $themes == 'on';
		$newadmin = apply_filters('sph_admin_caps_new', $newadmin, $user);
		if ($newadmin) {
			$added = true;

			# flag as admin with remove moderator flag
			sp_update_member_item($uid, 'admin', 1);
			sp_update_member_item($uid, 'moderator', 0);

            # admin default options
        	$sfadminoptions = array();
            $sfadminoptions['sfnotify'] = false;
            $sfadminoptions['sfstatusmsgtext'] = '';
            sp_update_member_item($uid, 'admin_options', $sfadminoptions);

			# remove any usergroup permissions
			spdb_query('DELETE FROM '.SFMEMBERSHIPS." WHERE user_id=$uid");
		}

        # reset auths and memberships for new admins
        sp_reset_memberships($uid);
        sp_reset_auths($uid);
	}

    do_action('sph_admin_new_save');

	if ($added) {
	    $mess = spa_text('New admins added!');
 	} else {
		$mess = spa_text('No data changed!');
	}

	return $mess;
}
?>