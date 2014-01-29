<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2013-09-22 11:17:27 -0700 (Sun, 22 Sep 2013) $
$Rev: 10716 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==================================================================
#
# 	CORE: This file is loaded at CORE
#	SP Common Display Elements - shared front/back end
#
# ==================================================================

# Version: 5.0
function sp_render_group_forum_select($goURL=false, $valueURL=false, $showSelects=true, $showFirstRow=true, $firstRowLabel='', $id='', $class='', $length=40) {
    include_once (SF_PLUGIN_DIR.'/forum/content/classes/sp-group-view-class.php');
	if (empty($firstRowLabel)) $firstRowLabel = sp_text('Select Forum');
	if (empty($class)) {
		$class= 'spControl';
		$indent = '&nbsp;&nbsp;';
	} else {
		$indent = '';
	}

	# load data and check if empty or denied
	$groups = new spGroupView('', false);
	if ($groups->groupViewStatus == 'no access' || $groups->groupViewStatus == 'no data') return;
    $level = 0;
    $out = '';

	if ($groups->pageData) {
		if ($showSelects) {
			$out = "<select id='$id' class='$class' name='$id' ";
    		if ($goURL) $out.= 'onchange="javascript:spjChangeURL(this)"';
    		$out.= ">\n";
		}
		if ($showFirstRow && $firstRowLabel) $out.= '<option>'.$firstRowLabel.'</option>'."\n";
		foreach ($groups->pageData as $group) {
			$out.= '<optgroup class="spList" label="'.$indent.sp_create_name_extract($group->group_name).'">'."\n";
			if ($group->forums) {
				foreach ($group->forums as $forum) {
					if ($valueURL) {
						$out.= '<option value="'.$forum->forum_permalink.'">';
					} else {
						$out.= '<option value="'.$forum->forum_id.'">';
					}
					$out.= str_repeat($indent, $level).sp_create_name_extract($forum->forum_name, $length).'</option>'."\n";
					if (!empty($forum->subforums)) $out.= sp_compile_forums($forum->subforums, $forum->forum_id, 1, $valueURL);
				}
			}
			$out.= '</optgroup>';
		}
		if ($showSelects) $out.='</select>'."\n";
	}
	return $out;
}

# Version: 5.0
function sp_compile_forums($forums, $parent=0, $level=0, $valueURL=false) {
	$out = '';
	$indent = '&nbsp;&rarr;&nbsp;';
	foreach ($forums as $forum) {
		if ($forum->parent == $parent && $forum->forum_id != $parent) {
			if ($valueURL) {
				$out.= '<option value="'.$forum->forum_permalink.'">';
			} else {
				$out.= '<option value="'.$forum->forum_id.'">';
			}
			$out.= str_repeat($indent, $level).sp_create_name_extract($forum->forum_name).'</option>'."\n";
			if (!empty($forum->children)) $out.= sp_compile_forums($forums, $forum->forum_id, $level+1, $valueURL);
		}
	}
	return $out;
}

# ------------------------------------------------------------------
# sp_create_name_extract()
#
# Version: 5.0
# truncates a forum or topic name for display in Quicklinks
#	$name:		name of forum or topic
#	$length:	optional length - defaults to 40 characters
# ------------------------------------------------------------------
function sp_create_name_extract($name, $length=40) {
	$name = sp_filter_title_display($name);
	if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';
	return $name;
}

# ------------------------------------------------------------------
# sp_truncate()
#
# Version: 5.0
# truncates a forum or topic name for display and adds ellipsis
#	$name:		name of forum or topic
#	$length:	length to truncate to (required)
# ------------------------------------------------------------------
function sp_truncate($name, $length) {
	if($length > 0) {
		if (strlen($name) > $length) $name = substr($name, 0, $length).'&#8230;';
	}
	return $name;
}

