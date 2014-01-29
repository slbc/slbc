<?php
/*
Simple:Press
Admin Forums Data Prep Support Functions
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_group_sequence_options($type, $current) {
	$groups = spdb_table(SFGROUPS, '', '', 'group_seq');
	$total = count($groups);
    $out = '';

	if ($groups) {
		$positions = array();
		$key = 0;

		foreach ($groups as $group) {
			if ($type == 'edit' && $current == $group->group_seq) {
				$positions[$key]['number'] = $group->group_seq;
				$positions[$key]['label']  = '<i>'.spa_text('Current position').'</i>';
			} elseif ($type == 'edit' && $group->group_seq == ($current+1)) {
				# skip
			} else {
				$positions[$key]['number'] = $group->group_seq;
				$positions[$key]['label']  = '<i>'.spa_text('Position before').'</i>:  <b>'.sp_filter_title_display($group->group_name).'</b>';
			}
			$key++;
		}
		if (($type == 'new') || ($type == 'edit' && $current < $total)) {
			$positions[$key]['number'] = ($group->group_seq+1);
			$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($group->group_name).'</b>';
		}

		if ($current == 0) $current = ($group->group_seq+1);

		if (count($positions) == 0) {
			$positions[0]['number'] = 1;
			$positions[0]['label']  = spa_text('Current position');
			$current=1;
		}


		$out.= '<td class="sflabel">'.spa_text('Display position').'</td>';
		$out.= '<td>';

		$out.= '<table class="form-table table-cbox"><tr>';
		$out.= "<td width='100%' class='td-cbox'>\n";

		$key = 1;

		foreach ($positions as $seq) {
			$check = '';
			if ($current == $key) $check = ' checked="checked" ';
			$out.= '<label for="sfradio-'.$key.'" class="sflabel radio">'.$seq['label'].'</label>'."\n";
			$out.= '<input type="radio" name="group_seq" id="sfradio-'.$key.'" value="'.$seq['number'].'" '.$check.' />'."\n";
			$key++;
		}
		$out.= '</td></tr></table>';
		$out.= '</td>';
	}
	return $out;
}

function spa_new_forum_sequence_options($action, $type, $id, $current) {
	$positions = array();
	$key = 0;

	if ($type == 'forum') {
		# grab all forums in the group except subforums
		$forums = spa_get_group_forums_by_parent($id, 0);
		$current = (count($forums)+1);

		if ($forums) {
			foreach ($forums as $forum) {
				$positions[$key]['number'] = $forum->forum_seq;
				$positions[$key]['label']  = '<i>'.spa_text('Position before').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
				$key++;
			}
			$positions[$key]['number'] = ($forum->forum_seq+1);
			$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
		}
		$current = (!empty($forum)) ? $forum->forum_seq + 1 : 1;
	}

	if ($type == 'subforum') {
		$forum = spdb_table(SFFORUMS, "forum_id=$id", 'row');

		# forum has no sub forums...
		if (empty($forum->children)) {
			$positions[$key]['number'] = ($forum->forum_seq+1);
			$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
			$current = ($forum->forum_seq+1);
		} else {
			# forum does have sub forums
			$list = array();
			$subs = unserialize($forum->children);

			$positions[$key]['number'] = ($forum->forum_seq+1);
			$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
			$list[] = ($forum->forum_seq+1);
			$key++;

			if ($subs) {
				foreach ($subs as $sub) {
					$subrecord = spdb_table(SFFORUMS, "forum_id=$sub", 'row');
					if (!in_array(($subrecord->forum_seq+1), $list)) {
						$positions[$key]['number'] = ($subrecord->forum_seq+1);
						$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($subrecord->forum_name).'</b>';
						$list[] = ($subrecord->forum_seq+1);
						$key++;
					}
				}
			}
			$current=$list[count($list)-1];
		}
	}

	if (count($positions) == 0) {
		$positions[0]['number'] = 1;
		$positions[0]['label']  = spa_text('Current position');
		$current = 1;
	}

	$out = '<table class="form-table table-cbox"><tr>';
	$out.= "<td width='100%' class='td-cbox'>\n";

	$key = 100;

	foreach ($positions as $seq) {
		$check = '';
		if ($current == $seq['number']) $check = ' checked="checked" ';
		$out.= '<label for="sfradio-'.$key.'" class="sflabel radio">'.$seq['label'].'</label>'."\n";
		$out.= '<input type="radio" class="radiosequence" name="forum_seq" id="sfradio-'.$key.'" value="'.$seq['number'].'" '.$check.' />'."\n";
		$key++;
	}
	$out.= '</td></tr></table>';

	return $out;
}

function spa_edit_forum_sequence_options($action, $type, $id, $current) {
	$positions = array();
	$key = 0;

	if ($type == 'forum') {
    	$forums = spa_get_group_forums_by_parent($id, 0);
    } else {
		$parentid = spdb_table(SFFORUMS, "forum_id=$id", 'parent');
		$parent = spdb_table(SFFORUMS, "forum_id=$parentid", 'row');
		$subs = unserialize($parent->children);
        $subs = implode(',', $subs);
        $forums = spdb_table(SFFORUMS, "forum_id IN ($subs)", '', 'forum_seq');
    }

	if ($forums) {
		foreach ($forums as $forum) {
			if ($current == $forum->forum_seq) {
				$positions[$key]['number'] = $forum->forum_seq;
				$positions[$key]['label']  = '<i>'.spa_text('Current position').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
				$key++;
			} else {
				if ($key == 0 && ($current > 1 || $current == 0)) {
					$positions[$key]['number'] = $forum->forum_seq;
					$positions[$key]['label']  = '<i>'.spa_text('Position before').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
					$key++;
   					$positions[$key]['number'] = ($forum->forum_seq + 1);
   					$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
				    $key++;
				} else {
    				$positions[$key]['number'] = ($forum->forum_seq+1);
    				$positions[$key]['label']  = '<i>'.spa_text('Position after').'</i>:  <b>'.sp_filter_title_display($forum->forum_name).'</b>';
    				$key++;
				}
			}
		}
		if ($current == 0) $current = ($forum->forum_seq + 1);
	}

	if (count($positions) == 0) {
		$positions[0]['number'] = 1;
		$positions[0]['label']  = spa_text('Current position');
		$current = 1;
	}

	$out = '<table class="form-table table-cbox"><tr>';
	$out.= "<td width='100%' class='td-cbox'>\n";

	$key = 100;

	foreach ($positions as $seq) {
		$check = '';
		if ($current == $seq['number']) $check = ' checked="checked" ';
		$out.= '<label for="sfradio-'.$key.'" class="sflabel radio">'.$seq['label'].'</label>'."\n";
		$out.= '<input type="radio" class="radiosequence" name="forum_seq" id="sfradio-'.$key.'" value="'.$seq['number'].'" '.$check.' />'."\n";
		$key++;
	}
	$out.= '</td></tr></table>';

	return $out;
}

function spa_paint_custom_icons() {
	global $spPaths;

	$out = '';

	# Open custom icons folder and get cntents for matching
	$path = SF_STORE_DIR.'/'.$spPaths['custom-icons'].'/';
	$dlist = @opendir($path);
	if (!$dlist) {
		echo '<table><tr><td class="sflabel"><strong>'.spa_text('The custom icons folder does not exist').'</strong></td></tr></table>';
		return;
	}

	# start the table display
	$out.= '<table class="form-table"><tr>';
	$out.= '<th style="width:60%;text-align:center">'.spa_text('Icon').'</th>';
	$out.= '<th style="width:30%;text-align:center">'.spa_text('Filename').'</th>';
	$out.= '<th style="width:9%;text-align:center">'.spa_text('Remove').'</th>';
	$out.= '</tr>';

    $out.= '<tr><td colspan="3">';
    $out.= '<div id="sf-custom-icons">';
	while (false !== ($file = readdir($dlist))) {
		if ($file != "." && $file != "..") {
			$found = false;
		    $out.= '<table width="100%">';
			$out.= '<tr>';
			$out.= '<td align="center" width="60%" ><img class="sfcustomicon" src="'.esc_url(SFCUSTOMURL.'/'.$file).'" alt="" /></td>';
			$out.= '<td align="center" width="30%" class="sflabel">';
			$out.= $file;
			$out.= '</td>';
			$out.= '<td align="center" width="9%" class="sflabel">';
			$site = esc_url(SFHOMEURL.'index.php?sp_ahah=forums&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;action=delicon&amp;file=$file");
			$out.= '<img src="'.SFCOMMONIMAGES.'delete.png" title="'.spa_text('Delete custom icon').'" alt="" onclick="spjDelRowReload(\''.$site.'\', \'sfreloadci\');" />';
			$out.= '</td>';
			$out.= '</tr>';
			$out.= '</table>';
		}
	}
	$out.= '</div>';
	$out.= '</td></tr></table>';
	closedir($dlist);

	echo $out;
	return;
}
?>