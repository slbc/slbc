<?php
/*
Simple:Press
Template handler
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# --------------------------------------------------------------------------------------
#
# sp_process_template()
#
# The main control center for the loading up of the required templates.
# Uses the $spVars 'pageview' to determine which template to load.
# Templates are always surrounded by the spMainContainer div
#
# --------------------------------------------------------------------------------------
function sp_process_template() {
	global $spVars, $spGlobals, $spThisUser, $spNewPosts;

	# grab the pageview, checking to see if its a search page
	$pageview = $spVars['pageview'];

	# determine page template to load
	switch ($pageview) {
		case 'group':
			$tempName = sp_process_group_view();
			break;

		case 'forum':
			$tempName = sp_process_forum_view();
			break;

		case 'topic':
			$tempName = sp_process_topic_view();
			break;

		case 'search':
			$tempName = sp_process_search_view();
			break;

		case 'members':
			$tempName = sp_process_members_view();
			break;

		case 'profileedit':
			$tempName = sp_process_profileedit_view();
			break;

		case 'profileshow':
			$tempName = sp_process_profileshow_view();
			break;

		case 'newposts':
			$tempName = sp_process_newposts_view();
			break;

		default:
			$tempName = sp_process_default_view($pageview);
			break;
	}

	# allow plugins/themes access to the template name
	$tempName = apply_filters('sph_TemplateName', $tempName, $pageview);

	# allow output prior to SP display
	do_action('sph_BeforeDisplayStart', $pageview, $tempName);

	# SP display starts here

	# Any control data item inspection needed
	if ($spThisUser->admin && !empty($spThisUser->inspect)) sp_display_inspector('control', '');

    # forum top anchor
	echo '<a id="spForumTop"></a>';

	# Define the main forum container
	echo "\n\n<!-- Simple:Press display start -->\n\n";
	echo '<div id="spMainContainer">';

	# Create the sliding panel div needed for mobile display
	echo "<div id='spMobilePanel'></div>";

	# allow output before the SP display
	do_action('sph_AfterDisplayStart', $pageview, $tempName);

	# load the pageview template if valid
	sp_load_template($tempName);

	# allow output after the SP display
	do_action('sph_BeforeDisplayEnd', $pageview, $tempName);

	# Display any queued messages
	sp_render_queued_notification();

	echo '</div>';
	echo "\n\n<!-- Simple:Press display end -->\n\n";

	# forum bottom anchor
	echo '<a id="spForumBottom"></a>';

	# SP display ends here

	# allow output after the SP display
	do_action('sph_AfterDisplayEnd', $pageview, $tempName);

	# Post display processing
	sp_post_display_processing($pageview);
}

# --------------------------------------------------------------------------------------
#
# sp_process_group_view()
#
# Performs group view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_group_view() {
	global $spGroupView;
	return 'spGroupView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_forum_view()
#
# Performs forum view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_forum_view() {
	global $spForumView, $spVars;

	# Store the topic page so that we can get back to it later (breadcrumb usage)
	sp_push_topic_page($spVars['forumid'], $spVars['page']);

	return 'spForumView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_topic_view()
#
# Performs topic view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_topic_view() {
	global $spTopicView, $spVars, $spThisUser;

	if (!empty($spVars['topicid'])) {
		# reduce unread counts for topic views if needed
		sp_remove_users_newposts($spVars['topicid'], $spThisUser->ID);
	}
	return 'spTopicView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_search_view()
#
# Performs search processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_search_view() {
	global $spSearchView;
	return 'spSearchView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_members_view()
#
# Performs members list view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_members_view() {
	global $spMembersList;
	return 'spMembersView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_profileedit_view()
#
# Performs profile edit view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_profileedit_view() {
	return 'spProfileEdit.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_profileshow_view()
#
# Performs profile show view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_profileshow_view() {
	global $spVars, $spThisUser;
	if (!empty($spVars['member'])) {
		$dname = urldecode($spVars['member']);
		$userid = spdb_table(SFUSERS, "user_login='$dname'", 'ID');
	} else {
		$userid = $spThisUser->ID;
	}

    if (!sp_get_auth('view_profiles') || empty($userid)) {
		sp_notify(1, sp_text('Invalid profile request'));
		return 'spDefault.php';
    } else {
		global $spProfileUser;
		sp_SetupUserProfileData();
		return 'spProfileShow.php';
	}
}

# --------------------------------------------------------------------------------------
#
# sp_process_permissions_view()
#
# Performs pemissions view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_permissions_view() {
	return 'spPermissions.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_newposts_view()
#
# Performs new posts view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_newposts_view() {
	return 'spNewPostsView.php';
}

# --------------------------------------------------------------------------------------
#
# sp_process_default_view()
#
# $pageview:	The current page view (likely plugin defined)
#
# Performs default and user defined view processing and returns the template file name
#
# --------------------------------------------------------------------------------------
function sp_process_default_view($pageview) {
	# try building standard template name based on unknown pageview type
	$tempName = 'sp'.ucfirst($pageview).'.php';

	# now see if the 'standard' template file for this pageview exists
	# if not, set template to default template
	$template = SPTEMPLATES.$tempName;

	# allow plugins/themes access to the template name
	$template = apply_filters('sph_DefaultViewTemplate', $template, $pageview);

	if (!file_exists($template)) $template = SPTEMPLATES.'spDefault.php';
	return $template;
}

# --------------------------------------------------------------------------------------
#
# sp_load_template()
#
# $tempName:	The template name.
#
# Opens and Includes the required template. Returns textual errors if the
# file is not found
#
# --------------------------------------------------------------------------------------
function sp_load_template($tempName) {
	# set up some globals for theme template files to use directly
	global $spGroupView, $spThisGroup, $spForumView, $spThisForum, $spThisSubForum, $spThisForumSubs,
	$spTopicView, $spThisTopic, $spThisPost, $spThisPostUser, $spListView, $spThisListTopic,
    $spThisUser, $spProfileUser, $spMembersList, $spThisMemberGroup, $spThisMember,
    $spGlobals, $spVars, $spDevice, $spMobile;

	if (!empty($tempName) && file_exists($tempName)) {
		include ($tempName);
	} else if (!empty($tempName) && file_exists(SPTEMPLATES.$tempName)) {
		include (SPTEMPLATES.$tempName);
	} else {
		$tempName = explode('/', $tempName);
		echo '<p class="spCenter spHeaderName">['.$tempName[count($tempName) - 1].'] - '.sp_text('Template File Not Found').'</p>';
        echo '<div class="spHeaderMessage">';
        echo '<p>'.spa_text('Sorry, but the required template file could not be found or could not be opened.').'</p>';
        echo '<br/><p>';
        spa_etext('This can be caused by a missing/corrupt theme or theme file. Please check the Simple:Press Theme List admin panel and make sure a valid theme is selected. Or please check the location of the selected theme on your server and make sure the theme and the required template file exist.');
        echo '</p>';
        echo '</div>';
	}
}

# --------------------------------------------------------------------------------------
#
# sp_post_display_processing()
# Any tasks that ma be needed after the display os all rendered
#
# --------------------------------------------------------------------------------------
function sp_post_display_processing($pageview) {
	global $spThisTopic;
	if ($pageview == 'topic' && !empty($spThisTopic)) {
		sp_update_opened($spThisTopic->topic_id, $spThisTopic->display_page);
	}
}

# --------------------------------------------------------------------------------------
#
# sp_HeaderBegin()
# Fires a wp action to indicate SP header start for plugins
#
# --------------------------------------------------------------------------------------
function sp_HeaderBegin() {
	do_action('sph_HeaderBegin');
}

# --------------------------------------------------------------------------------------
#
# sp_HeaderEnd()
# Fires a wp action to indicate SP header end for plugins
#
# --------------------------------------------------------------------------------------
function sp_HeaderEnd() {
	do_action('sph_HeaderEnd');
}

# --------------------------------------------------------------------------------------
#
# sp_FooterBegin()
# Fires a wp action to indicate SP footer start for plugins
#
# --------------------------------------------------------------------------------------
function sp_FooterBegin() {
	do_action('sph_FooterBegin');
}

# --------------------------------------------------------------------------------------
#
# sp_FooterEnd()
# Fires a wp action to indicate SP footer end for plugins
#
# --------------------------------------------------------------------------------------
function sp_FooterEnd() {
	do_action('sph_FooterEnd');
}

# --------------------------------------------------------------------------------------
#
# __sp()
#
# $text		text string to be translated
# $domain	unique domain name of theme
#
# NOTES TO FOLLOW WHEN FUNCTION IS WRITTEN
#
# --------------------------------------------------------------------------------------

function __sp($text) {
    global $spGlobals;
    $domain = (isset($spGlobals['themedomain'])) ? $spGlobals['themedomain'] : '';
    return __($text, $domain);
}
?>