# ------------------------------------------------------------------
# sp_get_user_special_ranks()
#
# Version: 5.0
# returns an array of user special ranks
#
#	$userid:	user id to get the special rank
# ------------------------------------------------------------------
function sp_get_user_special_ranks($userid) {
	global $spPaths, $spGlobals;

	$userRanks = array();
	$memberRanks = sp_get_special_rank($userid);
    if (empty($spGlobals['special_rank']) || empty($memberRanks)) return $userRanks;

	$count = 0;
	foreach ($spGlobals['special_rank'] as $key => $rank) {
		if (is_array($memberRanks) && in_array($key, $memberRanks)) {
			$userRanks[$count]['badge'] = '';
			if ($rank['badge'] && file_exists(SF_STORE_DIR.'/'.$spPaths['ranks'].'/'.$rank['badge'])) {
				$userRanks[$count]['badge'] = esc_url(SFRANKS.$rank['badge']);
			}
			$userRanks[$count]['name'] = $key;
			$count++;
		}
	}
	return $userRanks;
}

# ------------------------------------------------------------------
# sp_get_user_forum_rank()
#
# Version: 5.0
# returns an array (single element) of the user/guest forum rank
# based on the post count
#
#	$usertype:	use type - can be admin, user or guest
#	$userid:	user id to get the special rank
#	$userposts:	if user, number of posts made
# ------------------------------------------------------------------
function sp_get_user_forum_rank($usertype, $userid, $userposts) {
	global $spPaths, $spGlobals;

	$forumRank = array();
	$forumRank[0]['badge'] = '';

	switch ($usertype) {
		case 'Admin':
			$forumRank[0]['name'] = sp_text('Admin').' ';
			break;

		case 'Moderator':
			$forumRank[0]['name'] = sp_text('Moderator').' ';
			break;

		case 'User':
			$forumRank[0]['name'] = sp_text('Member').' ';
			break;

		case 'Guest':
			$forumRank[0]['name'] = sp_text('Guest').' ';
			break;
	}

	# check for forum rank
	$rankdata =  array();
	if ($usertype == 'User' && !empty($spGlobals['forum_rank'])) {
		# put into arrays to make easy to sort
		$index = 0;
		foreach ($spGlobals['forum_rank'] as $x => $info) {
			$rankdata['title'][$index] = $x;
			$rankdata['posts'][$index] = $info['posts'];
			$rankdata['badge'][$index] = '';
			if (isset($info['badge'])) $rankdata['badge'][$index] = $info['badge'];
			$index++;
		}
		# sort rankings
		array_multisort($rankdata['posts'], SORT_ASC, $rankdata['title'], $rankdata['badge']);

		# find ranking of current user
		for ($x=0; $x<count($rankdata['posts']); $x++) {
			if ($userposts <= $rankdata['posts'][$x]) {
				if ($rankdata['badge'][$x] && file_exists(SF_STORE_DIR.'/'.$spPaths['ranks'].'/'.$rankdata['badge'][$x])) {
					$forumRank[0]['badge'] = esc_url(SFRANKS.$rankdata['badge'][$x]);
				}
				$forumRank[0]['name'] = $rankdata['title'][$x];
				break;
			}
		}
	}

	return $forumRank;
}

# ------------------------------------------------------------------
# sp_build_avatar_display()
#
# Version: 5.0
# Will attach profile, website or nothing to avatar
#	userid:		id of the user
#	avatar:		Avatar display code
#   link:       attachment to make (profile, website, none)
# ------------------------------------------------------------------
function sp_build_avatar_display($userid, $avatar, $link) {
	global $spVars;

	switch ($link) {
		case 'profile':
			# for profiles, do we have a user and can current user view a profile?
			$forumid = (!empty($spVars['forumid'])) ? $spVars['forumid'] : '';
			if (!empty($userid) && sp_get_auth('view_profiles', $forumid)) $avatar = sp_attach_user_profile_link($userid, $avatar);
			break;

		case 'website':
			# for website, do we have a user?
			if (!empty($userid)) $avatar = sp_attach_user_web_link($userid, $avatar);
			break;

		default:
			# fire action for plugins that might add other display type
			$avatar = apply_filters('sph_BuildAvatarDisplay_'.$link, $avatar, $userid);
			break;
	}

	$avatar = apply_filters('sph_BuildAvatarDisplay', $avatar, $userid);
	return $avatar;
}

