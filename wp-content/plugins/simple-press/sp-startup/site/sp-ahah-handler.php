<?php
/*
Simple:Press
Public - ahah handler
$LastChangedDate: 2013-09-19 13:12:36 -0700 (Thu, 19 Sep 2013) $
$Rev: 10709 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
# SITE - This file loads at core level - all page loads
# handles all AHAH calls via index.php and WP parse hook
#
# ==========================================================================================

function sp_ahah_handler($wp) {
   # only process our ahah requests
	if (array_key_exists('sp_ahah', $wp->query_vars)) {
		header('Content-Type: text/html; charset='.get_option('blog_charset'));

		# verify proper ahah nonce
		$check = check_ajax_referer('forum-ahah', 'sfnonce', false);
		if (!$check) {
			echo '<div class="spMessage">';
			echo '<p>'.sp_text('Access denied - ajax nonce check failed').'</p>';
			global $spThisUser;
			if (empty($spThisUser->ID)) {
				echo '<p>'.sp_text('Do you need to log in?').' (<a href="'.site_url('wp-login.php', 'login_post').'">'.sp_text('Log in').'</a>)</p>';
				echo '</div>';
				$sflogin = sp_get_option('sflogin');
				$redirect = (!empty($sflogin['sfloginurl']) && empty($redirect)) ? $sflogin['sfloginurl'] : '';
				$args = array('action' => 'login', 'redirect_to' => $redirect);
				$login_url = add_query_arg($args, esc_url(wp_login_url()));
				sp_redirect($login_url);
			}
			echo '</div>';
			die();
		}

		# process the request
		switch ($wp->query_vars['sp_ahah']) {
			case 'newpostpopup':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-newpostpopup.php');
				break;

			case 'acknowledge':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-acknowledge.php');
				break;

			case 'admintoollinks':
				include (SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-admintoollinks.php');
				break;

			case 'admintools':
				include (SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-admintools.php');
				break;

			case 'autoupdate':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-autoupdate.php');
				break;

			case 'permissions':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-permissions.php');
				break;

			case 'quote':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-quote.php');
				break;

			case 'remove-notice':
				include(SF_PLUGIN_DIR.'/forum/content/ahah/sp-ahah-notice.php');
				break;

			case 'multiselect':
				include(SF_PLUGIN_DIR.'/admin/library/ahah/spa-ahah-multiselect.php');
				break;

			case 'profile':
				include(SF_PLUGIN_DIR.'/forum/profile/ahah/sp-ahah-profile.php');
				break;

			case 'profile-save':
				include(SF_PLUGIN_DIR.'/forum/profile/ahah/sp-ahah-profile-save.php');
				break;

			case 'post':
				include(SF_PLUGIN_DIR.'/forum/library/sp-post.php');
				break;

			case 'search':
				include(SF_PLUGIN_DIR.'/forum/library/sp-search.php');
				break;

			case 'help':
				include(SF_PLUGIN_DIR.'/admin/library/ahah/spa-ahah-help.php');
				break;

			case 'admins-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-admins/ahah/spa-ahah-admins-loader.php');
				break;

			case 'components-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-components/ahah/spa-ahah-components-loader.php');
				break;

			case 'forums-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-forums/ahah/spa-ahah-forums-loader.php');
				break;

			case 'options-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-options/ahah/spa-ahah-options-loader.php');
				break;

			case 'permissions-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-permissions/ahah/spa-ahah-permissions-loader.php');
				break;

			case 'profiles-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-profiles/ahah/spa-ahah-profiles-loader.php');
				break;

			case 'toolbox-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-toolbox/ahah/spa-ahah-toolbox-loader.php');
				break;

			case 'integration-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-integration/ahah/spa-ahah-integration-loader.php');
				break;

			case 'plugins-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-plugins/ahah/spa-ahah-plugins-loader.php');
				break;

			case 'themes-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-themes/ahah/spa-ahah-themes-loader.php');
				break;

			case 'integration-perm':
				include(SF_PLUGIN_DIR.'/admin/panel-integration/ahah/spa-ahah-integration-perm.php');
				break;

			case 'usergroups-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-usergroups/ahah/spa-ahah-usergroups-loader.php');
				break;

			case 'users-loader':
				include(SF_PLUGIN_DIR.'/admin/panel-users/ahah/spa-ahah-users-loader.php');
				break;

			case 'components':
				include(SF_PLUGIN_DIR.'/admin/panel-components/ahah/spa-ahah-components.php');
				break;

			case 'forums':
				include(SF_PLUGIN_DIR.'/admin/panel-forums/ahah/spa-ahah-forums.php');
				break;

			case 'profiles':
				include(SF_PLUGIN_DIR.'/admin/panel-profiles/ahah/spa-ahah-profiles.php');
				break;

			case 'users':
				include(SF_PLUGIN_DIR.'/admin/panel-users/ahah/spa-ahah-users.php');
				break;

			case 'usergroups':
				include(SF_PLUGIN_DIR.'/admin/panel-usergroups/ahah/spa-ahah-usergroups.php');
				break;

			case 'usermapping':
				include(SF_PLUGIN_DIR.'/admin/panel-usergroups/ahah/spa-ahah-map-users.php');
				break;

			case 'memberships':
				include(SF_PLUGIN_DIR.'/admin/panel-usergroups/ahah/spa-ahah-memberships.php');
				break;

			case 'troubleshooting':
				include(SF_PLUGIN_DIR.'/admin/help/spa-ahah-troubleshooting.php');
				break;

			case 'upgrade':
				include(SPBOOT.'install/sp-upgrade.php');
				break;

			case 'install':
				include(SPBOOT.'install/sp-install.php');
				break;

			case 'uploader':
				include(SF_PLUGIN_DIR.'/admin/resources/jscript/ajaxupload/sf-uploader.php');
				break;

			case 'remove-news':
				include(SF_PLUGIN_DIR.'/admin/library/ahah/spa-ahah-general.php');
				break;

			case 'install-log':
				include(SF_PLUGIN_DIR.'/admin/panel-toolbox/ahah/spa-ahah-install-log.php');
				break;

			# unknown handler type - must be plugin page, so fire plugin specific handler action
			default:
				do_action('sph_ahah_handler_'.$wp->query_vars['sp_ahah']);
				break;
		}
	}
}

?>