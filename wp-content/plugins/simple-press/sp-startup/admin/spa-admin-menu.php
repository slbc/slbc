<?php
/*
Simple:Press
Desc:
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# ==========================================================================================
#
#	CORE ADMIN
# 	Loaded by global admin - globally required by back end/admin for all pages
#
# ==========================================================================================

function spa_admin_menu() {
	global $spStatus, $sfadminpanels, $spThisUser, $plugin_page, $submenu;

	if ($spStatus == 'ok') {
        # set up our default admin menu
        spa_setup_admin_menu();

        if (spa_can_access_admin_panels()) {
            # build our admin nav menu
            $adminparent = '';
            foreach ($sfadminpanels as $panel) {
                if ($panel[7] && ((sp_current_user_can($panel[1])) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator)))) {
            		if (empty($adminparent)) {
                        $adminparent = $panel[2];
                        add_object_page('Simple:Press', spa_text('Forum'), 'read', $adminparent, '', 'div');
                        add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);

                        # hack for wp stubborness of not wanting singular submenu under a menu item
            			$submenu[$adminparent][1] = null;
                		if (strpos($plugin_page, 'simple-press') !== false) add_submenu_page($adminparent, '', '', 'read', $adminparent);
                    } else {
                        add_submenu_page($adminparent, esc_attr($panel[0]), esc_attr($panel[0]), 'read', $panel[2]);
                    }
       			}
            }
		} elseif (current_user_can('administrator')) {
    		$parent = 'simple-press/sp-startup/admin/spa-admin-notice.php';
			add_object_page('Simple:Press', spa_text('Forum'), 'manage_options', $parent, '', 'div');
			add_submenu_page($parent, spa_text('WP Admin Notice'), spa_text('WP Admin Notice'), 'read', 'simple-press/sp-startup/admin/spa-admin-notice.php');

            # hack for wp stubborness of not wanting singular submenu under a menu item
			$submenu[$parent][1] = null;
    		if (strpos($plugin_page, 'simple-press') !== false) add_submenu_page($parent, '', '', 'read', $parent);
        }
	} else {
		$parent = SPINSTALLPATH;
		add_object_page('Simple:Press', spa_text('Forum'), 'activate_plugins', $parent, '', 'div');

		if ($spStatus == 'Install') {
			add_submenu_page($parent, spa_text('Install Simple:Press'), spa_text('Install Simple:Press'), 'activate_plugins', $parent);
			$submenu[SPINSTALLPATH][1] = null;
		} else {
			add_submenu_page($parent, spa_text('Upgrade Simple:Press'), spa_text('Upgrade Simple:Press'), 'activate_plugins', $parent);
			$submenu[SPINSTALLPATH][1] = null;
		}

        # hack for wp stubborness of not wanting singular submenu under a menu item
		if (strpos($plugin_page, 'simple-press') !== false) {
			add_submenu_page($parent, '', '', 'read', $plugin_page);
			$plugin_page = $parent;
		}
	}

    # let plugins add new wp admin nav panels
    do_action('sph_admin_menu');
}

function spa_can_access_admin_panels() {
    global $sfadminpanels, $spThisUser;

    foreach ($sfadminpanels as $panel) {
        if (sp_current_user_can($panel[1]) || ($panel[0] == 'Admins' && ($spThisUser->admin || $spThisUser->moderator))) return true;
    }
    return false;
}

function spa_setup_admin_menu() {
	global $sfadminpanels, $sfactivepanels, $sfatooltips;

	# Get correct tooltips file
	$lang = WPLANG;
	if (empty($lang)) $lang = 'en';
	$ttpath = SPHELP.'admin/tooltips/admin-menu-tips-'.$lang.'.php';
	if (file_exists($ttpath) == false) $ttpath = SPHELP.'admin/tooltips/admin-menu-tips-en.php';
	if (file_exists($ttpath)) include_once($ttpath);

    $sfadminpanels = $sfactivepanels = array();

    /**
     * admin panel array elements
     * 0 - panel name
     * 1 - spf capability to view
     * 2 - admin file
     * 3 - tool tip
     * 4 - icon
     * 5 - loader function
     * 6 - subpanels
     * 7 - display in wp admin left side menu (should be false for user plugins)
    */
	$forms = array(
		spa_text('Manage Groups And Forums') => array('forums' => 'sfreloadfb'),
		spa_text('Create New Group') => array('creategroup' => ''),
		spa_text('Create New Forum') => array('createforum' => ''),
		spa_text('Custom Icons') => array('customicons' => 'sfreloadci'),
		spa_text('Add Global Permission Set') => array('globalperm' => ''),
		spa_text('Delete All Permission Sets') => array('removeperms' => ''),
		spa_text('Merge Forums') => array('mergeforums' => 'sfreloadmf'),
		spa_text('Global RSS Settings') => array('globalrss' => 'sfreloadfd'));
    $sfadminpanels[] = array(spa_text('Forums'), 'SPF Manage Forums', 'simple-press/admin/panel-forums/spa-forums.php', $sfatooltips['forums'], SFADMINIMAGES.'menu/sp_Forums.png', SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['forums'] = 0;

	$forms = array(
		spa_text('Global Settings') => array('global' => 'sfreloadog'),
		spa_text('General Display Settings') => array('display' => ''),
		spa_text('Content Settings') => array('content' => ''),
		spa_text('Member Settings') => array('members' => 'sfreloadms'),
		spa_text('Email Settings') => array('email' => ''));
    $sfadminpanels[] = array(spa_text('Options'), 'SPF Manage Options', 'simple-press/admin/panel-options/spa-options.php', $sfatooltips['options'], SFADMINIMAGES.'menu/sp_Options.png', SFHOMEURL.'index.php?sp_ahah=options-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['options'] = 1;

	$forms = array(
		spa_text('Smileys') => array('smileys' => 'sfreloadsm'),
		spa_text('Login And Registration') => array('login' => ''),
		spa_text('SEO') => array('seo' => 'sfreloadse'),
		spa_text('Forum Ranks') => array('forumranks' => 'sfreloadfr'),
		spa_text('Custom Messages') => array('messages' => ''));
    $sfadminpanels[] = array(spa_text('Components'), 'SPF Manage Components', 'simple-press/admin/panel-components/spa-components.php', $sfatooltips['components'], SFADMINIMAGES.'menu/sp_Components.png', SFHOMEURL.'index.php?sp_ahah=components-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['components'] = 2;

	$forms = array(
		spa_text('Manage User Groups') => array('usergroups' => 'sfreloadub'),
		spa_text('Create New User Group') => array('createusergroup' => ''),
		spa_text('Map Users to User Group') => array('mapusers' => 'sfreloadmu'));
    $sfadminpanels[] = array(spa_text('User Groups'), 'SPF Manage User Groups', 'simple-press/admin/panel-usergroups/spa-usergroups.php', $sfatooltips['usergroups'], SFADMINIMAGES.'menu/sp_Usergroups.png', SFHOMEURL.'index.php?sp_ahah=usergroups-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['usergroups'] = 3;

	$forms = array(
		spa_text('Manage Permissions Sets') => array('permissions' => 'sfreloadpb'),
		spa_text('Add New Permission Set') => array('createperm' => ''),
		spa_text('Reset Permissions') => array('resetperms' => ''),
		spa_text('Add New Authorization') => array('newauth' => ''));
    $sfadminpanels[] = array(spa_text('Permissions'), 'SPF Manage Permissions', 'simple-press/admin/panel-permissions/spa-permissions.php', $sfatooltips['permissions'], SFADMINIMAGES.'menu/sp_Permissions.png', SFHOMEURL.'index.php?sp_ahah=permissions-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['permissions'] = 4;

	$forms = array(
		spa_text('Page and Permalink') => array('page' => 'sfreloadpp'),
		spa_text('Storage Locations') => array('storage' => 'sfreloadsl'));
    $sfadminpanels[] = array(spa_text('Integration'), 'SPF Manage Options', 'simple-press/admin/panel-integration/spa-integration.php', $sfatooltips['integration'], SFADMINIMAGES.'menu/sp_Integration.png', SFHOMEURL.'index.php?sp_ahah=integration-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['integration'] = 5;

	$forms = array(
		spa_text('Profile Options') => array('options' => ''),
		spa_text('Profile Tabs & Menus') => array('tabsmenus' => 'sfreloadptm'),
		spa_text('Avatars') => array('avatars' => 'sfreloadav'));
    $sfadminpanels[] = array(spa_text('Profiles'), 'SPF Manage Profiles', 'simple-press/admin/panel-profiles/spa-profiles.php', $sfatooltips['profiles'], SFADMINIMAGES.'menu/sp_Profiles.png', SFHOMEURL.'index.php?sp_ahah=profiles-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['profiles'] = 6;

 	if (sp_current_user_can('SPF Manage Admins')) {
		$forms = array(
    		spa_text('Your Admin Options') => array('youradmin' => 'sfreloadao'),
			spa_text('Global Admin Options') => array('globaladmin' => ''),
			spa_text('Manage Admins') => array('manageadmin' => 'sfreloadma'));
    } else {
    	$forms = array(
    		spa_text('Your Admin Options') => array('youradmin' => 'sfreloadao'));
    }
    $sfadminpanels[] = array(spa_text('Admins'), 'SPF Manage Admins', 'simple-press/admin/panel-admins/spa-admins.php', $sfatooltips['admins'], SFADMINIMAGES.'menu/sp_Admins.png', SFHOMEURL.'index.php?sp_ahah=admins-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['admins'] = 7;

	$forms = array(
		spa_text('Member Information') => array('members' => ''));
    $sfadminpanels[] = array(spa_text('Users'), 'SPF Manage Users', 'simple-press/admin/panel-users/spa-users.php', $sfatooltips['users'], SFADMINIMAGES.'menu/sp_Users.png', SFHOMEURL.'index.php?sp_ahah=users-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['users'] = 8;

	$forms = array(
		spa_text('Available Plugins') => array('list' => 'sfreloadpl'));
	if (!is_multisite() || is_super_admin()) $forms[spa_text('Plugin Uploader')] = array('upload' => '');
    $sfadminpanels[] = array(spa_text('Plugins'), 'SPF Manage Plugins', 'simple-press/admin/panel-plugins/spa-plugins.php', $sfatooltips['plugins'], SFADMINIMAGES.'menu/sp_Plugins.png', SFHOMEURL.'index.php?sp_ahah=plugins-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['plugins'] = 9;

	$forms = array(
		spa_text('Available Themes') => array('list' => 'sfreloadtlist'),
		spa_text('Mobile Phone Theme') => array('mobile' => 'sfreloadmlist'),
		spa_text('Mobile Tablet Theme') => array('tablet' => 'sfreloadtablist'));
	if (!is_multisite() || is_super_admin()) {
	   $forms[spa_text('Theme Editor')] = array('editor' => 'sfreloadttedit');
	   $forms[spa_text('Theme Uploader')] = array('upload' => '');
    }
    $sfadminpanels[] = array(spa_text('Themes'), 'SPF Manage Themes', 'simple-press/admin/panel-themes/spa-themes.php', $sfatooltips['themes'], SFADMINIMAGES.'menu/sp_Themes.png', SFHOMEURL.'index.php?sp_ahah=themes-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['themes'] = 10;

	$forms = array(
		spa_text('Toolbox') => array('toolbox' => ''),
		spa_text('Housekeeping') => array('housekeeping' => 'sfreloadhk'),
		spa_text('Data Inspector') => array('inspector' => ''),
		spa_text('CRON Inspector') => array('cron' => 'sfcron'),
		spa_text('Error Log') => array('errorlog' => 'sfreloadel'),
		spa_text('Environment') => array('environment' => ''),
		spa_text('Install Log') => array('log' => ''),
		spa_text('Change Log') => array('changelog' => ''),
		spa_text('Uninstall') => array('uninstall' => ''));
    $sfadminpanels[] = array(spa_text('Toolbox'), 'SPF Manage Toolbox', 'simple-press/admin/panel-toolbox/spa-toolbox.php', $sfatooltips['toolbox'], SFADMINIMAGES.'menu/sp_Toolbox.png', SFHOMEURL.'index.php?sp_ahah=toolbox-loader&amp;sfnonce='.wp_create_nonce('forum-ahah'), $forms, true);
    $sfactivepanels['toolbox'] = 11;

    # allow plugins to alter the admin menus
	$sfadminpanels = apply_filters('sf_admin_panels', $sfadminpanels);
	$sfactivepanels = apply_filters('sf_admin_activepanels', $sfactivepanels);
}

?>