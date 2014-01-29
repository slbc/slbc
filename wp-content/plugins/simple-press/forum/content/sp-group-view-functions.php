<?php
/*
Simple:Press
Template Function Handler
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ======================================================================================
#
# GROUP VIEW
# Group Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderIcon()
#	Display Group Icon
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderIcon($args='') {
	global $spThisGroup;
	$defs = array('tagId' 		=> 'spGroupHeaderIcon%ID%',
				  'tagClass' 	=> 'spHeaderIcon',
				  'icon' 		=> 'sp_GroupIcon.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupHeaderIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);

	# Check if a custom icon
	if (!empty($spThisGroup->group_icon)) {
		$icon = SFCUSTOMURL.$spThisGroup->group_icon;
	} else {
		$icon = SPTHEMEICONSURL.sanitize_file_name($icon);
	}

	if ($get) return $icon;

	$out = "<img id='$tagId' class='$tagClass' src='$icon' alt='' />\n";
	$out = apply_filters('sph_GroupHeaderIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderName()
#	Display Group Name/Title in Header
#	Scope:	Group Loop
#	Version: 5.0
#
#	Changelog:
#	5.2.3 - 'toggleTagId' argument added
#			'collapse' argument added
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderName($args='') {
	global $spThisGroup;
	$defs = array('tagId' 		=> 'spGroupHeaderName%ID%',
				  'tagClass' 	=> 'spHeaderName',
				  'toggleTagId' => 'spGroupOpenClose%ID%',
				  'collapse'	=> 1,
				  'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupHeaderName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$toggleTagId= esc_attr($toggleTagId);
	$collapse	= (int) $collapse;
	$truncate	= (int) $truncate;
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);
	$toggleTagId = '#'.str_ireplace('%ID%', $spThisGroup->group_id, $toggleTagId);

	if ($get) return sp_truncate($spThisGroup->group_name, $truncate);

	$out = '';
	if (!empty($spThisGroup->group_name)) {
		$out.= "<div id='$tagId' class='$tagClass' ";
		if($collapse) $out.= "onclick='jQuery(\"$toggleTagId\").click();' style='cursor: pointer;'";
		$out.= ">".sp_truncate($spThisGroup->group_name, $truncate)."</div>\n";
	}

	$out = apply_filters('sph_GroupHeaderName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderDescription()
#	Display Group Description in Header
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderDescription($args='') {
	global $spThisGroup;
	$defs = array('tagId' 		=> 'spGroupHeaderDescription%ID%',
				  'tagClass' 	=> 'spHeaderDescription',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupHeaderDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);

	if ($get) return $spThisGroup->group_desc;

	$out = (empty($spThisGroup->group_desc)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisGroup->group_desc</div>\n";
	$out = apply_filters('sph_GroupHeaderDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupOpenClose()
#	Display Open and Close of forum listing
#	Scope:	Group Loop
#	Version: 5.1
#
#	default values= 'open', 'closed'
#
# --------------------------------------------------------------------------------------
function sp_GroupOpenClose($args='', $toolTipOpen='', $toolTipClose='') {
	global $spThisGroup;
	$defs = array('tagId' 		=> 'spGroupOpenClose%ID%',
				  'tagClass' 	=> 'spIcon',
				  'openIcon' 	=> 'sp_GroupOpen.png',
				  'closeIcon' 	=> 'sp_GroupClose.png',
				  'default'		=> 'open',
				  'echo'		=> 1
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupOpenClose_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$openIcon	= SPTHEMEICONSURL.sanitize_file_name($openIcon);
	$closeIcon	= SPTHEMEICONSURL.sanitize_file_name($closeIcon);
	$default	= esc_attr($default);
	$echo		= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);
	$div = 'forumlist'.$spThisGroup->group_id;

	if (isset($_COOKIE[$div])) $default = $_COOKIE[$div];

	($default == 'open') ? $icon = $closeIcon : $icon = $openIcon;
	($default == 'open') ? $tooltip = $toolTipClose : $tooltip = $toolTipOpen;

	if($default == 'closed') {
		echo '<style type="text/css">#'.$div.' {display:none;}</style>';
	}

	$out = "<span id='$tagId' onclick='spjOpenCloseForums(\"$div\", \"$tagId\", \"$tagClass\", \"$openIcon\", \"$closeIcon\", \"$toolTipOpen\", \"$toolTipClose\");'><img class='$tagClass vtip' title='$tooltip' src='$icon' alt='' /></span>\n";
	$out = apply_filters('sph_GroupOpenClose', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderMessage()
#	Display Special Group Message in Header
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderMessage($args='') {
	global $spThisGroup;
	$defs = array('tagId' 		=> 'spGroupHeaderMessage%ID%',
				  'tagClass' 	=> 'spHeaderMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupHeaderMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);

	if ($get) return $spThisGroup->group_message;

	$out = (empty($spThisGroup->group_message)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisGroup->group_message</div>\n";
	$out = apply_filters('sph_GroupHeaderMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_GroupHeaderRSSButton()
#	Display Group Level RSS Button
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_GroupHeaderRSSButton($args='', $label='', $toolTip='') {
	global $spThisUser, $spThisGroup;

	if (!$spThisGroup->group_rss_active) return;

	$defs = array('tagId' 		=> 'spGroupHeaderRSSButton%ID%',
				  'tagClass' 	=> 'spLink',
				  'icon' 		=> 'sp_Feed.png',
				  'iconClass'	=> 'spIcon',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_GroupHeaderRSSButton_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$icon		= sanitize_file_name($icon);
	$iconClass 	= esc_attr($iconClass);
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisGroup->group_id, $tagId);

	# Get or construct rss url
	if (empty($spThisGroup->rss)) {
		$rssOpt = sp_get_option('sfrss');
		if ($rssOpt['sfrssfeedkey'] && isset($spThisUser->feedkey)) {
            $rssUrl = sp_get_sfqurl(trailingslashit(sp_build_url('', '', 0, 0, 0, 1)).user_trailingslashit($spThisUser->feedkey)).'group='.$spThisGroup->group_id;
		} else {
            $sym = (strpos(sp_url(), '?')) ? '&' : '?';
   			$rssUrl = trailingslashit(sp_build_url('', '', 0, 0, 0, 1)).sp_add_get()."group=$spThisGroup->group_id";
		}
	} else {
		$rssUrl = $spThisGroup->rss;
	}

	if ($get) return $rssUrl;

	$out = "<a class='$tagClass vtip' id='$tagId' title='$toolTip' rel='nofollow' href='$rssUrl'>";
	if (!empty($icon)) $out.= "<img class='$iconClass' src='".SPTHEMEICONSURL.$icon."' alt=''/>";
	if (!empty($label)) $out.= sp_filter_title_display($label);
	$out.= "</a>\n";
	$out = apply_filters('sph_GroupHeaderRSSButton', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoGroupMessage()
#	Display Message when no Groups can be displayed
#	Scope:	Group Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoGroupMessage($args='', $deniedMessage='', $definedMessage='') {
	global $spGroupView;
	$defs = array('tagId'		=> 'spNoGroupMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoGroupMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	# is Access denied to all groups
	if ($spGroupView->groupViewStatus == 'no access') {
		$m = sp_filter_title_display($deniedMessage);
	} elseif ($spGroupView->groupViewStatus == 'no data') {
		$m = sp_filter_title_display($definedMessage);
	} else {
		return;
	}

	if ($get) return $m;

	$out = "<div id='$tagId' class='$tagClass'>".$m."</div>\n";
	$out = apply_filters('sph_NoGroupMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# ======================================================================================
#
# GROUP VIEW
# Forum Loop Functions
#
# ======================================================================================

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexIcon()
#	Display Forum Icon
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexIcon($args='') {
	global $spThisForum;
	$defs = array('tagId' 		=> 'spForumIndexIcon%ID%',
				  'tagClass' 	=> 'spRowIcon',
				  'icon' 		=> 'sp_ForumIcon.png',
				  'iconUnread'	=> 'sp_ForumIconPosts.png',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	# Check if a custom icon
	$path = SPTHEMEICONSDIR;
	$url = SPTHEMEICONSURL;
	if($spThisForum->unread) {
		$fIcon = sanitize_file_name($iconUnread);
		if (!empty($spThisForum->forum_icon_new)) {
			$fIcon = sanitize_file_name($spThisForum->forum_icon_new);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	} else {
		$fIcon = sanitize_file_name($icon);
		if (!empty($spThisForum->forum_icon)) {
			$fIcon = sanitize_file_name($spThisForum->forum_icon);
			$path = SFCUSTOMDIR;
			$url = SFCUSTOMURL;
		}
	}
	if(!file_exists($path.$fIcon)) {
		$fIcon = SPTHEMEICONSURL.sanitize_file_name($icon);
	} else {
		$fIcon = $url.$fIcon;
	}

	if ($get) return $fIcon;

	$out = "<img id='$tagId' class='$tagClass' src='$fIcon' alt='' />\n";
	$out = apply_filters('sph_ForumIndexIcon', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexName()
#	Display Forum Name/Title in Header
#	Scope:	Forumn sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexName($args='', $toolTip='') {
	global $spThisForum;
	$defs = array('tagId'    	=> 'spForumIndexName%ID%',
			      'tagClass' 	=> 'spRowName',
			      'truncate'	=> 0,
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexName_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$truncate	= (int) $truncate;
	$toolTip	= esc_attr($toolTip);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	$toolTip = str_ireplace('%NAME%', htmlspecialchars($spThisForum->forum_name, ENT_QUOTES, SFCHARSET), $toolTip);

	if ($get) return sp_truncate($spThisForum->forum_name, $truncate);

	$out = "<a href='$spThisForum->forum_permalink' id='$tagId' class='$tagClass vtip' title='$toolTip'>".sp_truncate($spThisForum->forum_name, $truncate)."</a>\n";
	$out = apply_filters('sph_ForumIndexName', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexDescription()
#	Display Forum Description in Header
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexDescription($args='') {
	global $spThisForum;
	$defs = array('tagId'    	=> 'spForumIndexDescription%ID%',
			      'tagClass' 	=> 'spRowDescription',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumDescription_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	if ($get) return $spThisForum->forum_desc;

	$out = (empty($spThisForum->forum_desc)) ? '' : "<div id='$tagId' class='$tagClass'>$spThisForum->forum_desc</div>\n";
	$out = apply_filters('sph_ForumIndexDescription', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPageLinks()
#	Display Forum 'in row' page links
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPageLinks($args='', $toolTip='') {
	global $spThisForum, $spGlobals;

	$topics_per_page = $spGlobals['display']['topics']['perpage'];
	if ($topics_per_page >= $spThisForum->topic_count) return '';

	$defs = array('tagId'    		=> 'spForumIndexPageLinks%ID%',
				  'tagClass' 		=> 'spInRowPageLinks',
				  'icon'			=> 'sp_ArrowRightSmall.png',
				  'iconClass'		=> 'spIconSmall',
				  'pageLinkClass'	=> 'spInRowForumPageLink',
				  'showLinks'		=> 4,
				  'echo'			=> 1,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexPageLinks_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$iconClass		= esc_attr($iconClass);
	$pageLinkClass	= esc_attr($pageLinkClass);
	$showLinks		= (int) $showLinks;
	$toolTip		= esc_attr($toolTip);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$total_pages=($spThisForum->topic_count / $topics_per_page);
	if (!is_int($total_pages)) $total_pages=intval($total_pages) + 1;
	($total_pages > $showLinks ? $max_count = $showLinks : $max_count = $total_pages);
	for ($x = 1; $x <= $max_count; $x++) {
		$out.= "<a class='$pageLinkClass vtip' href='".sp_build_url($spThisForum->forum_slug, '', $x, 0)."' title='".str_ireplace('%PAGE%', $x, $toolTip)."'>$x</a>\n";
	}
	if ($total_pages > $showLinks) {
		if(!empty($icon)) $out.= "<img src='".SPTHEMEICONSURL.$icon."' class='$iconClass' alt=''/>\n";
		$out.= "<a class='$pageLinkClass vtip' href='".sp_build_url($spThisForum->forum_slug, '', $total_pages, 0)."' title='".str_ireplace('%PAGE%', $total_pages, $toolTip)."'>$total_pages</a>\n";
	}
	$out.= "</div>\n";

	$out = apply_filters('sph_ForumIndexPageLinks', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexStatusIcons()
#	Display Forum Status (Locked/New Post/Blank)
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexStatusIcons($args='', $toolTipLock='', $toolTipPost='', $toolTipAdd='') {
	global $spThisForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spForumIndexStatus%ID%',
				  'tagClass' 		=> 'spStatusIcon',
				  'showLock'		=> 1,
				  'showNewPost'		=> 1,
				  'showAddTopic'	=> 1,
				  'iconLock'		=> 'sp_ForumStatusLock.png',
				  'iconPost'		=> 'sp_ForumStatusPost.png',
				  'iconAdd'		    => 'sp_ForumStatusAdd.png',
                  'first'           => 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexStatusIcons_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$showLock		= (int) $showLock;
	$showNewPost	= (int) $showNewPost;
	$showAddTopic	= (int) $showAddTopic;
	$toolTipPost	= esc_attr($toolTipPost);
	$toolTipLock	= esc_attr($toolTipLock);
	$toolTipAdd	    = esc_attr($toolTipAdd);
	$first   	    = (int) $first;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	if ($get) return $spThisForum->forum_status;

	$out = "<div id='$tagId' class='$tagClass'>\n";

	# Dislay if global lock down or forum locked
	if ($showLock && !empty($iconLock)) {
		if ($spGlobals['lockdown'] || $spThisForum->forum_status)  {
			$icon = SPTHEMEICONSURL.sanitize_file_name($iconLock);
			$out.= "<img class='vtip' src='$icon' title='$toolTipLock' alt='' />\n";
		}
	}

	# New Post Popup
	if ($showNewPost && !empty($iconPost)) {
		if ($spThisForum->unread) {
			$icon = SPTHEMEICONSURL.sanitize_file_name($iconPost);
			$toolTipPost = str_ireplace('%COUNT%', $spThisForum->unread, $toolTipPost);

			$site = SFHOMEURL."index.php?sp_ahah=newpostpopup&amp;action=forum&amp;id=$spThisForum->forum_id&amp;first=$first&amp;sfnonce=".wp_create_nonce('forum-ahah');
			$linkId = 'spNewPostPopup'.$spThisForum->forum_id;
			$out.= "<a rel='nofollow' id='$linkId' href='javascript:void(null)' onclick='spjDialogAjax(this, \"$site\", \"$toolTipPost\", 600, 0, 0);'>";

			$out.= "<img class='vtip' src='$icon' title='$toolTipPost' alt='' /></a>\n";
		}
	}

    # add new topic icon
    if ($showAddTopic && !empty($iconAdd)) {
		if (sp_get_auth('start_topics', $spThisForum->forum_id) && ((!$spThisForum->forum_status && !$spGlobals['lockdown']) || $spThisUser->admin)) {
			$url = sp_build_url($spThisForum->forum_slug, '', 1, 0).sp_add_get().'new=topic';
			$out.= "<a href='$url' class='vtip' title='$toolTipAdd'>\n";
			$icon = SPTHEMEICONSURL.sanitize_file_name($iconAdd);
			$out.= "<img src='$icon' alt='' />\n";
			$out.= "</a>\n";
		}
	}

	$out = apply_filters('sph_ForumIndexStatusIconsLast', $out, $a);

	$out.= "</div>\n";

	$out = apply_filters('sph_ForumIndexStatusIcons', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexLockIcon()
#	Display Forum Status (Locked)
#	Scope:	Forum sub Loop
#	Version: 5.1
#
#	Changelog
#	5.2.3	Added 'statusClass' to icons with no action
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexLockIcon($args='', $toolTip='') {
	global $spThisForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spForumIndexLockIcon%ID%',
				  'tagClass' 		=> 'spIcon',
				  'statusClass'		=> 'spIconNoAction',
				  'icon'			=> 'sp_ForumStatusLock.png',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexLockIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$statusClass	= esc_attr($statusClass);
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	if ($get) return $spThisForum->forum_status;
	$out='';

	if ($spGlobals['lockdown'] || $spThisForum->forum_status)  {
		$out = "<div id='$tagId' class='$tagClass $statusClass vtip' title='$toolTip' >\n";
		# Dislay if global lock down or forum locked
		if(!empty($icon)) $out.= "<img src='".SPTHEMEICONSURL."$icon' alt='' />\n";
		$out.= "</div>\n";
		$out = apply_filters('sph_ForumIndexLockIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexAddIcon()
#	Display Forum Status (Add Topic)
#	Scope:	Forum sub Loop
#	Version: 5.1
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexAddIcon($args='', $toolTip='') {
	global $spThisForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 			=> 'spForumIndexAddIcon%ID%',
				  'tagClass' 		=> 'spIcon',
				  'icon'			=> 'sp_ForumStatusAdd.png',
				  'echo'			=> 1,
				  'get'				=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexAddIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= sanitize_file_name($icon);
	$echo			= (int) $echo;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	$out='';

    # add new topic icon
	if (sp_get_auth('start_topics', $spThisForum->forum_id) && ((!$spThisForum->forum_status && !$spGlobals['lockdown']) || $spThisUser->admin)) {
		$url = sp_build_url($spThisForum->forum_slug, '', 1, 0).sp_add_get().'new=topic';
		$out.= "<a id='$tagId' class='$tagClass vtip' title='$toolTip' href='$url'>\n";
		if(!empty($icon)) $out.= "<img src='".SPTHEMEICONSURL."$icon' alt='' />\n";
		$out.= "</a>\n";
		$out = apply_filters('sph_ForumIndexAddIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPostsIcon()
#	Display Forum Status (Show Posts)
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPostsIcon($args='', $toolTip='') {
	global $spThisForum, $spGlobals, $spThisUser;

	$defs = array('tagId' 		=> 'spForumIndexPostsIcon%ID%',
				  'tagClass' 	=> 'spIcon',
				  'icon'		=> 'sp_ForumStatusPost.png',
				  'openIcon' 	=> 'sp_GroupOpen.png',
				  'closeIcon' 	=> 'sp_GroupClose.png',
				  'popup'		=> 1,
                  'first'       => 0,
				  'echo'		=> 1,
				  'get'			=> 0
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexPostsIcon_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$icon			= SPTHEMEICONSURL.sanitize_file_name($icon);
	$openIcon		= SPTHEMEICONSURL.sanitize_file_name($openIcon);
	$closeIcon		= SPTHEMEICONSURL.sanitize_file_name($closeIcon);
	$popup			= (int) $popup;
	$first   	    = (int) $first;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	$out='';

	if(!$popup) $icon = $openIcon;

    # show new posts icon
	if ($spThisForum->unread) {
		$toolTip = str_ireplace('%COUNT%', $spThisForum->unread, $toolTip);
		$site = SFHOMEURL."index.php?sp_ahah=newpostpopup&amp;action=forum&amp;id=$spThisForum->forum_id&amp;popup=$popup&amp;first=$first&amp;sfnonce=".wp_create_nonce('forum-ahah');
		$linkId = 'spNewPostPopup'.$spThisForum->forum_id;

		$out.= "<a id='$tagId' class='$tagClass vtip' title='$toolTip' rel='nofollow' id='$linkId' href='javascript:void(null)' ";
		if($popup) {
			$out.= "onclick='spjDialogAjax(this, \"$site\", \"$toolTip\", 600, 0, 0);'>";
		} else {
			$target = 'spInlineTopics'.$spThisForum->forum_id;
			$spinner = SFCOMMONIMAGES.'working.gif';
			$out.= "onclick='spjInlineTopics(\"$target\", \"$site\", \"$spinner\", \"$tagId\", \"$openIcon\", \"$closeIcon\");'>";
		}
		if(!empty($icon)) $out.= "<img src='$icon' alt='' />\n";
		$out.= "</a>\n";
		$out = apply_filters('sph_ForumIndexPostsIcon', $out, $a);
	}

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexInlinePosts()
#	Display inline dropdopwn posts section (Show Posts)
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexInlinePosts() {
	global $spThisForum;
	echo "<div class='spInlineTopics' id='spInlineTopics".$spThisForum->forum_id."' style='display:none;'></div>";
	sp_InsertBreak();
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexPostCount()
#	Display Forum 'in row' total post count
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexPostCount($args='', $label='') {
	global $spThisForum;
	$defs = array('tagId'    		=> 'spForumIndexPostCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'includeSubs'		=> 1,
				  'stack'			=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexPostCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$includeSubs	= (int) $includeSubs;
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if($includeSubs && $spThisForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	$data = ($includeSubs ? $spThisForum->post_count_sub : $spThisForum->post_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$data</span>\n";
	$out.= "</div>\n";
	$out = apply_filters('sph_ForumIndexPostCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexTopicCount()
#	Display Forum 'in row' total topic count
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexTopicCount($args='', $label='') {
	global $spThisForum;
	$defs = array('tagId'    		=> 'spForumIndexTopicCount%ID%',
				  'tagClass' 		=> 'spInRowCount',
				  'labelClass'		=> 'spInRowLabel',
				  'numberClass'		=> 'spInRowNumber',
				  'includeSubs'		=> 1,
				  'stack'			=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexTopicCount_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$numberClass	= esc_attr($numberClass);
	$includeSubs	= (int) $includeSubs;
	$stack			= (int) $stack;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if($includeSubs && $spThisForum->forum_id_sub == 0) $includeSubs = 0;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	($stack ? $att='<br />' : $att= ' ');

	$data = ($includeSubs ? $spThisForum->topic_count_sub : $spThisForum->topic_count);
	if ($get) return $data;

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."$att</span>\n";
	$out.= "<span class='$numberClass'>$data</span>\n";
	$out.= "</div>\n";
	$out = apply_filters('sph_ForumIndexTopicCount', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexLastPost()
#	Display Forum 'in row' link to the last post made to a topic in this forum
#	Scope:	Forum sub Loop
#	Version: 5.0
#
#	Changelog:
#	5.1 - 'Order' argument added
#	5.1	- 'ItemBreak' argument added
#	5.2.3 - 'L' Linebreak - added to Order argument
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexLastPost($args='', $lastPostLabel='', $noTopicsLabel='') {
	global $spThisForum;

    # if no posts just bail since there wont be a last post
    if ($spThisForum->post_count == 0) return;

	$defs = array('tagId'    		=> 'spForumIndexLastPost%ID%',
				  'tagClass' 		=> 'spInRowPostLink',
				  'labelClass'		=> 'spInRowLabel',
				  'infoClass'		=> 'spInRowInfo',
				  'linkClass'		=> 'spInRowLastPostLink',
				  'includeSubs'		=> 1,
				  'tip'   			=> 1,
				  'order'			=> 'UTD',
				  'nicedate'		=> 1,
				  'date'  			=> 0,
				  'time'  			=> 0,
				  'stackdate'		=> 0,
				  'user'  			=> 1,
				  'truncate'		=> 0,
				  'truncateUser'	=> 0,
				  'itemBreak'		=> '<br />',
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexLastPost_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$infoClass		= esc_attr($infoClass);
	$linkClass		= esc_attr($linkClass);
	$includeSubs	= (int) $includeSubs;
	$tip			= (int) $tip;
	$order			= esc_attr($order);
	$nicedate		= (int) $nicedate;
	$date			= (int) $date;
	$time			= (int) $time;
	$stackdate		= (int) $stackdate;
	$user			= (int) $user;
	$truncate		= (int) $truncate;
	$truncateUser	= (int) $truncateUser;
	$echo			= (int) $echo;
	$get			= (int) $get;

	if($includeSubs && $spThisForum->forum_id_sub == 0) $includeSubs = 0;
	$postCount = ($includeSubs ? $spThisForum->post_count_sub : $spThisForum->post_count);

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);
	$posttip = ($includeSubs ? $spThisForum->post_tip_sub : $spThisForum->post_tip);
	if ($tip && !empty($posttip)) {
		$title = "title='$posttip'";
		$linkClass.= ' vtip';
	} else {
		$title='';
	}

	($stackdate ? $dlb='<br />' : $dlb=' - ');

	# user
	$poster = ($includeSubs ? sp_build_name_display($spThisForum->user_id_sub, sp_truncate($spThisForum->display_name_sub, $truncateUser), true) : sp_build_name_display($spThisForum->user_id, sp_truncate($spThisForum->display_name, $truncateUser), true));
	if (empty($poster)) $poster = ($includeSubs ? sp_truncate($spThisForum->guest_name_sub, $truncateUser) : sp_truncate($spThisForum->guest_name, $truncateUser));

	# other items
	$permalink = ($includeSubs ? $spThisForum->post_permalink_sub : $spThisForum->post_permalink);
	$topicname = ($includeSubs ? sp_truncate($spThisForum->topic_name_sub, $truncate) : sp_truncate($spThisForum->topic_name, $truncate));
	$postdate  = ($includeSubs ? $spThisForum->post_date_sub : $spThisForum->post_date);

	if ($get) {
		$getData = new stdClass();
		$getData->permalink = $permalink;
		$getData->topic_name = $topicname;
		$getData->post_date = $postdate;
		$getData->user = $poster;
		return $getData;
	}

	$U = $poster;
	$T = "<a class='$linkClass' $title href='$permalink'>$topicname</a>";
	if ($nicedate) {
		$D = sp_nicedate($postdate);
	} else {
		if($date) {
			$D = sp_date('d', $postdate);
			if($time) {
				$D.= $dlb.sp_date('t', $postdate);
			}
		}
	}

	$out = "<div id='$tagId' class='$tagClass'>\n";
	if($postCount) {
		$out.= "<span class='$labelClass'>".sp_filter_title_display($lastPostLabel)." \n";
		for($x=0; $x<strlen($order); $x++) {
			$i = substr($order, $x, 1);
			switch($i) {
				case 'U':
					if($user) {
						if($x != 0) $out.= "<span class='$labelClass'>";
						$out.= $U. "</span>\n";
					}
					if($x != (strlen($order)-1)) {
						if(substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'T':
					if($x == 0) $out.= "</span>".$itemBreak;
					$out.= "<span class='$linkClass'>";

					$out.= $T. "</span>\n";
					if($x != (strlen($order)-1)) {
						if(substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'D':
					if($x != 0) $out.= "<span class='$labelClass'>";
					$out.= $D. "</span>\n";
					if($x != (strlen($order)-1)) {
						if(substr($order, $x+1, 1) != 'L') {
							$out.= $itemBreak;
						}
					}
					break;
				case 'L':
					$out.= '<br />';
					break;
			}
		}
	} else {
		$out.= "<span class='$labelClass'>".sp_filter_title_display($noTopicsLabel)." \n";
		$out.= "</span>\n";
	}

	$out.= "</div>\n";
	$out = apply_filters('sph_ForumIndexLastPost', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexModerators()
#	Display Forum moderators
#	Scope:	Forum sub Loop
#	Version: 5.2
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexModerators($args='', $label='') {
	global $spGlobals, $spThisForum;
	$defs = array('tagId'    		=> 'spForumModerators%ID%',
				  'tagClass' 		=> 'spForumModeratorList',
				  'listClass'		=> 'spInRowLabel',
				  'labelClass'		=> 'spRowDescription',
                  'showEmpty'       => 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexModerators_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$listClass		= esc_attr($listClass);
	$labelClass		= esc_attr($labelClass);
	$showEmpty		= (int) $showEmpty;
	$echo			= (int) $echo;
	$get			= (int) $get;

	$tagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	$mods = $spGlobals['forum_moderators']['users'][$spThisForum->forum_id];
	if ($get) return $mods;

    # build mods list with name display
    if (!empty($mods)) {
        $modList = '';
        $first = true;
        foreach ($mods as $mod) {
            if (!$first) $modList.= ', ';
            $first = false;
            $modList.= sp_build_name_display($mod['user_id'], $mod['display_name'], true);
        }
    } else if ($showEmpty) {
        $modList = 'none';
    } else {
        return '';
    }

	$out = "<div id='$tagId' class='$tagClass'>\n";
	$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."</span>\n";
	$out.= "<span class='$listClass'>$modList</span>\n";
	$out.= "</div>\n";
	$out = apply_filters('sph_ForumIndexModerators', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_ForumIndexSubForums()
#	Display Sub Forums below parent
#	Scope:	Forum sub Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_ForumIndexSubForums($args='', $label='', $toolTip='') {
	global $spThisForum, $spThisForumSubs;

	if (empty($spThisForumSubs)) return;

	$defs = array('tagId'    		=> 'spForumIndexSubForums%ID%',
				  'tagClass' 		=> 'spInRowSubForums',
				  'labelClass'		=> 'spInRowLabel',
				  'linkClass'		=> 'spInRowSubForumlink',
				  'icon'			=> 'sp_SubForumIcon.png',
				  'unreadIcon'		=> 'sp_SubForumIcon.png',
				  'iconClass'		=> 'spIconSmall',
				  'topicCount'		=> 1,
				  'allNested'		=> 1,
				  'stack'			=> 0,
				  'truncate'		=> 0,
				  'echo'			=> 1,
				  'get'				=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_ForumIndexSubForums_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId			= esc_attr($tagId);
	$tagClass		= esc_attr($tagClass);
	$labelClass		= esc_attr($labelClass);
	$linkClass		= esc_attr($linkClass);
	$icon			= sanitize_file_name($icon);
	$unreadIcon		= sanitize_file_name($unreadIcon);
	$iconClass		= esc_attr($iconClass);
	$topicCount		= (int) $topicCount;
	$allNested		= (int) $allNested;
	$stack			= (int) $stack;
	$truncate		= (int) $truncate;
	$echo			= (int) $echo;
	$get			= (int) $get;
	$toolTip		= esc_attr($toolTip);

	$thisTagId = str_ireplace('%ID%', $spThisForum->forum_id, $tagId);

	if ($get) return $spThisForumSubs;

	$out = "<div id='$thisTagId' class='$tagClass'>\n";
	if($stack) {
		$out.= "<ul class='$labelClass'><li>".sp_filter_title_display($label)."<ul>";
	} else {
		$out.= "<span class='$labelClass'>".sp_filter_title_display($label)."</span>\n";
	}
	foreach ($spThisForumSubs as $sub) {
		if ($sub->parent == $spThisForum->forum_id || $allNested == true) {
			$thisToolTip = str_ireplace('%NAME%', htmlspecialchars($sub->forum_name, ENT_QUOTES, SFCHARSET), $toolTip);
			if($stack) $out.= "<li>";

            if ($sub->unread) {
    			if (!empty($unreadIcon)) $out.= "<img src='".SPTHEMEICONSURL.$unreadIcon."' class='$iconClass' alt=''/>\n";
            } else {
    			if (!empty($icon)) $out.= "<img src='".SPTHEMEICONSURL.$icon."' class='$iconClass' alt=''/>\n";
            }

			$thisTagId = str_ireplace('%ID%', $sub->forum_id, $tagId);
			$out.= "<a href='$sub->forum_permalink' id='$thisTagId' class='$linkClass vtip' title='$thisToolTip'>".sp_truncate($sub->forum_name, $truncate)."</a>\n";
			if ($topicCount) $out.= " ($sub->topic_count)\n";
			if($stack) $out.= "</li>";
		}
	}
	if($stack) $out.= "</ul></li></ul>";

	$out.= "</div>\n";
	$out = apply_filters('sph_ForumIndexSubForums', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

# --------------------------------------------------------------------------------------
#
#	sp_NoForumsInGroupMessage()
#	Display Message when no Forums are found in a Group
#	Scope:	Forum Loop
#	Version: 5.0
#
# --------------------------------------------------------------------------------------
function sp_NoForumsInGroupMessage($args='', $definedMessage='') {
	global $spForumView;
	$defs = array('tagId'		=> 'spNoForumsInGroupMessage',
				  'tagClass'	=> 'spMessage',
				  'echo'		=> 1,
				  'get'			=> 0,
				  );
	$a = wp_parse_args($args, $defs);
	$a = apply_filters('sph_NoForumsInGroupMessage_args', $a);
	extract($a, EXTR_SKIP);

	# sanitize before use
	$tagId		= esc_attr($tagId);
	$tagClass	= esc_attr($tagClass);
	$echo		= (int) $echo;
	$get		= (int) $get;

	if ($get) return sp_filter_title_display($definedMessage);

	$out = "<div id='$tagId' class='$tagClass'>".sp_filter_title_display($definedMessage)."</div>\n";
	$out = apply_filters('sph_NoForumsInGroupMessage', $out, $a);

	if ($echo) {
		echo $out;
	} else {
		return $out;
	}
}

?>