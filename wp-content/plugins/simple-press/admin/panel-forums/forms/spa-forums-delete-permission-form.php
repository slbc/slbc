<?php
/*
Simple:Press
Admin Forums Delete Permission Form
$LastChangedDate: 2013-08-05 13:11:02 -0700 (Mon, 05 Aug 2013) $
$Rev: 10469 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

# function to display the delete forum permission set form.  It is hidden until the delete permission set link is clicked
function spa_forums_delete_permission_form($perm_id) {
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	spjAjaxForm('sfpermissiondel<?php echo $perm_id; ?>', 'sfreloadfb');
});
</script>
<?php
	$perm = spdb_table(SFPERMISSIONS, "permission_id=$perm_id", 'row');

	echo '<div class="sfform-panel-spacer"></div>';

	spa_paint_options_init();

    $ahahURL = SFHOMEURL.'index.php?sp_ahah=forums-loader&amp;sfnonce='.wp_create_nonce('forum-ahah').'&amp;saveform=delperm';
?>
	<form action="<?php echo $ahahURL; ?>" method="post" id="sfpermissiondel<?php echo $perm->permission_id; ?>" name="sfpermissiondel<?php echo $perm->permission_id; ?>">
<?php
		echo sp_create_nonce('forum-adminform_permissiondelete');
		spa_paint_open_tab(spa_text('Forums').' - '.spa_text('Manage Groups and Forums'), true);
			spa_paint_open_panel();
				spa_paint_open_fieldset(spa_text('Delete Permission Set'), 'true', 'delete-permission-set', false);
?>
					<input type="hidden" name="permission_id" value="<?php echo $perm->permission_id; ?>" />
<?php
					echo '<p>';
					spa_etext('Warning! You are about to delete a permission set');
					echo '</p>';
					echo '<p>';
					spa_etext('This will remove ALL access to this forum for this usergroup');
					echo '</p>';
					echo '<p>';
					echo sprintf(spa_text('Please note that this action %s can NOT be reversed %s'), '<strong>', '</strong>');
					echo '</p>';
					echo '<p>';
					spa_etext('Click on the delete permission set button below to proceed');
					echo '</p>';

				spa_paint_close_fieldset(false);
			spa_paint_close_panel();
			do_action('sph_forums_delete_perm_panel');
		spa_paint_close_tab();
?>
		<div class="sfform-submit-bar">
		<input type="submit" class="button-primary" id="delperm<?php echo $perm->permission_id; ?>" name="delperm<?php echo $perm->permission_id; ?>" value="<?php spa_etext('Delete Permission Set'); ?>" />
		<input type="button" class="button-primary" onclick="javascript:jQuery('#curperm-<?php echo $perm->permission_id; ?>').html('');" id="sfpermissiondel<?php echo $perm->permission_id; ?>" name="delpermcancel<?php echo $perm->permission_id; ?>" value="<?php spa_etext('Cancel'); ?>" />
	</div>
</form>

	<div class="sfform-panel-spacer"></div>
<?php
}
?>