<?php
/*
Simple:Press
Common AHAH
$LastChangedDate: 2013-09-23 03:50:55 -0700 (Mon, 23 Sep 2013) $
$Rev: 10729 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

spa_admin_ahah_support();

# subseqeent page loads,,,
if (isset($_GET['page_msbox'])) {
	$msbox = $_GET['msbox'];
	$uid = sp_esc_int($_GET['uid']);
	$name = esc_attr($_GET['name']);
	$from = esc_html($_GET['from']);
	$num = sp_esc_int($_GET['num']);
	$offset = sp_esc_int($_GET['offset']);
	$max = sp_esc_int($_GET['max']);
	$filter = urldecode($_GET['filter']);

	if ($_GET['page_msbox'] == 'filter') $max = spa_get_query_max($msbox, $uid, $filter);
	echo spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter);
	die();
}

# handle include of file but not via ahah
if (isset($action)) {

	if ($action == 'addug') {
		spa_prepare_msbox_list('usergroup_add', $usergroup_id);
		echo spa_populate_msbox_list('usergroup_add', $usergroup_id, 'amid', $from, $to, 100);
	}

	if ($action == 'delug') {
		spa_prepare_msbox_list('usergroup_del', $usergroup_id);
		echo spa_populate_msbox_list('usergroup_del', $usergroup_id, 'dmid', $from, $to, 100);
	}

	if ($action == 'addru') {
		spa_prepare_msbox_list('rank_add', $rank_id);
		echo spa_populate_msbox_list('rank_add', $rank_id, 'amember_id', $from, $to, 100);
	}

	if ($action == 'delru') {
		spa_prepare_msbox_list('rank_del', $rank_id);
		echo spa_populate_msbox_list('rank_del', $rank_id, 'dmember_id', $from, $to, 100);
	}

	if ($action == 'addadmin') {
		spa_prepare_msbox_list('admin_add', '');
		echo spa_populate_msbox_list('admin_add', '', 'member_id', $from, $to, 100);
	}

	return;
}
die();

# --------------------------------------------------------------------------
# Create the temporary table to be used for all operations
# --------------------------------------------------------------------------
function spa_prepare_msbox_list($msbox, $uid) {
	# drop any existing temp table tp start afresh
	spdb_query('DROP TABLE IF EXISTS sftempmembers');

	switch ($msbox) {

		case 'usergroup_add':
			$records = spdb_query('CREATE TABLE sftempmembers AS
				SELECT DISTINCT '.SFMEMBERS.'.user_id, '.SFMEMBERS.'.display_name
				FROM '.SFMEMBERSHIPS.'
				RIGHT JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id
				WHERE (usergroup_id != '.$uid.' AND admin = 0) OR ('.SFMEMBERSHIPS.'.user_id IS NULL AND admin = 0)
				ORDER BY display_name'
			);
			break;

		case 'usergroup_del':
			$records =	spdb_query('CREATE TABLE sftempmembers AS
				SELECT DISTINCT '.SFMEMBERS.'.user_id, '.SFMEMBERS.'.display_name
				FROM '.SFMEMBERSHIPS.'
				JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFMEMBERSHIPS.'.user_id
				WHERE '.SFMEMBERSHIPS.'.usergroup_id='.$uid.'
				ORDER BY display_name'
			);
			break;

		case 'rank_add':
			$specialRank = sp_get_sfmeta('special_rank', false, $uid);
			$rank = $specialRank[0]['meta_key'];
			$records =	spdb_query('CREATE TABLE sftempmembers AS
				SELECT DISTINCT '.SFMEMBERS.'.user_id, '.SFMEMBERS.'.display_name
				FROM '.SFSPECIALRANKS.'
				RIGHT JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFSPECIALRANKS.'.user_id
				WHERE (special_rank != "'.$rank.'") OR ('.SFSPECIALRANKS.'.user_id IS NULL)
				ORDER BY display_name'
			);
			break;

		case 'rank_del':
			$specialRank = sp_get_sfmeta('special_rank', false, $uid);
			$rank = $specialRank[0]['meta_key'];
			$records =	spdb_query('CREATE TABLE sftempmembers AS
				SELECT DISTINCT '.SFMEMBERS.'.user_id, '.SFMEMBERS.'.display_name
				FROM '.SFSPECIALRANKS.'
				RIGHT JOIN '.SFMEMBERS.' ON '.SFMEMBERS.'.user_id = '.SFSPECIALRANKS.'.user_id
				WHERE (special_rank = "'.$rank.'")
				ORDER BY display_name'
			);
			break;

		case 'admin_add':
			$records = spdb_query('CREATE TABLE sftempmembers AS
				SELECT '.SFMEMBERS.'.user_id, display_name
				FROM '.SFMEMBERS.'
				WHERE admin=0
				ORDER BY display_name'
			);
			break;
	}
}

# --------------------------------------------------------------------------
# Initial list (i.e., psge 1)
# --------------------------------------------------------------------------
function spa_populate_msbox_list($msbox, $uid, $name, $from, $to, $num) {
	$out = '';

	$records = spdb_select('set', 'SELECT * FROM sftempmembers LIMIT 0, '.$num);
	$max = spa_get_query_max($msbox, $uid, '');

	$out.= '<table>';
	$out.= '<tr>';
	$out.= '<td width="50%" style="border:none !important;vertical-align: top !important;">';
	$out.= '<div id="mslist-'.$name.$uid.'">';
	$out.= spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, 0, $max, '');
	$out.= '</div>';
	$out.= '</td>';

	$out.= '<td width="50%" style="border:none !important;vertical-align: top !important;">';
	$out.= '<div align="center"><strong>'.$to.' <span id="selcount">0</span></strong></div>';
	$out.= '<select class="sfacontrol" multiple="multiple" size="10" id="'.$name.$uid.'" name="'.$name.'[]" >';
	$out.= '<option disabled="disabled" value="-1">'.spa_text('List is empty').'</option>';
	$out.= '</select>';
	$out.= '<div align="center" style="margin-top:29px;">';
	$out.= '<p>'.spa_text('Maximum Selection - 400 Users').'</p>';
	$out.= '<input type="button" id="add'.$uid.'" class="button button-highlighted" value="'.spa_text('Remove From Selected List').'" onclick="spjTransferSelectList(\''.$name.$uid.'\', \'temp-'.$name.$uid.'\', \''.esc_js(spa_text('List is Empty')).'\', \''.esc_js(spa_text('Maximum of 400 Users would be exceeded - please reduce the selections')).'\', \''.$name.$uid.'\')" />';
	$out.= '</div>';
	$out.= '</td>';
	$out.= '</tr>';
	$out.= '</table>';

	return $out;
}

# --------------------------------------------------------------------------
# Subsequent page loads
# --------------------------------------------------------------------------
function spa_page_msbox_list($msbox, $uid, $name, $from, $num, $offset, $max, $filter) {
	$out = '';
	$like = '';
	if ($filter != '') $like = " WHERE display_name LIKE '%".esc_sql(like_escape($filter))."%'";
	$records = spdb_select('set', 'SELECT * FROM sftempmembers'.$like.' LIMIT '.$offset.', '.$num);

	$out.= spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter);
	return $out;
}

# --------------------------------------------------------------------------
# get the record counts
# --------------------------------------------------------------------------
function spa_get_query_max($msbox, $uid, $filter) {
	$like = '';

	if ($filter != '') $like = " WHERE display_name LIKE '%".esc_sql(like_escape($filter))."%'";
	$max = spdb_select('var', '
		SELECT COUNT(*) AS user_count
		FROM sftempmembers'.$like
	);

	if (!$max) $max = 0;
	return $max;
}

function spa_render_msbox_list($msbox, $uid, $name, $from, $num, $records, $offset, $max, $filter) {
	$out = '';
	$empty = true;

	$out.= '<div align="center"><strong>'.$from.'</strong></div>';
	$out.= '<select class="sfacontrol" multiple="multiple" size="10" id="temp-'.$name.$uid.'" name="temp-'.$name.$uid.'[]">';

	if ($records) {
		foreach ($records as $record) {
			$empty = false;
			$out.= '<option value="'.$record->user_id.'">'.sp_filter_name_display($record->display_name).'</option>'."\n";
		}
	}
	if ($empty) $out.= '<option disabled="disabled" value="-1">'.spa_text('List is empty').'</option>';
	$out.= '</select>';

	$out.= '<div align="center">';
	$out.= '<small style="line-height:1.6em;">'.spa_text('Paging Controls').'</small><br />';
	$out.= '<span id="filter-working"></span>';
	$last = floor($max / $num) * $num;
	if ($last >= $max) $last = $last - $num;
	$disabled = '';
	if (($offset + $num) >= $max) $disabled = ' disabled="disabled"';

	$site = SFHOMEURL.'index.php?sp_ahah=multiselect&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=".urlencode($from)."&amp;num=$num&amp;offset=$last&amp;max=$max&amp;filter=$filter";
	$out.= '<input type="button"'.$disabled.' id="lastpage'.$uid.'" class="button button-highlighted sfalignright" value="'.spa_text('Last').'" onclick="spjUpdateMultiSelectList(\''.$site.'\', \''.$name.$uid.'\');" />';

	$site = SFHOMEURL.'index.php?sp_ahah=multiselect&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=".urlencode($from)."&amp;num=$num&amp;offset=".($offset + $num)."&amp;max=$max&amp;filter=$filter";
	$out.= '<input type="button"'.$disabled.' id="nextpage'.$uid.'" class="button button-highlighted sfalignright" value="'.spa_text('Next').'" onclick="spjUpdateMultiSelectList(\''.$site.'\', \''.$name.$uid.'\');" />';

	$disabled = '';
	if ($offset == 0) $disabled = ' disabled="disabled"';

	$site = SFHOMEURL.'index.php?sp_ahah=multiselect&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=".urlencode($from)."&amp;num=$num&amp;offset=0&amp;max=$max&amp;filter=$filter";
	$out.= '<input type="button"'.$disabled.' id="firstpage'.$uid.'" class="button button-highlighted sfalignleft" value="'.spa_text('First').'" onclick="spjUpdateMultiSelectList(\''.$site.'\', \''.$name.$uid.'\');" />';

	$site = SFHOMEURL.'index.php?sp_ahah=multiselect&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;page_msbox=next&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=".urlencode($from)."&amp;num=$num&amp;offset=".($offset - $num)."&amp;max=$max&amp;filter=$filter";
	$out.= '<input type="button"'.$disabled.' id="prevpage'.$uid.'" class="button button-highlighted sfalignleft" value="'.spa_text('Prev').'" onclick="spjUpdateMultiSelectList(\''.$site.'\', \''.$name.$uid.'\');" />';

	$out.= '<div style="clear:both;padding: 5px 0pt;">';
	$out.= '<input type="button" id="add'.$uid.'" class="button button-highlighted" value="'.spa_text('Move to Selected List').'" onclick="spjTransferSelectList(\'temp-'.$name.$uid.'\', \''.$name.$uid.'\', \''.esc_js(spa_text('List is Empty')).'\', \''.esc_js(spa_text('Maximum of 400 Users would be exceeded - please reduce the selections')).'\', \''.$name.$uid.'\')" />';
	$out.= '</div>';

	$out.= '<input type=text id="list-filter'.$name.$uid.'" name="list-filter'.$name.$uid.'" value="'.$filter.'" class="sfacontrol sfalignleft" style="width:50% !important;" />';
	$gif = SFCOMMONIMAGES."working.gif";
	$site = SFHOMEURL.'index.php?sp_ahah=multiselect&amp;sfnonce='.wp_create_nonce('forum-ahah')."&amp;page_msbox=filter&amp;msbox=$msbox&amp;uid=$uid&amp;name=$name&amp;from=".urlencode($from)."&amp;num=$num&amp;offset=0&amp;max=$max";
	$out.= '<input type="button" id="filter'.$uid.'" class="button button-highlighted" value="'.spa_text('Filter List').'" style="margin-top:1px" onclick="spjFilterMultiSelectList(\''.$site.'\', \''.$name.$uid.'\', \''.$gif.'\');" />';

	$out.= '</div>';
	return $out;
}
?>