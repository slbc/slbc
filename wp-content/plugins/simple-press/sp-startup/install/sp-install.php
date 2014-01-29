<?php
/*
Simple:Press
Main Forum Installer (New Instalations)
$LastChangedDate: 2013-09-24 12:22:46 -0700 (Tue, 24 Sep 2013) $
$Rev: 10734 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

global $current_user;

$InstallID = get_option('sfInstallID'); # use wp option table
wp_set_current_user($InstallID);

# use WP check here since SPF stuff wont be set up
if (!current_user_can('activate_plugins')) {
    spa_etext('Access denied - only users who can activate plugins may perform this installation');
    die();
}

require_once(dirname(__FILE__).'/sp-upgrade-support.php');
require_once(SF_PLUGIN_DIR.'/admin/library/spa-support.php');

$phase = 0;
$subphase = 0;

if (isset($_GET['phase'])) {
	$phase = sp_esc_int($_GET['phase']);
	if ($phase == 0) {
		echo '<h5>'.spa_text('Installing').' '.spa_text('Simple:Press').'...</h5>';
	} else {
		if (isset($_GET['subphase'])) $subphase = sp_esc_int($_GET['subphase']);
	}
	sp_perform_install($phase, $subphase);
}
die();

function sp_perform_install($phase, $subphase=0) {
	global $current_user, $spVars;

	switch ($phase) {
		case 1:
            # create an array of installed tables to save for uninstall. plugins will add theirs to be sure we get good cleanup
            $tables = array();

			# sfforums table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFFORUMS." (
					forum_id bigint(20) NOT NULL auto_increment,
					forum_name varchar(200) NOT NULL,
					group_id bigint(20) NOT NULL,
					forum_seq int(4) default NULL,
					forum_desc text default NULL,
					forum_status int(4) NOT NULL default '0',
					forum_disabled smallint(1) NOT NULL default '0',
					forum_slug varchar(200) NOT NULL,
					forum_rss text default NULL,
					forum_icon varchar(50) default NULL,
					forum_icon_new varchar(50) default NULL,
					topic_icon varchar(50) default NULL,
					topic_icon_new varchar(50) default NULL,
					post_id bigint(20) default NULL,
					post_id_held bigint(20) default NULL,
					topic_count mediumint(8) default '0',
					post_count mediumint(8) default '0',
					post_count_held mediumint(8) default '0',
					forum_rss_private smallint(1) NOT NULL default '0',
					parent bigint(20) NOT NULL default '0',
					children text default NULL,
					forum_message text,
                    keywords varchar(256) default NULL,
					PRIMARY KEY  (forum_id),
					KEY groupf_idx (group_id),
					KEY fslug_idx (forum_slug),
					KEY post_idx (post_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFFORUMS;

			# sfgroups table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFGROUPS." (
					group_id bigint(20) NOT NULL auto_increment,
					group_name text,
					group_seq int(4) default NULL,
					group_desc text,
					group_rss text,
					group_icon varchar(50) default NULL,
					group_message text,
					PRIMARY KEY  (group_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFGROUPS;

			# sfmembers table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFMEMBERS." (
					user_id bigint(20) NOT NULL default '0',
					display_name varchar(100) default NULL,
					moderator smallint(1) NOT NULL default '0',
					avatar longtext default NULL,
					signature text default NULL,
					posts int(4) default NULL,
					lastvisit datetime default NULL,
					newposts longtext,
					checktime datetime default NULL,
					admin smallint(1) NOT NULL default '0',
					feedkey varchar(36) default NULL,
					admin_options longtext default NULL,
					user_options longtext default NULL,
					auths longtext default NULL,
					memberships longtext default NULL,
					special_ranks text default NULL,
					PRIMARY KEY  (user_id),
					KEY admin_idx (admin),
					KEY moderator_idx (moderator)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFMEMBERS;

			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFOPTIONS." (
				option_id bigint(20) unsigned NOT NULL auto_increment,
				option_name varchar(64) NOT NULL default '',
				option_value longtext NOT NULL,
				PRIMARY KEY (option_name),
				KEY option_id (option_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFOPTIONS;

			# sfmemberships table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFMEMBERSHIPS." (
					membership_id bigint(20) NOT NULL auto_increment,
					user_id bigint(20) unsigned NOT NULL default '0',
					usergroup_id bigint(20) unsigned NOT NULL default '0',
					PRIMARY KEY  (membership_id),
					KEY user_idx (user_id),
					KEY usergroup_idx (usergroup_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFMEMBERSHIPS;

			# sfmeta table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFMETA." (
					meta_id bigint(20) NOT NULL auto_increment,
					meta_type varchar(20) NOT NULL,
					meta_key varchar(100) default NULL,
					meta_value longtext,
					autoload tinyint(2) NOT NULL default '0',
					PRIMARY KEY (meta_id),
					KEY meta_type_idx (meta_type),
					KEY autoload_idx (autoload)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFMETA;

			# sfpermissions table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFPERMISSIONS." (
					permission_id bigint(20) NOT NULL auto_increment,
					forum_id bigint(20) NOT NULL default '0',
					usergroup_id bigint(20) unsigned NOT NULL default '0',
					permission_role bigint(20) NOT NULL default '0',
					PRIMARY KEY  (permission_id),
					KEY forum_idx (forum_id),
					KEY usergroup_idx (usergroup_id),
					KEY perm_role_idx (permission_role)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFPERMISSIONS;

			# sfdefpermissions table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFDEFPERMISSIONS." (
					permission_id bigint(20) NOT NULL auto_increment,
					group_id bigint(20) NOT NULL default '0',
					usergroup_id bigint(20) NOT NULL default '0',
					permission_role bigint(20) NOT NULL default '0',
					PRIMARY KEY  (permission_id),
					KEY group_idx (group_id),
					KEY usergroup_idx (usergroup_id),
					KEY perm_role_idx (permission_role)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFDEFPERMISSIONS;

			# sfposts table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFPOSTS." (
					post_id bigint(20) NOT NULL auto_increment,
					post_content longtext,
					post_date datetime NOT NULL,
					topic_id bigint(20) NOT NULL,
					user_id bigint(20) default NULL,
					forum_id bigint(20) NOT NULL,
					guest_name varchar(20) default NULL,
					guest_email varchar(50) default NULL,
					post_status int(4) NOT NULL default '0',
					post_pinned smallint(1) NOT NULL default '0',
					post_index mediumint(8) default '0',
					post_edit mediumtext,
					poster_ip varchar(39) NOT NULL default '0.0.0.0',
					comment_id bigint(20) default NULL,
					source smallint(1) NOT NULL default '0',
					PRIMARY KEY  (post_id),
					KEY topicp_idx (topic_id),
					KEY forump_idx (forum_id),
					KEY user_idx (user_id),
					KEY guest_name_idx (guest_name),
					KEY comment_idx (comment_id),
					KEY post_date_idx (post_date),
					FULLTEXT KEY post_content (post_content)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFPOSTS;

			# sfauths table def
        	$sql = "
        		CREATE TABLE IF NOT EXISTS ".SFAUTHS." (
        			auth_id bigint(20) NOT NULL auto_increment,
        			auth_name varchar(50) NOT NULL,
        			auth_desc text,
                    active smallint(1) NOT NULL default '0',
                    ignored smallint(1) NOT NULL default '0',
                    enabling smallint(1) NOT NULL default '0',
                    admin_negate smallint(1) NOT NULL default '0',
                    auth_cat bigint(20) NOT NULL default '1',
                    warning tinytext,
        			PRIMARY KEY  (auth_id)
        		) ENGINE=MyISAM ".spdb_charset().";";
        	spdb_query($sql);
            $tables[] = SFAUTHS;

			# sfauthcats table def
        	$sql = "
        		CREATE TABLE IF NOT EXISTS ".SFAUTHCATS." (
        			authcat_id tinyint(4) NOT NULL auto_increment,
        			authcat_name varchar(50) NOT NULL,
					authcat_slug varchar(50) NOT NULL,
        			authcat_desc tinytext,
        			PRIMARY KEY  (authcat_id)
        		) ENGINE=MyISAM ".spdb_charset().";";
        	spdb_query($sql);
            $tables[] = SFAUTHCATS;

			# sfroles table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFROLES." (
					role_id mediumint(8) unsigned NOT NULL auto_increment,
					role_name varchar(50) NOT NULL default '',
					role_desc varchar(150) NOT NULL default '',
					role_auths longtext NOT NULL,
					PRIMARY KEY  (role_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFROLES;

			# sftopics table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFTOPICS." (
					topic_id bigint(20) NOT NULL auto_increment,
					topic_name varchar(200) NOT NULL,
					topic_date datetime NOT NULL,
					topic_status int(4) NOT NULL default '0',
					forum_id bigint(20) NOT NULL,
					user_id bigint(20) default NULL,
					topic_pinned smallint(1) NOT NULL default '0',
					topic_opened bigint(20) NOT NULL default '0',
					topic_slug varchar(200) NOT NULL,
					post_id bigint(20) default NULL,
					post_id_held bigint(20) default NULL,
					post_count mediumint(8) default '0',
					post_count_held mediumint(8) default '0',
					PRIMARY KEY  (topic_id),
					KEY forumt_idx (forum_id),
					KEY tslug_idx (topic_slug),
					KEY user_idx (user_id),
					KEY post_idx (post_id),
					FULLTEXT KEY topic_name_idx (topic_name)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFTOPICS;

			# sftrack table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFTRACK." (
					id bigint(20) NOT NULL auto_increment,
					trackuserid bigint(20) default '0',
					trackname varchar(50) NOT NULL,
					trackdate datetime NOT NULL,
                    forum_id bigint(20) default NULL,
                    topic_id bigint(20) default NULL,
                    pageview varchar(50) NOT NULL,
                    notification varchar(1024) default NULL,
                    device char(1) default 'D',
					PRIMARY KEY  (id),
					KEY user_idx (trackuserid),
					KEY forum_idx (forum_id),
					KEY topic_idx (topic_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFTRACK;

			# sfusergroups table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFUSERGROUPS." (
					usergroup_id bigint(20) NOT NULL auto_increment,
					usergroup_name text NOT NULL,
					usergroup_desc text default NULL,
					usergroup_badge varchar(50) default NULL,
					usergroup_join tinyint(4) unsigned NOT NULL default '0',
					usergroup_is_moderator tinyint(4) unsigned NOT NULL default '0',
					PRIMARY KEY  (usergroup_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFUSERGROUPS;

			# sfwaiting table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFWAITING." (
					topic_id bigint(20) NOT NULL,
					forum_id bigint(20) NOT NULL,
					post_count int(4) NOT NULL,
					post_id bigint(20) NOT NULL default '0',
					user_id bigint(20) unsigned default '0',
					PRIMARY KEY  (topic_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFWAITING;

			# install log table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFLOG." (
					id bigint(20) NOT NULL auto_increment,
					user_id bigint(20) NOT NULL,
					install_date date NOT NULL,
					release_type varchar(20),
					version varchar(10) NOT NULL,
					build int(6) NOT NULL,
					PRIMARY KEY (id),
					KEY user_idx (user_id)
                ) ENGINE=MyISAM ".spdb_charset().";";
            spdb_query($sql);
            $tables[] = SFLOG;

			# install log section table def
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFLOGMETA." (
					id int(11) unsigned NOT NULL AUTO_INCREMENT,
					version varchar(10) DEFAULT NULL,
					log_data tinytext,
					PRIMARY KEY (id)
                ) ENGINE=MyISAM ".spdb_charset().";";
            spdb_query($sql);
            $tables[] = SFLOGMETA;

			# error log table
			$sql = "
			CREATE TABLE IF NOT EXISTS ".SFERRORLOG." (
			  id bigint(20) NOT NULL auto_increment,
			  error_date datetime NOT NULL,
			  error_type varchar(10) NOT NULL,
			  error_cat varchar(13) NOT NULL default 'spaErrOther',
			  keycheck varchar(45),
			  error_count tinyint,
			  error_text text,
			  PRIMARY KEY (id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFERRORLOG;

			# user notices table
			$sql = "
			CREATE TABLE IF NOT EXISTS ".SFNOTICES." (
			  notice_id bigint(20) NOT NULL auto_increment,
			  user_id bigint(20) default NULL,
			  guest_email varchar(50) default NULL,
			  post_id bigint(20) default NULL,
			  link varchar(255) default NULL,
			  link_text varchar(200) default NULL,
			  message varchar(255) NOT NULL default '',
			  expires int(4) default NULL,
			  PRIMARY KEY (notice_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFNOTICES;

			# special ranks (5.3.2)
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFSPECIALRANKS." (
					id int(11) unsigned NOT NULL AUTO_INCREMENT,
					user_id bigint(20) default NULL,
					special_rank varchar(100),
					PRIMARY KEY (id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFSPECIALRANKS;

			# user activity (5.3.2 - in preparation)
			$sql = "
				CREATE TABLE IF NOT EXISTS ".SFUSERACTIVITY." (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					user_id bigint(20) NOT NULL,
					type_id smallint(4) NOT NULL,
					item_id bigint(20) NOT NULL,
					PRIMARY KEY (id),
					KEY type_idx (type_id)
				) ENGINE=MyISAM ".spdb_charset().";";
			spdb_query($sql);
            $tables[] = SFUSERACTIVITY;

            # now save off installed tables
            sp_add_option('installed_tables', $tables);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Tables created').'</h5>';
			break;

		case 2:
            # populate auths
            spa_setup_auth_cats();
            spa_setup_auths();

            # set up the default permissions/roles
            spa_setup_permissions();

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Permission data built').'</h5>';
			break;

		case 3:
			# Create default 'Guest' user group data
			$guests = spa_create_usergroup_row('Guests', 'Default Usergroup for guests of the forum', '', '0', '0', false);

			# Create default 'Members' user group data
			$members = spa_create_usergroup_row('Members', 'Default Usergroup for registered users of the forum', '', '0', '0', false);

			# Create default 'Moderators' user group data
			$moderators = spa_create_usergroup_row('Moderators', 'Default Usergroup for moderators of the forum', '', '0', '1', false);

			# Create default user groups
			sp_add_sfmeta('default usergroup', 'sfguests', $guests); # default usergroup for guests
			sp_add_sfmeta('default usergroup', 'sfmembers', $members); # default usergroup for members
			sp_create_usergroup_meta($members); # create default usergroups for existing wp roles

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Usergroup data built').'</h5>';
			break;

		case 4:
			$page_args = array('post_status' => 'publish', 'post_type' => 'page', 'post_author' => $current_user->ID,
				'ping_status' => 'closed', 'comment_status' => 'closed', 'post_parent' => 0, 'menu_order' => 0,
				'to_ping' =>  '', 'pinged' => '', 'post_password' => '', 'post_content' => '', 'guid' => '',
				'post_content_filtered' => '', 'post_excerpt' => '', 'import_id' => 0, 'post_title' => 'Forum', 'page_template' => 'default');
			require_once(ABSPATH.'wp-admin/includes/theme.php');
			$page_id = wp_insert_post($page_args);
			$page = spdb_table(SFWPPOSTS, "ID=".$page_id, 'row');
			sp_add_option('sfslug', $page->post_name);

			# Update the guid for the new page
			$guid = get_permalink($page_id);
			spdb_query("UPDATE ".SFWPPOSTS." SET guid='".$guid."' WHERE ID=".$page_id);
			sp_add_option('sfpage', $page_id);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Forum page created').'</h5>';
			break;

		case 5:
			# Create Base Option Records (V1)
			sp_add_option('sfuninstall', false);

			sp_add_option('sfdates', get_option('date_format'));
			sp_add_option('sftimes', get_option('time_format'));

			sp_add_option('sfpermalink', get_permalink(sp_get_option('sfpage')));

			sp_add_option('sflockdown', false);

			$rankdata['posts'] = 2;
			$rankdata['usergroup'] = 'none';
			$rankdata['image'] = 'none';
			sp_add_sfmeta('forum_rank', 'New Member', $rankdata, 1);
			$rankdata['posts'] = 1000;
			$rankdata['usergroup'] = 'none';
			$rankdata['image'] = 'none';
			sp_add_sfmeta('forum_rank', 'Member', $rankdata, 1);

			$sfimage = array();
			$sfimage['enlarge'] = true;
			$sfimage['process'] = true;
			$sfimage['thumbsize'] = 100;
			$sfimage['style'] = 'left';
			$sfimage['constrain'] = true;
			$sfimage['forceclear'] = false;
			sp_add_option('sfimage', $sfimage);

			sp_add_option('sfbadwords', '');
			sp_add_option('sfreplacementwords', '');
			sp_add_option('sfeditormsg','');

			$sfmail = array();
			$sfmail['sfmailsender'] = get_bloginfo('name');
			$admin_email = get_bloginfo('admin_email');
			$comp = explode('@', $admin_email);
			$sfmail['sfmailfrom'] = $comp[0];
			$sfmail['sfmaildomain'] = $comp[1];
			$sfmail['sfmailuse'] = true;
			sp_add_option('sfmail', $sfmail);

			$sfmail = array();
			$sfmail['sfusespfreg'] = true;
			$sfmail['sfnewusersubject'] = 'Welcome to %BLOGNAME%';
			$sfmail['sfnewusertext'] = 'Welcome %USERNAME% to %BLOGNAME% %NEWLINE%Please find below your login details: %NEWLINE%Username: %USERNAME% %NEWLINE%Password: %PASSWORD% %NEWLINE%%LOGINURL%';
			sp_add_option('sfnewusermail', $sfmail);

			$sfpostmsg = array();
			$sfpostmsg['sfpostmsgtext'] = '';
			$sfpostmsg['sfpostmsgtopic'] = false;
			$sfpostmsg['sfpostmsgpost'] = false;
			sp_add_option('sfpostmsg', $sfpostmsg);

			$sflogin = array();
			$sflogin['sfregmath'] = true;
			$sflogin['sfloginurl'] = sp_url();
			$sflogin['sflogouturl'] = sp_url();
			$sflogin['sfregisterurl'] = '';
			$sflogin['sfloginemailurl'] = esc_url(wp_login_url());
			$sflogin['sptimeout'] = 20;
			sp_add_option('sflogin', $sflogin);

			$sfadminsettings = array();
			$sfadminsettings['sfdashboardstats'] = true;
            $sfadminsettings['sfadminapprove'] = false;
            $sfadminsettings['sfmoderapprove'] = false;
            $sfadminsettings['editnotice'] = true;
            $sfadminsettings['movenotice'] = true;
			sp_add_option('sfadminsettings', $sfadminsettings);

			$sfauto = array();
			$sfauto['sfautoupdate'] = false;
			$sfauto['sfautotime'] = 300;
			sp_add_option('sfauto', $sfauto);

			$sffilters = array();
			$sffilters['sfnofollow'] = false;
			$sffilters['sftarget'] = true;
			$sffilters['sfurlchars'] = 40;
			$sffilters['sffilterpre'] = false;
			$sffilters['sfmaxlinks'] = 0;
			$sffilters['sfnolinksmsg'] = "<b>** you don't have permission to see this link **</b>";
            $sffilters['sfdupemember'] = 0;
			$sffilters['sfdupeguest'] = 0;
			$sffilters['sfmaxsmileys'] = 0;
			sp_add_option('sffilters', $sffilters);

			$sfseo = array();
			$sfseo['sfseo_overwrite'] = false;
			$sfseo['sfseo_blogname'] = false;
			$sfseo['sfseo_pagename'] = false;
			$sfseo['sfseo_topic'] = true;
			$sfseo['sfseo_forum'] = true;
			$sfseo['sfseo_noforum'] = false;
			$sfseo['sfseo_page'] = true;
			$sfseo['sfseo_sep'] = '|';
			sp_add_option('sfseo', $sfseo);

			$sfsigimagesize = array();
			$sfsigimagesize['sfsigwidth'] = 0;
			$sfsigimagesize['sfsigheight'] = 0;
			sp_add_option('sfsigimagesize', $sfsigimagesize);

			# (V4.1.0)
			$sfmembersopt = array();
			$sfmembersopt['sfcheckformember'] = true;
			$sfmembersopt['sfsinglemembership'] = false;
			$sfmembersopt['sfhidestatus'] = true;
			sp_add_option('sfmemberopts', $sfmembersopt);

			$sfcontrols = array();
			$sfcontrols['showtopcount'] = 10;
			$sfcontrols['shownewcount'] = 10;
			$sfcontrols['sfdefunreadposts'] = 50;
			$sfcontrols['sfusersunread'] = false;
			$sfcontrols['sfmaxunreadposts'] = 50;
			sp_add_option('sfcontrols', $sfcontrols);

			$sfblock = array();
			$sfblock['blockadmin'] = false;
			$sfblock['blockroles'] = 'administrator';
			$sfblock['blockredirect'] = get_permalink(sp_get_option('sfpage'));
			sp_add_option('sfblockadmin', $sfblock);

			$sfmetatags = array();
			$sfmetatags['sfdescr'] = '';
			$sfmetatags['sfdescruse'] = 1;
			$sfmetatags['sfusekeywords'] = 2;
			$sfmetatags['keywords'] = 'forum';
			sp_add_option('sfmetatags', $sfmetatags);

			# display array
			$sfdisplay = array();

			$sfdisplay['pagetitle']['notitle'] 			= false;
			$sfdisplay['pagetitle']['banner'] 			= '';

			$sfdisplay['forums']['singleforum']			= false;

			$sfdisplay['topics']['perpage']				= 12;
			$sfdisplay['topics']['sortnewtop']			= true;

			$sfdisplay['posts']['perpage']				= 20;
			$sfdisplay['posts']['sortdesc']				= false;
			sp_add_option('sfdisplay', $sfdisplay);

			sp_add_sfmeta('sort_order', 'forum', '', 1);
			sp_add_sfmeta('sort_order', 'topic', '', 1);

			# guest settings
			$sfguests = array();
			$sfguests['reqemail'] = true;
			$sfguests['storecookie'] = true;
			sp_add_option('sfguests', $sfguests);

			# profile management
			$sfprofile = array();
			$sfprofile['nameformat'] = true;
			$sfprofile['fixeddisplayformat'] = 0;
			$sfprofile['profilelink'] = 3;
			$sfprofile['weblink'] = 3;
			$sfprofile['displaymode'] = 1;
			$sfprofile['displaypage'] = '';
			$sfprofile['displayquery'] = '';
			$sfprofile['formmode'] = 1;
			$sfprofile['formpage'] = '';
			$sfprofile['formquery'] = '';
			$sfprofile['photosmax'] = 0;
			$sfprofile['photoswidth'] = 0;
			$sfprofile['photosheight'] = 0;
			$sfprofile['firstvisit'] = true;
			$sfprofile['forcepw'] = false;
			$sfprofile['profileinstats'] = true;
			sp_add_option('sfprofile', $sfprofile);

			# avatar options
			$sfavatars = array();
			$sfavatars['sfshowavatars'] = true;
			$sfavatars['sfavataruploads'] = true;
			$sfavatars['sfavatarpool'] = false;
			$sfavatars['sfavatarremote'] = false;
			$sfavatars['sfgmaxrating'] = 1;
			$sfavatars['sfavatarsize'] = 50;
			$sfavatars['sfavatarresize'] = true;
			$sfavatars['sfavatarresizequality'] = 90;
			$sfavatars['sfavatarfilesize'] = 10240;
			$sfavatars['sfavatarpriority'] = array(0, 2, 3, 1, 4, 5);  # gravatar, upload, spf, wp, pool, remote
			sp_add_option('sfavatars', $sfavatars);

			# RSS stuff
			$sfrss = array();
			$sfrss['sfrsscount'] = 15;
			$sfrss['sfrsswords'] = 0;
			$sfrss['sfrsstopicname'] = false;
			$sfrss['sfrssfeedkey'] = true;
			sp_add_option('sfrss', $sfrss);

			sp_add_option('sffiltershortcodes', true);

			# post content width
			$sfpostwrap = array();
			$sfpostwrap['postwrap']=false;
			$sfpostwrap['postwidth']=0;
			sp_add_option('sfpostwrap', $sfpostwrap);

			sp_add_option('sfwplistpages', true);

			# Script in footer
			sp_add_option('sfscriptfoot', false);

            # the_content filter options
			sp_add_option('sfinloop', false);
            sp_add_option('sfmultiplecontent', true);
			sp_add_option('sfwpheadbypass', false);

            # auto update stuff in sfmeta
            $autoup = array('spjUserUpdate', SFHOMEURL.'index.php?sp_ahah=autoupdate&amp;sfnonce='.wp_create_nonce('forum-ahah'));
            sp_add_sfmeta('autoupdate', 'user', $autoup);

			# Set up unique key
			$uKey = substr(chr(rand(97, 122)).md5(time()), 0, 10);
			sp_add_option('spukey', $uKey);

			# default theme
			$theme = array();
			$theme['theme'] = 'default';
			$theme['style'] = 'default.php';
			$theme['color'] = 'silver';
			sp_add_option('sp_current_theme', $theme);

			$theme = array();
            $theme['active'] = false;
			$theme['theme'] = 'default';
			$theme['style'] = 'default.php';
			$theme['color'] = 'silver';
			$theme['usetemplate'] = false;
			$theme['pagetemplate'] = '';
			$theme['notitle'] = true;
			sp_add_option('sp_mobile_theme', $theme);
			sp_add_option('sp_tablet_theme', $theme);

        	sp_add_option('account-name', '');
        	sp_add_option('display-name', '');
        	sp_add_option('guest-name', '');

			# Create smileys Record
			sp_build_base_smileys();

			# set up daily transient clean up cron
			wp_schedule_event(time(), 'daily', 'sph_transient_cleanup_cron');

			# profile tabs
			spa_new_profile_setup();

            # build the list of moderators per forum
            sp_update_forum_moderators();

			# set up hourly stats generation
			sp_add_option('sp_stats_interval', 3600);
			wp_schedule_event(time(), 'hourly', 'sph_stats_cron');

			# set up weekly news processing
    		wp_schedule_event(time(), 'sp_news_interval', 'sph_news_cron');
			sp_add_sfmeta('news', 'news', array('id' => -999.999, 'show' => 0, 'news' => esc_sql(spa_text('Latest Simple Press News will be shown here'))));

			# create initial last post time stamp
			sp_add_option('poststamp', current_time('mysql'));

			# setup mysql search sfmeta values (5.2.0)
			$s = array();
			$v = spdb_select('row', "SHOW VARIABLES LIKE 'ft_min_word_len'");
			(empty($v->Value) ? $s['min'] = 4 : $s['min'] = $v->Value);
			$v = spdb_select('row', "SHOW VARIABLES LIKE 'ft_max_word_len'");
			(empty($v->Value) ? $s['max'] = 84 : $s['max'] = $v->Value);
			sp_add_sfmeta('mysql', 'search', $s, true);

            # combined css and js cache fles
        	sp_add_option('combinecss', false);
	        sp_add_option('combinejs', false);

			sp_add_option('post_count_delete', false);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Default forum options created').'</h5>';
			break;

		case 6:
			# Create sp-resources folder for the current install - does not include themes, plugins or languages
			$perms = fileperms(SF_STORE_DIR);
			$owners = stat(SF_STORE_DIR);
			if($perms === false) $perms = 0755;
			$basepath = '';
			if (is_multisite()) {
                # construct multisite storage directory structure and create if necessary
                $basepath .= 'blogs.dir/'.SFBLOGID;
                if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);
                $basepath .= '/files';
                if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);
                $basepath .= '/';
            }
			$basepath.= 'sp-resources';
			if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);
			# hive off the basepath for later use - use wp options
			add_option('sp_storage1', SF_STORE_DIR.'/'.$basepath);

			# Did it get created?
			$success = true;
			if (!file_exists(SF_STORE_DIR.'/'.$basepath)) $success = false;
			sp_add_option('spStorageInstall1', $success);

			# Is the ownership correct?
			$ownersgood = false;
			if($success) {
				$newowners = stat(SF_STORE_DIR.'/'.$basepath);
				if($newowners['uid']==$owners['uid'] && $newowners['gid']==$owners['gid']) {
					$ownersgood = true;
				} else {
					@chown(SF_STORE_DIR.'/'.$basepath, $owners['uid']);
					@chgrp(SF_STORE_DIR.'/'.$basepath, $owners['gid']);
					$newowners = stat(SF_STORE_DIR.'/'.$basepath);
					if($newowners['uid']==$owners['uid'] && $newowners['gid']==$owners['gid']) $ownersgood = true;
				}
			}
			sp_add_option('spOwnersInstall1', $ownersgood);
            $basepath.= '/';

			$sfconfig = array();
			$sfconfig['avatars'] 		= $basepath.'forum-avatars';
			$sfconfig['avatar-pool'] 	= $basepath.'forum-avatar-pool';
			$sfconfig['smileys'] 		= $basepath.'forum-smileys';
			$sfconfig['ranks'] 			= $basepath.'forum-badges';
			$sfconfig['custom-icons']	= $basepath.'forum-custom-icons';
			$sfconfig['cache']			= $basepath.'forum-cache';

            # Create sp-resources folder and themes, plugins and languages folders
            # if not multisite, just add to sp-resource created above
            # if multisite try to use set up of main blog (id 1) or create in wp-content if main blog does not have SP installed
            global $wpdb;
			if (is_multisite()) {
    			$basepath = 'sp-resources';
    			if (!file_exists(SF_STORE_DIR.'/'.$basepath)) @mkdir(SF_STORE_DIR.'/'.$basepath, $perms);

    			# Did it get created?
    			$success = true;
    			if (!file_exists(SF_STORE_DIR.'/'.$basepath)) $success = false;
    			sp_add_option('spStorageInstall2', $success);

				# Is the ownership correct?
				$ownersgood = false;
				if($success) {
					$newowners = stat(SF_STORE_DIR.'/'.$basepath);
					if($newowners['uid']==$owners['uid'] && $newowners['gid']==$owners['gid']) {
						$ownersgood = true;
					} else {
						@chown(SF_STORE_DIR.'/'.$basepath, $owners['uid']);
						@chgrp(SF_STORE_DIR.'/'.$basepath, $owners['gid']);
						$newowners = stat(SF_STORE_DIR.'/'.$basepath);
						if($newowners['uid']==$owners['uid'] && $newowners['gid']==$owners['gid']) $ownersgood = true;
					}
				}
				sp_add_option('spOwnersInstall2', $ownersgood);
    			add_option('sp_storage2', SF_STORE_DIR.'/'.$basepath);
                $basepath.= '/';
                if ($wpdb->blogid == 1) {
        			$sfconfig['language-sp']             = $basepath.'forum-language/simple-press';
        			$sfconfig['language-sp-plugins']     = $basepath.'forum-language/sp-plugins';
        			$sfconfig['language-sp-themes']	     = $basepath.'forum-language/sp-themes';
        			$sfconfig['plugins'] 		         = $basepath.'forum-plugins';
        			$sfconfig['themes']			         = $basepath.'forum-themes';
                } else {
                    $blog_prefix = $wpdb->get_blog_prefix(1);
                    $row = $wpdb->get_row("SELECT * FROM {$blog_prefix}sfoptions WHERE option_name 'sfconfig'");
                    if (is_object($row)) {
                        $mainConfig = unserialize($row->option_value);
            			$sfconfig['language-sp']             = $mainConfig['language-sp'];
            			$sfconfig['language-sp-plugins']     = $mainConfig['language-sp-plugins'];
            			$sfconfig['language-sp-themes']	     = $mainConfig['language-sp-themes'];
            			$sfconfig['plugins'] 		         = $mainConfig['plugins'];
            			$sfconfig['themes']			         = $mainConfig['themes'];
                    } else {
            			$sfconfig['language-sp']             = $basepath.'forum-language/simple-press';
            			$sfconfig['language-sp-plugins']     = $basepath.'forum-language/sp-plugins';
            			$sfconfig['language-sp-themes']	     = $basepath.'forum-language/sp-themes';
            			$sfconfig['plugins'] 		         = $basepath.'forum-plugins';
            			$sfconfig['themes']			         = $basepath.'forum-themes';
                    }
                }
            } else {
    			add_option('sp_storage2', get_option('sp_storage1'));
    			sp_add_option('spStorageInstall2', true);
				sp_add_option('spOwnersInstall2', true);
    			$sfconfig['language-sp']             = $basepath.'forum-language/simple-press';
    			$sfconfig['language-sp-plugins']     = $basepath.'forum-language/sp-plugins';
    			$sfconfig['language-sp-themes']	     = $basepath.'forum-language/sp-themes';
    			$sfconfig['plugins'] 		         = $basepath.'forum-plugins';
    			$sfconfig['themes']			         = $basepath.'forum-themes';
            }

			sp_add_option('sfconfig', $sfconfig);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			if ($success) {
				spa_etext('Storage location created').'</h5>';
			} else {
				spa_etext('Storage location creation failed').'</h5>';
			}
			break;

		case 7:
			# Move and extract zip install archives

            # first do stuff that could be in blogs.dir for multisite
			$successCopy1 = false;
			$successExtract1 = false;
			$zipfile = SF_PLUGIN_DIR.'/sp-startup/install/sp-resources-install-part1.zip';
			$extract_to = get_option('sp_storage1');
			# Copy the zip file
			if (@copy($zipfile, $extract_to.'/sp-resources-install-part1.zip')) {
				$successCopy1 = true;
				# Now try and unzip it
				require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
				$zipfile = $extract_to.'/sp-resources-install-part1.zip';
				$zipfile = str_replace('\\','/',$zipfile); # sanitize for Win32 installs
				$zipfile = preg_replace('|/+|','/', $zipfile); # remove any duplicate slash
				$extract_to = str_replace('\\','/',$extract_to); # sanitize for Win32 installs
				$extract_to = preg_replace('|/+|','/', $extract_to); # remove any duplicate slash
				$archive = new PclZip($zipfile);
				$archive->extract($extract_to);
				if ($archive->error_code == 0) {
					$successExtract1 = true;
					# Lets try and remove the zip as it seems to have worked
					@unlink($zipfile);
				}
			}

			sp_add_option('spCopyZip1', $successCopy1);
			sp_add_option('spUnZip1', $successExtract1);

            # now do stuff that could should not be blogs.dir for multisite
			$successCopy2 = false;
			$successExtract2 = false;
			$zipfile = SF_PLUGIN_DIR.'/sp-startup/install/sp-resources-install-part2.zip';
			$extract_to = get_option('sp_storage2');
			# Copy the zip file
			if (@copy($zipfile, $extract_to.'/sp-resources-install-part2.zip')) {
				$successCopy2 = true;
				# Now try and unzip it
				require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
				$zipfile = $extract_to.'/sp-resources-install-part2.zip';
				$zipfile = str_replace('\\','/',$zipfile); # sanitize for Win32 installs
				$zipfile = preg_replace('|/+|','/', $zipfile); # remove any duplicate slash
				$extract_to = str_replace('\\','/',$extract_to); # sanitize for Win32 installs
				$extract_to = preg_replace('|/+|','/', $extract_to); # remove any duplicate slash
				$archive = new PclZip($zipfile);
				$archive->extract($extract_to);
				if ($archive->error_code == 0) {
					$successExtract2 = true;
					# Lets try and remove the zip as it seems to have worked
					@unlink($zipfile);
				}
			}

			sp_add_option('spCopyZip2', $successCopy2);
			sp_add_option('spUnZip2', $successExtract2);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';

			if ($successCopy1 && $successExtract1 && $successCopy2 && $successExtract2) {
				spa_etext('Resources created').'</h5>';
			} elseif(!$successCopy1 || !$successCopy2) {
				spa_etext('Resources file failed to copy').'</h5>';
			} elseif(!$successExtract1 || !$successExtract2) {
				spa_etext('Resources file failed to unzip');
				echo ' - '.$archive->error_string.'</h5>';
			}

			break;

		case 8:
			# CREATE MEMBERS TABLE ---------------------------
			sp_install_members_table($subphase);

            # give them feedkeys
            sp_generate_member_feedkeys();

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			echo spa_text('Members data created for existing users').' '.(($subphase - 1) * 250 + 1).' - '.($subphase * 250).'</h5>';
			break;

		case 9:
			# grant spf capabilities to installer
			$user = new WP_User($current_user->ID);
			$user->add_cap('SPF Manage Options');
			$user->add_cap('SPF Manage Forums');
			$user->add_cap('SPF Manage User Groups');
			$user->add_cap('SPF Manage Permissions');
			$user->add_cap('SPF Manage Components');
			$user->add_cap('SPF Manage Admins');
			$user->add_cap('SPF Manage Users');
			$user->add_cap('SPF Manage Profiles');
			$user->add_cap('SPF Manage Toolbox');
			$user->add_cap('SPF Manage Plugins');
			$user->add_cap('SPF Manage Themes');
			sp_update_member_item($current_user->ID, 'admin', 1);

			# admin your option defaults
        	$sfadminoptions = array();
            $sfadminoptions['sfnotify'] = false;
            $sfadminoptions['sfstatusmsgtext'] = '';
            $sfadminoptions['notify-edited'] = true;
            sp_update_member_item($current_user->ID, 'admin_options', $sfadminoptions);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Admin permission data built').'</h5>';
			break;

		case 10:
			# UPDATE VERSION/BUILD NUMBERS -------------------------

			sp_log_event(SPRELEASE, SPVERSION, SPBUILD);

			# Lets update permalink and force a rewrite rules flush
			sp_update_permalink(true);

			echo '<h5>'.spa_text('Phase').' - '.$phase.' - ';
			spa_etext('Version number updated').'</h5>';
			break;

		case 11:
			# REPORTS ERRORS IF COPY OR UNZIP FAILED ---------------

			$sCreate1	= sp_get_option('spStorageInstall1');
			$sCreate2 	= sp_get_option('spStorageInstall2');
			$sOwners1	= sp_get_option('spOwnersInstall1');
			$sOwners2	= sp_get_option('spOwnersInstall2');
			$sCopy1 	= sp_get_option('spCopyZip1');
			$sUnzip1	= sp_get_option('spUnZip1');
			$sCopy2 	= sp_get_option('spCopyZip2');
			$sUnzip2	= sp_get_option('spUnZip2');
			if ($sCreate1 && $sCreate2 && $sCopy1 && $sUnzip1 && $sCopy2 && $sUnzip2 && $sOwners1 && $sOwners2) {
				echo '<h5>'.spa_text('The installation has been completed').'</h5>';
			} else {

				$image = "<img src='".SF_PLUGIN_URL."/sp-startup/install/resources/images/important.png' alt='' style='float:left;padding: 5px 5px 8px 0;' />";

				echo '<h5>';
				spa_etext('YOU WILL NEED TO PERFORM THE FOLLOWING TASKS TO ALLOW SIMPLE:PRESS TO WORK CORRECTLY');
				echo '</h5><br />';

				if (!$sCreate1) {
					echo $image.'<h5>[';
					spa_etext('Storage location part 1 creation failed');
					echo '] - ';
					spa_etext("You will need to manually create a required a folder named").': '.get_option('sp_storage1');
					echo '</h5>';
				} elseif(!$sOwners1) {
					echo $image.'<h5>[';
					spa_etext('Storage location part 1 ownership failed');
					echo '] - ';
					spa_etext("We were unable to create your folders with the correct server ownership and these will need to be manually changed").': '.get_option('sp_storage1');
					echo '</h5>';
				}
				if (!$sCreate2) {
					echo $image.'<h5>[';
					spa_etext('Storage location part 2 creation failed');
					echo '] - ';
					spa_etext("You will need to manually create a required a folder named").': '.get_option('sp_storage2');
					echo '</h5>';
				} elseif(!$sOwners2) {
					echo $image.'<h5>[';
					spa_etext('Storage location part 2 ownership failed');
					echo '] - ';
					spa_etext("We were unable to create your folders with the correct server ownership and these will need to be manually changed").': '.get_option('sp_storage2');
					echo '</h5>';
				}
				if (!$sCopy1) {
					echo $image.'<h5>[';
					spa_etext('Resources part 1 file failed to copy');
					echo '] - ';
					spa_etext("You will need to manually copy and extract the file '/simple-press/sp-startup/install/sp-resources-install-part1.zip' to the new folder").': '.get_option('sp_storage1');
					echo '</h5>';
				}
				if (!$sCopy2) {
					echo $image.'<h5>[';
					spa_etext('Resources part 2 file failed to copy');
					echo '] - ';
					spa_etext("You will need to manually copy and extract the file '/simple-press/sp-startup/install/sp-resources-install-part2.zip' to the new folder").': '.get_option('sp_storage2');
					echo '</h5>';
				}
				if (!$sUnzip1) {
					echo $image.'<h5>[';
					spa_etext('Resources part 2 file failed to unzip');
					echo '] - ';
					spa_etext("You will need to manually unzip the file 'sp-resources-install-part1.zip' in the new folder").': '.get_option('sp_storage1');
					echo '</h5>';
				}
				if (!$sUnzip2) {
					echo $image.'<h5>[';
					spa_etext('Resources part 2 file failed to unzip');
					echo '] - ';
					spa_etext("You will need to manually unzip the file 'sp-resources-install-part2.zip' in the new folder").': '.get_option('sp_storage2');
					echo '</h5>';
				}
			}

			delete_option('sfInstallID');
			delete_option('sp_storage1');
			delete_option('sp_storage2');

			sp_delete_option('spStorageInstall1');
			sp_delete_option('spStorageInstall2');
			sp_delete_option('spOwnersInstall1');
			sp_delete_option('spOwnersInstall2');

			sp_delete_option('spCopyZip1');
			sp_delete_option('spCopyZip2');
			sp_delete_option('spUnZip1');
			sp_delete_option('spUnZip2');

			break;
	}
}

?>