<?php
/*
Simple:Press
DESC: Sitewide Functions back and front end
$LastChangedDate: 2013-10-01 07:51:24 -0700 (Tue, 01 Oct 2013) $
$Rev: 10772 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE
# 	Loaded by core - globally required by back end/admin for all pages
#
# ==========================================================================================

# ------------------------------------------------------------------
# sp_set_rewrite_rules()
# Setup the forum rewrite rules
# ------------------------------------------------------------------
function sp_set_rewrite_rules($rules) {
	global $wp_rewrite;

	$slug = sp_get_option('sfslug');

    $slugmatch = $slug;
    if ($wp_rewrite->using_index_permalinks()) $slug = str_replace('index.php/', '', $slug); # handle PATHINFO permalinks

    $slugmatch = apply_filters('sph_rewrite_rules_slug', $slugmatch);

    $sf_rules = array();
    $sf_rules = apply_filters('sph_rewrite_rules_start', $sf_rules, $slugmatch, $slug);

    # admin new posts list
	$sf_rules[$slugmatch.'/newposts/?$'] = 'index.php?pagename='.$slug.'&sf_newposts=all';

    # members list?
	$sf_rules[$slugmatch.'/members/?$'] = 'index.php?pagename='.$slug.'&sf_members=list';
	$sf_rules[$slugmatch.'/members/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_members=list&sf_page=$matches[1]';

    # match profile?
	$sf_rules[$slugmatch.'/profile/?$'] = 'index.php?pagename='.$slug.'&sf_profile=edit';
	$sf_rules[$slugmatch.'/profile/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_profile=show&sf_member=$matches[1]';
	$sf_rules[$slugmatch.'/profile/([^/]+)/edit/?$'] = 'index.php?pagename='.$slug.'&sf_profile=edit&sf_member=$matches[1]';

    # match forum and topic with pages
	$sf_rules[$slugmatch.'/rss/?$'] = 'index.php?pagename='.$slug.'&sf_feed=all'; # match main rss feed
	$sf_rules[$slugmatch.'/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_feed=all&sf_feedkey=$matches[1]'; # match main rss feed with feedkey
	$sf_rules[$slugmatch.'/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]'; # match forum
	$sf_rules[$slugmatch.'/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_page=$matches[2]'; # match forum with page
	$sf_rules[$slugmatch.'/([^/]+)/rss/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum'; # match forum rss feed
	$sf_rules[$slugmatch.'/([^/]+)/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_feed=forum&sf_feedkey=$matches[2]'; # match forum rss feed with feedkey
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]';  # match topic
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/page-([0-9]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_page=$matches[3]'; # match topic with page
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/rss/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_feed=topic'; # match topic rss feed
	$sf_rules[$slugmatch.'/([^/]+)/([^/]+)/rss/([^/]+)/?$'] = 'index.php?pagename='.$slug.'&sf_forum=$matches[1]&sf_topic=$matches[2]&sf_feed=topic&sf_feedkey=$matches[3]'; # match topic rss feed with feedkey

    $sf_rules = apply_filters('sph_rewrite_rules_end', $sf_rules, $slugmatch, $slug);
	$rules = array_merge($sf_rules, $rules);

	return $rules;
}

# ------------------------------------------------------------------
# sp_set_query_vars()
# Setup the forum query variables
# ------------------------------------------------------------------
function sp_set_query_vars($vars) {
    # forums and topics
	$vars[] = 'sf_forum';
	$vars[] = 'sf_topic';
	$vars[] = 'sf_page';

    # new posts
 	$vars[] = 'sf_newposts';

    # ahah handler
	$vars[] = 'sp_ahah';

    # members list
	$vars[] = 'sf_members';

    # profile
	$vars[] = 'sf_profile';
	$vars[] = 'sf_member';

	$vars[] = 'sf_feed';
	$vars[] = 'sf_feedkey';

    $vars = apply_filters('sph_query_vars', $vars);

	return $vars;
}

# ------------------------------------------------------------------
# sp_get_system_status()
# Determine if forum can be run or if it requires install/upgrade
# Sets $spStatus to 'ok', 'Install' or 'Upgrade'
# ------------------------------------------------------------------
function sp_get_system_status() {
	global $wpdb, $spStatus;

	$current_version = sp_get_option('sfversion');
	$current_build = sp_get_option('sfbuild');
	if(!isset($current_version) && !isset($current_build)) {
		$spStatus = 'Install';
		return;
	}

	# First find out if build number has changed but check first against the log
	# in case it is a glitch or has been changed in the toolbox
	if ((empty($current_version) || version_compare($current_version, '1.0', '<')) || (($current_build != SPBUILD) || ($current_version != SPVERSION))) {
        # check if user is attempting to 'downgrade'
        # if so flag as upgrade and catch the downgrade in the load install routine
        if (SPBUILD < $current_build || SPVERSION < $current_version) {
			if (!defined('SFADMINFORUM')) define('SFADMINFORUM', admin_url('admin.php?page=simple-press/admin/panel-forums/spa-forums.php'));
    		$spStatus = 'Upgrade';
	       	return;
        }

        # so get the values from the log and compare
		# there is a chance the option records got corrupted (?) or the build has been manually reset
		# so get the values from the log and compare
		# We check the last log entry build number
		# aganst the option build number and if option build number is less
		# we update it to the last log entry
		$sql = 'SELECT build, version FROM '.SFLOG.' ORDER BY id DESC LIMIT 1';
		$log = $wpdb->get_results($sql, ARRAY_A);
		if($log) {
			if ($current_build != $log[0]['build']) {
				# But if the force upgrade flag is set we do NOT reset build number
				if (sp_get_option('sfforceupgrade') == false) {
					sp_update_option('sfbuild', $log[0]['build']);
					sp_update_option('sfversion', $log[0]['version']);
					$current_build = $log[0]['build'];
					$current_version = $log[0]['version'];
				}
			}
		}
	}

	# Has the systen been installed?
	if (empty($current_version) || version_compare($current_version, '1.0', '<')) {
		$spStatus = 'Install';
		return;
	}

	# Base already installed - check Version and Build Number
	if (($current_build < SPBUILD) || ($current_version != SPVERSION)) {
		if (!defined('SFADMINFORUM')) define('SFADMINFORUM', admin_url('admin.php?page=simple-press/admin/panel-forums/spa-forums.php'));
		$spStatus = 'Upgrade';
		return;
	}

	$spStatus = apply_filters('sph_system_status', 'ok');

	if($spStatus == 'ok') {
		# Set up error reporting
		$wpdb->hide_errors();
		set_error_handler('sp_construct_php_error');
	}
	return $spStatus;
}

# ------------------------------------------------------------------
# sp_localisation()
# Setup the forum localisation
# ------------------------------------------------------------------
function sp_localisation() {
	# i18n support
	global $spPaths;

	$locale = get_locale();

    $bothSpecial = apply_filters('sph_load_both_textdomain', array('sp_ahah=permissions'));
    $adminSpecial = apply_filters('sph_load_admin_textdomain', array('&loadform', 'sp_ahah=help', 'sp_ahah=multiselect', 'sp_ahah=integration-perm', 'sp_ahah=components', 'sp_ahah=forums', 'sp_ahah=profiles', 'sp_ahah=users', 'sp_ahah=usergroups'));

	if (sp_strpos_arr($_SERVER['QUERY_STRING'], $bothSpecial) !== false) {
        $mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp'].'/spa-'.$locale.'.mo';
        load_textdomain('spa', $mofile);
    	$mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp'].'/sp-'.$locale.'.mo';
        $mofile = apply_filters('sph_localization_mo', $mofile);
        load_textdomain('sp', $mofile);
	} else if (is_admin() || sp_strpos_arr($_SERVER['QUERY_STRING'], $adminSpecial) !== false) {
        $mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp'].'/spa-'.$locale.'.mo';
        load_textdomain('spa', $mofile);
	} else {
    	$mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp'].'/sp-'.$locale.'.mo';
        $mofile = apply_filters('sph_localization_mo', $mofile);
        load_textdomain('sp', $mofile);
	}
}

function sp_plugin_localisation($domain) {
	global $spPaths;
	$locale = get_locale();
	$mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp-plugins'].'/'.$domain.'-'.$locale.'.mo';
    $mofile = apply_filters('sph_localization_plugin_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
}

function sp_theme_localisation($domain) {
	global $spPaths, $spGlobals;
	$locale = get_locale();
	$mofile = WP_CONTENT_DIR.'/'.$spPaths['language-sp-themes'].'/'.$domain.'-'.$locale.'.mo';
    $mofile = apply_filters('sph_localization_theme_mo', $mofile, $domain);
	load_textdomain($domain, $mofile);
	$spGlobals['themedomain'] = $domain;
}

# ------------------------------------------------------------------
# sp_feed()
# Redirects RSS feed requests
# ------------------------------------------------------------------
function sp_feed() {
	global $spVars, $wp_query;

	if (is_page() && $wp_query->post->ID == sp_get_option('sfpage') && !empty($spVars['feed'])) {
		include_once(SPBOOT.'/sp-load-forum.php');

		#check for old style feed urls - load query args into spVars for new style
		if (isset($_GET['xfeed'])) {
            $spVars['feed'] = sp_esc_str($_GET['xfeed']);
   		    $spVars['feedkey'] = sp_esc_str($_GET['feedkey']);
   		    $spVars['forumslug'] = sp_esc_str($_GET['forum']);
   		    $spVars['topicslug'] = sp_esc_str($_GET['topic']);
		}

		# do we have the clunky group rss feed?
    	if (isset($_GET['group'])) $spVars['feed'] = 'group';

		# new style rss feed urls
		if (!empty($spVars['feed'])) {
			include SF_PLUGIN_DIR.'/forum/feeds/sp-feeds.php';
			exit;
		}
	}
}

# lets make wp think our sp feeds are feeds to keep folks from mucking them
function sp_is_feed_check($query) {
    if (!is_admin() && $query->is_main_query() && !empty($query->query_vars['sf_feed'])) {
        $query->set('feed', 'forum');
        $query->is_feed = true;
    }
    return $query;
}

function sp_get_permalink($link, $id, $sample) {
	global $spIsForum;
	if($spIsForum) {
		if ($id == sp_get_option('sfpage') && in_the_loop()) $link = sp_canonical_url();
	}
	return $link;
}

# ------------------------------------------------------------------
# sp_update_permalink()
#
# Updates the forum permalink. Called from plugin activation and
# upon each display of a forum admin page. If the permalink is
# found to have changed the rewrite rules are also flushed
# ------------------------------------------------------------------
function sp_update_permalink($autoflush=false) {
	global $wp_rewrite;

	$slug = sp_get_option('sfslug');
	if ($slug) {
		$sfperm = sp_get_option('sfpermalink');

		# go for whole row to ensure it is cached
		$page = spdb_table(SFWPPOSTS, "post_name='$slug' AND post_status='publish' AND post_type='page'", 'row');
		if ($page) {
			sp_update_option('sfpage', $page->ID);
			$perm = get_permalink($page->ID);
			if (get_option('page_on_front') == $page->ID && get_option('show_on_front') == 'page') {
   				$perm = rtrim($perm, '/');
			 	if ($wp_rewrite->using_permalinks()) {
                    $perm.= '/'.$slug;
                } else {
                    $perm.= '/?page_id='.$page->ID;
                }
			}
			# only update it if base permalink has been changed
			if ($sfperm != $perm) {
				sp_update_option('sfpermalink', $perm);
				$sfperm = $perm;
				$autoflush = true;
			}
		}
	}

	if ($autoflush) $wp_rewrite->flush_rules();
	return $sfperm;
}

# --------------------------------------------------------------------------------------
#
#	sp_wp_avatar()
#	hooks into the wp get_avatar() function return value
#	Scope:	Site
#
#	avatar:      the wp (or wp plugin) avatar img tag
#	id_or_email: user id, email address, or comment object for avatar
#	size:		 Display size in pixels
#
# --------------------------------------------------------------------------------------
function sp_wp_avatar($avatar, $id_or_email, $size) {
	include_once(SF_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php');
    sp_forum_api_support();

	# this could be user id, email or comment object
	# if comment object want a user id or email address
	# pass other two striaght through
	if (is_object($id_or_email)) { # comment object passed in
		if (!empty($id_or_email->user_id)) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			$arg = ($user) ? $id : '';
		} elseif (!empty($id_or_email->comment_author_email)) {
			$arg = $id_or_email->comment_author_email;
		}
	} else {
		$arg = $id_or_email;
	}

	# replace the wp avatar image src with our spf img src
	$pattern = '/<img[^>]+src[\\s=\'"]+([^"\'>\\s]+)/is';
	$sfavatar = sp_UserAvatar("echo=0&link=none&size=$size&context=user&wp=$avatar", $arg);
	preg_match($pattern, $sfavatar, $sfmatch);
	preg_match($pattern, $avatar, $wpmatch);
	$avatar = str_replace($wpmatch[1], $sfmatch[1], $avatar);
	return $avatar;
}

function sp_mobile_check() {
	global $spMobile, $spDevice;

	$spDevice = sp_detect_device();
	if ($spDevice == 'mobile' || $spDevice == 'tablet') $spMobile = true;
	$spMobile = apply_filters('sph_mobile_check', $spMobile);
	$spDevice = apply_filters('sph_device_check', $spDevice);
}

# ------------------------------------------------------------------
# sp_load_blog_script()
# Loads any JS needed on blog when not a forum view
# ------------------------------------------------------------------
function sp_load_blog_script() {
    do_action('sph_blog_scripts_start');
    do_action('sph_blog_scripts_end');
}

# ------------------------------------------------------------------
# sp_load_blog_support()
# Loads any support needed on blog when not a forum view
# ------------------------------------------------------------------
function sp_load_blog_support() {
	global $wp_query, $spGlobals;

	# Grab WP post object for use in action
	$wpPost = $wp_query->get_queried_object();
	do_action('sph_blog_support_start', $wpPost);

	do_action('sph_blog_support_end', $wpPost);
}

# ------------------------------------------------------------------
# spa_register_math()
#
# Filter Call
# Sets up the spam math on registration form
# ------------------------------------------------------------------
function spa_register_math() {
	$sflogin = sp_get_option('sflogin');
	if ($sflogin['sfregmath']) {
		$spammath = sp_math_spam_build();
		$uKey = sp_get_option('spukey');
		$uKey1 = $uKey.'1';
		$uKey2 = $uKey.'2';

		$out = '<input type="hidden" size="30" name="url" value="" /></p>'."\n";
		$out.= '<label>'.sp_text('Math Required!').'<br />'."\n";
		$out.= sprintf(sp_text('What is the sum of: %s %s + %s %s'), '<strong>', $spammath[0], $spammath[1], '</strong>').'</label>'."\n";
		$out.= '<input class="input" type="text" tabindex="3" id="'.$uKey1.'" name="'.$uKey1.'" value="" />'."\n";
		$out.= '<input type="hidden" name="'.$uKey2.'" value="'.$spammath[2].'" />'."\n";
		$out.= '<br />';
		echo $out;
	}
}

# ------------------------------------------------------------------
# spa_register_error()
#
# Filter Call
# Sets up the spam math error is required
#	$errors:	registration errors array
# ------------------------------------------------------------------
function spa_register_error($errors) {
	global $spIsForum;

	$sflogin = sp_get_option('sflogin');
	if ($sflogin['sfregmath']) {
		$spamtest = sp_spamcheck();
		if ($spamtest[0] == true) {
			$errormsg = '<b>ERROR</b>: '.$spamtest[1];

			if ($spIsForum == false) {
				$errors->add('Bad Math', $errormsg);
			} else {
				$errors['math_check'] = $errormsg;
			}
		}
	}
	return $errors;
}

function sp_get_current_sp_theme() {
	global $spDevice;

	if ($spDevice == 'mobile') {
		$theme = sp_get_option('sp_mobile_theme');
		if ($theme['active']) return $theme;
	}

	if ($spDevice == 'tablet') {
		$theme = sp_get_option('sp_tablet_theme');
		if ($theme['active']) return $theme;
	}

	return sp_get_option('sp_current_theme');
}

# ------------------------------------------------------------------
# sp_display_inspector()
#
# Displays data objects when needed
# 	$dName		Object key name
#	$dObject	The data object to display
# ------------------------------------------------------------------
function sp_display_inspector($dName, $dObject) {
	global $spThisUser;

    if (empty($spThisUser->inspect)) return;

	$i = $spThisUser->inspect;

	if ($dName == 'control') {
		# spVars, spGlobals, spThisUser
		if (array_key_exists('con_spVars', $i) && $i['con_spVars']) {
			global $spVars;
			ashow($spVars, $spThisUser->ID, 'spVars');
		}
		if (array_key_exists('con_spGlobals', $i) && $i['con_spGlobals']) {
			global $spGlobals;
			ashow($spGlobals, $spThisUser->ID, 'spGlobals');
		}
		if (array_key_exists('con_spThisUser', $i) && $i['con_spThisUser']) {
			ashow($spThisUser, $spThisUser->ID, 'spThisUser');
		}
	} else {
		# called direct from class file
		if (array_key_exists($dName, $i) && $i[$dName]) {
			if (!empty($dObject)) {
				$dName = ltrim(strrchr($dName, '_'), '_');
				ashow($dObject, $spThisUser->ID, $dName);
			}
		}
	}
}

# ------------------------------------------------------------------
# sp_display_inspector_profile_XXX()
#
# Displays profile data objects when needed - special
# ------------------------------------------------------------------
add_filter('sph_ProfileShowHeader', 'sp_display_inspector_profile_popup', 1, 3);
function sp_display_inspector_profile_popup($out, $spProfileUser, $a) {
	sp_display_inspector('pro_spProfileUser', $spProfileUser);
	return $out;
}

add_action('sph_profile_edit_after_tabs', 'sp_display_inspector_profile_edit');
function sp_display_inspector_profile_edit() {
	global $spProfileUser;
	sp_display_inspector('pro_spProfileUser', $spProfileUser);
}

# 5.3
# function for aborting forum display and outputting a message instead (ie upgrade needed)
function sp_abort_display_forum() {
    global $spStatus;

    # are we awaiting an upgrade - outptut message and bail
	if ($spStatus != 'ok') {
	   $message = sp_forum_unavailable();
       return $message;
    }

    # let plugins hook in
    $message = apply_filters('sph_alternate_forum_content', '');

    return $message;
}

function sp_forum_unavailable() {
	global $current_user;

	$out = '';
	$out.= '<div id="spMainContainer">';
	$out.= '<div class="spMessage">';
	$out.= '<p><img src="'.SPTHEMEICONSURL.'sp_Information.png" alt="" /> ';
	$out.= sp_text('Sorry, the forum is temporarily unavailable while it is being upgraded to a new version.').'</p>';
	if (sp_is_forum_admin($current_user->ID)) {
		$out.= '<a href="'.SFADMINFORUM.'">'.sp_text('Click here to perform the upgrade').'</a>';
	}
	$out.= '</div>';
	$out.= '</div>';
	$out = apply_filters('sph_forum_unavailable', $out);
	return $out;
}

?>