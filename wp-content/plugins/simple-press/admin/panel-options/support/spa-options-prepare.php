<?php
/*
Simple:Press
Admin Options General Support Functions
$LastChangedDate: 2013-06-12 07:32:14 -0700 (Wed, 12 Jun 2013) $
$Rev: 10370 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_global_data() {
	$sfoptions = array();
	$sfoptions['sflockdown'] = sp_get_option('sflockdown');

	# auto update
	$sfauto = sp_get_option('sfauto');
	$sfoptions['sfautoupdate'] = $sfauto['sfautoupdate'];
	$sfoptions['sfautotime'] = $sfauto['sfautotime'];

	$sfrss = sp_get_option('sfrss');
	$sfoptions['sfrsscount'] = $sfrss['sfrsscount'];
	$sfoptions['sfrsswords'] = $sfrss['sfrsswords'];
	$sfoptions['sfrssfeedkey'] = $sfrss['sfrssfeedkey'];
	$sfoptions['sfrsstopicname'] = $sfrss['sfrsstopicname'];

	$sfblock = sp_get_option('sfblockadmin');
	$sfoptions['blockadmin'] = $sfblock['blockadmin'];
	$sfoptions['blockredirect'] = sp_filter_url_display($sfblock['blockredirect']);
	$sfoptions['blockprofile'] = $sfblock['blockprofile'];
    $sfoptions['blockroles'] = $sfblock['blockroles'];

	$sfoptions['defeditor'] = sp_get_option('speditor');
	if (!isset($sfoptions['defeditor']) || empty($sfoptions['defeditor'])) $sfoptions['defeditor'] = 4;

	$sfoptions['combinecss'] = sp_get_option('combinecss');
	$sfoptions['combinejs'] = sp_get_option('combinejs');

	return $sfoptions;
}

function spa_get_display_data() {
	$sfdisplay = sp_get_option('sfdisplay');
	$sfcontrols = sp_get_option('sfcontrols');

	# Page title
	$sfoptions['sfnotitle'] = $sfdisplay['pagetitle']['notitle'];
	$sfoptions['sfbanner']  = sp_filter_url_display($sfdisplay['pagetitle']['banner']);

	# Stats
	$sfoptions['showtopcount']	= $sfcontrols['showtopcount'];
	$sfoptions['shownewcount']	= $sfcontrols['shownewcount'];
	$sfoptions['statsinterval']	= sp_get_option('sp_stats_interval') / 3600; # display in hours

	$sfoptions['sfdefunreadposts'] = $sfcontrols['sfdefunreadposts'];
	$sfoptions['sfusersunread'] = $sfcontrols['sfusersunread'];
	$sfoptions['sfmaxunreadposts'] = $sfcontrols['sfmaxunreadposts'];

	$sfoptions['sfsingleforum'] = $sfdisplay['forums']['singleforum'];

	$sfoptions['sfpagedtopics'] = $sfdisplay['topics']['perpage'];
	$sfoptions['sftopicsort'] 	= $sfdisplay['topics']['sortnewtop'];

	$sfoptions['sfpagedposts'] 	= $sfdisplay['posts']['perpage'];
	$sfoptions['sfsortdesc'] 	= $sfdisplay['posts']['sortdesc'];

	$sfoptions['sftoolbar']		= $sfdisplay['editor']['toolbar'];

	return $sfoptions;
}

function spa_get_content_data() {
	$sfoptions = array();

	# image resizing
	$sfimage = sp_get_option('sfimage');
	$sfoptions['sfimgenlarge'] = $sfimage['enlarge'];
	$sfoptions['sfthumbsize'] = $sfimage['thumbsize'];
	$sfoptions['style'] = $sfimage['style'];
	$sfoptions['process'] = $sfimage['process'];
	$sfoptions['constrain'] = $sfimage['constrain'];
	$sfoptions['forceclear'] = $sfimage['forceclear'];

	$sfoptions['sfdates'] = sp_get_option('sfdates');
	$sfoptions['sftimes'] = sp_get_option('sftimes');

	if (empty($sfoptions['sfdates'])) $sfoptions['sfdates'] = 'j F Y';
	if (empty($sfoptions['sftimes'])) $sfoptions['sftimes'] = 'g:i a';

	# link filters
	$sffilters = sp_get_option('sffilters');
	$sfoptions['sfnofollow'] = $sffilters['sfnofollow'];
	$sfoptions['sftarget'] = $sffilters['sftarget'];
	$sfoptions['sfurlchars'] = $sffilters['sfurlchars'];
	$sfoptions['sffilterpre'] = $sffilters['sffilterpre'];
	$sfoptions['sfmaxlinks'] = $sffilters['sfmaxlinks'];
	$sfoptions['sfnolinksmsg'] = sp_filter_text_edit($sffilters['sfnolinksmsg']);
	$sfoptions['sfdupemember'] = $sffilters['sfdupemember'];
	$sfoptions['sfdupeguest'] = $sffilters['sfdupeguest'];
	$sfoptions['sfmaxsmileys'] = $sffilters['sfmaxsmileys'];

	# shortcode filtering
	$sfoptions['sffiltershortcodes'] = sp_get_option('sffiltershortcodes');
	$sfoptions['sfshortcodes'] = sp_filter_text_edit(sp_get_option('sfshortcodes'));

	$sfpostwrap = sp_get_option('sfpostwrap');
	$sfoptions['postwrap']=$sfpostwrap['postwrap'];
	$sfoptions['postwidth']=$sfpostwrap['postwidth'];

	return $sfoptions;
}

function spa_get_members_data() {
	global $wp_roles;

	$sfoptions = array();

	$sfmemberopts = sp_get_option('sfmemberopts');
	$sfoptions['sfcheckformember'] = $sfmemberopts['sfcheckformember'];
	$sfoptions['sfhidestatus'] = $sfmemberopts['sfhidestatus'];

	$sfguests = sp_get_option('sfguests');
	$sfoptions['reqemail'] = $sfguests['reqemail'];
	$sfoptions['storecookie'] = $sfguests['storecookie'];

	$sfuser = sp_get_option('sfuserremoval');
	$sfoptions['sfuserremove'] = $sfuser['sfuserremove'];
	$sfoptions['sfuserperiod'] = $sfuser['sfuserperiod'];
	$sfoptions['sfuserinactive'] = $sfuser['sfuserinactive'];
	$sfoptions['sfusernoposts'] = $sfuser['sfusernoposts'];

	$sfoptions['account-name'] = sp_get_option('account-name');
	$sfoptions['display-name'] = sp_get_option('display-name');
	$sfoptions['guest-name'] = sp_get_option('guest-name');

	# cron scheduled?
	$sfoptions['sched'] = wp_get_schedule('sph_cron_user');

	$sfoptions['post_count_delete'] = sp_get_option('post_count_delete');

	return $sfoptions;
}

function spa_get_email_data() {
	$sfoptions = array();

	# Load New User Email details
	$sfmail = sp_get_option('sfnewusermail');
	$sfoptions['sfusespfreg'] = $sfmail['sfusespfreg'];
	$sfoptions['sfnewusersubject'] = sp_filter_title_display($sfmail['sfnewusersubject']);
	$sfoptions['sfnewusertext'] = sp_filter_title_display($sfmail['sfnewusertext']);

	# Load Email Filter Options
	$sfmail = sp_get_option('sfmail');
	$sfoptions['sfmailsender'] = $sfmail['sfmailsender'];
	$sfoptions['sfmailfrom'] = $sfmail['sfmailfrom'];
	$sfoptions['sfmaildomain'] = $sfmail['sfmaildomain'];
	$sfoptions['sfmailuse'] = $sfmail['sfmailuse'];

	return $sfoptions;
}

function spa_get_style_data() {
	$sfoptions = array();

	# post content wrap and width
	$sfpostwrap = array();
	$sfpostwrap = sp_get_option('sfpostwrap');
	$sfoptions['postwrap']=$sfpostwrap['postwrap'];
	$sfoptions['postwidth']=$sfpostwrap['postwidth'];

	$sfoptions['sfwplistpages'] = sp_get_option('sfwplistpages');

	$sfoptions['sftemplate'] = false;

	return $sfoptions;
}

?>