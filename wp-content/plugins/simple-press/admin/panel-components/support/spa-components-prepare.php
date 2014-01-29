<?php
/*
Simple:Press
Admin Components General Support Functions
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_get_login_data() {
	$sfcomps = array();

	$sflogin = array();
	$sflogin = sp_get_option('sflogin');
	$sfcomps['sfregmath'] = $sflogin['sfregmath'];
	$sfcomps['sfloginurl'] = sp_filter_url_display($sflogin['sfloginurl']);
	$sfcomps['sfloginemailurl'] = sp_filter_url_display($sflogin['sfloginemailurl']);
	$sfcomps['sflogouturl'] = sp_filter_url_display($sflogin['sflogouturl']);
	$sfcomps['sfregisterurl'] = sp_filter_url_display($sflogin['sfregisterurl']);
	$sfcomps['sptimeout'] = sp_esc_int($sflogin['sptimeout']);

	$sfrpx = sp_get_option('sfrpx');
	$sfcomps['sfrpxenable'] = $sfrpx['sfrpxenable'];
	$sfcomps['sfrpxkey'] = $sfrpx['sfrpxkey'];
	$sfcomps['sfrpxredirect'] = sp_filter_url_display($sfrpx['sfrpxredirect']);

	return $sfcomps;
}

function spa_get_seo_data() {
	$sfcomps = array();

	# browser title
	$sfseo = sp_get_option('sfseo');
	$sfcomps['sfseo_overwrite'] = $sfseo['sfseo_overwrite'];
	$sfcomps['sfseo_blogname'] = $sfseo['sfseo_blogname'];
	$sfcomps['sfseo_pagename'] = $sfseo['sfseo_pagename'];
	$sfcomps['sfseo_topic'] = $sfseo['sfseo_topic'];
	$sfcomps['sfseo_forum'] = $sfseo['sfseo_forum'];
	$sfcomps['sfseo_noforum'] = $sfseo['sfseo_noforum'];
	$sfcomps['sfseo_page'] = $sfseo['sfseo_page'];
	$sfcomps['sfseo_sep'] = $sfseo['sfseo_sep'];

	# meta tags
	$sfmetatags= array();
	$sfmetatags = sp_get_option('sfmetatags');
	$sfcomps['sfdescr'] = sp_filter_title_display($sfmetatags['sfdescr']);
	$sfcomps['sfdescruse'] = $sfmetatags['sfdescruse'];
	$sfcomps['sfusekeywords'] = sp_filter_title_display($sfmetatags['sfusekeywords']);
	$sfcomps['sfkeywords'] = $sfmetatags['sfkeywords'];

	return $sfcomps;
}

function spa_get_forumranks_data() {
	$rankings = sp_get_sfmeta('forum_rank');

	return $rankings;
}

function spa_get_specialranks_data() {
	$special_rankings = sp_get_sfmeta('special_rank');

	return $special_rankings;
}

function spa_get_messages_data() {
	$sfcomps = array();

	# custom message for posts
	$sfpostmsg = array();
	$sfpostmsg = sp_get_option('sfpostmsg');
	$sflogin = array();
	$sflogin = sp_get_option('sflogin');

	$sfcomps['sfpostmsgtext'] = sp_filter_text_edit($sfpostmsg['sfpostmsgtext']);
	$sfcomps['sfpostmsgtopic'] = $sfpostmsg['sfpostmsgtopic'];
	$sfcomps['sfpostmsgpost'] = $sfpostmsg['sfpostmsgpost'];

	# custom editor message
	$sfcomps['sfeditormsg'] = sp_filter_text_edit(sp_get_option('sfeditormsg'));

	$sneakpeek = sp_get_sfmeta('sneakpeek', 'message');
	$adminview = sp_get_sfmeta('adminview', 'message');
	$userview = sp_get_sfmeta('userview', 'message');

	$sfcomps['sfsneakpeek'] = '';
	$sfcomps['sfadminview'] = '';
	$sfcomps['sfuserview'] = '';
	if (!empty($sneakpeek[0])) $sfcomps['sfsneakpeek'] = sp_filter_text_edit($sneakpeek[0]['meta_value']);
	if (!empty($adminview[0])) $sfcomps['sfadminview'] = sp_filter_text_edit($adminview[0]['meta_value']);
	if (!empty($userview[0])) $sfcomps['sfuserview'] = sp_filter_text_edit($userview[0]['meta_value']);
	$sfcomps['sfsneakredirect'] = sp_filter_url_display($sflogin['sfsneakredirect']);

	return $sfcomps;
}

function spa_paint_custom_smileys() {
	global $spPaths, $tab;

	$out='';
	$scount = -1;

	# load smiles from sfmeta
	$filelist = array();

	$meta = sp_get_sfmeta('smileys', 'smileys');
	$smeta = $meta[0]['meta_value'];

	# Open forum-smileys folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['smileys'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
	   echo '<table><tr><td class="sflabel"><strong>'.spa_text('The forum-smileys folder does not exist').'</strong></td></tr></table>';
       return;
    }

	# start the table display
	$out.= '<table class="form-table"><tr>';
	$out.= '<th style="width:5%;text-align:center"></th>';
	$out.= '<th style="width:5%;text-align:center"></th>';
	$out.= '<th style="width:25%;text-align:center">'.spa_text('File').'</th>';
	$out.= '<th style="width:25%;text-align:center">'.spa_text('Name').'</th>';
	$out.= '<th style="width:25%;text-align:center">'.spa_text('Code').'</th>';
	$out.= '<th style="width:5%;text-align:center">'.spa_text('Break').'</th>';
	$out.= '<th style="width:5%;text-align:center">'.spa_text('In Use').'</th>';
	$out.= '<th style="width:5%;text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

    $out.= '<tr><td colspan="8">';
	$row = 0;
	$out.= '<table width="100%" id="sfsmileytable" border="1" cellspacing="0">';

	# gather the file data
	while (false !== ($file = readdir($dlist))) {
		$path_info = pathinfo($path.$file);
		$ext = strtolower($path_info['extension']);
		if (($file != "." && $file != "..") && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp')) {
			$filelist[] = $file;
		}
	}

	# now to sort them if required
	$newfiles = (count($filelist)+1);
	$sortlist = array();

	if ($filelist) {
		foreach ($filelist as $file) {
			$found = false;
			if ($meta[0]['meta_value']) {
				foreach ($meta[0]['meta_value'] as $name => $info) {
					if ($info[0] == $file) {
						$found = true;
						break;
					}
				}
			}
			if ($found) {
				if (isset($info[3])) {
					$sortlist[$info[3]] = $file;
				} else {
					$sortlist[] = $file;
				}
			} else {
				$sortlist[$newfiles] = $file;
				$newfiles++;
			}
		}
		ksort($sortlist);
	}

	if ($sortlist) {
		foreach ($sortlist as $file) {
			$found = false;
			$out.= '<tr id="'.$row.'">';
			$row++;
			$out.= '<td width="5%" class="dragHandle">&nbsp;</td>';
			$out.= '<td width="5%" class="sflabel" align="center"><img class="spSmiley" src="'.SFSMILEYS.$file.'" alt="" /></td>';
			if ($meta[0]['meta_value']) {
				foreach ($meta[0]['meta_value'] as $name => $info) {
					if ($info[0] == $file) {
						$found = true;
						break;
					}
				}
			}
			if (!$found) {
				$sname = '';
				$code = '';
				$in_use = '';
				$break = '';
			} else {
				$code = stripslashes($info[1]);
				$sname = $name;
				$in_use = $info[2];
				if (isset($info[4]) ? $break=$info[4] : $break=false);
			}
			$scount++;

			$out.= '<td width="25%" class="sflabel" class="sflabel" align="center">';
			$out.= '<input type="hidden" name="smfile[]" value="'.$file.'" />';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td width="25%" class="sflabel" align="center">';
			$out.= '<input type="text" class="sfpostcontrol" size="20" tabindex="'.$tab.'" name="smname[]" value="'.$sname.'" />';
			$out.= '</td>';
			$out.= '<td width="25%" class="sflabel" align="center">';
			$out.= '<input type="text" class="sfpostcontrol" size="20" tabindex="'.$tab.'" name="smcode[]" value="'.$code.'" />';
			$out.= '</td>';

			$out.= '<td width="5%" class="sflabel" align="center">';
			$checked = '';
			if ($break) $checked = " checked='checked'";
			if ($sname == '') $sname = 'newbreak-'.$scount;
			$out.= '<label for="sfbreak-'.$sname.'"></label>';
			$out.= '<input type="checkbox" name="smbreak-'.$sname.'" id="sfbreak-'.$sname.'"'.$checked.' />';
			$out.= '</td>';

			$out.= '<td width="5%" class="sflabel" align="center">';
			$checked = '';
			if ($in_use) $checked = " checked='checked'";
			if ($sname == '') $sname = 'new-'.$scount;
			$out.= '<label for="sf-'.$sname.'"></label>';
			$out.= '<input type="checkbox" name="sminuse-'.$sname.'" id="sf-'.$sname.'"'.$checked.' />';
			$out.= '</td>';

			$out.= '<td width="5%" class="sflabel" align="center">';
			$site = esc_url(SFHOMEURL."index.php?sp_ahah=components&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;action=delsmiley&amp;file=".$file);
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete Smiley').'" alt="" onclick="spjDelRowReload(\''.$site.'\', \'sfreloadsm\');" />';
			$out.= '</td>';
			$out.= '</tr>';
		}
	}

	$out.= '</table>';
	$out.= '<input type="hidden" id="smiley-count" name="smiley-count" value="'.$scount.'" />';
	$out.= '</td></tr></table>';
	closedir($dlist);
	echo $out;
}

function spa_paint_rank_images() {
	global $tab, $spPaths;

	$out = '';

	# Open badges folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['ranks'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<tr><td class="sflabel"><strong>'.spa_text('The rank badges folder does not exist').'</strong></td></tr>';
		return;
	}

	# start the table display
	$out.= '<table class="form-table"><tr>';
	$out.= '<th style="width:60%;text-align:center">'.spa_text('Badge').'</th>';
	$out.= '<th style="width:30%;text-align:center">'.spa_text('Filename').'</th>';
	$out.= '<th style="width:9%;text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

    $out.= '<tr><td colspan="3">';
    $out.= '<div id="sf-rank-badges">';
	while (false !== ($file = readdir($dlist))) {
		$path_info = pathinfo($path.$file);
		$ext = strtolower($path_info['extension']);
		if (($file != "." && $file != "..") && ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp')) {
			$found = false;
		    $out.= '<table width="100%">';
			$out.= '<tr>';
			$out.= '<td align="center" width="60%" ><img class="sfrankbadge" src="'.esc_url(SFRANKS.'/'.$file).'" alt="" /></td>';
			$out.= '<td align="center" width="30%" class="sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td align="center" width="9%" class="sflabel">';
			$site = esc_url(SFHOMEURL."index.php?sp_ahah=components&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;action=delbadge&amp;file=".$file);
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete Rank Badge').'" alt="" onclick="spjDelRowReload(\''.$site.'\', \'sfreloadfr\');" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';
	closedir($dlist);
	echo $out;
}

?>