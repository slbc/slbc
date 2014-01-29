<?php
/*
Simple:Press
DESC:
$LastChangedDate: 2013-09-14 14:49:56 -0700 (Sat, 14 Sep 2013) $
$Rev: 10682 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE ADMIN
#	Loaded by core - globally required by back end/admin for all pages
#
# ==========================================================================================

# ------------------------------------------------------------------
# spa_load_menu_css()
#
# Filter Call
# Loads the forum additions to the WP admin menu
# ------------------------------------------------------------------
function spa_load_menu_css() {
	$spMenuStyleUrl = SFADMINCSS.'spa-menu.css';
	wp_register_style('spMenuStyle', $spMenuStyleUrl);
	wp_enqueue_style( 'spMenuStyle');
}

function spa_load_updater() {
	if (empty($_GET['action']) || ($_GET['action'] != 'do-core-reinstall' && $_GET['action'] != 'do-core-upgrade')) include_once(SPBOOT.'admin/spa-admin-updater-class.php');
}

# Set the uninstall flag if required
function spa_check_removal() {
	if (isset($_GET['spf']) && $_GET['spf'] == 'uninstall') sp_update_option('sfuninstall', true);
}

# ------------------------------------------------------------------
# spa_block_admin()
#
# Blocks normal users from accessing WP admin area
# ------------------------------------------------------------------
function spa_block_admin() {
	global $wp_roles, $current_user;

	# Is this the admin interface?
	if (strstr(strtolower($_SERVER['REQUEST_URI']),'/wp-admin/') &&
		!strstr(strtolower($_SERVER['REQUEST_URI']),'async-upload.php') &&
		!strstr(strtolower($_SERVER['REQUEST_URI']),'admin-ajax.php')) {
		# get the user level and required level to access admin pages
		$sfblock = sp_get_option('sfblockadmin');
		if ($sfblock['blockadmin'] && !empty($sfblock['blockroles'])) {
			$role_matches = array_intersect_key($sfblock['blockroles'], array_flip($current_user->roles));
			$access = in_array(1, $role_matches);
			# block admin if required
			$is_moderator = sp_get_member_item($current_user->ID, 'moderator');
			if (!sp_current_user_can('SPF Manage Options') &&
				!sp_current_user_can('SPF Manage Forums') &&
				!sp_current_user_can('SPF Manage Components') &&
				!sp_current_user_can('SPF Manage User Groups') &&
				!sp_current_user_can('SPF Manage Permissions') &&
				!sp_current_user_can('SPF Manage Tags') &&
				!sp_current_user_can('SPF Manage Users') &&
				!sp_current_user_can('SPF Manage Profiles') &&
				!sp_current_user_can('SPF Manage Admins') &&
				!sp_current_user_can('SPF Manage Toolbox') &&
				!$is_moderator &&
				!$access
				) {
				if ($sfblock['blockprofile']) {
					$redirect = sp_url('profile');
				} else {
					$redirect = $sfblock['blockredirect'];
				}
				wp_redirect($redirect, 302);
			}
		}
	}
}

# compatability function for php 4 and array_intersect_key
if (!function_exists('array_intersect_key')) {
	function array_intersect_key ($isec, $arr2) {
		$argc = func_num_args();
		for ($i = 1; !empty($isec) && $i < $argc; $i++) {
			 $arr = func_get_arg($i);
			 foreach ($isec as $k => $v) {
				 if (!isset($arr[$k])) unset($isec[$k]);
			 }
		}
		return $isec;
	}
}
# ------------------------------------------------------------------
# spa_permalink_changed()
#
# Triggered by permalink changed action passing in old and new
# ------------------------------------------------------------------
function spa_permalink_changed($old, $new) {
	global $wp_rewrite;

	if (empty($new)) {
		$perm = user_trailingslashit(SFSITEURL).'?page_id='.sp_get_option('sfpage');
		sp_update_option('sfpermalink', $perm);
	} else {
		$perm = user_trailingslashit(SFSITEURL.sp_get_option('sfslug'));
		sp_update_option('sfpermalink', $perm);
		$wp_rewrite->flush_rules();
	}
    sp_update_permalink();
}

# ????
function spa_add_plugin_action($links, $plugin) {
	global $spStatus;

	if ($plugin == 'simple-press/sp-control.php') {
		if ($spStatus != 'ok') {
			# Install or Upgrade
			$actionlink = '<a href="'.admin_url('admin.php?page='.SPINSTALLPATH).'">'.spa_text($spStatus).'</a>';
			array_unshift( $links, $actionlink );
		} else {
			# Uninstall
			if (sp_get_option('sfuninstall') == false) {
				$param['spf']='uninstall';
				$passURL = add_query_arg($param, $_SERVER['REQUEST_URI']);
				$msg = sprintf('Are You Sure? %sThis option will REMOVE ALL FORUM DATA %safter deactivating Simple:Press %s Press OK to prepare for data removal', '\n\n', '\n', '\n\n');
				$actionlink = '<a href="javascript: if(confirm(\''.$msg.'\')) {window.location=\''.$passURL.'\';}">'.spa_text('Uninstall').'</a>';
				array_unshift( $links, $actionlink );
			}
		}
	}
	return $links;
}

# ------------------------------------------------------------------
# spa_activate_plugin()
#
# Reloads the rewrite rules just in case
# Handles activation for cron jobs
# ------------------------------------------------------------------
function spa_activate_plugin() {
	global $spStatus;

	if ($spStatus == 'ok') {
		# set up daily transient clean up cron
		wp_clear_scheduled_hook('sph_transient_cleanup_cron');
		wp_schedule_event(time(), 'daily', 'sph_transient_cleanup_cron');

		# set up hourly stats generation
		wp_clear_scheduled_hook('sph_stats_cron');
		wp_schedule_event(time(), 'sp_stats_interval', 'sph_stats_cron');

		# set up weekly news check
		wp_clear_scheduled_hook('sph_news_cron');
		wp_schedule_event(time(), 'sp_news_interval', 'sph_news_cron');

		# set up user auto removal cron job
		wp_clear_scheduled_hook('sph_cron_user');
		$sfuser = sp_get_option('sfuserremoval');
		if ($sfuser['sfuserremove']) wp_schedule_event(time(), 'daily', 'sph_cron_user');

		sp_update_permalink(true);
	}

	do_action('sph_activated');
}

# ------------------------------------------------------------------
# spa_deactivate_plugin()
#
# Removes all forum data prior to uninstall
# Handles deactivation for cron jobs
# ------------------------------------------------------------------
function spa_deactivate_plugin() {
	if (sp_get_option('sfuninstall')) { # uninstall - remove all data
		# remove any admin capabilities
		$admins = spdb_table(SFMEMBERS, "admin=1");
		foreach ($admins as $admin) {
			$user = new WP_User($admin->user_id);
			$user->remove_cap('SPF Manage Options');
			$user->remove_cap('SPF Manage Forums');
			$user->remove_cap('SPF Manage User Groups');
			$user->remove_cap('SPF Manage Permissions');
			$user->remove_cap('SPF Manage Tags');
			$user->remove_cap('SPF Manage Components');
			$user->remove_cap('SPF Manage Admins');
			$user->remove_cap('SPF Manage Profiles');
			$user->remove_cap('SPF Manage Users');
			$user->remove_cap('SPF Manage Toolbox');
			$user->remove_cap('SPF Manage Plugins');
			$user->remove_cap('SPF Manage Themes');
		}

		# remove any installed tables
		$tables = sp_get_option('installed_tables');
		if ($tables) {
			foreach ($tables as $table) {
				spdb_query("DROP TABLE IF EXISTS $table");
			}
		}

		# Remove the Page record
		$sfpage = sp_get_option('sfpage');
		if (!empty($sfpage)) {
			spdb_query('DELETE FROM '.SFWPPOSTS.' WHERE ID='.sp_get_option('sfpage'));
		}

		# remove widget data
		delete_option('widget_spf');
		delete_option('widget_sforum');

		# remove any wp options we might have set
		delete_option('sfInstallID');
		delete_option('sp_storage1');
		delete_option('sp_storage2');

		# Now remove user meta data
		$optionlist = array(
			'sfadmin',
			'location',
			'msn',
			'skype',
			'icq',
			'facebook',
			'myspace',
			'twitter',
			'linkedin',
			'youtube',
			'googleplus',
			'sfuse_quicktags',
			'signature',
			'sigimage'
		);

		foreach ($optionlist as $option) {
			spdb_query('DELETE FROM '.SFUSERMETA." WHERE meta_key='$option';");
		}

		# let's remove our directories and storage
		global $spPaths;
		if (!empty($spPaths)) {
			foreach ($spPaths as $storage => $path) {
				# lets not remove plugins and themes
				if ($storage != 'plugins' && $storage != 'themes') sp_remove_dir(SF_STORE_DIR.'/'.$path);
			}
		}

		# send our uninstall action
		do_action('sph_uninstalled');
	}

	# remove the combined css and js cache files
	sp_clear_combined_css('all');
	sp_clear_combined_css('mobile');
	sp_clear_combined_css('tablet');

	# remove cron jobs for deactivaton or uninstall
	wp_clear_scheduled_hook('spf_cron_pm'); # left here for 5.0 who doesnt upgrade
	wp_clear_scheduled_hook('spf_cron_sitemap'); # left here for 5.0 who doesnt upgrade

	wp_clear_scheduled_hook('sph_cron_user');
	wp_clear_scheduled_hook('sph_transient_cleanup_cron');
	wp_clear_scheduled_hook('sph_stats_cron');
	wp_clear_scheduled_hook('sph_news_cron');

	# remove storage locations if so directed
	if (sp_get_option('removestorage')) {
		# remove configurable storage locations
		$locations = sp_get_option('sfconfig');
		if (!empty($locations)) {
			foreach ($locations as $location) {
				sp_remove_dir(SF_STORE_DIR.'/'.$location);
			}
		}

		# remove the languages folder if it exists
		sp_remove_dir(SF_STORE_DIR.'/forum-language');

		# remove the sp-resources folder if it exists
		sp_remove_dir(SF_STORE_DIR.'/sp-resources');
	}

	# send deactivated action
	do_action('sph_deactivated');
}

function spa_wp_discussion_avatar($list) {
	echo '<h3>'.spa_text('Currently, all WP avatars are being replaced by Simple:Press avatars. You can change this at');
	echo ': <a href="'.admin_url('admin.php?page=simple-press/admin/panel-profiles/spa-profiles.php&amp;tab=avatars').'">';
	echo spa_text('Forum - Profiles - Avatars');
	echo '</a>.';
	echo '</h3>';
}

# Dashboard Widgets and News from SP ==============================

# ------------------------------------------------------------------
# spa_dashboard_setup()
#
# Filter Call
# Sets up the forum advisory in the dashboard
# for forum admins and moderators only
# ------------------------------------------------------------------
function spa_dashboard_setup() {
	global $spNews;
	# standard forum widget
	wp_add_dashboard_widget('spa_dashboard_forum', spa_text('Forums'), 'spa_dashboard_forum');
	# News update widget
	$spNews = spa_check_for_news();
	if(!empty($spNews)) {
		wp_add_dashboard_widget('spa_dashboard_news', spa_text('Simple:Press News'), 'spa_dashboard_news');
		add_action('in_admin_footer', 'spa_remove_news');
	}
}

function spa_check_for_news() {
	$news = sp_get_sfmeta('news', 'news');
	if (!empty($news)) {
		if ($news[0]['meta_value']['show']) return $news[0]['meta_value']['news'];
	}
}

# ------------------------------------------------------------------
# spa_dashboard_forum()
#
# Filter Call
# Sets up the forum advisory in the dashboard
# ------------------------------------------------------------------
function spa_dashboard_forum() {
	global $spGlobals, $spThisUser, $spStatus;

	$out = '';

	# check we have an installed version
	if ($spStatus != 'ok') {
		$out.= '<div style="border: 1px solid #eeeeee; padding: 10px; font-weight: bold;">'."\n";
		$out.= '<p><img style="vertical-align:bottom;border:none;" src="'.SPTHEMEICONSURL.'sp_Information.png" alt="" />'."\n";
		$out.= '&nbsp;&nbsp;'.sprintf(spa_text('The forum is temporarily unavailable while awaiting %s'), $spStatus).'</p>';

		if ($spThisUser->admin) $out.= '&nbsp;&nbsp;<a style="text-decoration: underline;" href="'.SFADMINFORUM.'">'.spa_text('Perform Upgrade').'</a>';
		$out.= '</div>';
		echo $out;
		return;
	}

	$out.= '<div id="sf-dashboard">';
	echo $out;
	do_action('sph_dashboard_start');

	if ($spGlobals['admin']['sfdashboardstats']) {
		include_once(SF_PLUGIN_DIR.'/forum/content/sp-common-view-functions.php');
		include_once(SF_PLUGIN_DIR.'/forum/content/sp-template-control.php');
		echo '<br /><table class="sfdashtable">';
		echo '<tr>';
		echo '<td>';
		sp_OnlineStats('link_names=0', '<b>'.spa_text('Most Users Ever Online').': </b>', '<b>'.spa_text('Currently Online').': </b>', '<b>'.spa_text('Currently Browsing this Page').': </b>', spa_text('Guest(s)'));
		echo '</td>';
		echo '<td>';
		sp_ForumStats('', '<b>'.spa_text('Forum Stats').': </b>', spa_text('Groups').': ', spa_text('Forums').': ', spa_text('Topics').': ', spa_text('Posts').': ');
		echo '</td>';
		echo '<td>';
		sp_MembershipStats('', '<b>'.spa_text('Member Stats').': </b>', spa_text('There are %COUNT% Members'), spa_text('There have been %COUNT% Guest Posters'), spa_text('There are %COUNT% Moderators'), spa_text('There are %COUNT% Admins'));
		echo '</td>';
		echo '<td>';
		sp_TopPostersStats('link_names=0', '<b>'.spa_text('Top Posters').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_NewMembers('link_names=0', '<b>'.spa_text('Newest Members').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_ModsList('link_names=0', '<b>'.spa_text('Moderators').': </b>');
		echo '</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="4">';
		sp_AdminsList('link_names=0', '<b>'.spa_text('Administrators').': </b>');
		echo '</td>';
		echo '</tr></table><br />';
	}

	do_action('sph_dashboard_end');

	$out = '';
	$out.= '<p><br /><a href="'.sp_url().'">'.spa_text('Go To Forum').'</a></p>';
	$out.= '</div>';
	echo $out;
}

# ------------------------------------------------------------------
# spa_dashboard_news()
#
# Announcement dashboard widget
# ------------------------------------------------------------------
function spa_dashboard_news() {
	global $spNews;

	echo '<img src="'.SFCOMMONIMAGES.'sp-small-logo.png" alt="" style="float: left; margin: 0 15px 10px 0;" />';
	echo '<h4 style="padding-top: 8px; margin: 8px 0 5px 0;">'.spa_text('The latest news from').'<br />Simple:Press</h4>';
	echo '<div style="clear:both;"></div>';
	echo ($spNews);
	echo '<div style="clear:both;"></div>';
	$site = SFHOMEURL.'index.php?sp_ahah=remove-news&amp;action=news&amp;sfnonce='.wp_create_nonce('forum-ahah');
	echo '<input type="button" value="'.spa_text('Remove').'" class="" onclick="spjRemoveNews(\''.$site.'\')"/>';
}

# Load inline script to remove news widget
function spa_remove_news() {
?>
	<script type="text/javascript">
	function spjRemoveNews(url) {
		jQuery(document).ready(function() {
			jQuery('#spa_dashboard_news').fadeOut();
			jQuery('#spa_dashboard_news').load(url);
		});
	}
	</script>
<?php
}

?>