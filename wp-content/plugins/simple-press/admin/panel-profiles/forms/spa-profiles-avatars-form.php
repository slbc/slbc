<?php
/*
Simple:Press
Admin Profile Avatars Form
$LastChangedDate: 2013-08-23 12:54:15 -0700 (Fri, 23 Aug 2013) $
$Rev: 10568 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

define('SFUPLOADER', SFHOMEURL."index.php?sp_ahah=uploader&sfnonce=".wp_create_nonce('forum-ahah'));

function spa_profiles_avatars_form() {
	global $spPaths;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfavatarsform', 'sfreloadav');

	jQuery("#sfavataroptions").sortable({
		placeholder: 'sortable-placeholder',
		update: function () {
			jQuery("input#sfavataropts").val(jQuery("#sfavataroptions").sortable('serialize'));
		}
	});

	var button = jQuery('#sf-upload-button'), interval;
	new AjaxUpload(button,{
		action: '<?php echo SFUPLOADER; ?>',
		name: 'uploadfile',
	    data: {
		    saveloc : '<?php echo addslashes(SF_STORE_DIR."/".$spPaths['avatar-pool']."/"); ?>'
	    },
		onSubmit : function(file, ext){
            /* check for valid extension */
			if (! (ext && /^(jpg|png|jpeg|gif|JPG|PNG|JPEG|GIF)$/.test(ext))){
				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Only JPG, PNG or GIF files are allowed!')); ?></p>');
				return false;
			}
			/* change button text, when user selects file */
			utext = '<?php echo esc_js(spa_text('Uploading')); ?>';
			button.text(utext);
			/* If you want to allow uploading only 1 file at time, you can disable upload button */
			this.disable();
			/* Uploding -> Uploading. -> Uploading... */
			interval = window.setInterval(function(){
				var text = button.text();
				if (text.length < 13){
					button.text(text + '.');
				} else {
					button.text(utext);
				}
			}, 200);
		},
		onComplete: function(file, response){
			jQuery('#sf-upload-status').html('');
			button.text('<?php echo esc_js(spa_text('Browse')); ?>');
			window.clearInterval(interval);
			/* re-enable upload button */
			this.enable();
			/* add file to the list */
			if (response==="success"){
                site = "<?php echo SFHOMEURL; ?>index.php?sp_ahah=profiles&amp;sfnonce=<?php echo wp_create_nonce('forum-ahah'); ?>&amp;action=delavatar&amp;file=" + file;
				jQuery('<table width="100%"></table>').appendTo('#sf-avatar-pool').html('<tr><td width="60%" align="center"><img class="sfavatarpool" src="<?php echo SFAVATARPOOLURL; ?>/' + file + '" alt="" /></td><td class="sflabel" align="center" width="30%">' + file + '</td><td class="sflabel" align="center" width="9%"><img src="<?php echo SFCOMMONIMAGES; ?>' + 'delete.png' + '" title="<?php echo esc_js(spa_text('Delete Avatar')); ?>" alt="" onclick="spjDelRowReload(\'' + site + '\', \'sfreloadav\');" /></td></tr>');
				jQuery('#sf-upload-status').html('<p class="sf-upload-status-success"><?php echo esc_js(spa_text('Avatar Uploaded!')); ?></p>');
			} else if (response==="invalid"){
				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file has an invalid format!')); ?></p>');
			} else if (response==="exists") {
				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Sorry, the file already exists!')); ?></p>');
			} else {
				jQuery('#sf-upload-status').html('<p class="sf-upload-status-fail"><?php echo esc_js(spa_text('Error uploading file!!')); ?></p>');
			}
		}
	});
});
</script>
<?php

	$sfoptions = spa_get_avatars_data();

    $ahahURL = SFHOMEURL."index.php?sp_ahah=profiles-loader&amp;sfnonce=".wp_create_nonce('forum-ahah')."&amp;saveform=avatars";
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfavatarsform" name="sfavatars">
	<?php echo sp_create_nonce('forum-adminform_avatars'); ?>
<?php

	spa_paint_options_init();

