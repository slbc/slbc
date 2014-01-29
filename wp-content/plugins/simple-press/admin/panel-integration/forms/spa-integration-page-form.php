<?php
/*
Simple:Press
Admin integration Page and Permalink Form
$LastChangedDate: 2013-09-15 02:12:36 -0700 (Sun, 15 Sep 2013) $
$Rev: 10689 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_integration_page_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('wppageform', 'sfreloadpp');
});
</script>
<?php

	$sfoptions = spa_get_integration_page_data();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=integration-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=page';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="wppageform" name="wppage">
	<?php echo sp_create_nonce('forum-adminform_integration'); ?>
<?php

	spa_paint_options_init();

	spa_paint_open_tab(spa_text('Integration').' - '.spa_text('Page and Permalink'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('WP Forum Page Details'), true, 'forum-page-details');
				if ($sfoptions['sfpage'] == 0) echo '<tr><td colspan="2"><div class="sfoptionerror">'.spa_text('ERROR: The page slug is either missing or incorrect. The forum will not display until this is corrected').'</div></td></tr>';
				spa_paint_select_start(spa_text('Select the WP Page to be used to display your forum'), 'slug', 'slug');
				echo spa_create_page_select($sfoptions['sfpage']);
				spa_paint_select_end();
			spa_paint_close_fieldset();

			if ($sfoptions['sfpage'] != 0) {
				$title = spdb_table(SFWPPOSTS, 'ID='.$sfoptions['sfpage'], 'post_title');
				$template = spdb_table(SFWPPOSTMETA, "meta_key='_wp_page_template' AND post_id=".$sfoptions['sfpage'], 'meta_value');
				spa_paint_open_fieldset(spa_text('Current WP Forum Page'), false, '', true);
					echo '<table class="form-table"><tr>';
					echo '<th>'.spa_text('Forum page ID').'</th>';
					echo '<th>'.spa_text('Page title').'</th>';
					echo '<th>'.spa_text('Page template').'</th>';
					echo '</tr>';
					echo '<tr>';
					echo '<td class="sflabel">'.$sfoptions['sfpage'].'</td>';
					echo '<td class="sflabel">'.$title.'</td>';
					echo '<td class="sflabel">'.$template.'</td>';
					echo "</tr></table>";
				spa_paint_close_fieldset();

				spa_paint_open_fieldset(spa_text('Update Forum Permalink'), true, 'forum-permalink', false);
					echo '<p class="sublabel">'.spa_text('Current permalink').':<br /></p><div class="subhead" id="adminupresult"><p>'.$sfoptions["sfpermalink"].'</p></div><br />';
					spa_paint_update_permalink();
				spa_paint_close_fieldset();
			}

			spa_paint_open_fieldset(spa_text('Integration Options'), true, 'integration-options');
				spa_paint_checkbox(spa_text('Filter WP list pages'), 'sfwplistpages', $sfoptions['sfwplistpages']);
				spa_paint_checkbox(spa_text('Load javascript in footer'), 'sfscriptfoot', $sfoptions['sfscriptfoot']);
				spa_paint_checkbox(spa_text('Force the strict use of the WP API'), 'sfuseob', $sfoptions['sfuseob']);
			spa_paint_close_fieldset();

			spa_paint_open_fieldset(spa_text('Theme Display Options'), true, 'theme-options');
				spa_paint_checkbox(spa_text('Limit forum display to within WP loop'), 'sfinloop', $sfoptions['sfinloop']);
				spa_paint_checkbox(spa_text('Allow multiple loading of forum content'), 'sfmultiplecontent', $sfoptions['sfmultiplecontent']);
				spa_paint_checkbox(spa_text('Bypass wp_head action complete requirement'), 'sfwpheadbypass', $sfoptions['sfwpheadbypass']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_integration_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update WP Integration'); ?>" />
	</div>
	</form>
<?php
}

function spa_create_page_select($currentpageid) {
	$pages = spdb_table(SFWPPOSTS, "post_type='page' && post_status!='trash'", '', 'menu_order');

	if ($pages) {
		$default = '';
		$out = '';
		$spacer = '&nbsp;&nbsp;&nbsp;&nbsp;';
		$out.= '<optgroup label="'.spa_text('Select the WP page').':">'."\n";
		foreach ($pages as $page) {
			$sublevel = 0;
			if ($page->post_parent) {
				$parent = $page->post_parent;
				$pageslug = $page->post_name;
				while ($parent) {
					$thispage = spdb_table(SFWPPOSTS, "ID=$parent", 'row');
					$pageslug = $thispage->post_name.'/'.$pageslug;
					$parent = $thispage->post_parent;
					$sublevel++;
				}
			} else {
				$pageslug = $page->post_name;
			}

			if ($currentpageid == $page->ID) {
				$default = 'selected="selected" ';
			} else {
				$default = null;
			}
			$out.= '<option '.$default.'value="'.$page->ID.'">'.$spacer.str_repeat('&rarr;&nbsp;', $sublevel).$pageslug.'</option>'."\n";
			$default = '';
		}
		$out.= '</optgroup>';
	} else {
		$out.='<option value="0">'.spa_text('No WP pages found - please create one').'</option>'."\n";
	}
	return $out;
}

function spa_paint_update_permalink() {
    $site = SFHOMEURL.'index.php?sp_ahah=integration-perm&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;item=upperm';
	$target = 'adminupresult';
	$gif = SFCOMMONIMAGES.'working.gif';

	echo '<input type="button" class="button button-highlighted" value="'.spa_text('Update Forum Permalink').'" onclick="spjAdminTool(\''.$site.'\', \''.$target.'\', \''.$gif.'\');" />';
}
?>