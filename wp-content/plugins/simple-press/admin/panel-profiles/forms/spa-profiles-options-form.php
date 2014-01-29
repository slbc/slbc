<?php
/*
Simple:Press
Admin Profile Options Form
$LastChangedDate: 2013-03-16 15:24:09 -0700 (Sat, 16 Mar 2013) $
$Rev: 10079 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_profiles_options_form()
{
?>
<script type="text/javascript">
	spjAjaxForm('sfoptionsform', '');
</script>
<?php

	$sfoptions = spa_get_options_data();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=profiles-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=options';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfoptionsform" name="sfoptions">
	<?php echo sp_create_nonce('forum-adminform_options'); ?>
<?php

	spa_paint_options_init();

#== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(spa_text('Profiles').' - '.spa_text('Profile Options'));

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Display Name Format'), true, 'display-name-format');
				spa_paint_checkbox(spa_text('Let member choose display name'), 'nameformat', $sfoptions['nameformat']);
				spa_paint_select_start(spa_text('Display name format if member cannot choose').'<br />'.spa_text('(ignored if member allowed to choose)'), 'fixeddisplayformat', 'fixeddisplayformat');
				echo spa_display_name_format_options($sfoptions['fixeddisplayformat']);
				spa_paint_select_end();
    			echo '<tr><td colspan="2"><br /><div class="sfoptionerror">';
    			spa_etext('Warning: If you change the display name format, it may take some time on a large number of users to update them to the new format. Please be patient.');
    			echo '</div><br />';
    			echo '</td></tr>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Profile Link'), true, 'profile-link');
				$values = array(spa_text("Member's display name"), spa_text("Member's avatar"), spa_text('Profile icon'));
				spa_paint_radiogroup(spa_text('Set profile link to'), 'profilelink', $values, $sfoptions['profilelink'], false, true);
				spa_paint_checkbox(spa_text('Always use profile link in statistics lists'), 'profileinstats', $sfoptions['profileinstats']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Website Link'), true, 'website-link');
				$values = array(spa_text("Member's display name"), spa_text("Member's avatar"), spa_text('Website icon'));
				spa_paint_radiogroup(spa_text('Set website link to'), 'weblink', $values, $sfoptions['weblink'], false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Personal Photos'), true, 'personal-photos');
				spa_paint_input(spa_text('Maximum number of photos allowed'), 'photosmax', $sfoptions['photosmax'], false, false);
				spa_paint_input(spa_text('Maximum pixel width of photo display'), 'photoswidth', $sfoptions['photoswidth'], false, false);
				spa_paint_input(spa_text('Maximum pixel height of photo display'), 'photosheight', $sfoptions['photosheight'], false, false);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Signature Image Size'), true, 'sig-images');
				echo '<tr><td colspan="2">&nbsp;<u>'.spa_text('If you are allowing signature images (zero = not limited)').':</u></td></tr>';
				spa_paint_input(spa_text('Maximum signature width (pixels)'), 'sfsigwidth', $sfoptions['sfsigwidth']);
				spa_paint_input(spa_text('Maximum signature height (pixels)'), 'sfsigheight', $sfoptions['sfsigheight']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('First Forum Visit'), true, 'first-forum-visit');
				spa_paint_checkbox(spa_text('Display profile form on login'), 'firstvisit', $sfoptions['firstvisit']);
            	$show_password_fields = apply_filters('show_password_fields', true);
        		if ($show_password_fields) spa_paint_checkbox(spa_text('Force password change'), 'forcepw', $sfoptions['forcepw']);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_options_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Display Profile Mode'), true, 'display-profile-mode');
				$values = array(spa_text('Popup window'), spa_text('Forum profile page'), spa_text('BuddyPress profile'), spa_text('WordPress author page'), spa_text('Other page'), spa_text('Mingle profile'));
				spa_paint_radiogroup(spa_text('Display profile information in'), 'displaymode', $values, $sfoptions['displaymode'], false, true);
				spa_paint_input(spa_text('URL for Other page'), 'displaypage', sp_filter_url_display($sfoptions['displaypage']), false, true);
				spa_paint_input(spa_text('Query String Variable Name'), 'displayquery', sp_filter_title_display($sfoptions['displayquery']), false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Profile Entry Form Mode'), true, 'profile-entry-form-mode');
				$values = array(spa_text('Forum profile form'), spa_text('WordPress profile form'), spa_text('BuddyPress profile'), spa_text('Other form'), spa_text('Mingle profile'));
				spa_paint_radiogroup(spa_text('Enter profile information In'), 'formmode', $values, $sfoptions['formmode'], false, true);
				spa_paint_input(spa_text('URL for Other page'), 'formpage', sp_filter_url_display($sfoptions['formpage']), false, true);
				spa_paint_input(spa_text('Query string variable name'), 'formquery', sp_filter_title_display($sfoptions['formquery']), false, true);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Profile Overview Message'), true, 'profile-message');
				$submessage=spa_text('Text you enter here will be displayed to the User on their profile overview page');
				spa_paint_wide_textarea(spa_text('Profile overview message'), 'sfprofiletext', sp_filter_text_edit($sfoptions['sfprofiletext']), $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_options_right_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Profile Options'); ?>" />
	</div>
	</form>
<?php
}
# ------------------------------------------------------------------
# spa_display_name_format_options()
# Initializes the different options for the Display Name Format
# dropdown list.
# ------------------------------------------------------------------
function spa_display_name_format_options($option_id=0) {
	$options = array(0 => 'WP DisplayName',
					 1 => 'WP Login',
					 2 => 'FirstName',
					 3 => 'LastName',
					 4 => 'FirstName LastName',
					 5 => 'LastName, FirstName',
					 6 => 'FirstInitial LastName',
					 7 => 'FirstName LastInitial',
					 8 => 'First and Last Initials');

	$out = '';
	foreach ($options as $option_value => $option_name) {
		$selected_text = '';
		if (intval($option_value) == intval($option_id)) $selected_text = 'selected="selected" ';
		$out.='<option '.$selected_text.'value="'.$option_value.'">'.$option_name.'</option>'."\n";
	}

	return $out;
}
?>