#== PROFILE OPTIONS Tab ============================================================

	spa_paint_open_tab(spa_text('Profiles').' - '.spa_text('Avatars'));
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Options'), true, 'avatar-options');
				spa_paint_checkbox(spa_text('Display avatars'), 'sfshowavatars', $sfoptions['sfshowavatars']);
				spa_paint_input(spa_text('Maximum avatar display width (pixels)'), 'sfavatarsize', $sfoptions['sfavatarsize'], false, false);
				spa_paint_checkbox(spa_text('Enable avatar uploading'), 'sfavataruploads', $sfoptions['sfavataruploads']);
				spa_paint_input(spa_text('Maximum avatar upload file size (bytes)'), 'sfavatarfilesize', $sfoptions['sfavatarfilesize'], false, false);
				spa_paint_checkbox(spa_text('Auo resize avatar uploads'), 'sfavatarresize', $sfoptions['sfavatarresize']);
				spa_paint_input(spa_text('Uploaded avatar resize quality (if resizing)'), 'sfavatarresizequality', $sfoptions['sfavatarresizequality'], false, false);
				spa_paint_checkbox(spa_text('Enable avatar pool selection'), 'sfavatarpool', $sfoptions['sfavatarpool']);
				spa_paint_checkbox(spa_text('Enable remote avatars'), 'sfavatarremote', $sfoptions['sfavatarremote']);
				$values = array(spa_text('G - Suitable for all'), spa_text('PG- Suitable for 13 and above'), spa_text('R - Suitable for 17 and above'), spa_text('X - Suitable for all adults'));
				spa_paint_radiogroup(spa_text('Gravatar max rating'), 'sfgmaxrating', $values, $sfoptions['sfgmaxrating'], false, true);
				spa_paint_checkbox(spa_text('Replace WP avatar with SP avatar'), 'sfavatarreplace', $sfoptions['sfavatarreplace']);
    			echo '<tr><td colspan="2"><br /><div class="sfoptionerror">';
    			spa_etext('Warning: If you want to replace WP avatars with SP avatars, make sure you dont have WP avatars in your avatar priorities (have it below SP Default Avatars) or you will have a circular reference');
    			echo '</div><br />';
    			echo '</td></tr>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		do_action('sph_profiles_avatar_left_panel');
		spa_paint_tab_right_cell();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Priorities'), true, 'avatar-priorities');
				echo '<tr>';
				echo '<td class="sflabel" colspan="2">';
				echo '<table class="form-table table-cbox">';
				echo '<tr>';
				echo '<td class="td-cbox" style="width:200px">';
				echo '<ul id="sfavataroptions" class="menu">';
				$list = array(0 => spa_text('Gravatars'), 1 => spa_text('WP Avatars'), 2 => spa_text('Uploaded Avatar'), 3 => spa_text('SP Default Avatars'), 4 => spa_text('Avatar Pool'), 5 => spa_text('Remote Avatar'));
				if($sfoptions['sfavatarpriority'])
				{
					foreach ($sfoptions['sfavatarpriority'] as $priority)
					{
						echo '<li id="aitem_'.$priority.'" class="menu-item menu-item-depth-0"><span class="item-name">'.$list[$priority].'</span></li>';
					}
				}
				echo '</ul>';
				echo '<input type="text" class="inline_edit" size="70" id="sfavataropts" name="sfavataropts" />';
				echo '</td>';
				echo '<td class="td-cbox">';
				spa_etext('Select the avatar dislay priority order by dragging and dropping the buttons in the column to the left.  The top of the list is the highest priority order.  When an avatar is found for the current priority, it is output.  If none is found, the next priority is checked and so on.  An SP Default Avatar will always be found. Any avatar after the SP Default Avatar is essentially ignored');
				echo '</td>';
				echo '</tr>';
				echo '</table>';
				echo '</td>';
				echo '</tr>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

	spa_paint_close_tab();

	spa_paint_open_nohead_tab(true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Pool Upload'), true, 'avatar-pool-upload');
				$loc = SF_STORE_DIR."/".$spPaths['avatar-pool'].'/';
				spa_paint_file(spa_text('Select avatar to upload'), 'newavatar', false, true, $loc);
				echo '<tr>';
				echo '<td class="sflabel" colspan="2"><small>';
				spa_etext('Please be advised that Admin uploaded avatars for the avatar pool are NOT subject to the user uploaded avatar size limits.  So use caution when picking avatars for your avatar pool');
				echo '</small></td>';
				echo '</tr>';
			spa_paint_close_fieldset();
		spa_paint_close_panel();

		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Avatar Pool'), true, 'avatar-pool', true);
				spa_paint_avatar_pool();
			spa_paint_close_fieldset(true);
		spa_paint_close_panel();

		do_action('sph_profiles_avatar_right_panel');
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Avatar Options'); ?>" />
	</div>
	</form>
<?php
}
?>