# ------------------------------------------------------------------
# sp_attach_user_web_link()
#
# Version: 5.0
# Create a link to a users website if they have entered one in their
# profile record.
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
#	returnitem:	return targetitem if nothing found
# ------------------------------------------------------------------
function sp_attach_user_web_link($userid, $targetitem, $returnitem=true) {
	global $session_weblink;

	# is the website url cached?
	$webSite = (empty($session_weblink[$userid])) ? $webSite = spdb_table(SFUSERS, "ID=$userid", 'user_url') : $session_weblink[$userid];
	if (empty($webSite)) $webSite = '#';

	# update cache (may be same)
	$session_weblink[$userid] = $webSite;

	# now attach the website url - ignoring if not defined
	if ($webSite != '#') {
		$webSite = sp_check_url($webSite);
		if (!empty($webSite)) {
			$content = "<a href='$webSite' class='spLink spWebLink' title=''>$targetitem</a>";
			$sffilters = sp_get_option('sffilters');
			if ($sffilters['sftarget']) $content = sp_filter_save_target($content);
			if ($sffilters['sfnofollow']) $content = sp_filter_save_nofollow($content);
			return $content;
		}
	}

	# No wesbite link exists
	if ($returnitem) {
		return $targetitem;
	} else {
		return '';
	}
}

# ------------------------------------------------------------------
# sp_attach_user_profile_link()
#
# Version: 5.0
# Create a link to a users profile using the global profile display
# settings
#	userid:		id of the user
#	targetitem:	user name, avatar or web icon - sent as code
# ------------------------------------------------------------------
function sp_attach_user_profile_link($userid, $targetitem) {
	if (!sp_get_auth('view_profiles')) return $targetitem;

	$sfprofile = sp_get_option('sfprofile');
	switch ($sfprofile['displaymode']) {
		case 1:
			# SF Popup profile
			$site = SFHOMEURL.'index.php?sp_ahah=profile&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;action=popup&amp;user=$userid";
			$title = sp_text('Profile');
			$position = 'center';
			return "<a rel='nofollow' href='javascript:void(null)' class='spLink spProfilePopupLink vtip' title='$title' onclick='spjDialogAjax(this, \"$site\", \"$title\", 750, 0, \"$position\");'>$targetitem</a>";
			break;

		case 2:
			# SF Profile page
			$user = new WP_User($userid);
			$site = sp_url('profile/'.urlencode($user->user_login));
			return "<a href='$site' class='spLink spProfilePage vtip' title='$user->user_login'>$targetitem</a>";
			break;

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);

            # try to handle BP switches between username and login ussge
    		$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
            if (strstr($username, ' ')) {
                $username = $user->user_nicename;
            } else {
                $username = urlencode($username);
            }
			$site = SFSITEURL.user_trailingslashit('members/'.str_replace(' ', '', $username).'/profile');
            $site = apply_filters('sph_buddypress_profile', $site, $user);
			return "<a href='$site' class='spLink spBPProfile vtip' title='$user->user_login'>$targetitem</a>";
			break;

		case 4:
			# WordPress authors page
			$userkey = spdb_table(SFUSERS, "ID=$userid", 'user_nicename');
			if ($userkey) {
				$site = SFSITEURL.user_trailingslashit('author/'.$userkey);
				return "<a href='$site' class='spLink spWPProfile vtip' title='$userkey'>$targetitem</a>";
			} else {
				return $targetitem;
			}
			break;

		case 5:
			# Handoff to user specified page
			if ($sfprofile['displaypage']) {
				$title = sp_text('Profile');
				$out = "<a href='".$sfprofile['displaypage'];
				if ($sfprofile['displayquery']) $out.= '?'.sp_filter_title_display($sfprofile['displayquery']).'='.$userid;
				$out.= "' class='spLink spUserDefinedProfile vtip' title='$title'>$targetitem</a>";
			} else {
				$out = $targetitem;
			}
			return $out;
			break;

		case 6:
			# Mingle profile page
			$user = new WP_User($userid);
			$site = SFSITEURL.user_trailingslashit(urlencode($user->user_login));
            $site = apply_filters('sph_mingle_profile', $site, $user);
			return "<a href='$site' class='spLink spMingleProfile vtip' title='$user->user_login'>$targetitem</a>";
			break;

		default:
			# plugins offering new type?
			$targetitem = apply_filters('AttachUserProfileLink_'.$sfprofile['displaymode'], $targetitem, $userid);
			return $targetitem;
			break;
	}
}

