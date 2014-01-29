<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
#	sp_NoMembersListMessage()
#	Display Message when no Member Lists can be displayed
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoMembersListMessage($args='', $deniedMessage='', $definedMessage='') {
	global $spMembersList;
	$defs = array('tagId'		=> 'spNoMembersMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoMembersListMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# check for no access or not data
	if ($spMembersList->membersListStatus == 'no access') {
		$m = sp_filter_title_display($deniedMessage);
	} elseif ($spMembersList->membersListStatus == 'no data') {
		$m = sp_filter_title_display($definedMessage);
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>\n";
	$out = apply_filters('sph_NoMembersListMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoMemberMessage()
#	Display Message when no Members can be displayed
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoMemberMessage($args='', $definedMessage='') {
	global $spMembersList;
	$defs = array('tagId'		=> 'spNoMembersMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoMembersMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$m = sp_filter_title_display($definedMessage);

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>$m</div>\n";
	$out = apply_filters('sph_NoMembersMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# Member Group Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupIcon()
#	Display Group Icon
#	Scope:	Members Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupIcon($args='') {
	global $spThisMemberGroup;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId' 		=> 'spUsergroupIcon%ID%',
				  'tagClass' 	=> 'spHeaderIcon',
				  'icon' 		=> 'sp_MembersIcon.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_UsergroupIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon 		= sanitize_file_name($icon);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMemberGroup->usergroup_id, $tagId);

	$icon = SPTHEMEICONSURL.sanitize_file_name($icon);

	if ($get) return $icon;

	$out = "<img id='$tagId' class='$tagClass' src='$icon' alt='' />\n";
	$out = apply_filters('sph_UsergroupIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupName()
#	Display Usergroup Name/Title in Header
#	Scope:	Members Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupName($args='') {
	global $spThisMemberGroup;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId' 		=> 'spUsergroupname%ID%',
				  'tagClass' 	=> 'spHeaderName',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_UsergroupName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMemberGroup->usergroup_id, $tagId);

	if ($get) return $spThisMemberGroup->usergroup_name;

	$out = (empty($spThisMemberGroup->usergroup_name)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisMemberGroup->usergroup_name</div>\n";
	$out = apply_filters('sph_UsergroupName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_UsergroupDescription()
#	Display Usergroup Description in Header
#	Scope:	Usergroup Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersUsergroupDescription($args='') {
	global $spThisMemberGroup;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId' 		=> 'spUsergroupDescription%ID%',
				  'tagClass' 	=> 'spHeaderDescription',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_UsergroupDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMemberGroup->usergroup_id, $tagId);

	if ($get) return $spThisMemberGroup->usergroup_desc;

	$out = (empty($spThisMemberGroup->usergroup_desc)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisMemberGroup->usergroup_desc</div>\n";
	$out = apply_filters('sph_UsergroupDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MembersListName()
#	Display user name with link
#	Scope:	Members List loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MembersListName($args='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'		=> 'spMembersListName%ID%',
				  'tagClass' 	=> 'spRowName',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MembersListName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);

	if ($get) return $spThisMember->display_name;

	$out = "<span id='$tagId' class='$tagClass'>".sp_build_name_display($spThisMember->user_id, $spThisMember->display_name, true)."</span>\n";
	$out = apply_filters('sph_MembersListName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListPostCount()
#	Display user post count for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListPostCount($args='', $label='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    		=> 'spMembersListPostCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'stack'			=> 1,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : ': ';

    $count = max($spThisMember->posts, 0);

	if ($get) return $count;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
	$out.= "<span class='$numberClass'>$count</span>";
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListLastVisit()
#	Display user last visit for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListLastVisit($args='', $label='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    		=> 'spMembersListLastVisit%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'dateClass'		=> 'spInRowDate',
				  'stack'			=> 1,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListLastVisit_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$labelClass	= esc_attr($labelClass);
	$dateClass	= esc_attr($dateClass);
	$stack		= (int) $stack;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : ': ';

	if ($get) return $spThisMember->lastvisit;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
	$out.= "<span class='$dateClass'>".sp_date('d', $spThisMember->lastvisit).$att.sp_date('t', $spThisMember->lastvisit).'</span>';
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListLastVisit', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListRegistered()
#	Display user registration date for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListRegistered($args='', $label='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    	=> 'spMembersListRegistration%ID%',
				  'tagClass' 	=> 'spInRowCount',
				  'labelClass'	=> 'spInRowLabel',
				  'dateClass'	=> 'spInRowDate',
				  'stack'		=> 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListRegistered_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$labelClass	= esc_attr($labelClass);
	$dateClass	= esc_attr($dateClass);
	$stack		= (int) $stack;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : ': ';

	if ($get) return $spThisMember->user_registered;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
	$out.= "<span class='$dateClass'>".sp_date('d', $spThisMember->user_registered).'<br />'.sp_date('t', $spThisMember->user_registered).'</span>';
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListRegistered', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListUrl()
#	Display user registration date for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListUrl($args='', $label='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    	=> 'spMembersListURL%ID%',
				  'tagClass' 	=> 'spInRowCount',
				  'labelClass'	=> 'spInRowLabel',
				  'dateClass'	=> 'spInRowDate',
				  'stack'		=> 1,
                  'showIcon'    => 0,
                  'icon'        => 'sp_UserWebsite.png',
                  'iconClass'   => 'spImg',
                  'targetNew'   => 1,
                  'noFollow'    => 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListUrl_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$labelClass	= esc_attr($labelClass);
	$dateClass	= esc_attr($dateClass);
	$stack		= (int) $stack;
    $icon       = sanitize_file_name($icon);
    $iconClass  = esc_attr($iconClass);
    $targetNew  = (int) $targetNew;
    $noFollow   = (int) $noFollow;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : ': ';

	if ($get) return $spThisMember->user_url;

	$out = "<div id='$tagId' class='$tagClass'>";
   	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
    if ($showIcon && !empty($icon) && $spThisMember->user_url != '') {
        $target = ($targetNew) ? ' target="_blank"' : '';
        $follow = ($noFollow) ? ' rel="nofollow"' : '';
        $out.= "<a id='$tagId' class='$tagClass vtip' href='$spThisMember->user_url' title=''$target$follow><img class='$iconClass' src='".SPTHEMEICONSURL.$icon."' alt='' /></a>\n";
    } else {
    	$out.= "<span class='$dateClass'>".sp_make_clickable($spThisMember->user_url).'</span>';
    }
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListUrl', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListRank()
#	Display user badges/ranks for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListRank($args='', $label='') {
	global $spThisUser, $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    	=> 'spMembersListRank%ID%',
				  'tagClass' 	=> 'spInRowCount',
				  'labelClass'	=> 'spInRowLabel',
				  'rank'		=> 1,
				  'rankClass'	=> 'spInRowRank',
				  'badge'		=> 1,
				  'badgeClass'	=> 'spImg',
				  'stack'		=> 1,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListRank_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$rankClass		= esc_attr($rankClass);
	$badgeClass		= esc_attr($badgeClass);
	$rank			= (int) $rank;
	$badge			= (int) $badge;
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : ': ';

	# grab the user rank info
	$ranks = sp_get_user_special_ranks($spThisMember->user_id);
	if (empty($ranks)) {
		$usertype = ($spThisMember->admin) ? 'Admin' : 'User';
		$ranks = sp_get_user_forum_rank($usertype, $spThisMember->user_id, $spThisMember->posts);
	}

	if ($get) return $ranks;

	# now render it
	$out = "<div id='$tagId' class='$tagClass'>";
	if(!empty($label)) $out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>";
	foreach ($ranks as $thisRank) {
		if ($badge && !empty($thisRank['badge'])) $out.= "<img class='$badgeClass' src='".$thisRank['badge']."' alt='' />$att";
		if ($rank) $out.= "<span class='$rankClass'>".$thisRank['name']."</span>$att";
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListRank', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListActions()
#	Display user actions for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListActions($args='', $label='', $startedToolTip='', $postedToolTip='') {
	global $spThisMember;

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    		=> 'spMembersListActions%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'started'			=> 1,
				  'startedIcon'		=> 'sp_TopicsStarted.png',
				  'startedClass'	=> 'spIcon',
				  'posted'			=> 1,
				  'postedIcon'		=> 'sp_TopicsPosted.png',
				  'postedClass'		=> 'spIcon',
				  'profile'			=> 1,
				  'profileIcon'		=> 'sp_ProfileForm.png',
				  'profileClass'	=> 'spIcon',
				  'stack'			=> 0,
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListActions_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$startedClass	= esc_attr($startedClass);
	$postedClass	= esc_attr($postedClass);
	$profileClass	= esc_attr($profileClass);
	$started		= (int) $started;
	$posted			= (int) $posted;
	$profile		= (int) $profile;
	$startedIcon	= SPTHEMEICONSURL.sanitize_file_name($startedIcon);
	$postedIcon		= SPTHEMEICONSURL.sanitize_file_name($postedIcon);
	$profileIcon	= SPTHEMEICONSURL.sanitize_file_name($profileIcon);
	$echo			= (int) $echo;
	if (!empty($startedToolTip))	$startedToolTip	= esc_attr($startedToolTip);
	if (!empty($postedToolTip)) 	$postedToolTip	= esc_attr($postedToolTip);

	$tagId = str_ireplace('%ID%', $spThisMember->user_id, $tagId);
	$att = ($stack) ? '<br />' : '';

	# now render it
	$out = "<div id='$tagId' class='$tagClass'>";
	if(!empty($label)) $out.= "<span class='$labelClass'>".sp_filter_title_display($label)."<br /></span>";
	if ($started) {
		$param['forum'] = 'all';
		$param['value'] = $spThisMember->user_id;
		$param['type'] = 5;
		$param['search'] = 1;
		$url = add_query_arg($param, sp_url());
		$url = sp_filter_wp_ampersand($url);
		$out.= "<a href='".esc_url($url)."'><img class='$startedClass vtip' src='$startedIcon' title='$startedToolTip' alt='' />$att</a>";
	}

	if ($posted) {
		$param['forum'] = 'all';
		$param['value'] = $spThisMember->user_id;
		$param['type'] = 4;
		$param['search'] = 1;
		$url = add_query_arg($param, sp_url());
		$url = sp_filter_wp_ampersand($url);
		$out.= "<a href='".esc_url($url)."'><img class='$postedClass vtip' src='$postedIcon' title='$postedToolTip' alt='' />$att</a>";
	}

	if ($profile) {
		$link = "<img class='$profileClass' src='$profileIcon' alt='' />$att";
		$out.= sp_attach_user_profile_link($spThisMember->user_id, $link);
	}
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListActions', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListSearchForm()
#	Display member search form for the memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListSearchForm($args='') {

	if (!sp_get_auth('view_members_list')) return;

	$defs = array('tagId'    			=> 'spMembersListSearchForm',
				  'tagClass'			=> 'spForm',
	              'controlFieldset'		=> '',
			   	  'controlInput'		=> 'spControl',
			   	  'controlInputSize'	=> 30,
				  'controlSubmit'		=> 'spSubmit',
				  'controlAllMembers'	=> 'spSubmit',
	              'classLabel'			=> 'spLabel',
				  'labelFormTitle'		=> '',
				  'labelSearch'			=> '',
				  'labelSearchSubmit'	=> '',
				  'labelSearchAll'		=> '',
	              'classWildcard'		=> 'spSearchDetails',
			 	  'labelWildcard'		=> '',
				  'labelWildcardAny'	=> '',
				  'labelWildcardChar'	=> '',
				  'echo'				=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListSearchForm_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagid				= esc_attr($tagId);
	$tagClass			= esc_attr($tagClass);
	$controlFieldset	= esc_attr($controlFieldset);
	$controlInput		= esc_attr($controlInput);
	$controlInputSize	= (int) $controlInputSize;
	$controlSubmit		= esc_attr($controlSubmit);
	$controlAllMembers	= esc_attr($controlAllMembers);
	$classLabel			= esc_attr($classLabel);
	$labelFormTitle		= sp_filter_title_display($labelFormTitle);
	$labelSearch		= sp_filter_title_display($labelSearch);
	$labelSearchSubmit	= sp_filter_title_display($labelSearchSubmit);
	$labelSearchAll		= sp_filter_title_display($labelSearchAll);
	$labelWildcard		= sp_filter_title_display($labelWildcard);
	$labelWildcardAny	= sp_filter_title_display($labelWildcardAny);
	$labelWildcardChar	= sp_filter_title_display($labelWildcardChar);
	$echo				= (int) $echo;

	$search = (!empty($_POST['msearch']) && !isset($_POST['allmembers'])) ? sp_esc_str($_POST['msearch']) : '';
	$search = (!empty($_GET['msearch'])) ? sp_esc_str($_GET['msearch']) : $search;
	$ug = (!empty($_POST['ug']) && !isset($_POST['allmembers'])) ? sp_esc_int($_POST['ug']) : '';
	$ug = (!empty($_GET['ug'])) ? sp_esc_int($_GET['ug']) : $ug;

	$out = "<div id='$tagId'>";
	$out.= "<form class='$tagClass' action='".SPMEMBERLIST."' method='post' name='searchmembers'>";
	$out.= "<fieldset class='$controlFieldset'><legend>$labelFormTitle</legend>";
	$out.= "<label class='$classLabel' for='msearch'>$labelSearch</label>";
	$out.= "<input type='hidden' class='$controlInput' name='ug' id='ug' value='$ug' />";
	$out.= "<input type='text' class='$controlInput' name='msearch' id='msearch' size='$controlInputSize' value='$search' />";
	$out.= "<input type='submit' class='$controlSubmit' name='membersearch' id='membersearch' value='$labelSearchSubmit' />";
	$out.= "<input type='submit' class='$controlAllMembers' name='allmembers' id='allmembers' value='$labelSearchAll' />";
	$out.= "<p class='$classWildcard'>$labelWildcard<br />$labelWildcardAny<br />$labelWildcardChar</p>";
	$out.= '</fieldset>';
	$out.= '</form>';
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListSearchForm', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListPageLinks()
#	Display page links for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#		5.2 Added tagId argument
#
# --------------------------------------------------------------------------------------
function sp_MemberListPageLinks($args='', $label='', $toolTip='') {

	if (!sp_get_auth('view_members_list')) return;

	global $spMembersList;
	$defs = array('tagId'			=> 'spMemberPageLinks',
				  'tagClass' 		=> 'spPageLinks',
				  'prevIcon'		=> 'sp_ArrowLeft.png',
				  'nextIcon'		=> 'sp_ArrowRight.png',
				  'iconClass'		=> 'spIcon',
				  'pageLinkClass'	=> 'spPageLinks',
				  'curPageClass'	=> 'spCurrent',
				  'showLinks'		=> 4,
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	if(!empty($prevIcon))	$prevIcon = SPTHEMEICONSURL.sanitize_file_name($prevIcon);
	if(!empty($nextIcon))	$nextIcon = SPTHEMEICONSURL.sanitize_file_name($nextIcon);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$curPageClass	= esc_attr($curPageClass);
	$showLinks		= (int) $showLinks;
	$label			= sp_filter_title_display($label);
	$toolTip		= esc_attr($toolTip);
	$echo			= (int) $echo;

	global $spVars;
	$curToolTip = str_ireplace('%PAGE%', $spVars['page'], $toolTip);

	if(isset($_POST['allmembers'])) {
		$search = '';
		$ug = '';
	} else {
		if (isset($_GET['page'])) $spVars['page'] = sp_esc_int($_GET['page']);
		$search = (!empty($_POST['msearch'])) ? sp_esc_str($_POST['msearch']) : '';
		$search = (!empty($_GET['msearch'])) ? sp_esc_str($_GET['msearch']) : $search;
		$ug = (!empty($_POST['ug'])) ? sp_esc_int($_POST['ug']) : '';
		$ug = (!empty($_GET['ug'])) ? sp_esc_int($_GET['ug']) : $ug;
	}

	$out = "<div id='$tagId' class='$tagClass'>";
	$totalPages = ($spMembersList->totalMemberCount / $spMembersList->membersNumber);
	if (!is_int($totalPages)) $totalPages = (intval($totalPages) + 1);
	$out.= $label;
	$out.= sp_page_prev($spVars['page'], $showLinks, SPMEMBERLIST, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);

	$url = SPMEMBERLIST;
	if ($spVars['page'] > 1) $url = user_trailingslashit(trailingslashit($url).'page-'.$spVars['page']);
	if (!empty($search)) {
		$param['msearch'] = $search;
		$url = add_query_arg($param, $url);
		$url = sp_filter_wp_ampersand($url);
	}
	if (!empty($ug)) {
		$param['ug'] = $ug;
		$url = add_query_arg($param, $url);
		$url = sp_filter_wp_ampersand($url);
	}
	$out.= "<a href='$url' class='$pageLinkClass $curPageClass vtip' title='$curToolTip'>".$spVars['page'].'</a>';

	$out.= sp_page_next($spVars['page'], $totalPages, $showLinks, SPMEMBERLIST, $pageLinkClass, $iconClass, $prevIcon, $nextIcon, $toolTip, $search, $ug);
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_MemberListUsergroupSelect()
#	Display page links for memebers list
#	Scope:	Members List Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_MemberListUsergroupSelect($args='') {
    global $spMembersList;

    if (empty($spMembersList->userGroups)) return;
	if (!sp_get_auth('view_members_list')) return;

	global $spMembersList;
	$defs = array('tagId'   		=> 'spUsergroupSelect',
                  'tagClass' 		=> 'spUsergroupSelect',
                  'selectClass'		=> 'spControl',
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_MemberListUsergroupSelect_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId	        = esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$selectClass	= esc_attr($selectClass);
	$echo			= (int) $echo;

	$search = (!empty($_POST['msearch']) && !isset($_POST['allmembers'])) ? '&amp;msearch='.sp_esc_str($_POST['msearch']) : '';
	$search = (!empty($_GET['msearch'])) ? '&amp;msearch='.sp_esc_str($_GET['msearch']) : $search;
	$ug = (!empty($_POST['ug']) && !isset($_POST['allmembers'])) ? sp_esc_int($_POST['ug']) : '';
	$ug = (!empty($_GET['ug'])) ? sp_esc_int($_GET['ug']) : $ug;

	$out = "<div id='$tagId' class='$tagClass'>";
	$out.= "<select class='$selectClass' name='sp_usergroup_select' onchange='javascript:spjChangeURL(this)'>";
	$out.= "<option value='#'>".sp_text('Select Specific Usergroup')."</option>";
	foreach ($spMembersList->userGroups as $usergroup) {
        $selected = ($usergroup['usergroup_id'] == $ug) ? "selected='selected'" : '';
		$out.= "<option $selected value='".sp_get_sfqurl(sp_url('members')).'ug='.$usergroup['usergroup_id'].$search."'>".sp_filter_title_display($usergroup['usergroup_name']).'</option>';
    }
	if (!empty($ug)) $out.= "<option value='".sp_get_sfqurl(sp_url('members')).$search."'>".sp_text('Reset to Default Usergroups')."</option>";
    $out.= '</select>';
	$out.= "</div>\n";
	$out = apply_filters('sph_MemberListUsergroupSelect', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

?>