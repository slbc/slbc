<?php
/*
Simple:Press
Admin Forums Remove All Permissions Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the remove all permission set form.  It is hidden until the remove all permission set link is clicked
function spa_forums_remove_perms_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfallpermissionsdel', 'sfreloadfb');
});
</script>
<?php

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=removeperms';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfallpermissionsdel" name="sfallpermissionsdel">
<?php
		echo sp_create_nonce('forum-adminform_allpermissionsdelete');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Delete All Permission Sets'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Delete All Forum Permission Sets'), 'true', 'delete-all-forum-permission-sets', false);
					echo '<p>';
					spa_etext('Warning! You are about to delete all permission sets');
					echo '</p>';
					echo '<p>';
					spa_etext('This will delete all permission sets for all groups and forums');
					echo '</p>';
					echo '<p>';
					echo sprintf(spa_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the delete all permission sets button below to proceed');
					echo '</p>';
				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="saveit" name="saveit" value="<?php spa_etext('Delete All Permission Sets'); ?>" />
		</div>
	</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>