# ------------------------------------------------------------------
# sp_build_name_display()
#
# Version: 5.0
# Cleans user name and attaches profile or website link if set
#	$userid:		id of the user
#	$username:		name of the user or guest
#	$stats:			Optional - if stats set to true
# ------------------------------------------------------------------
function sp_build_name_display($userid, $username, $stats=false) {
	global $spThisUser, $spVars;

	$username = apply_filters('sph_build_name_display', $username, $userid);

	if ($userid && sp_get_auth('view_profiles')) {
		$profile = sp_get_option('sfprofile');


		# is profile linked to user name
		if (($profile['profilelink'] == 1 || ($profile['profileinstats'] == 1 && $stats == true))) {
			# link to profile
			return sp_attach_user_profile_link($userid, $username);
		} elseif($profile['weblink'] == 1) {
			# link to website
			return sp_attach_user_web_link($userid, $username);
		}
	}

	# neither permission or profile/web link
	return $username;
}

# ------------------------------------------------------------------
# sp_build_profile_formlink()
#
# Version: 5.0
# Create a link to the profile form preferred
#	$userid:		id of the user
# ------------------------------------------------------------------
function sp_build_profile_formlink($userid) {
	global $spThisUser;

	$sfprofile = sp_get_option('sfprofile');
	switch ($sfprofile['formmode']) {
		case 1:
			# SPF form
			$edit = '';
			if ($userid != $spThisUser->ID) {
				$user = new WP_User($userid);
				$edit = urlencode($user->user_login).'edit';
			}
			$site = sp_url('profile/'.$edit);
			return $site;
			break;

		case 2:
			# WordPress form
			return SFHOMEURL.'wp-admin/user-edit.php?user_id='.$userid;
			break;

		case 3:
			# BuddyPress profile page
			$user = new WP_User($userid);
            # try to handle BP switches between username and login ussge
    		$username = bp_is_username_compatibility_mode() ? $user->user_login : $user->user_nicename;
            if (strstr($username, ' ')) {
                $username = $user->user_nicename;
            } else {
                $username = urlencode($username);
            }
			$site = SFSITEURL.'members/'.str_replace(' ', '', $username).'/profile/edit/';
            $site = apply_filters('sph_buddypress_profile', $site, $user);
			return $site;
			break;

		case 4:
			# Handoff to user specified form
			if ($sfprofile['formpage']) {
				$out = $sfprofile['formpage'];
				if ($sfprofile['formquery']) $out.= '?'.sp_filter_title_display($sfprofile['formquery']).'='.$userid;
			} else {
				$out = '';
			}
			return $out;
			break;

		case 5:
			# Mingle account page
			$user = new WP_User($userid);
			$site = SFSITEURL.user_trailingslashit('account');
            $site = apply_filters('sph_mingle_profile', $site, $user);
			return $site;
			break;

	}
}

# ------------------------------------------------------------------
# sp_date()
#
# Version: 5.0
# Formats a date and time for display
#	$type	't'=time  'd'=date
#	$data	The actual date string
# ------------------------------------------------------------------
function sp_date($type, $data) {
	if ($type == 'd') {
		return date_i18n(SFDATES, mysql2date('U', $data, false));
	} else {
		return date_i18n(SFTIMES, mysql2date('U', $data, false));
	}
}

# ------------------------------------------------------------------
# sp_get_topic_url()
#
# Version: 5.0
# Builds a topic url including all icons etc
#	$forumslug:		forum slug for url
#	$topicslug:		topic slug for url
#	etc.
# ------------------------------------------------------------------
function sp_get_topic_url($forumslug, $topicslug, $topicname) {
	global $spVars;

    $out = '';
	$topicname=sp_filter_title_display($topicname);
	if (isset($spVars['searchvalue']) && $spVars['searchvalue']) {
		$out.= '<a href="'.sp_build_url($forumslug, $topicslug, 1, 0);
		if (strpos(sp_url(), '?') === false) {
			$out.= '?value';
		} else {
			$out.= '&amp;value';
		}
		$out.= '='.$spVars['searchvalue'].'&amp;type='.$spVars['searchtype'].'&amp;include='.$spVars['searchinclude'].'&amp;scope='.'&amp;search='.$spVars['searchpage'].'">'.$topicname.'</a>'."\n";
	} else {
		$out = '<a href="'.sp_build_url($forumslug, $topicslug, 1, 0).'">'.sp_filter_title_display($topicname).'</a>'."\n";
	}
	return $out;
}

?>