<?php
/*
Simple:Press
Admin Admins Your Options Form
$LastChangedDate: 2013-08-11 12:03:54 -0700 (Sun, 11 Aug 2013) $
$Rev: 10506 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_admins_your_options_form() {
	global $spThisUser;
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfmyadminoptionsform', 'sfreloadao');
});
</script>
<?php
	$sfadminsettings = spa_get_admins_your_options_data();
	$sfadminoptions = spa_get_admins_global_options_data();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=admins-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=youradmin';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfmyadminoptionsform" name="sfmyadminoptions">
	<?php echo sp_create_nonce('my-admin_options'); ?>
<?php

	spa_paint_options_init();
	spa_paint_open_tab(spa_text('Admins').' - '.spa_text('Your Admin Options'), true);
		spa_paint_open_panel();
			spa_paint_open_fieldset(spa_text('Your Admin/Moderator Options'), 'true', 'your-admin-options');
				spa_paint_checkbox(spa_text('Receive email notification on new topic/post'), 'sfnotify', $sfadminsettings['sfnotify']);
				spa_paint_checkbox(spa_text('Receive notification (within forum - not email) on topic/post edits'), 'notify-edited', $sfadminsettings['notify-edited']);
				spa_paint_checkbox(spa_text('Bypass the Simple Press logout redirect'), 'bypasslogout', $sfadminsettings['bypasslogout']);
				$submessage = spa_text('Text you enter here will be displayed as a custom message when you are offline if the sf_admin_mod_status() template tag is used');
				spa_paint_wide_textarea(spa_text('Custom offline status message'), 'sfstatusmsgtext', sp_filter_text_edit($sfadminsettings['sfstatusmsgtext']), $submessage);
			spa_paint_close_fieldset();
		spa_paint_close_panel();
		do_action('sph_admins_options_top_panel');

		if($spThisUser->admin) {
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Set Your Moderator Options'), 'true', 'set-moderator-options');
					spa_paint_checkbox(spa_text('Grant all moderators the same option settings as above - except for offline message'), 'setmods', $sfadminsettings['setmods']);
				spa_paint_close_fieldset();
			spa_paint_close_panel();
		}
	spa_paint_close_tab();

?>
	<div class="sfform-submit-bar">
	<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Update Your Admin Options'); ?>" />
	</div>
	</form>
<?php
}

?>